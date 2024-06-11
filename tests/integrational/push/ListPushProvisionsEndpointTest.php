<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class ListPushProvisionsEndpointTest extends PubNubTestCase
{
    public function testListChannelGroupAPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsEndpointExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "apns",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $list->pushType(PNPushType::APNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testListChannelGroupAPNS2()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsEndpointExposed($this->pubnub);

        $list->stubFor("/v2/push/sub-key/demo/devices-apns2/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID",
                "topic" => "coolTopic",
                "environment" => "production"
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $list->pushType(PNPushType::APNS2)
            ->deviceId("coolDevice")
            ->topic("coolTopic")
            ->environment("production")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testListChannelGroupFCM()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsEndpointExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "gcm",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $list->pushType(PNPushType::FCM)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertNotEmpty($result);
    }

    public function testListChannelGroupMPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsEndpointExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "mpns",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $list->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
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

        $list = new ListPushProvisionsEndpointExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "type" => "gcm",
                "uuid" => "sampleUUID",
            ])
            ->setResponseBody('[1, "Modified Channels"]');

        $result = $list->pushType(PNPushType::GCM)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertNotEmpty($result);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ListPushProvisionsEndpointExposed extends ListPushProvisions
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

    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    public function requestOptions()
    {
        return [
            'transport' => $this->transport
        ];
    }
}
// phpcs:ignore PSR1.Classes.ClassDeclaration
