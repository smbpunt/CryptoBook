# Plan : Bascule de la prod vers VPS2

Couvre le point 2 de `roadmap.md`. Release `v0.2.0` (`dev` vers `main`, après le point 1), migration des données de l'ancien VPS vers `cryptobook_prod` sur VPS2, DNS de `cryptobook.fr` et `www`, activation du worker. Aucun code applicatif : le point est un déroulé d'exploitation, plus une MR de documentation à la fin. S'appuie sur tout ce que pose le point 1 (`vps-deploy-staging.md`) : `deploy.php`, pipeline, `cryptobook-worker@.service`, `deploy/sql/row-counts.sql`.

## État des lieux

- Ancienne prod : ancien code, crons `crypto:*` et `fiat:*`, pas de worker, aucun snapshot enregistré. Postgres d'une version ≤ 18.
- VPS2 : site `cryptobook.fr` provisionné par l'étape 19 au point 1 (bloc Caddy, `prod/shared/.env.local`, base `cryptobook_prod` vide). Pas de bloc `www`. Le certificat de `cryptobook.fr` ne peut pas être obtenu tant que le DNS pointe sur l'ancien VPS.
- `main` aligné sur `dev` par la PR GitHub #25 (sans tag), avant la migration vers GitLab. La MR de release ne porte donc que les commits du point 1 et ceux qui suivent ; dernier tag `0.1.0`.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Tag | `0.2.0` / `v0.2.0` | **`v0.2.0`** sur `main` après le merge du point 1 (déclenche `deploy_production`). Pas de tag sur la PR #25 : `main` n'y contient ni `.gitlab-ci.yml` ni `deploy.php` |
| Écritures pendant la copie | site ouvert / coupé | **Serveur web de l'ancienne prod arrêté** juste avant le dump final : aucune saisie perdue entre le dump et le DNS |
| Certificat | DNS-01 avant bascule / HTTP-01 après | **HTTP-01 après le DNS** : quelques minutes sans HTTPS valide, acceptées |
| `www` | absent / redirection | **`www.cryptobook.fr` → `https://cryptobook.fr` (301)**, bloc Caddy ajouté après le DNS |
| Worker de prod | avant / après le DNS | **Après le DNS** : un seul serveur appelle Fixer et écrit des snapshots |
| Retour arrière | perte acceptée / avec dump | **Dump de `cryptobook_prod` archivé**, puis retour sur l'ancien VPS. Les saisies faites sur VPS2 ne sont pas réinjectées |
| Fenêtre | — | **2 semaines**, ancien VPS arrêté mais intact, puis résiliation sur décision de Punt |
| Sauvegarde | — | Étape 17 du framework, corrigée pour gérer plusieurs sites (MR `installation-vps`) |

## Prérequis

- Point 1 terminé : `deploy_dev` vert, staging conforme depuis plusieurs jours (row counts identiques, smoke test, worker testé).
- MR `installation-vps` étape 17 multi-sites mergée. Sur VPS2 : `sudo bash install-vps.sh --role=vps2 --steps=17 --site=cryptobook`, puis `cat /etc/cron.d/pg-backup*` contient `smbai_prod` **et** `cryptobook_prod`.
- `prod/shared/.env.local` sur VPS2 complet (`MAILER_DSN`, `FIXER_API_KEY` de l'ancienne prod) : voir le tableau P3 du point 1.
- `/etc/systemd/system/cryptobook-worker@.service` présent sur VPS2, non activé.
- **J-1 au moins** : TTL 300 s sur `cryptobook.fr` et `www.cryptobook.fr` (OVH). Noter l'IP actuelle de l'ancienne prod.
- Sur l'ancienne prod, identifiés et notés : le service web (nom systemd), la crontab (`crontab -l -u <user>` sauvegardée dans `/root/crontab-cryptobook.bak`), le nom de la base, `psql --version`.

## Déroulé

### 1. Release (sans coupure)

1. MR `dev` → `main` sur GitLab, titre « Release 0.2.0 », merge commit. Pipeline de MR vert.
2. `git tag v0.2.0 origin/main && git push origin v0.2.0` → pipeline : lint, analyse, tests, puis `deploy_production` vers `/var/www/cryptobook/prod`.
3. Sur VPS2 : `readlink /var/www/cryptobook/prod/current` = dernière release, `php bin/console about` (`Environment prod`, `Debug false`), `php bin/console doctrine:migrations:status` (schéma vide migré). `curl -sI --resolve cryptobook.fr:80:92.222.87.219 http://cryptobook.fr` répond (redirection HTTPS de Caddy).

### 2. Coupure (ancienne prod)

1. Arrêter le serveur web de l'ancienne prod (`systemctl stop <service web>`).
2. Commenter les lignes `crypto:*` et `fiat:*` de la crontab (sauvegarde faite en prérequis).
3. `pg_dump -Ft -f /tmp/cryptobook-final.tar <base>`, puis `psql -X -At -d <base> -f row-counts.sql > /tmp/counts-old.txt`.
4. Copier les deux fichiers sur VPS2 (`scp -P 2222`), et le dump sur le poste de Punt (archive hors serveur).

### 3. Restauration (VPS2)

```bash
sudo -u postgres dropdb --if-exists cryptobook_prod
sudo -u postgres createdb -O cryptobook_prod cryptobook_prod
sudo -u postgres pg_restore -d cryptobook_prod --no-owner --no-privileges --role=cryptobook_prod --schema=public /tmp/cryptobook-final.tar
psql -X -At -d cryptobook_prod -f /var/www/cryptobook/prod/current/deploy/sql/row-counts.sql > /tmp/counts-new.txt
diff /tmp/counts-old.txt /tmp/counts-new.txt     # attendu : vide. Sinon : retour arrière
cd /var/www/cryptobook/prod/current
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:migrations:status       # aucune migration en attente
php bin/console doctrine:schema:validate
php bin/console cache:pool:clear dashboard.cache
```

La base `cryptobook_prod` remplacée est celle créée vide au déploiement de l'étape 1 : rien n'y est perdu.

### 4. DNS et certificats

1. OVH : `cryptobook.fr` A → `92.222.87.219`, `www.cryptobook.fr` A → `92.222.87.219`.
2. Attendre : `dig +short @1.1.1.1 cryptobook.fr` et `@8.8.8.8` (idem `www`) renvoient `92.222.87.219`.
3. Ajouter à `/etc/frankenphp/Caddyfile` sur VPS2 :
   ```caddyfile
   www.cryptobook.fr {
       redir https://cryptobook.fr{uri} permanent
   }
   ```
   Puis `sudo systemctl reload frankenphp` et `sudo chown deployer:deployer /var/log/frankenphp/*.log`. Pas de `frankenphp validate` en root.
4. `curl -sI https://cryptobook.fr` → 200 (ou 302 vers le login) avec un certificat valide ; `curl -sI https://www.cryptobook.fr` → 301. Si le certificat de l'apex manque encore après 2 minutes (backoff ACME après les échecs depuis le point 1) : `sudo systemctl restart frankenphp` puis re-`chown` des logs.

### 5. Worker

```bash
sudo systemctl enable --now cryptobook-worker@prod
cd /var/www/cryptobook/prod/current && php bin/console wallet:snapshot   # premier point sans attendre H:01
journalctl -u cryptobook-worker@prod -f
```

À H:01 : un snapshot par utilisateur, `select max(recorded_at) from wallet_snapshot` avance. Le lendemain à 6 h : `fiat:updateRates` sans erreur.

### 6. Contrôles finaux

- Login, dashboard (graphique avec les points du jour), une liste, EasyAdmin, mot de passe oublié (mail reçu : le worker traite la file `async`).
- `/var/log/frankenphp/cryptobook_prod.log` et `var/log/prod-*.log` sans erreur.
- `sudo bash verify-vps.sh --role=vps2 --steps=19 --site=cryptobook --domaine=cryptobook.fr` : 0 ✗.
- J+1 : `ls /var/backups/postgres/ | grep cryptobook_prod`.

## Retour arrière

Possible pendant 2 semaines. Dans l'ordre :

1. `sudo systemctl disable --now cryptobook-worker@prod`.
2. `sudo -u postgres pg_dump -Ft -f /var/backups/postgres/cryptobook_prod_rollback_$(date +%F).tar.gz cryptobook_prod`, copie sur le poste de Punt.
3. OVH : `cryptobook.fr` et `www` vers l'ancienne IP.
4. Ancienne prod : `systemctl start <service web>`, crontab restaurée depuis `/root/crontab-cryptobook.bak`.

Ce qui a été saisi sur VPS2 reste dans le dump de l'étape 2, sans réinjection automatique.

## Fin de la fenêtre (2 semaines, décision de Punt)

Dernier `pg_dump` de l'ancienne prod archivé hors serveur, puis résiliation de l'ancien VPS.

## Documentation (MR vers `dev` après la bascule)

Commit `docs: mark the VPS migration as done` :

- `doc/roadmap.md` : points 1 et 2 dans « Fait » (« Déploiement : Deployer + GitLab CI, staging sur VPS1, prod sur VPS2 »).
- `doc/bug.md` : retirer les deux points `vps.md` (certificat, retour arrière), tranchés ici.
- `CLAUDE.md`, section « Déploiement » : prod sur VPS2 (`cryptobook.fr`), worker `cryptobook-worker@prod`, sauvegarde par l'étape 17.

## Hors périmètre

- Ping de vie (point de roadmap à part).
- Copie externe des sauvegardes au-delà du pull VPS3.
- Réinjection des données saisies sur VPS2 en cas de retour arrière.

## À vérifier

- Comportement de Caddy après des échecs ACME répétés sur `cryptobook.fr` depuis le point 1 : le `reload` qui ajoute `www` relance-t-il l'obtention, ou faut-il le `restart` ?
- `pg_restore` depuis la version de Postgres de l'ancienne prod : relire la sortie, aucune erreur tolérée.
- Le mail de réinitialisation part bien avec le `MAILER_DSN` repris de l'ancienne prod.
