<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\PNConfiguration;
use PubNub\PubNubUtil;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class SetStateTest extends \PubNubTestCase
{
    public function testApplyStateForChannel()
    {
        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/myUserId/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUserId"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, "
                . "\"status\" : \"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForSomebodyElseChannel()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUserId("someoneElseUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/someoneElseUUID/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "someoneElseUUID",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelsSync()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUserId("myUserId");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel,testChannel2/uuid/myUserId/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUserId",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels(["testChannel", "testChannel2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroup()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUserId("myUserId");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/myUserId/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
                "channel-group" => "cg1",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUserId",
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $response = $setState->channelGroups("cg1")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroups()
    {
        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => PubNubUtil::urlEncode("cg1,cg2")
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $response = $setState->channelGroups(["cg1", "cg2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForMix()
    {
        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => PubNubUtil::urlEncode("cg1,cg2")
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $response = $setState->channels("ch1")->channelGroups(["cg1", "cg2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyNon200()
    {
        $this->expectException(PubNubException::class);
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUserId");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $setState->channels("ch1")->channelGroups(["cg1", "cg2"])->state($myState)->sync();
    }

    public function testMissingState()
    {
        $this->expectNotToPerformAssertions();

        $setState = new SetStateExposed($this->pubnub_demo);

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "pnsdk" => $this->encodedSdkName,
                "state" => "%5B%5D"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $setState->channels("ch1")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUserId");
        $config->setAuthKey("myKey");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "auth" => "myKey"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testNullSubKey()
    {
        $this->expectException(\TypeError::class);

        $config = $this->config->clone();
        $config->setUuid("myUserId");
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testEmptySubKey()
    {
        $this->expectException(PubNubException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $config = $this->config->clone();
        $config->setUuid("myUserId");
        $config->setSubscribeKey("");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testChannelAndGroupMissing()
    {
        $this->expectException(PubNubException::class);
        $this->expectExceptionMessage("Channel or group missing");

        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $setState->state($myState)->sync();
    }

    public function testNullPayload()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub_demo);

        $myState = null;

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUserId/data")
            ->withQuery([
                "uuid" => "myUserId",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"service\": \"Presence\"}");


        $setState->channels("testChannel")->state($myState)->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class SetStateExposed extends SetState
{
    protected PsrStubClient $client;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->client = new PsrStubClient();
        $pubnubInstance->setClient($this->client);
    }

    public function stubFor(string $url): PsrStub
    {
        $stub = new PsrStub($url);
        $this->client->addStub($stub);
        return $stub;
    }
}
