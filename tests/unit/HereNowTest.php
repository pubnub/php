<?php

use Pubnub\Pubnub;
use Pubnub\PubnubException;

class HereNowTest extends TestCase
{
    protected static $message = 'Hello from here_now() test';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group herenow
     */
    public function testHereNow()
    {
        $response = $this->pubnub->hereNow(static::$channel);
        $this->assertEquals('200', $response['status']);
        $this->assertEquals('Presence', $response['service']);
    }

    /**
     * @group herenow
     */
    public function testHereNowEmptyChannel()
    {
        $this->setExpectedException('\Pubnub\PubnubException');
        $this->pubnub->hereNow('');
    }
}
 