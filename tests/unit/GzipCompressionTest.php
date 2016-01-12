<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class GzipCompressionTest extends \TestCase
{
    protected static $message = 'Hello from publish() test!';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group gzip
     */
    public function testEnableGzipCompression()
    {
        $pubnub = new Pubnub(array_merge(static::$keys, array('gzip' => true)));

        $response = $pubnub->publish(static::$channel, static::$message);

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }
}
