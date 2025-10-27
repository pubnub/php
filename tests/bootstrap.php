<?php

//phpcs:disable

// Enable all errors
error_reporting(E_ALL);

// Load environment variables from .env.dev file (for local development)
// This allows developers to set PUBLISH_KEY, SUBSCRIBE_KEY, etc. locally
$envFile = dirname(__DIR__) . '/.env.dev';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad(); // safeLoad() won't throw if .env.dev is missing

    // Dotenv only populates $_ENV and $_SERVER, but tests use getenv()
    // Manually populate putenv() so getenv() works throughout the test suite
    $requiredEnvKeys = [
        'PUBLISH_KEY',
        'PUBLISH_PAM_KEY',
        'SECRET_KEY',
        'SECRET_PAM_KEY',
        'SUBSCRIBE_KEY',
        'SUBSCRIBE_PAM_KEY',
        'UUID_MOCK',
    ];

    foreach ($requiredEnvKeys as $key) {
        if (isset($_ENV[$key])) {
            putenv("$key={$_ENV[$key]}");
        }
    }
}

require_once(__DIR__ . '/PubNubTestCase.php');

if (!class_exists('Thread')) {
    class Thread
    {
        public function start(): void
        {
        }
    }
}
