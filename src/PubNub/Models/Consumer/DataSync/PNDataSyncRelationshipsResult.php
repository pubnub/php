<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the paginated relationship list operation.
 */
class PNDataSyncRelationshipsResult extends PNDataSyncCollectionResult
{
    /**
     * @return PNDataSyncRelationship[]
     */
    public function getData(): array
    {
        /** @var PNDataSyncRelationship[] */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $item
     */
    protected static function recordFromPayload(array $item): PNDataSyncRelationship
    {
        return PNDataSyncRelationship::fromPayload($item);
    }
}
