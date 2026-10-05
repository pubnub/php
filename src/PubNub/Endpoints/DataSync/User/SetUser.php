<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncUserResult;

/**
 * Replaces a DataSync user in full.
 *
 * Anything left unset is cleared, so use updateUser() when only some fields should change.
 */
class SetUser extends DataSyncEndpoint
{
    use HasPayloadAndStatus;
    use HasIfMatch;

    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PUT;
    protected int $endpointOperationType = PNOperationType::PNDataSyncSetUserOperation;
    protected string $endpointName = "SetUser";

    protected ?int $entityClassVersion = null;

    /**
     * @return $this
     */
    public function userId(string $userId): static
    {
        $this->id = $userId;
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
        $this->validateId("userId");
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
