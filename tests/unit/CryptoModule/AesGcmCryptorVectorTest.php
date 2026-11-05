<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Crypto\AesGcmCryptor;
use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use ReflectionMethod;

/**
 * Cross-SDK known-answer tests for the AES-256-GCM ("ACGM") cryptor. The vectors were generated with
 * node:crypto, independently of this SDK; decrypting them here must reproduce the original plaintext
 * byte for byte.
 * Layout: "PNED" | 0x01 | "ACGM" | 0x0C | nonce (12) | cipher text | tag (16).
 */
class AesGcmCryptorVectorTest extends TestCase
{
    // "PNED" | version 1 | "ACGM" | cryptor data size 12 (nonce only)
    private const EXPECTED_HEADER_HEX = '504e4544' . '01' . '4143474d' . '0c';
    private const HEADER_LENGTH = 10;
    private const NONCE_LENGTH = 12;
    private const TAG_LENGTH = 16;
    private const GENERIC_ERROR = 'Decryption error: message decryption failed';

    private const VECTORS = [
        'hello' => [
            'cipherKey' => 'enigma',
            'keySha256' => '67a4f45f0d1d9bc606486fc42dc4941668e71d34ee500735fe9b7ea4625c687c',
            'plaintext' => 'Hello world',
            'plaintextHex' => '48656c6c6f20776f726c64',
            'nonce' => '0102030405060708090a0b0c',
            'tag' => 'dabd1d02d7219c77218ad262c4a9aecc',
            'ciphertext' => 'ea1b5780788b593294f898',
            'ciphertextWithTag' => 'ea1b5780788b593294f898dabd1d02d7219c77218ad262c4a9aecc',
            'metadata' => '0102030405060708090a0b0c',
            'payload' => '504e4544014143474d0c0102030405060708090a0b0c'
                . 'ea1b5780788b593294f898dabd1d02d7219c77218ad262c4a9aecc',
        ],
        'unicode' => [
            'cipherKey' => 'enigma',
            'keySha256' => '67a4f45f0d1d9bc606486fc42dc4941668e71d34ee500735fe9b7ea4625c687c',
            'plaintext' => 'héllo 你好',
            'plaintextHex' => '68c3a96c6c6f20e4bda0e5a5bd',
            'nonce' => '111111111111111111111111',
            'tag' => 'a5dd072360f4433e2979f440fc7827b3',
            'ciphertext' => 'd138fbfc4bd0c337e29d9f2c98',
            'ciphertextWithTag' => 'd138fbfc4bd0c337e29d9f2c98a5dd072360f4433e2979f440fc7827b3',
            'metadata' => '111111111111111111111111',
            'payload' => '504e4544014143474d0c111111111111111111111111'
                . 'd138fbfc4bd0c337e29d9f2c98a5dd072360f4433e2979f440fc7827b3',
        ],
        'binary' => [
            'cipherKey' => 'myCipherKey',
            'keySha256' => '8c33871ac40eea7eb80fdd88465c48f0ee10054de5dfe77af9ed8c0ef626663e',
            'plaintext' => null,
            'plaintextHex' => '00ff7f80',
            'nonce' => '000102030405060708090a0b',
            'tag' => '8fefca7becf7235b4d09b970d4d56d19',
            'ciphertext' => 'c411f0f1',
            'ciphertextWithTag' => 'c411f0f18fefca7becf7235b4d09b970d4d56d19',
            'metadata' => '000102030405060708090a0b',
            'payload' => '504e4544014143474d0c000102030405060708090a0bc411f0f18fefca7becf7235b4d09b970d4d56d19',
        ],
        'single-byte' => [
            'cipherKey' => 'customKey',
            'keySha256' => '128cf0f9f9027c04c79c0ff574341db71cb7a7545c40469d59804bf78377f215',
            'plaintext' => 'a',
            'plaintextHex' => '61',
            'nonce' => 'a1a2a3a4a5a6a7a8a9aaabac',
            'tag' => 'eef1ccce8f5a09087348786b99b75076',
            'ciphertext' => 'fb',
            'ciphertextWithTag' => 'fbeef1ccce8f5a09087348786b99b75076',
            'metadata' => 'a1a2a3a4a5a6a7a8a9aaabac',
            'payload' => '504e4544014143474d0ca1a2a3a4a5a6a7a8a9aaabacfbeef1ccce8f5a09087348786b99b75076',
        ],
        'one-block' => [
            'cipherKey' => 'enigma',
            'keySha256' => '67a4f45f0d1d9bc606486fc42dc4941668e71d34ee500735fe9b7ea4625c687c',
            'plaintext' => '0123456789abcdef',
            'plaintextHex' => '30313233343536373839616263646566',
            'nonce' => '8f5a2d1b6e9c4ef0a37d83b9',
            'tag' => '8c21b92d48f4dd5c71e99dce881c2ec9',
            'ciphertext' => 'fd24a488008bd084305aaee605b34902',
            'ciphertextWithTag' => 'fd24a488008bd084305aaee605b349028c21b92d48f4dd5c71e99dce881c2ec9',
            'metadata' => '8f5a2d1b6e9c4ef0a37d83b9',
            'payload' => '504e4544014143474d0c8f5a2d1b6e9c4ef0a37d83b9'
                . 'fd24a488008bd084305aaee605b349028c21b92d48f4dd5c71e99dce881c2ec9',
        ],
        'multi-block-json' => [
            'cipherKey' => 'enigma',
            'keySha256' => '67a4f45f0d1d9bc606486fc42dc4941668e71d34ee500735fe9b7ea4625c687c',
            // U+1F30D (globe showing Europe-Africa) is written as an escape so the exact bytes survive editing.
            'plaintext' => "{\"text\":\"Hello, 世界! \u{1F30D}\",\"n\":42,\"list\":[1,2,3],\"nested\":{\"ok\":true}}",
            'plaintextHex' => '7b2274657874223a2248656c6c6f2c20e4b896e7958c2120f09f8c8d222c226e'
                . '223a34322c226c697374223a5b312c322c335d2c226e6573746564223a7b226f6b223a747275657d7d',
            'nonce' => 'ffffffffffffffffffffffff',
            'tag' => 'd307669e83fa4c9a8b13743a07a21b7f',
            'ciphertext' => '0d54b73ce2509d4e7655043f5b103cb4f1e78738a73a2f56109813955359b478'
                . '4774de19050b4f14f7a439a859ac6c327856fe1af874bbe888945c603004176ddab22578e407663346',
            'ciphertextWithTag' => '0d54b73ce2509d4e7655043f5b103cb4f1e78738a73a2f56109813955359b478'
                . '4774de19050b4f14f7a439a859ac6c327856fe1af874bbe888945c603004176ddab22578e407663346'
                . 'd307669e83fa4c9a8b13743a07a21b7f',
            'metadata' => 'ffffffffffffffffffffffff',
            'payload' => '504e4544014143474d0cffffffffffffffffffffffff'
                . '0d54b73ce2509d4e7655043f5b103cb4f1e78738a73a2f56109813955359b478'
                . '4774de19050b4f14f7a439a859ac6c327856fe1af874bbe888945c603004176ddab22578e407663346'
                . 'd307669e83fa4c9a8b13743a07a21b7f',
        ],
        'unicode-key' => [
            // U+1F511 (key) is written as an escape so the exact bytes survive editing.
            'cipherKey' => "ключ \u{1F511} key",
            'keySha256' => 'c87d40b9e09784a5d672976e22b6bc9701da1e6fe5b8db721ed9ce715aedbdb0',
            'plaintext' => "Line one\n\tLine two with \"quotes\" & symbols <>/\\ \u{2014} spans several AES blocks.",
            'plaintextHex' => '4c696e65206f6e650a094c696e652074776f2077697468202271756f74657322'
                . '20262073796d626f6c73203c3e2f5c20e28094207370616e73207365766572616c2041455320626c6f636b732e',
            'nonce' => '000000000000000000000000',
            'tag' => '8390572d9b05255f3097614e888fcca4',
            'ciphertext' => '326052d9ff1b757ed8810f5d6f87a7c021920eb09b82e47049efecf5094f6f58'
                . '37691e9fb66539dfc47d434cc01b783527c4d62ae2d8163043f86dc7ac405b90971ceaa07134add2be50dfd60f',
            'ciphertextWithTag' => '326052d9ff1b757ed8810f5d6f87a7c021920eb09b82e47049efecf5094f6f58'
                . '37691e9fb66539dfc47d434cc01b783527c4d62ae2d8163043f86dc7ac405b90971ceaa07134add2be50dfd60f'
                . '8390572d9b05255f3097614e888fcca4',
            'metadata' => '000000000000000000000000',
            'payload' => '504e4544014143474d0c000000000000000000000000'
                . '326052d9ff1b757ed8810f5d6f87a7c021920eb09b82e47049efecf5094f6f58'
                . '37691e9fb66539dfc47d434cc01b783527c4d62ae2d8163043f86dc7ac405b90971ceaa07134add2be50dfd60f'
                . '8390572d9b05255f3097614e888fcca4',
        ],
    ];

    /**
     * @return Generator<string, array{array<string, string|null>}>
     */
    public static function vectorProvider(): Generator
    {
        foreach (self::VECTORS as $name => $vector) {
            yield $name => [$vector];
        }
    }

    /**
     * @return Generator<string, array{array<string, string|null>}>
     */
    public static function textVectorProvider(): Generator
    {
        foreach (self::VECTORS as $name => $vector) {
            if ($vector['plaintext'] !== null) {
                yield $name => [$vector];
            }
        }
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testKeyDerivationMatchesVector(array $vector): void
    {
        $this->assertSame($vector['keySha256'], hash('sha256', $vector['cipherKey']));
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testPayloadLayoutMatchesSpecification(array $vector): void
    {
        $this->assertSame($vector['nonce'], $vector['metadata'], 'metadata must contain only the nonce');
        $this->assertSame(
            $vector['ciphertext'] . $vector['tag'],
            $vector['ciphertextWithTag'],
            'tag must be appended to the cipher text'
        );
        $this->assertSame(
            self::EXPECTED_HEADER_HEX . $vector['metadata'] . $vector['ciphertextWithTag'],
            $vector['payload']
        );

        $module = CryptoModule::aesCbcCryptor($vector['cipherKey'], true);
        $header = $module->decodeHeader(self::fromHex($vector['payload']));

        $this->assertSame('PNED', $header->getSentinel());
        $this->assertSame(AesGcmCryptor::CRYPTOR_ID, $header->getCryptorId());
        $this->assertSame(self::fromHex($vector['nonce']), $header->getCryptorData());
        $this->assertSame(self::HEADER_LENGTH + self::NONCE_LENGTH, $header->getLength());
    }

    /**
     * @dataProvider textVectorProvider
     * @param array<string, string|null> $vector
     */
    public function testPlaintextHexIsUtf8OfPlaintext(array $vector): void
    {
        $this->assertSame($vector['plaintextHex'], bin2hex($vector['plaintext']));
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testDecryptRawReproducesPlaintextBytes(array $vector): void
    {
        $decryptRaw = new ReflectionMethod(AesGcmCryptor::class, 'decryptRaw');

        $decrypted = $decryptRaw->invoke(new AesGcmCryptor($vector['cipherKey']), self::cryptoPayload($vector));

        $this->assertSame($vector['plaintextHex'], bin2hex($decrypted));
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testCryptorDecryptReturnsPlaintext(array $vector): void
    {
        $decrypted = (new AesGcmCryptor($vector['cipherKey']))->decrypt(self::cryptoPayload($vector));

        $this->assertEquals(self::expectedDecryptResult($vector), $decrypted);
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testCryptoModuleRoutesVectorToAesGcmCryptor(array $vector): void
    {
        $modules = [
            'aesCbcCryptor' => CryptoModule::aesCbcCryptor($vector['cipherKey'], true),
            'legacyCryptor with random IV' => CryptoModule::legacyCryptor($vector['cipherKey'], true),
            'legacyCryptor with static IV' => CryptoModule::legacyCryptor($vector['cipherKey'], false),
        ];

        foreach ($modules as $name => $module) {
            $this->assertEquals(
                self::expectedDecryptResult($vector),
                $module->decrypt(self::payloadBase64($vector)),
                "Decrypt mismatch for module: $name"
            );
        }
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testCipherKeyConfigurationDecryptsVector(array $vector): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey($vector['cipherKey']);
        $pubnub = new PubNub($config);

        $decrypted = $pubnub->getCrypto()->decrypt(self::payloadBase64($vector));

        $this->assertEquals(self::expectedDecryptResult($vector), $decrypted);
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testTamperedVectorFailsAuthentication(array $vector): void
    {
        $payload = self::fromHex($vector['payload']);
        $cipherTextOffset = self::HEADER_LENGTH + self::NONCE_LENGTH;
        $tagOffset = strlen($payload) - self::TAG_LENGTH;

        $cases = [
            'nonce' => [$vector['cipherKey'], self::flipByte($payload, self::HEADER_LENGTH)],
            'first cipher text byte' => [$vector['cipherKey'], self::flipByte($payload, $cipherTextOffset)],
            'first tag byte' => [$vector['cipherKey'], self::flipByte($payload, $tagOffset)],
            'last byte' => [$vector['cipherKey'], self::flipByte($payload, strlen($payload) - 1)],
            'wrong key' => [$vector['cipherKey'] . 'x', $payload],
        ];

        foreach ($cases as $name => [$cipherKey, $data]) {
            try {
                CryptoModule::aesCbcCryptor($cipherKey, true)->decrypt(base64_encode($data));
                $this->fail("Expected decryption to fail for case: $name");
            } catch (PubNubResponseParsingException $e) {
                $this->assertSame(self::GENERIC_ERROR, $e->getMessage(), "Non-generic error for case: $name");
            }
        }
    }

    /**
     * @dataProvider vectorProvider
     * @param array<string, string|null> $vector
     */
    public function testPreviousTagFirstLayoutIsRejected(array $vector): void
    {
        // Same nonce, tag and cipher text, but in the superseded layout: size 28 | nonce | tag | cipher text.
        $previousLayout = '504e4544014143474d1c' . $vector['nonce'] . $vector['tag'] . $vector['ciphertext'];
        $module = CryptoModule::aesCbcCryptor($vector['cipherKey'], true);

        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage(self::GENERIC_ERROR);

        $module->decrypt(base64_encode(self::fromHex($previousLayout)));
    }

    /**
     * Cryptors return the decoded value when the plaintext is JSON and the raw plaintext otherwise.
     *
     * @param array<string, string|null> $vector
     */
    private static function expectedDecryptResult(array $vector): mixed
    {
        $plaintext = self::fromHex($vector['plaintextHex']);
        $decoded = json_decode($plaintext);

        return $decoded === null ? $plaintext : $decoded;
    }

    /**
     * @param array<string, string|null> $vector
     */
    private static function cryptoPayload(array $vector): CryptoPayload
    {
        return new CryptoPayload(
            self::fromHex($vector['ciphertextWithTag']),
            self::fromHex($vector['nonce']),
            AesGcmCryptor::CRYPTOR_ID
        );
    }

    /**
     * @param array<string, string|null> $vector
     */
    private static function payloadBase64(array $vector): string
    {
        return base64_encode(self::fromHex($vector['payload']));
    }

    private static function fromHex(string $hex): string
    {
        $bytes = hex2bin($hex);
        if ($bytes === false) {
            throw new \InvalidArgumentException("Invalid hex string: $hex");
        }

        return $bytes;
    }

    private static function flipByte(string $data, int $index): string
    {
        $data[$index] = chr(ord($data[$index]) ^ 0x01);

        return $data;
    }
}
