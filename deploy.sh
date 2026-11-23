#!/usr/bin/env bash
# Auto-deploy: run every minute by cron (user stockpilot). If GitHub main has new commits,
# fast-forward, back up the DB before migrations, build and clear caches. Log: storage/logs/deploy.log
set -uo pipefail
APP=/home/stockpilot/htdocs/stockpilot.cloud
cd "$APP" || exit 1
exec 9>/tmp/stokito-deploy.lock
flock -n 9 || exit 0
git fetch -q origin main 2>/dev/null || exit 0
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)
[ "$LOCAL" = "$REMOTE" ] && exit 0

LOG=storage/logs/deploy.log
run() {
  set -e
  echo "== $(date -Is) deploy ${LOCAL:0:7} -> ${REMOTE:0:7}"
  CHANGED=$(git diff --name-only "$LOCAL" "$REMOTE")
  git merge --ff-only -q origin/main
  if grep -q '^composer.lock$' <<<"$CHANGED"; then composer install --no-interaction --optimize-autoloader; fi
  if grep -q '^database/migrations/' <<<"$CHANGED"; then
    mkdir -p "$HOME/backups"
    DB=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"')
    U=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"')
    P=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"')
    CNF=$(mktemp); chmod 600 "$CNF"; printf '[client]\nuser=%s\npassword=%s\n' "$U" "$P" > "$CNF"
    mysqldump --defaults-extra-file="$CNF" --single-transaction "$DB" | gzip > "$HOME/backups/pre_migrate_$(date +%Y%m%d_%H%M%S).sql.gz"
    rm -f "$CNF"
    php artisan migrate --force
  fi
  if grep -q '^package-lock.json$' <<<"$CHANGED"; then npm ci --no-audit --no-fund; fi
  npm run build >/dev/null
  php artisan view:cache >/dev/null
  php artisan optimize:clear >/dev/null
  echo "== ok"
}
( run ) >> "$LOG" 2>&1 || echo "== $(date -Is) FAILED (see above)" >> "$LOG"
