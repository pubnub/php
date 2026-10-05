<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Shared behaviour of the paginated DataSync list responses.
 *
 * They all read the same envelope - a data array of records and a meta object carrying the cursor
 * - so only the kind of record differs. A subclass says which one to build and narrows getData()
 * back to it.
 */
abstract class PNDataSyncCollectionResult
{
    /** @var PNDataSyncRecord[] */
    protected array $data;

    protected ?PNDataSyncPage $page;

    /**
     * @param PNDataSyncRecord[] $data
     */
    final public function __construct(array $data, ?PNDataSyncPage $page = null)
    {
        $this->data = $data;
        $this->page = $page;
    }

    public function getPage(): ?PNDataSyncPage
    {
        return $this->page;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function __toString(): string
    {
        return sprintf("count: %s, page: %s", count($this->data), $this->page);
    }

    /**
     * @param array<array-key, mixed> $item One element of the envelope's data array.
     */
    abstract protected static function recordFromPayload(array $item): PNDataSyncRecord;

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        $items = [];

        foreach (PNDataSyncValue::arrayOrNull($payload, "data") ?? [] as $item) {
            if (is_array($item)) {
                $items[] = static::recordFromPayload($item);
            }
        }

        $meta = PNDataSyncValue::arrayOrNull($payload, "meta");

        return new static($items, $meta === null ? null : PNDataSyncPage::fromPayload($meta));
    }
}
