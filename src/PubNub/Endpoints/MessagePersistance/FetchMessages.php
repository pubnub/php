<?php

namespace PubNub\Endpoints\MessagePersistance;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesResult;
use PubNub\PubNubUtil;

class FetchMessages extends Endpoint
{
    protected const GET_PATH = "/v3/history%s/sub-key/%s/channel/%s";

    protected const SINGLE_CHANNEL_MAX_MESSAGES = 100;
    protected const DEFAULT_SINGLE_CHANNEL_MESSAGES = 100;

    protected const MULTIPLE_CHANNELS_MAX_MESSAGES = 25;
    protected const DEFAULT_MULTIPLE_CHANNELS_MESSAGES = 25;

    protected const MAX_MESSAGES_ACTIONS = 25;
    protected const DEFAULT_MESSAGES_ACTIONS = 25;

    protected array $channels;

    protected int $start;
    protected int $end;
    protected int $count;

    protected bool $includeMeta = false;
    protected bool $includeUuid = false;
    protected bool $includeMessageType = true;
    protected bool $includeMessageActions = false;
    protected bool $includeCustomMessageType = true;

    protected array $customParamMapping = [
        'start' => 'start',
        'end' => 'end',
        'count' => 'max',
        'includeMeta' => 'include_meta',
        'includeUuid' => 'include_uuid',
        'includeMessageType' => 'include_message_type',
        'includeCustomMessageType' => 'include_custom_message_type',
    ];

    public function channels(...$channel): self
    {
        if (is_array($channel[0])) {
            $this->channels = $channel[0];
        } elseif (strpos($channel[0], ',')) {
            $this->channels = array_map('trim', explode(',', $channel[0]));
        } else {
            $this->channels = $channel;
        }
        return $this;
    }

    public function start(int $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function end(int $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    public function includeMeta(bool $includeMeta): self
    {
        $this->includeMeta = $includeMeta;
        return $this;
    }

    public function includeUuid(bool $includeUuid): self
    {
        $this->includeUuid = $includeUuid;
        return $this;
    }

    public function includeMessageType(bool $includeMessageType): self
    {
        $this->includeMessageType = $includeMessageType;
        return $this;
    }

    public function includeCustomMessageType(bool $includeCustomMessageType): self
    {
        $this->includeCustomMessageType = $includeCustomMessageType;
        return $this;
    }

    public function includeMessageActions(bool $includeMessageActions): self
    {
        $this->includeMessageActions = $includeMessageActions;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if (!is_array($this->channels) || count($this->channels) === 0) {
            throw new PubNubValidationException("Channel Missing");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];
        foreach ($this->customParamMapping as $customParam => $requestParam) {
            // @phpstan-ignore-next-line
            if (isset($this->$customParam) && !is_null($this->$customParam)) {
                if (strpos($customParam, 'include') === 0) {
                    $params[$requestParam] = $this->$customParam ? 'true' : 'false';
                    continue;
                }
                $params[$requestParam] = $this->$customParam;
            }
        }

        return $params;
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        $withActions = $this->includeMessageActions ? '-with-actions' : '';
        $channelList = $this->includeMessageActions
            ? PubNubUtil::urlEncode($this->channels[0])
            : implode(',', array_map(fn($channel) => PubNubUtil::urlEncode($channel), $this->channels));

        return sprintf(
            self::GET_PATH,
            $withActions,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $channelList,
        );
    }

    public function sync(): PNFetchMessagesResult
    {
        return parent::sync();
    }

    /**
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json): PNFetchMessagesResult
    {
        return PNFetchMessagesResult::fromJson(
            $json,
            $this->pubnub->getCrypto(),
            isset($this->start) ? $this->start : null,
            isset($this->end) ? $this->end : null
        );
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    protected function buildData()
    {
        return null;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNFetchMessagesOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Fetch Messages";
    }
}
