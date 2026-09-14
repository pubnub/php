<?php

namespace PubNub\Endpoints\DataSync\Traits;

/**
 * Optimistic concurrency control for the DataSync write operations.
 *
 * The server answers 412 Precondition Failed when the supplied ETag no longer matches the
 * stored resource, which lets callers detect a concurrent modification instead of overwriting it.
 */
trait HasIfMatch
{
    protected ?string $eTag = null;

    /**
     * @return $this
     */
    public function ifMatchesETag(string $eTag): static
    {
        $this->eTag = $eTag;
        $this->customHeaders['If-Match'] = $eTag;
        return $this;
    }
}
