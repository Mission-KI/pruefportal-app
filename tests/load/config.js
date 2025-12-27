/**
 * Environment configuration for load testing
 */

module.exports = {
  environments: {
    local: {
      baseUrl: 'http://localhost:8070',
      name: 'Local Development'
    },
    test: {
      baseUrl: 'https://test.pruefportal.mission-ki.de',
      name: 'Test Server'
    },
    prod: {
      baseUrl: 'https://pruefportal.mission-ki.de',
      name: 'Production'
    }
  },

  paths: {
    registration: '/users/register'
  },

  defaults: {
    environment: 'test',
    users: 10,
    headless: true,
    timeout: 30000  // 30 seconds per registration attempt
  },

  // Password that meets validation requirements:
  // Min 8 chars, at least 1 number, at least 1 special char
  testPassword: 'LoadTest2024!@'
};
