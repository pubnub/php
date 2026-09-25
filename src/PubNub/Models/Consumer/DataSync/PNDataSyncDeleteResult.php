<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of every DataSync delete operation.
 *
 * A successful delete answers 200 with an empty body, so reaching this object at all means the
 * delete succeeded; anything else surfaces as an exception from sync() or an error status from envelope().
 */
class PNDataSyncDeleteResult
{
    public function isSuccess(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return "success: true";
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self();
    }
}
