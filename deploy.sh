#!/usr/bin/env bash
# Auto-deploy, run every minute by cron (user stockpilot). Log: storage/logs/deploy.log
# - GitHub main ahead of the server  -> fast-forward, DB backup before migrations, build, caches
# - server ahead of GitHub (commit made on the server) -> push it to GitHub
# - histories diverged -> do nothing, log once (fix by hand)
set -uo pipefail
export PATH=/usr/local/bin:/usr/bin:/bin   # cron gives no PATH: npm could not find vite
APP=/home/stockpilot/htdocs/stockpilot.cloud
cd "$APP" || exit 1
exec 9>/tmp/stokito-deploy.lock
flock -n 9 || exit 0
git fetch -q origin main 2>/dev/null || exit 0
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)
[ "$LOCAL" = "$REMOTE" ] && { rm -f /tmp/stokito-deploy.diverged; exit 0; }
LOG=storage/logs/deploy.log

if git merge-base --is-ancestor "$REMOTE" "$LOCAL"; then
  git push -q origin HEAD:main >> "$LOG" 2>&1 && echo "== $(date -Is) pushed server commits ${REMOTE:0:7} -> ${LOCAL:0:7}" >> "$LOG"
  exit 0
fi
if ! git merge-base --is-ancestor "$LOCAL" "$REMOTE"; then
  [ -f /tmp/stokito-deploy.diverged ] || echo "== $(date -Is) DIVERGED: server ${LOCAL:0:7} vs GitHub ${REMOTE:0:7} — nothing deployed, fix by hand" >> "$LOG"
  touch /tmp/stokito-deploy.diverged
  exit 0
fi

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
    mysqldump --defaults-extra-file="$CNF" --single-transaction --no-tablespaces "$DB" | gzip > "$HOME/backups/pre_migrate_$(date +%Y%m%d_%H%M%S).sql.gz"
    rm -f "$CNF"
    php artisan migrate --force
  fi
  if grep -q '^package-lock.json$' <<<"$CHANGED"; then npm ci --no-audit --no-fund; fi
  npm run build > /tmp/stokito-build.log 2>&1 || { tail -20 /tmp/stokito-build.log; exit 1; }
  php artisan optimize:clear >/dev/null
  php artisan view:cache >/dev/null
  php artisan route:cache >/dev/null
  echo "== ok"
}
# not "( run ) || ...": inside an || list bash ignores set -e, so failures were logged as ok
( run ) >> "$LOG" 2>&1
[ $? -eq 0 ] || echo "== $(date -Is) FAILED (see above)" >> "$LOG"
