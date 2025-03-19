<?php

//phpcs:disable

// Enable all errors
error_reporting(E_ALL);

require_once(__DIR__ . '/PubNubTestCase.php');

if (!class_exists('Thread')) {
    class Thread
    {
        public function start(): void
        {
        }
    }
}
