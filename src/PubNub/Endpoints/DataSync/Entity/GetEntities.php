<?php

namespace PubNub\Endpoints\DataSync\Entity;

use PubNub\Endpoints\DataSync\DataSyncCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncEntitiesResult;
use PubNub\PubNubUtil;

/**
 * Lists the DataSync entities of one class, one page at a time.
 */
class GetEntities extends DataSyncCollectionEndpoint
{
    protected const RESOURCE = "entities";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.entity+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetEntitiesOperation;
    protected string $endpointName = "GetEntities";

    protected ?string $entityClass = null;

    protected ?int $entityClassVersion = null;

    protected ?string $entityClassLevel = null;

    /**
     * @return $this
     */
    public function entityClass(string $entityClass): static
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * Defaults to the latest version of the class when omitted.
     *
     * @return $this
     */
    public function entityClassVersion(int $entityClassVersion): static
    {
        $this->entityClassVersion = $entityClassVersion;
        return $this;
    }

    /**
     * Either "Global" or "SubKey"; defaults to the standard SubKey then Global resolution.
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
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        $params = array_merge($this->defaultParams(), $this->collectionParams());

        $params['entity_class'] = PubNubUtil::urlEncode((string) $this->entityClass);

        if ($this->entityClassVersion !== null) {
            $params['entity_class_version'] = (string) $this->entityClassVersion;
        }

        if (!empty($this->entityClassLevel)) {
            $params['entity_class_level'] = PubNubUtil::urlEncode($this->entityClassLevel);
        }

        return $params;
    }

    public function sync(): PNDataSyncEntitiesResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncEntitiesResult
    {
        return PNDataSyncEntitiesResult::fromPayload($result);
    }
}
