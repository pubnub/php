<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\PubNubUtil;
use Tests\Helpers\StubTransport;

class SetStateTest extends \PubNubTestCase
{
    public function testApplyStateForChannel()
    {
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/myUUID/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, "
                . "\"status\" : \"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForSomebodyElseChannel()
    {
        $config = $this->config->clone();
        $config->setUuid("someoneElseUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel/uuid/someoneElseUUID/data")
            ->withQuery([
                "uuid" => "someoneElseUUID",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelsSync()
    {
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/testChannel,testChannel2/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");

        $response = $setState->channels(["testChannel", "testChannel2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroup()
    {
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $response = $setState->channelGroups("cg1")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroups()
    {
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/,/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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

        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => PubNubUtil::urlEncode("cg1,cg2")
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : "
                . "\"online\" }, \"service\": \"Presence\"}");


        $setState->channels("ch1")->channelGroups(["cg1cg2"])->state($myState)->sync();
    }

    public function testMissingState()
    {
        $this->expectNotToPerformAssertions();

        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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
        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $config->setAuthKey("myKey");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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
        $config->setUuid("myUUID");
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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
        $config->setUuid("myUUID");
        $config->setSubscribeKey("");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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

        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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

        $config = $this->config->clone();
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);
        $setState = new SetStateExposed($pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/demo/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
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
