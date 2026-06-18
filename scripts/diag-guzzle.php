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
 * It also runs a handler swap (publish, version=1.1) through Guzzle's curl
 * handler vs its StreamHandler (PHP stream wrapper, no libcurl). The raw curl
 * CLI control uses a different libcurl+TLS stack than PHP, so it can't exonerate
 * PHP's curl handler; this swap stays inside PHP and changes only the handler.
 * If the hang vanishes on the stream handler, the fault is PHP's curl handler /
 * its libcurl build; if it persists on both, the cause is above libcurl.
 *
 * Usage (from project root, keys exported as in the CI workflow):
 *   php scripts/diag-guzzle.php
 *
 * Finally it runs a VERBOSE capture (publish, curl handler, ~12 reused requests)
 * that dumps libcurl's wire-level trace per request — the `< Connection:`,
 * `< Keep-Alive:`, and `* Connection #N ... left intact / will close` lines —
 * so we can see EXACTLY what the load balancer sends on the connection it later
 * black-holes. The suite ran clean for months until the LB moved nginx ->
 * Pingora; the prime suspect is now a server-side keep-alive change (e.g. a low
 * `keepalive_requests` cap, or retiring the connection without a clean
 * `Connection: close`) that PHP's curl reuse trips over on the ~10th request.
 * Watch request #8/#9 (the retirement boundary) for the header the SDK ignores.
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
 *   DIAG_VERBOSE_ITERS           (optional, default 12 — how many requests the
 *                                 verbose capture fires to span the ~10th
 *                                 retirement boundary)
 */

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\RequestOptions;

$publishKey   = getenv('PUBLISH_KEY') ?: '';
$subscribeKey = getenv('SUBSCRIBE_KEY') ?: '';
$iterations   = (int) (getenv('DIAG_ITERATIONS') ?: 200);
$delayMs      = (int) (getenv('DIAG_DELAY_MS') ?: 0);
$verboseIters = (int) (getenv('DIAG_VERBOSE_ITERS') ?: 12);

if ($publishKey === '' || $subscribeKey === '') {
    fwrite(STDERR, "WARNING: PUBLISH_KEY/SUBSCRIBE_KEY not set — publish probe will be skipped.\n");
}

// Request timeout RAISED to 15s (was 10s, the SDK default). The Balancer holds a
// rate-limited publish ~10s before returning a 429; at the old 10s timeout the SDK
// aborted (cURL 28 / 0 bytes) ~8ms before the 429 arrived. 15s lets the response
// land — so this run now CONFIRMS the mechanism: the every-10th request should show
// up as an HTTP 429 (or a slow ~10s success) instead of a timeout. Mirrors raw
// curl's --max-time 12 and the tests' setNonSubscribeRequestTimeout(15).
const REQUEST_TIMEOUT = 15;
const CONNECT_TIMEOUT = 10;

/**
 * Fire $iterations requests at $url through ONE reused client and report.
 *
 * @param string $version '2' or '1.1' — passed to Guzzle's 'version' option.
 * @param string $handler  'curl' (default, the SDK transport) or 'stream'
 *                         (PHP stream wrapper — no libcurl). Swapping the
 *                         handler isolates whether the hang lives in PHP's
 *                         curl handler / its libcurl+OpenSSL build: if the
 *                         stream handler runs clean where curl hangs, the
 *                         fault is in the curl path, not the server.
 */
function probe(
    string $label,
    string $url,
    string $version,
    int $iterations,
    int $delayMs,
    string $handler = 'curl'
): void {
    // One client for the whole loop == SDK behavior == keep-alive pool reuse.
    if ($handler === 'stream') {
        // StreamHandler uses PHP's HTTP stream wrapper (no libcurl at all).
        $client = new GuzzleHttpClient(['handler' => HandlerStack::create(new StreamHandler())]);
    } else {
        // Explicit CurlHandler == what Guzzle picks by default == the SDK path.
        $client = new GuzzleHttpClient(['handler' => HandlerStack::create(new CurlHandler())]);
    }

    $times       = [];
    $failures      = [];
    $failedIndices = [];
    $reuseCount  = 0;
    $seenPorts   = [];      // local TCP ports seen — a repeat == a reused socket
    $httpVersions = [];     // negotiated protocol distribution (from PSR-7)
    $ipOk        = [];      // backend IP => count of successful requests
    $ipFail      = [];      // backend IP => count of timed-out requests
    $statusCodes = [];      // HTTP status => count (429s now visible, not thrown)

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $curlInfo = null;
        try {
            $response = $client->get($url, [
                RequestOptions::TIMEOUT         => REQUEST_TIMEOUT,
                RequestOptions::CONNECT_TIMEOUT => CONNECT_TIMEOUT,
                'version'                       => $version,
                // Do NOT throw on 4xx/5xx — we WANT to observe the Balancer's 429
                // as a normal response. With http_errors on (Guzzle default) a 429
                // throws ClientException and gets lumped into the opaque failure
                // bucket; off, we can tally it by status code below.
                RequestOptions::HTTP_ERRORS     => false,
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

            // Tally status code — every 10th should now be 429 (~10s), the rest 200.
            $code = $response->getStatusCode();
            $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;
            if ($code === 429) {
                $failedIndices[] = $i;   // record the cadence even though it didn't throw
            }

            $ip = $curlInfo['ip'] ?? '?';
            $ipOk[$ip] = ($ipOk[$ip] ?? 0) + 1;
        } catch (\Throwable $e) {
            $elapsed = (microtime(true) - $start) * 1000;
            $failedIndices[] = $i;
            // curl populates primary_ip once connected, so even a post-connect
            // timeout tells us WHICH backend black-holed the request.
            $ip = $curlInfo['ip'] ?? '?';
            $ipFail[$ip] = ($ipFail[$ip] ?? 0) + 1;
            // Keep one representative full message; the rest are summarized by index.
            if (count($failures) < 1) {
                $failures[] = sprintf("  e.g. #%d after %dms (ip=%s): %s", $i, (int) $elapsed, $ip, $e->getMessage());
            }
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
        "\n=== %s (handler=%s, version=%s, %d iterations, delay=%dms) ===\n",
        $label,
        $handler,
        $version,
        $iterations,
        $delayMs
    );
    if ($handler === 'stream') {
        // StreamHandler exposes no libcurl stats, so socket-reuse and per-backend
        // IP columns below will read 0 / '?' — that's expected, not a finding.
        echo "  (note: stream handler reports no socket/IP stats — ignore those columns)\n";
    }
    $httpDist = [];
    foreach ($httpVersions as $v => $n) {
        $httpDist[] = 'http/' . $v . ' x' . $n;
    }
    printf("  ok=%d  fail=%d  reused-socket=%d  distinct-sockets=%d\n", $ok, $fail, $reuseCount, count($seenPorts));
    // Status-code distribution: with http_errors off and a 15s timeout, the
    // every-10th rate-limited publish now shows as `429 x20` here instead of a
    // timeout — the confirmation that the "hang" was a delayed 429 all along.
    ksort($statusCodes);
    $codeDist = [];
    foreach ($statusCodes as $c => $n) {
        $codeDist[] = $c . ' x' . $n;
    }
    printf("  status-codes: %s\n", implode(' ', $codeDist) ?: '(none — all threw)');
    printf("  negotiated-protocol (PSR-7): %s\n", implode(' ', $httpDist) ?: '(n/a)');
    printf("  latency p50=%dms p99=%dms max=%dms\n", (int) $p50, (int) $p99, (int) $max);
    // Per-backend breakdown: if the failures concentrate on one (or few) IPs
    // while other IPs only ever succeed, the cause is a wedged backend node
    // behind the LB — not a rate limit (which would return fast 429s, not 10s
    // 0-byte hangs).
    $allIps = array_unique(array_merge(array_keys($ipOk), array_keys($ipFail)));
    sort($allIps);
    echo "  per-backend (ok/fail):";
    foreach ($allIps as $ip) {
        printf(" %s=%d/%d", $ip, $ipOk[$ip] ?? 0, $ipFail[$ip] ?? 0);
    }
    echo "\n";
    // $failedIndices now holds both thrown failures (timeouts) AND 429s observed as
    // normal responses. Label it as the every-10th cadence rather than "failures".
    if (!empty($failedIndices)) {
        echo "  rate-limited/failed indices: " . implode(',', $failedIndices) . "\n";
        if (!empty($failures)) {
            echo implode("\n", $failures) . "\n";
        }
    } else {
        echo "  (no 429s, no failures)\n";
    }
}

/**
 * Fire $iterations publishes over ONE reused curl connection with libcurl
 * VERBOSE tracing on, and print, per request, only the lines that reveal how the
 * load balancer manages the keep-alive connection:
 *   - `< Connection:` / `< Keep-Alive:` response headers (does Pingora announce a
 *     request cap or signal close?)
 *   - `* Connection #N ... left intact` vs `* Closing connection` (when libcurl
 *     decides to drop / retire the pooled socket)
 *   - `* Re-using existing connection` (proof the request rode a pooled socket)
 *   - any `* ... timed out` / error line on the hung request
 *
 * This is the smoking-gun capture for the nginx->Pingora hypothesis: watch the
 * boundary request (~#8/#9) for a server header the SDK's pooled handle ignores,
 * or a silent close that leaves the next reused request writing into a dead
 * socket (-> 0 bytes -> 10s timeout).
 *
 * Uses Guzzle's raw 'curl' option passthrough (CurlHandler only) to set
 * CURLOPT_VERBOSE + CURLOPT_STDERR at a temp stream we then read back.
 */
function captureVerbose(string $url, string $version, int $iterations, int $gapMs = 0): void
{
    printf(
        "\n######## verbose wire capture (publish, curl, version=%s, %d requests, gap=%dms) ########\n",
        $version,
        $iterations,
        $gapMs
    );
    if ($gapMs > 0) {
        // The point of the gap run: at gap=0 the every-10th request hangs; at
        // gap=1000ms the loop is clean. The open question this answers is WHY —
        // does the boundary request (~#9/#10) RE-USE the same socket (so the gap
        // just keeps it healthy), or does the client CONNECT a NEW socket (so the
        // gap lets a silent drop become visible and the client reconnects cleanly)?
        echo "Question: at this gap, does the ~#9/#10 boundary REUSE the socket or open a NEW connection?\n";
    } else {
        echo "Watch the ~#8/#9 boundary: what does the LB send right before the hang?\n";
    }

    // One client == one keep-alive pool, so requests reuse the same socket.
    $client = new GuzzleHttpClient(['handler' => HandlerStack::create(new CurlHandler())]);

    // Only the lines worth reading from libcurl's (very chatty) verbose trace.
    $keep = ['Connection', 'Keep-Alive', 'Re-using', 'Closing connection', 'left intact',
             'Connected to', 'timed out', 'Operation timed out', 'Empty reply', 'Connection died'];

    $reused = 0;        // requests that rode a pooled socket ("Re-using existing")
    $newConns = 0;      // requests that opened a fresh socket ("Connected to")

    for ($i = 0; $i < $iterations; $i++) {
        $trace = fopen('php://temp', 'w+');
        $start = microtime(true);
        $status = '?';
        $err = null;
        try {
            $resp = $client->get($url, [
                RequestOptions::TIMEOUT         => REQUEST_TIMEOUT,
                RequestOptions::CONNECT_TIMEOUT => CONNECT_TIMEOUT,
                'version'                       => $version,
                // CurlHandler passes these straight to the easy handle.
                'curl'                          => [
                    CURLOPT_VERBOSE => true,
                    CURLOPT_STDERR  => $trace,
                ],
            ]);
            $status = (string) $resp->getStatusCode();
        } catch (\Throwable $e) {
            $err = $e->getMessage();
        }
        $elapsed = (int) ((microtime(true) - $start) * 1000);

        // Pull the interesting verbose lines out of the trace stream.
        rewind($trace);
        $raw = stream_get_contents($trace) ?: '';
        fclose($trace);

        // Classify reuse-vs-reconnect from the raw trace (independent of $keep
        // filtering): "Re-using existing" == pooled socket; "Connected to" == a
        // fresh TCP connect this request. This is the direct evidence for the gap
        // question above.
        $isReuse = stripos($raw, 'Re-using existing') !== false;
        $isNew   = stripos($raw, 'Connected to') !== false;
        if ($isReuse) {
            $reused++;
        }
        if ($isNew) {
            $newConns++;
        }
        $conn = $isReuse ? 'REUSE' : ($isNew ? 'NEW-CONN' : '?');

        $lines = [];
        foreach (explode("\n", $raw) as $line) {
            $line = rtrim($line);
            if ($line === '') {
                continue;
            }
            // The TLS handshake line matches "Connection" but is per-request
            // noise, not keep-alive signal — drop it.
            if (stripos($line, 'SSL connection using') !== false) {
                continue;
            }
            foreach ($keep as $needle) {
                if (stripos($line, $needle) !== false) {
                    $lines[] = '    ' . $line;
                    break;
                }
            }
        }

        printf("  #%d  [%s]  status=%s  %dms%s\n", $i, $conn, $status, $elapsed, $err !== null ? "  ERROR: $err" : '');
        echo $lines ? implode("\n", $lines) . "\n" : "    (no keep-alive/connection lines in trace)\n";

        if ($gapMs > 0) {
            usleep($gapMs * 1000);
        }
    }

    printf("  -- summary: reused=%d new-connections=%d (of %d) --\n", $reused, $newConns, $iterations);
}

$timeUrl = "https://ps.pndsn.com/time/0";

echo "######## PHP Guzzle reuse diagnostic ########\n";
echo "Guzzle reuses one client across the whole run (like the SDK).\n";

// Baseline: keyless, both protocols.
probe("time/0 baseline", $timeUrl, '2', $iterations, $delayMs);
probe("time/0 baseline", $timeUrl, '1.1', $iterations, $delayMs);

// The actual failing op: publish.
if ($publishKey !== '' && $subscribeKey !== '') {
    $publishUrl = sprintf(
        "https://ps.pndsn.com/publish/%s/%s/0/diag-guzzle/0/%%22x%%22?uuid=diag-guzzle",
        $publishKey,
        $subscribeKey
    );

    // The failures are 10s 0-byte HANGS, not 429s, so this is NOT a rate limit.
    // A raw `curl --http1.1` CLI reusing one connection runs 200/200 clean, so
    // the hang is specific to the PHP/Guzzle path. The SDK forces 'version'=>'2'
    // (Endpoint::requestOptions line ~501) while curl ran 1.1 — so probe BOTH
    // here to isolate whether the forced HTTP/2 option is the trigger.
    // Watch: does version=1.1 go clean while version=2 hangs?
    probe("publish version=2", $publishUrl, '2', $iterations, $delayMs);
    probe("publish version=1.1", $publishUrl, '1.1', $iterations, $delayMs);

    // Handler swap: run the SAME publish loop through the curl handler and then
    // through the StreamHandler (PHP stream wrapper, no libcurl). The curl CLI
    // control runs clean but uses a DIFFERENT libcurl+TLS stack than PHP, so it
    // doesn't exonerate PHP's curl handler. This swap stays inside PHP and
    // changes only the handler: if the every-10th hang VANISHES on the stream
    // handler, it pins the fault to PHP's curl handler / its libcurl+OpenSSL
    // build; if it persists on both, the cause is above libcurl (server/network).
    echo "\n######## handler swap (publish, version=1.1) ########\n";
    probe("publish handler=curl", $publishUrl, '1.1', $iterations, $delayMs, 'curl');
    probe("publish handler=stream", $publishUrl, '1.1', $iterations, $delayMs, 'stream');

    // Verbose wire capture across the every-10th retirement boundary. This is the
    // hand-to-ops evidence for the nginx->Pingora keep-alive hypothesis: it shows
    // exactly what the LB sends on the connection right before it's black-holed.
    captureVerbose($publishUrl, '1.1', $verboseIters, 0);

    // Same capture WITH a 1000ms gap (the spacing that makes the loop clean). The
    // per-request REUSE/NEW-CONN tag + summary answers: does the gap keep ONE
    // socket alive across the boundary (all REUSE), or does it let a silent drop
    // surface so the client reconnects (a NEW-CONN at ~#9/#10)? We currently have
    // NO direct evidence either way — distinct-sockets=1 can't tell reuse from a
    // same-port reconnect. This settles it.
    captureVerbose($publishUrl, '1.1', $verboseIters, 1000);

    // Spacing check: if it were a time/rate effect, gaps would change the rate
    // of failure. For a per-request routing fault, the ~1-in-10 cadence persists
    // regardless of spacing.
    echo "\n######## spacing check (publish, 30 req each) ########\n";
    foreach ([0, 1000] as $gapMs) {
        probe("publish gap=" . $gapMs . "ms", $publishUrl, '1.1', 30, $gapMs);
    }
}

echo "\n######## done ########\n";
