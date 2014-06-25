<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class InstantiationTest extends \TestCase
{
    /**
     * @group instantiation
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing required $publish_key param
     */
    public function testMissingPublishKey()
    {
        new Pubnub();
    }

    /**
     * @group instantiation
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing required $publish_key param
     */
    public function testMissingPublishNamedKey()
    {
        new Pubnub(array(
            'subscribe_key' => 'demo'
        ));
    }

    /**
     * @group instantiation
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing required $subscribe_key param
     */
    public function testMissingSubscribeKey()
    {
        new Pubnub('demo');
    }

    /**
     * @group instantiation
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing required $subscribe_key param
     */
    public function testMissingSubscribeNamedKey()
    {
        new Pubnub(array(
            'publish_key' => 'demo'
        ));
    }
}