<?php

namespace PubNub\Endpoints\DataSync\User;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasJsonPatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncUserResult;

/**
 * Applies an RFC-6902 JSON Patch to a DataSync user, leaving untouched fields alone.
 */
class UpdateUser extends DataSyncEndpoint
{
    use HasJsonPatch;
    use HasIfMatch;

    protected const RESOURCE = "users";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.user+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNDataSyncUpdateUserOperation;
    protected string $endpointName = "UpdateUser";

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
        $this->validatePatch();
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        return $this->buildPatchData();
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
