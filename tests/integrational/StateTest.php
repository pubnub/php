<?php

use PubNub\Endpoints\Presence\GetState;
use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;


class StateTest extends PubNubTestCase
{
    public function testApplyStateForChannel()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/myUUID/data")
            ->withQuery([
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForSomebodyElseChannel()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/someoneElseUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("someoneElseUUID");

        $response = $setState->channels("testChannel")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelsSync()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel,testChannel2/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $setState->channels(["testChannel", "testChannel2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroup()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/,/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $setState->channelGroups("cg1")->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForChannelGroups()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/,/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $setState->channelGroups(["cg1", "cg2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyStateForMix()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $setState->channels("ch1")->channelGroups(["cg1", "cg2"])->state($myState)->sync();

        $this->assertEquals($response->getState()["age"], 20);
        $this->assertEquals($response->getState()["status"], "online");
    }

    public function testApplyNon200()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "channel-group" => "cg1,cg2"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $setState->channels("ch1")->channelGroups(["cg1cg2"])->state($myState)->sync();
    }

    public function testMissingState()
    {
        $setState = new SetStateExposed($this->pubnub);

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "pnsdk" => $this->encodedSdkName,
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $setState->channels("ch1")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName,
                "auth" => "myKey"
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testNullSubKey()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testEmptySubKey()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $setState->channels("ch1")->state($myState)->sync();
    }

    public function testChannelAndGroupMissing()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\" }, \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $setState->state($myState)->sync();
    }

    public function testNullPayload()
    {
        $this->expectException(PubNubException::class);

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22:20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $setState->channels("testChannel")->state($myState)->sync();
    }

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

        $getState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/sampleUUID")
            ->withQuery([
                "uuid" => "sampleUUID",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"payload\": { \"age\" : 20, \"status\" : \"online\"}, \"service\": \"Presence\"}");

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


}


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