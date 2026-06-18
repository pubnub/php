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
  # Fields: http_code | time_total | local_port | remote_ip
  curl "$@" -K "$CFG" \
    --max-time 12 \
    -s -o /dev/null \
    -w '%{http_code} %{time_total} %{local_port} %{remote_ip} %{num_connects}\n' \
  | awk '
      {
        idx = NR - 1
        code = $1; t = $2; port = $3; ip = $4; nconn = $5
        printf "  #%-3d code=%s time=%ss port=%s ip=%s conns=%s\n", idx, code, t, port, ip, nconn
        total++
        last_nconn = nconn + 0    # cumulative; final value = total TCP connections opened
        ipseen[ip]++
        if (code == "000" || code == "" ) {                 # hang / no response
          fail++; failidx = failidx (failidx=="" ? "" : ",") idx
          ipfail[ip]++
        } else {
          ok++; ipok[ip]++
        }
        if (port != lastport && NR > 1) sockets++
        portseen[port]=1
        lastport = port
      }
      END {
        ndist = 0; for (p in portseen) ndist++
        printf "\n  ok=%d  fail=%d  distinct-sockets=%d  tcp-connects-opened=%d  (total=%d)\n", ok+0, fail+0, ndist, last_nconn, total
        printf "  (reuse proof: tcp-connects-opened << total => keep-alive working; ideally 1 + one reconnect per hang)\n"
        printf "  per-backend (ok/fail):"
        for (ip in ipseen) printf " %s=%d/%d", ip, ipok[ip]+0, ipfail[ip]+0
        printf "\n"
        if (fail+0 > 0) printf "  failed-indices: %s\n", failidx
        else            printf "  (no failures)\n"
      }'
}

echo "######## raw curl reuse diagnostic ########"
echo "One curl process reuses one keep-alive connection across ${ITER} publishes."
echo "Compare against scripts/diag-guzzle.php (the PHP/Guzzle path)."

run_probe "publish (HTTP/1.1)" --http1.1

echo ""
echo "######## done ########"