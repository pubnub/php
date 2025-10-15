<?php

namespace PubNubTests\unit;

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

    public function testGetTokenReturnsNullByDefault(): void
    {
        $token = $this->pubnub->getToken();

        $this->assertNull($token);
    }

    public function testSetTokenAndGetToken(): void
    {
        $testToken = 'test-token-abc123';

        $this->pubnub->setToken($testToken);

        $this->assertEquals($testToken, $this->pubnub->getToken());
    }

    public function testSetTokenOverwritesPreviousToken(): void
    {
        $this->pubnub->setToken('first-token');
        $this->assertEquals('first-token', $this->pubnub->getToken());

        $this->pubnub->setToken('second-token');
        $this->assertEquals('second-token', $this->pubnub->getToken());
    }

    public function testSetTokenWithEmptyString(): void
    {
        $this->pubnub->setToken('');

        $this->assertEquals('', $this->pubnub->getToken());
    }

    //phpcs:disable
    public function testSetTokenWithLongToken(): void
    {
        $longToken = 'qEF2AkF0GmFtet9DdHRsGDxDcmVzpURjaGFuoWpteS1jaGFubmVsGENDZ3JwoEN1c3KgQ3NwY6BEdXVpZKBDcGF0pURjaGFuoENnc' .
                     'nCgQ3VzcqBDc3BjoER1dWlkoERtZXRhoER1dWlkZ215LXV1aWRDc2lnWCAvUKKYbfc0vvvEhYqepG7-_lN5jh_yaA6eo98nAHV8Ug==';

        $this->pubnub->setToken($longToken);

        $this->assertEquals($longToken, $this->pubnub->getToken());
    }
    //phpcs:enable

    public function testSetTokenPersistsAcrossMultipleCalls(): void
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

    public function testTimestampReturnsCurrentTime(): void
    {
        $before = time();
        $timestamp = $this->pubnub->timestamp();
        $after = time();

        $this->assertIsInt($timestamp);
        // Timestamp should be between before and after
        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual($after, $timestamp);
    }

    public function testTimestampReturnsUnixTimestamp(): void
    {
        $timestamp = $this->pubnub->timestamp();

        // Should be a reasonable Unix timestamp (after year 2020)
        $this->assertGreaterThan(1577836800, $timestamp); // Jan 1, 2020

        // Should be before year 2100
        $this->assertLessThan(4102444800, $timestamp); // Jan 1, 2100
    }

    public function testTimestampChangesOverTime(): void
    {
        $timestamp1 = $this->pubnub->timestamp();
        usleep(1100000); // Sleep for slightly over 1 second
        $timestamp2 = $this->pubnub->timestamp();

        $this->assertGreaterThan($timestamp1, $timestamp2);
    }

    // ============================================================================
    // SEQUENCE ID TESTS
    // ============================================================================

    public function testGetSequenceIdReturnsInteger(): void
    {
        $sequenceId = $this->pubnub->getSequenceId();

        $this->assertIsInt($sequenceId);
    }

    public function testGetSequenceIdStartsAtOne(): void
    {
        $config = new PNConfiguration();
        $config->setSubscribeKey('demo');
        $config->setPublishKey('demo');
        $config->setUserId('test-user');
        $pubnub = new PubNub($config);

        $sequenceId = $pubnub->getSequenceId();

        $this->assertEquals(1, $sequenceId);
    }

    public function testGetSequenceIdIncrementsOnEachCall(): void
    {
        $id1 = $this->pubnub->getSequenceId();
        $id2 = $this->pubnub->getSequenceId();
        $id3 = $this->pubnub->getSequenceId();

        $this->assertEquals($id1 + 1, $id2);
        $this->assertEquals($id2 + 1, $id3);
    }

    public function testGetSequenceIdWrapsAtMaxSequence(): void
    {
        // Get sequence to near max
        for ($i = 0; $i < PubNub::$MAX_SEQUENCE; $i++) {
            $this->pubnub->getSequenceId();
        }

        // Next call should wrap to 1
        $sequenceId = $this->pubnub->getSequenceId();

        $this->assertEquals(1, $sequenceId);
    }

    public function testGetSequenceIdIsUnique(): void
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = $this->pubnub->getSequenceId();
        }

        // All IDs should be unique
        $this->assertEquals(100, count(array_unique($ids)));
    }

    // ============================================================================
    // CONFIGURATION GETTER TESTS
    // ============================================================================

    public function testGetConfigurationReturnsConfiguration(): void
    {
        $config = $this->pubnub->getConfiguration();

        $this->assertInstanceOf(PNConfiguration::class, $config);
    }

    public function testGetConfigurationReturnsSameConfiguration(): void
    {
        $config1 = $this->pubnub->getConfiguration();
        $config2 = $this->pubnub->getConfiguration();

        $this->assertSame($config1, $config2);
    }

    public function testGetConfigurationReturnsCorrectValues(): void
    {
        $config = $this->pubnub->getConfiguration();

        $this->assertEquals('demo', $config->getSubscribeKey());
        $this->assertEquals('demo', $config->getPublishKey());
        $this->assertEquals('test-user', $config->getUserId());
    }

    // ============================================================================
    // BASE PATH TESTS
    // ============================================================================

    public function testGetBasePathReturnsString(): void
    {
        $basePath = $this->pubnub->getBasePath();

        $this->assertIsString($basePath);
    }

    public function testGetBasePathReturnsValidUrl(): void
    {
        $basePath = $this->pubnub->getBasePath();

        $this->assertStringStartsWith('http', $basePath);
    }

    public function testGetBasePathWithCustomHost(): void
    {
        $basePath = $this->pubnub->getBasePath('custom.pubnub.com');

        $this->assertEquals('https://custom.pubnub.com', $basePath);
    }

    public function testGetBasePathUsesConfigurationOrigin(): void
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

    public function testSetClientAndGetClient(): void
    {
        $mockClient = $this->createMock(\Psr\Http\Client\ClientInterface::class);

        $this->pubnub->setClient($mockClient);

        $this->assertSame($mockClient, $this->pubnub->getClient());
    }

    public function testGetClientReturnsSameInstanceByDefault(): void
    {
        $client1 = $this->pubnub->getClient();
        $client2 = $this->pubnub->getClient();

        $this->assertSame($client1, $client2);
    }

    // ============================================================================
    // REQUEST FACTORY TESTS
    // ============================================================================

    public function testSetRequestFactoryAndGetRequestFactory(): void
    {
        $mockFactory = $this->createMock(\Psr\Http\Message\RequestFactoryInterface::class);

        $this->pubnub->setRequestFactory($mockFactory);

        $this->assertSame($mockFactory, $this->pubnub->getRequestFactory());
    }

    public function testGetRequestFactoryReturnsSameInstanceByDefault(): void
    {
        $factory1 = $this->pubnub->getRequestFactory();
        $factory2 = $this->pubnub->getRequestFactory();

        $this->assertSame($factory1, $factory2);
    }

    // ============================================================================
    // LOGGER TESTS
    // ============================================================================

    public function testGetLoggerReturnsLoggerInterface(): void
    {
        $logger = $this->pubnub->getLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testSetLoggerAndGetLogger(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);

        $this->pubnub->setLogger($mockLogger);

        $this->assertSame($mockLogger, $this->pubnub->getLogger());
    }

    public function testGetLoggerReturnsSameInstanceByDefault(): void
    {
        $logger1 = $this->pubnub->getLogger();
        $logger2 = $this->pubnub->getLogger();

        $this->assertSame($logger1, $logger2);
    }

    public function testSetLoggerReplacesDefaultLogger(): void
    {
        $defaultLogger = $this->pubnub->getLogger();
        $this->assertInstanceOf(\Psr\Log\NullLogger::class, $defaultLogger);

        $customLogger = $this->createMock(LoggerInterface::class);
        $this->pubnub->setLogger($customLogger);

        $this->assertNotSame($defaultLogger, $this->pubnub->getLogger());
        $this->assertSame($customLogger, $this->pubnub->getLogger());
    }
}
