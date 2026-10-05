<?php

namespace PubNub\Endpoints\DataSync\Channel;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncChannelResult;

/**
 * Fetches a single DataSync channel by identifier.
 */
class GetChannel extends DataSyncEndpoint
{
    protected const RESOURCE = "channels";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.channel+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDataSyncGetChannelOperation;
    protected string $endpointName = "GetChannel";

    /**
     * @return $this
     */
    public function channelId(string $channelId): static
    {
        $this->id = $channelId;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateId("channelId");
    }

    public function sync(): PNDataSyncChannelResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function createResponse($result): PNDataSyncChannelResult
    {
        return PNDataSyncChannelResult::fromPayload($result);
    }
}
