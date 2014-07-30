<?php

require_once('../../legacy/Pubnub.php');
require_once('../../legacy/PubnubPAM.php');
require_once('TestCase.php');

class PAMUserLevelIntegrationTest extends TestCase
{
    /** @var  PubnubPAM */
    protected $pam;
    /** @var  Pubnub */
    protected $pubnub_secret;
    protected $channel;

    protected static $publish = 'pub-c-81d9633a-c5a0-4d6c-9600-fda148b61648';
    protected static $subscribe = 'sub-c-35ffee42-e763-11e3-afd8-02ee2ddab7fe';
    protected static $secret = 'sec-c-NDNlODA0ZmItNzZhMC00OTViLWI5NWMtM2M4MzA4ZWM2ZjIz';
    protected static $pnsdk = 'Pubnub-PHP/3.6.0';
    protected static $access_key = 'abcd';
    protected static $message = 'hello from grant() test';

    public function setUp()
    {
        parent::setUp();

        $this->channel = 'pubnub_php_test_pam_' . phpversion() . time();

        $this->pubnub_secret = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish,
            'secret_key' => self::$secret
        ));

        $this->pubnub_secret->revoke();

        sleep(5);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testGrantNoReadNoWrite()
    {
        $this->pubnub_secret->grant(0, 0, $this->channel, self::$access_key);

        $response = $this->pubnub_secret->audit($this->channel, self::$access_key);

        $this->assertEquals('0', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('0', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
        $this->assertEquals($this->channel, $response['payload']['channel']);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testGrantReadNoWrite()
    {
        $this->pubnub_secret->grant(1, 0, $this->channel, self::$access_key);

        $response = $this->pubnub_secret->audit($this->channel, self::$access_key);

        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('0', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testGrantNoReadWrite()
    {
        $this->pubnub_secret->grant(0, 1, $this->channel, self::$access_key);

        $response = $this->pubnub_secret->audit($this->channel, self::$access_key);

        $this->assertEquals('0', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testGrantReadWrite()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel, self::$access_key);

        $response = $this->pubnub_secret->audit($this->channel, self::$access_key);

        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testAuditNoAuth()
    {
        // granting rw access to admin, r to user, non-avail to users w\o auth key
        $this->pubnub_secret->grant(1, 1, $this->channel, 'admin_key', 10);
        $this->pubnub_secret->grant(1, 0, $this->channel, 'user_key', 10);
        $this->pubnub_secret->grant(0, 0, $this->channel);

        $response = $this->pubnub_secret->audit($this->channel);

        $this->assertEquals('1', $response['payload']['channels'][$this->channel]['auths']['admin_key']['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['auths']['user_key']['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel]['r']);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testNewInstancesWithAuthKey()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel, 'admin_key', 10);
        $this->pubnub_secret->grant(1, 0, $this->channel, 'user_key', 10);
        $this->pubnub_secret->grant(0, 0, $this->channel, null, 10);

        $nonAuthorizedClient = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish
        ));

        $authorizedClient = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish,
            'auth_key' => 'admin_key'
        ));

        $authorizedResponse = $authorizedClient->publish($this->channel, 'hi');
        $nonAuthorizedResponse = $nonAuthorizedClient->publish($this->channel, 'hi');

        $this->assertEquals(1, $authorizedResponse[0]);
        $this->assertEquals(403, $nonAuthorizedResponse['status']);

        $nonAuthorizedClient->setAuthKey('admin_key');
        $nonAuthorizedResponse = $nonAuthorizedClient->publish($this->channel, 'hi');

        $this->assertEquals(1, $nonAuthorizedResponse[0]);
    }

    /**
     * @group pam
     * @group pam-user
     */
    public function testRevoke()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel, 'admin_key');
        $this->pubnub_secret->revoke($this->channel, 'admin_key');

        $response = $this->pubnub_secret->audit($this->channel, 'admin_key');

        $this->assertEquals('0', $response['payload']['auths']['admin_key']['w']);
        $this->assertEquals('0', $response['payload']['auths']['admin_key']['r']);
    }
}
