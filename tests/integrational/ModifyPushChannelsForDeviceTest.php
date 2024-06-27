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
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
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

    public function testFCMSuccessRemoveAll()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
            ->withQuery([
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listRemove->pushType(PNPushType::FCM)
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult::class, $response);
    }

    public function testMicrosoftSuccessRemoveAll()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
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
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setAuthKey('myKey');
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->stubFor("/v1/push/sub-key/demo/devices/coolDevice/remove")
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
        $this->expectException(\TypeError::class);
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey("");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullPushTypeRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->sync();
    }

    public function testEmptyDeviceIdRemoveAll()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listRemove = new RemoveChannelsFromPushTestExposed($pubnub);

        $listRemove->pushType(PNPushType::MPNS)
            ->deviceId("")
            ->sync();
    }

    public function testAddAppleSuccess()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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

    public function testAddFCMSuccessSync()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "add" => "ch1,ch2,ch3",
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $listAdd->pushType(PNPushType::FCM)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushAddChannelResult::class, $response);
    }

    public function testAddMicrosoftSuccessSync()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);

        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setAuthKey("myKey");
        $pubnub = new PubNub($config);

        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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
        $this->expectException(\TypeError::class);

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);

        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey('');
        $pubnub = new PubNub($config);
        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullPushTypeAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Push Type is missing");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testNullDeviceIdAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->sync();
    }

    public function testEmptyDeviceIdAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Device ID is missing for push operation");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("")
            ->sync();
    }

    public function testMissingChannelsAdd()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $listAdd = new AddChannelsToPushExposed($pubnub);

        $listAdd->pushType(PNPushType::MPNS)
            ->deviceId("Example")
            ->sync();
    }

    public function testAppleSuccessRemove()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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

    public function testFCMSuccessRemove()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
            ->withQuery([
                "remove" => "ch1,ch2,ch3",
                "type" => "gcm",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "sampleUUID"
            ])
            ->setResponseBody("[1, \"Modified Channels\"]");

        $response = $remove->pushType(PNPushType::FCM)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();

        $this->assertInstanceOf(\PubNub\Models\Consumer\Push\PNPushRemoveChannelResult::class, $response);
    }

    public function testMicrosoftSuccessRemove()
    {
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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
        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setAuthKey("myKey");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->stubFor("/v1/push/sub-key/demo/devices/coolDevice")
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
        $this->expectException(\TypeError::class);

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey(null);
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("coolDevice")
            ->sync();
    }

    public function testEmptySubscribeKeyRemove()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Subscribe Key not configured");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $config->setSubscribeKey('');
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);


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

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

        $remove->pushType(PNPushType::MPNS)
            ->channels(["ch1", "ch2", "ch3"])
            ->deviceId("")
            ->sync();
    }

    public function testMissingChannels()
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage("Channel missing");

        $config = $this->config->clone();
        $config->setUuid("sampleUUID");
        $pubnub = new PubNub($config);
        $remove = new RemovePushNotificationsFromChannelsExposed($pubnub);

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

// phpcs:ignore PSR1.Classes.ClassDeclaration
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

// phpcs:ignore PSR1.Classes.ClassDeclaration
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

// phpcs:ignore PSR1.Classes.ClassDeclaration
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
