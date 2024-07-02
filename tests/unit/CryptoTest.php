<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;

class CryptoTest extends TestCase
{
    /**
     * @group crypto
     *
     * @requires extension openssl
     */
    public function testOpenSSLAES()
    {
        $logger = new \Monolog\Logger('CryptoTest');
        $toDecode = "QfD1NCBJCmt1aPPGU2cshw==";
        $key = "testKey";
        $crypto = new \PubNub\PubNubCrypto($key, false);
        $hey = "

        dfjn
        t564

        sdfhp\n
        ";


        $this->assertEquals($hey, $crypto->decrypt($crypto->encrypt($hey), $logger));

        // NOTICE: The original encoded message is wrapped into quotes which are stripped out inside the decrypt method
        $this->assertEquals("hey-0", $crypto->decrypt($toDecode, $logger));
    }

    /**
     * @group crypto
     *
     * @requires extension openssl
     */
    public function testOpenSSLAESRandomIV()
    {
        $logger = new \Monolog\Logger('CryptoTest');
        $toDecode = "6y+vBnIds5znZOL8htyCJy0Wno4rKxs7ILwbWeF/AwamGqzTC+moces4/HOSVJyK";
        $key = "testKey";
        $crypto = new \PubNub\PubNubCrypto($key, true);
        $hey = "

        dfjn
        t564

        sdfhp\n
        ";


        $this->assertEquals($hey, $crypto->decrypt($crypto->encrypt($hey), $logger));

        // NOTICE: The original encoded message is wrapped into quotes which are stripped out inside the decrypt method
        $decrypted = $crypto->decrypt($toDecode, $logger);

        $this->assertObjectHasProperty("hi", $decrypted);
        $this->assertEquals("hello world", $decrypted->hi);
    }

    /**
     * @group crypto
     *
     * @requires extension mcrypt
     */
    public function testMcryptAES()
    {
        $logger = new \Monolog\Logger('CryptoTest');
        $toDecode = "QfD1NCBJCmt1aPPGU2cshw==";
        $key = "testKey";
        $crypto = new \PubNub\PubNubCryptoLegacy($key, false);
        $hey = "

        dfjn
        t564

        sdfhp\n
        ";


        $this->assertEquals($hey, $crypto->decrypt($crypto->encrypt($hey)));

        // NOTICE: The original encoded message is wrapped into quotes which are stripped out inside the decrypt method
        $this->assertEquals("hey-0", $crypto->decrypt($toDecode));
    }
}
