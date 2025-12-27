<?php

use function Cake\Core\env;

return [
    'noReplyFromEmail' => env('NO_REPLY_FROM_EMAIL', 'no-reply@localhost'),
    'statuses' => [
        0 => 'Noch nicht gestartet',
        10 => 'Anwendungsfall',
        15 => 'Anwendungsfall Revision',
        20 => 'Schutzbedarf',
        30 => 'VCIO-Einstufung',
        35 => 'Validierungsentscheidung',
        40 => 'Validierung',
        50 => 'Bewertung',
        60 => 'Abgeschlossen',
    ],

    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', 'your-secret-salt-key-change-this-in-production'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'persistent' => false,
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', 5432),
            'username' => env('DB_USER', 'mission_ki_user'),
            'password' => env('DB_PASSWORD', 'mission_ki_password'),
            'database' => env('DB_NAME', 'mission_ki_db'),
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,
            'url' => env('DATABASE_URL', null),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'persistent' => false,
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', 5432),
            'username' => env('DB_USER', 'mission_ki_user'),
            'password' => env('DB_PASSWORD', 'mission_ki_password'),
            'database' => env('DB_NAME', 'mission_ki_db') . '_test',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'url' => env('DATABASE_TEST_URL', null),
        ],
    ],

    /*
     * Email configuration.
     *
     * Supports two drivers:
     * - 'ses' (default): AWS SES for production deployments
     * - 'smtp': SMTP for self-hosted deployments (e.g., Mailpit for local dev)
     *
     * Set EMAIL_DRIVER environment variable to switch between drivers.
     */
    'EmailTransport' => [
        'default' => match (env('EMAIL_DRIVER', 'ses')) {
            'smtp' => [
                'className' => \Cake\Mailer\Transport\SmtpTransport::class,
                'host' => env('SMTP_HOST', 'localhost'),
                'port' => (int)env('SMTP_PORT', 1025),
                'username' => env('SMTP_USERNAME', null),
                'password' => env('SMTP_PASSWORD', null),
                'tls' => filter_var(env('SMTP_TLS', false), FILTER_VALIDATE_BOOLEAN),
                'timeout' => 30,
            ],
            'ses' => [
                'className' => 'AwsSes',
            ],
            default => [
                'className' => 'AwsSes',
            ],
        },
        'debug' => [
            'className' => 'Debug',
        ],
    ],
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => [
                env('EMAIL_SENDER_EMAIL', env('SES_SENDER_EMAIL', 'no-reply@example.com')) =>
                    env('EMAIL_SENDER_NAME', env('SES_SENDER_NAME', 'Pruefportal'))
            ],
        ],
    ],
];
