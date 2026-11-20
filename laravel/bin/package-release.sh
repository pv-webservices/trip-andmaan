#!/usr/bin/env bash
# Builds a self-contained release archive for Hostinger:
#   - compiled CSS/JS (public/build) — production never runs Node.js
#   - vendor/ installed without dev packages, optimised autoloader
#   - Filament's published assets
#   - the original images/videos/PDFs for the first `php artisan cms:import`
# Excludes secrets (.env), local databases, uploads, logs and tests.
#
# Usage (from the repository root, Git Bash/macOS/Linux):
#   bash laravel/bin/package-release.sh
# Output: dist-release/trip-andaman-<timestamp>-<commit>.tar.gz
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
APP="$ROOT/laravel"
PHP_BIN="${PHP:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"  # not $COMPOSER: Composer reads that as a composer.json path
STAMP="$(date +%Y%m%d%H%M%S)"
COMMIT="$(git -C "$ROOT" rev-parse --short HEAD 2>/dev/null || echo local)"
NAME="trip-andaman-${STAMP}-${COMMIT}"
WORK="$(mktemp -d)"
STAGE="$WORK/$NAME"
OUT="$ROOT/dist-release"

echo "==> Building front-end assets"
(cd "$APP" && npm ci --no-audit --no-fund && npm run build)

echo "==> Copying application into $STAGE"
mkdir -p "$STAGE"
(cd "$APP" && tar -cf - \
  --exclude=./.env --exclude='./.env.*' --exclude=./node_modules --exclude=./vendor \
  --exclude=./tests --exclude=./storage --exclude=./public/storage --exclude=./public/hot \
  --exclude=./database/database.sqlite --exclude=./database/content/media \
  --exclude=./.phpunit.cache --exclude=./.phpunit.result.cache \
  .) | (cd "$STAGE" && tar -xf -)
cp "$APP/.env.example" "$STAGE/.env.example"

# Empty storage skeleton (on the server storage/ is replaced by a link to shared/storage).
for dir in app/public app/private framework/cache/data framework/sessions framework/views logs; do
  mkdir -p "$STAGE/storage/$dir"
done

echo "==> Installing PHP dependencies (no dev packages)"
"$COMPOSER_BIN" install --working-dir="$STAGE" --no-dev --optimize-autoloader \
  --no-interaction --no-progress --prefer-dist
(cd "$STAGE" && "$PHP_BIN" artisan filament:assets >/dev/null)

echo "==> Bundling original media for the first content import"
MEDIA="$STAGE/database/content/media"
mkdir -p "$MEDIA/images"
for folder in editorial trips illustrations video-posters; do
  cp -R "$ROOT/public/images/$folder" "$MEDIA/images/$folder"
done
cp -R "$ROOT/public/videos" "$MEDIA/videos"
cp -R "$ROOT/public/itineraries" "$MEDIA/itineraries"

# The deploy script travels with the release.
cp "$APP/bin/deploy-release.sh" "$WORK/deploy-release.sh"

echo "==> Writing archive"
mkdir -p "$OUT"
tar -czf "$OUT/$NAME.tar.gz" -C "$WORK" "$NAME"
cp "$APP/bin/deploy-release.sh" "$OUT/deploy-release.sh"
rm -rf "$WORK"
echo "Release: $OUT/$NAME.tar.gz ($(du -h "$OUT/$NAME.tar.gz" | cut -f1))"
echo "Deploy script: $OUT/deploy-release.sh"
