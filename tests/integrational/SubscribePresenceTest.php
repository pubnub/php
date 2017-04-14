<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\PubNub;
use PubNubTestCase;
use Tests\Helpers\StubTransport;


/**
 * Class SubscribePresenceTest
 *
 * This test is synchronous and use Stubs to simulate incoming messages about presence changes
 * @package Tests\Integrational
 */
class SubscribePresenceTest extends PubNubTestCase
{
    public function testMessageOnPresenceCallback() {
        $transport = new StubTransport();

        $transport->stubFor("/v2/presence/sub-key/demo/channel/blah/leave")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"status": 200, "action": "leave", "message": "OK", "service": "Presence"}');

        $transport->stubFor("/v2/subscribe/demo/blah,blah-pnpres/0")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}');

        $transport->stubFor("/v2/subscribe/demo/blah,blah-pnpres/0")
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "tt" => '14818963579052943',
                "tr" => "12"
            ])
            ->setResponseStatus("HTTP/1.0 200 OK")
            ->setResponseBody('{"t":{"t":"14818963588185526","r":12},"m":[{"a":"2","f":0,"p":{"t":"14818963587725382","r":2},"k":"demo","c":"blah-pnpres","d":{"action": "join", "timestamp": 1481896358, "uuid": "test-subscribe-listener", "occupancy": 1},"b":"blah-pnpres"}]}');

        $callback = new MySubscribeCallbackToTestPresence();

        $pubnub = PubNub::demo();
        $pubnub->getConfiguration()->setTransport($transport)->setUuid("myUUID");

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("blah")->withPresence()->execute();

        $this->assertTrue($callback->areBothConnectedAndDisconnectedInvoked());
        $this->assertEquals(3, $transport->requestsCount());
    }
}


class MySubscribeCallbackToTestPresence extends SubscribeCallback
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
    }

    function presence($pubnub, $presence)
    {
        throw new PubNubUnsubscribeException();
    }
}
