<?php

namespace PubNubTests\integrational\fileSharing;

use PubNubTestCase;
use PubNub\Endpoints\FileSharing\ListFiles;
use PubNub\PubNub;
use Tests\Helpers\StubTransport;

class ListFilesTest extends PubNubTestCase
{
    public function testListFiles()
    {
        $listFiles = new ListFilesExposed($this->pubnub);

        $listFiles
            ->stubFor("/v1/files/demo/channels/my_channel")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody('{"status": 200, "data": [
                {"id": "my_file_id", "name": "my_file_name", "size": 123, "time_token": 1234567890}
            ]}');

        $response = $listFiles->channel("my_channel")->sync();

        $this->assertNotEmpty($response);
        $this->assertCount(1, $response->getData());
        $this->assertEquals("my_file_id", $response->getData()[0]->getId());
        $this->assertEquals("my_file_name", $response->getData()[0]->getName());
        $this->assertEquals(123, $response->getData()[0]->getSize());
        $this->assertEquals(1234567890, $response->getData()[0]->getTimeToken());
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ListFilesExposed extends ListFiles
{
    protected $transport;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);

        $this->transport = new StubTransport();
    }

    public function stubFor($url)
    {
        return $this->transport->stubFor($url);
    }

    public function requestOptions()
    {
        return [
            'transport' => $this->transport
        ];
    }
}
