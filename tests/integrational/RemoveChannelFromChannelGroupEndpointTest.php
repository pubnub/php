<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


class RemoveChannelFromChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "remove" => "ch1,ch2"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $removeChannelFromChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $removeChannelFromChannelGroup->channels(["ch1" ,"ch2"])->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channels missing");

        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $removeChannelFromChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey",
                "remove" => "ch1,ch2"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {}, \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $removeChannelFromChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,:[]*`|
        $groupCharacters = "-._~@!$&'()+;=";
        // Not valid
        // :&*+;
        $channelCharacters = "-.,_~[]@!$'()=`|";

        $this->pubnub_pam->removeChannelFromChannelGroup()
            ->channels($channelCharacters)
            ->channelGroup($groupCharacters)
            ->sync();
    }
}


class RemoveChannelFromChannelGroupExposed extends RemoveChannelFromChannelGroup
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