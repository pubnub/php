<?php


use PubNub\Callbacks\SubscribeCallback;
use PubNub\Managers\ListenerManager;

class ListenerManagerTest extends PubNubTestCase
{
    public function testUrlEncode()
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
    public function count()
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
    function status($pubnub, $status)
    {
        // TODO: Implement status() method.
    }

    function message($pubnub, $message)
    {
        // TODO: Implement message() method.
    }

    function presence($pubnub, $presence)
    {
        // TODO: Implement presence() method.
    }
}