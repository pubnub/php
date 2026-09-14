<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the paginated relationship list operation.
 */
class PNDataSyncRelationshipsResult
{
    /** @var PNDataSyncRelationship[] */
    protected array $data;

    protected ?PNDataSyncPage $page;

    /**
     * @param PNDataSyncRelationship[] $data
     */
    final public function __construct(array $data, ?PNDataSyncPage $page = null)
    {
        $this->data = $data;
        $this->page = $page;
    }

    /**
     * @return PNDataSyncRelationship[]
     */
    public function getData(): array
    {
        return $this->data;
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
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        $items = [];

        if (array_key_exists("data", $payload) && is_array($payload["data"])) {
            foreach ($payload["data"] as $item) {
                if (is_array($item)) {
                    $items[] = PNDataSyncRelationship::fromPayload($item);
                }
            }
        }

        $meta = PNDataSyncValue::arrayOrNull($payload, "meta");

        return new static(
            $items,
            $meta === null ? null : PNDataSyncPage::fromPayload($meta)
        );
    }
}
