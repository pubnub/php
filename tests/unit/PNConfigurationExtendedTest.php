<?php

namespace PubNubTests\unit

use PHPUnit\Framework\TestCase;

use PubNub\PNConfiguration;
use PubNub\CryptoModule;

class PNConfigurationExtendedTest extends TestCase
{
    // ============================================================================
    // TIMEOUT CONFIGURATION TESTS
    // ============================================================================

    public function testGetNonSubscribeRequestTimeoutReturnsDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertEquals(10, $config->getNonSubscribeRequestTimeout());
    }

    public function testSetNonSubscribeRequestTimeout(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setNonSubscribeRequestTimeout(20);

        $this->assertEquals(20, $config->getNonSubscribeRequestTimeout());
    }

    public function testSetNonSubscribeRequestTimeoutWithZero(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setNonSubscribeRequestTimeout(0);

        $this->assertEquals(0, $config->getNonSubscribeRequestTimeout());
    }

    public function testSetNonSubscribeRequestTimeoutWithLargeValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setNonSubscribeRequestTimeout(3600);

        $this->assertEquals(3600, $config->getNonSubscribeRequestTimeout());
    }

    public function testGetSubscribeTimeoutReturnsDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertEquals(310, $config->getSubscribeTimeout());
    }

    public function testSetSubscribeTimeout(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSubscribeTimeout(400);

        $this->assertEquals(400, $config->getSubscribeTimeout());
    }

    public function testSetSubscribeTimeoutWithMinimalValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSubscribeTimeout(1);

        $this->assertEquals(1, $config->getSubscribeTimeout());
    }

    public function testSetSubscribeTimeoutWithLargeValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSubscribeTimeout(10000);

        $this->assertEquals(10000, $config->getSubscribeTimeout());
    }

    public function testGetConnectTimeoutReturnsDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertEquals(10, $config->getConnectTimeout());
    }

    public function testSetConnectTimeout(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setConnectTimeout(15);

        $this->assertEquals(15, $config->getConnectTimeout());
    }

    public function testSetConnectTimeoutWithMinimalValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setConnectTimeout(1);

        $this->assertEquals(1, $config->getConnectTimeout());
    }

    public function testSetConnectTimeoutWithLargeValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setConnectTimeout(300);

        $this->assertEquals(300, $config->getConnectTimeout());
    }

    public function testAllTimeoutSettingsTogether(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setNonSubscribeRequestTimeout(25);
        $config->setSubscribeTimeout(350);
        $config->setConnectTimeout(20);

        $this->assertEquals(25, $config->getNonSubscribeRequestTimeout());
        $this->assertEquals(350, $config->getSubscribeTimeout());
        $this->assertEquals(20, $config->getConnectTimeout());
    }

    // ============================================================================
    // SECURITY CONFIGURATION TESTS
    // ============================================================================

    public function testIsSecureReturnsDefaultTrue(): void
    {
        $config = new PNConfiguration();

        $this->assertTrue($config->isSecure());
    }

    public function testSetSecureFalse(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSecure(false);

        $this->assertFalse($config->isSecure());
    }

    public function testSetSecureTrue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSecure(false);
        $this->assertFalse($config->isSecure());

        $config->setSecure(true);
        $this->assertTrue($config->isSecure());
    }

    public function testSetSecureDefaultsToTrue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setSecure(false);
        $config->setSecure(); // No parameter, should default to true

        $this->assertTrue($config->isSecure());
    }

    public function testSetSecureCanBeToggled(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $this->assertTrue($config->isSecure());

        $config->setSecure(false);
        $this->assertFalse($config->isSecure());

        $config->setSecure(true);
        $this->assertTrue($config->isSecure());
    }

    // ============================================================================
    // ORIGIN CONFIGURATION TESTS
    // ============================================================================

    public function testGetOriginReturnsNullByDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getOrigin());
    }

    public function testSetOriginAndGetOrigin(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setOrigin('custom.pubnub.com');

        $this->assertEquals('custom.pubnub.com', $config->getOrigin());
    }

    public function testSetOriginWithIPAddress(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setOrigin('192.168.1.100');

        $this->assertEquals('192.168.1.100', $config->getOrigin());
    }

    public function testSetOriginWithPort(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setOrigin('localhost:8080');

        $this->assertEquals('localhost:8080', $config->getOrigin());
    }

    public function testSetOriginOverwritesPrevious(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setOrigin('first-origin.com');
        $this->assertEquals('first-origin.com', $config->getOrigin());

        $config->setOrigin('second-origin.com');
        $this->assertEquals('second-origin.com', $config->getOrigin());
    }

    // ============================================================================
    // AUTH KEY CONFIGURATION TESTS
    // ============================================================================

    public function testGetAuthKeyReturnsNullByDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getAuthKey());
    }

    public function testSetAuthKeyAndGetAuthKey(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setAuthKey('my-auth-key');

        $this->assertEquals('my-auth-key', $config->getAuthKey());
    }

    public function testSetAuthKeyWithEmptyString(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setAuthKey('');

        $this->assertEquals('', $config->getAuthKey());
    }

    public function testSetAuthKeyWithSpecialCharacters(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $authKey = 'auth!@#$%^&*()_+-={}[]|:;<>?,./';
        $config->setAuthKey($authKey);

        $this->assertEquals($authKey, $config->getAuthKey());
    }

    public function testSetAuthKeyOverwritesPrevious(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setAuthKey('first-key');
        $this->assertEquals('first-key', $config->getAuthKey());

        $config->setAuthKey('second-key');
        $this->assertEquals('second-key', $config->getAuthKey());
    }

    // ============================================================================
    // FILTER EXPRESSION TESTS
    // ============================================================================

    public function testGetFilterExpressionReturnsNullByDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getFilterExpression());
    }

    public function testSetFilterExpressionAndGetFilterExpression(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setFilterExpression('uuid == "user123"');

        $this->assertEquals('uuid == "user123"', $config->getFilterExpression());
    }

    public function testSetFilterExpressionWithComplexExpression(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $expression = 'uuid == "admin" && region == "us-east"';
        $config->setFilterExpression($expression);

        $this->assertEquals($expression, $config->getFilterExpression());
    }

    public function testSetFilterExpressionOverwritesPrevious(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setFilterExpression('first-expression');
        $this->assertEquals('first-expression', $config->getFilterExpression());

        $config->setFilterExpression('second-expression');
        $this->assertEquals('second-expression', $config->getFilterExpression());
    }

    // ============================================================================
    // CRYPTO CONFIGURATION TESTS
    // ============================================================================

    public function testIsAesEnabledReturnsFalseByDefault(): void
    {
        $config = new PNConfiguration();

        $this->assertFalse($config->isAesEnabled());
    }

    public function testIsAesEnabledReturnsTrueWhenCipherKeySet(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setCipherKey('test-key');

        $this->assertTrue($config->isAesEnabled());
    }

    public function testIsAesEnabledReturnsTrueWhenCryptoModuleSet(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $cryptoModule = CryptoModule::legacyCryptor('key', false);
        $config->setCryptoModule($cryptoModule);

        $this->assertTrue($config->isAesEnabled());
    }

    public function testGetCryptoSafeReturnsNullWhenNotConfigured(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getCryptoSafe());
    }

    public function testGetCryptoSafeReturnsCryptoWhenConfigured(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setCipherKey('test-key');

        $crypto = $config->getCryptoSafe();

        $this->assertInstanceOf(CryptoModule::class, $crypto);
    }

    public function testGetCryptoSafeDoesNotThrowException(): void
    {
        $config = new PNConfiguration();

        // This should not throw an exception
        $crypto = $config->getCryptoSafe();

        $this->assertNull($crypto);
    }

    public function testSetCryptoModuleAndGetCryptoSafe(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $cryptoModule = CryptoModule::aesCbcCryptor('my-key', true);
        $config->setCryptoModule($cryptoModule);

        $crypto = $config->getCryptoSafe();

        $this->assertSame($cryptoModule, $crypto);
    }

    public function testGetUseRandomIVReturnsDefaultTrue(): void
    {
        $config = new PNConfiguration();

        $this->assertTrue($config->getUseRandomIV());
    }

    public function testSetUseRandomIVFalse(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setUseRandomIV(false);

        $this->assertFalse($config->getUseRandomIV());
    }

    public function testSetUseRandomIVTrue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');

        $config->setUseRandomIV(false);
        $config->setUseRandomIV(true);

        $this->assertTrue($config->getUseRandomIV());
    }

    // ============================================================================
    // KEY GETTERS TESTS
    // ============================================================================

    public function testGetPublishKeyReturnsNull(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getPublishKey());
    }

    public function testGetPublishKeyReturnsSetValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setPublishKey('pub-key-123');

        $this->assertEquals('pub-key-123', $config->getPublishKey());
    }

    public function testGetSecretKeyReturnsNull(): void
    {
        $config = new PNConfiguration();

        $this->assertNull($config->getSecretKey());
    }

    public function testGetSecretKeyReturnsSetValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSecretKey('sec-key-456');

        $this->assertEquals('sec-key-456', $config->getSecretKey());
    }

    public function testGetSubscribeKeyReturnsSetValue(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSubscribeKey('sub-key-789');

        $this->assertEquals('sub-key-789', $config->getSubscribeKey());
    }

    // ============================================================================
    // CLONE METHOD TESTS
    // ============================================================================

    public function testCloneCreatesNewInstance(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSubscribeKey('demo');

        $cloned = $config->clone();

        $this->assertNotSame($config, $cloned);
    }

    public function testClonePreservesValues(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSubscribeKey('sub-key');
        $config->setPublishKey('pub-key');
        $config->setSecretKey('secret-key');
        $config->setOrigin('custom-origin.com');
        $config->setAuthKey('auth-key');
        $config->setSecure(false);

        $cloned = $config->clone();

        $this->assertEquals('test-user', $cloned->getUserId());
        $this->assertEquals('sub-key', $cloned->getSubscribeKey());
        $this->assertEquals('pub-key', $cloned->getPublishKey());
        $this->assertEquals('secret-key', $cloned->getSecretKey());
        $this->assertEquals('custom-origin.com', $cloned->getOrigin());
        $this->assertEquals('auth-key', $cloned->getAuthKey());
        $this->assertFalse($cloned->isSecure());
    }

    public function testClonePreservesTimeoutSettings(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setNonSubscribeRequestTimeout(25);
        $config->setSubscribeTimeout(400);
        $config->setConnectTimeout(15);

        $cloned = $config->clone();

        $this->assertEquals(25, $cloned->getNonSubscribeRequestTimeout());
        $this->assertEquals(400, $cloned->getSubscribeTimeout());
        $this->assertEquals(15, $cloned->getConnectTimeout());
    }

    public function testCloneCreatesUnlockedConfiguration(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSubscribeKey('demo');
        $config->lock();

        $cloned = $config->clone();

        // Cloned config should not be locked, so this should not throw
        $cloned->setPublishKey('new-pub-key');
        $this->assertEquals('new-pub-key', $cloned->getPublishKey());
    }

    public function testCloneIsIndependent(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('original-user');
        $config->setSubscribeKey('original-key');

        $cloned = $config->clone();
        $cloned->setUserId('cloned-user');
        $cloned->setSubscribeKey('cloned-key');

        // Original should be unchanged
        $this->assertEquals('original-user', $config->getUserId());
        $this->assertEquals('original-key', $config->getSubscribeKey());

        // Cloned should have new values
        $this->assertEquals('cloned-user', $cloned->getUserId());
        $this->assertEquals('cloned-key', $cloned->getSubscribeKey());
    }

    // ============================================================================
    // COMPREHENSIVE INTEGRATION TESTS
    // ============================================================================

    public function testFullConfigurationSetup(): void
    {
        $config = new PNConfiguration();
        $config->setUserId('test-user');
        $config->setSubscribeKey('sub-key');
        $config->setPublishKey('pub-key');
        $config->setSecretKey('secret-key');
        $config->setOrigin('custom.pubnub.com');
        $config->setAuthKey('my-auth-key');
        $config->setSecure(true);
        $config->setNonSubscribeRequestTimeout(30);
        $config->setSubscribeTimeout(320);
        $config->setConnectTimeout(12);
        $config->setFilterExpression('uuid != "bot"');
        $config->setUseRandomIV(true);
        $config->setCipherKey('cipher-key');

        // Verify all values
        $this->assertEquals('test-user', $config->getUserId());
        $this->assertEquals('sub-key', $config->getSubscribeKey());
        $this->assertEquals('pub-key', $config->getPublishKey());
        $this->assertEquals('secret-key', $config->getSecretKey());
        $this->assertEquals('custom.pubnub.com', $config->getOrigin());
        $this->assertEquals('my-auth-key', $config->getAuthKey());
        $this->assertTrue($config->isSecure());
        $this->assertEquals(30, $config->getNonSubscribeRequestTimeout());
        $this->assertEquals(320, $config->getSubscribeTimeout());
        $this->assertEquals(12, $config->getConnectTimeout());
        $this->assertEquals('uuid != "bot"', $config->getFilterExpression());
        $this->assertTrue($config->isAesEnabled());
        $this->assertTrue($config->getUseRandomIV());
    }
}
