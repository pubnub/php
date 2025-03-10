<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class ListPushProvisionsEndpointTest extends PubNubTestCase
{
    public function testListChannelGroupAPNS()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsEndpointExposed($pubnub);

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
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsEndpointExposed($pubnub);

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
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsEndpointExposed($pubnub);

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
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsEndpointExposed($pubnub);

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

        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsEndpointExposed($pubnub);

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
