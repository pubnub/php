<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipResult;

/**
 * Replaces a DataSync membership in full.
 *
 * The linked channel and user are immutable and are therefore never sent.
 */
class SetMembership extends DataSyncEndpoint
{
    use HasPayloadAndStatus;
    use HasIfMatch;

    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PUT;
    protected int $endpointOperationType = PNOperationType::PNDataSyncSetMembershipOperation;
    protected string $endpointName = "SetMembership";

    protected ?int $relationshipClassVersion = null;

    /**
     * @return $this
     */
    public function membershipId(string $membershipId): static
    {
        $this->id = $membershipId;
        return $this;
    }

    /**
     * @return $this
     */
    public function relationshipClassVersion(int $relationshipClassVersion): static
    {
        $this->relationshipClassVersion = $relationshipClassVersion;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("membershipId");
        $this->validateClassVersion($this->relationshipClassVersion, "relationshipClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = ['relationshipClassVersion' => $this->relationshipClassVersion];

        return $this->envelopeData($this->withPayloadAndStatus($data));
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
