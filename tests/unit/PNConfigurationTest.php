<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;

use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubConfigurationException;
use PubNub\PNConfiguration;
use PubNub\PubNubUtil;

class PNConfigurationTest extends TestCase
{
    public function testInitWithUUID()
    {
        $config = new PNConfiguration();
        $config->setUuid('foo-bar-baz');
        $this->assertEquals($config->getUuid(), 'foo-bar-baz');
    }

    public function testInitWithUserId()
    {
        $config = new PNConfiguration();
        $config->setUserId('foo-bar-baz');
        $this->assertEquals($config->getUserId(), 'foo-bar-baz');
    }

    public function testThrowOnUserIdAndUUID()
    {
        $this->expectException(PubNubConfigurationException::class);
        $this->expectExceptionMessage("Cannot use UserId and UUID simultaneously");
        $config = new PNConfiguration();
        $config->setUserId('foo-bar-baz');
        $config->setUuid('foo-bar-baz');
    }

    public function testThrowOnEmptyUserId()
    {
        $this->expectException(PubNubConfigurationException::class);
        $this->expectExceptionMessage("UserID should not be empty");
        $config = new PNConfiguration();
        $config->setUserId('');
    }
}
