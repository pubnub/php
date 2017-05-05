<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use Tests\Helpers\StubTransport;


class ListPushProvisionsTest extends \PubNubTestCase
{
    public function testAppleSuccess()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "type" => "apns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::APNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testGoogleSuccess()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::GCM)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testMicrosoftSuccess()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testIsAuthRequiredSuccess()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "auth" => "myKey",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[\"ch1\", \"ch2\", \"ch3\"]");

        $response = $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushListProvisionsResult::class, $response);
    }

    public function testNullSubscribeKey()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptySubscribeKey()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->deviceId("coolDevice")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testNullPushType()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceId()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptyDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $list = new ListPushProvisionsExposed($this->pubnub);

        $list->deviceId("")
            ->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function superCallTest()
    {
        $this->pubnub_pam->listPushProvisions()
            ->deviceId(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}


class ListPushProvisionsExposed extends ListPushProvisions
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