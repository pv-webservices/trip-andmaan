#!/usr/bin/env bash
# Activates a release on the Hostinger account over SSH. Run from the domain
# folder (e.g. ~/domains/tripandaman.in) where the archive was uploaded:
#
#   bash deploy-release.sh trip-andaman-<stamp>-<commit>.tar.gz          # deploy
#   bash deploy-release.sh --rollback                                    # previous release
#
# Layout it maintains:
#   releases/<name>/      application code (replaced on every release)
#   shared/.env           secrets (created by you once; never in a release)
#   shared/storage/       uploads, sessions, cache, logs (kept across releases)
#   current -> releases/<name>
#   public_html -> current/public   (set up once; see DEPLOYMENT-HOSTINGER.md)
set -euo pipefail

BASE="$(pwd)"
PHP_BIN="${PHP:-php}"
KEEP=5

activate() {
  local release="$1"
  ln -sfn "$release" "$BASE/current.tmp"
  mv -Tf "$BASE/current.tmp" "$BASE/current"
  echo "Live release: $(readlink "$BASE/current")"
}

if [[ "${1:-}" == "--rollback" ]]; then
  current="$(basename "$(readlink "$BASE/current")")"
  previous="$(ls -1 "$BASE/releases" | sort | grep -B1 -x "$current" | head -n1)"
  if [[ -z "$previous" || "$previous" == "$current" ]]; then
    echo "No earlier release to roll back to." >&2; exit 1
  fi
  activate "releases/$previous"
  (cd "$BASE/current" && "$PHP_BIN" artisan optimize:clear >/dev/null && "$PHP_BIN" artisan optimize >/dev/null)
  echo "Rolled back to $previous. If the newer release ran database migrations, see BACKUP-RESTORE.md."
  exit 0
fi

ARCHIVE="${1:?Usage: deploy-release.sh <release.tar.gz> | --rollback}"
NAME="$(basename "$ARCHIVE" .tar.gz)"
[[ -f "$BASE/shared/.env" ]] || { echo "Create $BASE/shared/.env first (see .env.example)." >&2; exit 1; }
# Refuse to deploy with an incomplete configuration (Laravel would silently fall back to defaults).
for setting in 'APP_KEY=base64:' 'DB_CONNECTION=mysql' 'DB_DATABASE=.' 'DB_USERNAME=.' 'DB_PASSWORD=.' 'APP_ENV=production' 'APP_DEBUG=false'; do
  grep -Eq "^${setting}" "$BASE/shared/.env" || { echo "shared/.env is missing or has an empty: ${setting%%=*}" >&2; exit 1; }
done

mkdir -p "$BASE/releases" "$BASE/shared/storage"
for dir in app/public app/private framework/cache/data framework/sessions framework/views logs; do
  mkdir -p "$BASE/shared/storage/$dir"
done
chmod 600 "$BASE/shared/.env"

echo "==> Extracting $NAME"
tar -xzf "$ARCHIVE" -C "$BASE/releases"
RELEASE="$BASE/releases/$NAME"
rm -rf "$RELEASE/storage"
ln -s "$BASE/shared/storage" "$RELEASE/storage"
ln -sfn "$BASE/shared/.env" "$RELEASE/.env"

cd "$RELEASE"
# Shell link instead of `artisan storage:link`: Hostinger disables PHP's symlink().
ln -sfn "$BASE/shared/storage/app/public" "$RELEASE/public/storage"
echo "==> Database migrations"
"$PHP_BIN" artisan migrate --force
echo "==> Caches"
"$PHP_BIN" artisan optimize:clear >/dev/null
"$PHP_BIN" artisan optimize
"$PHP_BIN" artisan filament:optimize >/dev/null

cd "$BASE"
activate "releases/$NAME"
# The page cache is keyed by content version; clear it so new templates show at once.
(cd "$BASE/current" && "$PHP_BIN" artisan cache:clear >/dev/null)

echo "==> Keeping the last $KEEP releases"
ls -1 "$BASE/releases" | sort | head -n -"$KEEP" | while read -r old; do
  [[ "releases/$old" == "$(readlink "$BASE/current")" ]] || rm -rf "$BASE/releases/$old"
done
echo "Done. First deploy only: php artisan cms:import, then php artisan cms:create-admin (in $BASE/current)."
