<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PubNub\Crypto\LegacyCryptor;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class PaddingTest extends TestCase
{
    protected LegacyCryptor $cryptor;
    protected function setUp(): void
    {
        $this->cryptor = new LegacyCryptor("myCipherKey", false);
    }

    /**
     * @dataProvider padProvider
     * @param string $plain
     * @param string $padded
     * @return void
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testPad(string $plain, string $padded): void
    {
        $this->assertEquals($this->cryptor->pad($plain, 16), $padded);
    }

    /**
     * @dataProvider depadProvider
     * @param string $padded
     * @param string $expected
     * @return void
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testDepad(string $padded, string $expected): void
    {
        $this->assertEquals($this->cryptor->depad($padded, 16), $expected);
    }

    public function padProvider(): Generator
    {
        yield ["123456789012345", "123456789012345\x01"];
        yield ["12345678901234", "12345678901234\x02\x02"];
        yield ["1234567890123456", "1234567890123456" . str_repeat("\x10", 16)];
    }

    public function depadProvider(): Generator
    {
        yield ["123456789012345\x01", "123456789012345"];
        yield ["12345678901234\x02\x02", "12345678901234"];
        yield ["1234567890123456" . str_repeat("\x10", 16), "1234567890123456"];
        yield ["1234567890123456", "1234567890123456"];
    }
}
