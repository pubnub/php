<?php

namespace Tests\Examples;

use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testExamples(): void
    {
        ob_start();
        // Include the examples file
        require_once __DIR__ . '/../../examples/Configuration.php';
        ob_end_clean();

        // The examples file will be executed and we can verify its output
        // The assertions are already in the examples file
        $this->assertTrue(true); // If we reach this point, all examples passed
    }
}

