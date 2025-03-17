<?php

namespace PubNubTests\integrational;

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStubClient;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubServerException;

class FilesTest extends PubNubTestCase
{
    protected string $channel = "files-test";
    protected string $textFilePath = __DIR__ . '/assets/spam.spam';
    protected string $binaryFilePath = __DIR__ . '/assets/pn.gif';
    protected ?string $textFileId;
    protected ?string $binaryFileId;

    public function testEmptyFileList()
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(0, $response->getData());
    }

    public function testSendTextFile()
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

    public function testSendBinaryFile()
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

    public function testNonEmptyFileList()
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(2, $response->getData());
        $this->textFileId = $response->getData()[0]['id'];
    }

    public function testGetDownloadUrls()
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

    public function testDownloadFiles()
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

    public function testDeleteAllFiles()
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

    public function testEmptyFileListAfterDelete()
    {
        $response = $this->pubnub->listFiles()->channel($this->channel)->sync();
        $this->assertNotEmpty($response);
        $this->assertCount(0, $response->getData());
    }

    public function testThrowErrorOnMalformedResponse()
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
        $this->pubnub->setClient($client);
        $pubnub->getFileDownloadUrl()->channel($this->channel)->fileId('none')->fileName('none')->sync();
    }

    public function testThrowErrorOnNoFileFound()
    {
        $this->expectException(PubNubServerException::class);
        $this->pubnub->downloadFile()->channel($this->channel)->fileId('-')->fileName('-')->sync();
    }
}
