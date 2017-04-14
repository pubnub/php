<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\Models\Server\SubscribeMessage;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;

class SubscribeWildCardTest extends PubNubTestCase
{
    public function testWildCard()
    {
        $transport = new StubTransport();

        $transport->stubFor("/v2/presence/sub-key/demo/channel/channels.%2A/leave")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"status": 200, "action": "leave", "message": "OK", "service": "Presence"}');

        $transport->stubFor("/v2/subscribe/demo/channels.%2A/0")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}');

        $transport->stubFor("/v2/subscribe/demo/channels.%2A/0")
            ->withQuery([
                "tt" => '14818963579052943',
                "tr" => "12",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},"m":[{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c","p":{"t":"14921661962867845","r":12},"k":"sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe","c":"channels.one","u":{},"d":{"text":"hey"},"b":"channels.*"}]}');

        $callback = new MySubscribeCallbackToTestWildCard();

        $pubnub = PubNub::demo();
        $pubnub->getConfiguration()->setTransport($transport)->setUuid("myUUID");

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("channels.*")->execute();

        $this->assertTrue($callback->areBothConnectedAndDisconnectedInvoked());
        $this->assertEquals(3, $transport->requestsCount());
    }
}


class MySubscribeCallbackToTestWildCard extends SubscribeCallback
{
    protected $connectedInvoked = false;
    protected $disconnectedInvoked = false;

    public function areBothConnectedAndDisconnectedInvoked()
    {
        return $this->connectedInvoked && $this->disconnectedInvoked;
    }

    function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $this->connectedInvoked = true;

        } else if ($status->getCategory() === PNStatusCategory::PNDisconnectedCategory) {
            $this->disconnectedInvoked = true;
        } else {
            if ($status->getException() !== null) {
                throw new \Exception($status->getException()->getMessage());
            } else {
                throw new \Exception("Unexpected status category: " . $status->getCategory());
            }
        }
    }

    /**
     * @param $pubnub
     * @param PNMessageResult $message
     * @throws PubNubUnsubscribeException
     */
    function message($pubnub, $message)
    {
        if ($message->getChannel() !== 'channels.one') {
            throw new \PHPUnit_Framework_AssertionFailedError("Actual channel " . $message->getChannel() . " doesn't match expected channels.one" );
        }

        if ($message->getSubscription() !== 'channels.*') {
            throw new \PHPUnit_Framework_AssertionFailedError("Actual subscription " . $message->getChannel() . " doesn't match expected channels.one" );
        }

        if ($message->getPublisher() !== 'eda482a8-9de3-4891-b328-b2c1d14f210c') {
            throw new \PHPUnit_Framework_AssertionFailedError("Actual uuid " . $message->getPublisher() . " doesn't match expected eda482a8-9de3-4891-b328-b2c1d14f210c");
        }

        throw new PubNubUnsubscribeException();
    }

    function presence($pubnub, $presence)
    {
    }
}
