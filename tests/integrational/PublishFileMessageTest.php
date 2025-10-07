<?php

namespace PubNubTests\integrational;

use PubNubTestCase;

final class PublishFileMessageTest extends PubNubTestCase
{
    protected string $channel = "publish-file-message-test";
    protected string $testFilePath;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary test file
        $this->testFilePath = sys_get_temp_dir() . '/test_file_' . uniqid() . '.txt';
        file_put_contents($this->testFilePath, 'Test file content for publish file message');
    }

    public function tearDown(): void
    {
        // Clean up temporary file
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        
        // Clean up uploaded files
        try {
            $listResponse = $this->pubnub->listFiles()->channel($this->channel)->sync();
            foreach ($listResponse->getData() as $file) {
                $this->pubnub->deleteFile()
                    ->channel($this->channel)
                    ->fileId($file['id'])
                    ->fileName($file['name'])
                    ->sync();
            }
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
        
        parent::tearDown();
    }

    public function testPublishFileMessageWithBasicMessage(): void
    {
        // First upload a file to get file ID
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("Initial file upload")
            ->sync();
        
        fclose($file);
        
        $this->assertNotEmpty($uploadResponse->getFileId());
        
        // Now publish a file message notification
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("File notification message")
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithMetadata(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File with metadata")
            ->sync();
        
        fclose($file);
        
        // Publish file message with metadata
        $metadata = [
            "author" => "test-user",
            "fileType" => "text",
            "importance" => "high"
        ];
        
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("File with metadata notification")
            ->meta($metadata)
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithCustomMessageType(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish file message with custom message type
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Custom type notification")
            ->customMessageType("file_notification")
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithTTL(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish file message with TTL
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Message with TTL")
            ->ttl(60) // 60 minutes
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithShouldStore(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish file message with shouldStore flag
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Stored message")
            ->shouldStore(true)
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithAllOptions(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish file message with all options
        $metadata = [
            "category" => "documents",
            "tags" => ["important", "urgent"]
        ];
        
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Complete file notification")
            ->meta($metadata)
            ->customMessageType("complete_notification")
            ->ttl(120)
            ->shouldStore(true)
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithEncryption(): void
    {
        // Use encrypted pubnub instance
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub_enc->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("Encrypted file upload")
            ->sync();
        
        fclose($file);
        
        // Publish encrypted file message
        $response = $this->pubnub_enc->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Encrypted file notification")
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageWithComplexMessage(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish file message with complex message structure
        $complexMessage = [
            "type" => "file_uploaded",
            "details" => [
                "uploader" => "test-user",
                "timestamp" => time(),
                "description" => "Important document"
            ],
            "actions" => ["review", "approve", "archive"]
        ];
        
        $response = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message($complexMessage)
            ->sync();
        
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimetoken());
    }

    public function testPublishFileMessageMultipleTimes(): void
    {
        // Upload a file first
        $file = fopen($this->testFilePath, "r");
        $fileName = basename($this->testFilePath);
        
        $uploadResponse = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName($fileName)
            ->message("File upload")
            ->sync();
        
        fclose($file);
        
        // Publish multiple file messages for the same file
        $response1 = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("First notification")
            ->sync();
        
        $response2 = $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId($uploadResponse->getFileId())
            ->fileName($fileName)
            ->message("Second notification")
            ->sync();
        
        $this->assertNotEmpty($response1->getTimetoken());
        $this->assertNotEmpty($response2->getTimetoken());
        $this->assertNotEquals($response1->getTimetoken(), $response2->getTimetoken());
    }

    public function testPublishFileMessageWithInvalidFileId(): void
    {
        $this->expectException(\PubNub\Exceptions\PubNubServerException::class);
        
        // Try to publish with non-existent file ID
        $this->pubnub->publishFileMessage()
            ->channel($this->channel)
            ->fileId('invalid-file-id-12345')
            ->fileName('nonexistent.txt')
            ->message("This should fail")
            ->sync();
    }

    public function testPublishFileMessageWithEmptyChannel(): void
    {
        $this->expectException(\PubNub\Exceptions\PubNubValidationException::class);
        
        $this->pubnub->publishFileMessage()
            ->channel('')
            ->fileId('some-file-id')
            ->fileName('test.txt')
            ->message("Empty channel")
            ->sync();
    }
}
