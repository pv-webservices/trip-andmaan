# Backup, restore and rollback

All content lives in two places: the **MySQL database** (pages, sections, packages,
itinerary days, destinations, announcements, popups, administrators, media records)
and **`shared/storage/app/public`** (uploaded files). Both are needed for a restore.
`shared/.env` holds the secrets.

## Backup

Over SSH, from `~/domains/<domain>`:

```bash
bash current/bin/backup.sh            # → backups/<YYYYMMDD-HHMMSS>/
bash current/bin/backup.sh --keep 14  # keep the 14 newest (default 10)
```

Each backup folder (permissions 600) holds `database.sql.gz` (mysqldump, consistent
snapshot), `media.tar.gz` (uploaded files) and `env.copy` (secrets — store securely).
Database credentials are passed through a temporary option file, never on the
command line.

Schedule it daily with a Hostinger cron job (see `DEPLOYMENT-HOSTINGER.md` §7) and
**download backups off the server** regularly (File Manager or
`scp -P <port> -r <user>@<host>:domains/<domain>/backups/<stamp> .`). Hostinger's own
automatic backups (hPanel › Files › Backups) are an additional safety net, not a
replacement.

Without SSH: export the database in hPanel › Databases › phpMyAdmin › Export, and
download `domains/<domain>/shared/storage/app/public` with the File Manager.

## Restore

```bash
bash current/bin/restore.sh backups/<stamp>                # database and media
bash current/bin/restore.sh backups/<stamp> --db-only
bash current/bin/restore.sh backups/<stamp> --media-only
```

The script asks you to type `RESTORE`, puts the site in maintenance mode, restores,
clears the page cache and brings the site back. The media folder it replaces is kept
as `shared/storage/app/public.before-restore-<stamp>` until you delete it.

Without SSH: phpMyAdmin › Import the `.sql` (gunzip it first), and upload the media
folder back with the File Manager.

To move to a new server: deploy a release (§5 of the deployment guide) with the old
`env.copy` values (new database credentials), skip `cms:import`, then restore.

## Rollback of a release

```bash
cd ~/domains/<domain>
bash deploy-release.sh --rollback      # switches `current` to the previous release
```

Releases never touch uploads or content, so a rollback keeps all edits. If the newer
release added database migrations, the older code normally still works (migrations in
this project only add tables/columns). If a release notes a migration that changes
existing data, take a backup first (`deploy` checklist) and, to undo it, roll back and
restore that backup with `--db-only`.

## Tested

Backup, database restore, media-only restore after deleting uploads, release
upgrade and rollback were exercised on PHP 8.3 + Apache + MariaDB 11.4 using these
scripts (`VERIFICATION.md`). They have not yet been run on the Hostinger account.
