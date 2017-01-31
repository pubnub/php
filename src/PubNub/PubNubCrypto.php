<?php

namespace PubNub;


class PubNubCrypto extends PubNubCryptoCore {
    public function encrypt($plainText) {
        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $encrypted = openssl_encrypt($plainText, 'aes-256-cbc', $paddedCipherKey, OPENSSL_RAW_DATA,
            $this->initializationVector);
        $encode = base64_encode($encrypted);

        return $encode;
    }

    public function decrypt($cipherText) {
        if (gettype($cipherText) != "string"){
            // TODO: raise error
            return "DECRYPTION_ERROR";
        }

        $shaCipherKey = hash("sha256", $this->cipherKey);
        $paddedCipherKey = substr($shaCipherKey, 0, 32);

        $decrypted = openssl_decrypt($cipherText, 'aes-256-cbc', $paddedCipherKey, 0,
            $this->initializationVector);

        if ($decrypted === false) {
            // TODO: log a string
            openssl_error_string();
            return "";
        }

        $unPadded = $this->unPadPKCS7($decrypted, 16);

        $result = json_decode($unPadded);

        if ($result === null) {
            return $unPadded;
        } else {
            return $result;
        }
    }

}
