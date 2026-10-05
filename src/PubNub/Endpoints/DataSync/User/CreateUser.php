<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncUserResult;

/**
 * Creates a DataSync user.
 *
 * User is a predefined entity class, so entityClass may be left out and defaults to "User"
 * server-side; set it only when the key defines a class that derives from User.
 */
class CreateUser extends DataSyncEndpoint
{
    use HasPayloadAndStatus;

    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::POST;
    protected int $endpointOperationType = PNOperationType::PNDataSyncCreateUserOperation;
    protected string $endpointName = "CreateUser";

    protected ?string $userId = null;

    protected ?string $entityClass = null;

    protected ?int $entityClassVersion = null;

    protected ?string $entityClassLevel = null;

    /**
     * Optional. The server generates an identifier when none is supplied.
     *
     * @return $this
     */
    public function userId(string $userId): static
    {
        $this->userId = $userId;
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
     * Either "Global" or "SubKey".
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
        $this->validateClassVersion($this->entityClassVersion, "entityClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = [];

        if ($this->userId !== null && $this->userId !== '') {
            $data['id'] = $this->userId;
        }

        if ($this->entityClass !== null && $this->entityClass !== '') {
            $data['entityClass'] = $this->entityClass;
        }

        $data['entityClassVersion'] = $this->entityClassVersion;

        if ($this->entityClassLevel !== null && $this->entityClassLevel !== '') {
            $data['entityClassLevel'] = $this->entityClassLevel;
        }

        return $this->envelopeData($this->withPayloadAndStatus($data));
    }

    public function sync(): PNDataSyncUserResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncUserResult
    {
        return PNDataSyncUserResult::fromPayload($result);
    }
}
