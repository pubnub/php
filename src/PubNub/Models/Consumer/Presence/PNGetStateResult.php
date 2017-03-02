<?php

class PNGetStateResult
{
    protected $channels;

    public function __construct($channels)
    {
        $this->channels = $channels;
    }

    public function __toString()
    {
        return sprintf("Current state is %s", $this->channels);
    }
}