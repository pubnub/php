<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the paginated membership list operation.
 */
class PNDataSyncMembershipsResult extends PNDataSyncCollectionResult
{
    /**
     * @return PNDataSyncMembership[]
     */
    public function getData(): array
    {
        /** @var PNDataSyncMembership[] */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $item
     */
    protected static function recordFromPayload(array $item): PNDataSyncMembership
    {
        return PNDataSyncMembership::fromPayload($item);
    }
}
