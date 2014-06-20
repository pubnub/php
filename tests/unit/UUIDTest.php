<?php

use Pubnub\Pubnub;


class UUIDTest extends TestCase
{
    protected static $message = 'Hello from uuid() test';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group uuid
     */
    public function testUUID()
    {
        $this->assertEquals(36, strlen($this->pubnub->uuid()));
    }

    /**
     * @group uuid
     */
    public function testSetAndGetUUID()
    {
        $uuid = 'uglyUUID';
        $this->pubnub->setUUID($uuid);

        $this->assertEquals($uuid, $this->pubnub->getUUID());
    }
}
 