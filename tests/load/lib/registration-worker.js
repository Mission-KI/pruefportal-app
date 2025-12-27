/**
 * Registration worker - handles individual user registration via Puppeteer
 */

const puppeteer = require('puppeteer');

/**
 * Register a single user
 * @param {Object} userData - User data object
 * @param {string} baseUrl - Base URL of the application
 * @param {Object} options - Worker options
 * @returns {Promise<Object>} Result object with success status, duration, error
 */
async function registerUser(userData, baseUrl, options = {}) {
  const {
    headless = true,
    timeout = 30000,
    verbose = false
  } = options;

  const browser = await puppeteer.launch({
    headless,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  page.setDefaultTimeout(timeout);

  const startTime = Date.now();
  const registrationUrl = `${baseUrl}/users/register`;

  try {
    if (verbose) {
      console.log(`[${userData.username}] Navigating to ${registrationUrl}`);
    }

    // Navigate to registration page
    await page.goto(registrationUrl, { waitUntil: 'networkidle2' });

    // Extract CSRF token
    const csrfToken = await page.$eval(
      'input[name="_csrfToken"]',
      el => el.value
    );

    if (!csrfToken) {
      throw new Error('CSRF token not found on page');
    }

    if (verbose) {
      console.log(`[${userData.username}] CSRF token extracted`);
    }

    // Fill form fields
    await page.select('select[name="salutation"]', userData.salutation);
    await page.type('input[name="full_name"]', userData.full_name);
    await page.type('input[name="username"]', userData.username);

    if (userData.company) {
      await page.type('input[name="company"]', userData.company);
    }

    await page.type('input[name="password"]', userData.password);

    if (verbose) {
      console.log(`[${userData.username}] Form filled, submitting...`);
    }

    // Submit form and wait for navigation
    await Promise.all([
      page.click('button[type="submit"]'),
      page.waitForNavigation({ waitUntil: 'networkidle2' })
    ]);

    // Check success - if we're no longer on /users/register, registration succeeded
    const currentUrl = page.url();
    const success = !currentUrl.includes('/users/register');

    const duration = Date.now() - startTime;

    if (verbose) {
      console.log(`[${userData.username}] ${success ? 'SUCCESS' : 'FAILED'} (${duration}ms)`);
    }

    return {
      success,
      user: userData.username,
      duration,
      error: success ? null : 'Still on registration page after submission'
    };

  } catch (error) {
    const duration = Date.now() - startTime;

    if (verbose) {
      console.error(`[${userData.username}] ERROR: ${error.message}`);
    }

    return {
      success: false,
      user: userData.username,
      duration,
      error: error.message
    };

  } finally {
    await browser.close();
  }
}

module.exports = {
  registerUser
};
