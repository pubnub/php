<?php

namespace Tests\Integrational;

use PubNub\PNConfiguration;
use PubNub\PubNub;

class SslTest extends \PubNubTestCase
{
    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslIsSetByDefault()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUserId('demo');
        $pubnub = new PubNub($config);

        $this->assertTrue($pubnub->getConfiguration()->isSecure());
    }

    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslCanBeDisabled()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUserId('demo');
        $config->setSecure(false);
        $pubnub = new PubNub($config);

        $this->assertFalse($pubnub->getConfiguration()->isSecure());
    }
}
