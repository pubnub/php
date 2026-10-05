<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasJsonPatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipResult;

/**
 * Applies an RFC-6902 JSON Patch to a DataSync membership, leaving untouched fields alone.
 */
class UpdateMembership extends DataSyncEndpoint
{
    use HasJsonPatch;
    use HasIfMatch;

    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNDataSyncUpdateMembershipOperation;
    protected string $endpointName = "UpdateMembership";

    /**
     * @return $this
     */
    public function membershipId(string $membershipId): static
    {
        $this->id = $membershipId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("membershipId");
        $this->validatePatch();
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        return $this->buildPatchData();
    }

    public function sync(): PNDataSyncMembershipResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncMembershipResult
    {
        return PNDataSyncMembershipResult::fromPayload($result);
    }
}
