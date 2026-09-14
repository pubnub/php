<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A DataSync change notification delivered over the subscribe stream.
 *
 * The server publishes one of these whenever a record the client is subscribed to is created,
 * updated or deleted. The channel carrying the event is the record's identifier, or
 * __{projection}__{id} when a non-default projection is in play. Relationship and membership
 * events are published on the channels of the two records they link rather than on their own id.
 */
class PNDataSyncEventResult
{
    public const SOURCE = "data-sync";

    public const EVENT_CREATE = "create";
    public const EVENT_UPDATE = "update";
    public const EVENT_DELETE = "delete";

    public const TYPE_ENTITY = "entity";
    public const TYPE_USER = "user";
    public const TYPE_CHANNEL = "channel";
    public const TYPE_RELATIONSHIP = "relationship";
    public const TYPE_MEMBERSHIP = "membership";

    /** The predefined classes are shaped like a generic entity. */
    private const ENTITY_TYPES = [self::TYPE_ENTITY, self::TYPE_USER, self::TYPE_CHANNEL];

    /** A membership is a relationship between a Channel and a User. */
    private const RELATIONSHIP_TYPES = [self::TYPE_RELATIONSHIP, self::TYPE_MEMBERSHIP];

    protected ?string $version;

    protected ?string $event;

    protected ?string $source;

    protected ?string $type;

    protected ?string $className;

    protected ?int $classVersion;

    protected ?string $classLevel;

    protected ?PNDataSyncEntity $entity;

    protected ?PNDataSyncRelationship $relationship;

    protected ?PNDataSyncMembership $membership;

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
        ?string $classLevel = null,
        ?PNDataSyncEntity $entity = null,
        ?PNDataSyncRelationship $relationship = null,
        ?PNDataSyncMembership $membership = null,
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
        $this->classLevel = $classLevel;
        $this->entity = $entity;
        $this->relationship = $relationship;
        $this->membership = $membership;
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
     * One of "entity", "user", "channel", "relationship" or "membership".
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
     * Either "Global" or "SubKey", telling a built-in class apart from a developer-defined one
     * that happens to carry the same name.
     */
    public function getClassLevel(): ?string
    {
        return $this->classLevel;
    }

    /**
     * The changed record of an entity, user or channel event. Null on a delete and on the
     * relationship-shaped types.
     */
    public function getEntity(): ?PNDataSyncEntity
    {
        return $this->entity;
    }

    /**
     * The changed record of a relationship or membership event. Null on a delete and on the
     * entity-shaped types.
     *
     * A membership is reported here too, with the channel as entity A and the user as entity B;
     * getMembership() returns the same record under its own names.
     */
    public function getRelationship(): ?PNDataSyncRelationship
    {
        return $this->relationship;
    }

    /**
     * The changed record of a membership event under the channelId / userId names the Membership
     * endpoints use. Null for every other type and on a delete.
     */
    public function getMembership(): ?PNDataSyncMembership
    {
        return $this->membership;
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
        $className = PNDataSyncValue::stringOrNull($metadata, 'className');
        $classVersion = PNDataSyncValue::intOrNull($metadata, 'classVersion');
        $classLevel = PNDataSyncValue::stringOrNull($metadata, 'classLevel');

        $data = PNDataSyncValue::arrayOrNull($payload, 'data') ?? [];
        $id = PNDataSyncValue::stringOrNull($data, 'id');

        // The server is free to vary the casing of both fields, so neither is compared verbatim.
        $eventName = $event === null ? null : strtolower($event);
        $typeName = $type === null ? null : strtolower($type);

        if ($eventName === self::EVENT_DELETE) {
            return new self(
                $version,
                $event,
                self::SOURCE,
                $type,
                $className,
                $classVersion,
                $classLevel,
                null,
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
        $membership = null;

        // The class name and version live in the metadata rather than in the record itself.
        if (in_array($typeName, self::ENTITY_TYPES, true)) {
            $entity = PNDataSyncEntity::fromPayload(array_merge($data, [
                'entityClass' => $className,
                'entityClassVersion' => $classVersion,
            ]));
        } elseif (in_array($typeName, self::RELATIONSHIP_TYPES, true)) {
            $record = array_merge($data, [
                'relationshipClass' => $className,
                'relationshipClassVersion' => $classVersion,
            ]);

            if ($typeName === self::TYPE_MEMBERSHIP) {
                $membership = PNDataSyncMembership::fromPayload($record);

                // A membership names its two sides channelId and userId on the wire.
                $record['entityAId'] = $membership->getChannelId();
                $record['entityBId'] = $membership->getUserId();
            }

            $relationship = PNDataSyncRelationship::fromPayload($record);
        }

        return new self(
            $version,
            $event,
            self::SOURCE,
            $type,
            $className,
            $classVersion,
            $classLevel,
            $entity,
            $relationship,
            $membership,
            $id,
            null,
            $channel,
            $subscription,
            $timetoken
        );
    }
}
