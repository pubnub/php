<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipResult;

/**
 * Fetches a single DataSync relationship by identifier.
 */
class GetRelationship extends DataSyncEndpoint
{
    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetRelationshipOperation;
    protected string $endpointName = "GetRelationship";

    /**
     * @return $this
     */
    public function relationshipId(string $relationshipId): static
    {
        $this->id = $relationshipId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("relationshipId");
    }

    public function sync(): PNDataSyncRelationshipResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncRelationshipResult
    {
        return PNDataSyncRelationshipResult::fromPayload($result);
    }
}
