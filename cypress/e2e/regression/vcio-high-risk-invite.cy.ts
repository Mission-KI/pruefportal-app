/**
 * VCIO High Risk - Invite Examiner at Validation Decision
 *
 * Scenario 2: No examiner initially, high protection need
 * Flow: Project (no examiner) → UCD → PNA (high risk) → VCIO →
 *       Validation Decision (invite examiner) → Examiner Validates → Accept → Certificate
 *
 * This test covers the workflow where:
 * - No examiner is assigned initially
 * - PNA answers result in high protection need
 * - Examiner must be invited at validation decision stage
 * - Examiner validates, candidate accepts
 */
describe('VCIO High Risk - Invite Examiner at Validation Decision', () => {
  let testData: any
  let processId: string

  before(() => {
    cy.fixture('test-data').then((data) => {
      testData = data
    })
  })

  it('should complete workflow with examiner invited at validation decision', () => {
    const uniqueId = Date.now()

    // ========================================
    // PHASE 1: Create project WITHOUT examiner (as candidate)
    // ========================================
    cy.log('**PHASE 1: Create project without examiner**')

    cy.login(testData.testUser.email, testData.testUser.password)

    cy.visit('/projects/add')
    cy.get('#title').type(`VCIO High Risk Invite Test - ${uniqueId}`)
    cy.get('#description').type('E2E test: No examiner initially, high risk, invite at decision')
    cy.get('input[name="process_title"]').type('High Risk Invite Process')

    cy.get('body').then($body => {
      if ($body.find('#accept-disclaimer').length > 0) {
        cy.get('#accept-disclaimer').check({ force: true })
      }
    })

    cy.get('#project-form button[type="submit"]').last().should('not.be.disabled')
    cy.get('#project-form button[type="submit"]').last().click()

    cy.url().should('match', /\/process/, { timeout: 15000 })

    cy.url().then((url) => {
      if (url.includes('/processes/start/')) {
        const match = url.match(/\/processes\/start\/(\d+)/)
        if (match) processId = match[1]
        cy.get('input[type="checkbox"]').check({ force: true })
        cy.contains('a', 'Prüfprozess beginnen').click()
      } else {
        const match = url.match(/\/processes\/view\/(\d+)/)
        if (match) processId = match[1]
      }
    })

    // ========================================
    // PHASE 2: Complete UCD
    // ========================================
    cy.log('**PHASE 2: Complete Use Case Description**')

    cy.url().should('include', '/usecase-descriptions/', { timeout: 10000 })

    cy.fillUCDStep(1, testData.ucd.step1)
    cy.submitUCDStep()

    cy.fillUCDStep(2, testData.ucd.step2)
    cy.submitUCDStep()

    cy.fillUCDStep(3, testData.ucd.step3)
    cy.submitUCDStep()

    cy.submitFinalUCDStep()

    cy.url().should('include', '/processes/view/', { timeout: 15000 })

    // ========================================
    // PHASE 3: Complete PNA with HIGH risk answers
    // ========================================
    cy.log('**PHASE 3: Complete PNA with high risk answers**')

    cy.visit('/')
    cy.contains('a', 'Prüfung fortsetzen', { timeout: 15000 }).click()

    cy.url().should('match', /\/criteria\/|\/protection-needs-analysis\//, { timeout: 10000 })

    cy.fillPNAWithRisk('high')

    cy.log('**PNA complete with HIGH risk - Status is now 30**')

    // ========================================
    // PHASE 4: Complete VCIO self-assessment
    // ========================================
    cy.log('**PHASE 4: Complete VCIO self-assessment**')

    cy.visit('/')
    cy.contains('a', 'Prüfung fortsetzen', { timeout: 15000 }).click()

    cy.url().should('include', '/indicators/', { timeout: 10000 })

    cy.fillVCIOForAllQDs(testData.vcio.evidence, testData.vcio.level)

    cy.completeVCIO()

    cy.log('**VCIO complete - Status is now 35 (Validation Decision)**')

    // ========================================
    // PHASE 5: Invite Examiner at Validation Decision
    // ========================================
    cy.log('**PHASE 5: Invite Examiner at Validation Decision**')

    cy.url().should('include', '/indicators/decide-validation/', { timeout: 10000 })

    cy.get('body').should('contain', 'Hoher Schutzbedarf')

    cy.contains('h3', 'PrüferIn einladen').should('be.visible')

    cy.inviteExaminerAtValidation(testData.examiner.name, testData.examiner.email)

    cy.log('**Examiner invited and qualification confirmed - Status is now 40**')

    // ========================================
    // PHASE 6: Examiner validates VCIO
    // ========================================
    cy.log('**PHASE 6: Examiner validates VCIO**')

    Cypress.session.clearAllSavedSessions()
    cy.clearCookies()
    cy.clearLocalStorage()

    cy.login(testData.examiner.email, testData.examiner.password)

    cy.then(() => {
      cy.visit(`/indicators/validation/${processId}`)
    })

    cy.validateVCIOAsExaminer(testData.vcio.level)

    cy.completeExaminerValidation()

    cy.log('**Examiner validation complete - Status is now 50**')

    // ========================================
    // PHASE 7: Candidate accepts validation
    // ========================================
    cy.log('**PHASE 7: Candidate accepts validation**')

    Cypress.session.clearAllSavedSessions()
    cy.clearCookies()
    cy.clearLocalStorage()

    cy.login(testData.testUser.email, testData.testUser.password)

    cy.then(() => {
      cy.visit(`/indicators/accept-validation/${processId}`)
    })

    cy.acceptValidation()

    cy.log('**Validation accepted - Status is now 60 (Complete)**')

    // ========================================
    // PHASE 8: Verify completion
    // ========================================
    cy.log('**PHASE 8: Verify process completion**')

    cy.url().should('match', /\/processes\/(view|total-result)\//, { timeout: 10000 })

    cy.get('[data-testid="overall-assessment"]').should('exist')

    cy.log('**TEST COMPLETE: High risk workflow with examiner invite succeeded**')
  })
})
