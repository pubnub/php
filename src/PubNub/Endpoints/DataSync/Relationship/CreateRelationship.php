<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipResult;

/**
 * Creates a DataSync relationship between two entities.
 */
class CreateRelationship extends DataSyncEndpoint
{
    use HasPayloadAndStatus;

    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::POST;
    protected int $endpointOperationType = PNOperationType::PNDataSyncCreateRelationshipOperation;
    protected string $endpointName = "CreateRelationship";

    protected ?string $relationshipId = null;

    protected ?string $entityAId = null;

    protected ?string $entityBId = null;

    protected ?string $relationshipClass = null;

    protected ?int $relationshipClassVersion = null;

    /**
     * Optional. The server generates an identifier when none is supplied.
     *
     * @return $this
     */
    public function relationshipId(string $relationshipId): static
    {
        $this->relationshipId = $relationshipId;
        return $this;
    }

    /**
     * Identifier of the entity on the source side of the relationship.
     *
     * @return $this
     */
    public function entityAId(string $entityAId): static
    {
        $this->entityAId = $entityAId;
        return $this;
    }

    /**
     * Identifier of the entity on the target side of the relationship.
     *
     * @return $this
     */
    public function entityBId(string $entityBId): static
    {
        $this->entityBId = $entityBId;
        return $this;
    }

    /**
     * @return $this
     */
    public function relationshipClass(string $relationshipClass): static
    {
        $this->relationshipClass = $relationshipClass;
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

        if ($this->entityAId === null || trim($this->entityAId) === '') {
            throw new PubNubValidationException("entityAId missing");
        }

        if ($this->entityBId === null || trim($this->entityBId) === '') {
            throw new PubNubValidationException("entityBId missing");
        }

        if ($this->relationshipClass === null || trim($this->relationshipClass) === '') {
            throw new PubNubValidationException("relationshipClass missing");
        }

        $this->validateClassVersion($this->relationshipClassVersion, "relationshipClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = [];

        if ($this->relationshipId !== null && $this->relationshipId !== '') {
            $data['id'] = $this->relationshipId;
        }

        $data['entityAId'] = $this->entityAId;
        $data['entityBId'] = $this->entityBId;
        $data['relationshipClass'] = $this->relationshipClass;
        $data['relationshipClassVersion'] = $this->relationshipClassVersion;

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
