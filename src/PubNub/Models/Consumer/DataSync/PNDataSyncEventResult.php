<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A DataSync change notification delivered over the subscribe stream.
 *
 * The server publishes one of these whenever a record the client is subscribed to is created,
 * updated or deleted. The channel carrying the event is the record's identifier, or
 * __{projection}__{id} when a non-default projection is in play.
 */
class PNDataSyncEventResult
{
    public const SOURCE = "data-sync";

    public const EVENT_CREATE = "create";
    public const EVENT_UPDATE = "update";
    public const EVENT_DELETE = "delete";

    public const TYPE_ENTITY = "entity";
    public const TYPE_RELATIONSHIP = "relationship";

    protected ?string $version;

    protected ?string $event;

    protected ?string $source;

    protected ?string $type;

    protected ?string $className;

    protected ?int $classVersion;

    protected ?PNDataSyncEntity $entity;

    protected ?PNDataSyncRelationship $relationship;

    protected ?string $id;

    protected ?string $deletedAt;

    protected ?string $channel;

    protected ?string $subscription;

    protected ?string $timetoken;

    public function __construct(
        ?string $version = null,
        ?string $event = null,
        ?string $source = null,
        ?string $type = null,
        ?string $className = null,
        ?int $classVersion = null,
        ?PNDataSyncEntity $entity = null,
        ?PNDataSyncRelationship $relationship = null,
        ?string $id = null,
        ?string $deletedAt = null,
        ?string $channel = null,
        ?string $subscription = null,
        ?string $timetoken = null
    ) {
        $this->version = $version;
        $this->event = $event;
        $this->source = $source;
        $this->type = $type;
        $this->className = $className;
        $this->classVersion = $classVersion;
        $this->entity = $entity;
        $this->relationship = $relationship;
        $this->id = $id;
        $this->deletedAt = $deletedAt;
        $this->channel = $channel;
        $this->subscription = $subscription;
        $this->timetoken = $timetoken;
    }

    /**
     * Schema version of the event envelope itself, e.g. "3.0".
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * One of "create", "update" or "delete".
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * Always "data-sync"; events from other sources never reach a listener.
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * Either "entity" or "relationship".
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getClassVersion(): ?int
    {
        return $this->classVersion;
    }

    /**
     * The changed entity, or null for a delete event or a relationship event.
     */
    public function getEntity(): ?PNDataSyncEntity
    {
        return $this->entity;
    }

    /**
     * The changed relationship, or null for a delete event or an entity event.
     */
    public function getRelationship(): ?PNDataSyncRelationship
    {
        return $this->relationship;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getSubscription(): ?string
    {
        return $this->subscription;
    }

    public function getTimetoken(): ?string
    {
        return $this->timetoken;
    }

    public function __toString(): string
    {
        return sprintf(
            "event: %s, type: %s, className: %s, classVersion: %s, id: %s",
            $this->event,
            $this->type,
            $this->className,
            $this->classVersion,
            $this->id
        );
    }

    /**
     * Builds an event from a subscribe payload, or returns null when the payload is not a DataSync event.
     *
     * @param mixed $payload
     */
    public static function fromPayload(
        $payload,
        ?string $channel = null,
        ?string $subscription = null,
        ?string $timetoken = null
    ): ?self {
        if (!is_array($payload) || !array_key_exists('metadata', $payload)) {
            return null;
        }

        $metadata = $payload['metadata'];

        if (!is_array($metadata)) {
            return null;
        }

        // Message type 5 is not exclusive to DataSync, so unrelated traffic has to be filtered out.
        if (PNDataSyncValue::stringOrNull($metadata, 'source') !== self::SOURCE) {
            return null;
        }

        $version = PNDataSyncValue::stringOrNull($payload, 'version');
        $event = PNDataSyncValue::stringOrNull($metadata, 'event');
        $type = PNDataSyncValue::stringOrNull($metadata, 'type');
        $className = self::leafClassName(PNDataSyncValue::stringOrNull($metadata, 'className'));
        $classVersion = PNDataSyncValue::intOrNull($metadata, 'classVersion');

        $data = PNDataSyncValue::arrayOrNull($payload, 'data') ?? [];
        $id = PNDataSyncValue::stringOrNull($data, 'id');

        if ($event === self::EVENT_DELETE) {
            return new self(
                $version,
                $event,
                self::SOURCE,
                $type,
                $className,
                $classVersion,
                null,
                null,
                $id,
                PNDataSyncValue::stringOrNull($data, 'deletedAt'),
                $channel,
                $subscription,
                $timetoken
            );
        }

        $entity = null;
        $relationship = null;

        // The class name and version live in the metadata rather than in the record itself.
        if ($type === self::TYPE_ENTITY) {
            $entity = PNDataSyncEntity::fromPayload(array_merge($data, [
                'entityClass' => $className,
                'entityClassVersion' => $classVersion,
            ]));
        } elseif ($type === self::TYPE_RELATIONSHIP) {
            $relationship = PNDataSyncRelationship::fromPayload(array_merge($data, [
                'relationshipClass' => $className,
                'relationshipClassVersion' => $classVersion,
            ]));
        }

        return new self(
            $version,
            $event,
            self::SOURCE,
            $type,
            $className,
            $classVersion,
            $entity,
            $relationship,
            $id,
            null,
            $channel,
            $subscription,
            $timetoken
        );
    }

    /**
     * Class names arrive colon-delimited with their inherited classes ("Base:vehicle"); only the
     * most derived one identifies the record.
     */
    private static function leafClassName(?string $className): ?string
    {
        if ($className === null || $className === '') {
            return $className;
        }

        $segments = explode(':', $className);

        return end($segments);
    }
}
