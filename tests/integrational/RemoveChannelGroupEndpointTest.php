<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


class RemoveChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $removeChannelGroup->channelGroup("groupA")->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $removeChannelGroup->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $removeChannelGroup->channelGroup("")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $removeChannelGroup->channelGroup("groupA")->sync();
    }

    public function superCallTest()
    {
        $this->pubnub_pam->removeChannelGroup()
            ->channelGroup(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}


class RemoveChannelGroupExposed extends RemoveChannelGroup
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