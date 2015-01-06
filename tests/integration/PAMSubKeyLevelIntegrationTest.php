<?php

use Pubnub\Pubnub;
use Pubnub\PubnubPAM;

class PAMSubKeyLevelIntegrationTest extends PAMTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->pubnub_secret->revoke();

        sleep(5);
    }

    /**
     * @group pam
     * @group pam-sub-key
     */
    public function testGrantNoReadNoWrite()
    {
        $this->pubnub_secret->grant(0, 0);

        $response = $this->pubnub_secret->audit();

        $this->assertEquals('0', $response['payload']['r']);
        $this->assertEquals('0', $response['payload']['w']);
        $this->assertEquals('subkey', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-sub-key
     */
    public function testGrantReadNoWrite()
    {
        $this->pubnub_secret->grant(1, 0);

        $response = $this->pubnub_secret->audit();

        $this->assertEquals('1', $response['payload']['r']);
        $this->assertEquals('0', $response['payload']['w']);
        $this->assertEquals('subkey', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-sub-key
     */
    public function testGrantNoReadWrite()
    {
        $this->pubnub_secret->grant(0, 1);

        $response = $this->pubnub_secret->audit();

        $this->assertEquals('0', $response['payload']['r']);
        $this->assertEquals('1', $response['payload']['w']);
        $this->assertEquals('subkey', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-sub-key
     */
    public function testGrantReadWrite()
    {
        $this->pubnub_secret->grant(1, 1);

        $response = $this->pubnub_secret->audit();

        $this->assertEquals('1', $response['payload']['r']);
        $this->assertEquals('1', $response['payload']['w']);
        $this->assertEquals('subkey', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-sub-key
     */
    public function testRevoke()
    {
        $this->pubnub_secret->grant(1, 1);

        $response = $this->pubnub_secret->revoke();

        $this->assertEquals('0', $response['payload']['w']);
        $this->assertEquals('0', $response['payload']['r']);
        $this->assertEquals('subkey', $response['payload']['level']);
    }
}
 