<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use Tests\Helpers\StubTransport;

class RemoveChannelsFromPushEndpointTest extends \PubNubTestCase
{
    public function testPushRemoveSingleChannel()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "apns",
                "uuid" => "sampleUUID",
                "remove" => "ch"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->channels('ch')
            ->pushType(PNPushType::APNS)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }
    public function testPushRemoveApns2()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
                "remove" => "ch",
                "topic" => "coolTopic",
                "environment" => "development"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->channels('ch')
            ->pushType(PNPushType::APNS2)
            ->deviceId('coolDevice')
            ->topic('coolTopic')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testPushRemoveMultipleChannels()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "mpns",
                "uuid" => "sampleUUID",
                "remove" => "ch1,ch2"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->channels(['ch1', 'ch2'])
            ->pushType(PNPushType::MPNS)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testPushRemoveFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "fcm",
                "uuid" => "sampleUUID",
                "remove" => "ch1,ch2,ch3"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->channels(['ch1', 'ch2', 'ch3'])
            ->pushType(PNPushType::FCM)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveChannelsFromPushEndpointExposed extends RemoveChannelsFromPush
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
