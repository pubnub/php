<?php

namespace PubNub\Crypto;

use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Exceptions\PubNubCryptoException;
use PubNub\Exceptions\PubNubResponseParsingException;

/**
 * AES-256-GCM cryptor (identifier "ACGM").
 *
 * Currently decrypt-only: it can be registered as an additional cryptor in a CryptoModule,
 * but cannot be used as the default (encrypting) cryptor.
 *
 * Payload layout: "PNED" | 0x01 | "ACGM" | 0x0C | nonce (12) | cipher text | tag (16).
 */
class AesGcmCryptor extends Cryptor
{
    public const CRYPTOR_ID = 'ACGM';
    public const NONCE_LENGTH = 12;
    public const TAG_LENGTH = 16;
    public const CIPHER_ALGO = 'aes-256-gcm';

    protected string $cipherKey;

    public function __construct(string $cipherKey)
    {
        $this->cipherKey = $cipherKey;
    }

    public function getCipherKey(?string $cipherKey = null): string
    {
        return $cipherKey ? $cipherKey : $this->cipherKey;
    }

    public function encrypt(string $text, ?string $cipherKey = null): CryptoPayload
    {
        throw new PubNubCryptoException('AesGcmCryptor is decrypt-only. Encryption is not supported yet.');
    }

    public function decrypt(CryptoPayload $payload, ?string $cipherKey = null): mixed
    {
        $decrypted = $this->decryptRaw($payload, $cipherKey);

        $result = json_decode($decrypted, false);

        if ($result === null) {
            return $decrypted;
        } else {
            return $result;
        }
    }

    protected function decryptRaw(CryptoPayload $payload, ?string $cipherKey = null): string
    {
        $nonce = (string)$payload->getCryptorData();
        $data = $payload->getData();

        // The header's cryptor data holds only the nonce; the cipher text must carry at least
        // one byte followed by the authentication tag.
        if (strlen($nonce) !== static::NONCE_LENGTH || strlen($data) <= static::TAG_LENGTH) {
            throw new PubNubResponseParsingException("Decryption error: message decryption failed");
        }

        $secret = hash("sha256", $this->getCipherKey($cipherKey), true);
        $decrypted = openssl_decrypt(
            substr($data, 0, -static::TAG_LENGTH),
            static::CIPHER_ALGO,
            $secret,
            OPENSSL_RAW_DATA,
            $nonce,
            substr($data, -static::TAG_LENGTH)
        );

        if ($decrypted === false) {
            // A wrong key and tampered data, nonce or tag must all surface as the same generic error.
            throw new PubNubResponseParsingException("Decryption error: message decryption failed");
        }

        return $decrypted;
    }
}
