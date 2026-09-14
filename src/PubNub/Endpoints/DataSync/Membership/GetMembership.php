<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipResult;

/**
 * Fetches a single DataSync membership by identifier.
 */
class GetMembership extends DataSyncEndpoint
{
    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetMembershipOperation;
    protected string $endpointName = "GetMembership";

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
