<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * The `meta` block of a paginated DataSync list response.
 *
 * Note the wire format uses snake_case here even though the resource fields are camelCase.
 */
class PNDataSyncPage
{
    protected ?string $nextCursor;

    protected bool $hasNext;

    protected ?int $limit;

    public function __construct(?string $nextCursor = null, bool $hasNext = false, ?int $limit = null)
    {
        $this->nextCursor = $nextCursor;
        $this->hasNext = $hasNext;
        $this->limit = $limit;
    }

    /**
     * Opaque token to pass to cursor() on the next request; null once the last page is reached.
     */
    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function hasNext(): bool
    {
        return $this->hasNext;
    }

    /**
     * The limit the server actually applied, which may differ from the requested one.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function __toString(): string
    {
        return sprintf(
            "nextCursor: %s, hasNext: %s, limit: %s",
            $this->nextCursor,
            $this->hasNext ? 'true' : 'false',
            $this->limit
        );
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            PNDataSyncValue::stringOrNull($payload, "next_cursor"),
            (bool) PNDataSyncValue::boolOrNull($payload, "has_next"),
            PNDataSyncValue::intOrNull($payload, "limit")
        );
    }
}
