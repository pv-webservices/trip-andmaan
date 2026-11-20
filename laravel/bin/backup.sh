#!/usr/bin/env bash
# Backs up the database and uploaded media. Run from the domain folder
# (e.g. ~/domains/tripandaman.in) over SSH or from a Hostinger cron job:
#
#   bash current/bin/backup.sh            # creates backups/<timestamp>/
#   bash current/bin/backup.sh --keep 14  # keep the 14 newest backups (default 10)
#
# Output: backups/<stamp>/database.sql.gz, media.tar.gz, env.copy (permissions 600).
# Copy backups off the server regularly (hPanel File Manager or scp): a backup on
# the same account does not protect against losing the account.
set -euo pipefail

BASE="$(pwd)"
KEEP=10
[[ "${1:-}" == "--keep" ]] && KEEP="${2:?number}"
ENV_FILE="$BASE/shared/.env"
[[ -f "$ENV_FILE" ]] || { echo "Run from the domain folder (shared/.env not found)." >&2; exit 1; }

value() { grep -E "^$1=" "$ENV_FILE" | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="$BASE/backups/$STAMP"
umask 077
mkdir -p "$OUT"

# Credentials go in a temporary option file, never on the command line.
OPTS="$(mktemp)"
trap 'rm -f "$OPTS"' EXIT
printf '[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n' \
  "$(value DB_HOST)" "$(value DB_PORT)" "$(value DB_USERNAME)" "$(value DB_PASSWORD)" > "$OPTS"

DUMP="$(command -v mysqldump || command -v mariadb-dump)"
"$DUMP" --defaults-extra-file="$OPTS" --single-transaction --quick --routines --no-tablespaces \
  "$(value DB_DATABASE)" | gzip -9 > "$OUT/database.sql.gz"

# Uploaded media only (sessions, caches and logs are not needed to restore the site).
tar -czf "$OUT/media.tar.gz" -C "$BASE/shared/storage/app" public
cp "$ENV_FILE" "$OUT/env.copy"

ls -1 "$BASE/backups" | sort | head -n -"$KEEP" | while read -r old; do rm -rf "$BASE/backups/$old"; done
echo "Backup written to backups/$STAMP ($(du -sh "$OUT" | cut -f1))"
