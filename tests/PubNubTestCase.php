<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubUtil;

abstract class PubNubTestCase extends TestCase
{
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
        $publishKey = getenv("PUBLISH_KEY");
        $subscribeKey = getenv("SUBSCRIBE_KEY");
        $publishKeyPam = getenv("PUBLISH_PAM_KEY");
        $subscribeKeyPam = getenv("SUBSCRIBE_PAM_KEY");
        $secretKeyPam = getenv("SECRET_PAM_KEY");

        parent::setUp();

        $this->config = new PNConfiguration();
        $this->config->setSubscribeKey($subscribeKey);
        $this->config->setPublishKey($publishKey);

        $this->config_enc = new PNConfiguration();
        $this->config_enc->setSubscribeKey($subscribeKey);
        $this->config_enc->setPublishKey($publishKey);
        $this->config_enc->setCipherKey(static::CIPHER_KEY);

        $this->config_pam = new PNConfiguration();
        $this->config_pam->setSubscribeKey($subscribeKeyPam);
        $this->config_pam->setPublishKey($publishKeyPam);
        $this->config_pam->setSecretKey($secretKeyPam);

        $this->pubnub = new PubNub($this->config);
        $this->pubnub_enc = new PubNub($this->config_enc);
        $this->pubnub_pam = new PubNub($this->config_pam);

        $this->pubnub->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        $this->pubnub_enc->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        $this->pubnub_pam->getLogger()->pushHandler(new \Monolog\Handler\ErrorLogHandler());

        $this->encodedSdkName = PubNubUtil::urlEncode($this->pubnub->getSdkFullName());
    }
}
