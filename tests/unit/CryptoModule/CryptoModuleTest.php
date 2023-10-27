<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Exceptions\PubNubResponseParsingException;

class CryptoModuleTest extends TestCase
{
    protected string $cipherKey = "myCipherKey";

    /**
     * @dataProvider decodeProvider
     * @param string $encrypted
     * @param mixed $expected
     * @return void
     */
    public function testDecode(CryptoModule $module, string $encrypted, mixed $expected): void
    {
        try {
            $decrypted = $module->decrypt($encrypted);
        } catch (PubNubResponseParsingException $e) {
            $decrypted = $e->getMessage();
        }
        $this->assertEquals($expected, $decrypted);
    }

    /**
     * @dataProvider encodeProvider
     * @param string $message
     * @param mixed $expected
     * @return void
     */
    public function testEnode(CryptoModule $module, string $message, mixed $expected): void
    {
        try {
            $encrypted = $module->encrypt($message);
            if (!$expected) {
                $this->assertEquals($message, $module->decrypt($encrypted));
                return;
            }
        } catch (PubNubResponseParsingException $e) {
            $encrypted = $e->getMessage();
        }
        $this->assertEquals($expected, $encrypted);
    }

    protected function encodeProvider(): Generator
    {
        $legacyRandomModule = CryptoModule::legacyCryptor($this->cipherKey, true);
        $legacyStaticModule = CryptoModule::legacyCryptor($this->cipherKey, false);
        $aesCbcModuleStatic = CryptoModule::aesCbcCryptor($this->cipherKey, false);
        $aesCbcModuleRandom = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        yield [$legacyRandomModule, '', 'Encryption error: message is empty'];
        yield [$legacyStaticModule, '', 'Encryption error: message is empty'];
        yield [$aesCbcModuleStatic, '', 'Encryption error: message is empty'];
        yield [
            $legacyStaticModule,
            "Hello world encrypted with legacyModuleStaticIv",
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
        ];
        yield [
            $legacyRandomModule,
            "Hello world encrypted with legacyModuleRandomIv",
            null,
        ];
        yield [
            $legacyStaticModule,
            "Hello world encrypted with legacyModuleStaticIv",
            null,
        ];
        // test fallback decrypt with static IV
        yield [
            $aesCbcModuleStatic,
            "Hello world encrypted with legacyModuleStaticIv",
            null,
        ];
        // test falback decrypt with random IV
        yield [
            $aesCbcModuleRandom,
            "Hello world encrypted with legacyModuleRandomIv",
            null,
        ];
        yield [
            $aesCbcModuleRandom,
            'Hello world encrypted with aesCbcModule',
            null,
        ];
    }

    protected function decodeProvider(): Generator
    {
        $legacyRandomModule = CryptoModule::legacyCryptor($this->cipherKey, true);
        $legacyStaticModule = CryptoModule::legacyCryptor($this->cipherKey, false);
        $aesCbcModuleStatic = CryptoModule::aesCbcCryptor($this->cipherKey, false);
        $aesCbcModuleRandom = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        yield [$legacyRandomModule, '', 'Decryption error: message is empty'];
        yield [$legacyStaticModule, '', 'Decryption error: message is empty'];
        yield [$aesCbcModuleStatic, '', 'Decryption error: message is empty'];
        yield [
            $legacyRandomModule,
            "T3J9iXI87PG9YY/lhuwmGRZsJgA5y8sFLtUpdFmNgrU1IAitgAkVok6YP7lacBiVhBJSJw39lXCHOLxl2d98Bg==",
            "Hello world encrypted with legacyModuleRandomIv",
        ];
        yield [
            $legacyStaticModule,
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
            "Hello world encrypted with legacyModuleStaticIv",
        ];
        // test fallback decrypt with static IV
        yield [
            $aesCbcModuleStatic,
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
            "Hello world encrypted with legacyModuleStaticIv",
        ];
        // test falback decrypt with random IV
        yield [
            $aesCbcModuleRandom,
            "T3J9iXI87PG9YY/lhuwmGRZsJgA5y8sFLtUpdFmNgrU1IAitgAkVok6YP7lacBiVhBJSJw39lXCHOLxl2d98Bg==",
            "Hello world encrypted with legacyModuleRandomIv",
        ];
        yield [
            $aesCbcModuleRandom,
            'UE5FRAFBQ1JIEKzlyoyC/jB1hrjCPY7zm+X2f7skPd0LBocV74cRYdrkRQ2BPKeA22gX/98pMqvcZtFB6TCGp3Zf1M8F730nlfk=',
            'Hello world encrypted with aesCbcModule',
        ];
    }
}
