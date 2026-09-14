<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\DataSync\PNDataSyncUsersResult;
use PubNub\PubNubUtil;

/**
 * Lists DataSync users, one page at a time.
 *
 * The entity class defaults to User, so unlike getEntities() the class name is optional.
 */
class GetUsers extends DataSyncCollectionEndpoint
{
    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetUsersOperation;
    protected string $endpointName = "GetUsers";

    protected ?string $entityClass = null;

    protected ?int $entityClassVersion = null;

    protected ?string $entityClassLevel = null;

    /**
     * Narrows the listing to one class, which has to be User or a subclass of it.
     * Defaults to User when omitted.
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
            $params['entity_class'] = PubNubUtil::urlEncode($this->entityClass);
        }

        if ($this->entityClassVersion !== null) {
            $params['entity_class_version'] = (string) $this->entityClassVersion;
        }

        if (!empty($this->entityClassLevel)) {
            $params['entity_class_level'] = PubNubUtil::urlEncode($this->entityClassLevel);
        }

        return $params;
    }

    public function sync(): PNDataSyncUsersResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncUsersResult
    {
        return PNDataSyncUsersResult::fromPayload($result);
    }
}
