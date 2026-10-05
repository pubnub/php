<?php

namespace PubNub\Endpoints\DataSync\Channel;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncDeleteResult;

/**
 * Deletes a DataSync channel.
 */
class DeleteChannel extends DataSyncEndpoint
{
    use HasIfMatch;

    protected const RESOURCE = "channels";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.channel+json;version=1";

    /** A successful delete answers 200 with no body; every other operation returns one. */
    protected const RESPONSE_MAY_BE_EMPTY = true;

    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNDataSyncDeleteChannelOperation;
    protected string $endpointName = "DeleteChannel";

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
