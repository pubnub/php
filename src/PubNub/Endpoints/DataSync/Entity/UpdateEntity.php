<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasJsonPatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntityResult;

/**
 * Applies an RFC-6902 JSON Patch to a DataSync entity, leaving untouched fields alone.
 */
class UpdateEntity extends DataSyncEndpoint
{
    use HasJsonPatch;
    use HasIfMatch;

    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNDataSyncUpdateEntityOperation;
    protected string $endpointName = "UpdateEntity";

    /**
     * @return $this
     */
    public function entityId(string $entityId): static
    {
        $this->id = $entityId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("entityId");
        $this->validatePatch();
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        return $this->buildPatchData();
    }

    public function sync(): PNDataSyncEntityResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncEntityResult
    {
        return PNDataSyncEntityResult::fromPayload($result);
    }
}
