<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class SubscribeSignalTest extends PubNubTestCase
{
    public function testSignal()
    {
        $transport = new StubTransport();

        $transport->stubFor("/v2/subscribe/demo/test/0")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}');

        $transport->stubFor("/v2/subscribe/demo/test/0")
            ->withQuery([
                "tt" => '14818963579052943',
                "tr" => "12",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},'
                . '"m":[{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c",'
                . '"p":{"t":"14921661962867845","r":12},"k":"demo","e":1,"c":"test","u":{},'
                . '"d":{"text":"hey"},"b":"test"}]}');

        $callback = new MySubscribeCallbackToTestSignal();

        $config = $this->config->clone();
        $config->setTransport($transport);
        $config->setUuid("myUUID");

        $pubnub = new PubNub($config);

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("test")->execute();

        $this->assertTrue($callback->signalInvoked);
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
