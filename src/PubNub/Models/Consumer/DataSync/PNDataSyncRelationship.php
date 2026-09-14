<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A DataSync relationship record linking two entities.
 */
class PNDataSyncRelationship
{
    protected ?string $id;

    protected ?string $entityAId;

    protected ?string $entityBId;

    protected ?string $relationshipClass;

    protected ?int $relationshipClassVersion;

    protected ?string $status;

    /** @var array<string, mixed>|null */
    protected ?array $payload;

    protected ?string $createdAt;

    protected ?string $updatedAt;

    protected ?string $eTag;

    protected ?string $expiresAt;

    /**
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        ?string $id = null,
        ?string $entityAId = null,
        ?string $entityBId = null,
        ?string $relationshipClass = null,
        ?int $relationshipClassVersion = null,
        ?string $status = null,
        ?array $payload = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $eTag = null,
        ?string $expiresAt = null
    ) {
        $this->id = $id;
        $this->entityAId = $entityAId;
        $this->entityBId = $entityBId;
        $this->relationshipClass = $relationshipClass;
        $this->relationshipClassVersion = $relationshipClassVersion;
        $this->status = $status;
        $this->payload = $payload;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->eTag = $eTag;
        $this->expiresAt = $expiresAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEntityAId(): ?string
    {
        return $this->entityAId;
    }

    public function getEntityBId(): ?string
    {
        return $this->entityBId;
    }

    public function getRelationshipClass(): ?string
    {
        return $this->relationshipClass;
    }

    public function getRelationshipClassVersion(): ?int
    {
        return $this->relationshipClassVersion;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getETag(): ?string
    {
        return $this->eTag;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function __toString(): string
    {
        return sprintf(
            "id: %s, entityAId: %s, entityBId: %s, relationshipClass: %s, eTag: %s",
            $this->id,
            $this->entityAId,
            $this->entityBId,
            $this->relationshipClass,
            $this->eTag
        );
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            PNDataSyncValue::stringOrNull($payload, "id"),
            PNDataSyncValue::stringOrNull($payload, "entityAId"),
            PNDataSyncValue::stringOrNull($payload, "entityBId"),
            PNDataSyncValue::stringOrNull($payload, "relationshipClass"),
            PNDataSyncValue::intOrNull($payload, "relationshipClassVersion"),
            PNDataSyncValue::stringOrNull($payload, "status"),
            PNDataSyncValue::arrayOrNull($payload, "payload"),
            PNDataSyncValue::stringOrNull($payload, "createdAt"),
            PNDataSyncValue::stringOrNull($payload, "updatedAt"),
            PNDataSyncValue::stringOrNull($payload, "eTag"),
            PNDataSyncValue::stringOrNull($payload, "expiresAt")
        );
    }
}
