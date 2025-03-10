<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class AddChannelChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);
        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s",
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $response = $addChannelChannelGroup->channelGroup("gr|oupA")->channels(["c|h1", "ch2s"])->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channels(["c|h1", "ch2s"])->sync();
    }

    public function testGroupIsEmpty()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channelGroup("")->channels(["c|h1", "ch2s"])->sync();
    }

    public function testChannelMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channels missing");
        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->expectNotToPerformAssertions();
        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID",
                "auth" => "myKey",
                "add" => "ch1,ch2"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $addChannelChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }

    public function testErrorBodyForbidden()
    {
        $this->expectException(PubNubServerException::class);
        $this->expectExceptionMessage("Server responded with an error and the status code is 403");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub_demo);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/demo/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "myUUID",
                "auth" => "myKey",
                "add" => "ch1,ch2"
            ])
            ->setResponseStatus(403)
            ->setResponseBody("{\"status\": 403, \"message\": \"OK\", \"payload\": {},\"service\":\"ChannelGroups\"}");

        $this->pubnub_demo->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $addChannelChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,:[]*`|
        $groupCharacters = "-._~@!$&'()+;=,:[]*`|";
        // Not valid
        // :&*+;
        $channelCharacters = "-.,_~[]@!$'()=`|:&*+;";

        $this->expectException(PubNubServerException::class);

        $this->pubnub_pam->addChannelToChannelGroup()
            ->channels($channelCharacters)
            ->channelGroup($groupCharacters)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class AddChannelChannelGroupExposed extends AddChannelToChannelGroup
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
