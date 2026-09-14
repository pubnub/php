<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A DataSync entity record.
 *
 * Also used for the predefined User and Channel families, which are entity subclasses and
 * therefore carry exactly the same fields.
 *
 * Timestamps stay as the raw strings returned by the server rather than being parsed into
 * DateTime objects, matching the other PubNub SDKs.
 */
class PNDataSyncEntity
{
    protected ?string $id;

    protected ?string $entityClass;

    protected ?int $entityClassVersion;

    protected ?string $entityClassLevel;

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
        ?string $entityClass = null,
        ?int $entityClassVersion = null,
        ?string $entityClassLevel = null,
        ?string $status = null,
        ?array $payload = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $eTag = null,
        ?string $expiresAt = null
    ) {
        $this->id = $id;
        $this->entityClass = $entityClass;
        $this->entityClassVersion = $entityClassVersion;
        $this->entityClassLevel = $entityClassLevel;
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

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function getEntityClassVersion(): ?int
    {
        return $this->entityClassVersion;
    }

    public function getEntityClassLevel(): ?string
    {
        return $this->entityClassLevel;
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
            "id: %s, entityClass: %s, entityClassVersion: %s, status: %s, eTag: %s",
            $this->id,
            $this->entityClass,
            $this->entityClassVersion,
            $this->status,
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
            PNDataSyncValue::stringOrNull($payload, "entityClass"),
            PNDataSyncValue::intOrNull($payload, "entityClassVersion"),
            PNDataSyncValue::stringOrNull($payload, "entityClassLevel"),
            PNDataSyncValue::stringOrNull($payload, "status"),
            PNDataSyncValue::arrayOrNull($payload, "payload"),
            PNDataSyncValue::stringOrNull($payload, "createdAt"),
            PNDataSyncValue::stringOrNull($payload, "updatedAt"),
            PNDataSyncValue::stringOrNull($payload, "eTag"),
            PNDataSyncValue::stringOrNull($payload, "expiresAt")
        );
    }
}
