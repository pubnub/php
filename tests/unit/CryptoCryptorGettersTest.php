<?php

use PHPUnit\Framework\TestCase;
use PubNub\Crypto\AesCbcCryptor;
use PubNub\Crypto\LegacyCryptor;

class CryptoCryptorGettersTest extends TestCase
{
    // ============================================================================
    // AesCbcCryptor TESTS
    // ============================================================================

    public function testAesCbcCryptorConstructor(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        
        $this->assertInstanceOf(AesCbcCryptor::class, $cryptor);
    }

    public function testAesCbcCryptorGetCipherKey(): void
    {
        $cryptor = new AesCbcCryptor('my-cipher-key');
        
        $this->assertEquals('my-cipher-key', $cryptor->getCipherKey());
    }

    public function testAesCbcCryptorGetCipherKeyWithNullParameter(): void
    {
        $cryptor = new AesCbcCryptor('my-cipher-key');
        
        $this->assertEquals('my-cipher-key', $cryptor->getCipherKey(null));
    }

    public function testAesCbcCryptorGetCipherKeyWithCustomParameter(): void
    {
        $cryptor = new AesCbcCryptor('default-key');
        
        // If a custom key is provided, it should return that instead
        $this->assertEquals('custom-key', $cryptor->getCipherKey('custom-key'));
    }

    public function testAesCbcCryptorGetIV(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $iv = $cryptor->getIV();
        
        $this->assertIsString($iv);
        $this->assertEquals(16, strlen($iv)); // IV should be 16 bytes
    }

    public function testAesCbcCryptorGetIVReturnsRandomValues(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $iv1 = $cryptor->getIV();
        $iv2 = $cryptor->getIV();
        
        // Each call should return a different random IV
        $this->assertNotEquals($iv1, $iv2);
    }

    public function testAesCbcCryptorEncryptReturnsPayload(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $result = $cryptor->encrypt('test message');
        
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $result);
    }

    public function testAesCbcCryptorEncryptPayloadHasCryptorId(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $result = $cryptor->encrypt('test message');
        
        $this->assertEquals('ACRH', $result->getCryptorId());
    }

    public function testAesCbcCryptorEncryptPayloadHasData(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $result = $cryptor->encrypt('test message');
        
        $this->assertNotEmpty($result->getData());
    }

    public function testAesCbcCryptorEncryptPayloadHasCryptorData(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $result = $cryptor->encrypt('test message');
        
        // Cryptor data should contain the IV (16 bytes)
        $this->assertEquals(16, strlen($result->getCryptorData()));
    }

    public function testAesCbcCryptorEncryptDecryptRoundTrip(): void
    {
        $cryptor = new AesCbcCryptor('test-key');
        $original = 'Hello, World!';
        
        $encrypted = $cryptor->encrypt($original);
        $decrypted = $cryptor->decrypt($encrypted);
        
        $this->assertEquals($original, $decrypted);
    }

    public function testAesCbcCryptorWithDifferentKeys(): void
    {
        $cryptor1 = new AesCbcCryptor('key1');
        $cryptor2 = new AesCbcCryptor('key2');
        
        $this->assertEquals('key1', $cryptor1->getCipherKey());
        $this->assertEquals('key2', $cryptor2->getCipherKey());
    }

    // ============================================================================
    // LegacyCryptor TESTS
    // ============================================================================

    public function testLegacyCryptorConstructor(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        
        $this->assertInstanceOf(LegacyCryptor::class, $cryptor);
    }

    public function testLegacyCryptorConstructorWithRandomIV(): void
    {
        $cryptor = new LegacyCryptor('test-key', true);
        
        $this->assertInstanceOf(LegacyCryptor::class, $cryptor);
    }

    public function testLegacyCryptorGetCipherKey(): void
    {
        $cryptor = new LegacyCryptor('my-legacy-key', false);
        
        $this->assertEquals('my-legacy-key', $cryptor->getCipherKey());
    }

    public function testLegacyCryptorGetIVWithStaticIV(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $iv = $cryptor->getIV();
        
        $this->assertIsString($iv);
        $this->assertEquals(16, strlen($iv)); // IV should be 16 bytes
        $this->assertEquals('0123456789012345', $iv); // Static IV
    }

    public function testLegacyCryptorGetIVWithStaticIVIsConsistent(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $iv1 = $cryptor->getIV();
        $iv2 = $cryptor->getIV();
        
        // With static IV, should return same value every time
        $this->assertEquals($iv1, $iv2);
        $this->assertEquals('0123456789012345', $iv1);
    }

    public function testLegacyCryptorGetIVWithRandomIV(): void
    {
        $cryptor = new LegacyCryptor('test-key', true);
        $iv = $cryptor->getIV();
        
        $this->assertIsString($iv);
        $this->assertEquals(16, strlen($iv)); // IV should be 16 bytes
    }

    public function testLegacyCryptorGetIVWithRandomIVReturnsRandomValues(): void
    {
        $cryptor = new LegacyCryptor('test-key', true);
        $iv1 = $cryptor->getIV();
        $iv2 = $cryptor->getIV();
        
        // With random IV, each call should return different value
        $this->assertNotEquals($iv1, $iv2);
    }

    public function testLegacyCryptorEncryptReturnsPayload(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $result = $cryptor->encrypt('test message');
        
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $result);
    }

    public function testLegacyCryptorEncryptPayloadHasCryptorId(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $result = $cryptor->encrypt('test message');
        
        $this->assertEquals('0000', $result->getCryptorId());
    }

    public function testLegacyCryptorEncryptPayloadHasData(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $result = $cryptor->encrypt('test message');
        
        $this->assertNotEmpty($result->getData());
    }

    public function testLegacyCryptorEncryptPayloadCryptorDataIsEmpty(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $result = $cryptor->encrypt('test message');
        
        // Legacy cryptor uses empty string for cryptor data
        $this->assertEquals('', $result->getCryptorData());
    }

    public function testLegacyCryptorEncryptDecryptRoundTripWithStaticIV(): void
    {
        $cryptor = new LegacyCryptor('test-key', false);
        $original = 'Hello, Legacy!';
        
        $encrypted = $cryptor->encrypt($original);
        $decrypted = $cryptor->decrypt($encrypted);
        
        $this->assertEquals($original, $decrypted);
    }

    public function testLegacyCryptorEncryptDecryptRoundTripWithRandomIV(): void
    {
        $cryptor = new LegacyCryptor('test-key', true);
        $original = 'Hello, Random IV!';
        
        $encrypted = $cryptor->encrypt($original);
        $decrypted = $cryptor->decrypt($encrypted);
        
        $this->assertEquals($original, $decrypted);
    }

    public function testLegacyCryptorWithDifferentKeys(): void
    {
        $cryptor1 = new LegacyCryptor('key1', false);
        $cryptor2 = new LegacyCryptor('key2', true);
        
        $this->assertEquals('key1', $cryptor1->getCipherKey());
        $this->assertEquals('key2', $cryptor2->getCipherKey());
    }

    // ============================================================================
    // COMPARISON TESTS
    // ============================================================================

    public function testAesCbcCryptorAndLegacyCryptorHaveDifferentCryptorIds(): void
    {
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $aesEncrypted = $aesCbc->encrypt('test');
        $legacyEncrypted = $legacy->encrypt('test');
        
        $this->assertEquals('ACRH', $aesEncrypted->getCryptorId());
        $this->assertEquals('0000', $legacyEncrypted->getCryptorId());
        $this->assertNotEquals($aesEncrypted->getCryptorId(), $legacyEncrypted->getCryptorId());
    }

    public function testBothCryptorsReturnPayloadObjects(): void
    {
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $aesResult = $aesCbc->encrypt('test');
        $legacyResult = $legacy->encrypt('test');
        
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $aesResult);
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $legacyResult);
    }

    public function testBothCryptorsHandleEmptyStrings(): void
    {
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $aesResult = $aesCbc->encrypt('');
        $legacyResult = $legacy->encrypt('');
        
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $aesResult);
        $this->assertInstanceOf(\PubNub\Crypto\Payload::class, $legacyResult);
    }

    public function testBothCryptorsHandleLongMessages(): void
    {
        $longMessage = str_repeat('A', 10000); // 10KB message
        
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $aesEncrypted = $aesCbc->encrypt($longMessage);
        $legacyEncrypted = $legacy->encrypt($longMessage);
        
        $this->assertEquals($longMessage, $aesCbc->decrypt($aesEncrypted));
        $this->assertEquals($longMessage, $legacy->decrypt($legacyEncrypted));
    }

    public function testBothCryptorsHandleUnicodeCharacters(): void
    {
        $unicodeMessage = '🔒 Encrypted 文字 مشفر';
        
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $aesEncrypted = $aesCbc->encrypt($unicodeMessage);
        $legacyEncrypted = $legacy->encrypt($unicodeMessage);
        
        $this->assertEquals($unicodeMessage, $aesCbc->decrypt($aesEncrypted));
        $this->assertEquals($unicodeMessage, $legacy->decrypt($legacyEncrypted));
    }

    public function testBothCryptorsHaveGetCipherKeyMethod(): void
    {
        $aesCbc = new AesCbcCryptor('key1');
        $legacy = new LegacyCryptor('key2', false);
        
        $this->assertTrue(method_exists($aesCbc, 'getCipherKey'));
        $this->assertTrue(method_exists($legacy, 'getCipherKey'));
        
        $this->assertEquals('key1', $aesCbc->getCipherKey());
        $this->assertEquals('key2', $legacy->getCipherKey());
    }

    public function testBothCryptorsHaveGetIVMethod(): void
    {
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $this->assertTrue(method_exists($aesCbc, 'getIV'));
        $this->assertTrue(method_exists($legacy, 'getIV'));
        
        $this->assertEquals(16, strlen($aesCbc->getIV()));
        $this->assertEquals(16, strlen($legacy->getIV()));
    }

    public function testBothCryptorsImplementEncryptAndDecrypt(): void
    {
        $aesCbc = new AesCbcCryptor('key');
        $legacy = new LegacyCryptor('key', false);
        
        $this->assertTrue(method_exists($aesCbc, 'encrypt'));
        $this->assertTrue(method_exists($aesCbc, 'decrypt'));
        $this->assertTrue(method_exists($legacy, 'encrypt'));
        $this->assertTrue(method_exists($legacy, 'decrypt'));
    }
}
