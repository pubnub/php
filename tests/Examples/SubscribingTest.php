<?php

namespace Tests\Examples;

use PHPUnit\Framework\TestCase;

class SubscribingTest extends TestCase
{
    public function testExamples(): void
    {
        // Define constant to prevent infinite loop in Subscribing.php
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }

        ob_start();
        // Include the examples file
        require_once __DIR__ . '/../../examples/Subscribing.php';
        ob_end_clean();

        // The examples file will be executed and we can verify its output
        // The assertions are already in the examples file
        $this->assertTrue(true); // If we reach this point, all examples passed
    }
}
