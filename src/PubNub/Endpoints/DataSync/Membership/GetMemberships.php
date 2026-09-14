<?php

namespace PubNub\Endpoints\DataSync\Membership;

use PubNub\Endpoints\DataSync\DataSyncCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\DataSync\PNDataSyncMembershipsResult;
use PubNub\PubNubUtil;

/**
 * Lists DataSync memberships, one page at a time.
 *
 * Set userId to list the channels a user belongs to, or channelId to list the members of a channel.
 */
class GetMemberships extends DataSyncCollectionEndpoint
{
    protected const RESOURCE = "memberships";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.membership+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetMembershipsOperation;
    protected string $endpointName = "GetMemberships";

    protected ?string $channelId = null;

    protected ?string $userId = null;

    protected ?int $relationshipClassVersion = null;

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
     * Defaults to the latest version of the class when omitted.
     *
     * @return $this
     */
    public function relationshipClassVersion(int $relationshipClassVersion): static
    {
        $this->relationshipClassVersion = $relationshipClassVersion;
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

        if (!empty($this->channelId)) {
            $params['channel_id'] = PubNubUtil::urlEncode($this->channelId);
        }

        if (!empty($this->userId)) {
            $params['user_id'] = PubNubUtil::urlEncode($this->userId);
        }

        if ($this->relationshipClassVersion !== null) {
            $params['relationship_class_version'] = (string) $this->relationshipClassVersion;
        }

        return $params;
    }

    public function sync(): PNDataSyncMembershipsResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncMembershipsResult
    {
        return PNDataSyncMembershipsResult::fromPayload($result);
    }
}
