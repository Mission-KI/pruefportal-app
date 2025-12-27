describe('User Registration - Smoke Test', () => {
  const testUser = {
    fullName: 'Test Registration User',
    email: `test-register-${Date.now()}@example.com`,
    company: 'Test Company GmbH',
    password: 'TestPassword123!'
  }

  beforeEach(() => {
    cy.mailpitDeleteAll()
  })

  it('should complete the full registration flow with email verification', () => {
    cy.visit('/users/register')

    cy.get('#full_name').type(testUser.fullName)
    cy.get('#username').type(testUser.email)
    cy.get('#company').type(testUser.company)
    cy.get('#password').type(testUser.password)
    cy.get('#accept-beta-disclaimer').check({ force: true })

    cy.get('button[type="submit"]').should('not.be.disabled')
    cy.get('button[type="submit"]').click()

    cy.url().should('not.include', '/users/register', { timeout: 10000 })
    cy.get('.flash-message, [class*="alert"], [class*="success"]', { timeout: 5000 })
      .should('be.visible')

    cy.mailpitExtractActivationLink(testUser.email).then((activationPath) => {
      cy.visit(activationPath)
    })

    cy.url().should('include', '/users/login', { timeout: 10000 })
    cy.get('.flash-message, [class*="alert"], [class*="success"]', { timeout: 5000 })
      .should('be.visible')

    cy.get('#username').type(testUser.email)
    cy.get('#password').type(testUser.password)
    cy.get('button[type="submit"]').click()

    cy.url().should('not.include', '/users/login', { timeout: 10000 })
    cy.get('nav', { timeout: 10000 }).should('be.visible')
  })

  it('should show validation errors for invalid form submission', () => {
    cy.visit('/users/register')

    cy.get('button[type="submit"]').should('be.disabled')

    cy.get('#full_name').type('T')
    cy.get('#username').type('invalid-email')
    cy.get('#password').type('weak')

    cy.get('button[type="submit"]').should('be.disabled')
  })

  it('should send confirmation email after registration', () => {
    const uniqueEmail = `email-test-${Date.now()}@example.com`

    cy.visit('/users/register')

    cy.get('#full_name').type('Email Test User')
    cy.get('#username').type(uniqueEmail)
    cy.get('#company').type('Email Test Company')
    cy.get('#password').type('TestPassword123!')
    cy.get('#accept-beta-disclaimer').check({ force: true })

    cy.get('button[type="submit"]').click()

    cy.url().should('not.include', '/users/register', { timeout: 10000 })

    cy.mailpitGetMailsFor(uniqueEmail).then((mails) => {
      expect(mails.length).to.be.greaterThan(0)

      const mail = mails[0]
      // Mailpit returns Snippet in summary, check Subject for confirmation email
      expect(mail.Subject).to.include('MISSION KI')
    })
  })

  it('should reject activation with invalid token', () => {
    cy.visit('/activate-account/invalid-token-12345', { failOnStatusCode: false })

    cy.get('body').then(($body) => {
      const text = $body.text()
      const hasErrorIndicator =
        text.includes('NotFoundException') ||
        text.includes('Token') ||
        text.includes('404') ||
        text.includes('Not Found')
      expect(hasErrorIndicator).to.be.true
    })
  })
})
