<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Access\Permissions;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;

/**
 * Reading category-level permissions back out of a parsed grant token. The fixtures mirror the
 * CBOR-decoded shape that GrantToken::parseToken() hands to PNAccessManagerTokenResult.
 */
class TokenCategoriesParseTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     */
    private function token(array $overrides = []): PNAccessManagerTokenResult
    {
        return PNAccessManagerTokenResult::fromArray(array_merge([
            'v' => 2,
            't' => 1755000000,
            'ttl' => 60,
            'res' => ['chan' => ['ch-a' => 1], 'grp' => [], 'usr' => [], 'spc' => [], 'uuid' => []],
            'pat' => ['chan' => [], 'grp' => [], 'usr' => [], 'spc' => [], 'uuid' => []],
            'meta' => [],
            'sig' => 'signature-bytes',
        ], $overrides));
    }

    /**
     * @param Permissions|false $granted
     */
    private function granted($granted): Permissions
    {
        $this->assertInstanceOf(Permissions::class, $granted);

        /** @var Permissions $granted */
        return $granted;
    }

    public function testBothCategoriesAreDecoded(): void
    {
        $token = $this->token(['cat' => ['chan' => 32, 'uuid' => 32]]);

        $channels = $this->granted($token->getChannelsCategory());
        $this->assertSame('channels', $channels->getName());
        $this->assertTrue($channels->hasGet());
        $this->assertFalse($channels->hasRead());

        $uuids = $this->granted($token->getUuidsCategory());
        $this->assertSame('uuids', $uuids->getName());
        $this->assertTrue($uuids->hasGet());
    }

    public function testChannelsCategoryAloneDoesNotGrantUuids(): void
    {
        $token = $this->token(['cat' => ['chan' => 32]]);

        $this->assertTrue($this->granted($token->getChannelsCategory())->hasGet());
        $this->assertFalse($token->getUuidsCategory());
        $this->assertFalse($this->granted($token->getChannelResource('ch-a'))->hasGet());
    }

    public function testTokenWithoutCategories(): void
    {
        $token = $this->token();

        $this->assertSame([], $token->getCategories());
        $this->assertFalse($token->getChannelsCategory());
        $this->assertFalse($token->getUuidsCategory());
    }

    public function testCategoriesAppearInToArrayOnlyWhenPresent(): void
    {
        $this->assertArrayNotHasKey('categories', $this->token()->toArray());

        $categories = $this->token(['cat' => ['chan' => 32, 'uuid' => 32]])->toArray()['categories'];

        $this->assertTrue($categories['chan']['get']);
        $this->assertFalse($categories['chan']['read']);
        $this->assertTrue($categories['uuid']['get']);
    }
}
