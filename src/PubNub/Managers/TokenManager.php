<?php

namespace PubNub\Managers;

class TokenManager
{
    private ?string $token = null;

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }
}
