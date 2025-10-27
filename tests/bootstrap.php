<?php

//phpcs:disable

// Enable all errors
error_reporting(E_ALL);

// Load environment variables from .env file (for local development)
// This allows developers to set PUBLISH_KEY, SUBSCRIBE_KEY, etc. locally
// without committing sensitive keys to the repository.
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad(); // safeLoad() won't throw if .env is missing

    // Also populate putenv() for backward compatibility with existing tests that use getenv()
    // This way we don't need to modify PubNubTestCase.php
    foreach (['PUBLISH_KEY', 'SUBSCRIBE_KEY', 'SECRET_KEY', 'PUBLISH_PAM_KEY', 'SUBSCRIBE_PAM_KEY', 'SECRET_PAM_KEY', 'UUID_MOCK'] as $key) {
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
