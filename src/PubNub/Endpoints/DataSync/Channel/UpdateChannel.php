<?php

namespace PubNub\Endpoints\DataSync\Channel;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasJsonPatch;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncChannelResult;

/**
 * Applies an RFC-6902 JSON Patch to a DataSync channel, leaving untouched fields alone.
 */
class UpdateChannel extends DataSyncEndpoint
{
    use HasJsonPatch;
    use HasIfMatch;

    protected const RESOURCE = "channels";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.channel+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNDataSyncUpdateChannelOperation;
    protected string $endpointName = "UpdateChannel";

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
        $this->validatePatch();
    }

    /**
     * @return string
     */
    protected function buildData()
    {
        return $this->buildPatchData();
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
