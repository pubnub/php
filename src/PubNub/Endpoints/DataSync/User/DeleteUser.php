<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;

/**
 * Deletes a DataSync user.
 */
class DeleteUser extends DataSyncEndpoint
{
    use HasIfMatch;

    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNDataSyncDeleteUserOperation;
    protected string $endpointName = "DeleteUser";

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
