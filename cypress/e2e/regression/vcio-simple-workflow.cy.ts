/**
 * VCIO Simple Workflow Test
 *
 * Scenario 1: No examiner assigned, low/moderate protection need
 * Flow: Project → UCD → PNA (low risk) → VCIO → Skip Validation → Certificate
 *
 * This test covers the complete assessment workflow where:
 * - No examiner is assigned to the project
 * - PNA answers result in low protection need
 * - Validation can be skipped (no examiner required)
 * - Process completes directly to status 60
 */
describe('VCIO Simple Workflow - No Examiner, Skip Validation', () => {
  let testData: any

  before(() => {
    cy.fixture('test-data').then((data) => {
      testData = data
    })
  })

  it('should complete full workflow without examiner (skip validation)', () => {
    const uniqueId = Date.now()

    // ========================================
    // PHASE 1: Create project WITHOUT examiner
    // ========================================
    cy.log('**PHASE 1: Create project without examiner**')

    cy.login(testData.testUser.email, testData.testUser.password)

    cy.visit('/projects/add')
    cy.get('#title').type(`VCIO Simple Test - ${uniqueId}`)
    cy.get('#description').type('E2E test: No examiner, low risk, skip validation')
    cy.get('input[name="process_title"]').type('Simple VCIO Process')

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
        cy.get('input[type="checkbox"]').check({ force: true })
        cy.contains('a', 'Prüfprozess beginnen').click()
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
    // PHASE 3: Complete PNA with LOW risk answers
    // ========================================
    cy.log('**PHASE 3: Complete PNA with low risk answers**')

    cy.visit('/')
    cy.contains('a', 'Prüfung fortsetzen', { timeout: 15000 }).click()

    cy.url().should('match', /\/criteria\/|\/protection-needs-analysis\//, { timeout: 10000 })

    cy.fillPNAWithRisk('low')

    cy.log('**PNA complete - Status is now 30**')

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
    // PHASE 5: Skip Validation (no examiner, low risk)
    // ========================================
    cy.log('**PHASE 5: Skip Validation**')

    cy.url().should('include', '/indicators/decide-validation/', { timeout: 10000 })

    cy.get('body').then($body => {
      const hasLowRisk = $body.text().includes('Niedriger Schutzbedarf')
      const hasModerateRisk = $body.text().includes('Moderater Schutzbedarf')
      expect(hasLowRisk || hasModerateRisk, 'Should have low or moderate risk level').to.be.true
    })

    cy.contains('h3', 'Validierung überspringen').should('be.visible')

    cy.skipValidation()

    cy.log('**Validation skipped - Status is now 60 (Complete)**')

    // ========================================
    // PHASE 6: Verify completion
    // ========================================
    cy.log('**PHASE 6: Verify process completion**')

    cy.get('body').should('contain', 'Abgeschlossen')

    cy.log('**TEST COMPLETE: Full workflow without examiner succeeded**')
  })
})
