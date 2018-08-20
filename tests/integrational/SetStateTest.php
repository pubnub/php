<?php

namespace Tests\Integrational;

use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubException;
use Tests\Helpers\StubTransport;


class SetStateTest extends \PubNubTestCase
{
    public function testApplyStateForChannel()
    {
        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/testChannel/uuid/myUUID/data")
            ->withQuery([
                "state" => "%7B%22age%22%3A20%7D",
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
                "uuid" => "someoneElseUUID",
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%5B%5D"
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
                "state" => "%7B%22age%22%3A20%7D",
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
        $this->expectExceptionMessage("Subscribe Key not configured");

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
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
        $this->expectExceptionMessage("Subscribe Key not configured");

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
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
        $this->expectExceptionMessage("Channel or group missing");

        $setState = new SetStateExposed($this->pubnub);

        $myState = [
            "age" => 20
        ];

        $setState->stubFor("/v2/presence/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel/ch1/uuid/myUUID/data")
            ->withQuery([
                "uuid" => "myUUID",
                "state" => "%7B%22age%22%3A20%7D",
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
                "state" => "%7B%22age%22%3A20%7D",
                "pnsdk" => $this->encodedSdkName
            ])
            ->setResponseBody("{ \"status\": 200, \"message\": \"OK\", \"service\": \"Presence\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $setState->channels("testChannel")->state($myState)->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,:[]*`|+;&
        $groupCharacters = "-._~@!$'()=";

        // Not valid
        // ,~/
        $channelCharacters = "-._:?#[]@!$&'()*+;=`|";

        // Not valid
        // ,~/#&+;
        $getStateCharacters = "-._:?[]@!$'()*=`|";

        // Not valid
        // /?#[]`|
        $uuidCharacters = "-.,_~:@!$&'()*+;=";

        $this->pubnub_pam->getConfiguration()->setUuid($uuidCharacters);

        $this->pubnub_pam->setState()
            ->state(['name' => $getStateCharacters])
            ->channels($channelCharacters)
            ->channelGroups($groupCharacters)
            ->sync();
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
