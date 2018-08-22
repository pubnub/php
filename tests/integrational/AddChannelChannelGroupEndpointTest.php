<?php

namespace Tests\Integrational;

use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


class AddChannelChannelGroupEndpointTest extends PubNubTestCase
{
    public function testSuccess()
    {
        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $response = $addChannelChannelGroup->channelGroup("gr|oupA")->channels(["c|h1", "ch2s"])->sync();

        $this->assertNotEmpty($response);
    }

    public function testGroupMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channels(["c|h1", "ch2s"])->sync();
    }

    public function testGroupIsEmpty()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel group missing");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/gr%7CoupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "add" => "c%7Ch1,ch2s"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channelGroup("")->channels(["c|h1", "ch2s"])->sync();
    }

    public function testChannelMissing()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channels missing");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID");

        $addChannelChannelGroup->channelGroup("groupA")->sync();
    }

    public function testIsAuthRequiredSuccess()
    {
        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey",
                "add" => "ch1,ch2"
            ])
            ->setResponseBody("{\"status\": 200, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $addChannelChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }

    public function testErrorBodyForbidden()
    {
        $this->expectException(PubNubServerException::class);
        $this->expectExceptionMessage("Server responded with an error and the status code is 403");

        $addChannelChannelGroup = new AddChannelChannelGroupExposed($this->pubnub);

        $addChannelChannelGroup->stubFor("/v1/channel-registration/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/channel-group/groupA")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "auth" => "myKey",
                "add" => "ch1,ch2"
            ])
            ->setResponseStatus("HTTP/1.0 403 Forbidden")
            ->setResponseBody("{\"status\": 403, \"message\": \"OK\", \"payload\": {} , \"service\": \"ChannelGroups\"}");

        $this->pubnub->getConfiguration()->setUuid("myUUID")->setAuthKey("myKey");

        $addChannelChannelGroup->channelGroup("groupA")->channels(["ch1", "ch2"])->sync();
    }

    public function testSuperCallTest()
    {
        // Not valid
        // ,:[]*`|
        $groupCharacters = "-._~@!$&'()+;=";
        // Not valid
        // :&*+;
        $channelCharacters = "-.,_~[]@!$'()=`|";

        $this->pubnub_pam->addChannelToChannelGroup()
            ->channels($channelCharacters)
            ->channelGroup($groupCharacters)
            ->sync();
    }
}


class AddChannelChannelGroupExposed extends AddChannelToChannelGroup
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
