<?php

namespace Tests\Functional;

use PubNub\Exceptions\PubNubConfigurationException;
use PubNubTestCase;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PNConfiguration;
use PubNub\PubNub;

class UuidTest extends PubNubTestCase
{
    public function testValidateOnSet()
    {
        $this->expectException(PubNubConfigurationException::class);
        $this->expectExceptionMessage("UUID should not be empty");
        $config = new PNConfiguration();
        $config->setPublishKey('fake')
            ->setSubscribeKey('fake')
            ->setUuid([123]);
    }

    public function testValidateOnSetWhitespace()
    {
        $this->expectException(PubNubConfigurationException::class);
        $this->expectExceptionMessage("UUID should not be empty");
        $config = new PNConfiguration();
        $config->setPublishKey('fake')
            ->setSubscribeKey('fake')
            ->setUuid("   ");
    }

    public function testValidateOnInit()
    {
        $this->expectException(PubNubConfigurationException::class);
        $this->expectExceptionMessage("UUID should not be empty");
        $config = new PNConfiguration();
        $config->setPublishKey('fake')
            ->setSubscribeKey('fake');

        new PubNub($config);
    }
}
