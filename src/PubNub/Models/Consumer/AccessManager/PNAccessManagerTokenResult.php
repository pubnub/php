<?php

namespace PubNub\Models\Consumer\AccessManager;

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

    /** @var PNDataSyncProjections|null */
    private $dataSyncProjections;

    /** @var bool */
    private $dataSyncProjectionsParsed = false;

    final public function __construct(
        $version,
        $timestamp,
        $ttl,
        $resources,
        $patterns,
        $metadata,
        $uuid,
        $sig
    ) {
        $this->version = $version;
        $this->timestamp = $timestamp;
        $this->ttl = $ttl;
        $this->resources = $resources;
        $this->patterns = $patterns;
        $this->metadata = $metadata;
        $this->uuid = $uuid;
        $this->sig = $sig;
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
        return str_replace(['/', '+'], ['-', '_'], base64_encode($this->sig));
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
            $token['sig']
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

        $projections = $this->getDataSyncProjections();

        if ($projections !== null) {
            $result['projections'] = $projections->toArray();
        }

        return $result;
    }
}
