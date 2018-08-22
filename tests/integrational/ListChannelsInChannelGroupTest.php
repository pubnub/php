<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


class ListChannelsInChannelGroupTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $listChannelsInChannelGroup->channelGroup("groupA")->sync();

        $this->assertEquals($response->getChannels(), ["a", "b"]);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("")->sync();
    }

    public function testNullPayload()
    {
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Unable to parse server response: No payload found in response");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }

    public function testNullBody()
    {
        $this->expectException(PubNubResponseParsingException::class);

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,:[]*`|
        $characters = "-._~@!$&'()+;=";

        $this->pubnub_pam->listChannelsInChannelGroup()
            ->channelGroup($characters)
            ->sync();
    }
}


class ListChannelsInChannelGroupExposed extends ListChannelsInChannelGroup
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