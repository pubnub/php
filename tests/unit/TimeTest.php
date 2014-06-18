<?php

use Pubnub\Pubnub;


class TimeTest extends TestCase
{
    protected static $message = 'Hello from time() test';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group time
     */
    public function testTime()
    {
        $time = $this->pubnub->time();
        $this->assertGreaterThan(10, strlen($time));
        $this->assertGreaterThan(1401219180, (int) $time);
    }
}
 