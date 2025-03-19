<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class ListChannelsInChannelGroupTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, "
                . "\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $response = $listChannelsInChannelGroup->channelGroup("groupA")->sync();

        $this->assertEquals($response->getChannels(), ["a", "b"]);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, "
                . "\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->sync();
    }

    public function testEmptyGroup()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, "
                . "\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("")->sync();
    }

    public function testNullPayload()
    {
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage("Unable to parse server response: No payload found in response");

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }

    public function testNullBody()
    {
        $this->expectException(PubNubResponseParsingException::class);

        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody(json_encode(""));

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $listChannelsInChannelGroup = new ListChannelsInChannelGroupExposed($this->pubnub_demo);

        $listChannelsInChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {\"channels\": [\"a\",\"b\"]}, "
                . "\"service\": \"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $listChannelsInChannelGroup->channelGroup("groupA")->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ListChannelsInChannelGroupExposed extends ListChannelsInChannelGroup
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
