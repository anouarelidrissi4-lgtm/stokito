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
