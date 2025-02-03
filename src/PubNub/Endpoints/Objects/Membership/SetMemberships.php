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

class SetMemberships extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/uuids/%s/channels";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNSetMembershipsOperation;
    protected string $endpointName = "SetMemberships";

    /** @var string */
    protected $userId;

    /** @var array */
    protected $channels;

    /** @var array */
    protected $custom;

    /** @var array */
    protected $include = [];

    /** @var PNMembershipIncludes */
    protected ?PNMembershipIncludes $includes;

    /** @var PNChannelMembership[] */
    protected array $memberships;

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
    public function uuid($uuid): self
    {
        $this->userId = $uuid;
        return $this;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function userId($userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param array $channels
     * @deprecated Use memberships() method
     *
     * @return $this
     */
    public function channels($channels): self
    {
        $this->channels = $channels;
        return $this;
    }

    /**
     * @param PNChannelMembership[] $memberships
     * @return $this
     */
    public function memberships(array $memberships): self
    {
        $this->memberships = $memberships;
        return $this;
    }

    /**
     * @param array $custom
     * @deprecated Use members() method
     *
     * @return $this
     */
    public function custom($custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * @param array $include
     * @deprecated Use includes() method
     *
     * @return $this
     */
    public function includeFields($include): self
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
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_string($this->userId)) {
            throw new PubNubValidationException("uuid missing");
        }

        if (!empty($this->channels) and !empty($this->memberships)) {
            throw new PubNubValidationException("Either memberships or channels should be provided");
        }

        if (empty($this->channels) and empty($this->memberships)) {
            throw new PubNubValidationException("Memberships or a list of channels missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        $entries = [];
        if (!empty($this->memberships)) {
            foreach ($this->memberships as $memberhip) {
                array_push($entries, $memberhip->toArray());
            }
        } elseif (!empty($this->channels)) {
            foreach ($this->channels as $value) {
                $entry = [
                    "channel" => [
                        "id" => $value,
                    ]
                ];
                if (!empty($this->custom)) {
                    $entry["custom"] = $this->custom;
                }

                array_push($entries, $entry);
            }
        }

        return PubNubUtil::writeValueAsString([
            "set" => $entries
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
     * @param array $result Decoded json
     * @return PNMembershipsResult
     */
    protected function createResponse($result): PNMembershipsResult
    {
        return PNMembershipsResult::fromPayload($result);
    }

    /**
     * @return array
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

            $includesString = implode(",", $includes);

            if (strlen($includesString) > 0) {
                $params['include'] = $includesString;
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

            $params['sort'] = $sortEntries;
        }

        return $params;
    }
}
