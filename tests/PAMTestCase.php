<?php

use Pubnub\Pubnub;

abstract class PAMTestCase extends PHPUnit_Framework_TestCase {
    protected $pubnub_secret;

    protected static $publish = 'pub-c-81d9633a-c5a0-4d6c-9600-fda148b61648';
    protected static $subscribe = 'sub-c-35ffee42-e763-11e3-afd8-02ee2ddab7fe';
    protected static $secret = 'sec-c-NDNlODA0ZmItNzZhMC00OTViLWI5NWMtM2M4MzA4ZWM2ZjIz';
    protected static $access_key = 'abcd';

    protected $channel;

    public function setUp()
    {
        parent::setUp();

        $this->channel = 'pubnub_php_test_pam_' . phpversion() . time();

        $this->pubnub_secret = new Pubnub(array(
            'origin' => 'pubsub.pubnub.com',
            'subscribe_key' => static::$subscribe,
            'publish_key' => static::$publish,
            'secret_key' => static::$secret
        ));
    }
}