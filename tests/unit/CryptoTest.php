<?php

use PHPUnit\Framework\TestCase;


class CryptoTest extends TestCase
{
    /**
     * @group crypto
     *
     * @requires extension openssl
     */
    public function testOpenSSL_AES()
    {
        $toDecode = "QfD1NCBJCmt1aPPGU2cshw==";
        $key = "testKey";
        $crypto = new \PubNub\PubNubCrypto($key);
        $hey = "

        dfjn
        t564

        sdfhp\n
        ";


        $this->assertEquals($hey, $crypto->decrypt($crypto->encrypt($hey)));

        // NOTICE: The original encoded message is wrapped into quotes which are stripped out inside the decrypt method
        $this->assertEquals("hey-0", $crypto->decrypt($toDecode));
    }

    /**
     * @group crypto
     *
     * @requires extension mcrypt
     */
    public function testMcrypt_AES()
    {
        $toDecode = "QfD1NCBJCmt1aPPGU2cshw==";
        $key = "testKey";
        $crypto = new \PubNub\PubNubCryptoLegacy($key);
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
