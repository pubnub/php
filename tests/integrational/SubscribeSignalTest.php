<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Models\Consumer\PubSub\PNSignalMessageResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class SubscribeSignalTest extends PubNubTestCase
{
    public function testSignal()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);

        $client = new PsrStubClient();
        $pubnub->setClient($client);

        $client->addStub((new PsrStub("/v2/subscribe/demo/test/0"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}'));

        $client->addStub((new PsrStub("/v2/subscribe/demo/test/0"))
            ->withQuery([
                "tt" => '14818963579052943',
                "tr" => "12",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},'
                . '"m":[{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c",'
                . '"p":{"t":"14921661962867845","r":12},"k":"demo","e":1,"c":"test","u":{},'
                . '"d":{"text":"hey"},"b":"test"}]}'));

        $callback = new MySubscribeCallbackToTestSignal();

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("test")->execute();

        $this->assertTrue($callback->signalInvoked);
    }

    public function testSignalIsNotDecryptedWhenCryptoIsConfigured(): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUUID");
        $config->setCipherKey("testCipher");
        $pubnub = new PubNub($config);

        $ciphertext = $config->getCrypto()->encrypt('"hey"');
        $encodedCiphertext = json_encode($ciphertext);

        $client = new PsrStubClient();
        $pubnub->setClient($client);

        $client->addStub((new PsrStub("/v2/subscribe/demo/test/0"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}'));

        $client->addStub((new PsrStub("/v2/subscribe/demo/test/0"))
            ->withQuery([
                "tt" => '14818963579052943',
                "tr" => "12",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},"m":['
                . '{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c",'
                . '"p":{"t":"14921661962867845","r":12},"k":"demo","e":1,"c":"test","u":{},'
                . '"d":' . $encodedCiphertext . ',"b":"test"},'
                . '{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c",'
                . '"p":{"t":"14921661962867846","r":12},"k":"demo","c":"test","u":{},'
                . '"d":' . $encodedCiphertext . ',"b":"test"}]}'));

        $callback = new MySubscribeCallbackToTestSignalDecryption();

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("test")->execute();

        $this->assertNotNull($callback->signal, 'the signal must be delivered');
        $this->assertSame($ciphertext, $callback->signal->getMessage());
        $this->assertFalse($callback->signal->isError());

        $this->assertNotNull($callback->message, 'the regular message must be delivered');
        $this->assertSame('hey', $callback->message->getMessage());
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class MySubscribeCallbackToTestSignalDecryption extends SubscribeCallback
{
    public ?PNSignalMessageResult $signal = null;

    public ?PNMessageResult $message = null;

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     */
    public function status($pubnub, $status): void
    {
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     */
    public function message($pubnub, $message): void
    {
        $this->message = $message;
    }

    /**
     * @param PubNub $pubnub
     * @param PNPresenceEventResult $presence
     */
    public function presence($pubnub, $presence): void
    {
    }

    /**
     * @param PubNub $pubnub
     * @param PNSignalMessageResult $signal
     */
    public function signal($pubnub, $signal): void
    {
        $this->signal = $signal;
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class MySubscribeCallbackToTestSignal extends SubscribeCallback
{
    public $signalInvoked = false;

    public function status($pubnub, $status)
    {
    }

    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
    }

    public function signal($pubnub, $signal)
    {
        $this->signalInvoked = true;
    }
}
