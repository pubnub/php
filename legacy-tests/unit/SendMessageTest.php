<?php

require_once 'TestCase.php';

class SendMessageTest extends TestCase
{
    /** @var  Pubnub */
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

    /**
     * @group sendmessage
     */
    public function testSendMessageEnc()
    {
        $messageToSendEnc = $this->pubnub_enc->sendMessage(self::$message);
        $this->assertTrue(strlen($messageToSendEnc) > strlen(self::$message));
        $this->assertEquals(self::$message, json_decode($this->pubnub_enc->AES->decrypt($messageToSendEnc, 'enigma')));
    }

    /**
     * @group sendmessage
     */
    public function testSendMessageRaw()
    {
        $messageToSend = $this->pubnub->sendMessage(self::$message);
        $this->assertTrue(strlen($messageToSend) > strlen(self::$message));
        $this->assertEquals(self::$message, json_decode($messageToSend));
    }
}
 