<?php

namespace PubNub\Endpoints\Objects\Member;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNub\PubNubUtil;
use PubNub\PubNub;

class SetMembers extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/channels/%s/uuids";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNSetMembersOperation;
    protected string $endpointName = "SetMembers";

    /** @var string */
    protected $channel;

    /** @var array */
    protected $uuids;

    /** @var array */
    protected $custom;

    /** @var array */
    protected $include = [];

    /** @var PNMemberIncludes */
    protected PNMemberIncludes $includes;

    /** @var PNChannelMember[] */
    protected array $members;

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
     * @param string $ch
     * @return $this
     */
    public function channel($ch)
    {
        $this->channel = $ch;

        return $this;
    }

    /**
     * @param array $uuids
     * @deprecated Use members() method
     *
     * @return $this
     */
    public function uuids($uuids)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * @param PNChannelMember[] $members
     * @return $this
     */
    public function members(array $members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * @param array $custom
     * @deprecated Use members() method
     * @return $this
     */
    public function custom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Defines a list of fields to be included in response. It takes an instance of PNMemberIncludes.
     *
     * Example:
     *
     * $includes = (new PNMemberIncludes())->custom()->status()->totalCount()->type()-user();
     * $pnGetMembers->include($includes);
     *
     * @param PNMemberIncludes $includes
     * @return $this
     */
    public function include(PNMemberIncludes $includes): self
    {
        $this->includes = $includes;
        return $this;
    }

    /**
     * @param array $include
     * @deprecated Use include() method
     * @return $this
     */
    public function includeFields($include)
    {
        $this->include = $include;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_string($this->channel)) {
            throw new PubNubValidationException("channel missing");
        }

        if (!empty($this->members) and !empty($this->uuids)) {
            throw new PubNubValidationException("Either members or uuids should be provided");
        }

        if (empty($this->uuids) and empty($this->members)) {
            throw new PubNubValidationException("Members or a list of uuids missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        $entries = [];
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                array_push($entries, $member->toArray());
            }
        } else {
            foreach ($this->uuids as $value) {
                $entry = [
                    "uuid" => [
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
            $this->channel
        );
    }

    public function sync(): PNMembersResult
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNMembersResult
     */
    protected function createResponse($result): PNMembersResult
    {
        return PNMembersResult::fromPayload($result);
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        if (!empty($this->includes)) {
            $params['include'] = (string)$this->includes;
        } else {
            if (count($this->include) > 0) {
                $includes = [];

                if (array_key_exists("customFields", $this->include)) {
                    array_push($includes, 'custom');
                }

                if (array_key_exists("customUUIDFields", $this->include)) {
                    array_push($includes, 'uuid.custom');
                }

                if (array_key_exists("UUIDFields", $this->include)) {
                    array_push($includes, 'uuid');
                }

                $includesString = implode(",", $includes);

                if (strlen($includesString) > 0) {
                    $params['include'] = $includesString;
                }
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
