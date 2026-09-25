<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the single-relationship DataSync operations (create, get, set and update).
 */
class PNDataSyncRelationshipResult extends PNDataSyncRecordResult
{
    public function getData(): PNDataSyncRelationship
    {
        /** @var PNDataSyncRelationship */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function recordFromPayload(array $data): PNDataSyncRelationship
    {
        return PNDataSyncRelationship::fromPayload($data);
    }
}
