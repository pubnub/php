<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;

abstract class PubNubTestCase extends TestCase
{
    const SUBSCRIBE_KEY = 'sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe';
    const PUBLISH_KEY = 'pub-c-139c0366-9b6a-4a3f-ac03-4f8d31c86df2';

    /** @var Pubnub pubnub */
    protected $pubnub;

    /** @var PubNub pubnub_enc */
    protected $pubnub_enc;

    /** @var PNConfiguration config */
    protected $config;

    public function setUp()
    {
        parent::setUp();

        $this->config = new PNConfiguration();
        $this->config->setSubscribeKey(static::SUBSCRIBE_KEY);
        $this->config->setPublishKey(static::PUBLISH_KEY);

        $config_enc = new PNConfiguration();
        $config_enc->setSubscribeKey(static::SUBSCRIBE_KEY);
        $config_enc->setPublishKey(static::PUBLISH_KEY);

        $this->pubnub = new PubNub($config_enc);
        $this->pubnub_enc = new PubNub($config_enc);
    }

    protected static function setupVCR()
    {
        $dir = realpath(dirname(__FILE__)) . "/fixtures";

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        \VCR\VCR::configure()->setCassettePath($dir);
    }
}
