<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use Tests\Helpers\StubTransport;

class RemoveDeviceFromPushEndpointTest extends \PubNubTestCase
{
    public function testRemovePushAPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "apns",
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
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
                "topic" => "coolTopic",
                "environment" => "production"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::APNS2)
            ->deviceId('coolDevice')
            ->topic('coolTopic')
            ->environment('production')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testRemovePushMPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "mpns",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::MPNS)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testRemovePushFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "fcm",
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GCM is deprecated. Please use FCM instead.');
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemoveDeviceFromPushEndpointExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "gcm",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $remove->pushType(PNPushType::GCM)
            ->deviceId('coolDevice')
            ->sync();

        $this->assertNotEmpty($result);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class RemoveDeviceFromPushEndpointExposed extends RemoveDeviceFromPush
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
