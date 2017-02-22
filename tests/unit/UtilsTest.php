<?php

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
        $this->expectException(PubNubBuildRequestException::class);
        $this->expectExceptionMessage("Value serialization error: Malformed UTF-8 characters, possibly incorrectly encoded");

        PubNubUtil::writeValueAsString(["key" => "\xB1\x31"]);
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

    public function testSignSha256()
    {
        $signInput = "sub-c-7ba2ac4c-4836-11e6-85a4-0619f8945a4f
pub-c-98863562-19a6-4760-bf0b-d537d1f5c582
grant
channel=asyncio-pam-FI2FCS0A&pnsdk=PubNub-Python-Asyncio%252F4.0.0&r=1&timestamp=1468409553&uuid=a4dbf92e-e5cb-428f-b6e6-35cce03500a2&w=1";

        $result = PubNubUtil::signSha256("my_key", $signInput);

        self::assertEquals("Dq92jnwRTCikdeP2nUs1__gyJthF8NChwbs5aYy2r_I=", $result);
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
