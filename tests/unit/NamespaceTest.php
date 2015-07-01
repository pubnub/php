<?php

use Pubnub\Pubnub;

class NamespaceTest extends TestCase
{
    private $ch;
    private $channelGroup;
    private $channelNamespace;

    public static function setUpBeforeClass()
    {
        self::cleanup();
    }

    public function setUp()
    {
        parent::setUp();
        $this->ch = "ch1" . rand();
        $this->channelGroup = "ptest-" . rand();
        $this->channelNamespace = "ptest-namespace-" . rand();
    }

    /**
     * @group cg
     * @group cg-namespace
     */
    public function testListAndRemoveNamespace()
    {
        $this->pubnub->channelGroupAddChannel(
            $this->channelNamespace . ":" . $this->channelGroup,
            array($this->ch)
        );

        sleep(1);
        $result = $this->pubnub->channelGroupListNamespaces();
        $this->assertTrue(in_array($this->channelNamespace, $result["payload"]["namespaces"]));

        $result = $this->pubnub->channelGroupRemoveNamespace($this->channelNamespace);
        $this->assertEquals('OK', $result['message']);

        sleep(1);
        $result = $this->pubnub->channelGroupListNamespaces();
        $this->assertFalse(in_array($this->channelNamespace, $result["payload"]["namespaces"]));
    }
}