<?php

namespace PubNub\Endpoints\DataSync;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;

/**
 * Shared behaviour for every DataSync endpoint.
 *
 * All DataSync resources live under /v1/datasync/subkeys/{subscribeKey}/{resource}[/{id}] and send
 * a vendor media type that is specific to the resource family, so subclasses only have to declare
 * RESOURCE and MEDIA_TYPE plus whatever fluent setters they need.
 */
abstract class DataSyncEndpoint extends Endpoint
{
    protected const PATH = "/v1/datasync/subkeys/%s/%s";

    /** Path segment identifying the resource family, e.g. "entities". */
    protected const RESOURCE = "";

    /** Vendor media type of the resource family, sent as the Content-Type of write requests. */
    protected const MEDIA_TYPE = "";

    protected const PATCH_MEDIA_TYPE = "application/json-patch+json";

    protected bool $endpointAuthRequired = true;

    /** Identifier of the addressed resource; null for create and list operations. */
    protected ?string $id = null;

    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->endpointRequestTimeout = $pubnubInstance->getConfiguration()->getNonSubscribeRequestTimeout();
        $this->endpointConnectTimeout = $pubnubInstance->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string|null
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        $path = sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            static::RESOURCE
        );

        if ($this->id !== null && $this->id !== '') {
            $path .= '/' . PubNubUtil::urlEncode($this->id);
        }

        return $path;
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        return $this->defaultParams();
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders()
    {
        // No Accept header: a delete answers with an empty body and no media type, so content
        // negotiation rejects anything specific there with a 406.
        return ['Connection' => 'Keep-Alive'];
    }

    /**
     * @return array<string, string>
     */
    protected function customHeaders()
    {
        $headers = $this->customHeaders;
        $contentType = $this->contentType();

        if ($contentType !== null) {
            $headers['Content-Type'] = $contentType;
        }

        return $headers;
    }

    /**
     * Content type of the request body, or null when the request carries no body.
     */
    protected function contentType(): ?string
    {
        return match ($this->httpMethod()) {
            PNHttpMethod::POST, PNHttpMethod::PUT => static::MEDIA_TYPE,
            PNHttpMethod::PATCH => static::PATCH_MEDIA_TYPE,
            default => null,
        };
    }

    /**
     * Wraps write payloads in the {"data": ...} request envelope the API expects.
     *
     * @param array<string, mixed> $data
     */
    protected function envelopeData(array $data): string
    {
        return PubNubUtil::writeValueAsString(['data' => $data]);
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateId(string $label): void
    {
        if ($this->id === null || trim($this->id) === '') {
            throw new PubNubValidationException("$label missing");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateClassVersion(?int $version, string $label): void
    {
        if ($version === null || $version < 1) {
            throw new PubNubValidationException("$label must be greater than or equal to 1");
        }
    }
}
