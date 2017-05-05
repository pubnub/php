<?php

namespace Tests\Integrational\Push;

use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use Tests\Helpers\StubTransport;


class ModifyPushChannelsForDeviceTest extends \PubNubTestCase
{
    public function testListChannelGroupAPNS()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice/remove")
            ->withQuery([
                "type" => "apns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listRemove->pushType(PNPushType::APNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult::class, $response);
    }

    public function testGoogleSuccessRemoveAll()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice/remove")
            ->withQuery([
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listRemove->pushType(PNPushType::GCM)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult::class, $response);
    }

    public function testMicrosoftSuccessRemoveAll()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice/remove")
            ->withQuery([
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult::class, $response);
    }

    public function testIsAuthRequiredSuccessRemoveAll()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice/remove")
            ->withQuery([
                "auth" => "myKey",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult::class, $response);
    }

    public function testNullSubscribeKeyRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullPushTypeRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptyDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listRemove = new RemoveChannelsFromPushTestExposed($this->pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("")
            ->sync();
    }

    public function testAddAppleSuccess()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "add" => "ch1,ch2,ch3",
                "type" => "apns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listAdd->pushType(PNPushType::APNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushAddChannelResult::class, $response);
    }

    public function testAddGoogleSuccessSync()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "add" => "ch1,ch2,ch3",
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listAdd->pushType(PNPushType::GCM)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushAddChannelResult::class, $response);
    }

    public function testAddMicrosoftSuccessSync()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "add" => "ch1,ch2,ch3",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushAddChannelResult::class, $response);
    }

    public function testIsAuthRequiredSuccessAdd()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "add" => "ch1,ch2,ch3",
                "auth" => "myKey",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushAddChannelResult::class, $response);
    }

    public function testNullSubscribeKeyAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey("");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullPushTypeAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceIdAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->sync();
    }

    public function testEmptyDeviceIdAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("")
            ->sync();
    }

    public function testMissingChannelsAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $listAdd = new AddChannelsToPushExposed($this->pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->deviceId("")
            ->sync();
    }

    public function testAppleSuccessRemove()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2,ch3",
                "type" => "apns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $remove->pushType(PNPushType::APNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveChannelResult::class, $response);
    }

    public function testGoogleSuccessRemove()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2,ch3",
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $remove->pushType(PNPushType::GCM)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveChannelResult::class, $response);
    }

    public function testMicrosoftSuccessRemove()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2,ch3",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveChannelResult::class, $response);
    }

    public function testIsAuthRequiredSuccessRemove()
    {
        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setAuthKey("myKey");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->stubFor("/v1/push/sub-key/sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe/devices/coolDevice")
            ->withQuery([
                "auth" => "myKey",
                "remove" => "ch1,ch2,ch3",
                "type" => "mpns",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveChannelResult::class, $response);
    }

    public function testNullSubscribeKeyRemove()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyRemove()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");
        $this->pubnub->getConfiguration()->setSubscribeKey(null);

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullPushType()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceId()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->sync();
    }

    public function testEmptyDeviceId()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("")
            ->sync();
    }

    public function testMissingChannels()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $this->pubnub->getConfiguration()->setUuid("sampleUUID");

        $remove = new RemovePushNotificationsFromChannelsExposed($this->pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->deviceId("")
            ->sync();
    }

    public function superCallTest()
    {
        $this->pubnub_pam->removeChannelsFromPush()
            ->channels(static::SPECIAL_CHARACTERS)
            ->deviceId(static::SPECIAL_CHARACTERS)
            ->sync();
    }
}


class RemoveChannelsFromPushTestExposed extends RemoveDeviceFromPush
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


class AddChannelsToPushExposed extends AddChannelsToPush
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


class RemovePushNotificationsFromChannelsExposed extends RemoveChannelsFromPush
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