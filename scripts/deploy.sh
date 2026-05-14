#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/er-drill}"
BRANCH="${DEPLOY_BRANCH:-staging}"

cd "$APP_DIR"

echo "==> Putting app into maintenance mode"
php artisan down --render="errors::503" --retry=15 || true

trap 'php artisan up || true' EXIT

echo "==> Fetching latest $BRANCH"
git fetch --prune origin
git reset --hard "origin/$BRANCH"

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Installing JS dependencies + building assets"
npm ci --no-audit --no-fund
npm run build

echo "==> Running migrations"
php artisan migrate --force

echo "==> Refreshing caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "==> Linking storage (idempotent)"
php artisan storage:link || true

echo "==> Restarting queue worker"
sudo -n /bin/systemctl restart er-drill-queue.service

echo "==> Deploy complete"
