<?php

use PHPUnit\Framework\TestCase;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\CryptoModule;
use Psr\Log\LoggerInterface;

class PubNubUtilityMethodsTest extends TestCase
{
    private PubNub $pubnub;
    private PNConfiguration $config;

    public function setUp(): void
    {
        $this->config = new PNConfiguration();
        $this->config->setSubscribeKey('demo');
        $this->config->setPublishKey('demo');
        $this->config->setUserId('test-user');
        
        $this->pubnub = new PubNub($this->config);
    }

    // ============================================================================
    // TOKEN METHODS TESTS
    // ============================================================================

    public function testGetTokenReturnsNullByDefault()
    {
        $token = $this->pubnub->getToken();
        
        $this->assertNull($token);
    }

    public function testSetTokenAndGetToken()
    {
        $testToken = 'test-token-abc123';
        
        $this->pubnub->setToken($testToken);
        
        $this->assertEquals($testToken, $this->pubnub->getToken());
    }

    public function testSetTokenOverwritesPreviousToken()
    {
        $this->pubnub->setToken('first-token');
        $this->assertEquals('first-token', $this->pubnub->getToken());
        
        $this->pubnub->setToken('second-token');
        $this->assertEquals('second-token', $this->pubnub->getToken());
    }

    public function testSetTokenWithEmptyString()
    {
        $this->pubnub->setToken('');
        
        $this->assertEquals('', $this->pubnub->getToken());
    }

    public function testSetTokenWithLongToken()
    {
        $longToken = 'qEF2AkF0GmFtet9DdHRsGDxDcmVzpURjaGFuoWpteS1jaGFubmVsGENDZ3JwoEN1c3KgQ3NwY6BEdXVpZKBDcGF0pURjaGFuoENnc' .
                     'nCgQ3VzcqBDc3BjoER1dWlkoERtZXRhoER1dWlkZ215LXV1aWRDc2lnWCAvUKKYbfc0vvvEhYqepG7-_lN5jh_yaA6eo98nAHV8Ug==';
        
        $this->pubnub->setToken($longToken);
        
        $this->assertEquals($longToken, $this->pubnub->getToken());
    }

    public function testSetTokenPersistsAcrossMultipleCalls()
    {
        $token = 'persistent-token';
        $this->pubnub->setToken($token);
        
        // Call getToken multiple times
        $this->assertEquals($token, $this->pubnub->getToken());
        $this->assertEquals($token, $this->pubnub->getToken());
        $this->assertEquals($token, $this->pubnub->getToken());
    }

    // ============================================================================
    // TIMESTAMP METHOD TESTS
    // ============================================================================

    public function testTimestampReturnsInteger()
    {
        $timestamp = $this->pubnub->timestamp();
        
        $this->assertIsInt($timestamp);
    }

    public function testTimestampReturnsCurrentTime()
    {
        $before = time();
        $timestamp = $this->pubnub->timestamp();
        $after = time();
        
        // Timestamp should be between before and after
        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual($after, $timestamp);
    }

    public function testTimestampReturnsUnixTimestamp()
    {
        $timestamp = $this->pubnub->timestamp();
        
        // Should be a reasonable Unix timestamp (after year 2020)
        $this->assertGreaterThan(1577836800, $timestamp); // Jan 1, 2020
        
        // Should be before year 2100
        $this->assertLessThan(4102444800, $timestamp); // Jan 1, 2100
    }

    public function testTimestampChangesOverTime()
    {
        $timestamp1 = $this->pubnub->timestamp();
        usleep(1100000); // Sleep for slightly over 1 second
        $timestamp2 = $this->pubnub->timestamp();
        
        $this->assertGreaterThan($timestamp1, $timestamp2);
    }

    // ============================================================================
    // SEQUENCE ID TESTS
    // ============================================================================

    public function testGetSequenceIdReturnsInteger()
    {
        $sequenceId = $this->pubnub->getSequenceId();
        
        $this->assertIsInt($sequenceId);
    }

    public function testGetSequenceIdStartsAtOne()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUserId('test-user');
        $pubnub = new PubNub($config);
        
        $sequenceId = $pubnub->getSequenceId();
        
        $this->assertEquals(1, $sequenceId);
    }

    public function testGetSequenceIdIncrementsOnEachCall()
    {
        $id1 = $this->pubnub->getSequenceId();
        $id2 = $this->pubnub->getSequenceId();
        $id3 = $this->pubnub->getSequenceId();
        
        $this->assertEquals($id1 + 1, $id2);
        $this->assertEquals($id2 + 1, $id3);
    }

    public function testGetSequenceIdWrapsAtMaxSequence()
    {
        // Get sequence to near max
        for ($i = 0; $i < PubNub::$MAX_SEQUENCE; $i++) {
            $this->pubnub->getSequenceId();
        }
        
        // Next call should wrap to 1
        $sequenceId = $this->pubnub->getSequenceId();
        
        $this->assertEquals(1, $sequenceId);
    }

    public function testGetSequenceIdIsUnique()
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = $this->pubnub->getSequenceId();
        }
        
        // All IDs should be unique
        $this->assertEquals(100, count(array_unique($ids)));
    }

    // ============================================================================
    // TELEMETRY MANAGER TESTS
    // ============================================================================

    public function testGetTelemetryManagerReturnsInstance()
    {
        $telemetryManager = $this->pubnub->getTelemetryManager();
        
        $this->assertInstanceOf(\PubNub\Managers\TelemetryManager::class, $telemetryManager);
    }

    public function testGetTelemetryManagerReturnsSameInstance()
    {
        $telemetryManager1 = $this->pubnub->getTelemetryManager();
        $telemetryManager2 = $this->pubnub->getTelemetryManager();
        
        $this->assertSame($telemetryManager1, $telemetryManager2);
    }

    // ============================================================================
    // CONFIGURATION GETTER TESTS
    // ============================================================================

    public function testGetConfigurationReturnsConfiguration()
    {
        $config = $this->pubnub->getConfiguration();
        
        $this->assertInstanceOf(PNConfiguration::class, $config);
    }

    public function testGetConfigurationReturnsSameConfiguration()
    {
        $config1 = $this->pubnub->getConfiguration();
        $config2 = $this->pubnub->getConfiguration();
        
        $this->assertSame($config1, $config2);
    }

    public function testGetConfigurationReturnsCorrectValues()
    {
        $config = $this->pubnub->getConfiguration();
        
        $this->assertEquals('demo', $config->getSubscribeKey());
        $this->assertEquals('demo', $config->getPublishKey());
        $this->assertEquals('test-user', $config->getUserId());
    }

    // ============================================================================
    // BASE PATH TESTS
    // ============================================================================

    public function testGetBasePathReturnsString()
    {
        $basePath = $this->pubnub->getBasePath();
        
        $this->assertIsString($basePath);
    }

    public function testGetBasePathReturnsValidUrl()
    {
        $basePath = $this->pubnub->getBasePath();
        
        $this->assertStringStartsWith('http', $basePath);
    }

    public function testGetBasePathWithCustomHost()
    {
        $basePath = $this->pubnub->getBasePath('custom.pubnub.com');
        
        $this->assertEquals('https://custom.pubnub.com', $basePath);
    }

    public function testGetBasePathUsesConfigurationOrigin()
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setUserId('test');
        $config->setOrigin('my-origin.pubnub.com');
        
        $pubnub = new PubNub($config);
        $basePath = $pubnub->getBasePath();
        
        $this->assertEquals('https://my-origin.pubnub.com', $basePath);
    }

    // ============================================================================
    // HTTP CLIENT TESTS
    // ============================================================================

    public function testGetClientReturnsClientInterface()
    {
        $client = $this->pubnub->getClient();
        
        $this->assertInstanceOf(\Psr\Http\Client\ClientInterface::class, $client);
    }

    public function testSetClientAndGetClient()
    {
        $mockClient = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        
        $this->pubnub->setClient($mockClient);
        
        $this->assertSame($mockClient, $this->pubnub->getClient());
    }

    public function testGetClientReturnsSameInstanceByDefault()
    {
        $client1 = $this->pubnub->getClient();
        $client2 = $this->pubnub->getClient();
        
        $this->assertSame($client1, $client2);
    }

    // ============================================================================
    // REQUEST FACTORY TESTS
    // ============================================================================

    public function testGetRequestFactoryReturnsRequestFactoryInterface()
    {
        $requestFactory = $this->pubnub->getRequestFactory();
        
        $this->assertInstanceOf(\Psr\Http\Message\RequestFactoryInterface::class, $requestFactory);
    }

    public function testSetRequestFactoryAndGetRequestFactory()
    {
        $mockFactory = $this->createMock(\Psr\Http\Message\RequestFactoryInterface::class);
        
        $this->pubnub->setRequestFactory($mockFactory);
        
        $this->assertSame($mockFactory, $this->pubnub->getRequestFactory());
    }

    public function testGetRequestFactoryReturnsSameInstanceByDefault()
    {
        $factory1 = $this->pubnub->getRequestFactory();
        $factory2 = $this->pubnub->getRequestFactory();
        
        $this->assertSame($factory1, $factory2);
    }

    // ============================================================================
    // LOGGER TESTS
    // ============================================================================

    public function testGetLoggerReturnsLoggerInterface()
    {
        $logger = $this->pubnub->getLogger();
        
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testSetLoggerAndGetLogger()
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        
        $this->pubnub->setLogger($mockLogger);
        
        $this->assertSame($mockLogger, $this->pubnub->getLogger());
    }

    public function testGetLoggerReturnsSameInstanceByDefault()
    {
        $logger1 = $this->pubnub->getLogger();
        $logger2 = $this->pubnub->getLogger();
        
        $this->assertSame($logger1, $logger2);
    }

    public function testSetLoggerReplacesDefaultLogger()
    {
        $defaultLogger = $this->pubnub->getLogger();
        $this->assertInstanceOf(\Psr\Log\NullLogger::class, $defaultLogger);
        
        $customLogger = $this->createMock(LoggerInterface::class);
        $this->pubnub->setLogger($customLogger);
        
        $this->assertNotSame($defaultLogger, $this->pubnub->getLogger());
        $this->assertSame($customLogger, $this->pubnub->getLogger());
    }
}
