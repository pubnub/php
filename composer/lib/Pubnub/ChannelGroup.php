<?php
 
namespace Pubnub;

class ChannelGroup
{
    public $group;
    public $namespace;

    public function __construct($name)
    {
        $parts = explode(":", $name);

        switch (count($parts)) {
            case 2:
                $this->setNamespace($parts[0]);
                $this->setGroup($parts[1]);
                break;
            case 1:
                $this->setGroup($name);
                break;
            default:
                throw new PubnubException("Invalid channel group string");
        }
    }

    public function setGroup($groupName)
    {
        if ($groupName != null && !empty($groupName))
            $this->group = $groupName;
    }

    public function setNamespace($namespaceName)
    {
        if ($namespaceName != null && !empty($namespaceName))
            $this->namespace = $namespaceName;
    }
}