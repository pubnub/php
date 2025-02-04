<?php

namespace PubNub\Endpoints\Objects\Membership;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipIncludes;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResult;
use PubNub\PubNub;

class GetMemberships extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/uuids/%s/channels";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNGetMembershipsOperation;
    protected string $endpointName = "GetMemberships";

    /** @var string */
    protected ?string $userId;

    /** @var array */
    protected array $include = [];

    /** @var PNMembershipIncludes */
    protected ?PNMembershipIncludes $includes;

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
     * @param array $include
     * @return $this
     */
    public function includeFields(array $include): self
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

        if (empty($this->userId)) {
            throw new PubNubValidationException("uuid missing");
        }
    }

    /**
     * @return string
     * @throws \PubNub\Exceptions\PubNubBuildRequestException
     */
    protected function buildData()
    {
        return null;
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

            $params['sort'] = $sortEntries;
        }

        return $params;
    }
}
