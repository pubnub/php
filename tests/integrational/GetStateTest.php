<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\GetState;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class GetStateTest extends PubNubTestCase
{
    public function testOneChannel()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . " \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channels("testChannel")->sync();

        $this->assertEquals($response->getChannels()["testChannel"]["age"], 20);
        $this->assertEquals($response->getChannels()["testChannel"]["status"], "online");
    }

    public function testOneChannelWithoutUUID()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/myUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $response = $getState->channels("testChannel")->sync();

        $this->assertEquals($response->getChannels()["testChannel"]["age"], 20);
        $this->assertEquals($response->getChannels()["testChannel"]["status"], "online");
    }

    public function testFailedPayload()
    {
        $this->expectException(PubNubException::class);

        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $getState->channels("testChannel")->sync();
    }

    public function testMultipleChannel()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/ch1,ch2/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"channels\": { \"ch1\": { \"age\" : 20, \"status\" : \"online\"}, "
                . "\"ch2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channels(["ch1", "ch2"])->sync();

        $this->assertEquals($response->getChannels()["ch1"]["age"], 20);
        $this->assertEquals($response->getChannels()["ch1"]["status"], "online");

        $this->assertEquals($response->getChannels()["ch2"]["age"], 100);
        $this->assertEquals($response->getChannels()["ch2"]["status"], "offline");
    }

    public function testOneChannelGroup()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/sampleUUID")
            ->withQuery([
                "channel-group" => "cg1",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, "
                . "\"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channelGroups("cg1")->sync();

        $this->assertEquals($response->getChannels()["chcg1"]["age"], 20);
        $this->assertEquals($response->getChannels()["chcg1"]["status"], "online");

        $this->assertEquals($response->getChannels()["chcg2"]["age"], 100);
        $this->assertEquals($response->getChannels()["chcg2"]["status"], "offline");
    }

    public function testManyChannelGroup()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/sampleUUID")
            ->withQuery([
                "channel-group" => PubNubUtil::urlEncode("cg1,cg2"),
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, "
                . "\"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $response = $getState->channelGroups(["cg1", "cg2"])->sync();

        $this->assertEquals($response->getChannels()["chcg1"]["age"], 20);
        $this->assertEquals($response->getChannels()["chcg1"]["status"], "online");

        $this->assertEquals($response->getChannels()["chcg2"]["age"], 100);
        $this->assertEquals($response->getChannels()["chcg2"]["status"], "offline");
    }

    public function testCombination()
    {
        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/sampleUUID")
            ->withQuery([
                "channel-group" => PubNubUtil::urlEncode("cg1,cg2"),
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"channels\": { \"chcg1\": { \"age\" : 20, \"status\" : \"online\"}, "
                . "\"chcg2\": { \"age\": 100, \"status\": \"offline\" } } }, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

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

        $getState = new GetStateExposed($this->pubnub_demo);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $getState->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $config = $this->config->clone();
        $config->setAuthKey("myKey");
        $config->setUuid("sampleUUID");

        $pubnub = new PubNub($config);
        $getState = new GetStateExposed($pubnub);
        $subKey = $pubnub->getConfiguration()->getSubscribeKey();
        $getState->stubFor("/v2/presence/sub-key/{$subKey}/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
                "auth" => "myKey",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $getState->channels("testChannel")->sync();
    }

    public function testNullSubKey()
    {
        $this->expectException(\TypeError::class);

        $config = $this->config->clone();
        $config ->setSubscribeKey(null);
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $getState = new GetStateExposed($pubnub);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $getState->channels("testChannel")->sync();
    }

    public function testEmptySubKeySync()
    {
        $this->expectException(PubNubException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $config = $this->config->clone();
        $config ->setSubscribeKey("");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $getState = new GetStateExposed($pubnub);

        $getState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", "
                . "\"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

        $getState->channels("testChannel")->sync();
    }

    public function testSuperCall()
    {
        $this->expectNotToPerformAssertions();
        // Not valid
        // ,~/
        $characters = "-._:?#[]@!$&'()*+;=`|";

        $this->pubnub_pam->getState()
            ->channels($characters)
            ->sync();
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class GetStateExposed extends GetState
{
    protected $client;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->client = new PsrStubClient();
        $pubnubInstance->setClient($this->client);
    }

    public function stubFor($url)
    {
        $stub = new PsrStub($url);
        $this->client->addStub($stub);
        return $stub;
    }
}
