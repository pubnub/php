<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\CryptoModule;

class PubNubCryptoMethodsTest extends TestCase
{
    // ============================================================================
    // isCryptoEnabled() TESTS
    // ============================================================================

    public function testIsCryptoEnabledReturnsFalseByDefault()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        
        $this->assertFalse($pubnub->isCryptoEnabled());
    }

    public function testIsCryptoEnabledReturnsTrueWhenCipherKeySet()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('test-cipher-key');
        
        $pubnub = new PubNub($config);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
    }

    public function testIsCryptoEnabledReturnsTrueWhenCryptoModuleSet()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('test-key', false);
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
    }

    public function testIsCryptoEnabledAfterSettingCryptoOnPubNub()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $this->assertFalse($pubnub->isCryptoEnabled());
        
        $cryptoModule = CryptoModule::aesCbcCryptor('my-key', true);
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
    }

    // ============================================================================
    // getCrypto() TESTS
    // ============================================================================

    public function testGetCryptoReturnsNullWhenNotConfigured()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        
        $this->assertNull($pubnub->getCrypto());
    }

    public function testGetCryptoReturnsModuleWhenCipherKeySet()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('test-cipher-key');
        
        $pubnub = new PubNub($config);
        $crypto = $pubnub->getCrypto();
        
        $this->assertInstanceOf(CryptoModule::class, $crypto);
    }

    public function testGetCryptoReturnsModuleWhenCryptoModuleSet()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('test-key', false);
        $pubnub->setCrypto($cryptoModule);
        
        $crypto = $pubnub->getCrypto();
        
        $this->assertInstanceOf(CryptoModule::class, $crypto);
        $this->assertSame($cryptoModule, $crypto);
    }

    public function testGetCryptoReturnsConfigurationCryptoWhenNoPubNubCrypto()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('config-key');
        
        $pubnub = new PubNub($config);
        $crypto = $pubnub->getCrypto();
        
        $this->assertInstanceOf(CryptoModule::class, $crypto);
    }

    public function testGetCryptoPrefersInstanceCryptoOverConfiguration()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('config-key');
        
        $pubnub = new PubNub($config);
        
        $instanceCrypto = CryptoModule::aesCbcCryptor('instance-key', true);
        $pubnub->setCrypto($instanceCrypto);
        
        $crypto = $pubnub->getCrypto();
        
        $this->assertSame($instanceCrypto, $crypto);
    }

    // ============================================================================
    // setCrypto() TESTS
    // ============================================================================

    public function testSetCryptoStoresModule()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('my-cipher-key', false);
        
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertSame($cryptoModule, $pubnub->getCrypto());
    }

    public function testSetCryptoOverwritesPreviousCrypto()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        
        $crypto1 = CryptoModule::legacyCryptor('key1', false);
        $pubnub->setCrypto($crypto1);
        $this->assertSame($crypto1, $pubnub->getCrypto());
        
        $crypto2 = CryptoModule::aesCbcCryptor('key2', true);
        $pubnub->setCrypto($crypto2);
        $this->assertSame($crypto2, $pubnub->getCrypto());
    }

    public function testSetCryptoEnablesCrypto()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $this->assertFalse($pubnub->isCryptoEnabled());
        
        $cryptoModule = CryptoModule::legacyCryptor('my-key', false);
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
    }

    public function testSetCryptoWithLegacyCryptor()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('legacy-key', false);
        
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertInstanceOf(CryptoModule::class, $pubnub->getCrypto());
    }

    public function testSetCryptoWithAesCbcCryptor()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::aesCbcCryptor('aes-key', true);
        
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertInstanceOf(CryptoModule::class, $pubnub->getCrypto());
    }

    public function testSetCryptoWithRandomIV()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('test-key', true);
        
        $pubnub->setCrypto($cryptoModule);
        
        $crypto = $pubnub->getCrypto();
        $this->assertNotNull($crypto);
    }

    public function testSetCryptoWithStaticIV()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::legacyCryptor('test-key', false);
        
        $pubnub->setCrypto($cryptoModule);
        
        $crypto = $pubnub->getCrypto();
        $this->assertNotNull($crypto);
    }

    // ============================================================================
    // INTEGRATION TESTS (getCrypto + setCrypto + isCryptoEnabled)
    // ============================================================================

    public function testCryptoWorkflowConfigurationOnly()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('config-cipher-key');
        
        $pubnub = new PubNub($config);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
        $this->assertInstanceOf(CryptoModule::class, $pubnub->getCrypto());
    }

    public function testCryptoWorkflowInstanceOnly()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $this->assertFalse($pubnub->isCryptoEnabled());
        
        $cryptoModule = CryptoModule::aesCbcCryptor('instance-key', true);
        $pubnub->setCrypto($cryptoModule);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
        $this->assertSame($cryptoModule, $pubnub->getCrypto());
    }

    public function testCryptoWorkflowBothConfigurationAndInstance()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('config-key');
        
        $pubnub = new PubNub($config);
        $this->assertTrue($pubnub->isCryptoEnabled());
        
        $instanceCrypto = CryptoModule::legacyCryptor('instance-key', false);
        $pubnub->setCrypto($instanceCrypto);
        
        $this->assertTrue($pubnub->isCryptoEnabled());
        $this->assertSame($instanceCrypto, $pubnub->getCrypto());
    }

    public function testCryptoCanBeUsedForEncryptionDecryption()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        
        $pubnub = new PubNub($config);
        $cryptoModule = CryptoModule::aesCbcCryptor('encryption-key', false);
        $pubnub->setCrypto($cryptoModule);
        
        $crypto = $pubnub->getCrypto();
        
        $plaintext = 'Hello, World!';
        $encrypted = $crypto->encrypt($plaintext);
        $decrypted = $crypto->decrypt($encrypted);
        
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testMultiplePubNubInstancesWithDifferentCrypto()
    {
        $config1 = new PNConfiguration();
        $config1->setSubscribeKey('demo');
        $config1->setUserId('user1');
        $pubnub1 = new PubNub($config1);
        $crypto1 = CryptoModule::legacyCryptor('key1', false);
        $pubnub1->setCrypto($crypto1);
        
        $config2 = new PNConfiguration();
        $config2->setSubscribeKey('demo');
        $config2->setUserId('user2');
        $pubnub2 = new PubNub($config2);
        $crypto2 = CryptoModule::aesCbcCryptor('key2', true);
        $pubnub2->setCrypto($crypto2);
        
        $this->assertNotSame($pubnub1->getCrypto(), $pubnub2->getCrypto());
        $this->assertSame($crypto1, $pubnub1->getCrypto());
        $this->assertSame($crypto2, $pubnub2->getCrypto());
    }

    public function testCryptoModuleCanBeRetrievedAndUsedDirectly()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test-user');
        $config->setCipherKey('direct-use-key');
        
        $pubnub = new PubNub($config);
        $crypto = $pubnub->getCrypto();
        
        // Use crypto module directly
        $message = 'Test message';
        $encrypted = $crypto->encrypt($message);
        
        $this->assertNotEquals($message, $encrypted);
        $this->assertIsString($encrypted);
    }
}
