/**
 * Generates unique test user data to avoid conflicts
 * during concurrent registration testing
 */

const config = require('../config');

/**
 * Generate a unique test user
 * @param {number} index - User index (0-based)
 * @returns {Object} User data object
 */
function generateUser(index) {
  const timestamp = Date.now();
  const randomId = Math.random().toString(36).substring(2, 8);
  const salutations = ['ms', 'mr', 'diverse'];

  return {
    salutation: salutations[index % salutations.length],
    full_name: `Load Test User ${index + 1}`,
    username: `loadtest-${timestamp}-${randomId}-${index}@example.com`,
    company: `Test Organization ${index + 1}`,
    password: config.testPassword
  };
}

/**
 * Generate N unique test users
 * @param {number} count - Number of users to generate
 * @returns {Array<Object>} Array of user data objects
 */
function generateUsers(count) {
  if (count <= 0 || !Number.isInteger(count)) {
    throw new Error('User count must be a positive integer');
  }

  return Array.from({ length: count }, (_, index) => generateUser(index));
}

/**
 * Generate SQL cleanup script for test users
 * @param {Array<Object>} users - Array of user objects
 * @param {string} timestamp - Timestamp for the test run
 * @returns {string} SQL cleanup script
 */
function generateCleanupSQL(users, timestamp) {
  const emails = users.map(u => `  '${u.username}'`).join(',\n');

  return `-- Cleanup script for load test: ${timestamp}
-- Total users: ${users.length}

-- Delete test users
DELETE FROM users
WHERE username IN (
${emails}
);

-- Verify deletion
SELECT COUNT(*) as deleted_count
FROM users
WHERE username LIKE 'loadtest-%@example.com';
`;
}

module.exports = {
  generateUser,
  generateUsers,
  generateCleanupSQL
};
