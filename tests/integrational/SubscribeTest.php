<?php

namespace Tests\Integrational;


use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

class SubscribeTest extends \PubNubTestCase
{
    public function testPublishDoNotStore()
    {
        $callback = new MyCallback();

        $this->pubnub->addListener($callback);
        $this->pubnub->subscribe()->channels("blah")->execute();
    }
}

class MyCallback extends SubscribeCallback
{

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
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