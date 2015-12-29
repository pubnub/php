<?php

class UtilsTest extends TestCase
{
    /**
     * @group utils
     */
    public function testEndsWith()
    {
        $this->assertTrue(\Pubnub\PubnubUtil::string_ends_with("foo-pnpres", "-pnpres"));
        $this->assertTrue(\Pubnub\PubnubUtil::string_ends_with("foo.bar-pnpres", "-pnpres"));
        $this->assertTrue(\Pubnub\PubnubUtil::string_ends_with("foo.*-pnpres", "-pnpres"));
        $this->assertFalse(\Pubnub\PubnubUtil::string_ends_with("foo", "-pnpres"));
        $this->assertFalse(\Pubnub\PubnubUtil::string_ends_with("foo.bar", "-pnpres"));
    }
}