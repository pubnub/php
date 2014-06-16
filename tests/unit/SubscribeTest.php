<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class SubscribeTest extends TestCase
{
    /**
     * @group subscribe
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing Subscribe Key in subscribe()
     */
    public function testMissingSubscribeKey()
    {
        $pubnub = new Pubnub('demo', false);
        $pubnub->subscribe('demo', function(){});
    }
}