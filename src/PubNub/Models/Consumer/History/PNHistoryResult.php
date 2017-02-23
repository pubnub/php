<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubUtil;

class PNHistoryResult
{
    private $messages;
    private $startTimetoken;
    private $endTimetoken;

    public function __construct($messages, $startTimetoken, $endTimetoken)
    {
        $this->messages = $messages;
        $this->startTimetoken = $startTimetoken;
        $this->endTimetoken = $endTimetoken;
    }

    public function __toString()
    {
        return sprintf("History result for range %s..%s", $this->startTimetoken, $this->endTimetoken);
    }

    public static function fromJson($jsonInput, $crypto, $includeTTOption = false, $cipher = null)
    {
        $rawItems = $jsonInput[0];
        $startTimetoken = $jsonInput[1];
        $endTimetoken = $jsonInput[2];
        $messages = [];

        foreach ($rawItems as $item) {
            if (PubNubUtil::isAssoc($item) && in_array('timetoken', $item)
                                           && in_array('message', $item)
                                           && $includeTTOption) {
                $message = new PNHistoryItemResult($item['message'], $crypto, $item['timetoken']);
            } else {
                $message = new PNHistoryItemResult($item, $crypto);
            }

            if ($cipher !== null) {
                $message->decrypt($cipher);
            }

            array_push($messages, $message);
        }

        return new PNHistoryResult(
            $messages,
            $startTimetoken,
            $endTimetoken
        );
    }
}