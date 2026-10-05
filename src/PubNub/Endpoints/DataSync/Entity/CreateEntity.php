<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntityResult;

/**
 * Creates a DataSync entity.
 *
 * The entity class has to already exist in the key's class registry; it is defined in the PubNub
 * portal rather than through the SDK.
 */
class CreateEntity extends DataSyncEndpoint
{
    use HasPayloadAndStatus;

    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::POST;
    protected int $endpointOperationType = PNOperationType::PNDataSyncCreateEntityOperation;
    protected string $endpointName = "CreateEntity";

    protected ?string $entityId = null;

    protected ?string $entityClass = null;

    protected ?int $entityClassVersion = null;

    protected ?string $entityClassLevel = null;

    /**
     * Optional. The server generates an identifier when none is supplied.
     *
     * @return $this
     */
    public function entityId(string $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return $this
     */
    public function entityClass(string $entityClass): static
    {
        $this->entityClass = $entityClass;
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
     * Either "Global" or "SubKey". Only needed to disambiguate two classes that share a name at
     * different levels; otherwise the server resolves SubKey before Global.
     *
     * @return $this
     */
    public function entityClassLevel(string $entityClassLevel): static
    {
        $this->entityClassLevel = $entityClassLevel;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();

        if ($this->entityClass === null || trim($this->entityClass) === '') {
            throw new PubNubValidationException("entityClass missing");
        }

        $this->validateClassVersion($this->entityClassVersion, "entityClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = [];

        if ($this->entityId !== null && $this->entityId !== '') {
            $data['id'] = $this->entityId;
        }

        $data['entityClass'] = $this->entityClass;
        $data['entityClassVersion'] = $this->entityClassVersion;

        if ($this->entityClassLevel !== null && $this->entityClassLevel !== '') {
            $data['entityClassLevel'] = $this->entityClassLevel;
        }

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
