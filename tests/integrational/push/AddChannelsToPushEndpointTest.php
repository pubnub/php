<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class AddChannelsToPushEndpointTest extends PubNubTestCase
{
    public function testPushAddSingleChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushEndpointExposed($this->pubnub);

        $add->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "add" => "ch",
                "type" => "apns",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $add->channels("ch")
            ->pushType(PNPushType::APNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testPushAddMultipleChannels()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushEndpointExposed($this->pubnub);

        $add->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "add" => "ch1,ch2",
                "type" => "apns",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $add->channels([ "ch1", "ch2"])
            ->pushType(PNPushType::APNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testPushAddApns2()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushEndpointExposed($this->pubnub);

        $add->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "add" => "ch1,ch2",
                "uuid" => "sampleUUID",
                "topic" => "coolTopic",
                "environment" => "production"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $add->channels([ "ch1", "ch2"])
            ->pushType(PNPushType::APNS2)
            ->deviceId("coolDevice")
            ->topic("coolTopic")
            ->environment("production")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testPushAddGoogle()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $add = new AddChannelsToPushEndpointExposed($this->pubnub);

        $add->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "add" => "ch1,ch2,ch3",
                "type" => "gcm",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $add->channels(["ch1", "ch2", "ch3"])
            ->pushType(PNPushType::GCM)
            ->deviceId("coolDevice");

        $this->assertNotEmpty($result);
    }
}


class AddChannelsToPushEndpointExposed extends AddChannelsToPush
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
