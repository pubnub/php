<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class SSLTest extends \TestCase
{
    protected $pubnub_sec;
    protected static $message = 'Hello from publish() test!';
    protected static $channel = 'pubnub_php_test';

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_sec = new Pubnub(array(
            'subscribe_key' => 'demo',
            'publish_key' => 'demo',
            'origin' => 'pubsub.pubnub.com',
            'ssl' => true,
            'pem_path' => "."
        ));
    }

    /**
     * @group ssl
     */

    public function testPublishToSecureConnection()
    {
        $response = $this->pubnub_sec->publish(static::$channel, static::$message);

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }
}
