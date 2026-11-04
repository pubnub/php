#!/usr/bin/env bash
# Runs the offline verification steps and prints a compact summary.
# Success prints one line per step; failure prints only the first lines of the failing output.
#
# Usage: scripts/run-checks.sh [--full] [extra phpunit test paths...]
#   --full   also run PHPStan on the whole project (as CI does)
#   MAX_LINES=40 limits failure output per step (default 40)

set -u
cd "$(dirname "$0")/.." || exit 2

MAX_LINES="${MAX_LINES:-40}"
FULL=0
EXTRA_TESTS=()
for arg in "$@"; do
  if [ "$arg" = "--full" ]; then FULL=1; else EXTRA_TESTS+=("$arg"); fi
done

if [ ! -x vendor/bin/phpunit ]; then
  echo "vendor/ is missing: run 'composer install' first"
  exit 2
fi

TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT
FAILED=0

# Changed PHP files under src/, tests/, examples/: working tree vs origin/master, plus untracked files.
CHANGED=()
while IFS= read -r f; do
  case "$f" in vendor/*) continue ;; esac
  [ -f "$f" ] && CHANGED+=("$f")
done < <( { git diff --name-only --diff-filter=d origin/master 2>/dev/null
            git ls-files --others --exclude-standard; } | grep -E '^(src|tests|examples)/.*\.php$' | sort -u )

# CRLF checkouts make phpcs/PHPStan report noise that CI (LF) does not see.
CRLF=0
if [ ${#CHANGED[@]} -gt 0 ] && grep -l $'\r' "${CHANGED[@]}" >/dev/null 2>&1; then CRLF=1; fi

# step <label> <summary-lines-on-success> <command...>
step() {
  local label="$1" keep="$2"; shift 2
  "$@" >"$TMP" 2>&1; local rc=$?
  sed -i.bak "s#$PWD/##g; /^Note: Using configuration file/d" "$TMP" && rm -f "$TMP.bak"
  if [ "$rc" -eq 0 ]; then
    printf '%-9s OK  %s\n' "$label" "$(grep -v '^\s*$' "$TMP" | tail -n "$keep" | tr '\n' ' ')"
  else
    FAILED=1
    printf '%-9s FAIL\n' "$label"
    grep -v '^\s*$' "$TMP" | head -n "$MAX_LINES" | sed 's/^/    /'
    local total; total=$(grep -vc '^\s*$' "$TMP")
    [ "$total" -gt "$MAX_LINES" ] && echo "    ... $((total - MAX_LINES)) more lines truncated"
  fi
}

step "unit" 1 php vendor/bin/phpunit --do-not-cache-result tests/unit

for t in ${EXTRA_TESTS[@]+"${EXTRA_TESTS[@]}"}; do
  step "test" 1 php vendor/bin/phpunit --do-not-cache-result "$t"
done

if [ ${#CHANGED[@]} -gt 0 ]; then
  step "phpstan" 1 php vendor/bin/phpstan analyze --memory-limit 512M --no-progress \
    --error-format=raw "${CHANGED[@]}"
  PHPCS_ARGS=(--standard=PSR12 --report=emacs)
  [ "$CRLF" -eq 1 ] && PHPCS_ARGS+=(--exclude=Generic.Files.LineEndings)
  step "phpcs" 1 php vendor/bin/phpcs "${PHPCS_ARGS[@]}" "${CHANGED[@]}"
else
  echo "phpstan   SKIP  no changed PHP files"
  echo "phpcs     SKIP  no changed PHP files"
fi

if [ "$FULL" -eq 1 ]; then
  step "phpstan*" 1 php vendor/bin/phpstan analyze --memory-limit 512M --no-progress --error-format=raw
else
  echo "phpstan*  SKIP  full-project analysis (use --full)"
fi

[ "$CRLF" -eq 1 ] && echo "note: CRLF files detected; phpcs LineEndings excluded. Full PHPStan may show baseline mismatches in untouched files (environment noise: the checkout needs refreshing)."
echo "not run: live suites (tests/functional, tests/integrational need keys)"

exit "$FAILED"
