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
    public function testGrantAllNonNamespacedChannelGroup()
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
    public function testGrantUserNonNamespacedChannelGroup()
    {
        $this->pubnub_secret->pamGrantChannelGroup(true, true, $this->channelGroup, $this->authKey);
        $response = $this->pubnub_secret->pamAuditChannelGroup($this->channelGroup, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantAllNamespacedChannelGroup()
    {
        $namespacedChannelGroup = $this->namespace . ":" . $this->channelGroup;

        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespacedChannelGroup);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespacedChannelGroup);
        $auths = $response["payload"]["channel-groups"][$namespacedChannelGroup];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantUserNamespacedChannelGroup()
    {
        $namespacedChannelGroup = $this->namespace . ":" . $this->channelGroup;

        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespacedChannelGroup, $this->authKey);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespacedChannelGroup, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals($namespacedChannelGroup, $response["payload"]["channel-group"]);
        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantAllNamespace()
    {
        $namespace = $this->namespace . ":";

        $this->pubnub_secret->pamRevokeChannelGroup($namespace);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace);
        $auths = $response["payload"]["channel-groups"][$namespace];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("0", $auths["r"]);
        $this->assertEquals("0", $auths["m"]);


        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespace);
        sleep(5);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace);
        $auths = $response["payload"]["channel-groups"][$namespace];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantUserNamespace()
    {
        $namespace = $this->namespace . ":";

        $this->pubnub_secret->pamRevokeChannelGroup($namespace, $this->authKey);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals($namespace, $response["payload"]["channel-group"]);
        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("0", $auths["r"]);
        $this->assertEquals("0", $auths["m"]);


        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespace, $this->authKey);
        sleep(5);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals($namespace, $response["payload"]["channel-group"]);
        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantAllGlobalNamespace()
    {
        $namespace = ":";

        $this->pubnub_secret->pamRevokeChannelGroup($namespace);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace);
        $auths = $response["payload"]["channel-groups"][$namespace];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("0", $auths["r"]);
        $this->assertEquals("0", $auths["m"]);


        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespace);
        sleep(5);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace);
        $auths = $response["payload"]["channel-groups"][$namespace];

        $this->assertEquals("channel-group", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }

    /**
     * @group pam-cg
     */
    public function testGrantUserGlobalNamespace()
    {
        $namespace = ":";

        $this->pubnub_secret->pamRevokeChannelGroup($namespace, $this->authKey);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals($namespace, $response["payload"]["channel-group"]);
        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("0", $auths["r"]);
        $this->assertEquals("0", $auths["m"]);


        $this->pubnub_secret->pamGrantChannelGroup(true, true, $namespace, $this->authKey);
        sleep(5);
        $response = $this->pubnub_secret->pamAuditChannelGroup($namespace, $this->authKey);
        $auths = $response["payload"]["auths"][$this->authKey];

        $this->assertEquals($namespace, $response["payload"]["channel-group"]);
        $this->assertEquals("channel-group+auth", $response["payload"]["level"]);
        $this->assertEquals("0", $auths["w"]);
        $this->assertEquals("1", $auths["r"]);
        $this->assertEquals("1", $auths["m"]);
    }
}