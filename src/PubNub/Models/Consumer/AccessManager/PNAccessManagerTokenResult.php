<?php

namespace PubNub\Models\Consumer\AccessManager;

use PubNub\Enums\PNGrantCategory;
use PubNub\Models\Access\Permissions;

class PNAccessManagerTokenResult
{
    /** @var int */
    private $version;

    /** @var int */
    private $timestamp;

    /** @var int */
    private $ttl;

    /** @var object */
    private $resources;

    /** @var object */
    private $patterns;

    /** @var array<string, mixed>|object|null */
    private $metadata;

    /** @var string */
    private $sig;

    /** @var string */
    private $uuid;

    /** @var array<string, int> */
    private $categories = [];

    /** @var PNDataSyncProjections|null */
    private $dataSyncProjections;

    /** @var bool */
    private $dataSyncProjectionsParsed = false;

    /**
     * @param array<string, int> $categories
     */
    final public function __construct(
        $version,
        $timestamp,
        $ttl,
        $resources,
        $patterns,
        $metadata,
        $uuid,
        $sig,
        array $categories = []
    ) {
        $this->version = $version;
        $this->timestamp = $timestamp;
        $this->ttl = $ttl;
        $this->resources = $resources;
        $this->patterns = $patterns;
        $this->metadata = $metadata;
        $this->uuid = $uuid;
        $this->sig = $sig;
        $this->categories = $categories;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getChannelResource($name)
    {
        return $this->getResource('chan', $name);
    }

    public function getChannelGroupResource($name)
    {
        return $this->getResource('grp', $name);
    }

    public function getUuidResource($name)
    {
        return $this->getResource('uuid', $name);
    }

    /**
     * Grants on the "users" scope, which a token abbreviates to "usr" and which is distinct from
     * the "uuid" scope above. DataSync User records are authorised through this one.
     *
     * @return Permissions|false false when the token grants nothing for that user.
     */
    public function getUserResource(string $name)
    {
        return $this->getResource('usr', $name);
    }

    /**
     * @return Permissions|false false when the token grants nothing for that entity.
     */
    public function getDataSyncEntityResource(string $name)
    {
        return $this->getResource('datasync:entities', $name);
    }

    /**
     * @return Permissions|false false when the token grants nothing for that relationship.
     */
    public function getDataSyncRelationshipResource(string $name)
    {
        return $this->getResource('datasync:relationships', $name);
    }

    /**
     * @return Permissions|false false when the token grants nothing for that membership.
     */
    public function getDataSyncMembershipResource(string $name)
    {
        return $this->getResource('datasync:memberships', $name);
    }

    private function getResource($type, $name)
    {
        if (isset($this->resources[$type][$name])) {
            return new Permissions($name, $this->resources[$type][$name]);
        } else {
            return false;
        }
    }

    public function getChannelPattern($name)
    {
        return $this->getPattern('chan', $name);
    }

    public function getChannelGroupPattern($name)
    {
        return $this->getPattern('grp', $name);
    }

    public function getUuidPattern($name)
    {
        return $this->getPattern('uuid', $name);
    }

    /**
     * Patterns on the "users" scope. See getUserResource() for how it differs from uuid.
     *
     * @return Permissions|false false when the token holds no user pattern with that name.
     */
    public function getUserPattern(string $name)
    {
        return $this->getPattern('usr', $name);
    }

    /**
     * @return Permissions|false false when the token holds no entity pattern with that name.
     */
    public function getDataSyncEntityPattern(string $name)
    {
        return $this->getPattern('datasync:entities', $name);
    }

    /**
     * @return Permissions|false false when the token holds no relationship pattern with that name.
     */
    public function getDataSyncRelationshipPattern(string $name)
    {
        return $this->getPattern('datasync:relationships', $name);
    }

    /**
     * @return Permissions|false false when the token holds no membership pattern with that name.
     */
    public function getDataSyncMembershipPattern(string $name)
    {
        return $this->getPattern('datasync:memberships', $name);
    }

    private function getPattern($type, $name)
    {
        if (isset($this->patterns[$type][$name])) {
            return new Permissions($name, $this->patterns[$type][$name]);
        } else {
            return false;
        }
    }

    /**
     * Category-level permissions as the token stores them, keyed "chan" and "uuid".
     *
     * @return array<string, int>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * The channels category, whose get permission allows listing all channel metadata.
     *
     * @return Permissions|false false when the token carries no channels category.
     */
    public function getChannelsCategory()
    {
        return $this->getCategory('chan', PNGrantCategory::CHANNELS);
    }

    /**
     * The uuids category, whose get permission allows listing all uuid metadata.
     *
     * @return Permissions|false false when the token carries no uuids category.
     */
    public function getUuidsCategory()
    {
        return $this->getCategory('uuid', PNGrantCategory::UUIDS);
    }

    /**
     * @return Permissions|false
     */
    private function getCategory(string $type, string $name)
    {
        if (isset($this->categories[$type])) {
            return new Permissions($name, $this->categories[$type]);
        } else {
            return false;
        }
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * DataSync field projections granted by this token, or null when it carries none.
     */
    public function getDataSyncProjections(): ?PNDataSyncProjections
    {
        if (!$this->dataSyncProjectionsParsed) {
            $this->dataSyncProjectionsParsed = true;
            $meta = $this->metadata;

            if (is_object($meta)) {
                $meta = (array) $meta;
            }

            if (is_array($meta) && isset($meta['pn-projections'])) {
                $this->dataSyncProjections = PNDataSyncProjections::fromArray($meta['pn-projections']);
            }
        }

        return $this->dataSyncProjections;
    }

    public function getSignature()
    {
        return strtr(base64_encode($this->sig), '+/', '-_');
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public static function fromArray($token)
    {
        return new static(
            $token['v'],
            $token['t'],
            $token['ttl'],
            $token['res'],
            $token['pat'],
            $token['meta'],
            (isset($token['uuid']) ? $token['uuid'] : null),
            $token['sig'],
            $token['cat'] ?? []
        );
    }

    public function toArray()
    {
        $resources = [];
        foreach ($this->resources as $type => $typeRes) {
            if (!empty($typeRes)) {
                $resources[$type] = [];
                foreach ($typeRes as $name => $value) {
                    $resources[$type] = array_merge($resources[$type], (new Permissions($name, $value))->toArray());
                }
            }
        }

        $patterns = [];
        foreach ($this->patterns as $type => $typePat) {
            if (!empty($typePat)) {
                $patterns[$type] = [];
                foreach ($typePat as $name => $value) {
                    $patterns[$type] = array_merge($patterns[$type], (new Permissions($name, $value))->toArray());
                }
            }
        }

        $result = [
            'version' => $this->version,
            'timestamp' => $this->timestamp,
            'ttl' => $this->ttl,
            'resources' => $resources,
            'patterns' => $patterns,
            'signature' => $this->getSignature(),
            'uuid' => $this->uuid,
        ];

        if (!empty($this->categories)) {
            $categories = [];
            foreach ($this->categories as $type => $value) {
                $categories = array_merge($categories, (new Permissions($type, $value))->toArray());
            }
            $result['categories'] = $categories;
        }

        $projections = $this->getDataSyncProjections();

        if ($projections !== null) {
            $result['projections'] = $projections->toArray();
        }

        return $result;
    }
}
