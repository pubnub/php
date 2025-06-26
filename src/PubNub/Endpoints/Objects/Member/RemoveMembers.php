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

class RemoveMembers extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/channels/%s/uuids";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::PATCH;
    protected int $endpointOperationType = PNOperationType::PNRemoveMembersOperation;
    protected string $endpointName = "ManageMembers";

    /** @var string */
    protected ?string $channel;

    /** @var array */
    protected array $uuids;

    /** @var array */
    protected array $include = [];

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
    public function channel(string $ch): self
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
    public function uuids(array $uuids): self
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * @param PNChannelMember[] $members
     * @return $this
     */
    public function members(array $members): self
    {
        $this->members = $members;
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
    public function includeFields(array $include): self
    {
        $this->include = $include;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     * @return void
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (empty($this->channel)) {
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
     * @throws \PubNub\Exceptions\PubNubBuildRequestException
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
                array_push($entries, $entry);
            }
        }
        return PubNubUtil::writeValueAsString([
            "delete" => $entries
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

    /**
     * @param array $result Decoded json
     * @return PNMembersResult
     */
    protected function createResponse($result): PNMembersResult
    {
        return PNMembersResult::fromPayload($result);
    }

    public function sync(): PNMembersResult
    {
        return parent::sync();
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

            if (array_key_exists("customUUIDFields", $this->include)) {
                array_push($includes, 'uuid.custom');
            }

            if (array_key_exists("UUIDFields", $this->include)) {
                array_push($includes, 'uuid');
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
