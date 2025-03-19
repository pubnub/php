<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNubTestCase;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

/**
 * Class SubscribePresenceTest
 *
 * This test is synchronous and use Stubs to simulate incoming messages about presence changes
 * @package Tests\Integrational
 */
class SubscribePresenceTest extends PubNubTestCase
{
    public function testMessageOnPresenceCallback()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);

        $client = new PsrStubClient();
        $pubnub->setClient($client);

        $client->addStub((new PsrStub("/v2/presence/sub-key/demo/channel/blah/leave"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"status": 200, "action": "leave", "message": "OK", "service": "Presence"}'));

        $client->addStub((new PsrStub("/v2/subscribe/demo/blah,blah-pnpres/0"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}'));

            $client->addStub((new PsrStub("/v2/subscribe/demo/blah,blah-pnpres/0"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID",
                "tt" => '14818963579052943',
                "tr" => "12"
            ])
            ->setResponseBody('{"t":{"t":"14818963588185526","r":12},"m":[{"a":"2","f":0,'
                . '"p":{"t":"14818963587725382","r":2},"k":"demo","c":"blah-pnpres",'
                . '"d":{"action": "join", "timestamp": 1481896358, "uuid": "test-subscribe-listener", "occupancy": 1},'
                . '"b":"blah-pnpres"}]}'));

        $callback = new MySubscribeCallbackToTestPresence();

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("blah")->withPresence()->execute();

        $this->assertTrue($callback->areBothConnectedAndDisconnectedInvoked());
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class MySubscribeCallbackToTestPresence extends SubscribeCallback
{
    protected $connectedInvoked = false;
    protected $disconnectedInvoked = false;

    public function areBothConnectedAndDisconnectedInvoked()
    {
        return $this->connectedInvoked && $this->disconnectedInvoked;
    }

    public function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            $this->connectedInvoked = true;
        } elseif ($status->getCategory() === PNStatusCategory::PNDisconnectedCategory) {
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
    public function message($pubnub, $message)
    {
    }

    public function presence($pubnub, $presence)
    {
        throw new PubNubUnsubscribeException();
    }
}
