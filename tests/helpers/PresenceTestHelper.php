<?php

namespace PubNubTests\Helpers;

/**
 * Trait to help with presence testing by spawning background subscribers
 */
trait PresenceTestHelper
{
    /** @var array<mixed> */
    private array $backgroundProcesses = [];

    /**
     * Subscribe multiple clients to a channel in the background
     *
     * @param string $channel Channel to subscribe to
     * @param int $clientCount Number of clients to subscribe
     * @param int $duration How long to keep subscribed (seconds)
     * @param string $uuidPrefix Prefix for generated UUIDs
     * @return array<string> Array of UUIDs that were subscribed
     */
    protected function subscribeBackgroundClients(
        string $channel,
        int $clientCount,
        int $duration = 30,
        string $uuidPrefix = "test-user-"
    ): array {
        $uuids = [];
        $scriptPath = __DIR__ . '/PresenceSubscriber.php';

        for ($i = 0; $i < $clientCount; $i++) {
            $uuid = $uuidPrefix . uniqid() . "-" . $i;
            $uuids[] = $uuid;

            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            // Use PHP_BINARY to get full path to PHP executable
            // This ensures the command works even when PATH is not set in child process
            $cmd = sprintf(
                '%s %s %s %s %d',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($scriptPath),
                escapeshellarg($channel),
                escapeshellarg($uuid),
                $duration
            );

            // Build environment: start with current OS environment, then ensure PubNub keys are set
            // bootstrap.php calls putenv() for .env variables, making them available to getenv()
            $env = getenv(); // Get all current OS environment variables
            $env['PUBLISH_KEY'] = getenv('PUBLISH_KEY') ?: '';
            $env['SUBSCRIBE_KEY'] = getenv('SUBSCRIBE_KEY') ?: '';
            $env['SECRET_KEY'] = getenv('SECRET_KEY') ?: '';

            $process = proc_open($cmd, $descriptorspec, $pipes, null, $env);

            if (is_resource($process)) {
                // Store process info for cleanup
                $this->backgroundProcesses[] = [
                    'process' => $process,
                    'pipes' => $pipes,
                    'uuid' => $uuid
                ];

                // Make pipes non-blocking
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);
            }
        }

        // Wait for background processes to start and establish subscriptions
        // Similar to Kotlin's Thread.sleep(2000) approach
        sleep(2);

        return $uuids;
    }

    /**
     * Wait for background clients to connect
     *
     * @param int $seconds Seconds to wait
     */
    protected function waitForPresence(int $seconds = 2): void
    {
        sleep($seconds);
    }

    /**
     * Wait for presence to propagate by checking hereNow
     *
     * @param \PubNub\PubNub $pubnub PubNub instance
     * @param string|array<string> $channels Channel(s) to check
     * @param int $expectedOccupancy Expected occupancy count
     * @param int $maxAttempts Maximum retry attempts
     * @param int $delaySeconds Delay between attempts
     * @return bool True if expected occupancy reached
     */
    protected function waitForOccupancy(
        $pubnub,
        string|array $channels,
        int $expectedOccupancy,
        int $maxAttempts = 10,
        int $delaySeconds = 1
    ): bool {
        $channels = is_array($channels) ? $channels : [$channels];

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                $result = $pubnub->hereNow()
                    ->channels($channels)
                    ->includeUuids(false)
                    ->sync();

                $totalOccupancy = $result->getTotalOccupancy();

                if ($totalOccupancy >= $expectedOccupancy) {
                    return true;
                }
            } catch (\Exception $e) {
                // Ignore errors during polling
            }

            sleep($delaySeconds);
        }

        return false;
    }

    /**
     * Clean up all background processes
     */
    protected function cleanupBackgroundClients(): void
    {
        foreach ($this->backgroundProcesses as $processInfo) {
            if (is_resource($processInfo['process'])) {
                // Close pipes
                foreach ($processInfo['pipes'] as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }

                // Terminate process
                proc_terminate($processInfo['process']);
                proc_close($processInfo['process']);
            }
        }

        $this->backgroundProcesses = [];
    }

    /**
     * Generate a unique channel name for testing
     *
     * @param string $prefix Prefix for the channel name
     * @return string Unique channel name
     */
    protected function generateTestChannel(string $prefix = "test-channel-"): string
    {
        return $prefix . uniqid() . '-' . mt_rand(1000, 9999);
    }

    /**
     * Generate multiple unique channel names
     *
     * @param int $count Number of channels to generate
     * @param string $prefix Prefix for channel names
     * @return array<string> Array of unique channel names
     */
    protected function generateTestChannels(int $count, string $prefix = "test-channel-"): array
    {
        $channels = [];
        for ($i = 0; $i < $count; $i++) {
            $channels[] = $this->generateTestChannel($prefix);
        }
        return $channels;
    }
}
