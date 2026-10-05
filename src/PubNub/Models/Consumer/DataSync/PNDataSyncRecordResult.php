<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Shared behaviour of the single-record DataSync responses.
 *
 * Each of them holds one record lifted out of the {"data": ...} envelope and forwards a couple of
 * its fields for convenience. A subclass only has to say which kind of record to build and narrow
 * getData() back to that kind, because PHP will not let a typed property be narrowed in a
 * subclass and there are no generics to lean on instead.
 */
abstract class PNDataSyncRecordResult
{
    protected PNDataSyncRecord $data;

    final public function __construct(PNDataSyncRecord $data)
    {
        $this->data = $data;
    }

    public function getId(): ?string
    {
        return $this->data->getId();
    }

    /**
     * Fingerprint to hand back through ifMatchesETag() on the next write.
     */
    public function getETag(): ?string
    {
        return $this->data->getETag();
    }

    public function __toString(): string
    {
        return (string) $this->data;
    }

    /**
     * @param array<array-key, mixed> $data Contents of the envelope's data member.
     */
    abstract protected static function recordFromPayload(array $data): PNDataSyncRecord;

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new static(
            static::recordFromPayload(PNDataSyncValue::arrayOrNull($payload, "data") ?? [])
        );
    }
}
