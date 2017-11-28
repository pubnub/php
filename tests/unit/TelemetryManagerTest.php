<?php

use PHPUnit\Framework\TestCase;
use PubNub\Managers\TelemetryManager;
use PubNub\Enums\PNOperationType;


class TelemetryManagerTest extends TestCase
{
    public function testAverageLatency()
    {
        $endpointLatencies = [
            ["d" => 100, "l" => 10],
            ["d" => 100, "l" => 20],
            ["d" => 100, "l" => 30],
            ["d" => 100, "l" => 40],
            ["d" => 100, "l" => 50],
        ];

        $averageLatency = TelemetryManager::averageLatencyFromData($endpointLatencies);

        $this->assertEquals(30, $averageLatency);
    }

    public function testValidQueries()
    {
        $manager = new TelemetryManager();

        $manager->storeLatency(1, PNOperationType::PNPublishOperation);
        $manager->storeLatency(2, PNOperationType::PNPublishOperation);
        $manager->storeLatency(3, PNOperationType::PNPublishOperation);
        $manager->storeLatency(4, PNOperationType::PNHistoryOperation);
        $manager->storeLatency(5, PNOperationType::PNHistoryOperation);
        $manager->storeLatency(6, PNOperationType::PNHistoryOperation);
        $manager->storeLatency(7, PNOperationType::PNRemoveGroupOperation);
        $manager->storeLatency(8, PNOperationType::PNRemoveGroupOperation);
        $manager->storeLatency(9, PNOperationType::PNRemoveGroupOperation);

        $queries = $manager->operationLatencies();

        $this->assertEquals(2, $queries["l_pub"]);
        $this->assertEquals(5, $queries["l_hist"]);
        $this->assertEquals(8, $queries["l_cg"]);
    }
}
