/**
 * Complete Assessment Workflow Test
 *
 * This test covers the full assessment journey:
 * 1. Candidate creates project and completes UCD → status 15
 * 2. Examiner accepts UCD → status 20
 * 3. Examiner completes Protection Needs Analysis (PNA) → status 30
 * 4. [TODO] Candidate completes VCIO self-assessment → status 40
 */
describe('Complete Assessment Workflow', () => {
  let testData: any
  let processId: string

  before(() => {
    cy.fixture('test-data').then((data) => {
      testData = data
    })
  })

  it('should complete workflow from project creation through PNA (status 30)', () => {
    const uniqueId = Date.now()

    // ========================================
    // PHASE 1: Candidate creates project and completes UCD
    // ========================================
    cy.log('**PHASE 1: Candidate creates project and completes UCD**')

    // Login as candidate
    cy.login(testData.testUser.email, testData.testUser.password)

    // Create project with examiner
    cy.visit('/projects/add')
    cy.get('#title').type(`Assessment Workflow Test - ${uniqueId}`)
    cy.get('#description').type('Full assessment workflow test project')
    cy.get('input[name="process_title"]').type('Workflow Test Process')

    // Candidate is automatically the logged-in user (no fields to fill)

    // Add an examiner by clicking the button first
    cy.contains('button', 'PrüferIn hinzufügen').click()

    // Fill examiner fields (dynamically generated)
    cy.get('#additional-participant-name-0').type(testData.examiner.name)
    cy.get('#additional-participant-email-0').type(testData.examiner.email)

    // Accept disclaimer if it exists
    cy.get('body').then($body => {
      if ($body.find('#accept-disclaimer').length > 0) {
        cy.get('#accept-disclaimer').check({ force: true })
      }
    })

    // Wait for form to be valid and submit
    cy.get('#project-form button[type="submit"]').last().should('not.be.disabled')
    cy.get('#project-form button[type="submit"]').last().click()

    // After creating a project, the app redirects to process start page
    cy.url().should('match', /\/process/, { timeout: 15000 })

    // If on /processes/start/, accept disclaimer and click button to start the process
    // This takes us directly to the UCD form
    cy.url().then((url) => {
      if (url.includes('/processes/start/')) {
        // Extract process ID from start URL
        const match = url.match(/\/processes\/start\/(\d+)/)
        if (match) {
          processId = match[1]
          cy.log(`Process ID: ${processId}`)
        }
        // Check the disclaimer checkbox first
        cy.get('input[type="checkbox"]').check({ force: true })
        // Click "Prüfprozess beginnen" button - goes directly to UCD form
        cy.contains('a', 'Prüfprozess beginnen').click()
      }
    })

    // Wait for UCD form to load
    cy.url().should('include', '/usecase-descriptions/', { timeout: 10000 })

    // Complete all UCD steps
    cy.then(() => {

      // Step 1 - General Information
      cy.fillUCDStep(1, testData.ucd.step1)
      cy.submitUCDStep()

      // Step 2 - Human Interaction
      cy.fillUCDStep(2, testData.ucd.step2)
      cy.submitUCDStep()

      // Step 3 - Other
      cy.fillUCDStep(3, testData.ucd.step3)
      cy.submitUCDStep()

      // Step 4 - Final Submission (status changes to 15)
      cy.submitFinalUCDStep()

      // Verify completion - redirected to process view
      cy.url().should('include', '/processes/view/', { timeout: 15000 })
    })

    // ========================================
    // PHASE 2: Candidate completes Protection Needs Analysis (PNA)
    // Note: UCD review step (status 15) was removed - status goes directly from 10 to 20
    // ========================================
    cy.log('**PHASE 2: Candidate completes Protection Needs Analysis**')

    // Continue as candidate (already logged in from Phase 1)
    cy.then(() => {
      // Navigate to dashboard and click continue button
      cy.visit('/')

      // Click "Prüfung fortsetzen" to go to PNA (German: Schutzbedarfsanalyse)
      cy.contains('a', 'Prüfung fortsetzen', { timeout: 15000 }).click()

      // Wait for PNA page to load (could be index or QD form)
      cy.url().should('match', /\/criteria\/|\/protection-needs-analysis\//, { timeout: 10000 })

      // Fill all PNA questions across all QDs
      // The PNA form may have multiple pages per QD (URL stays same, content changes)
      const fillCurrentPage = () => {
        cy.url().then((url) => {
          // If we're on a QD form page (URL has dash like 43-DA)
          if (url.match(/protection-needs-analysis\/\d+-[A-Z]+/)) {
            // Check if there are unfilled radio buttons
            cy.get('body').then(($body) => {
              const uncheckedRadios = $body.find('input[type="radio"]:not(:checked)')
              if (uncheckedRadios.length > 0) {
                // Fill all radio button groups on current page
                const processedGroups = new Set()
                $body.find('input[type="radio"]').each((_, radio) => {
                  const name = radio.getAttribute('name')
                  if (name && !processedGroups.has(name)) {
                    processedGroups.add(name)
                    cy.get(`input[name="${name}"]`).first().check({ force: true })
                  }
                })

                // Click "Next step" (German: Nächster Schritt)
                cy.contains('button', 'Nächster Schritt').click()

                // Wait for page update (form submission)
                cy.wait(2000)

                // Continue filling
                cy.then(() => fillCurrentPage())
              }
              // All radios on this page are checked, click next to proceed
              else {
                const nextBtn = $body.find('button:contains("Nächster Schritt")')
                if (nextBtn.length > 0) {
                  cy.contains('button', 'Nächster Schritt').click()
                  cy.wait(2000)
                  cy.then(() => fillCurrentPage())
                }
              }
            })
          }
          // If we're at overview page, look for more QDs to complete
          else if (url.match(/protection-needs-analysis\/\d+$/)) {
            cy.get('body').then(($body) => {
              const bewerten = $body.find('.grid a:contains("Bewerten"), .grid a:contains("Bewertung fortsetzen")')
              if (bewerten.length > 0) {
                cy.wrap(bewerten.first()).click()
                cy.wait(1000)
                cy.then(() => fillCurrentPage())
              }
            })
          }
          // Somewhere else (dashboard, etc.) - PNA might be done
        })
      }

      // Start by clicking first incomplete QD from overview
      cy.get('.grid a:contains("Bewerten"), .grid a:contains("Bewertung fortsetzen")').first().click()
      cy.wait(1000)
      fillCurrentPage()

      // After all QDs are complete, click "Complete Rating" button (German: Bewertung abschließen)
      cy.contains('a', 'Bewertung abschließen', { timeout: 15000 }).click()

      // Should be redirected to completion page or dashboard (status changes to 30)
      cy.url().should('match', /(criteria\/complete|^\/$)/, { timeout: 15000 })

      cy.log('**PHASE 3 COMPLETE: Status is now 30 (VCIO-Einstufung)**')
    })
  })

  // ========================================
  // PHASE 4: Candidate completes VCIO self-assessment (TODO)
  // ========================================
  it.skip('should complete VCIO self-assessment as candidate (status 40)', () => {
    // TODO: Implement VCIO test
    // This requires:
    // 1. Clear session and login as candidate
    // 2. Navigate to dashboard and find "VCIO-Einstufung" button
    // 3. Complete all Quality Dimension indicators with evidence
    // 4. Submit final VCIO assessment
    //
    // Note: This test needs a process that has completed PNA (status 30)
    // Consider using a fixture or running after the main workflow test
  })
})
