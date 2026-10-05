<?php

namespace PubNub\Models\Consumer\AccessManager;

/**
 * One half of the DataSync projections carried by a grant token, either the exact-identifier
 * scope ("res") or the pattern scope ("pat").
 *
 * On the wire the scope is a flat map of composite keys - "datasync:entities:vehicle-1" mapped to
 * a projection name - which this splits back into one map per record family.
 */
class PNDataSyncProjectionScope
{
    private const PREFIX = 'datasync:';

    /** @var array<string, string> */
    private array $entities;

    /** @var array<string, string> */
    private array $users;

    /** @var array<string, string> */
    private array $channels;

    /** @var array<string, string> */
    private array $relationships;

    /** @var array<string, string> */
    private array $memberships;

    /**
     * @param array<string, string> $entities
     * @param array<string, string> $users
     * @param array<string, string> $channels
     * @param array<string, string> $relationships
     * @param array<string, string> $memberships
     */
    public function __construct(
        array $entities = [],
        array $users = [],
        array $channels = [],
        array $relationships = [],
        array $memberships = []
    ) {
        $this->entities = $entities;
        $this->users = $users;
        $this->channels = $channels;
        $this->relationships = $relationships;
        $this->memberships = $memberships;
    }

    /**
     * Identifier to projection name, for entities.
     *
     * @return array<string, string>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Identifier to projection name, for users.
     *
     * @return array<string, string>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Identifier to projection name, for channels.
     *
     * @return array<string, string>
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Identifier to projection name, for relationships.
     *
     * @return array<string, string>
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Identifier to projection name, for memberships.
     *
     * @return array<string, string>
     */
    public function getMemberships(): array
    {
        return $this->memberships;
    }

    /**
     * Projection name granted for one entity, or null when the token names no projection for it.
     */
    public function getEntityProjection(string $id): ?string
    {
        return $this->entities[$id] ?? null;
    }

    public function getUserProjection(string $id): ?string
    {
        return $this->users[$id] ?? null;
    }

    public function getChannelProjection(string $id): ?string
    {
        return $this->channels[$id] ?? null;
    }

    public function getRelationshipProjection(string $id): ?string
    {
        return $this->relationships[$id] ?? null;
    }

    public function getMembershipProjection(string $id): ?string
    {
        return $this->memberships[$id] ?? null;
    }

    public function isEmpty(): bool
    {
        return count($this->entities) === 0
            && count($this->users) === 0
            && count($this->channels) === 0
            && count($this->relationships) === 0
            && count($this->memberships) === 0;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function toArray(): array
    {
        return [
            'entities' => $this->entities,
            'users' => $this->users,
            'channels' => $this->channels,
            'relationships' => $this->relationships,
            'memberships' => $this->memberships,
        ];
    }

    /**
     * Malformed and unrecognised keys are skipped rather than raised; a token is allowed to carry
     * scopes this SDK version does not know about yet.
     *
     * @param mixed $scope
     */
    public static function fromArray($scope): self
    {
        $families = [
            'entities' => [],
            'users' => [],
            'channels' => [],
            'relationships' => [],
            'memberships' => [],
        ];

        if (!is_array($scope)) {
            return new self();
        }

        foreach ($scope as $compositeKey => $projectionName) {
            $parsed = self::splitCompositeKey((string) $compositeKey);

            if ($parsed === null) {
                continue;
            }

            [$type, $id] = $parsed;

            if (array_key_exists($type, $families)) {
                $families[$type][$id] = (string) $projectionName;
            }
        }

        return new self(
            $families['entities'],
            $families['users'],
            $families['channels'],
            $families['relationships'],
            $families['memberships']
        );
    }

    /**
     * Splits "datasync:{type}:{id}" into its type and id, or returns null when it does not match.
     *
     * @return array{0: string, 1: string}|null
     */
    private static function splitCompositeKey(string $compositeKey): ?array
    {
        if (strpos($compositeKey, self::PREFIX) !== 0) {
            return null;
        }

        $remainder = substr($compositeKey, strlen(self::PREFIX));
        $separator = strpos($remainder, ':');

        // Both the type and the identifier have to be non-empty.
        if ($separator === false || $separator === 0 || $separator === strlen($remainder) - 1) {
            return null;
        }

        return [substr($remainder, 0, $separator), substr($remainder, $separator + 1)];
    }
}
