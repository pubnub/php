<?php

namespace PubNub\Models\Consumer\Objects\Channel;

class PNGetAllChannelMetadataResult
{
    /** @var integer */
    protected $totalCount;

    /** @var string */
    protected $prev;

    /** @var string */
    protected $next;

    /** @var array */
    protected $data;

    /** @var ?string */
    protected $eTag;

    /**
     * PNGetAllChannelMetadataResult constructor.
     * @param integer $totalCount
     * @param string $prev
     * @param string $next
     * @param array $data
     * @param $string $eTag
     */
    public function __construct($totalCount, $prev, $next, $data, ?string $eTag = null)
    {
        $this->totalCount = $totalCount;
        $this->prev = $prev;
        $this->next = $next;
        $this->data = $data;
        $this->eTag = $eTag;
    }

    /**
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return ?string
     */
    public function getETag(): ?string
    {
        return $this->eTag;
    }

    public function __toString()
    {
        if (!empty($data)) {
            $data_string = json_encode($data);
        }

        return sprintf(
            "totalCount: %s, prev: %s, next: %s, data: %s, eTag: %s",
            $this->totalCount,
            $this->prev,
            $this->next,
            $data_string,
            $this->eTag
        );
    }

    /**
     * @param array $payload
     * @return PNGetAllChannelMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $totalCount = null;
        $prev = null;
        $next = null;
        $data = null;
        $eTag = null;

        if (array_key_exists("totalCount", $payload)) {
            $totalCount = $payload["totalCount"];
        }

        if (array_key_exists("prev", $payload)) {
            $prev = $payload["prev"];
        }

        if (array_key_exists("next", $payload)) {
            $next = $payload["next"];
        }

        if (array_key_exists("data", $payload)) {
            $data = [];

            foreach ($payload["data"] as $value) {
                array_push($data, PNGetChannelMetadataResult::fromPayload([ "data" => $value ]));
            }
        }

        if (array_key_exists("eTag", $payload)) {
            $eTag = $payload["eTag"];
        }

        return new PNGetAllChannelMetadataResult($totalCount, $prev, $next, $data, $eTag);
    }
}
