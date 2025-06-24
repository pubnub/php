<?php

namespace Tests\Examples;

use PHPUnit\Framework\TestCase;

class MessagePersistanceTest extends TestCase
{
    public function testExamples(): void
    {
        // Check if the file exists and is readable
        $fileName = __DIR__ . '/../../examples/MessagePersistance.php';
        $this->assertFileExists($fileName);
        $this->assertFileIsReadable($fileName);

        // Let's make sure that the example has properly set up the environment
        if (!getenv('SUBSCRIBE_KEY')) {
            putenv('SUBSCRIBE_KEY=demo');
        }
        if (!getenv('PUBLISH_KEY')) {
            putenv('PUBLISH_KEY=demo');
        }

        ob_start();
        // Include the examples file
        require_once $fileName;
        $output = ob_get_clean();

        $this->assertStringContainsString('🎉 All Message Persistence demos completed!', $output);
        $this->assertStringNotContainsString('Exception', $output);
    }
}
