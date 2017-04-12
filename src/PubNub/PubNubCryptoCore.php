<?php

namespace PubNub;


use Monolog\Logger;

abstract class PubNubCryptoCore{
    /** @var  string */
    protected $cipherKey;

    /** @var  string */
    protected $initializationVector;

    public function __construct($key, $initializationVector = "0123456789012345")
    {
        $this->cipherKey = $key;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @param string | object $cipherText
     * @param Logger | null $logger
     * @return mixed
     */
    abstract function decrypt($cipherText, $logger = null);

    /**
     * @param mixed $plainText
     * @return mixed
     */
    abstract function encrypt($plainText);

    /**
     * @return string
     */
    public function getCipherKey()
    {
        return $this->cipherKey;
    }

    /**
     * @param string $cipherKey
     */
    public function setCipherKey($cipherKey)
    {
        $this->cipherKey = $cipherKey;
    }

    public function pkcs5Pad($text, $blockSize) {
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function unPadPKCS7($data, $blockSize) {
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

    public function isBlank($word) {
        if (($word == null) || ($word == false))
            return true;
        else
            return false;
    }

    protected function tryToJsonDecode($value) {
        $result = json_decode($value);

        if ($result === null) {
            return $value;
        } else {
            return $result;
        }
    }
}
