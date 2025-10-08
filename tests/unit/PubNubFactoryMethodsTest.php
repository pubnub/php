<?php

namespace PubNubTests\unit

use PHPUnit\Framework\TestCase;

use PubNub\PubNub;
use PubNub\PNConfiguration;

class PubNubFactoryMethodsTest extends TestCase
{
    // ============================================================================
    // PubNub::demo() TESTS
    // ============================================================================

    public function testDemoReturnsValidPubNubInstance(): void
    {
        $pubnub = PubNub::demo();

        $this->assertInstanceOf(PubNub::class, $pubnub);
    }

    public function testDemoHasDemoKeys(): void
    {
        $pubnub = PubNub::demo();
        $config = $pubnub->getConfiguration();

        $this->assertEquals('demo', $config->getSubscribeKey());
        $this->assertEquals('demo', $config->getPublishKey());
    }

    public function testDemoHasDemoUserId(): void
    {
        $pubnub = PubNub::demo();
        $config = $pubnub->getConfiguration();

        $this->assertEquals('demo', $config->getUserId());
    }

    public function testDemoIsImmediatelyUsable(): void
    {
        $pubnub = PubNub::demo();

        // Should be able to call methods without configuration errors
        $config = $pubnub->getConfiguration();

        $this->assertNotNull($config->getSubscribeKey());
        $this->assertNotNull($config->getPublishKey());
        $this->assertNotNull($config->getUserId());
    }

    public function testDemoCreatesNewInstanceEachTime(): void
    {
        $pubnub1 = PubNub::demo();
        $pubnub2 = PubNub::demo();

        $this->assertNotSame($pubnub1, $pubnub2);
    }

    public function testDemoInstancesAreIndependent(): void
    {
        $pubnub1 = PubNub::demo();
        $pubnub2 = PubNub::demo();

        // Modify one instance
        $pubnub1->setToken('token1');
        $pubnub2->setToken('token2');

        $this->assertEquals('token1', $pubnub1->getToken());
        $this->assertEquals('token2', $pubnub2->getToken());
    }

    public function testDemoConfigurationIsLocked(): void
    {
        $pubnub = PubNub::demo();
        $config = $pubnub->getConfiguration();

        // Configuration should be locked after being passed to PubNub constructor
        $this->expectException(\PubNub\Exceptions\PubNubConfigurationException::class);
        $config->setPublishKey('new-key');
    }

    public function testDemoCanBeUsedForBasicOperations(): void
    {
        $pubnub = PubNub::demo();

        // Should be able to create endpoint builders
        $this->assertNotNull($pubnub->publish());
        $this->assertNotNull($pubnub->subscribe());
        $this->assertNotNull($pubnub->time());
    }

    // ============================================================================
    // PNConfiguration::demoKeys() TESTS
    // ============================================================================

    public function testDemoKeysReturnsValidConfiguration(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertInstanceOf(PNConfiguration::class, $config);
    }

    public function testDemoKeysHasSubscribeKey(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertEquals('demo', $config->getSubscribeKey());
    }

    public function testDemoKeysHasPublishKey(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertEquals('demo', $config->getPublishKey());
    }

    public function testDemoKeysHasUserId(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertEquals('demo', $config->getUserId());
    }

    public function testDemoKeysConfigurationIsNotLocked(): void
    {
        $config = PNConfiguration::demoKeys();

        // Should be able to modify the configuration
        $config->setPublishKey('new-pub-key');
        $this->assertEquals('new-pub-key', $config->getPublishKey());

        $config->setSubscribeKey('new-sub-key');
        $this->assertEquals('new-sub-key', $config->getSubscribeKey());

        // Test other modifiable properties (userId has special handling due to UUID/UserId distinction)
        $config->setSecure(false);
        $this->assertFalse($config->isSecure());

        $config->setOrigin('custom.origin.com');
        $this->assertEquals('custom.origin.com', $config->getOrigin());
    }

    public function testDemoKeysCreatesNewInstanceEachTime(): void
    {
        $config1 = PNConfiguration::demoKeys();
        $config2 = PNConfiguration::demoKeys();

        $this->assertNotSame($config1, $config2);
    }

    public function testDemoKeysInstancesAreIndependent(): void
    {
        $config1 = PNConfiguration::demoKeys();
        $config2 = PNConfiguration::demoKeys();

        $config1->setPublishKey('key1');
        $config2->setPublishKey('key2');

        $this->assertEquals('key1', $config1->getPublishKey());
        $this->assertEquals('key2', $config2->getPublishKey());
    }

    public function testDemoKeysCanBeCustomized(): void
    {
        $config = PNConfiguration::demoKeys();

        // Customize the demo configuration
        $config->setSecure(false);
        $config->setOrigin('custom-origin.pubnub.com');
        $config->setAuthKey('auth-key-123');

        $this->assertFalse($config->isSecure());
        $this->assertEquals('custom-origin.pubnub.com', $config->getOrigin());
        $this->assertEquals('auth-key-123', $config->getAuthKey());
    }

    public function testDemoKeysCanBeUsedToCreatePubNub(): void
    {
        $config = PNConfiguration::demoKeys();
        $pubnub = new PubNub($config);

        $this->assertInstanceOf(PubNub::class, $pubnub);

        $retrievedConfig = $pubnub->getConfiguration();
        $this->assertEquals('demo', $retrievedConfig->getSubscribeKey());
        $this->assertEquals('demo', $retrievedConfig->getPublishKey());
        $this->assertEquals('demo', $retrievedConfig->getUserId());
    }

    public function testDemoKeysHasDefaultSecureSettings(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertTrue($config->isSecure());
    }

    public function testDemoKeysHasDefaultTimeouts(): void
    {
        $config = PNConfiguration::demoKeys();

        $this->assertEquals(10, $config->getNonSubscribeRequestTimeout());
        $this->assertEquals(310, $config->getSubscribeTimeout());
        $this->assertEquals(10, $config->getConnectTimeout());
    }

    // ============================================================================
    // INTEGRATION TESTS
    // ============================================================================

    public function testDemoMethodUsesDemoKeysInternally(): void
    {
        $demoConfig = PNConfiguration::demoKeys();
        $demoPubNub = PubNub::demo();

        $config = $demoPubNub->getConfiguration();

        // Should have same values as demoKeys()
        $this->assertEquals($demoConfig->getSubscribeKey(), $config->getSubscribeKey());
        $this->assertEquals($demoConfig->getPublishKey(), $config->getPublishKey());
        $this->assertEquals($demoConfig->getUserId(), $config->getUserId());
    }

    public function testDemoKeysAndDemoProduceSimilarResults(): void
    {
        $configFromDemoKeys = PNConfiguration::demoKeys();
        $pubnubFromDemo = PubNub::demo();
        $configFromDemo = $pubnubFromDemo->getConfiguration();

        $this->assertEquals(
            $configFromDemoKeys->getSubscribeKey(),
            $configFromDemo->getSubscribeKey()
        );
        $this->assertEquals(
            $configFromDemoKeys->getPublishKey(),
            $configFromDemo->getPublishKey()
        );
        $this->assertEquals(
            $configFromDemoKeys->getUserId(),
            $configFromDemo->getUserId()
        );
    }

    public function testDemoKeysCanBeCloned(): void
    {
        $config = PNConfiguration::demoKeys();
        $cloned = $config->clone();

        $this->assertNotSame($config, $cloned);
        $this->assertEquals('demo', $cloned->getSubscribeKey());
        $this->assertEquals('demo', $cloned->getPublishKey());
        $this->assertEquals('demo', $cloned->getUserId());
    }

    public function testMultipleDemoInstancesCanCoexist(): void
    {
        $pubnub1 = PubNub::demo();
        $pubnub2 = PubNub::demo();
        $pubnub3 = PubNub::demo();

        $this->assertInstanceOf(PubNub::class, $pubnub1);
        $this->assertInstanceOf(PubNub::class, $pubnub2);
        $this->assertInstanceOf(PubNub::class, $pubnub3);

        $this->assertNotSame($pubnub1, $pubnub2);
        $this->assertNotSame($pubnub2, $pubnub3);
        $this->assertNotSame($pubnub1, $pubnub3);
    }
}
