<?php

use Pubnub\Pubnub;

class ChannelGroupParsingTest extends TestCase
{
    private $channelGroup;
    private $channelNamespace;

    public function setUp()
    {
        $this->channelGroup = "ptest-" . rand();
        $this->channelNamespace = "ptest-namespace";
        $this->pubnub = new Pubnub(array(
            'publish_key' => 'demo',
            'subscribe_key' => 'demo',
            'origin' => 'dara24.devbuild.pubnub.com'
        ));
    }

    /**
     * @group cg-parsing
     */
    public function testParseGroup()
    {
        $name = 'europe';
        $channelGroup = new \Pubnub\ChannelGroup($name);
        $this->assertEquals('europe', $channelGroup->group);
        $this->assertNull($channelGroup->namespace);
    }

    /**
     * @group cg-parsing
     */
    public function testParseNamespacedGroup()
    {
        $name = 'news:europe';
        $channelGroup = new \Pubnub\ChannelGroup($name);
        $this->assertEquals('europe', $channelGroup->group);
        $this->assertEquals('news', $channelGroup->namespace);
    }

    /**
     * @group cg-parsing
     */
    public function testParseNamespace()
    {
        $name = 'news:';
        $channelGroup = new \Pubnub\ChannelGroup($name);
        $this->assertNull($channelGroup->group);
        $this->assertEquals('news', $channelGroup->namespace);
    }
}
