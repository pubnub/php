<?php


class PubnubAES {
    function decrypt($cipher_text, $cipher_key) {
        $iv = "0123456789012345";

        if (gettype($cipher_text) != "string")
            return "DECRYPTION_ERROR";

        $decoded = base64_decode($cipher_text);

        $sha_cipher_key = hash("sha256", $cipher_key);
        $padded_cipher_key = substr($sha_cipher_key, 0, 32);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $padded_cipher_key, $iv);

        $decrypted = mdecrypt_generic($td, $decoded); // TODO: handle non-encrypted unicode corner-case
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        
        $unpadded = $this->unpadPKCS7($decrypted, 16);

        return $unpadded;
    }
    
    function encrypt($plain_text, $cipher_key) {
        $iv = "0123456789012345";

        $sha_cipher_key = hash("sha256", $cipher_key);
        $padded_cipher_key = substr($sha_cipher_key, 0, 32);

        $padded_plain_text = $this->pkcs5Pad($plain_text, 16);

        # This is the way to do AES-256 using mcrypt PHP - its not AES-128 or anything other than that!
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $padded_cipher_key, $iv);
        $encrypted = mcrypt_generic($td, $padded_plain_text);
        $encode = base64_encode($encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $encode;
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    
    function unpadPKCS7($data, $blockSize) {
        $length = strlen($data);
        if ($length > 0) {
            $first = substr($data, -1);

            if (ord($first) <= $blockSize) {
                for ($i = $length - 2; $i > 0; $i--)
                    if (ord($data [$i] != $first))
                        break;

                return substr($data, 0, $i + 1);
            }
        }
        return $data;
    }

    function isBlank($word) {
        if (($word == null) || ($word == false))
            return true;
        else
            return false;
    }
}
