<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\PubNubUtil;

class UtilsTest extends TestCase
{
    /**
     * @group time
     * @group time-integrational
     */
    public function testUrlEncode(): void
    {
        $this->assertEquals('blah%2Bnjkl', PubNubUtil::urlEncode("blah+njkl"));
        $this->assertEquals('%7B%22value%22%3A%20%222%22%7D', PubNubUtil::urlEncode("{\"value\": \"2\"}"));
    }

    public function testWriteValueAsString(): void
    {
        //phpcs:disable 
        $this->expectException(PubNubBuildRequestException::class);
        $this->expectExceptionMessage("Value serialization error: Malformed UTF-8 characters, possibly incorrectly encoded");

        PubNubUtil::writeValueAsString(["key" => "\xB1\x31"]);
        //phpcs:enable
    }

    public function testPamEncode(): void
    {
        $params = [
            'abc' => true,
            'poq' => 4,
            'def' => false
        ];

        $result = PubNubUtil::preparePamParams($params);
        self::assertEquals("abc=true&def=false&poq=4", $result);
    }

    public function testJoinQuery(): void
    {
        $elements = [
            'a' => '2',
            'asdf' => "qwer",
            'n' => 'false'
        ];

        $this->assertEquals("a=2&asdf=qwer&n=false", PubNubUtil::joinQuery($elements));
    }
}
