<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class RemoveChannelsFromPushEndpointTest extends \PubNubTestCase
{
    public function testPushRemoveSingleChannel()
    {
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "remove" => "ch",
                "type" => "apns",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
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
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice")
            ->withQuery([
                "remove" => "ch",
                "topic" => "coolTopic",
                "environment" => "development",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
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
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2",
                "type" => "mpns",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
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
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveChannelsFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2,ch3",
                "type" => "gcm",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
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
