/**
 * FeedbackAI Demo Workflow Test (Schnelle Version)
 *
 * Zeigt den vollständigen Prüfprozess nach dem MISSION KI Qualitätsstandard.
 */
describe('FeedbackAI Demo - Vollständiger Prüfworkflow', () => {
  let testData: any

  const pnaTargetValues: Record<string, { af?: number; gf: number }> = {
    DA: { af: 3, gf: 3 },
    ND: { af: 3, gf: 2 },
    TR: { af: 3, gf: 2 },
    MA: { af: 3, gf: 2 },
    VE: { gf: 1 },
    CY: { af: 1, gf: 1 },
  }

  before(() => {
    cy.viewport(1920, 1080)
    cy.fixture('demo-feedbackai').then((data) => {
      testData = data
    })
  })

  beforeEach(() => {
    cy.viewport(1920, 1080)
  })

  it('Durchläuft den kompletten Prüfworkflow von Projekterstellung bis Schutzbedarfsanalyse', () => {
    const uniqueId = Date.now()

    // PHASE 1: Login und Projekterstellung
    cy.log('**PHASE 1: Projekterstellung**')
    cy.login(testData.testUser.email, testData.testUser.password)

    cy.visit('/projects/add')
    cy.get('#title').type(`${testData.project.title} - ${uniqueId}`, { delay: 5 })
    cy.get('#description').type(testData.project.description, { delay: 5 })
    cy.get('input[name="process_title"]').type(testData.process.title, { delay: 5 })

    cy.contains('button', 'PrüferIn hinzufügen').click()
    cy.get('#additional-participant-name-0').type(testData.examiner.name, { delay: 5 })
    cy.get('#additional-participant-email-0').type(testData.examiner.email, { delay: 5 })

    cy.get('body').then(($body) => {
      if ($body.find('#accept-disclaimer').length > 0) {
        cy.get('#accept-disclaimer').check({ force: true })
      }
    })

    cy.get('#project-form button[type="submit"]').last().should('not.be.disabled')
    cy.get('#project-form button[type="submit"]').last().click()

    cy.url().should('match', /\/process/, { timeout: 15000 })

    cy.url().then((url) => {
      if (url.includes('/processes/start/')) {
        cy.get('input[type="checkbox"]').check({ force: true })
        cy.contains('a', 'Prüfprozess beginnen').click()
      }
    })

    // PHASE 2: Anwendungsfallbeschreibung (UCD)
    cy.url().should('include', '/usecase-descriptions/', { timeout: 10000 })

    cy.then(() => {
      cy.log('**UCD Schritt 1**')
      cy.fillUCDStep(1, testData.ucd.step1)
      cy.submitUCDStep()

      cy.log('**UCD Schritt 2**')
      cy.fillUCDStep(2, testData.ucd.step2)
      cy.submitUCDStep()

      cy.log('**UCD Schritt 3**')
      cy.fillUCDStep(3, testData.ucd.step3)
      cy.submitUCDStep()

      cy.log('**UCD Abschluss**')
      cy.submitFinalUCDStep()

      cy.url().should('include', '/processes/view/', { timeout: 15000 })
    })

    // PHASE 3: Schutzbedarfsanalyse (PNA)
    cy.log('**PHASE 2: Schutzbedarfsanalyse**')

    cy.then(() => {
      cy.visit('/')
      cy.contains('a', 'Prüfung fortsetzen', { timeout: 15000 }).click()
      cy.url().should('match', /\/criteria\/|\/protection-needs-analysis\//, { timeout: 10000 })

      const fillPNAPage = () => {
        cy.url().then((url) => {
          const qdMatch = url.match(/protection-needs-analysis\/\d+-([A-Z]+)/)

          if (qdMatch) {
            const currentQD = qdMatch[1]
            const targetValues = pnaTargetValues[currentQD]

            cy.get('body').then(($body) => {
              const radioGroups = new Set<string>()
              $body.find('input[type="radio"]').each((_, radio) => {
                const name = radio.getAttribute('name')
                if (name) radioGroups.add(name)
              })

              if (radioGroups.size > 0) {
                radioGroups.forEach((name) => {
                  const targetValue = targetValues?.gf || 1
                  cy.get(`input[name="${name}"]`).then(($radios) => {
                    const targetRadio = $radios.filter(`[value="${targetValue}"]`)
                    if (targetRadio.length > 0) {
                      cy.wrap(targetRadio).check({ force: true })
                    } else {
                      cy.wrap($radios.first()).check({ force: true })
                    }
                  })
                })

                cy.get('body').then(($bodyAfter) => {
                  const nextBtn = $bodyAfter.find('button:contains("Nächster Schritt")')
                  if (nextBtn.length > 0) {
                    cy.contains('button', 'Nächster Schritt').click()
                    cy.wait(500)
                    cy.then(() => fillPNAPage())
                  }
                })
              } else {
                cy.get('body').then(($bodyNoRadio) => {
                  const nextBtn = $bodyNoRadio.find('button:contains("Nächster Schritt")')
                  if (nextBtn.length > 0) {
                    cy.contains('button', 'Nächster Schritt').click()
                    cy.wait(500)
                    cy.then(() => fillPNAPage())
                  }
                })
              }
            })
          } else if (url.match(/protection-needs-analysis\/\d+$/)) {
            cy.get('body').then(($body) => {
              const bewerten = $body.find(
                '.grid a:contains("Bewerten"), .grid a:contains("Bewertung fortsetzen")'
              )
              if (bewerten.length > 0) {
                cy.wrap(bewerten.first()).click()
                cy.wait(300)
                cy.then(() => fillPNAPage())
              }
            })
          }
        })
      }

      cy.get('.grid a:contains("Bewerten"), .grid a:contains("Bewertung fortsetzen")')
        .first()
        .click()
      cy.wait(300)
      fillPNAPage()

      cy.log('**Schutzbedarfsanalyse abschließen**')
      cy.contains('Bewertung abschließen', { timeout: 10000 }).click()
      cy.url().should('not.include', '/protection-needs-analysis/', { timeout: 10000 })

      cy.log('**✓ DEMO ABGESCHLOSSEN**')
    })
  })
})
