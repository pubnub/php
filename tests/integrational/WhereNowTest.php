<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


class WhereNowTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $uuid = "where-now-uuid";

        $this->pubnub->getConfiguration()->setUuid($uuid);

        $whereNow = new WhereNowTestExposed($this->pubnub);

        $whereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/uuid/where-now-uuid")
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

        $this->pubnub->getConfiguration()->setUuid($uuid);

        $whereNow = new WhereNowTestExposed($this->pubnub);

        $whereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/uuid/custom-uuid")
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

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub);

        $whereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/uuid/myUUID")
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

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub);

        $whereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/uuid/myUUID")
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

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $whereNow = new WhereNowTestExposed($this->pubnub);

        $whereNow->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/uuid/myUUID")
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

        $result = $this->pubnub_pam->whereNow()->uuid($uuid)->sync();

        $this->assertInternalType('array', $result->getChannels());
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