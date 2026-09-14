<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipResult;

/**
 * Replaces a DataSync relationship in full.
 *
 * The relationship class and the two linked entities are immutable and are therefore never sent.
 */
class SetRelationship extends DataSyncEndpoint
{
    use HasPayloadAndStatus;
    use HasIfMatch;

    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PUT;
    protected int $endpointOperationType = PNOperationType::PNDataSyncSetRelationshipOperation;
    protected string $endpointName = "SetRelationship";

    protected ?int $relationshipClassVersion = null;

    /**
     * @return $this
     */
    public function relationshipId(string $relationshipId): static
    {
        $this->id = $relationshipId;
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
        $this->validateId("relationshipId");
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
