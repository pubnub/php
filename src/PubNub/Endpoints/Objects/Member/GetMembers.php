<?php

namespace PubNub\Endpoints\Objects\Member;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNub\PubNub;

class GetMembers extends ObjectsCollectionEndpoint
{
    protected const PATH = "/v2/objects/%s/channels/%s/uuids";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNGetMembersOperation;
    protected string $endpointName = "GetMembers";

    /** @var string */
    protected ?string $channel;

    /** @var array */
    protected array $include = [];

    /** @var PNMemberIncludes */
    protected ?PNMemberIncludes $includes;

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
     * @param string $channel
     * @return $this
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function include(PNMemberIncludes $includes): self
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
     * @return void
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (empty($this->channel)) {
            throw new PubNubValidationException("channel missing");
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
