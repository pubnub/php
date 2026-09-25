<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipResult;

/**
 * Creates a DataSync membership linking a channel to a user.
 *
 * Membership is a predefined relationship class, so the class name is implied by the endpoint and
 * is never sent - only its version is.
 */
class CreateMembership extends DataSyncEndpoint
{
    use HasPayloadAndStatus;

    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::POST;
    protected int $endpointOperationType = PNOperationType::PNDataSyncCreateMembershipOperation;
    protected string $endpointName = "CreateMembership";

    protected ?string $membershipId = null;

    protected ?string $channelId = null;

    protected ?string $userId = null;

    protected ?int $relationshipClassVersion = null;

    /**
     * Optional. The server generates an identifier when none is supplied.
     *
     * @return $this
     */
    public function membershipId(string $membershipId): static
    {
        $this->membershipId = $membershipId;
        return $this;
    }

    /**
     * @return $this
     */
    public function channelId(string $channelId): static
    {
        $this->channelId = $channelId;
        return $this;
    }

    /**
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
    public function relationshipClassVersion(int $relationshipClassVersion): static
    {
        $this->relationshipClassVersion = $relationshipClassVersion;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();

        if ($this->channelId === null || trim($this->channelId) === '') {
            throw new PubNubValidationException("channelId missing");
        }

        if ($this->userId === null || trim($this->userId) === '') {
            throw new PubNubValidationException("userId missing");
        }

        $this->validateClassVersion($this->relationshipClassVersion, "relationshipClassVersion");
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        $data = [];

        if ($this->membershipId !== null && $this->membershipId !== '') {
            $data['id'] = $this->membershipId;
        }

        $data['channelId'] = $this->channelId;
        $data['userId'] = $this->userId;
        $data['relationshipClassVersion'] = $this->relationshipClassVersion;

        return $this->envelopeData($this->withPayloadAndStatus($data));
    }

    public function sync(): PNDataSyncMembershipResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncMembershipResult
    {
        return PNDataSyncMembershipResult::fromPayload($result);
    }
}
