<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class ListPushProvisionsTest extends \PubNubTestCase
{
    public function testAppleSuccess()
    {
        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "type" => "apns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::APNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testFCMSuccess()
    {
        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::FCM)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testMicrosoftSuccess()
    {
        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testIsAuthRequiredSuccess()
    {
        $config = $this->config->clone();
        $config->setAuthKey("myKey");
        $config->setUseRandomIV(false);
        $config->setCipherKey("cipherKey");
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsExposed($pubnub);

        $list->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => $this->pubnub_demo->getConfiguration()->getUuid(),
                "auth" => "myKey",
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testNullSubscribeKey()
    {
        $this->expectException(\TypeError::class);
        $config = $this->config->clone();
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsExposed($pubnub);

        $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptySubscribeKey()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $config = $this->config->clone();
        $config->setSubscribeKey('');
        $pubnub = new PubNub($config);
        $list = new ListPushProvisionsExposed($pubnub);

        $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testNullPushType()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceId()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptyDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $list = new ListPushProvisionsExposed($this->pubnub_demo);

        $list->deviceId("")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ListPushProvisionsExposed extends ListPushProvisions
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
