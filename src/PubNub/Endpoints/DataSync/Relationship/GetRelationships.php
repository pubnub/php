<?php

namespace PubNub\Endpoints\DataSync\Relationship;

use PubNub\Endpoints\DataSync\DataSyncCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncRelationshipsResult;

/**
 * Lists the DataSync relationships of one class, one page at a time.
 *
 * Narrow the result to the links of a specific entity by setting entityAId, entityBId or both.
 */
class GetRelationships extends DataSyncCollectionEndpoint
{
    protected const RESOURCE = "relationships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.relationship+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetRelationshipsOperation;
    protected string $endpointName = "GetRelationships";

    protected ?string $relationshipClass = null;

    protected ?int $relationshipClassVersion = null;

    protected ?string $entityAId = null;

    protected ?string $entityBId = null;

    /**
     * @return $this
     */
    public function relationshipClass(string $relationshipClass): static
    {
        $this->relationshipClass = $relationshipClass;
        return $this;
    }

    /**
     * Defaults to the latest version of the class when omitted.
     *
     * @return $this
     */
    public function relationshipClassVersion(int $relationshipClassVersion): static
    {
        $this->relationshipClassVersion = $relationshipClassVersion;
        return $this;
    }

    /**
     * @return $this
     */
    public function entityAId(string $entityAId): static
    {
        $this->entityAId = $entityAId;
        return $this;
    }

    /**
     * @return $this
     */
    public function entityBId(string $entityBId): static
    {
        $this->entityBId = $entityBId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();

        if ($this->relationshipClass === null || trim($this->relationshipClass) === '') {
            throw new PubNubValidationException("relationshipClass missing");
        }
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        $params = array_merge($this->defaultParams(), $this->collectionParams());

        $params['relationship_class'] = (string) $this->relationshipClass;

        if ($this->relationshipClassVersion !== null) {
            $params['relationship_class_version'] = (string) $this->relationshipClassVersion;
        }

        if (!empty($this->entityAId)) {
            $params['entity_a_id'] = $this->entityAId;
        }

        if (!empty($this->entityBId)) {
            $params['entity_b_id'] = $this->entityBId;
        }

        return $params;
    }

    public function sync(): PNDataSyncRelationshipsResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncRelationshipsResult
    {
        return PNDataSyncRelationshipsResult::fromPayload($result);
    }
}
