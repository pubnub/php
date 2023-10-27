<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Crypto\Header as CryptoHeader;
use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Crypto\AesCbcCryptor;

class HeaderEncoderTest extends TestCase
{
    protected CryptoModule $module;

    protected function setUp(): void
    {
        $this->module = new CryptoModule([], "0000");
    }

    /**
     * @dataProvider provideDecodeHeader
     * @param string $header
     * @param CryptoHeader $expected
     * @return void
     */
    public function testDecodeHeader(string $header, CryptoHeader $expected): void
    {
        $decoded = $this->module->decodeHeader($header);
        $this->assertEquals($expected, $decoded);
    }

    /**
     * @dataProvider provideEncodeHeader
     *
     * @param CryptoHeader $expected
     * @param string $
     * @return void
     */
    public function testEncodeHeader(CryptoPayload $payload, string $expected): void
    {
        $encoded = $this->module->encodeHeader($payload);
        $this->assertEquals($expected, $encoded);
    }

    public function provideDecodeHeader(): Generator
    {
        // decoding empty string should point to fallback cryptor
        yield ["", new CryptoHeader("", CryptoModule::FALLBACK_CRYPTOR_ID, "", 0)];

        // decoding header without cryptor data
        yield ["PNED\x01ACRH\x00", new CryptoHeader("PNED", "ACRH", "", 10)];

        // decoding with any data should add data length segment
        $cryptorData = "\x20";
        yield [
            "PNED\x01ACRH\x01" . $cryptorData,
            new CryptoHeader("PNED", "ACRH", $cryptorData, 10 + strlen($cryptorData))
        ];

        // if cryptor data is less than 255 characters data length segment is 1 byte long
        $cryptorData = str_repeat("\x20", 254);
        yield [
            "PNED\x01ACRH\xfe" . $cryptorData,
            new CryptoHeader("PNED", "ACRH", $cryptorData, 10 + strlen($cryptorData))
        ];

        // if cryptor data is greater than or equal 255 characters data length segment is 3 bytes long
        $cryptorData = str_repeat("\x20", 255);
        yield [
            "PNED\x01ACRH\xff\x00\xff" . $cryptorData,
            new CryptoHeader("PNED", "ACRH", $cryptorData, 12 + strlen($cryptorData))
        ];

        $cryptorData = str_repeat("\x20", 65535);
        yield [
            "PNED\x01ACRH\xff\xff\xff" . $cryptorData,
            new CryptoHeader("PNED", "ACRH", $cryptorData, 12 + strlen($cryptorData))
        ];
    }

    public function provideEncodeHeader(): Generator
    {
        $message = "";
        $cryptorData = "";
        // encode empty header for fallback cryptor
        yield [new CryptoPayload($message, $cryptorData, CryptoModule::FALLBACK_CRYPTOR_ID), ""];

        // encode header without cryptor data
        yield [new CryptoPayload($message, $cryptorData, AesCbcCryptor::CRYPTOR_ID), "PNED\x01ACRH\x00"];

        // header with cryptor data should include length byte
        $cryptorData = "\x20";
        yield [
            new CryptoPayload($message, $cryptorData, AesCbcCryptor::CRYPTOR_ID),
            "PNED\x01ACRH\x01" . $cryptorData,
        ];
        $cryptorData = str_repeat("\x20", 254);
        yield [
            new CryptoPayload($message, $cryptorData, AesCbcCryptor::CRYPTOR_ID),
            "PNED\x01ACRH\xfe" . $cryptorData,
        ];

        // encoding header with cryptor data longer than 254 bytes should include three length bytes
        $cryptorData = str_repeat("\x20", 255);
        yield [
            new CryptoPayload($message, $cryptorData, AesCbcCryptor::CRYPTOR_ID),
            "PNED\x01ACRH\xff\x00\xff" . $cryptorData,
        ];
        $cryptorData = str_repeat("\x20", 65535);
        yield [
            new CryptoPayload($message, $cryptorData, AesCbcCryptor::CRYPTOR_ID),
            "PNED\x01ACRH\xff\xff\xff" . $cryptorData,
        ];
    }
}
