<?php

// phpcs:disable PSR1.Files.SideEffects -- throwaway CI diagnostic script, not library code

/**
 * TEMPORARY diagnostic: reproduce the intermittent `cURL error 28` timeouts
 * that the live integration tests hit on CI.
 *
 * Unlike the shell `curl` probe in the workflow, this uses a SINGLE reused
 * GuzzleHttp\Client across all requests — exactly like the SDK does for an
 * entire PHPUnit run. That is the only way to exercise cURL keep-alive
 * connection reuse, which is the prime suspect for "connected fine, then 0
 * bytes received after 10s" (a stale / half-closed pooled socket).
 *
 * It runs the same operation that times out (publish) plus a keyless baseline
 * (time/0), under two protocol settings:
 *   - version=2   : what the SDK forces today (Endpoint::requestOptions)
 *   - version=1.1 : what ps.pndsn.com actually speaks
 *
 * Usage (from project root, keys exported as in the CI workflow):
 *   php scripts/diag-guzzle.php
 *
 * Env:
 *   PUBLISH_KEY, SUBSCRIBE_KEY   (required for the publish probe)
 *   DIAG_ITERATIONS              (optional, default 200)
 *   DIAG_DELAY_MS                (optional, idle gap between requests in ms,
 *                                 default 0; try 5000 to provoke idle-timeout
 *                                 of pooled sockets)
 */

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\RequestOptions;

$publishKey   = getenv('PUBLISH_KEY') ?: '';
$subscribeKey = getenv('SUBSCRIBE_KEY') ?: '';
$iterations   = (int) (getenv('DIAG_ITERATIONS') ?: 200);
$delayMs      = (int) (getenv('DIAG_DELAY_MS') ?: 0);

if ($publishKey === '' || $subscribeKey === '') {
    fwrite(STDERR, "WARNING: PUBLISH_KEY/SUBSCRIBE_KEY not set — publish probe will be skipped.\n");
}

// Mirror the SDK's timeout defaults (PNConfiguration: 10s request, 10s connect).
const REQUEST_TIMEOUT = 10;
const CONNECT_TIMEOUT = 10;

/**
 * Fire $iterations requests at $url through ONE reused client and report.
 *
 * @param string $version '2' or '1.1' — passed to Guzzle's 'version' option.
 */
function probe(string $label, string $url, string $version, int $iterations, int $delayMs): void
{
    // One client for the whole loop == SDK behavior == cURL keep-alive pool reuse.
    $client = new GuzzleHttpClient();

    $times       = [];
    $failures    = [];
    $reuseCount  = 0;
    $seenPorts   = [];      // local TCP ports seen — a repeat == a reused socket
    $httpVersions = [];     // curl-level wire protocol distribution

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $curlInfo = null;
        try {
            $response = $client->get($url, [
                RequestOptions::TIMEOUT         => REQUEST_TIMEOUT,
                RequestOptions::CONNECT_TIMEOUT => CONNECT_TIMEOUT,
                'version'                       => $version,
                // Detect socket reuse via local_port (num_connects is absent in
                // some libcurl builds). NOTE: do NOT trust the stat's
                // 'http_version' field — it echoes the REQUESTED version, not the
                // negotiated one. The PSR-7 response below reports the real wire
                // protocol (ps.pndsn.com is HTTP/1.1 only — no h2 over ALPN).
                'on_stats'                      => function ($stats) use (&$curlInfo) {
                    $h = $stats->getHandlerStats();
                    $curlInfo = [
                        'port'  => $h['local_port'] ?? null,
                        'ip'    => $h['primary_ip'] ?? '?',
                    ];
                },
            ]);
            $elapsed = (microtime(true) - $start) * 1000;
            $times[] = $elapsed;

            $port = $curlInfo['port'] ?? null;
            if ($port !== null) {
                if (isset($seenPorts[$port])) {
                    $reuseCount++;     // rode a previously-opened (pooled) socket
                }
                $seenPorts[$port] = true;
            }
            // Real negotiated protocol, straight from the PSR-7 response.
            $hv = $response->getProtocolVersion();
            $httpVersions[$hv] = ($httpVersions[$hv] ?? 0) + 1;
        } catch (\Throwable $e) {
            $elapsed = (microtime(true) - $start) * 1000;
            $failures[] = sprintf(
                "  #%d after %dms: %s",
                $i,
                (int) $elapsed,
                $e->getMessage()
            );
        }

        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
    }

    $ok    = count($times);
    $fail  = count($failures);
    sort($times);
    $p50   = $times ? $times[(int) (count($times) * 0.50)] ?? end($times) : 0;
    $p99   = $times ? $times[(int) (count($times) * 0.99)] ?? end($times) : 0;
    $max   = $times ? max($times) : 0;

    printf(
        "\n=== %s (version=%s, %d iterations, delay=%dms) ===\n",
        $label,
        $version,
        $iterations,
        $delayMs
    );
    $httpDist = [];
    foreach ($httpVersions as $v => $n) {
        $httpDist[] = 'http/' . $v . ' x' . $n;
    }
    printf("  ok=%d  fail=%d  reused-socket=%d  distinct-sockets=%d\n", $ok, $fail, $reuseCount, count($seenPorts));
    printf("  negotiated-protocol (PSR-7): %s\n", implode(' ', $httpDist) ?: '(n/a)');
    printf("  latency p50=%dms p99=%dms max=%dms\n", (int) $p50, (int) $p99, (int) $max);
    if ($failures) {
        echo "  FAILURES:\n" . implode("\n", $failures) . "\n";
    } else {
        echo "  (no failures)\n";
    }
}

$timeUrl = "https://ps.pndsn.com/time/0";

echo "######## PHP Guzzle reuse diagnostic ########\n";
echo "Guzzle reuses one client across the whole run (like the SDK).\n";

// Baseline: keyless, both protocols.
probe("time/0 baseline", $timeUrl, '2', $iterations, $delayMs);
probe("time/0 baseline", $timeUrl, '1.1', $iterations, $delayMs);

// The actual failing op: publish, both protocols.
if ($publishKey !== '' && $subscribeKey !== '') {
    $publishUrl = sprintf(
        "https://ps.pndsn.com/publish/%s/%s/0/diag-guzzle/0/%%22x%%22?uuid=diag-guzzle",
        $publishKey,
        $subscribeKey
    );
    probe("publish (SDK-forced h2)", $publishUrl, '2', $iterations, $delayMs);
    probe("publish (h1.1)", $publishUrl, '1.1', $iterations, $delayMs);
}

echo "\n######## done ########\n";
