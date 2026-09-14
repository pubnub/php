<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;

/**
 * Deletes a DataSync relationship. The linked entities themselves are left in place.
 */
class DeleteRelationship extends DataSyncEndpoint
{
    use HasIfMatch;

    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNDataSyncDeleteRelationshipOperation;
    protected string $endpointName = "DeleteRelationship";

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
