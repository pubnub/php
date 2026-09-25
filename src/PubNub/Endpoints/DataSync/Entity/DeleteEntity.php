<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;

/**
 * Deletes a DataSync entity.
 */
class DeleteEntity extends DataSyncEndpoint
{
    use HasIfMatch;

    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    /** A successful delete answers 200 with no body; every other operation returns one. */
    protected const RESPONSE_MAY_BE_EMPTY = true;

    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNDataSyncDeleteEntityOperation;
    protected string $endpointName = "DeleteEntity";

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
