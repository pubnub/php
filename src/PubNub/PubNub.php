<?php

namespace PubNub;

use Monolog\Logger;
use PHPUnit\Framework\Error\Deprecated;
use PubNub\Builders\SubscribeBuilder;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Access\Audit;
use PubNub\Endpoints\Access\Grant;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Endpoints\Access\Revoke;
use PubNub\Endpoints\Access\RevokeToken;
use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Endpoints\History;
use PubNub\Endpoints\HistoryDelete;
use PubNub\Endpoints\MessageCount;
use PubNub\Endpoints\MessageActions\AddMessageAction;
use PubNub\Endpoints\MessageActions\GetMessageActions;
use PubNub\Endpoints\MessageActions\RemoveMessageAction;
use PubNub\Endpoints\MessagePersistance\FetchMessages;
use PubNub\Endpoints\Objects\Channel\SetChannelMetadata;
use PubNub\Endpoints\Objects\Channel\GetChannelMetadata;
use PubNub\Endpoints\Objects\Channel\GetAllChannelMetadata;
use PubNub\Endpoints\Objects\Channel\RemoveChannelMetadata;
use PubNub\Endpoints\Objects\UUID\SetUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\GetUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\GetAllUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\RemoveUUIDMetadata;
use PubNub\Endpoints\Objects\Member\SetMembers;
use PubNub\Endpoints\Objects\Member\GetMembers;
use PubNub\Endpoints\Objects\Member\RemoveMembers;
use PubNub\Endpoints\Objects\Membership\SetMemberships;
use PubNub\Endpoints\Objects\Membership\GetMemberships;
use PubNub\Endpoints\Objects\Membership\RemoveMemberships;
use PubNub\Endpoints\Presence\GetState;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\Endpoints\Presence\SetState;
use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\PubSub\Signal;
use PubNub\Endpoints\PubSub\Fire;
use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Endpoints\Time;
use PubNub\Exceptions\PubNubConfigurationException;
use PubNub\Managers\BasePathManager;
use PubNub\Managers\SubscriptionManager;
use PubNub\Managers\TelemetryManager;
use PubNub\Managers\TokenManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use PubNub\Endpoints\FileSharing\{SendFile, DeleteFile, DownloadFile, GetFileDownloadUrl, ListFiles};
use PubNub\Endpoints\MessageActions\GetMessageAction;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;

class PubNub implements LoggerAwareInterface
{
    protected const SDK_VERSION = "7.2.0";
    protected const SDK_NAME = "PubNub-PHP";

    public static $MAX_SEQUENCE = 65535;

    protected PNConfiguration $configuration;

    protected BasePathManager $basePathManager;

    protected SubscriptionManager $subscriptionManager;

    protected TelemetryManager $telemetryManager;

    protected TokenManager $tokenManager;

    protected LoggerInterface $logger;

    protected int $nextSequence = 0;

    protected ?CryptoModule $cryptoModule = null;

    /**
     * PNConfiguration constructor.
     *
     * @param $config PNConfiguration
     */
    public function __construct(PNConfiguration $config)
    {
        $this->validateConfig($config);
        $config->lock();
        $this->configuration = $config;
        $this->basePathManager = new BasePathManager($config);
        $this->subscriptionManager = new SubscriptionManager($this);
        $this->telemetryManager = new TelemetryManager();
        $this->tokenManager = new TokenManager();
        $this->logger = new NullLogger();
    }

    /**
     * Pre-configured PubNub client with demo-keys
     * @return static
     */
    public static function demo(): static
    {
        return new PubNub(PNConfiguration::demoKeys());
    }

    /**
     * @param $configuration PNConfiguration
     *
     * @throws PubNubConfigurationException
     */
    private function validateConfig(PNConfiguration $configuration): void
    {
        if (empty($configuration->getUuid())) {
            throw new PubNubConfigurationException('UUID should not be empty');
        }
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener(SubscribeCallback $listener): void
    {
        $this->subscriptionManager->addListener($listener);
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener(SubscribeCallback $listener): void
    {
        $this->subscriptionManager->removeListener($listener);
    }

    /**
     * @return Publish
     */
    public function publish(): Publish
    {
        return new Publish($this);
    }

    /**
     * @return Fire
     */
    public function fire(): Fire
    {
        return new Fire($this);
    }

    /**
     * @return Signal
     */
    public function signal(): Signal
    {
        return new Signal($this);
    }

    /**
     * @return SubscribeBuilder
     */
    public function subscribe(): SubscribeBuilder
    {
        return new SubscribeBuilder($this->subscriptionManager);
    }

    /**
     * @return History
     */
    public function history(): History
    {
        return new History($this);
    }

    /**
     * @return HereNow
     */
    public function hereNow(): HereNow
    {
        return new HereNow($this);
    }

    /**
     * @return WhereNow
     */
    public function whereNow(): WhereNow
    {
        return new WhereNow($this);
    }

    /**
     * @return Grant
     */
    public function grant(): Grant
    {
        return new Grant($this);
    }

    /**
     * @return PNAccessManagerTokenResult
     * @throws PubNubTokenParseException
     */
    public function parseToken($token): PNAccessManagerTokenResult
    {
        return (new GrantToken($this))->parseToken($token);
    }

    /**
     * @return GrantToken
     */
    public function grantToken(): GrantToken
    {
        return new GrantToken($this);
    }

    /**
     * @return RevokeToken
     */
    public function revokeToken(): RevokeToken
    {
        return new RevokeToken($this);
    }

    /**
     * @return Audit
     */
    public function audit(): Audit
    {
        return new Audit($this);
    }

    /**
     * @return Revoke
     */
    public function revoke(): Revoke
    {
        return new Revoke($this);
    }

    /**
     * @return AddChannelToChannelGroup
     */
    public function addChannelToChannelGroup(): AddChannelToChannelGroup
    {
        return new AddChannelToChannelGroup($this);
    }

    /**
     * @return RemoveChannelFromChannelGroup
     */
    public function removeChannelFromChannelGroup(): RemoveChannelFromChannelGroup
    {
        return new RemoveChannelFromChannelGroup($this);
    }

    /**
     * @return RemoveChannelGroup
     */
    public function removeChannelGroup(): RemoveChannelGroup
    {
        return new RemoveChannelGroup($this);
    }

    /**
     * @return ListChannelsInChannelGroup
     */
    public function listChannelsInChannelGroup(): ListChannelsInChannelGroup
    {
        return new ListChannelsInChannelGroup($this);
    }

    /**
     * @return Time
     */
    public function time(): Time
    {
        return new Time($this);
    }

    /**
     * @return AddChannelsToPush
     */
    public function addChannelsToPush(): AddChannelsToPush
    {
        return new AddChannelsToPush($this);
    }

    /**
     * @return RemoveChannelsFromPush
     */
    public function removeChannelsFromPush(): RemoveChannelsFromPush
    {
        return new RemoveChannelsFromPush($this);
    }

    /**
     * @return RemoveDeviceFromPush
     */
    public function removeAllPushChannelsForDevice(): RemoveDeviceFromPush
    {
        return new RemoveDeviceFromPush($this);
    }

    /**
     * @return ListPushProvisions
     */
    public function listPushProvisions(): ListPushProvisions
    {
        return new ListPushProvisions($this);
    }

    /**
     * @return SetChannelMetadata
     */
    public function setChannelMetadata(): SetChannelMetadata
    {
        return new SetChannelMetadata($this);
    }

    /**
     * @return GetChannelMetadata
     */
    public function getChannelMetadata(): GetChannelMetadata
    {
        return new GetChannelMetadata($this);
    }

    /**
     * @return GetAllChannelMetadata
     */
    public function getAllChannelMetadata(): GetAllChannelMetadata
    {
        return new GetAllChannelMetadata($this);
    }

    /**
     * @return RemoveChannelMetadata
     */
    public function removeChannelMetadata(): RemoveChannelMetadata
    {
        return new RemoveChannelMetadata($this);
    }

    /**
     * @return SetUUIDMetadata
     */
    public function setUUIDMetadata(): SetUUIDMetadata
    {
        return new SetUUIDMetadata($this);
    }

    /**
     * @return GetUUIDMetadata
     */
    public function getUUIDMetadata(): GetUUIDMetadata
    {
        return new GetUUIDMetadata($this);
    }

    /**
     * @return GetAllUUIDMetadata
     */
    public function getAllUUIDMetadata(): GetAllUUIDMetadata
    {
        return new GetAllUUIDMetadata($this);
    }

    /**
     * @return RemoveUUIDMetadata
     */
    public function removeUUIDMetadata(): RemoveUUIDMetadata
    {
        return new RemoveUUIDMetadata($this);
    }

    /**
     * @return GetMembers
     */
    public function getMembers(): GetMembers
    {
        return new GetMembers($this);
    }

    /**
     * @return SetMembers
     */
    public function setMembers(): SetMembers
    {
        return new SetMembers($this);
    }

    /**
     * @return RemoveMembers
     */
    public function removeMembers(): RemoveMembers
    {
        return new RemoveMembers($this);
    }

    /**
     * @return GetMemberships
     */
    public function getMemberships(): GetMemberships
    {
        return new GetMemberships($this);
    }

    /**
     * @return SetMemberships
     */
    public function setMemberships(): SetMemberships
    {
        return new SetMemberships($this);
    }

    /**
     * @return RemoveMemberships
     */
    public function removeMemberships(): RemoveMemberships
    {
        return new RemoveMemberships($this);
    }

    /**
     * @return int
     */
    public function timestamp(): int
    {
        return time();
    }

    /**
     * @return string
     */
    public static function getSdkVersion(): string
    {
        return static::SDK_VERSION;
    }

    /**
     * @return string
     */
    public static function getSdkName(): string
    {
        return static::SDK_NAME;
    }

    /**
     * @return string
     */
    public static function getSdkFullName(): string
    {
        $fullName = static::SDK_NAME . "/" . static::SDK_VERSION;

        return $fullName;
    }

    /**
     * Get PubNub configuration object
     *
     * @return PNConfiguration
     */
    public function getConfiguration(): PNConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return string Base path
     */
    public function getBasePath($customHost = null): string
    {
        return $this->basePathManager->getBasePath($customHost);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return GetState
     */
    public function getState(): GetState
    {
        return new GetState($this);
    }

    /**
     * @return SetState
     */
    public function setState(): SetState
    {
        return new SetState($this);
    }

    /**
     * @return HistoryDelete
     */
    public function deleteMessages(): HistoryDelete
    {
        return new HistoryDelete($this);
    }

    /**
     * @return MessageCount
     */
    public function messageCounts(): MessageCount
    {
        return new MessageCount($this);
    }

    /**
     * @return TelemetryManager
     */
    public function getTelemetryManager(): TelemetryManager
    {
        return $this->telemetryManager;
    }

    /**
     * @return int unique sequence identifier
     */
    public function getSequenceId(): int
    {
        if (static::$MAX_SEQUENCE === $this->nextSequence) {
            $this->nextSequence = 1;
        } else {
            $this->nextSequence += 1;
        }

        return $this->nextSequence;
    }

    /**
     * @return string Token previously set by $this->setToken
     */
    public function getToken(): ?string
    {
        return $this->tokenManager->getToken();
    }

    /**
     * @param string $token Token obtained by GetToken
     */
    public function setToken(string $token)
    {
        return $this->tokenManager->setToken($token);
    }

    public function getCrypto(): ?CryptoModule
    {
        if ($this->cryptoModule) {
            return $this->cryptoModule;
        } else {
            return $this->configuration->getCryptoSafe();
        }
    }

    public function isCryptoEnabled(): bool
    {
        return !empty($this->cryptoModule) || !empty($this->configuration->getCryptoSafe());
    }

    public function setCrypto(CryptoModule $cryptoModule)
    {
        $this->cryptoModule = $cryptoModule;
    }

    public function fetchMessages(): FetchMessages
    {
        return new FetchMessages($this);
    }

    public function sendFile(): SendFile
    {
        return new SendFile($this);
    }

    public function deleteFile(): DeleteFile
    {
        return new DeleteFile($this);
    }

    public function downloadFile(): DownloadFile
    {
        return new DownloadFile($this);
    }

    public function listFiles(): ListFiles
    {
        return new ListFiles($this);
    }

    public function getFileDownloadUrl(): GetFileDownloadUrl
    {
        return new GetFileDownloadUrl($this);
    }

    public function addMessageAction(): AddMessageAction
    {
        return new AddMessageAction($this);
    }

    // TODO: Remove in 8.0.0
    public function getMessageAction(): GetMessageAction
    {
        trigger_error("This method is deprecated. Use getMessageActions()", E_USER_DEPRECATED);
        return new GetMessageAction($this);
    }

    public function getMessageActions(): GetMessageActions
    {
        return new GetMessageActions($this);
    }

    public function removeMessageAction(): RemoveMessageAction
    {
        return new RemoveMessageAction($this);
    }
}
