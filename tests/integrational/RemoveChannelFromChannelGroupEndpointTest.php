<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class RemoveChannelFromChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub_demo);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "remove" => "ch1,ch2",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $response = $removeChannelFromChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub_demo);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $removeChannelFromChannelGroup->channels(["ch1" ,"ch2"])->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channels missing");

        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub_demo);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $removeChannelFromChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $removeChannelFromChannelGroup = new RemoveChannelFromChannelGroupExposed($this->pubnub_demo);

        $removeChannelFromChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey",
                "remove" => "ch1,ch2",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $removeChannelFromChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveChannelFromChannelGroupExposed extends RemoveChannelFromChannelGroup
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
