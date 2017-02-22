<?php


use PubNub\Models\Consumer\AccessManager\PNAccessManagerGrantResult;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerAuditResult;

class PamTest extends PubNubTestCase
{
    /**
     * @group pam
     * @group pam-integrational
     */
    public function testGlobalLevel()
    {
        /** @var  PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam->grant()->read(true)->write(true)->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertCount(0, $response->getChannels());
        $this->assertCount(0, $response->getChannelGroups());
        $this->assertTrue($response->isReadEnabled());
        $this->assertTrue($response->isWriteEnabled());
        $this->assertFalse($response->isManageEnabled());

        /** @var  PNAccessManagerAuditResult $response */
        $response = $this->pubnub_pam->audit()->sync();

        $this->assertInstanceOf(PNAccessManagerAuditResult::class, $response);
        $this->assertGreaterThanOrEqual(0, $response->getChannels());
        $this->assertGreaterThanOrEqual(0, $response->getChannelGroups());
        $this->assertTrue($response->isReadEnabled());
        $this->assertTrue($response->isWriteEnabled());
        $this->assertFalse($response->isManageEnabled());

        $response = $this->pubnub_pam->revoke()->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
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

        /** @var PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam->grant()->channels($ch)->write(true)->read(true)->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch]->isManageEnabled());

        /** @var PNAccessManagerAuditResult $response */
        $response = $this->pubnub_pam->audit()->channels($ch)->sync();

        $this->assertInstanceOf(PNAccessManagerAuditResult::class, $response);
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

        /** @var PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam
            ->grant()->channels($ch)->write(true)->read(true)->authKeys($auth)->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch]->getAuthKeys()[$auth]->isManageEnabled());

        /** @var PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam->audit()->channels($ch)->authKeys($auth)->sync();

        $this->assertInstanceOf(PNAccessManagerAuditResult::class, $response);
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

        /** @var PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam
            ->grant()->channels([$ch1, $ch2])->write(true)->read(true)->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch1]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch1]->isManageEnabled());
        $this->assertFalse($response->getChannels()[$ch2]->isManageEnabled());

        /** @var PNAccessManagerAuditResult $response */
        $response = $this->pubnub_pam->audit()->channels([$ch1, $ch2])->sync();

        $this->assertInstanceOf(PNAccessManagerAuditResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch1]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->isWriteEnabled());
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

        /** @var PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam
            ->grant()
            ->channels([$ch1, $ch2])
            ->authKeys($auth)
            ->write(true)
            ->read(true)
            ->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertFalse($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isManageEnabled());

        /** @var PNAccessManagerAuditResult $response */
        $response = $this->pubnub_pam
            ->audit()
            ->channels([$ch1, $ch2])
            ->sync();

        $this->assertInstanceOf(PNAccessManagerAuditResult::class, $response);
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isReadEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertTrue($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isWriteEnabled());
        $this->assertFalse($response->getChannels()[$ch1]->getAuthKeys()[$auth]->isManageEnabled());
        $this->assertFalse($response->getChannels()[$ch2]->getAuthKeys()[$auth]->isManageEnabled());
    }
}
