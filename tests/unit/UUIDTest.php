<?php

use Pubnub\Pubnub;


class UUIDTest extends TestCase
{
    protected static $message = 'Hello from uuid() test';
    protected static $channel = 'pubnub_php_test';

    public function testUUID()
    {
        $this->assertEquals(36, strlen($this->pubnub->uuid()));
    }
}
 