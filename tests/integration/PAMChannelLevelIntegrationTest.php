<?php

use Pubnub\Pubnub;
use Pubnub\PubnubPAM;

class PAMChannelLevelIntegrationTest extends TestCase
{
    /** @var  PubnubPAM */
    protected $pam;
    /** @var  Pubnub */
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

        $this->pubnub_secret->revoke();

        sleep(5);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantNoReadNoWrite()
    {
        $this->pubnub_secret->grant(0, 0, $this->channel_public);

        $response = $this->pubnub_secret->audit($this->channel_public);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantReadNoWrite()
    {
        $this->pubnub_secret->grant(1, 0, $this->channel_public);

        $response = $this->pubnub_secret->audit($this->channel_public);

        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantNoReadWrite()
    {
        $this->pubnub_secret->grant(0, 1, $this->channel_public);

        $response = $this->pubnub_secret->audit($this->channel_public);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testGrantReadWrite()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel_public);

        $response = $this->pubnub_secret->audit($this->channel_public);

        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['r']);
        $this->assertEquals('1', $response['payload']['channels'][$this->channel_public]['w']);
        $this->assertEquals('channel', $response['payload']['level']);
    }

    /**
     * @group pam
     * @group pam-channel
     */
    public function testRevoke()
    {
        $this->pubnub_secret->grant(1, 1, $this->channel_private, 10);

        $response = $this->pubnub_secret->revoke($this->channel_private);

        $this->assertEquals('0', $response['payload']['channels'][$this->channel_private]['w']);
        $this->assertEquals('0', $response['payload']['channels'][$this->channel_private]['r']);
    }
}
 