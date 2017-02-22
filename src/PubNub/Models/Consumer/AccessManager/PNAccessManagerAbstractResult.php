<?php

namespace  PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

class PNAccessManagerAbstractResult
{
    /** @var  string */
    protected $level;

    /** @var  int */
    protected $ttl;

    /** @var  string */
    protected $subscribeKey;

    /** @var  array */
    protected $channels;

    /** @var  array */
    protected $channelGroups;

    /** @var  bool */
    protected $readEnabled;

    /** @var  bool */
    protected $writeEnabled;

    /** @var  bool */
    protected $manageEnabled;

    /**
     * PNAccessManagerAbstractResult constructor.
     * @param string $level
     * @param string $subscribeKey
     * @param array $channels
     * @param array $channelGroups
     * @param int $ttl
     * @param bool $r
     * @param bool $w
     * @param bool $m
     */
    public function __construct($level, $subscribeKey, array $channels, array $channelGroups, $ttl, $r, $w, $m)
    {
        $this->level = $level;
        $this->subscribeKey = $subscribeKey;
        $this->channels = $channels;
        $this->channelGroups = $channelGroups;
        $this->ttl = $ttl;
        $this->readEnabled = $r;
        $this->writeEnabled = $w;
        $this->manageEnabled = $m;
    }

    /**
     * @param array $jsonInput
     * @return mixed
     */
    public static function fromJson($jsonInput)
    {
        $constructedChannels = [];
        $constructedGroups = [];
        list($r, $w, $m, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        if (array_key_exists('channel', $jsonInput)) {
            $channelName = $jsonInput['channel'];
            $constructedAuthKeys = [];

            foreach ($jsonInput['auths'] as $authKeyName => $value) {
                $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
            }

            $constructedChannels[$channelName] = new PNAccessManagerChannelData(
                $channelName, $constructedAuthKeys, null, null, null, $ttl);
        }

        if (array_key_exists('channel-group', $jsonInput)) {
            if (is_string($jsonInput['channel-group'])) {
                $groupName = $jsonInput['channel-group'];
                $constructedAuthKeys = [];

                foreach ($jsonInput['auths'] as $authKeyName => $value) {
                    $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
                }

                $constructedGroups[$groupName] = new PNAccessManagerChannelGroupData(
                    $groupName,
                    $constructedAuthKeys,
                    null,
                    null,
                    null,
                    $ttl
                );
            }

            if (is_array($jsonInput['channel-group'])) {
                foreach ($jsonInput['channel-group'] as $groupName => $value) {
                    $constructedGroups[$groupName] = PNAccessManagerChannelGroupData::fromJson($groupName, $value);
                }
            }
        }

        if (array_key_exists('channel-groups', $jsonInput)) {
            if (is_string($jsonInput['channel-groups'])) {
                $groupName = $jsonInput['channel-groups'];
                $constructedAuthKeys = [];

                foreach ($jsonInput['auths'] as $authKeyName => $value) {
                    $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
                }

                $constructedGroups[$groupName] = new PNAccessManagerChannelGroupData(
                    $groupName,
                    $constructedAuthKeys,
                    null,
                    null,
                    null,
                    $ttl
                );
            }

            if (PubNubUtil::isAssoc($jsonInput['channel-groups'])) {
                foreach ($jsonInput['channel-groups'] as $groupName => $value) {
                    $constructedGroups[$groupName] = PNAccessManagerChannelGroupData::fromJson($groupName, $value);
                }
            }
        }

        if (array_key_exists('channels', $jsonInput)) {
            foreach ($jsonInput['channels'] as $channelName => $value) {
                $constructedChannels[$channelName] = PNAccessManagerChannelData::fromJson($channelName, $value);
            }
        }

        return new static(
            $jsonInput['level'],
            $jsonInput['subscribe_key'],
            $constructedChannels,
            $constructedGroups,
            $ttl,
            $r,
            $w,
            $m
        );
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return string
     */
    public function getSubscribeKey()
    {
        return $this->subscribeKey;
    }

    /**
     * @return PNAccessManagerChannelData[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return PNAccessManagerChannelGroupData[]
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    /**
     * @return bool
     */
    public function isReadEnabled()
    {
        return $this->readEnabled;
    }

    /**
     * @return bool
     */
    public function isWriteEnabled()
    {
        return $this->writeEnabled;
    }

    /**
     * @return bool
     */
    public function isManageEnabled()
    {
        return $this->manageEnabled;
    }
}
