<?php

namespace PubNub\Endpoints\DataSync\Channel;

use PubNub\Endpoints\DataSync\DataSyncEndpoint;
use PubNub\Endpoints\DataSync\Traits\HasIfMatch;
use PubNub\Endpoints\DataSync\Traits\HasPayloadAndStatus;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncChannelResult;

/**
 * Replaces a DataSync channel in full.
 *
 * Anything left unset is cleared, so use updateChannel() when only some fields should change.
 */
class SetChannel extends DataSyncEndpoint
{
    use HasPayloadAndStatus;
    use HasIfMatch;

    protected const RESOURCE = "channels";
    protected const MEDIA_TYPE = "application/vnd.pubnub.objects.channel+json;version=1";

    protected string $endpointHttpMethod = PNHttpMethod::PUT;
    protected int $endpointOperationType = PNOperationType::PNDataSyncSetChannelOperation;
    protected string $endpointName = "SetChannel";

    protected ?int $entityClassVersion = null;

    /**
     * @return $this
     */
    public function channelId(string $channelId): static
    {
        $this->id = $channelId;
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
        $this->validateId("channelId");
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
