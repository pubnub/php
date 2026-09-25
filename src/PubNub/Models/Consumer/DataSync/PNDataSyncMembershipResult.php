<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the single-membership DataSync operations (create, get, set and update).
 */
class PNDataSyncMembershipResult extends PNDataSyncRecordResult
{
    public function getData(): PNDataSyncMembership
    {
        /** @var PNDataSyncMembership */
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected static function recordFromPayload(array $data): PNDataSyncMembership
    {
        return PNDataSyncMembership::fromPayload($data);
    }
}
