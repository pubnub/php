<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Response of the single-relationship DataSync operations (create, get, set and update).
 */
class PNDataSyncRelationshipResult
{
    protected PNDataSyncRelationship $data;

    final public function __construct(PNDataSyncRelationship $data)
    {
        $this->data = $data;
    }

    public function getData(): PNDataSyncRelationship
    {
        return $this->data;
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
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        $data = PNDataSyncValue::arrayOrNull($payload, "data") ?? [];

        return new static(PNDataSyncRelationship::fromPayload($data));
    }
}
