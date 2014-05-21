<?php

use Pubnub\Pubnub;

class SendMessageTest extends TestCase
{
    protected $pubnub_enc;
    protected static $message = 'Hello from publishString() test';
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
    }

    public function testSendMessageEnc()
    {
        $messageToSendEnc = $this->pubnub_enc->sendMessage(static::$message);
        $this->assertGreaterThan(strlen(static::$message), strlen($messageToSendEnc));
        $this->assertEquals(static::$message, json_decode($this->pubnub_enc->AES->decrypt($messageToSendEnc, 'enigma')));
    }

    public function testSendMessageRaw()
    {
        $messageToSend = $this->pubnub->sendMessage(static::$message);
        $this->assertGreaterThan(strlen(static::$message), strlen($messageToSend));
        $this->assertEquals(static::$message, json_decode($messageToSend));
    }
}
 