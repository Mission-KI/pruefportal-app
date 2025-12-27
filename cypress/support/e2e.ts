import './commands'

/**
 * This is needed to handle basic auth in remote testing environments.
 */
beforeEach(() => {
  if (Cypress.env('testEnvironment') === 'remote-testing') {
    cy.intercept('*', (req) => {
      req.headers['authorization'] = 'Basic ' + btoa('admin:admin123')
    })
  }
})

afterEach(() => { })

// Handle uncaught exceptions to prevent tests from failing
Cypress.on('uncaught:exception', (err, runnable) => {
  // returning false here prevents Cypress from failing the test on uncaught exceptions.
  return false
})
