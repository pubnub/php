<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntityResult;

/**
 * Fetches a single DataSync entity by identifier.
 */
class GetEntity extends DataSyncEndpoint
{
    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetEntityOperation;
    protected string $endpointName = "GetEntity";

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
