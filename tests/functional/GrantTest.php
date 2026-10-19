<?php

namespace Tests\Functional;

use PubNub\Endpoints\Access\Grant;
use PubNub\Enums\PNGrantCategory;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;

class GrantTest extends \PubNubTestCase
{
    /** @var  GrantExposed */
    protected $grant;

    public function setUp(): void
    {
        parent::setUp();

        $this->pubnub_pam = new PubNubStubbed($this->config_pam);
        $this->grant = new GrantExposed($this->pubnub_pam);
    }

    public function testValidatesFlags()
    {
        $grant = new GrantExposed($this->pubnub);

        try {
            $grant->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException $exception) {
            $this->assertEquals("Secret key not configured", $exception->getMessage());
        }
    }

    public function testReadAndWriteToChannel()
    {
        $this->grant->channels('ch')->read(true)->write(true)->ttl(7);

        $this->assertEquals(
            sprintf(GrantExposed::PATH, $this->config_pam->getSubscribeKey()),
            $this->grant->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'r' => '1',
            'w' => '1',
            'ttl' => '7',
            'timestamp' => '123',
            'channel' => 'ch',
            'signature' => $this->fakeSignature(
                [
                    'pnsdk' => PubNub::getSdkFullName(),
                    'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
                    'r' => '1',
                    'w' => '1',
                    'ttl' => '7',
                    'timestamp' => '123',
                    'channel' => 'ch',
                ],
                PNHttpMethod::GET,
                '123',
                $this->pubnub_pam->getConfiguration()->getPublishKey(),
                $this->grant->buildPath(),
                $this->config_pam->getSecretKey()
            )
        ], $this->grant->buildParams());
    }

    public function testReadAndWriteToChannelGroup()
    {
        $this->grant->channelGroups(['gr1', 'gr2'])
            ->read(true)
            ->write(false);

        $this->assertEquals(
            sprintf(GrantExposed::PATH, $this->config_pam->getSubscribeKey()),
            $this->grant->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'r' => '1',
            'w' => '0',
            'timestamp' => '123',
            'channel-group' => PubNubUtil::urlEncode('gr1,gr2'),
            'signature' => $this->fakeSignature(
                [
                    'pnsdk' => PubNub::getSdkFullName(),
                    'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
                    'r' => '1',
                    'w' => '0',
                    'timestamp' => '123',
                    'channel-group' => 'gr1,gr2',
                ],
                PNHttpMethod::GET,
                '123',
                $this->pubnub_pam->getConfiguration()->getPublishKey(),
                $this->grant->buildPath(),
                $this->config_pam->getSecretKey()
            )
        ], $this->grant->buildParams());
    }

    public function testCategoriesAreSentAsTheCategoryParameter(): void
    {
        $this->grant->authKeys('auth-key')
            ->categories([PNGrantCategory::CHANNELS, PNGrantCategory::UUIDS])
            ->get(true)
            ->ttl(10);

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'g' => '1',
            'auth' => 'auth-key',
            'category' => PubNubUtil::urlEncode('channels,uuids'),
            'ttl' => '10',
            'timestamp' => '123',
            'signature' => $this->fakeSignature(
                [
                    'pnsdk' => PubNub::getSdkFullName(),
                    'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
                    'g' => '1',
                    'auth' => 'auth-key',
                    'category' => 'channels,uuids',
                    'ttl' => '10',
                    'timestamp' => '123',
                ],
                PNHttpMethod::GET,
                '123',
                $this->pubnub_pam->getConfiguration()->getPublishKey(),
                $this->grant->buildPath(),
                $this->config_pam->getSecretKey()
            )
        ], $this->grant->buildParams());
    }

    public function testCategoryRevokeSendsGetAsZero(): void
    {
        $params = $this->grant->authKeys('auth-key')
            ->categories(PNGrantCategory::CHANNELS)
            ->get(false)
            ->buildParams();

        $this->assertSame('channels', $params['category']);
        $this->assertSame('0', $params['g']);
    }

    public function testCategoriesAcceptACommaSeparatedString(): void
    {
        $params = $this->grant->authKeys('auth-key')->categories('channels,uuids')->get(true)->buildParams();

        $this->assertSame(PubNubUtil::urlEncode('channels,uuids'), $params['category']);
    }

    public function testDuplicateCategoriesAreSentOnce(): void
    {
        $params = $this->grant->authKeys('auth-key')
            ->categories([PNGrantCategory::UUIDS, PNGrantCategory::CHANNELS, PNGrantCategory::UUIDS])
            ->get(true)
            ->buildParams();

        $this->assertSame(PubNubUtil::urlEncode('uuids,channels'), $params['category']);
    }

    public function testCategoriesCombineWithChannels(): void
    {
        $params = $this->grant->authKeys('auth-key')
            ->channels('ch')
            ->categories(PNGrantCategory::UUIDS)
            ->get(true)
            ->buildParams();

        $this->assertSame('ch', $params['channel']);
        $this->assertSame('uuids', $params['category']);
        $this->assertSame('1', $params['g']);
    }

    /**
     * @dataProvider invalidCategoryGrantProvider
     * @param callable(GrantExposed): GrantExposed $configure
     */
    public function testInvalidCategoryGrantsAreRejected(callable $configure, string $expectedMessage): void
    {
        $this->expectException(PubNubValidationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $configure($this->grant)->validateParams();
    }

    /**
     * @return array<string, array{0: callable(GrantExposed): GrantExposed, 1: string}>
     */
    public function invalidCategoryGrantProvider(): array
    {
        $onlyGet = 'Only the get permission is allowed for categories';

        return [
            'missing auth key' => [
                fn(GrantExposed $grant) => $grant->categories(PNGrantCategory::CHANNELS)->get(true),
                'Category grants require at least one auth key',
            ],
            'empty auth key' => [
                fn(GrantExposed $grant) => $grant->authKeys([' '])->categories(PNGrantCategory::CHANNELS)->get(true),
                'Category grants require at least one auth key',
            ],
            'read' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->read(true),
                $onlyGet,
            ],
            'write' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->write(true),
                $onlyGet,
            ],
            'manage' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->manage(true),
                $onlyGet,
            ],
            'delete' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->delete(true),
                $onlyGet,
            ],
            'update' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->update(true),
                $onlyGet,
            ],
            'join' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->get(true)->join(true),
                $onlyGet,
            ],
            'unsupported category' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('users')->get(true),
                'Unsupported category "users"',
            ],
            'missing get' => [
                fn(GrantExposed $grant) => $grant->authKeys('a')->categories('channels')->read(false),
                'Category grants need get(true) to grant or get(false) to revoke',
            ],
        ];
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class PubNubStubbed extends PubNub
{
    public function timestamp(): int
    {
        return 123;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class GrantExposed extends Grant
{
    public const PATH = parent::PATH;
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }
}
