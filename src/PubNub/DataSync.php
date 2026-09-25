<?php

namespace PubNub;

use PubNub\Endpoints\DataSync\Channel\CreateChannel;
use PubNub\Endpoints\DataSync\Channel\DeleteChannel;
use PubNub\Endpoints\DataSync\Channel\GetChannel;
use PubNub\Endpoints\DataSync\Channel\GetChannels;
use PubNub\Endpoints\DataSync\Channel\SetChannel;
use PubNub\Endpoints\DataSync\Channel\UpdateChannel;
use PubNub\Endpoints\DataSync\Entity\CreateEntity;
use PubNub\Endpoints\DataSync\Entity\DeleteEntity;
use PubNub\Endpoints\DataSync\Entity\GetEntities;
use PubNub\Endpoints\DataSync\Entity\GetEntity;
use PubNub\Endpoints\DataSync\Entity\SetEntity;
use PubNub\Endpoints\DataSync\Entity\UpdateEntity;
use PubNub\Endpoints\DataSync\Membership\CreateMembership;
use PubNub\Endpoints\DataSync\Membership\DeleteMembership;
use PubNub\Endpoints\DataSync\Membership\GetMembership;
use PubNub\Endpoints\DataSync\Membership\GetMemberships;
use PubNub\Endpoints\DataSync\Membership\SetMembership;
use PubNub\Endpoints\DataSync\Membership\UpdateMembership;
use PubNub\Endpoints\DataSync\Relationship\CreateRelationship;
use PubNub\Endpoints\DataSync\Relationship\DeleteRelationship;
use PubNub\Endpoints\DataSync\Relationship\GetRelationship;
use PubNub\Endpoints\DataSync\Relationship\GetRelationships;
use PubNub\Endpoints\DataSync\Relationship\SetRelationship;
use PubNub\Endpoints\DataSync\Relationship\UpdateRelationship;
use PubNub\Endpoints\DataSync\User\CreateUser;
use PubNub\Endpoints\DataSync\User\DeleteUser;
use PubNub\Endpoints\DataSync\User\GetUser;
use PubNub\Endpoints\DataSync\User\GetUsers;
use PubNub\Endpoints\DataSync\User\SetUser;
use PubNub\Endpoints\DataSync\User\UpdateUser;

/**
 * Entry point for the DataSync operations, reached through $pubnub->dataSync().
 *
 * Every method hands back a fresh fluent builder, so a call reads as one chain:
 *
 *     $entity = $pubnub->dataSync()
 *         ->createEntity()
 *         ->entityClass('vehicle')
 *         ->entityClassVersion(1)
 *         ->payload(['make' => 'Toyota'])
 *         ->sync();
 *
 * Use sync() to get the typed result and have failures thrown, or envelope() to get the result
 * and the status side by side without exceptions.
 */
class DataSync
{
    protected PubNub $pubnub;

    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
    }

    public function createEntity(): CreateEntity
    {
        return new CreateEntity($this->pubnub);
    }

    public function getEntity(): GetEntity
    {
        return new GetEntity($this->pubnub);
    }

    public function getEntities(): GetEntities
    {
        return new GetEntities($this->pubnub);
    }

    public function setEntity(): SetEntity
    {
        return new SetEntity($this->pubnub);
    }

    public function updateEntity(): UpdateEntity
    {
        return new UpdateEntity($this->pubnub);
    }

    public function deleteEntity(): DeleteEntity
    {
        return new DeleteEntity($this->pubnub);
    }

    public function createRelationship(): CreateRelationship
    {
        return new CreateRelationship($this->pubnub);
    }

    public function getRelationship(): GetRelationship
    {
        return new GetRelationship($this->pubnub);
    }

    public function getRelationships(): GetRelationships
    {
        return new GetRelationships($this->pubnub);
    }

    public function setRelationship(): SetRelationship
    {
        return new SetRelationship($this->pubnub);
    }

    public function updateRelationship(): UpdateRelationship
    {
        return new UpdateRelationship($this->pubnub);
    }

    public function deleteRelationship(): DeleteRelationship
    {
        return new DeleteRelationship($this->pubnub);
    }

    public function createUser(): CreateUser
    {
        return new CreateUser($this->pubnub);
    }

    public function getUser(): GetUser
    {
        return new GetUser($this->pubnub);
    }

    public function getUsers(): GetUsers
    {
        return new GetUsers($this->pubnub);
    }

    public function setUser(): SetUser
    {
        return new SetUser($this->pubnub);
    }

    public function updateUser(): UpdateUser
    {
        return new UpdateUser($this->pubnub);
    }

    public function deleteUser(): DeleteUser
    {
        return new DeleteUser($this->pubnub);
    }

    public function createChannel(): CreateChannel
    {
        return new CreateChannel($this->pubnub);
    }

    public function getChannel(): GetChannel
    {
        return new GetChannel($this->pubnub);
    }

    public function getChannels(): GetChannels
    {
        return new GetChannels($this->pubnub);
    }

    public function setChannel(): SetChannel
    {
        return new SetChannel($this->pubnub);
    }

    public function updateChannel(): UpdateChannel
    {
        return new UpdateChannel($this->pubnub);
    }

    public function deleteChannel(): DeleteChannel
    {
        return new DeleteChannel($this->pubnub);
    }

    public function createMembership(): CreateMembership
    {
        return new CreateMembership($this->pubnub);
    }

    public function getMembership(): GetMembership
    {
        return new GetMembership($this->pubnub);
    }

    public function getMemberships(): GetMemberships
    {
        return new GetMemberships($this->pubnub);
    }

    public function setMembership(): SetMembership
    {
        return new SetMembership($this->pubnub);
    }

    public function updateMembership(): UpdateMembership
    {
        return new UpdateMembership($this->pubnub);
    }

    public function deleteMembership(): DeleteMembership
    {
        return new DeleteMembership($this->pubnub);
    }
}
