<?php

use Pubnub\Pubnub;
use Pubnub\PubnubPAM;

class PAMIntegrationTest extends TestCase
{
    /** @var  PubnubPAM */
    protected $pam;
    protected $pubnub_secret;
    protected $channel_public = 'pubnub_php_test_grant_public';
    protected $channel_private = 'pubnub_php_test_grant_private';

    protected static $publish = 'pub-c-81d9633a-c5a0-4d6c-9600-fda148b61648';
    protected static $subscribe = 'sub-c-35ffee42-e763-11e3-afd8-02ee2ddab7fe';
    protected static $secret = 'sec-c-NDNlODA0ZmItNzZhMC00OTViLWI5NWMtM2M4MzA4ZWM2ZjIz';
    protected static $pnsdk = 'Pubnub-PHP/3.6.0';
    protected static $access_key = 'abcd';
    protected static $message = 'hello from grant() test';

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_secret = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish,
            'secret_key' => self::$secret
        ));
    }

    /**
     * @group pam
     */
    public function testGrantNoReadNoWrite()
    {
        $response = $this->pubnub_secret->grant($this->channel_public, 0, 0, null, 10);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     */
    public function testGrantReadNoWrite()
    {
        $response = $this->pubnub_secret->grant($this->channel_public, 1, 0, null, 10);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     */
    public function testGrantNoReadWrite()
    {
        $response = $this->pubnub_secret->grant($this->channel_public, 0, 1, null, 10);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     */
    public function testGrantReadWrite()
    {
        $response = $this->pubnub_secret->grant($this->channel_public, 1, 1, null, 10);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     */
    public function testGrantReadWritePrivate()
    {
        $response = $this->pubnub_secret->grant($this->channel_private, 1, 1, self::$access_key, 10);
        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
        $this->assertEquals($this->channel_private, $response['payload']['channel']);
    }

    /**
     * @group pam
     */
    public function testAuditAuthKey()
    {
        // granting read-only access to user
        $this->pubnub_secret->grant($this->channel_private, 1, 0, self::$access_key, 10);
        $response = $this->pubnub_secret->audit($this->channel_private, self::$access_key);
        $this->assertEquals('1', $response['payload']['auths'][self::$access_key]['r']);
        $this->assertEquals('0', $response['payload']['auths'][self::$access_key]['w']);
        $this->assertEquals('user', $response['payload']['level']);
        $this->assertEquals($this->channel_private, $response['payload']['channel']);
    }

    /**
     * @group pam
     */
    public function testAuditNoAuth()
    {
        // granting rw access to admin, r to user, non-avail to users w\o auth key
        $this->pubnub_secret->grant($this->channel_private, 1, 1, 'admin_key', 10);
        $this->pubnub_secret->grant($this->channel_private, 1, 0, 'user_key', 10);
        $this->pubnub_secret->grant($this->channel_private, 0, 0, null, 10);

        $response = $this->pubnub_secret->audit($this->channel_private);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_private]['auths']['admin_key']['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_private]['auths']['user_key']['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_private]['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_private]['r']);
    }

    /**
     * @group pam1
     */
    public function testNewInstancesWithAuthKey()
    {
        $this->pubnub_secret->grant($this->channel_private, 1, 1, 'admin_key', 10);
        $this->pubnub_secret->grant($this->channel_private, 1, 0, 'user_key', 10);
        $this->pubnub_secret->grant($this->channel_private, 0, 0, null, 10);

        $nonAuthorizedClient = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish
        ));

        $authorizedClient = new Pubnub(array(
            'subscribe_key' => self::$subscribe,
            'publish_key' => self::$publish,
            'auth_key' => 'admin_key'
        ));

        $authorizedResponse = $authorizedClient->publish($this->channel_private, 'hi');
        $nonAuthorizedResponse = $nonAuthorizedClient->publish($this->channel_private, 'hi');

        $this->assertEquals(1, $authorizedResponse[0]);
        $this->assertEquals(403, $nonAuthorizedResponse['status']);

        $nonAuthorizedClient->setAuthKey('admin_key');
        $nonAuthorizedResponse = $nonAuthorizedClient->publish($this->channel_private, 'hi');

        $this->assertEquals(1, $nonAuthorizedResponse[0]);
    }

    /**
     * @group pam
     */
    public function testRevoke()
    {
        $this->pubnub_secret->grant($this->channel_private, 1, 1, 'admin_key', 10);
        $response = $this->pubnub_secret->revoke($this->channel_private, 'admin_key');
        $this->assertEquals('0', $response['payload']['auths']['admin_key']['w']);
        $this->assertEquals('0', $response['payload']['auths']['admin_key']['r']);
    }
}
 