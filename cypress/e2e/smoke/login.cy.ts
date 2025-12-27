describe('Login - Smoke Test', () => {
  it('should successfully log in with valid credentials', () => {
    const validUsername = 'test-user@example.com'
    const validPassword = 'admin123'

    cy.visit('/users/login')

    cy.get('#username').type(validUsername)
    cy.get('#password').type(validPassword)

    cy.get('button[type="submit"]').click()

    cy.url().should('not.include', '/users/login', { timeout: 10000 })
    cy.get('nav', { timeout: 10000 }).should('be.visible')
  })
})
