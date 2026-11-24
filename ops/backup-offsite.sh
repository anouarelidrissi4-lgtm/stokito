#!/usr/bin/env bash
# Stokito off-site backup (root cron, nightly). DB dump + uploaded files + .env -> Google Drive,
# encrypted by rclone crypt (remote gcrypt = gdrive:stokito-backups). Keys: /root/stokito-backup-crypt.txt
# Daily copies kept 30 days, monthly copies (1st of the month) kept 12 months. Log: /var/log/stokito-backup.log
set -euo pipefail
export PATH=/usr/local/bin:/usr/bin:/bin
APP=/home/stockpilot/htdocs/stockpilot.cloud
LOG=/var/log/stokito-backup.log
D=$(date +%F)
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

alert() {
  local msg="$1"
  local f; f=$(mktemp /tmp/stk-alert-XXXX.php); chmod 644 "$f"
  cat > "$f" <<'PHP'
<?php
chdir('/home/stockpilot/htdocs/stockpilot.cloud');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::raw(($argv[1] ?? 'Erreur de sauvegarde')."\n\nVoir /var/log/stokito-backup.log sur le serveur.", fn ($m) => $m->to('contact@stokito.com')->subject('[Stokito] Sauvegarde hors serveur ÉCHOUÉE'));
PHP
  sudo -u stockpilot php "$f" "$msg" >> "$LOG" 2>&1 || echo "   (alert email failed)" >> "$LOG"
  rm -f "$f"
}

run() {
  set -e
  echo "== $(date -Is) backup $D"
  val() { grep -E "^$1=" "$APP/.env" | head -1 | cut -d= -f2- | sed -E "s/^['\"]//; s/['\"]$//"; }
  CNF=$(mktemp); chmod 600 "$CNF"
  printf '[client]\nuser=%s\npassword=%s\n' "$(val DB_USERNAME)" "$(val DB_PASSWORD)" > "$CNF"
  mysqldump --defaults-extra-file="$CNF" --single-transaction --no-tablespaces --routines "$(val DB_DATABASE)" | gzip > "$TMP/db.sql.gz"
  rm -f "$CNF"
  gzip -t "$TMP/db.sql.gz"
  [ "$(stat -c %s "$TMP/db.sql.gz")" -gt 10000 ] || { echo "dump too small"; exit 1; }
  tar czf "$TMP/files.tgz" -C "$APP" storage/app .env
  rclone copy "$TMP" "gcrypt:daily/$D" --log-level ERROR
  if [ "$(date +%d)" = "01" ]; then rclone copy "$TMP" "gcrypt:monthly/$(date +%Y-%m)" --log-level ERROR; fi
  rclone delete gcrypt:daily --min-age 30d --log-level ERROR
  if rclone lsf gcrypt: --dirs-only | grep -qx 'monthly/'; then rclone delete gcrypt:monthly --min-age 370d --log-level ERROR; fi
  rclone rmdirs gcrypt: --leave-root --log-level ERROR
  echo "   db $(stat -c %s "$TMP/db.sql.gz") B, files $(stat -c %s "$TMP/files.tgz") B, remote: $(rclone lsf gcrypt:daily --dirs-only | wc -l) daily"
  echo "== ok"
}
if [ "${1:-}" = "--test-alert" ]; then alert "Test de l’alerte de sauvegarde (volontaire, à ignorer)."; echo "test alert sent"; exit 0; fi
( run ) >> "$LOG" 2>&1
rc=$?
if [ $rc -ne 0 ]; then
  echo "== $(date -Is) FAILED rc=$rc" >> "$LOG"
  alert "La sauvegarde du $D vers Google Drive a échoué (code $rc)."
fi
exit $rc
