<?php

use PHPUnit\Framework\TestCase;
use PubNub\Managers\BasePathManager;
use PubNub\PNConfiguration;

class BasePathManagerTest extends TestCase
{
    public function testGetBasePathWithDefaultSettings()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('https://ps.pndsn.com', $basePath);
    }

    public function testGetBasePathWithCustomOrigin()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setOrigin('custom.pubnub.com');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('https://custom.pubnub.com', $basePath);
    }

    public function testGetBasePathWithCustomHost()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath('special.pubnub.com');
        
        $this->assertEquals('https://special.pubnub.com', $basePath);
    }

    public function testGetBasePathWithCustomHostOverridesOrigin()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setOrigin('config-origin.pubnub.com');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath('param-host.pubnub.com');
        
        $this->assertEquals('https://param-host.pubnub.com', $basePath);
    }

    public function testGetBasePathWithInsecureConnection()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setSecure(false);
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('http://ps.pndsn.com', $basePath);
    }

    public function testGetBasePathWithInsecureAndCustomOrigin()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setSecure(false);
        $config->setOrigin('insecure.pubnub.com');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('http://insecure.pubnub.com', $basePath);
    }

    public function testGetBasePathWithIPAddress()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setOrigin('192.168.1.100');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('https://192.168.1.100', $basePath);
    }

    public function testGetBasePathWithPortNumber()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setOrigin('localhost:8080');
        
        $manager = new BasePathManager($config);
        
        $basePath = $manager->getBasePath();
        
        $this->assertEquals('https://localhost:8080', $basePath);
    }

    public function testGetBasePathMultipleCalls()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $manager = new BasePathManager($config);
        
        $basePath1 = $manager->getBasePath();
        $basePath2 = $manager->getBasePath();
        $basePath3 = $manager->getBasePath();
        
        $this->assertEquals($basePath1, $basePath2);
        $this->assertEquals($basePath2, $basePath3);
    }

    public function testGetBasePathWithDifferentCustomHosts()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $manager = new BasePathManager($config);
        
        $basePath1 = $manager->getBasePath('host1.pubnub.com');
        $basePath2 = $manager->getBasePath('host2.pubnub.com');
        
        $this->assertEquals('https://host1.pubnub.com', $basePath1);
        $this->assertEquals('https://host2.pubnub.com', $basePath2);
    }
}
