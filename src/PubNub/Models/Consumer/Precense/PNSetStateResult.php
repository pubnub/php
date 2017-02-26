<?php

class PNSetStateResult
{
    protected $state;

    public function __construct($state)
    {
        $this->state = $state;
    }

    public function __toString()
    {
        return sprintf("New state %s successfully set", $this->state);
    }
}