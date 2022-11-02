<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNubTestCase;
use Tests\Helpers\StubTransport;
use PHPUnit\Framework\Constraint\IsType;

class WhereNowTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $uuid = "where-now-uuid";

        $this->pubnub_demo->getConfiguration()->setUuid($uuid);

        $whereNow = new WhereNowTestExposed($this->pubnub_demo);

        $whereNow->stubFor("/v2/presence/sub-key/demo/uuid/where-now-uuid")
            ->withQuery([
                'uuid' => $uuid,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"Presence\"}");

        $response = $whereNow->sync();

        $this->assertEquals($response->getChannels(), ["a", "b"]);
    }

    public function testSuccessCustomUUID()
    {
        $uuid = "where-now-uuid";
        $customUuid = "custom-uuid";

        $this->pubnub_demo->getConfiguration()->setUuid($uuid);

        $whereNow = new WhereNowTestExposed($this->pubnub_demo);

        $whereNow->stubFor("/v2/presence/sub-key/demo/uuid/custom-uuid")
            ->withQuery([
                'uuid' => $uuid,
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"Presence\"}");

        $response = $whereNow->uuid($customUuid)->sync();

        $this->assertEquals($response->getChannels(), ["a", "b"]);
    }

    public function testBrokenWithString()
    {
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Unable to parse server response: Syntax error");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub_demo);

        $whereNow->stubFor("/v2/presence/sub-key/demo/uuid/myUUID")
            ->withQuery([
                'uuid' => "myUUID",
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [zimp]}, \"service\": \"Presence\"}");

        $whereNow->sync();
    }

    public function testBrokenWithoutJSON()
    {
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Unable to parse server response: Syntax error");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub_demo);

        $whereNow->stubFor("/v2/presence/sub-key/demo/uuid/myUUID")
            ->withQuery([
                'uuid' => "myUUID",
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": zimp}, \"service\": \"Presence\"}");

        $whereNow->sync();
    }

    public function testNullPayload()
    {
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Unable to parse server response: No payload found in response");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub_demo);

        $whereNow->stubFor("/v2/presence/sub-key/demo/uuid/myUUID")
            ->withQuery([
                'uuid' => "myUUID",
                'pnsdk' => $this->encodedSdkName
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"Presence\"}");

        $whereNow->sync();
    }

    public function testSuperCall()
    {
        $uuid = 'test-where-now-php-uuid-.*|@#';

        $result = $this->pubnub_demo->whereNow()->uuid($uuid)->sync();
        static::assertThat($result->getChannels(), new IsType('array'), 'Response should be of type \'array\'');
    }
}


class WhereNowTestExposed extends WhereNow
{
    protected $transport;

    public function __construct($pubnubInstance)
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