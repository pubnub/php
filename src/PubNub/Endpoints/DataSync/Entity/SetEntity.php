<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntityResult;

/**
 * Replaces a DataSync entity in full.
 *
 * Anything left unset is cleared, so use updateEntity() when only some fields should change.
 * The entity class itself is immutable and is therefore never sent.
 */
class SetEntity extends DataSyncEndpoint
{
    use HasPayloadAndStatus;
    use HasIfMatch;

    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PUT;
    protected int $endpointOperationType = PNOperationType::PNDataSyncSetEntityOperation;
    protected string $endpointName = "SetEntity";

    protected ?int $entityClassVersion = null;

    /**
     * @return $this
     */
    public function entityId(string $entityId): static
    {
        $this->id = $entityId;
        return $this;
    }

    /**
     * @return $this
     */
    public function entityClassVersion(int $entityClassVersion): static
    {
        $this->entityClassVersion = $entityClassVersion;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("entityId");
        $this->validateClassVersion($this->entityClassVersion, "entityClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = ['entityClassVersion' => $this->entityClassVersion];

        return $this->envelopeData($this->withPayloadAndStatus($data));
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
