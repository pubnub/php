<?php

use Pubnub\Pubnub;
use Pubnub\PubnubPAM;

class PAMChannelLevelIntegrationTest extends PAMTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_secret->revoke();

        sleep(5);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantNoReadNoWrite()
    {
        $this->pubnub_secret->grant(0, 0, $this->channel);

        $response = $this->pubnub_secret->audit($this->channel);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantReadNoWrite()
    {
        $this->pubnub_secret->grant(1, 0, $this->channel);

        $response = $this->pubnub_secret->audit($this->channel);

        $this->assertEquals('1', $response['payload']['channels'][$this->channel]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantNoReadWrite()
    {
        $this->pubnub_secret->grant(0, 1, $this->channel);

        $response = $this->pubnub_secret->audit($this->channel);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantReadWrite()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel);

        $response = $this->pubnub_secret->audit($this->channel);

        $this->assertEquals('1', $response['payload']['channels'][$this->channel]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testRevoke()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel, 10);

        $response = $this->pubnub_secret->revoke($this->channel);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['r']);
    }
}
 