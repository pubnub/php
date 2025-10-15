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
    public function testUrlEncode()
    {
        $this->assertEquals('blah%2Bnjkl', PubNubUtil::urlEncode("blah+njkl"));
        $this->assertEquals('%7B%22value%22%3A%20%222%22%7D', PubNubUtil::urlEncode("{\"value\": \"2\"}"));
    }

    public function testWriteValueAsString()
    {
        //phpcs:disable 
        $this->expectException(PubNubBuildRequestException::class);
        $this->expectExceptionMessage("Value serialization error: Malformed UTF-8 characters, possibly incorrectly encoded");

        PubNubUtil::writeValueAsString(["key" => "\xB1\x31"]);
        //phpcs:enable
    }

    public function testPamEncode()
    {
        $params = [
            'abc' => true,
            'poq' => 4,
            'def' => false
        ];

        $result = PubNubUtil::preparePamParams($params);
        self::assertEquals("abc=true&def=false&poq=4", $result);
    }

    public function testJoinQuery()
    {
        $elements = [
            'a' => '2',
            'asdf' => "qwer",
            'n' => 'false'
        ];

        $this->assertEquals("a=2&asdf=qwer&n=false", PubNubUtil::joinQuery($elements));
    }
}
