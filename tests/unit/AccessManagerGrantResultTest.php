<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerAbstractResult;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerCategoryData;

/**
 * Reading Grant (PAMv2) response payloads, as Grant::createResponse() hands them to fromJson().
 */
class AccessManagerGrantResultTest extends TestCase
{
    /**
     * The "level-category" example from the PAMv2 OpenAPI spec.
     */
    public function testCategoryLevelResponsePopulatesCategories(): void
    {
        $getOnly = ['r' => 0, 'w' => 0, 'm' => 0, 'd' => 0, 'g' => 1, 'u' => 0, 'j' => 0];
        $result = PNAccessManagerAbstractResult::fromJson([
            'level' => 'category',
            'subscribe_key' => 'sub-key',
            'ttl' => 1440,
            'categories' => [
                'channels' => ['auths' => ['auth_key' => $getOnly]],
                'uuids' => ['auths' => ['auth_key' => $getOnly]],
            ],
        ]);

        $this->assertSame('category', $result->getLevel());
        $this->assertSame(1440, $result->getTtl());
        $this->assertSame(['channels', 'uuids'], array_keys($result->getCategories()));

        foreach ($result->getCategories() as $name => $category) {
            $this->assertInstanceOf(PNAccessManagerCategoryData::class, $category);
            $this->assertSame($name, $category->getName());

            $keyData = $category->getAuthKeys()['auth_key'];
            $this->assertTrue($keyData->isGetEnabled(), "$name: get should be granted");
            $this->assertFalse($keyData->isReadEnabled(), "$name: read should not be granted");
            $this->assertFalse($keyData->isWriteEnabled(), "$name: write should not be granted");
            $this->assertFalse($keyData->isManageEnabled(), "$name: manage should not be granted");
            $this->assertFalse($keyData->isDeleteEnabled(), "$name: delete should not be granted");
            $this->assertFalse($keyData->isUpdateEnabled(), "$name: update should not be granted");
            $this->assertFalse($keyData->isJoinEnabled(), "$name: join should not be granted");
        }
    }

    public function testResponseWithoutCategoriesHasNone(): void
    {
        $result = PNAccessManagerAbstractResult::fromJson([
            'level' => 'user',
            'subscribe_key' => 'sub-key',
            'ttl' => 5,
            'channel' => 'ch-a',
            'auths' => ['auth_key' => ['r' => 1, 'w' => 1, 'm' => 0]],
        ]);

        $this->assertSame([], $result->getCategories());
        $this->assertTrue($result->getChannels()['ch-a']->getAuthKeys()['auth_key']->isReadEnabled());
    }

    public function testMalformedCategoriesAreSkipped(): void
    {
        $result = PNAccessManagerAbstractResult::fromJson([
            'level' => 'category',
            'subscribe_key' => 'sub-key',
            'categories' => ['channels'],
        ]);

        $this->assertSame([], $result->getCategories());
    }

    public function testEntityLevelFlagsAreReadIndividually(): void
    {
        $result = PNAccessManagerAbstractResult::fromJson([
            'level' => 'channel',
            'subscribe_key' => 'sub-key',
            'ttl' => 1,
            'channels' => [
                'ch-a' => ['r' => 0, 'w' => 0, 'm' => 0, 'd' => 0, 'g' => 1, 'u' => 0, 'j' => 1],
            ],
        ]);

        $channel = $result->getChannels()['ch-a'];
        $this->assertTrue($channel->isGetEnabled());
        $this->assertTrue($channel->isJoinEnabled());
        $this->assertFalse($channel->isUpdateEnabled());
        $this->assertFalse($channel->isManageEnabled());
    }
}
