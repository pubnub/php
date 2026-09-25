<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasJsonPatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipResult;

/**
 * Applies an RFC-6902 JSON Patch to a DataSync relationship, leaving untouched fields alone.
 */
class UpdateRelationship extends DataSyncEndpoint
{
    use HasJsonPatch;
    use HasIfMatch;

    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNDataSyncUpdateRelationshipOperation;
    protected string $endpointName = "UpdateRelationship";

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
        $this->validatePatch();
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        return $this->buildPatchData();
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
