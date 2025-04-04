<?php

namespace Tests\Integrational;

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\PubNub;
use PubNubTestCase;
use PHPUnit\Framework\AssertionFailedError;
use PubNub\PNConfiguration;
use PubNubTests\helpers\PsrStub;
use PubNubTests\helpers\PsrStubClient;

class SubscribeWildCardTest extends PubNubTestCase
{
    public function testWildCard()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("myUUID");
        $pubnub = new PubNub($config);

        $client = new PsrStubClient();
        $pubnub->setClient($client);

        $client->addStub((new PsrStub("/v2/presence/sub-key/demo/channel/channels.%2A/leave"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"status": 200, "action": "leave", "message": "OK", "service": "Presence"}'));

        $client->addStub((new PsrStub("/v2/subscribe/demo/channels.%2A/0"))
            ->withQuery([
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14818963579052943","r":12},"m":[]}'));

        $client->addStub((new PsrStub("/v2/subscribe/demo/channels.%2A/0"))
            ->withQuery([
                "tt" => '14818963579052943',
                "tr" => "12",
                "pnsdk" => $this->encodedSdkName,
                "uuid" => "myUUID"
            ])
            ->setResponseBody('{"t":{"t":"14921661962885137","r":12},'
                . '"m":[{"a":"5","f":0,"i":"eda482a8-9de3-4891-b328-b2c1d14f210c",'
                . '"p":{"t":"14921661962867845","r":12},"k":"demo","c":"channels.one","u":{},'
                . '"d":{"text":"hey"},"b":"channels.*"}]}'));

        $callback = new MySubscribeCallbackToTestWildCard();

        $pubnub->addListener($callback);
        $pubnub->subscribe()->channel("channels.*")->execute();

        $this->assertTrue($callback->areBothConnectedAndDisconnectedInvoked());
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
class MySubscribeCallbackToTestWildCard extends SubscribeCallback
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
        if ($message->getChannel() !== 'channels.one') {
            throw new AssertionFailedError("Actual channel " . $message->getChannel()
            . " doesn't match expected channels.one");
        }

        if ($message->getSubscription() !== 'channels.*') {
            throw new AssertionFailedError("Actual subscription " . $message->getChannel()
            . " doesn't match expected channels.one");
        }

        if ($message->getPublisher() !== 'eda482a8-9de3-4891-b328-b2c1d14f210c') {
            throw new AssertionFailedError("Actual uuid " . $message->getPublisher()
            . " doesn't match expected eda482a8-9de3-4891-b328-b2c1d14f210c");
        }

        throw new PubNubUnsubscribeException();
    }

    public function presence($pubnub, $presence)
    {
    }
}
