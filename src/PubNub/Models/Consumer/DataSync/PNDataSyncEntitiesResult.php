<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the paginated entity list operation.
 */
class PNDataSyncEntitiesResult extends PNDataSyncCollectionResult
{
    /**
     * @return PNDataSyncEntity[]
     */
    public function getData(): array
    {
        /** @var PNDataSyncEntity[] */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $item
     */
    protected static function recordFromPayload(array $item): PNDataSyncEntity
    {
        return PNDataSyncEntity::fromPayload($item);
    }
}
