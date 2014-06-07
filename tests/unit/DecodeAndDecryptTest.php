<?php

use Pubnub\Pubnub;


class DecodeAndDecryptTest extends TestCase
{
    protected static $message = 'Hello from time() test';
    protected static $channel = 'pubnub_php_test';

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new Pubnub(array(
            'subscribe_key' => 'demo',
            'publish_key' => 'demo',
            'origin' => 'pubsub.pubnub.com',
            'cipher_key' => 'enigma'
        ));
    }

    /**
     * @group decodeanddecrypt
     */
    public function testDADDefault()
    {
        $test_cipher_string_1 = 'f42pIQcWZ9zbTbH8cyLwByD/GsviOE0vcREIEVPARR0=';
        $test_plain_string_1 = 'Pubnub Messaging API 1';
        $test_cipher_string_2 = 'f42pIQcWZ9zbTbH8cyLwB/tdvRxjFLOYcBNMVKeHS54=';
        $test_plain_string_2 = 'Pubnub Messaging API 2';

        $response = $this->pubnub->decodeAndDecrypt(array(
            $test_cipher_string_1,
            $test_cipher_string_2

        ), 'default');

        $this->assertEquals($test_plain_string_1, $response[0]);
        $this->assertEquals($test_plain_string_2, $response[1]);
    }

    /**
     * @group decodeanddecrypt
     */
    public function testDADPresence()
    {
        $test_cipher_string_1 = 'f42pIQcWZ9zbTbH8cyLwByD/GsviOE0vcREIEVPARR0=';
        $test_cipher_string_2 = 'f42pIQcWZ9zbTbH8cyLwB/tdvRxjFLOYcBNMVKeHS54=';

        $response = $this->pubnub->decodeAndDecrypt(array(
            $test_cipher_string_1,
            $test_cipher_string_2
        ), 'presence');

        $this->assertEquals($test_cipher_string_1, $response[0]);
        $this->assertEquals($test_cipher_string_2, $response[1]);
    }
}
 