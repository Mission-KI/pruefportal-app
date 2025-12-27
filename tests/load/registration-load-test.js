#!/usr/bin/env node

/**
 * Registration Load Test
 *
 * Simulates concurrent user registrations to validate system capacity
 *
 * Usage:
 *   node registration-load-test.js --env=test --users=50
 */

const fs = require('fs');
const path = require('path');
const config = require('./config');
const { generateUsers, generateCleanupSQL } = require('./lib/user-generator');
const { registerUser } = require('./lib/registration-worker');

// Parse command line arguments
function parseArgs() {
  const args = process.argv.slice(2);
  const options = {
    env: config.defaults.environment,
    users: config.defaults.users,
    headless: config.defaults.headless,
    verbose: false
  };

  for (const arg of args) {
    if (arg.startsWith('--env=')) {
      options.env = arg.split('=')[1];
    } else if (arg.startsWith('--users=')) {
      options.users = parseInt(arg.split('=')[1], 10);
    } else if (arg === '--no-headless') {
      options.headless = false;
    } else if (arg === '--verbose') {
      options.verbose = true;
    }
  }

  return options;
}

// Calculate statistics from results
function calculateStats(results) {
  const successful = results.filter(r => r.success);
  const failed = results.filter(r => !r.success);
  const durations = results.map(r => r.duration);

  durations.sort((a, b) => a - b);

  const avg = durations.reduce((sum, d) => sum + d, 0) / durations.length;
  const min = durations[0];
  const max = durations[durations.length - 1];
  const p50 = durations[Math.floor(durations.length * 0.5)];
  const p95 = durations[Math.floor(durations.length * 0.95)];
  const p99 = durations[Math.floor(durations.length * 0.99)];

  return {
    total: results.length,
    successful: successful.length,
    failed: failed.length,
    successRate: ((successful.length / results.length) * 100).toFixed(1),
    durations: {
      avg: Math.round(avg),
      min,
      max,
      p50,
      p95,
      p99
    },
    failures: failed.map(f => ({ user: f.user, error: f.error }))
  };
}

// Save results to JSON file
function saveResults(testConfig, results, stats, timestamp) {
  const resultsDir = path.join(__dirname, 'results');

  if (!fs.existsSync(resultsDir)) {
    fs.mkdirSync(resultsDir, { recursive: true });
  }

  const filename = `load-test-${timestamp}.json`;
  const filepath = path.join(resultsDir, filename);

  const data = {
    testConfig,
    timestamp,
    summary: stats,
    results
  };

  fs.writeFileSync(filepath, JSON.stringify(data, null, 2));
  return filepath;
}

// Print summary report
function printSummary(testConfig, stats, resultsFile, cleanupFile) {
  const { baseUrl, name: envName } = testConfig.environment;
  const { total, successful, failed, successRate, durations, failures } = stats;

  console.log('\n' + '═'.repeat(60));
  console.log('  Registration Load Test Results');
  console.log('═'.repeat(60));
  console.log(`\nEnvironment:  ${envName}`);
  console.log(`Target URL:   ${baseUrl}`);
  console.log(`Total Users:  ${total}`);
  console.log(`\nResults:`);
  console.log(`  ✓ Successful:  ${successful} (${successRate}%)`);
  console.log(`  ✗ Failed:      ${failed} (${(100 - parseFloat(successRate)).toFixed(1)}%)`);
  console.log(`\nPerformance:`);
  console.log(`  Average:  ${(durations.avg / 1000).toFixed(1)}s`);
  console.log(`  Min:      ${(durations.min / 1000).toFixed(1)}s`);
  console.log(`  Max:      ${(durations.max / 1000).toFixed(1)}s`);
  console.log(`  p50:      ${(durations.p50 / 1000).toFixed(1)}s`);
  console.log(`  p95:      ${(durations.p95 / 1000).toFixed(1)}s`);
  console.log(`  p99:      ${(durations.p99 / 1000).toFixed(1)}s`);

  if (failures.length > 0) {
    console.log(`\nFailed Registrations:`);
    failures.forEach(f => {
      console.log(`  - ${f.user}`);
      console.log(`    Error: ${f.error}`);
    });
  }

  console.log(`\nDetails saved to: ${path.relative(process.cwd(), resultsFile)}`);
  console.log(`Cleanup script:   ${path.relative(process.cwd(), cleanupFile)}`);
  console.log('═'.repeat(60) + '\n');
}

// Main execution
async function main() {
  const options = parseArgs();

  // Validate environment
  if (!config.environments[options.env]) {
    console.error(`Error: Invalid environment '${options.env}'`);
    console.error(`Available: ${Object.keys(config.environments).join(', ')}`);
    process.exit(1);
  }

  const environment = config.environments[options.env];
  const baseUrl = environment.baseUrl;
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-').split('T').join('-').split('-').slice(0, 6).join('-');

  console.log('\n' + '═'.repeat(60));
  console.log('  Starting Registration Load Test');
  console.log('═'.repeat(60));
  console.log(`\nEnvironment: ${environment.name}`);
  console.log(`Target:      ${baseUrl}`);
  console.log(`Users:       ${options.users}`);
  console.log(`Headless:    ${options.headless}`);
  console.log(`\nGenerating test users...`);

  // Generate test users
  const users = generateUsers(options.users);
  console.log(`✓ Generated ${users.length} unique test users`);

  // Generate cleanup SQL
  const cleanupSQL = generateCleanupSQL(users, timestamp);
  const cleanupFile = path.join(__dirname, 'results', `cleanup-users-${timestamp}.sql`);

  if (!fs.existsSync(path.dirname(cleanupFile))) {
    fs.mkdirSync(path.dirname(cleanupFile), { recursive: true });
  }

  fs.writeFileSync(cleanupFile, cleanupSQL);
  console.log(`✓ Cleanup script prepared\n`);

  // Launch concurrent registrations
  console.log(`Launching ${users.length} concurrent browser instances...\n`);

  const startTime = Date.now();
  const workerOptions = {
    headless: options.headless,
    timeout: config.defaults.timeout,
    verbose: options.verbose
  };

  // Use Promise.allSettled to ensure all attempts complete
  const promises = users.map((userData, index) => {
    if (!options.verbose) {
      // Show progress indicator
      process.stdout.write(`\rStarted: ${index + 1}/${users.length}`);
    }
    return registerUser(userData, baseUrl, workerOptions);
  });

  const results = await Promise.allSettled(promises);
  const totalDuration = Date.now() - startTime;

  if (!options.verbose) {
    process.stdout.write('\n');
  }

  // Extract actual results (unwrap Promise.allSettled)
  const actualResults = results.map(r =>
    r.status === 'fulfilled' ? r.value : {
      success: false,
      user: 'unknown',
      duration: 0,
      error: r.reason?.message || 'Promise rejected'
    }
  );

  console.log(`\n✓ All registration attempts completed in ${(totalDuration / 1000).toFixed(1)}s\n`);

  // Calculate statistics
  const stats = calculateStats(actualResults);

  // Save results
  const testConfig = {
    environment: {
      name: environment.name,
      baseUrl
    },
    users: options.users,
    headless: options.headless,
    totalDuration: `${(totalDuration / 1000).toFixed(1)}s`
  };

  const resultsFile = saveResults(testConfig, actualResults, stats, timestamp);

  // Print summary
  printSummary(testConfig, stats, resultsFile, cleanupFile);

  // Exit with error code if any failures
  process.exit(stats.failed > 0 ? 1 : 0);
}

// Run main and handle errors
main().catch(error => {
  console.error('\nFatal error:', error.message);
  console.error(error.stack);
  process.exit(1);
});
