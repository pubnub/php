<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the single-entity DataSync operations (create, get, set and update).
 */
class PNDataSyncEntityResult extends PNDataSyncRecordResult
{
    public function getData(): PNDataSyncEntity
    {
        /** @var PNDataSyncEntity */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function recordFromPayload(array $data): PNDataSyncEntity
    {
        return PNDataSyncEntity::fromPayload($data);
    }
}
