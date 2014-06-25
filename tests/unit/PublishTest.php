<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class PublishTest extends TestCase
{

    protected $pubnub_enc;
    protected $pubnub_sec;
    protected static $message = 'Hello from publish() test!';
    protected static $channel = 'pubnub_php_test';

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_enc = new Pubnub(array(
            'subscribe_key' => 'demo',
            'publish_key' => 'demo',
            'origin' => 'pubsub.pubnub.com',
            'cipher_key' => 'enigma'
        ));

        $this->pubnub_sec = new Pubnub(array(
            'subscribe_key' => 'demo',
            'publish_key' => 'demo',
            'origin' => 'pubsub.pubnub.com',
            'secret_key' => 'sec-c-YjFmNzYzMGMtYmI3NC00NzJkLTlkYzYtY2MwMzI4YTJhNDVh'
        ));
    }

    /**
     * @group publish
     */

    public function testPublish()
    {
        $response = $this->pubnub->publish(static::$channel, static::$message);

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }

    /**
     * @group publish
     */
    public function testEncryptedPublish()
    {
        $response = $this->pubnub_enc->publish(static::$channel, static::$message);

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }

    /**
     * @group publish
     */
    public function testPublishSecretKey()
    {
        $response = $this->pubnub_sec->publish(static::$channel, static::$message);

        $this->assertEquals(1, $response[0]);
        $this->assertEquals('Sent', $response[1]);
        $this->assertGreaterThan(1400688897 * 10000000, $response[2]);
    }

    /**
     * @group publish
     */
    public function testInvalidChannelPublish()
    {
        $this->setExpectedException('\Pubnub\PubnubException');
        $this->pubnub->publish('', '');
    }
}