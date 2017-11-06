<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\GetState;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\Stub;
use Tests\Helpers\StubTransport;


class GetStateTest extends PubNubTestCase
{
    public function testOneChannel()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channels("testChannel")->sync();

        $this->assertEquals($response->getChannels()["testChannel"]["age"], 20);
        $this->assertEquals($response->getChannels()["testChannel"]["status"], "online");
    }

    public function testOneChannelWithoutUUID()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/myUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $getState->channels("testChannel")->sync();

        $this->assertEquals($response->getChannels()["testChannel"]["age"], 20);
        $this->assertEquals($response->getChannels()["testChannel"]["status"], "online");
    }

    public function testFailedPayload()
    {
        $this->expectException(PubNubException::class);

        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getState->channels("testChannel")->sync();
    }

    public function testMultipleChannel()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1,ch2/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"channels\": { \"ch1\": { \"age\" : 20, \"status\" : \"online\"}, \"ch2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channels(["ch1", "ch2"])->sync();

        $this->assertEquals($response->getChannels()["ch1"]["age"], 20);
        $this->assertEquals($response->getChannels()["ch1"]["status"], "online");

        $this->assertEquals($response->getChannels()["ch2"]["age"], 100);
        $this->assertEquals($response->getChannels()["ch2"]["status"], "offline");
    }

    public function testOneChannelGroup()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/,/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, \"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channelGroups("cg1")->sync();

        $this->assertEquals($response->getChannels()["chcg1"]["age"], 20);
        $this->assertEquals($response->getChannels()["chcg1"]["status"], "online");

        $this->assertEquals($response->getChannels()["chcg2"]["age"], 100);
        $this->assertEquals($response->getChannels()["chcg2"]["status"], "offline");
    }

    public function testManyChannelGroup()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/,/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, \"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channelGroups(["cg1", "cg2"])->sync();

        $this->assertEquals($response->getChannels()["chcg1"]["age"], 20);
        $this->assertEquals($response->getChannels()["chcg1"]["status"], "online");

        $this->assertEquals($response->getChannels()["chcg2"]["age"], 100);
        $this->assertEquals($response->getChannels()["chcg2"]["status"], "offline");
    }

    public function testCombination()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, \"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channels("ch1")->channelGroups(["cg1", "cg2"])->sync();

        $this->assertEquals($response->getChannels()["chcg1"]["age"], 20);
        $this->assertEquals($response->getChannels()["chcg1"]["status"], "online");

        $this->assertEquals($response->getChannels()["chcg2"]["age"], 100);
        $this->assertEquals($response->getChannels()["chcg2"]["status"], "offline");
    }

    public function testMissingChannelAndGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel or group missing");

        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getState->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName,
                "auth" => "myKey"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setAuthKey("myKey");
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getState->channels("testChannel")->sync();
    }

    public function testNullSubKey()
    {
        $this->expectException(PubNubException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setSubscribeKey(null);
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getState->channels("testChannel")->sync();
    }

    public function testEmptySubKeySync()
    {
        $this->expectException(PubNubException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $getState = new GetStateExposed($this->pubnub);

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setSubscribeKey("");
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $getState->channels("testChannel")->sync();
    }

    public function testSuperCall()
    {
        // Not valid
        // ,~/
        $characters = "-._:?#[]@!$&'()*+;=`|";

        $this->pubnub_pam->getState()
            ->channels($characters)
            ->sync();
    }
}


class GetStateExposed extends GetState
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
