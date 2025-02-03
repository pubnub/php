<?php

namespace PubNub\Models\Consumer\Objects\Member;

use PubNub\Models\Consumer\Objects\PNIncludes;

class PNMemberIncludes extends PNIncludes
{
    public bool $user = false;
    public bool $userId = false;
    public bool $userCustom = false;
    public bool $userType = false;
    public bool $userStatus = false;

    public function __construct()
    {
        $this->mapping = array_merge($this->mapping, [
            'user' => 'uuid',
            'userId' => 'uuid.id',
            'userCustom' => 'uuid.custom',
            'userType' => 'uuid.type',
            'userStatus' => 'uuid.status',
        ]);
    }

    public function user(bool $user = true): self
    {
        $this->user = $user;
        return $this;
    }

    public function userId(bool $userId = true): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function userCustom(bool $userCustom = true): self
    {
        $this->userCustom = $userCustom;
        return $this;
    }

    public function userType(bool $userType = true): self
    {
        $this->userType = $userType;
        return $this;
    }

    public function userStatus(bool $userStatus = true): self
    {
        $this->userStatus = $userStatus;
        return $this;
    }
}
