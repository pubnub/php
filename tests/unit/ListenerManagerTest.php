<?php

//phpcs:disable
namespace PubNubTests\unit;

use PubNubTestCase;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Managers\ListenerManager;

class ListenerManagerTest extends PubNubTestCase
{
    public function testUrlEncode(): void
    {
        $listener = new ExposedListenerManager($this->pubnub);

        $l1 = new MySubscribeCallback();
        $l2 = new MySubscribeCallback();
        $l3 = new MySubscribeCallback();

        $listener->addListener($l1);
        $listener->addListener($l2);

        $this->assertEquals(2, $listener->count());

        $listener->removeListener($l3);

        $this->assertEquals(2, $listener->count());

        $listener->removeListener($l2);

        $this->assertEquals(1, $listener->count());

        $listener->removeListener($l1);

        $this->assertEquals(0, $listener->count());
    }
}


class ExposedListenerManager extends ListenerManager
{
    public function count(): int
    {
        return count($this->listeners);
    }
}

class MySubscribeCallback extends SubscribeCallback
{
    /**
     * @param \PubNub\PubNub $pubnub
     * @param \PubNub\Models\ResponseHelpers\PNStatus $status
     * @return mixed
     */
    function status($pubnub, $status): void
    {
        // TODO: Implement status() method.
    }

    /** @phpstan-ignore-next-line */
    function message($pubnub, $message): void
    {
        // TODO: Implement message() method.
    }

    /** @phpstan-ignore-next-line */
    function presence($pubnub, $presence): void
    {
        // TODO: Implement presence() method.
    }
}
