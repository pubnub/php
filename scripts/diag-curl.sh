#!/usr/bin/env bash
#
# TEMPORARY diagnostic: reproduce the "every ~10th publish hangs 10s / 0 bytes"
# issue using the raw `curl` CLI instead of PHP/Guzzle.
#
# This is a CONTROL for scripts/diag-guzzle.php. The only way to reproduce the
# hang is to REUSE ONE keep-alive connection across many requests. A fresh curl
# per request opens a new socket every time and never reproduces it -- that is
# why the earlier per-request curl probes looked healthy.
#
# curl reuses the connection when you give it MANY URLs in ONE invocation, so
# this fires all N publishes down a single connection (like the SDK does).
#
# Usage (from project root):
#   set -a; source .env.dev; set +a
#   ./scripts/diag-curl.sh
#
# Env:
#   PUBLISH_KEY, SUBSCRIBE_KEY   (required)
#   DIAG_ITERATIONS              (optional, default 200)
#
# Output per request line: <index> <http_code> <time_total>s <local_port> <remote_ip>
#   - http_code 000            = no response (the hang / cURL error 28)
#   - http_code 429            = Balancer rate limit (fast) — curl reports these every
#                                ~10th publish; the PHP/Guzzle path black-holes (000/10s)
#                                at the same cadence instead. See the investigation doc.
#   - repeated local_port      = same reused socket (keep-alive working)
#   - a new local_port         = curl had to reopen after a failure

set -u

PUBLISH_KEY="${PUBLISH_KEY:-}"
SUBSCRIBE_KEY="${SUBSCRIBE_KEY:-}"
ITER="${DIAG_ITERATIONS:-200}"

if [[ -z "$PUBLISH_KEY" || -z "$SUBSCRIBE_KEY" ]]; then
  echo "ERROR: set PUBLISH_KEY and SUBSCRIBE_KEY (e.g. 'set -a; source .env.dev; set +a')" >&2
  exit 1
fi

URL="https://ps.pndsn.com/publish/${PUBLISH_KEY}/${SUBSCRIBE_KEY}/0/diag-curl/0/%22x%22?uuid=diag-curl"

# Build a curl-config file with N url entries -> ONE curl process, ONE connection.
CFG="$(mktemp)"
trap 'rm -f "$CFG"' EXIT
for ((i = 0; i < ITER; i++)); do
  printf 'url = "%s"\n' "$URL" >> "$CFG"
done

run_probe() {
  local label="$1"; shift
  echo ""
  echo "=== ${label} (${ITER} iterations) ==="

  # -K $CFG          : all N URLs, reusing the connection
  # --max-time 12    : per-request ceiling (>10s server timeout so the hang shows as 000)
  # -s -o /dev/null  : discard bodies
  # -w ...\n         : one machine-readable line per request
  #
  # CRITICAL: prefix the -w line with a unique sentinel ("STAT::") and parse ONLY
  # sentinel lines. With -K and many URLs, response bodies can still reach stdout and
  # merge with the metadata line (an earlier version miscounted every-10th HTTP 429s
  # as ok=200, e.g. `code={"status":429...`). Parsing on the sentinel makes the field
  # positions reliable regardless of body content.
  # NOTE: the sentinel must NOT start with '@' — curl reads a -w value beginning with
  # '@' as a FILENAME (`-w @file`), which fails with "error encountered when reading a
  # file". So use "STAT::", not "@@STAT@@".
  # Fields: http_code | time_total | local_port | remote_ip | num_connects
  curl "$@" -K "$CFG" \
    --max-time 12 \
    -s -o /dev/null \
    -w 'STAT:: %{http_code} %{time_total} %{local_port} %{remote_ip} %{num_connects}\n' \
  | awk '
      $1 != "STAT::" { next }       # ignore any body text that leaked onto stdout
      {
        idx = total                 # 0-based request index among sentinel lines
        code = $2; t = $3; port = $4; ip = $5; nconn = $6
        printf "  #%-3d code=%s time=%ss port=%s ip=%s conns=%s\n", idx, code, t, port, ip, nconn
        total++
        last_nconn = nconn + 0      # cumulative; final value = total TCP connections opened
        ipseen[ip]++
        if (code == "000" || code == "") {                  # hang / no response (cURL 28)
          hang++; hangidx = hangidx (hangidx=="" ? "" : ",") idx
          ipfail[ip]++
        } else if (code == "429") {                         # Balancer rate limit (fast)
          rl++; rlidx = rlidx (rlidx=="" ? "" : ",") idx
          ipok[ip]++   # server answered fast; not a hang
        } else {
          ok++; ipok[ip]++
        }
        if (port != lastport && NR > 1) sockets++
        portseen[port]=1
        lastport = port
      }
      END {
        ndist = 0; for (p in portseen) ndist++
        printf "\n  ok=%d  rate-limited(429)=%d  hung(000)=%d  distinct-sockets=%d  tcp-connects-opened=%d  (total=%d)\n", \
               ok+0, rl+0, hang+0, ndist, last_nconn, total
        printf "  (reuse proof: tcp-connects-opened << total => keep-alive working; ideally 1 + one reconnect per hang)\n"
        printf "  per-backend (non-hang/hung):"
        for (ip in ipseen) printf " %s=%d/%d", ip, ipok[ip]+0, ipfail[ip]+0
        printf "\n"
        if (rl+0 > 0)   printf "  rate-limited(429) indices: %s\n", rlidx
        if (hang+0 > 0) printf "  hung(000) indices: %s\n", hangidx
        if (rl+0 == 0 && hang+0 == 0) printf "  (no 429s, no hangs)\n"
      }'
}

echo "######## raw curl reuse diagnostic ########"
echo "One curl process reuses one keep-alive connection across ${ITER} publishes."
echo "Compare against scripts/diag-guzzle.php (the PHP/Guzzle path)."

run_probe "publish (HTTP/1.1)" --http1.1

echo ""
echo "######## done ########"