<?php

namespace PubNubTests\unit\CryptoModule;

use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Crypto\AesCbcCryptor;
use PubNub\Crypto\LegacyCryptor;
use PubNub\Crypto\Header as CryptoHeader;

/**
 * Regression tests for CryptoModule::decodeHeader and the encrypt/decrypt round
 * trip (which exercises the padding/unpadding fix in PaddingTrait).
 *
 * The header length guard previously read `strlen($header < 10)`, which took the
 * length of a boolean and never checked the real header length. It is now
 * `strlen($header) < 10`. These tests pin the corrected behaviour and confirm
 * that both the AES-CBC and legacy round trips (incl. the fallback header path)
 * still work end to end.
 */
class DecodeHeaderTest extends TestCase
{
    protected string $cipherKey = "myCipherKey";
    protected string $message = "Hello world, this is a PubNub crypto regression test payload.";

    public function testShortHeaderReturnsFallback(): void
    {
        // Fewer than 10 bytes: must fall back regardless of the (partial) sentinel.
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, true);
        $header = $module->decodeHeader('PNED');

        $this->assertEquals(CryptoModule::FALLBACK_CRYPTOR_ID, $header->getCryptorId());
        $this->assertEquals(0, $header->getLength());
        $this->assertEquals('', $header->getSentinel());
    }

    public function testEmptyHeaderReturnsFallback(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, true);
        $header = $module->decodeHeader('');

        $this->assertEquals(CryptoModule::FALLBACK_CRYPTOR_ID, $header->getCryptorId());
        $this->assertEquals(0, $header->getLength());
    }

    public function testLongHeaderWithoutSentinelReturnsFallback(): void
    {
        // At least 10 bytes but no PNED sentinel: this is legacy-encrypted data.
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, true);
        $header = $module->decodeHeader(str_repeat('x', 20));

        $this->assertEquals(CryptoModule::FALLBACK_CRYPTOR_ID, $header->getCryptorId());
        $this->assertEquals(0, $header->getLength());
    }

    public function testValidHeaderIsDecoded(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        // A real AES-CBC payload starts with the PNED sentinel header.
        $raw = base64_decode($module->encrypt($this->message));
        $header = $module->decodeHeader($raw);

        $this->assertEquals('PNED', $header->getSentinel());
        $this->assertEquals(AesCbcCryptor::CRYPTOR_ID, $header->getCryptorId());
        $this->assertGreaterThanOrEqual(10, $header->getLength());
    }

    public function testAesCbcRoundTrip(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        $encrypted = $module->encrypt($this->message);
        $this->assertEquals($this->message, $module->decrypt($encrypted));
    }

    public function testLegacyRoundTripUsesFallbackHeaderPath(): void
    {
        // Legacy-encrypted data carries no PNED header, so decrypt must route
        // through the fallback branch of decodeHeader and the legacy cryptor.
        $module = CryptoModule::legacyCryptor($this->cipherKey, true);

        $encrypted = $module->encrypt($this->message);
        $raw = base64_decode($encrypted);

        $header = $module->decodeHeader($raw);
        $this->assertEquals(CryptoModule::FALLBACK_CRYPTOR_ID, $header->getCryptorId());

        $this->assertEquals($this->message, $module->decrypt($encrypted));
    }

    public function testAesModuleCanDecryptLegacyPayload(): void
    {
        // Interop: data produced by the legacy cryptor must remain decryptable by
        // a module whose default is AES-CBC (both cryptors are registered).
        $legacyModule = CryptoModule::legacyCryptor($this->cipherKey, true);
        $aesModule = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        $encryptedByLegacy = $legacyModule->encrypt($this->message);
        $this->assertEquals($this->message, $aesModule->decrypt($encryptedByLegacy));
    }
}
