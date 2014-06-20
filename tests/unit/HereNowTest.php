<?php

use Pubnub\Pubnub;
use Pubnub\PubnubException;

class HereNowTest extends TestCase
{
    protected static $channel = 'pubnub_php_test';

    /**
     * @group herenow
     */
    public function testHereNow()
    {
        $response = $this->pubnub->hereNow(static::$channel);

        $this->assertEquals('200', $response['status']);
        $this->assertEquals('Presence', $response['service']);
        $this->assertInternalType('array', $response['uuids']);
    }

    /**
     * @group herenow
     */
    public function testHereNowWithoutUUIDs()
    {
        $response = $this->pubnub->hereNow(static::$channel, true);

        $this->assertEquals('200', $response['status']);
        $this->assertEquals('Presence', $response['service']);
        $this->assertArrayNotHasKey('uuids', $response);
    }

    /**
     * @group herenow
     *
     * @expectedException \Pubnub\PubnubException
     * @expectedExceptionMessage Missing Channel in hereNow()
     */
    public function testHereNowEmptyChannel()
    {
        $this->pubnub->hereNow('');
    }
}
 