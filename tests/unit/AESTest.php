<?php

use Pubnub\Pubnub;
use Pubnub\PubnubAES;

class AESTest extends TestCase {

    /** @var  PubnubAES */
    protected $aes;

    protected static $test_plain_string_1 = "Pubnub Messaging API 1";
    protected static $test_plain_string_2 = "Pubnub Messaging API 2";
    protected static $test_plain_unicode_1 = '"漢語"';

    protected static $test_cipher_string_1 = 'dyeYNg3Ngd800QkdA0pSmuQnojMtZ2nndc7imcQL+FM=';
    protected static $test_cipher_string_2 = 'dyeYNg3Ngd800QkdA0pSmt8OvP22zLfuvcnlkGLvIIU=';
    protected static $test_cipher_unicode_1 = "WvztVJ5SPNOcwrKsDrGlWQ==";

    public function setUp()
    {
        parent::setUp();

        $this->aes = new PubnubAES();
    }

    /**
     * @group aes
     */
    public function testAESEncryption()
    {
        $this->assertEquals(static::$test_cipher_string_1, $this->aes->encrypt(static::$test_plain_string_1, 'enigma'));
        $this->assertEquals(static::$test_cipher_string_2, $this->aes->encrypt(static::$test_plain_string_2, 'enigma'));
        $this->assertEquals(static::$test_cipher_unicode_1, $this->aes->encrypt(static::$test_plain_unicode_1, 'enigma'));
    }

    /**
     * @group aes
     */
    public function testAESDecryption()
    {
        $this->assertEquals(static::$test_plain_string_1, $this->aes->decrypt(static::$test_cipher_string_1,'enigma'));
        $this->assertEquals(static::$test_plain_string_2, $this->aes->decrypt(static::$test_cipher_string_2,'enigma'));
        $this->assertEquals(static::$test_plain_unicode_1, $this->aes->decrypt(static::$test_cipher_unicode_1, 'enigma'));
    }

    /**
     * @group aes
     */
    public function testAESunpadPKCS7()
    {
        $this->assertEquals("", $this->aes->unpadPKCS7("",1));
    }
}
