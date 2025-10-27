<?php

namespace PubNubTests\E2e;

use PubNubTests\Helpers\PresenceTestHelper;

/**
 * End-to-end tests for HereNow that call the real PubNub server
 * These tests require valid PUBLISH_KEY and SUBSCRIBE_KEY in .env file
 *
 * These are complex E2E tests that verify pagination behavior with real concurrent clients
 *
 * @group herenow
 * @group herenow-e2e
 */
class HereNowE2eTest extends \PubNubTestCase
{
    use PresenceTestHelper;

    /**
     * Test pagination with multiple channels - verifies limit applies per-channel
     * This test requires real background clients to properly test the server-side pagination
     *
     * @group herenow-pagination
     */
    public function testHereNowMultipleChannelsWithLimit(): void
    {
        $testLimit = 2;
        $channel1ClientsCount = 5;
        $channel2ClientsCount = 3;
        $totalClientsCount = 8;
        $channels = $this->generateTestChannels(2);

        try {
            // Subscribe 5 clients to channel1
            $uuids1 = $this->subscribeBackgroundClients($channels[0], $channel1ClientsCount, 30, "ch1-user-");

            // Subscribe 3 clients to channel2
            $uuids2 = $this->subscribeBackgroundClients($channels[1], $channel2ClientsCount, 30, "ch2-user-");

            // Wait for presence to propagate for both channels
            $this->assertTrue(
                $this->waitForOccupancy($this->pubnub, $channels[0], $channel1ClientsCount, 15, 1),
                "Failed to establish presence for channel1"
            );
            $this->assertTrue(
                $this->waitForOccupancy($this->pubnub, $channels[1], $channel2ClientsCount, 15, 1),
                "Failed to establish presence for channel2"
            );

            // Test limit on multiple channels
            $response = $this->pubnub->hereNow()
                ->channels($channels)
                ->includeUuids(true)
                ->limit($testLimit)
                ->sync();

            $this->assertEquals(2, $response->getTotalChannels());
            $this->assertEquals($totalClientsCount, $response->getTotalOccupancy());

            // Find channels in response (order may vary)
            $channelDataMap = [];
            foreach ($response->getChannels() as $channelData) {
                $channelDataMap[$channelData->getChannelName()] = $channelData;
            }

            // Verify channel1 data
            $this->assertArrayHasKey($channels[0], $channelDataMap);
            $this->assertEquals($channel1ClientsCount, $channelDataMap[$channels[0]]->getOccupancy());
            $this->assertEquals($testLimit, count($channelDataMap[$channels[0]]->getOccupants()));

            // Verify channel2 data
            $this->assertArrayHasKey($channels[1], $channelDataMap);
            $this->assertEquals($channel2ClientsCount, $channelDataMap[$channels[1]]->getOccupancy());
            $this->assertEquals($testLimit, count($channelDataMap[$channels[1]]->getOccupants()));
        } finally {
            $this->cleanupBackgroundClients();
        }
    }

    /**
     * Test pagination with both limit and offset - verifies correct page windowing
     * This test requires real background clients to verify UUIDs don't overlap between pages
     *
     * @group herenow-pagination
     */
    public function testHereNowWithLimitAndOffset(): void
    {
        $pageSize = 3;
        $offset = 3;
        $totalClientsCount = 8;
        $channel = $this->generateTestChannel();

        try {
            // Subscribe 8 clients in the background
            $uuids = $this->subscribeBackgroundClients($channel, $totalClientsCount, 30);

            // Wait for presence to propagate
            $this->assertTrue(
                $this->waitForOccupancy($this->pubnub, $channel, $totalClientsCount, 15, 1),
                "Failed to establish presence for $totalClientsCount clients"
            );

            // Get first page to collect all UUIDs
            $firstPageUuids = [];
            $firstPage = $this->pubnub->hereNow()
                ->channels($channel)
                ->includeUuids(true)
                ->limit($pageSize)
                ->sync();

            foreach ($firstPage->getChannels()[0]->getOccupants() as $occupant) {
                $firstPageUuids[] = $occupant->getUuid();
            }

            // Test both limit and offset together - second page starting from position 3
            $response = $this->pubnub->hereNow()
                ->channels($channel)
                ->includeUuids(true)
                ->limit($pageSize)
                ->offset($offset)
                ->sync();

            $this->assertEquals(1, $response->getTotalChannels());
            $this->assertEquals($totalClientsCount, $response->getTotalOccupancy());

            // Should return 3 occupants (limit=3) starting from offset 3
            $this->assertEquals($pageSize, count($response->getChannels()[0]->getOccupants()));

            // Verify paginated results don't overlap with first page
            $secondPageUuids = array_map(
                function($occupant) { return $occupant->getUuid(); },
                $response->getChannels()[0]->getOccupants()
            );

            foreach ($secondPageUuids as $uuid) {
                $this->assertNotContains($uuid, $firstPageUuids, "Second page should not contain UUIDs from first page");
            }
        } finally {
            $this->cleanupBackgroundClients();
        }
    }

    protected function tearDown(): void
    {
        // Ensure cleanup happens even if test fails
        $this->cleanupBackgroundClients();
        parent::tearDown();
    }
}
