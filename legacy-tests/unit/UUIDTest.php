<?php

require_once('./TestCase.php');


class UUIDTest extends TestCase
{
    protected static $message = 'Hello from uuid() test';
    protected static $channel = 'pubnub_php_test';

    public function testUUID()
    {
        $this->assertEquals(36, strlen($this->pubnub->uuid()));
    }

    public function testSetAndGetUUID()
    {
        $uuid = 'uglyUUID';
        $this->pubnub->setUUID($uuid);

        $this->assertEquals($uuid, $this->pubnub->getUUID());
    }
}
 