<?php

use Pubnub\Pubnub;
use Pubnub\PubnubPAM;

class PAMChannelGroupTest extends PAMTestCase
{
    protected $channelGroup;
    protected $namespace;
    protected $authKey;

    public function setUp()
    {
        parent::setUp();

        $this->channelGroup = "ptest-" . rand();
        $this->namespace = 'ptest-namespace';
        $this->authKey = "user-ptest";
    }

    /**
     * @group pam-cg
     */
    public function testGrantAllChannelGroup()
    {
        $this->pubnub_secret->pamGrantChannelGroup(true, true, $this->channelGroup);
        $response = $this->pubnub_secret->pamAuditChannelGroup($this->channelGroup);
        $auths = $response["payload"]["channel-groups"][$this->channelGroup];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantUserChannelGroup()
    {
        $this->pubnub_secret->pamGrantChannelGroup(true, true, $this->channelGroup, $this->authKey);
        $response = $this->pubnub_secret->pamAuditChannelGroup($this->channelGroup, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }
}