describe('Project Creation', () => {
  let testData: any

  before(() => {
    cy.fixture('test-data').then((data) => {
      testData = data
    })
  })

  beforeEach(() => {
    cy.login(testData.testUser.email, testData.testUser.password)
  })

  it('should navigate to project creation page', () => {
    cy.visit('/projects/add')
    cy.get('#project-form').should('be.visible')
    cy.get('#title').should('be.visible')
  })

  it('should have submit button disabled initially', () => {
    cy.visit('/projects/add')
    cy.get('#project-form button[type="submit"]').last().should('be.disabled')
  })

  it('should create a new project with process', () => {
    const uniqueId = Date.now()

    cy.visit('/projects/add')

    cy.get('#title').type(`${testData.project.title} - ${uniqueId}`)
    cy.get('#description').type(testData.project.description)

    cy.get('input[name="process_title"]').type(testData.project.processTitle)
    cy.get('textarea[name="process_description"]').type(testData.project.processDescription)

    // Candidate is automatically the logged-in user (no fields to fill)

    // Add an examiner by clicking the button first
    cy.contains('button', 'PrüferIn hinzufügen').click()

    // Fill examiner fields (dynamically generated)
    cy.get('#additional-participant-name-0').type(testData.participants.examiner.name)
    cy.get('#additional-participant-email-0').type(`examiner-${uniqueId}@example.com`)

    // Accept disclaimer if it exists
    cy.get('body').then($body => {
      if ($body.find('#accept-disclaimer').length > 0) {
        cy.get('#accept-disclaimer').check({ force: true })
      }
    })

    // Wait for form to be valid
    cy.get('#project-form button[type="submit"]').last().should('not.be.disabled')
    cy.get('#project-form button[type="submit"]').last().click()

    // After creating a project, the app redirects to process start page
    // Wait for URL to change from /projects/add
    cy.url().should('not.include', '/projects/add', { timeout: 15000 })

    // Verify we're on a process-related page (start or view)
    cy.url().should('match', /\/process/, { timeout: 5000 })
  })
})
