import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'https://test.pruefportal.mission-ki.de',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: true,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 15000,
    requestTimeout: 15000,
    responseTimeout: 15000,
    pageLoadTimeout: 30000,
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.ts',
    fixturesFolder: 'cypress/fixtures',
    screenshotsFolder: 'cypress/screenshots-testing',
    videosFolder: 'cypress/videos-testing',
    downloadsFolder: 'cypress/downloads-testing',
    retries: {
      runMode: 2,
      openMode: 0
    },
    env: {
      // Environment-specific variables can be set here
      testEnvironment: 'remote-testing'
    }
  },
})