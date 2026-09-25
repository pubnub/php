<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A DataSync membership record.
 *
 * Memberships are a predefined relationship class between a Channel and a User. The API exposes them
 * through channelId and userId rather than the generic entityAId and entityBId of a relationship.
 */
class PNDataSyncMembership implements PNDataSyncRecord
{
    protected ?string $id;

    protected ?string $channelId;

    protected ?string $userId;

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
        ?string $channelId = null,
        ?string $userId = null,
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
        $this->channelId = $channelId;
        $this->userId = $userId;
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

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * The predefined membership class the record belongs to, or a subclass of it.
     */
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
            "id: %s, channelId: %s, userId: %s, status: %s, eTag: %s",
            $this->id,
            $this->channelId,
            $this->userId,
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
            PNDataSyncValue::stringOrNull($payload, "channelId"),
            PNDataSyncValue::stringOrNull($payload, "userId"),
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
