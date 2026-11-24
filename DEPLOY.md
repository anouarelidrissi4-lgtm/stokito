# Déploiement de Stokito

Le serveur (VPS Hostinger, CloudPanel) suit la branche `main` de ce dépôt.

- `deploy.sh` tourne chaque minute (cron de l’utilisateur `stockpilot`).
- Un commit poussé sur `main` (depuis GitHub, même sur téléphone) est en ligne en une minute environ :
  `git merge --ff-only`, sauvegarde de la base avant toute migration (`~/backups/pre_migrate_*.sql.gz`),
  `php artisan migrate`, `npm run build`, caches vidés puis reconstruits.
- Un commit fait directement sur le serveur est poussé sur GitHub automatiquement.
- Si les deux historiques divergent, rien n’est déployé et une ligne `DIVERGED` est écrite une seule fois.
- Journal : `storage/logs/deploy.log`.

Jamais dans le dépôt : `.env`, clés, sauvegardes de base de données, données des clients.

## Surveillance et sauvegardes (crons de root, copies dans `ops/`)

- `ops/backup-offsite.sh` → `/root/backup-offsite.sh`, chaque nuit à 02:15 UTC : base + `storage/app` + `.env`
  vers Google Drive, chiffrés (rclone crypt, remote `gcrypt`). 30 jours + 12 mois. Clés : `/root/stokito-backup-crypt.txt`
  (et une copie hors ligne chez le propriétaire). Test : `/root/backup-offsite.sh --test-alert`.
- `ops/healthcheck.sh` → `/root/healthcheck.sh`, toutes les 5 minutes : `/up`, disque, échecs de déploiement
  ou de sauvegarde, certificat HTTPS. Emails via SMTP avec curl (marche même si Laravel est cassé). Test : `--test`.
- Les erreurs de l’application sont envoyées par `App\Support\ErrorAlert` (au plus une fois par heure par erreur, 20 par jour).
