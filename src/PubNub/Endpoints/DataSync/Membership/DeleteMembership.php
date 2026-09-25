<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;

/**
 * Deletes a DataSync membership. The linked channel and user are left in place.
 */
class DeleteMembership extends DataSyncEndpoint
{
    use HasIfMatch;

    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    /** A successful delete answers 200 with no body; every other operation returns one. */
    protected const RESPONSE_MAY_BE_EMPTY = true;

    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNDataSyncDeleteMembershipOperation;
    protected string $endpointName = "DeleteMembership";

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
    }

    public function sync(): PNDataSyncDeleteResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncDeleteResult
    {
        return PNDataSyncDeleteResult::fromPayload($result);
    }
}
