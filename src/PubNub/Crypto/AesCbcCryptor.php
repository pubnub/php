<?php

namespace PubNub\Crypto;

use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Crypto\PaddingTrait;

class AesCbcCryptor extends Cryptor
{
    use PaddingTrait;

    public const CRYPTOR_ID = 'ACRH';
    public const IV_LENGTH = 16;
    public const BLOCK_SIZE = 16;
    public const CIPHER_ALGO = 'aes-256-cbc';

    protected string $cipherKey;

    public function __construct(string $cipherKey)
    {
        $this->cipherKey = $cipherKey;
    }

    public function getIV(): string
    {
        return random_bytes(static::IV_LENGTH);
    }

    public function getCipherKey($cipherKey = null): string
    {
        return $cipherKey ? $cipherKey : $this->cipherKey;
    }

    protected function getSecret(string $cipherKey): string
    {
        $key = !is_null($cipherKey) ? $cipherKey : $this->cipherKey;
        return hash("sha256", $key, true);
    }

    public function encrypt(string $text, ?string $cipherKey = null): CryptoPayload
    {
        $secret = $this->getSecret($this->getCipherKey($cipherKey));
        $iv = $this->getIV();
        $encrypted = openssl_encrypt($text, static::CIPHER_ALGO, $secret, OPENSSL_RAW_DATA, $iv);
        return new CryptoPayload($encrypted, $iv, static::CRYPTOR_ID);
    }

    public function decrypt(CryptoPayload $payload, ?string $cipherKey = null)
    {
        $text = $payload->getData();
        $secret = $this->getSecret($this->getCipherKey($cipherKey));
        $iv = $payload->getCryptorData();
        $decrypted = openssl_decrypt($text, static::CIPHER_ALGO, $secret, OPENSSL_RAW_DATA, $iv);
        $result = json_decode($decrypted, false);

        if ($result === null) {
            return $decrypted;
        } else {
            return $result;
        }
    }
}
