<?php

namespace PubNubTests\helpers;

use PubNub\Exceptions\PubNubServerException;
use RuntimeException;

/**
 * Tolerates the gap between a DataSync write and the moment it becomes readable.
 *
 * A record does not always show up on the very next request, and a loaded CI runner widens that
 * window well past what it looks like locally. Pausing after every write is the obvious answer but
 * a poor one: it slows every run down whether or not it was needed, and it still loses the race
 * whenever propagation takes a moment longer than the pause. Retrying the read costs nothing when
 * the record is already there and absorbs a slow moment when it is not.
 *
 * Only a 404 is retried. Every other server error is an answer in its own right, so a test that
 * expects a record to be missing, or a request to be denied, keeps failing immediately.
 */
trait RetriesDataSyncReads
{
    /** How long a read may keep retrying before the test gives up on it. */
    private int $readRetryBudgetSeconds = 15;

    private int $readRetryIntervalMicroseconds = 500000;

    /**
     * Repeats $read until it comes back - and until $accepts is satisfied with what came back,
     * when a predicate is given - or until the budget runs out.
     *
     * @template TResult
     * @param callable(): TResult $read
     * @param (callable(TResult): bool)|null $accepts
     * @param string $description Named in the failure message when the budget runs out.
     * @return TResult
     */
    protected function readEventually(
        callable $read,
        ?callable $accepts = null,
        string $description = 'the read'
    ): mixed {
        $deadline = microtime(true) + $this->readRetryBudgetSeconds;
        $missing = null;

        while (true) {
            try {
                $result = $read();

                if ($accepts === null || $accepts($result)) {
                    return $result;
                }

                $missing = null;
            } catch (PubNubServerException $exception) {
                if ($exception->getStatusCode() !== 404) {
                    throw $exception;
                }

                $missing = $exception;
            }

            if (microtime(true) >= $deadline) {
                if ($missing !== null) {
                    throw $missing;
                }

                throw new RuntimeException(sprintf(
                    '%s did not settle within %d seconds',
                    $description,
                    $this->readRetryBudgetSeconds
                ));
            }

            usleep($this->readRetryIntervalMicroseconds);
        }
    }

    /**
     * Blocks until a record just written can be read back, so that whatever the test does next -
     * often writing something that links to it - does not run ahead of it.
     *
     * @param callable(): mixed $read
     */
    protected function readableNow(callable $read, string $description = 'the record'): void
    {
        $this->readEventually($read, null, $description);
    }
}
