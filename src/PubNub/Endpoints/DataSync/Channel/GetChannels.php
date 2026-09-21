<?php

namespace PubNub\Endpoints\DataSync\Channel;

use PubNub\Endpoints\DataSync\DataSyncCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\DataSync\PNDataSyncChannelsResult;

/**
 * Lists DataSync channels, one page at a time.
 *
 * The entity class defaults to Channel, so unlike getEntities() the class name is optional.
 */
class GetChannels extends DataSyncCollectionEndpoint
{
    protected const RESOURCE = "channels";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.channel+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetChannelsOperation;
    protected string $endpointName = "GetChannels";

    protected ?string $entityClass = null;

    protected ?int $entityClassVersion = null;

    protected ?string $entityClassLevel = null;

    /**
     * Narrows the listing to one class, which has to be Channel or a subclass of it.
     * Defaults to Channel when omitted.
     *
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
     * Either "Global" or "SubKey".
     *
     * @return $this
     */
    public function entityClassLevel(string $entityClassLevel): static
    {
        $this->entityClassLevel = $entityClassLevel;
        return $this;
    }

    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        $params = array_merge($this->defaultParams(), $this->collectionParams());

        if (!empty($this->entityClass)) {
            $params['entity_class'] = $this->entityClass;
        }

        if ($this->entityClassVersion !== null) {
            $params['entity_class_version'] = (string) $this->entityClassVersion;
        }

        if (!empty($this->entityClassLevel)) {
            $params['entity_class_level'] = $this->entityClassLevel;
        }

        return $params;
    }

    public function sync(): PNDataSyncChannelsResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncChannelsResult
    {
        return PNDataSyncChannelsResult::fromPayload($result);
    }
}
