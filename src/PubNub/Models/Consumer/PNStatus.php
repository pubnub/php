<?php

namespace PubNub\Models\Consumer;


class PNStatus
{
    /** @var  bool */
    private $error;

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }
}