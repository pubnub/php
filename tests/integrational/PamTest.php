<?php

namespace Tests\Integrational;

use PubNub\Models\Consumer\AccessManager\PNAccessManagerAbstractResult;

/**
 * Class PamTest
 * NOTICE: Endpoint requests aren't mocked
 *
 * @package Tests\Integrational
 */
class PamTest extends \PubNubTestCase
{
    /**
     * @group pam
     * @group pam-integrational
     */
    public function testGlobalLevel()
    {
        $response = $this->pubnub_pam->grant()->read(true)->write(true)->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertCount(0, $response->getChannels());
        $this->assertCount(0, $response->getChannelGroups());
        $this->assertTrue($response->isReadEnabled());
        $this->assertTrue($response->isWriteEnabled());
        $this->assertFalse($response->isManageEnabled());

        $response = $this->pubnub_pam->revoke()->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertCount(0, $response->getChannels());
        $this->assertCount(0, $response->getChannelGroups());
        $this->assertFalse($response->isReadEnabled());
        $this->assertFalse($response->isWriteEnabled());
        $this->assertFalse($response->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testSingleChannel()
    {
        $ch = "test-pam-php-ch";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam->grant()->channels($ch)->write(true)->read(true)->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testSingleChannelWithAuth()
    {
        $ch = "test-pam-php-ch";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()->channels($ch)->write(true)->read(true)->authKeys($auth)->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch]->getAuthKeys()[$auth]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testMultipleChannels()
    {
        $ch1 = "test-pam-php-ch1";
        $ch2 = "test-pam-php-ch2";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()->channels([$ch1, $ch2])->write(true)->read(true)->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch1]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch1]->isManageEnabled());
        $this->assertFalse($response->getChannels()[$ch2]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testMultipleChannelsWithAuth()
    {
        $ch1 = "test-pam-php-ch1";
        $ch2 = "test-pam-php-ch2";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->channels([$ch1, $ch2])
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertFalse($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testSingleChannelGroup()
    {
        $cg = "test-pam-cg";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->channelGroups($cg)
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("channel-group", $response->getLevel());
        $this->assertTrue($response->getChannelGroups()[$cg]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$cg]->isWriteEnabled());
        $this->assertFalse($response->getChannelGroups()[$cg]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testSingleChannelGroupWithAuth()
    {
        $cg = "test-pam-php-cg";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->channelGroups($cg)
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("channel-group+auth", $response->getLevel());
        $this->assertTrue($response->getChannelGroups()[$cg]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$cg]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannelGroups()[$cg]->getAuthKeys()[$auth]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testMultipleChannelGroups()
    {
        $gr1 = "test-pam-php-cg1";
        $gr2 = "test-pam-php-cg2";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->channelGroups([$gr1, $gr2])
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("channel-group", $response->getLevel());
        $this->assertTrue($response->getChannelGroups()[$gr1]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr2]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr1]->isWriteEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr2]->isWriteEnabled());
        $this->assertFalse($response->getChannelGroups()[$gr1]->isManageEnabled());
        $this->assertFalse($response->getChannelGroups()[$gr2]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testMultipleChannelGroupsWithAuth()
    {
        $gr1 = "test-pam-php-cg1";
        $gr2 = "test-pam-php-cg2";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->channelGroups([$gr1, $gr2])
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("channel-group+auth", $response->getLevel());
        $this->assertTrue($response->getChannelGroups()[$gr1]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr2]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getChannelGroups()[$gr2]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannelGroups()[$gr1]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertFalse($response->getChannelGroups()[$gr2]->getAuthKeys()[$auth]->isManageEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testSingleUuid()
    {
        $targetUuid = "test-pam-uuid";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->uuids([$targetUuid])
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->delete(false)
            ->get(true)
            ->update(true)
            ->join(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("uuid", $response->getLevel());
        $this->assertTrue($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertFalse($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isDeleteEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isGetEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isUpdateEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid]->getAuthKeys()[$auth]->isJoinEnabled());
    }

    /**
     * @group pam
     * @group pam-integrational
     */
    public function testMultipleUuid()
    {
        $targetUuid1 = "test-pam-uuid-1";
        $targetUuid2 = "test-pam-uuid-2";
        $auth = "test-pam-php-auth";

        $this->pubnub->getConfiguration()->setUuid('my_uuid');

        $response = $this->pubnub_pam
            ->grant()
            ->uuids([$targetUuid1, $targetUuid2])
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->manage(true)
            ->delete(true)
            ->get(true)
            ->update(true)
            ->join(false)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAbstractResult::class, $response);
        $this->assertEquals("uuid", $response->getLevel());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isDeleteEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isDeleteEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isGetEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isGetEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isUpdateEnabled());
        $this->assertTrue($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isUpdateEnabled());
        $this->assertFalse($response->getUsers()[$targetUuid1]->getAuthKeys()[$auth]->isJoinEnabled());
        $this->assertFalse($response->getUsers()[$targetUuid2]->getAuthKeys()[$auth]->isJoinEnabled());
    }
}
