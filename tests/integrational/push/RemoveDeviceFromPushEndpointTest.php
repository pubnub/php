<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class RemoveDeviceFromPushEndpointTest extends \PubNubTestCase
{
    public function testRemovePushAPNS()
    {
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "type" => "apns",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::APNS)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testRemovePushAPNS2()
    {
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice/remove")
            ->withQuery([
                "topic" => "coolTopic",
                "environment" => "production",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::APNS2)
            ->deviceId('coolDevice')
            ->topic('coolTopic')
            ->environment('production')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testRemovePushFCM()
    {
        $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub_demo);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "type" => "fcm",
                "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::FCM)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testWarningWhenUsingDeprecatedGCMType()
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \Exception($errstr, $errno);
        }, E_USER_DEPRECATED);

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('GCM is deprecated. Please use FCM instead.');
            $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

            $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub_demo);

            $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
                ->withQuery([
                    "type" => "gcm",
                    "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                    "uuid" => "sampleUUID",
                ])
                ->setResponseBody('[1, "Modified Channels"]');

            $result = $remove->pushType(PNPushType::GCM)
                ->deviceId('coolDevice')
                ->sync();

            $this->assertNotEmpty($result);
        } finally {
            restore_error_handler();
        }
    }

    public function testWarningWhenUsingDeprecatedAPNSType(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \Exception($errstr, $errno);
        }, E_USER_DEPRECATED);

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('APNS is deprecated. Please use APNS2 instead.');
            $this->pubnub_demo->getConfiguration()->setUuid("sampleUUID");

            $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub_demo);

            $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
                ->withQuery([
                    "type" => "apns",
                    "pnsdk" => $this->pubnub_demo->getSdkFullName(),
                    "uuid" => "sampleUUID",
                ])
                ->setResponseBody('[1, "Modified Channels"]');

            $result = $remove->pushType(PNPushType::APNS)
                ->deviceId('coolDevice')
                ->sync();

            $this->assertNotEmpty($result);
        } finally {
            restore_error_handler();
        }
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveDeviceFromPushEndpointExposed extends RemoveDeviceFromPush
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
