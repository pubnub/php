<?php

namespace PubNub\Endpoints\Objects\Membership;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipIncludes;
use PubNub\Models\Consumer\Objects\Membership\PNChannelMembership;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResult;
use PubNub\PubNubUtil;
use PubNub\PubNub;

class ManageMemberships extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/uuids/%s/channels";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNManageMembershipsOperation;
    protected string $endpointName = "ManageMemberships";

    /** @var string */
    protected ?string $userId;

    /** @var string[] */
    protected array $setChannels;

    /** @var string[] */
    protected array $removeChannels;

    /** @var string[] */
    protected array $custom;

    /** @var string[] */
    protected array $include = [];

    /** @var PNMembershipIncludes */
    protected ?PNMembershipIncludes $includes;

    /** @var ?PNChannelMembership[] */
    protected ?array $setMemberships;

    /** @var ?PNChannelMembership[] */
    protected ?array $removeMemberships;

    /**
     * @param PubNub $pubnubInstance
     */
    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->endpointConnectTimeout = $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
        $this->endpointRequestTimeout = $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function uuid(string $uuid): self
    {
        $this->userId = $uuid;
        return $this;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function userId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param mixed $channels
     * @deprecated Use memberships() method
     *
     * @return $this
     */
    public function setChannels(mixed $channels): self
    {
        $this->setChannels = $channels;
        return $this;
    }

    /**
     * @param mixed $channels
     * @deprecated Use memberships() method
     *
     * @return $this
     */
    public function removeChannels(mixed $channels): self
    {
        $this->removeChannels = $channels;
        return $this;
    }

    /**
     * @param PNChannelMembership[] $memberships
     * @return $this
     */
    public function setMemberships(array $memberships): self
    {
        $this->setMemberships = $memberships;
        return $this;
    }

    /**
     * @param PNChannelMembership[] $memberships
     * @return $this
     */
    public function removeMemberships(array $memberships): self
    {
        $this->removeMemberships = $memberships;
        return $this;
    }

    /**
     * @param mixed $custom
     * @deprecated Use members() method
     *
     * @return $this
     */
    public function custom(mixed $custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * @param string[] $include
     * @deprecated Use includes() method
     *
     * @return $this
     */
    public function includeFields(array $include): self
    {
        $this->include = $include;
        return $this;
    }

    /**
     * Defines a list of fields to be included in response. It takes an instance of PNMemberIncludes.
     *
     * Example:
     *
     * $includes = (new PNMembershipIncludes())->custom()->status()->totalCount()->type()-user();
     * $pnGetMembers->include($includes);
     *
     * @param PNMembershipIncludes $includes
     * @return $this
     */
    public function include(PNMembershipIncludes $includes): self
    {
        $this->includes = $includes;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     * @return void
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (empty($this->userId)) {
            throw new PubNubValidationException("uuid missing");
        }

        $memberships = !empty($this->setMemberships) or !empty($this->removeMemberships);
        $channels = !empty($this->setChannels) or !empty($this->removeChannels);

        if ($memberships and $channels) {
            throw new PubNubValidationException("Either memberships or channels should be provided");
        }

        if (!$memberships and !$channels) {
            throw new PubNubValidationException("Memberships or a list of channels missing");
        }
    }

    /**
     * @return string
     * @throws \PubNub\Exceptions\PubNubBuildRequestException
     */
    protected function buildData()
    {
        $set = [];
        if (!empty($this->setMemberships)) {
            foreach ($this->setMemberships as $memberhip) {
                array_push($set, $memberhip->toArray());
            }
        } else {
            foreach ($this->setChannels as $value) {
                array_push($set, ["channel" => ["id" => $value], "custom" => $this->custom]);
            }
        }

        $remove = [];
        if (!empty($this->removeMemberships)) {
            foreach ($this->removeMemberships as $memberhip) {
                array_push($remove, $memberhip->toArray());
            }
        } else {
            foreach ($this->removeChannels as $value) {
                array_push($remove, ["channel" => ["id" => $value]]);
            }
        }

        return PubNubUtil::writeValueAsString([
            "set" => $set,
            "delete" => $remove
        ]);
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->userId
        );
    }

    public function sync(): PNMembershipsResult
    {
        return parent::sync();
    }

    /**
     * @param mixed $result Decoded json
     * @return PNMembershipsResult
     */
    protected function createResponse($result): PNMembershipsResult
    {
        return PNMembershipsResult::fromPayload($result);
    }

    /**
     * @return string[]
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        if (!empty($this->includes)) {
            $params['include'] = (string)$this->includes;
        } elseif (count($this->include) > 0) {
            $includes = [];

            if (array_key_exists("customFields", $this->include)) {
                array_push($includes, 'custom');
            }

            if (array_key_exists("customChannelFields", $this->include)) {
                array_push($includes, 'channel.custom');
            }

            if (array_key_exists("channelFields", $this->include)) {
                array_push($includes, 'channel');
            }

            if (!empty($includes)) {
                $params['include'] = implode(",", $includes);
            }
        }

        if (array_key_exists("totalCount", $this->include)) {
            $params['count'] = "true";
        }

        if (array_key_exists("next", $this->page)) {
            $params['start'] = $this->page["next"];
        }

        if (array_key_exists("prev", $this->page)) {
            $params['end'] = $this->page["prev"];
        }

        if (!empty($this->filter)) {
            $params['filter'] = $this->filter;
        }

        if (!empty($this->limit)) {
            $params['limit'] = $this->limit;
        }

        if (!empty($this->sort)) {
            $sortEntries = [];

            foreach ($this->sort as $key => $value) {
                if ($value === 'asc' || $value === 'desc') {
                    array_push($sortEntries, "$key:$value");
                } else {
                    array_push($sortEntries, $key);
                }
            }

            $params['sort'] = join(",", $sortEntries);
        }

        return $params;
    }
}
