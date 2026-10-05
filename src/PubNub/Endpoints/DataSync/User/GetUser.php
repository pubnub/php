<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncUserResult;

/**
 * Fetches a single DataSync user by identifier.
 */
class GetUser extends DataSyncEndpoint
{
    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetUserOperation;
    protected string $endpointName = "GetUser";

    /**
     * @return $this
     */
    public function userId(string $userId): static
    {
        $this->id = $userId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("userId");
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
