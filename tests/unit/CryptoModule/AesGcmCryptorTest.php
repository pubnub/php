<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Crypto\AesCbcCryptor;
use PubNub\Crypto\AesGcmCryptor;
use PubNub\Crypto\LegacyCryptor;
use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Exceptions\PubNubCryptoException;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesItemResult;
use PubNub\PNConfiguration;
use ReflectionMethod;

class AesGcmCryptorTest extends TestCase
{
    private const CIPHER_KEY = 'enigma';
    private const GENERIC_ERROR = 'Decryption error: message decryption failed';
    private const NONCE_OFFSET = 10;
    private const NONCE_LENGTH = 12;
    private const CIPHER_TEXT_OFFSET = self::NONCE_OFFSET + self::NONCE_LENGTH;
    private const TAG_LENGTH = 16;

    public function testDecryptsUnicodeString(): void
    {
        $message = "Hello ACGM - zażółć gęślą jaźń \u{1F600}";
        $module = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);

        $this->assertSame($message, $module->decrypt(self::acgmBase64(self::CIPHER_KEY, $message)));
    }

    public function testJsonPlaintextIsReturnedDecoded(): void
    {
        $json = '{"text":"hello","n":42,"list":[1,2,3]}';
        $module = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);

        $decrypted = $module->decrypt(self::acgmBase64(self::CIPHER_KEY, $json));

        $this->assertIsObject($decrypted);
        $this->assertEquals(json_decode($json), $decrypted);
    }

    public function testDecryptsLargeBinaryDataByteForByte(): void
    {
        $data = random_bytes(1024 * 1024);
        $decryptRaw = new ReflectionMethod(AesGcmCryptor::class, 'decryptRaw');

        $decrypted = $decryptRaw->invoke(
            new AesGcmCryptor(self::CIPHER_KEY),
            self::cryptoPayload(self::buildAcgmPayload(self::CIPHER_KEY, $data))
        );

        $this->assertSame($data, $decrypted);
    }

    /**
     * @dataProvider factoryModuleProvider
     */
    public function testFactoryModulesDecryptAcgmAcrhAndLegacyData(CryptoModule $module): void
    {
        $message = 'yay!';
        $payloads = [
            'ACGM' => self::acgmBase64(self::CIPHER_KEY, $message),
            'ACRH' => CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true)->encrypt($message),
            'legacy' => CryptoModule::legacyCryptor(self::CIPHER_KEY, true)->encrypt($message),
        ];

        foreach ($payloads as $name => $payload) {
            $this->assertSame($message, $module->decrypt($payload), "Decrypt mismatch for $name data");
        }
    }

    public function testCustomModuleWithoutAesGcmCryptorRejectsAcgmData(): void
    {
        $module = self::customModuleWithoutAesGcm();

        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('unknown cryptor error');

        $module->decrypt(self::acgmBase64(self::CIPHER_KEY, 'yay!'));
    }

    public function testCustomModuleDecryptsAcgmDataAfterRegisteringAesGcmCryptor(): void
    {
        $module = self::customModuleWithoutAesGcm()->registerCryptor(new AesGcmCryptor(self::CIPHER_KEY));

        $this->assertSame('yay!', $module->decrypt(self::acgmBase64(self::CIPHER_KEY, 'yay!')));
    }

    public function testFactoryModulesAlreadyRegisterAesGcmCryptor(): void
    {
        $module = CryptoModule::legacyCryptor(self::CIPHER_KEY, true);

        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Cryptor id already in use');

        $module->registerCryptor(new AesGcmCryptor(self::CIPHER_KEY));
    }

    public function testTamperedDataFailsWithGenericError(): void
    {
        $message = 'a message protected by the GCM authentication tag';
        $valid = self::buildAcgmPayload(self::CIPHER_KEY, $message);
        $module = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);
        $this->assertSame($message, $module->decrypt(base64_encode($valid)));

        $tagOffset = strlen($valid) - self::TAG_LENGTH;
        $cases = [
            'nonce' => [self::CIPHER_KEY, self::flipByte($valid, self::NONCE_OFFSET)],
            'first cipher text byte' => [self::CIPHER_KEY, self::flipByte($valid, self::CIPHER_TEXT_OFFSET)],
            'last cipher text byte' => [self::CIPHER_KEY, self::flipByte($valid, $tagOffset - 1)],
            'first tag byte' => [self::CIPHER_KEY, self::flipByte($valid, $tagOffset)],
            'last byte' => [self::CIPHER_KEY, self::flipByte($valid, strlen($valid) - 1)],
            'wrong key' => ['wrong-key', $valid],
        ];

        foreach ($cases as $name => [$cipherKey, $data]) {
            try {
                CryptoModule::aesCbcCryptor($cipherKey, true)->decrypt(base64_encode($data));
                $this->fail("Expected decryption to fail for case: $name");
            } catch (PubNubResponseParsingException $e) {
                $this->assertSame(PubNubResponseParsingException::class, get_class($e), "Wrong error for: $name");
                $this->assertSame(self::GENERIC_ERROR, $e->getMessage(), "Non-generic error for case: $name");
                $this->assertNull($e->getPrevious(), "Native crypto error surfaced for case: $name");
                $this->assertStringNotContainsString('bad decrypt', $e->getMessage(), "Leaked detail for: $name");
                $this->assertStringNotContainsString('tag', strtolower($e->getMessage()), "Leaked detail for: $name");
            }
        }
    }

    /**
     * @dataProvider malformedPayloadProvider
     */
    public function testMalformedPayloadIsRejected(string $payload): void
    {
        $module = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);

        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage(self::GENERIC_ERROR);

        $module->decrypt(base64_encode($payload));
    }

    public function testCryptorRejectsPayloadWithoutNonce(): void
    {
        $payload = new CryptoPayload(random_bytes(32), null, AesGcmCryptor::CRYPTOR_ID);

        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage(self::GENERIC_ERROR);

        (new AesGcmCryptor(self::CIPHER_KEY))->decrypt($payload);
    }

    public function testEncryptIsNotSupported(): void
    {
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('AesGcmCryptor is decrypt-only. Encryption is not supported yet.');

        (new AesGcmCryptor(self::CIPHER_KEY))->encrypt('yay!');
    }

    public function testModuleEncryptWithAesGcmCryptorIdIsNotSupported(): void
    {
        $module = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);

        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('AesGcmCryptor is decrypt-only. Encryption is not supported yet.');

        $module->encrypt('yay!', AesGcmCryptor::CRYPTOR_ID);
    }

    public function testAesGcmCryptorCannotBeDefaultCryptor(): void
    {
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('cannot be used as the default cryptor');

        new CryptoModule(
            [AesGcmCryptor::CRYPTOR_ID => new AesGcmCryptor(self::CIPHER_KEY)],
            AesGcmCryptor::CRYPTOR_ID
        );
    }

    public function testAesGcmCryptorCannotBeDefaultCryptorUnderCustomId(): void
    {
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('cannot be used as the default cryptor');

        new CryptoModule(['CUST' => new AesGcmCryptor(self::CIPHER_KEY)], 'CUST');
    }

    public function testFactoriesKeepTheirDefaultEncryptor(): void
    {
        $legacy = CryptoModule::legacyCryptor(self::CIPHER_KEY, false);
        $aesCbc = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true);

        $legacyEncrypted = base64_decode($legacy->encrypt('yay!'));
        $aesCbcEncrypted = base64_decode($aesCbc->encrypt('yay!'));

        $this->assertStringStartsNotWith('PNED', $legacyEncrypted);
        $this->assertSame(AesCbcCryptor::CRYPTOR_ID, $aesCbc->decodeHeader($aesCbcEncrypted)->getCryptorId());
    }

    public function testDecryptUsesCipherKeyOverride(): void
    {
        $cryptor = new AesGcmCryptor('other-key');
        $payload = self::cryptoPayload(self::buildAcgmPayload(self::CIPHER_KEY, 'yay!'));

        $this->assertSame('other-key', $cryptor->getCipherKey());
        $this->assertSame(self::CIPHER_KEY, $cryptor->getCipherKey(self::CIPHER_KEY));
        $this->assertSame('yay!', $cryptor->decrypt($payload, self::CIPHER_KEY));
    }

    public function testCipherKeyConfigurationDecryptsFetchedAcgmMessage(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setCipherKey(self::CIPHER_KEY);
        $json = [
            'message' => self::acgmBase64(self::CIPHER_KEY, '{"text":"hello"}'),
            'timetoken' => '17000000000000000',
        ];

        $item = PNFetchMessagesItemResult::fromJson($json, $config->getCryptoSafe());

        $this->assertEquals((object)['text' => 'hello'], $item->getMessage());
    }

    /**
     * @return Generator<string, array{CryptoModule}>
     */
    public static function factoryModuleProvider(): Generator
    {
        yield 'legacyCryptor' => [CryptoModule::legacyCryptor(self::CIPHER_KEY, true)];
        yield 'aesCbcCryptor' => [CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true)];
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function malformedPayloadProvider(): Generator
    {
        $valid = self::buildAcgmPayload(self::CIPHER_KEY, 'yay!');

        // 28 was the cryptor data size of the superseded nonce + tag layout.
        yield 'cryptor data size 28' => [self::buildAcgmPayload(self::CIPHER_KEY, 'yay!', 28)];
        yield 'cryptor data size 16' => [self::buildAcgmPayload(self::CIPHER_KEY, 'yay!', 16)];
        yield 'nonce and tag without cipher text' => [
            substr($valid, 0, self::CIPHER_TEXT_OFFSET) . substr($valid, -self::TAG_LENGTH),
        ];
        yield 'header and nonce only' => [substr($valid, 0, self::CIPHER_TEXT_OFFSET)];
        yield 'truncated in the middle of the nonce' => [substr($valid, 0, self::NONCE_OFFSET + 5)];
    }

    /**
     * Builds an ACGM payload with OpenSSL: PNED | 0x01 | ACGM | size | nonce (12) | cipher text | tag (16).
     */
    private static function buildAcgmPayload(string $cipherKey, string $plaintext, int $cryptorDataSize = 12): string
    {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';
        $cipherText = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            hash('sha256', $cipherKey, true),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LENGTH
        );
        if ($cipherText === false) {
            throw new \RuntimeException('Failed to build ACGM test payload');
        }

        return 'PNED' . chr(1) . AesGcmCryptor::CRYPTOR_ID . chr($cryptorDataSize) . $nonce . $cipherText . $tag;
    }

    private static function acgmBase64(string $cipherKey, string $plaintext): string
    {
        return base64_encode(self::buildAcgmPayload($cipherKey, $plaintext));
    }

    private static function cryptoPayload(string $raw): CryptoPayload
    {
        $header = CryptoModule::aesCbcCryptor(self::CIPHER_KEY, true)->decodeHeader($raw);

        return new CryptoPayload(
            substr($raw, $header->getLength()),
            $header->getCryptorData(),
            $header->getCryptorId()
        );
    }

    private static function customModuleWithoutAesGcm(): CryptoModule
    {
        return new CryptoModule([
            LegacyCryptor::CRYPTOR_ID => new LegacyCryptor(self::CIPHER_KEY, true),
            AesCbcCryptor::CRYPTOR_ID => new AesCbcCryptor(self::CIPHER_KEY),
        ], AesCbcCryptor::CRYPTOR_ID);
    }

    private static function flipByte(string $data, int $index): string
    {
        $data[$index] = chr(ord($data[$index]) ^ 0x01);

        return $data;
    }
}
