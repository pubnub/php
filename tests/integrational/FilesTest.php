<?php

namespace PubNubTests\integrational;

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStubClient;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubConnectionException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;

/** @phpstan-consistent-constructor */
final class FilesTest extends PubNubTestCase
{
    protected string $channel = "files-test";
    protected string $textFilePath = __DIR__ . '/assets/spam.spam';
    protected string $binaryFilePath = __DIR__ . '/assets/pn.gif';
    protected ?string $textFileId;
    protected ?string $binaryFileId;

    protected function cleanupFiles(): void
    {
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
    }

    public static function tearDownAfterClass(): void
    {

        $instance = new static();
        $instance->setUp();
        $instance->cleanupFiles();
        parent::tearDownAfterClass();
    }

    public function testEmptyFileList(): void
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(0, $response->getData());
    }

    public function testSendTextFile(): void
    {
        $file = fopen($this->textFilePath, "r");

        $response = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This is the requested text file")
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());
    }

    public function testSendBinaryFile(): void
    {
        $file = fopen($this->binaryFilePath, "r");

        $response = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->binaryFilePath))
            ->message("This is the requested binary file")
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());
    }

    public function testNonEmptyFileList(): void
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(2, $response->getData());
        $this->textFileId = $response->getData()[0]['id'];
    }

    public function testGetDownloadUrls(): void
    {
        $listFilesResponse = $this->pubnub->listFiles()->channel($this->channel)->sync();
        foreach ($listFilesResponse->getData() as $file) {
            $response = $this->pubnub->getFileDownloadUrl()
                ->channel($this->channel)
                ->fileId($file['id'])
                ->fileName($file['name'])
                ->sync();
            $this->assertNotEmpty($response);
            $this->assertNotEmpty($response->getFileUrl());
        }
    }

    public function testDownloadFiles(): void
    {
        $listFilesResponse = $this->pubnub->listFiles()->channel($this->channel)->sync();
        foreach ($listFilesResponse->getData() as $file) {
            $response = $this->pubnub->downloadFile()
                ->channel($this->channel)
                ->fileId($file['id'])
                ->fileName($file['name'])
                ->sync();
            $this->assertNotEmpty($response);
            if ($file['name'] == basename($this->binaryFilePath)) {
                $this->assertEquals(file_get_contents($this->binaryFilePath), $response->getFileContent());
            } else {
                $this->assertEquals(file_get_contents($this->textFilePath), $response->getFileContent());
            }
        }
    }

    public function testDeleteAllFiles(): void
    {
        $listFilesResponse = $this->pubnub->listFiles()->channel($this->channel)->sync();
        foreach ($listFilesResponse->getData() as $file) {
            $response = $this->pubnub->deleteFile()
                ->channel($this->channel)
                ->fileId($file['id'])
                ->fileName($file['name'])
                ->sync();
            $this->assertNotEmpty($response);
            $this->assertEquals(200, $response->getStatus(), "Unexpected status value");
        }
    }

    public function testEmptyFileListAfterDelete(): void
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(0, $response->getData());
    }

    public function testThrowErrorOnMalformedResponse(): void
    {
        $this->expectException(PubNubResponseParsingException::class);
        $client = new PsrStubClient();
        $config = new PNConfiguration();
        $config->setPublishKey("demo");
        $config->setSubscribeKey("demo");
        $config->setUserId("test");
        $pubnub = new PubNub($config);
        $pubnub->setClient($client);
        $client->stubFor("/v1/files/demo/channels/files-test/files/none/none")
            ->withQuery(["pnsdk" => $pubnub->getSdkFullName(), "uuid" => "test"])
            ->setResponseBody('{}')
            ->setResponseStatus(307)
            ->setResponseHeaders(['Location' => '']);
        $pubnub->getFileDownloadUrl()->channel($this->channel)->fileId('none')->fileName('none')->sync();
    }

    public function testThrowErrorOnNoFileFound(): void
    {
        $this->expectException(PubNubServerException::class);
        $this->pubnub->downloadFile()->channel($this->channel)->fileId('-')->fileName('-')->sync();
    }

    public function testFileUploadWithEncryption(): void
    {
        // Enable encryption in configuration
        $pubnub = new PubNub($this->config_enc);
        $file = fopen($this->textFilePath, "r");

        $response = $pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This is an encrypted file")
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());

        // Verify file can be downloaded and decrypted
        $downloadResponse = $pubnub->downloadFile()
            ->channel($this->channel)
            ->fileId($response->getFileId())
            ->fileName(basename($this->textFilePath))
            ->sync();

        $this->assertEquals(
            file_get_contents($this->textFilePath),
            $downloadResponse->getFileContent()
        );

        $this->pubnub->deleteFile()
            ->channel($this->channel)
            ->fileId($response->getFileId())
            ->fileName(basename($this->textFilePath))
            ->sync();

        fclose($file);
    }

    public function testFileUploadWithTTL(): void
    {
        $file = fopen($this->textFilePath, "r");

        $response = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This file will expire")
            ->ttl(60) // 60 seconds TTL
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());

        // Verify file exists immediately after upload
        $listResponse = $this->pubnub->listFiles()
            ->channel($this->channel)
            ->sync();

        $this->assertCount(1, $listResponse->getData());

        fclose($file);
    }

    public function testFileUploadWithCustomMessageType(): void
    {
        $file = fopen($this->textFilePath, "r");

        $response = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This is a custom message type file")
            ->customMessageType("file_upload")
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());

        fclose($file);
    }

    public function testFileUploadWithMetadata(): void
    {
        $file = fopen($this->textFilePath, "r");

        $metadata = [
            "author" => "test_user",
            "description" => "Test file with metadata",
            "tags" => ["test", "metadata"]
        ];

        $response = $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This is a file with metadata")
            ->meta($metadata)
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->getTimestamp());

        fclose($file);
    }

    public function testFileUploadWithEmptyFile(): void
    {
        // Create an empty file
        $emptyFilePath = __DIR__ . '/assets/empty.txt';
        file_put_contents($emptyFilePath, '');

        try {
            $file = fopen($emptyFilePath, "r");

            $response = $this->pubnub->sendFile()
                ->channel($this->channel)
                ->fileHandle($file)
                ->fileName('empty.txt')
                ->message("This is an empty file")
                ->sync();

            $this->assertNotEmpty($response);
            $this->assertNotEmpty($response->getFileId());

            // Verify file can be downloaded
            $downloadResponse = $this->pubnub->downloadFile()
                ->channel($this->channel)
                ->fileId($response->getFileId())
                ->fileName('empty.txt')
                ->sync();

            $this->assertEquals('', $downloadResponse->getFileContent());

            fclose($file);
        } finally {
            // Clean up the temporary file
            if (file_exists($emptyFilePath)) {
                unlink($emptyFilePath);
            }
        }
    }

    public function testFileUploadWithLargeFile(): void
    {
        // Create a large file (5MB)
        $largeFilePath = __DIR__ . '/assets/large.txt';
        $largeContent = str_repeat('x', 5 * 1024 * 1024); // 5MB of data
        file_put_contents($largeFilePath, $largeContent);

        try {
            $file = fopen($largeFilePath, "r");

            $response = $this->pubnub->sendFile()
                ->channel($this->channel)
                ->fileHandle($file)
                ->fileName('large.txt')
                ->message("This is a large file")
                ->sync();

            $this->assertNotEmpty($response);
            $this->assertNotEmpty($response->getFileId());

            // Verify file can be downloaded
            $downloadResponse = $this->pubnub->downloadFile()
                ->channel($this->channel)
                ->fileId($response->getFileId())
                ->fileName('large.txt')
                ->sync();

            $this->assertEquals($largeContent, $downloadResponse->getFileContent());

            fclose($file);
        } finally {
            // Clean up the temporary file
            if (file_exists($largeFilePath)) {
                unlink($largeFilePath);
            }
        }
    }

    public function testFileUploadWithInvalidParameters(): void
    {
        $file = fopen($this->textFilePath, "r");

        // Test with invalid channel
        $this->expectException(\PubNub\Exceptions\PubNubValidationException::class);
        $this->pubnub->sendFile()
            ->channel("")  // Empty channel
            ->fileHandle($file)
            ->fileName(basename($this->textFilePath))
            ->message("This should fail")
            ->sync();

        fclose($file);

        // Test with invalid file handle
        $this->expectException(\Exception::class);
        $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle(null)  // Null file handle
            ->fileName(basename($this->textFilePath))
            ->message("This should fail")
            ->sync();

        // Test with invalid file name
        $this->expectException(\PubNub\Exceptions\PubNubValidationException::class);
        $this->pubnub->sendFile()
            ->channel($this->channel)
            ->fileHandle($file)
            ->fileName("")  // Empty file name
            ->message("This should fail")
            ->sync();
    }

    public function testFileDownloadWithEncryption(): void
    {
        // Create a test file with specific content
        $filePath = __DIR__ . '/assets/encrypted.txt';
        $fileContent = "This is encrypted content";
        file_put_contents($filePath, $fileContent);

        try {
            // Upload file with encryption
            $pubnub = new PubNub($this->config_enc);
            $file = fopen($filePath, "r");

            $response = $pubnub->sendFile()
                ->channel($this->channel)
                ->fileHandle($file)
                ->fileName(basename($filePath))
                ->message("This is an encrypted file")
                ->sync();

            $this->assertNotEmpty($response);
            $this->assertNotEmpty($response->getFileId());

            // Download and verify the encrypted file
            $downloadResponse = $pubnub->downloadFile()
                ->channel($this->channel)
                ->fileId($response->getFileId())
                ->fileName(basename($filePath))
                ->sync();

            $this->assertEquals($fileContent, $downloadResponse->getFileContent());

            // Try downloading with non-encrypted client (should fail)

            $downloadResponse = $this->pubnub->downloadFile()
                ->channel($this->channel)
                ->fileId($response->getFileId())
                ->fileName(basename($filePath))
                ->sync();

            // We shoul be able to download the file with the non-encrypted client, but the content should be encrypted
            $this->assertNotEquals($fileContent, $downloadResponse->getFileContent());

            // manually decrypt the content
            $decryptedContent = $this->config_enc->getCrypto()->decrypt($downloadResponse->getFileContent());
            $this->assertEquals($fileContent, $decryptedContent);

            fclose($file);
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function testFileDownloadWithInvalidFileId(): void
    {
        $this->expectException(PubNubServerException::class);

        $this->pubnub->downloadFile()
            ->channel($this->channel)
            ->fileId('invalid-file-id')
            ->fileName('test.txt')
            ->sync();
    }

    public function testFileListingWithPagination(): void
    {
        // Upload multiple files
        $files = [];
        for ($i = 0; $i < 5; $i++) {
            $filePath = __DIR__ . "/assets/test{$i}.txt";
            file_put_contents($filePath, "content{$i}");
            $files[] = $filePath;

            $file = fopen($filePath, "r");
            $this->pubnub->sendFile()
                ->channel($this->channel)
                ->fileHandle($file)
                ->fileName(basename($filePath))
                ->message("Test file {$i}")
                ->sync();
            fclose($file);
        }

        try {
            // List files
            $response = $this->pubnub->listFiles()
                ->channel($this->channel)
                ->sync();

            $this->assertNotEmpty($response->getData());
            $this->assertGreaterThanOrEqual(5, count($response->getData()));

            // Verify we can get file details
            foreach ($response->getData() as $file) {
                $this->assertNotEmpty($file['id']);
                $this->assertNotEmpty($file['name']);
                $this->assertNotEmpty($file['created']);
            }
        } finally {
            // Clean up test files
            foreach ($files as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    public function testNetworkFailureScenario(): void
    {
        // Create a mock client that simulates network failure
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw new ConnectException(
                    "Network failure",
                    $request,
                    null,
                    ['errno' => CURLE_COULDNT_CONNECT]
                );
            }
        };

        $this->pubnub->setClient($client);

        $file = fopen($this->textFilePath, "r");

        try {
            $this->expectException(PubNubConnectionException::class);

            $this->pubnub->sendFile()
                ->channel($this->channel)
                ->fileHandle($file)
                ->fileName(basename($this->textFilePath))
                ->message("This should fail due to network")
                ->sync();
        } finally {
            fclose($file);
            // Restore original client
            $this->pubnub->setClient(new \GuzzleHttp\Client());
        }
    }
}
