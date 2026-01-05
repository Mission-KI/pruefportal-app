/// <reference types="cypress" />

interface MailpitMessageSummary {
  ID: string
  MessageID: string
  From: { Name: string; Address: string }
  To: Array<{ Name: string; Address: string }>
  Cc: Array<{ Name: string; Address: string }>
  Bcc: Array<{ Name: string; Address: string }>
  Subject: string
  Created: string
  Size: number
  Attachments: number
  Read: boolean
  Snippet: string
}

interface MailpitMessagesResponse {
  total: number
  unread: number
  count: number
  start: number
  messages: MailpitMessageSummary[]
}

interface MailpitMessage {
  ID: string
  MessageID: string
  From: { Name: string; Address: string }
  To: Array<{ Name: string; Address: string }>
  Subject: string
  Created: string
  Text: string
  HTML: string
  Size: number
  Attachments: any[]
}

interface ProjectData {
  title: string
  description: string
  processTitle: string
  processDescription?: string
  candidateName: string
  candidateEmail: string
  examinerName: string
  examinerEmail: string
}

interface UCDStepData {
  [fieldName: string]: string | number
}

declare global {
  namespace Cypress {
    interface Chainable {
      mailpitDeleteAll(): Chainable<void>
      mailpitGetAllMails(): Chainable<MailpitMessageSummary[]>
      mailpitGetMailsFor(email: string): Chainable<MailpitMessageSummary[]>
      mailpitGetLatestMailFor(email: string): Chainable<MailpitMessage>
      mailpitExtractActivationLink(email: string): Chainable<string>
      login(username: string, password: string): Chainable<void>
      createProject(data: ProjectData): Chainable<void>
      fillUCDStep(step: number, data: UCDStepData): Chainable<void>
      submitUCDStep(): Chainable<void>
      submitFinalUCDStep(): Chainable<void>
      fillPNAWithRisk(riskLevel: 'low' | 'high'): Chainable<void>
      fillVCIOForAllQDs(evidence: string, level: number): Chainable<void>
      completeVCIO(): Chainable<void>
      skipValidation(): Chainable<void>
      inviteExaminerAtValidation(name: string, email: string): Chainable<void>
      confirmExaminerQualification(): Chainable<void>
      validateVCIOAsExaminer(level: number): Chainable<void>
      completeExaminerValidation(): Chainable<void>
      acceptValidation(): Chainable<void>
    }
  }
}

Cypress.Commands.add('mailpitDeleteAll', () => {
  const mailpitUrl = Cypress.env('mailpitUrl') || Cypress.env('mailhogUrl')
  return cy.request({
    method: 'DELETE',
    url: `${mailpitUrl}/api/v1/messages`,
    failOnStatusCode: false,
  }).then(() => {
    cy.log('Mailpit: All messages deleted')
  })
})

Cypress.Commands.add('mailpitGetAllMails', () => {
  const mailpitUrl = Cypress.env('mailpitUrl') || Cypress.env('mailhogUrl')
  return cy.request<MailpitMessagesResponse>({
    method: 'GET',
    url: `${mailpitUrl}/api/v1/messages`,
  }).then((response) => {
    return response.body.messages || []
  })
})

Cypress.Commands.add('mailpitGetMailsFor', (email: string) => {
  const mailpitUrl = Cypress.env('mailpitUrl') || Cypress.env('mailhogUrl')
  return cy.request<MailpitMessagesResponse>({
    method: 'GET',
    url: `${mailpitUrl}/api/v1/search`,
    qs: {
      query: `to:${email}`,
    },
  }).then((response) => {
    return response.body.messages || []
  })
})

Cypress.Commands.add('mailpitGetLatestMailFor', (email: string) => {
  const mailpitUrl = Cypress.env('mailpitUrl') || Cypress.env('mailhogUrl')
  const maxRetries = 10
  const retryDelay = 1000

  const fetchEmail = (attempt: number): Cypress.Chainable<MailpitMessage> => {
    return cy.request<MailpitMessagesResponse>({
      method: 'GET',
      url: `${mailpitUrl}/api/v1/search`,
      qs: {
        query: `to:${email}`,
      },
    }).then((response) => {
      const messages = response.body.messages || []
      if (messages.length > 0) {
        // Fetch full message content
        return cy.request<MailpitMessage>({
          method: 'GET',
          url: `${mailpitUrl}/api/v1/message/${messages[0].ID}`,
        }).then((msgResponse) => msgResponse.body)
      }
      if (attempt < maxRetries) {
        cy.log(`No email yet for ${email}, retrying (${attempt}/${maxRetries})...`)
        return cy.wait(retryDelay).then(() => fetchEmail(attempt + 1))
      }
      throw new Error(`No emails found for ${email} after ${maxRetries} retries`)
    })
  }

  return fetchEmail(1)
})

Cypress.Commands.add('mailpitExtractActivationLink', (email: string) => {
  return cy.mailpitGetLatestMailFor(email).then((mail) => {
    // Mailpit provides Text and HTML separately
    let body = mail.Text || mail.HTML || ''

    // Handle quoted-printable encoding (=XX patterns and soft line breaks)
    if (body.includes('=3D') || body.includes('=\r\n') || body.includes('=\n')) {
      body = body
        .replace(/=\r?\n/g, '') // Remove soft line breaks
        .replace(/=([0-9A-F]{2})/gi, (_, hex) => String.fromCharCode(parseInt(hex, 16)))
    }

    // Try to find full URL with activate-account path
    const fullUrlMatch = body.match(/https?:\/\/[^\s"<>]+\/activate-account\/[a-zA-Z0-9]+/i)
    if (fullUrlMatch) {
      const url = new URL(fullUrlMatch[0])
      return url.pathname + url.search
    }

    // Try to find URL with /users/activate and token parameter
    const usersActivateMatch = body.match(/https?:\/\/[^\s"<>]+\/users\/activate[^\s"<>]*/i)
    if (usersActivateMatch) {
      const url = new URL(usersActivateMatch[0])
      return url.pathname + url.search
    }

    // Try path-only patterns
    const pathPatterns = [
      /\/activate-account\/[a-zA-Z0-9]+/i,
      /\/users\/activate\?token=[a-zA-Z0-9]+[^\s"]*/i,
      /\/users\/activate\/[a-zA-Z0-9]+/i
    ]

    for (const pattern of pathPatterns) {
      const match = body.match(pattern)
      if (match) {
        return match[0]
      }
    }

    // Log body for debugging if no match found
    cy.log('Email body (first 1000 chars):', body.substring(0, 1000))
    throw new Error('Activation link not found in email body. Check Cypress logs for email content.')
  })
})

Cypress.Commands.add('login', (username: string, password: string) => {
  cy.session([username, password], () => {
    cy.visit('/users/login')
    cy.get('#username').type(username, { delay: 5 })
    cy.get('#password').type(password, { delay: 5 })
    cy.get('button[type="submit"]').click()
    cy.url().should('not.include', '/users/login', { timeout: 5000 })
  })
})

Cypress.Commands.add('createProject', (data: ProjectData) => {
  cy.visit('/projects/add')

  cy.get('#title').type(data.title)
  cy.get('#description').type(data.description)

  cy.get('input[name="process_title"]').type(data.processTitle)
  if (data.processDescription) {
    cy.get('textarea[name="process_description"]').type(data.processDescription)
  }

  cy.get('#candidate-name').type(data.candidateName)
  cy.get('#candidate-email').type(data.candidateEmail)
  cy.get('#examiner-name').type(data.examinerName)
  cy.get('#examiner-email').type(data.examinerEmail)

  cy.get('#accept-disclaimer').check({ force: true })

  cy.get('#project-form button[type="submit"]').last().click()

  cy.url().should('include', '/projects/view/', { timeout: 15000 })
})

Cypress.Commands.add('fillUCDStep', (step: number, data: UCDStepData) => {
  cy.get(`[x-show="currentStep === ${step}"]`, { timeout: 10000 }).should('be.visible')

  Object.entries(data).forEach(([fieldName, value]) => {
    const selector = `[name="${fieldName}"]`

    cy.get(selector).then($el => {
      const tagName = $el.prop('tagName').toLowerCase()
      const inputType = $el.attr('type')

      if (tagName === 'textarea') {
        cy.get(selector).clear().type(String(value), { delay: 5 })
      } else if (tagName === 'select') {
        cy.get(selector).select(String(value))
      } else if (inputType === 'radio') {
        cy.get(`${selector}[value="${value}"]`).check({ force: true })
      } else {
        cy.get(selector).clear().type(String(value), { delay: 5 })
      }
    })
  })
})

Cypress.Commands.add('submitUCDStep', () => {
  cy.get('.form-navigation button[type="submit"]').filter(':visible').click()
  cy.wait(500)
})

Cypress.Commands.add('submitFinalUCDStep', () => {
  cy.get('#checkFinishedCompletely').check({ force: true })
  cy.get('button[type="submit"]').filter(':visible').last().click()
})

Cypress.Commands.add('fillPNAWithRisk', (riskLevel: 'low' | 'high') => {
  const fillCurrentPage = () => {
    cy.url().then((url) => {
      if (url.match(/protection-needs-analysis\/\d+-[A-Z]+/)) {
        cy.get('body').then(($body) => {
          const radioGroups = new Set<string>()
          $body.find('input[type="radio"]').each((_, radio) => {
            const name = radio.getAttribute('name')
            if (name) radioGroups.add(name)
          })

          if (radioGroups.size > 0) {
            radioGroups.forEach(name => {
              const radios = $body.find(`input[name="${name}"]`)
              const values = radios.map((_, r) => Cypress.$(r).val()).get().sort()
              const targetValue = riskLevel === 'low' ? values[0] : values[values.length - 1]
              cy.get(`input[name="${name}"][value="${targetValue}"]`).check({ force: true })
            })

            cy.contains('button', 'Nächster Schritt').click()
            cy.wait(1000)
            cy.then(() => fillCurrentPage())
          } else {
            const nextBtn = $body.find('button:contains("Nächster Schritt")')
            if (nextBtn.length > 0) {
              cy.contains('button', 'Nächster Schritt').click()
              cy.wait(1000)
              cy.then(() => fillCurrentPage())
            }
          }
        })
      } else if (url.match(/protection-needs-analysis\/\d+$/)) {
        cy.get('body').then(($body) => {
          const bewerten = $body.find('a:contains("Bewerten"), a:contains("Bewertung fortsetzen")')
          if (bewerten.length > 0) {
            cy.wrap(bewerten.first()).click()
            cy.wait(500)
            cy.then(() => fillCurrentPage())
          }
        })
      }
    })
  }

  cy.get('a:contains("Bewerten"), a:contains("Bewertung fortsetzen")').first().click()
  cy.wait(500)
  fillCurrentPage()

  cy.contains('a', 'Bewertung abschließen', { timeout: 15000 }).click()
  cy.url().should('include', '/criteria/complete/', { timeout: 15000 })

  cy.get('input[type="checkbox"][name="final_confirmation"]').check({ force: true })
  cy.get('.final-confirmation button[type="submit"]').click()
  cy.url().should('match', /(processes\/view|indicators)/, { timeout: 15000 })
})

Cypress.Commands.add('fillVCIOForAllQDs', (evidence: string, level: number) => {
  const fillQD = () => {
    cy.get('body').then(($body) => {
      const incompleteCard = $body.find('a:contains("Einstufen")')
      if (incompleteCard.length > 0) {
        cy.wrap(incompleteCard.first()).click()
        cy.url().should('include', '/indicators/', { timeout: 10000 })

        cy.get('#indicators-form', { timeout: 10000 }).then($form => {
          const radioGroups = new Set<string>()
          $form.find('input[type="radio"][name*="level_candidate"]').each((_, radio) => {
            const name = radio.getAttribute('name')
            if (name) radioGroups.add(name)
          })

          radioGroups.forEach(name => {
            cy.get(`input[name="${name}"][value="${level}"]`).check({ force: true })
          })

          $form.find('textarea[name*="evidence"]').each((_, textarea) => {
            const name = textarea.getAttribute('name')
            if (name) {
              cy.get(`textarea[name="${name}"]`).clear().type(evidence, { delay: 1 })
            }
          })
        })

        cy.get('#indicators-form button[type="submit"]').first().click()
        cy.wait(1000)

        cy.url().then((url) => {
          if (url.includes('/indicators/index/') || url.includes('/indicators/add/') || url.includes('/indicators/edit/')) {
            cy.then(() => fillQD())
          }
        })
      }
    })
  }

  fillQD()
})

Cypress.Commands.add('completeVCIO', () => {
  cy.contains('a', 'Einstufung überprüfen', { timeout: 15000 }).click()
  cy.url().should('include', '/indicators/complete/', { timeout: 10000 })
  cy.get('input[type="checkbox"][name="final_confirmation"]').check({ force: true })
  cy.get('.final-confirmation button[type="submit"]').click()
  cy.url().should('include', '/indicators/decide-validation/', { timeout: 15000 })
})

Cypress.Commands.add('skipValidation', () => {
  cy.get('[data-testid="skip-validation-btn"]').click()
  cy.url().should('match', /(\/processes\/view\/|^\/$|localhost:8070\/$)/, { timeout: 15000 })
})

Cypress.Commands.add('inviteExaminerAtValidation', (name: string, email: string) => {
  cy.get('[data-testid="examiner-name-input"]').first().type(name)
  cy.get('[data-testid="examiner-email-input"]').first().type(email)
  cy.get('#confirm-qualification').check({ force: true })
  cy.get('[data-testid="invite-examiner-confirm-btn"]').click()
  cy.url().should('match', /(processes\/view|indicators)/, { timeout: 15000 })
})

Cypress.Commands.add('confirmExaminerQualification', () => {
  cy.get('#confirm-qualification').check({ force: true })
  cy.get('[data-testid="confirm-qualification-btn"]').click()
  cy.url().should('match', /(processes\/view|indicators)/, { timeout: 15000 })
})

Cypress.Commands.add('validateVCIOAsExaminer', (level: number) => {
  cy.url().should('include', '/indicators/validation/', { timeout: 10000 })

  const validateQD = () => {
    cy.get('body').then(($body) => {
      const validateBtn = $body.find('a:contains("Validieren")')
      if (validateBtn.length > 0) {
        cy.wrap(validateBtn.first()).click()
        cy.url().should('include', '/indicators/validate/', { timeout: 10000 })

        cy.get('form').then($form => {
          const radioGroups = new Set<string>()
          $form.find('input[type="radio"][name*="level_examiner"]').each((_, radio) => {
            const name = radio.getAttribute('name')
            if (name) radioGroups.add(name)
          })

          radioGroups.forEach(name => {
            cy.get(`input[name="${name}"][value="${level}"]`).check({ force: true })
          })
        })

        cy.get('[data-testid="validation-next-step"]').click()
        cy.wait(1000)
        cy.then(() => validateQD())
      }
    })
  }

  validateQD()
})

Cypress.Commands.add('completeExaminerValidation', () => {
  cy.contains('a', 'Validierung abschließen', { timeout: 15000 }).click()
  cy.get('input[type="checkbox"][name="final_confirmation"]').check({ force: true })
  cy.get('[data-testid="complete-validation-btn"]').click()
  cy.url().should('match', /(\/processes\/view\/|^\/$|localhost:8070\/$)/, { timeout: 15000 })
})

Cypress.Commands.add('acceptValidation', () => {
  cy.url().should('match', /(accept-validation|processes\/view)/, { timeout: 10000 })
  cy.get('body').then($body => {
    if ($body.find('input[type="checkbox"][name="final_confirmation"]').length > 0) {
      cy.get('input[type="checkbox"][name="final_confirmation"]').check({ force: true })
      cy.get('[data-testid="accept-validation-btn"]').click()
    }
  })
  cy.url().should('match', /(\/processes\/(view|total-result)\/|^\/$|localhost:8070\/$)/, { timeout: 15000 })
})
