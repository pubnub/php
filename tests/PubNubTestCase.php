<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubUtil;

abstract class PubNubTestCase extends TestCase
{
    const PUBLISH_KEY = 'pub-c-139c0366-9b6a-4a3f-ac03-4f8d31c86df2';
    const SUBSCRIBE_KEY = 'sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe';

    const PUBLISH_KEY_PAM = "pub-c-0a5c823c-c1fd-4c3f-b31a-8a0b545fa463";
    const SUBSCRIBE_KEY_PAM = "sub-c-d69e3958-1528-11e7-bc52-02ee2ddab7fe";
    const SECRET_KEY_PAM = "sec-c-ZDAxMzk0ZmMtODE4ZC00YzA0LWIyOTYtMDMyZDVjOTM3ZjQ2";

    const CIPHER_KEY = "enigma";

    const SPECIAL_CHARACTERS = "-.,_~:/?#[]@!$&'()*+;=`|";
    const SPECIAL_CHANNEL = "-._~:/?#[]@!$&'()*+;=`|";


    /** @var Pubnub pubnub */
    protected $pubnub;

    /** @var PubNub pubnub_enc */
    protected $pubnub_enc;

    /** @var PubNub pubnub_pam */
    protected $pubnub_pam;

    /** @var PNConfiguration config */
    protected $config;

    /** @var PNConfiguration config */
    protected $config_enc;

    /** @var PNConfiguration config */
    protected $config_pam;

    /** @var  string */
    protected $encodedSdkName;

    public function setUp()
    {
        parent::setUp();

        $this->config = new PNConfiguration();
        $this->config->setSubscribeKey(static::SUBSCRIBE_KEY);
        $this->config->setPublishKey(static::PUBLISH_KEY);

        $this->config_enc = new PNConfiguration();
        $this->config_enc->setSubscribeKey(static::SUBSCRIBE_KEY);
        $this->config_enc->setPublishKey(static::PUBLISH_KEY);
        $this->config_enc->setCipherKey(static::CIPHER_KEY);

        $this->config_pam = new PNConfiguration();
        $this->config_pam->setSubscribeKey(static::SUBSCRIBE_KEY_PAM);
        $this->config_pam->setPublishKey(static::PUBLISH_KEY_PAM);
        $this->config_pam->setSecretKey(static::SECRET_KEY_PAM);

        $this->pubnub = new PubNub($this->config);
        $this->pubnub_enc = new PubNub($this->config_enc);
        $this->pubnub_pam = new PubNub($this->config_pam);

        $this->pubnub->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        $this->pubnub_enc->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        $this->pubnub_pam->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());

        $this->encodedSdkName = PubNubUtil::urlEncode($this->pubnub->getSdkFullName());
    }
}
