<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class RemoveChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub_demo);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $response = $removeChannelGroup->channelGroup("groupA")->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub_demo);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $removeChannelGroup->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub_demo);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $removeChannelGroup->channelGroup("")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $removeChannelGroup = new RemoveChannelGroupExposed($this->pubnub_demo);

        $removeChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $removeChannelGroup->channelGroup("groupA")->sync();
    }

    public function superCallTest()
    {
        $this->pubnub_pam->removeChannelGroup()
            ->channelGroup(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveChannelGroupExposed extends RemoveChannelGroup
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
