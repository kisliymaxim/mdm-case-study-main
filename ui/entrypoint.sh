#!/bin/sh
set -e

cd /app

# Always run npm install — idempotent when the lockfile matches the
# existing node_modules, so it's effectively a no-op on warm boots
# (~1s). Covers three cases the named-volume setup used to mask:
#   1. Fresh checkout: host ./ui/node_modules is missing/empty.
#   2. package.json changed since last boot.
#   3. Host-side `rm -rf node_modules` for any reason.
echo "[ui-entrypoint] npm install"
npm install --prefer-offline --no-audit --no-fund

exec "$@"
