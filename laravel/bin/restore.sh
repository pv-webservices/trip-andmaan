#!/usr/bin/env bash
# Restores a backup made by backup.sh. Run from the domain folder:
#
#   bash current/bin/restore.sh backups/<stamp>              # database + media
#   bash current/bin/restore.sh backups/<stamp> --db-only
#   bash current/bin/restore.sh backups/<stamp> --media-only
#
# The site is put in maintenance mode while restoring. The current media folder
# is kept as shared/storage/app/public.before-restore-<stamp> until you delete it.
set -euo pipefail

BASE="$(pwd)"
SRC="${1:?Usage: restore.sh backups/<stamp> [--db-only|--media-only]}"
MODE="${2:-all}"
ENV_FILE="$BASE/shared/.env"
[[ -f "$SRC/database.sql.gz" && -f "$SRC/media.tar.gz" ]] || { echo "Not a backup folder: $SRC" >&2; exit 1; }

value() { grep -E "^$1=" "$ENV_FILE" | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
read -r -p "Restore $SRC ($MODE) over the live site? Type RESTORE to continue: " answer
[[ "$answer" == "RESTORE" ]] || { echo "Cancelled."; exit 1; }

PHP_BIN="${PHP:-php}"
(cd "$BASE/current" && "$PHP_BIN" artisan down --retry=60 >/dev/null) || true
trap '(cd "$BASE/current" && "$PHP_BIN" artisan up >/dev/null) || true' EXIT

if [[ "$MODE" != "--media-only" ]]; then
  OPTS="$(mktemp)"
  printf '[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n' \
    "$(value DB_HOST)" "$(value DB_PORT)" "$(value DB_USERNAME)" "$(value DB_PASSWORD)" > "$OPTS"
  CLIENT="$(command -v mysql || command -v mariadb)"
  gunzip -c "$SRC/database.sql.gz" | "$CLIENT" --defaults-extra-file="$OPTS" "$(value DB_DATABASE)"
  rm -f "$OPTS"
  echo "Database restored."
fi

if [[ "$MODE" != "--db-only" ]]; then
  STAMP="$(date +%Y%m%d-%H%M%S)"
  mv "$BASE/shared/storage/app/public" "$BASE/shared/storage/app/public.before-restore-$STAMP"
  tar -xzf "$SRC/media.tar.gz" -C "$BASE/shared/storage/app"
  echo "Media restored (previous files kept in shared/storage/app/public.before-restore-$STAMP)."
fi

(cd "$BASE/current" && "$PHP_BIN" artisan cache:clear >/dev/null)
echo "Done. Check the website and the admin panel."
