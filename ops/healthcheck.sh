#!/usr/bin/env bash
# Stokito health check (root cron, every 5 min). Emails contact@stokito.com straight through SMTP with curl,
# so it works even when Laravel is broken. One email when a problem starts, a reminder every 6 h, one when it is fixed.
# Checks: site /up (2 failures in a row), disk > 85 %, new FAILED/DIVERGED in deploy or backup logs, TLS cert < 14 days.
set -uo pipefail
export PATH=/usr/local/bin:/usr/bin:/bin
APP=/home/stockpilot/htdocs/stockpilot.cloud
ST=/var/lib/stokito-health; mkdir -p "$ST"
TO=contact@stokito.com

envval() { grep -E "^$1=" "$APP/.env" | head -1 | cut -d= -f2- | sed -E "s/^['\"]//; s/['\"]$//"; }

mail() { # subject, body
  local U P N M subj
  U=$(envval MAIL_USERNAME); P=$(envval MAIL_PASSWORD)
  N=$(mktemp); M=$(mktemp); chmod 600 "$N" "$M"
  printf 'machine smtp.hostinger.com login %s password %s\n' "$U" "$P" > "$N"
  subj="=?UTF-8?B?$(printf '%s' "[Stokito] $1" | base64 -w0)?="
  printf 'From: Stokito Monitor <%s>\r\nTo: %s\r\nSubject: %s\r\nDate: %s\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n%s\r\n\r\n-- \r\nhealthcheck.sh sur le serveur (%s)\r\n' \
    "$U" "$TO" "$subj" "$(date -R)" "$2" "$(hostname)" > "$M"
  curl -s --max-time 30 --url "smtps://smtp.hostinger.com:465" --ssl-reqd --netrc-file "$N" \
    --mail-from "$U" --mail-rcpt "$TO" --upload-file "$M" >/dev/null 2>&1
  local rc=$?; rm -f "$N" "$M"; return $rc
}

# state machine per check: problem <name> <message> | ok <name>
problem() {
  local f="$ST/$1.down" now; now=$(date +%s)
  if [ ! -f "$f" ]; then
    mail "PROBLÈME : $2" "$2"$'\n\n'"Détecté le $(date '+%d/%m/%Y %H:%M') (heure du serveur)." && echo "$now" > "$f"
  elif [ $((now - $(cat "$f"))) -ge 21600 ]; then
    mail "TOUJOURS EN PANNE : $2" "$2"$'\n\n'"Le problème dure encore." && echo "$now" > "$f"
  fi
}
ok() { local f="$ST/$1.down"; [ -f "$f" ] && { mail "RÉTABLI : $1" "Le problème « $1 » est résolu ($(date '+%d/%m/%Y %H:%M'))."; rm -f "$f"; }; return 0; }

if [ "${1:-}" = "--test" ]; then mail "Test de la surveillance (volontaire, à ignorer)" "Si vous lisez ceci, les alertes du serveur fonctionnent."; echo "test mail rc=$?"; exit 0; fi

# 1. site
code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 https://stokito.com/up)
if [ "$code" != "200" ]; then
  n=$(( $(cat "$ST/site.fails" 2>/dev/null || echo 0) + 1 )); echo $n > "$ST/site.fails"
  [ $n -ge 2 ] && problem site "stokito.com ne répond pas correctement (HTTP $code sur /up, $n vérifications de suite)."
else
  echo 0 > "$ST/site.fails"; ok site
fi

# 2. disk
use=$(df --output=pcent / | tail -1 | tr -dc '0-9')
if [ "$use" -gt 85 ]; then problem disque "Le disque du serveur est plein à $use %."; else ok disque; fi

# 3. new failures in the deploy / backup logs (only lines added since the last run)
for pair in "deploiement:$APP/storage/logs/deploy.log" "sauvegarde:/var/log/stokito-backup.log"; do
  name=${pair%%:*}; log=${pair#*:}; [ -f "$log" ] || continue
  last=$(cat "$ST/$name.lines" 2>/dev/null || echo 0); total=$(wc -l < "$log")
  [ "$total" -lt "$last" ] && last=0
  bad=$(tail -n +$((last + 1)) "$log" | grep -E "FAILED|DIVERGED" | tail -3)
  echo "$total" > "$ST/$name.lines"
  [ -n "$bad" ] && mail "Échec : $name" "Nouvelles lignes d’échec dans $log :"$'\n'"$bad"
done

# 4. TLS certificate, once a day around 08:00
if [ "$(date +%H)" = "08" ] && [ "$(date +%M)" -lt 5 ]; then
  end=$(echo | openssl s_client -connect stokito.com:443 -servername stokito.com 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
  if [ -n "$end" ]; then
    days=$(( ($(date -d "$end" +%s) - $(date +%s)) / 86400 ))
    if [ "$days" -lt 14 ]; then problem certificat "Le certificat HTTPS de stokito.com expire dans $days jours."; else ok certificat; fi
  fi
fi
exit 0
