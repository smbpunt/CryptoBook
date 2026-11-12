# Plan : Déploiement par le framework FrankenPHP, staging sur VPS1

Couvre le point 1 de `roadmap.md`. Dépôt déplacé sur GitLab (`smbai/cryptobook`), site provisionné par le framework `installation-vps` sur VPS1 (dev) et VPS2 (prod), déploiement Deployer + pipeline GitLab repris de `smbai/symfony/symfony-template`, staging `dev.cryptobook.fr` alimenté par un dump de la prod actuelle. Aucun changement de schéma, de route ni de dépendance composer. La bascule de la prod est le point 2 (`prod-cutover.md`).

## État des lieux

Audit des deux VPS du 2026-11-12 (identiques) :

| Élément | Valeur |
|---|---|
| FrankenPHP | 1.12.7, PHP **8.5.9 ZTS**, installé en paquets apt (`/etc/php-zts/`, extensions = paquets `php-zts-*`) |
| Extensions du PHP web | `pcntl opcache mbstring curl dom simplexml tokenizer fileinfo` présentes ; **`intl`, `pdo_pgsql`, `pgsql`, `zip` absentes** |
| PHP système | **8.5.4** NTS (`php8.5-*` apt, étape 08 du framework), Composer en `/usr/local/bin/composer` |
| `frankenphp php-cli` | n'accepte qu'un script ou `-r` (pas de `-v`, `-m`, `--ini`), `PHP_BINARY` vide |
| PostgreSQL | **18.6** → `serverVersion=18` |
| Ressources | 2 cœurs, 3 Go, pas de swap, 34 Go libres |
| `session.save_path` web | `/var/lib/php-zts/session` (hors release, commun à tous les sites) |
| Bases `cryptobook*` | absentes (step 19 pas encore joué) |

Dépôts de référence lus :

- Template : `deploy.php` (recipe `symfony`, `shared_dirs` `var/cache` + `var/log`, pas de `bin/php` → PHP système), `.gitlab-ci.yml` (lint → analyze → test → deploy, tags `vX.Y.Z`, jobs via `make` du template), `ci/*.sh` (`setup-ssh.sh` décode `SSH_PRIVATE_KEY` en base64, variable de groupe `smbai`, protégée).
- Framework : étape 19 (site + `.env.local` + bases, `APP_ENV=prod` sur les deux stages), étape 23 `create-app.sh` (protections, deploy keys `deployer-vps1/2`, variables `DEPLOY_HOST`/`DEPLOY_PATH`, ne pousse une branche que si elle est absente), étape 17 (réécrit **tout** `/etc/cron.d/pg-backup` avec un seul site).

Code CryptoBook :

- `config/packages/cache.yaml` : pools sur le filesystem, `%kernel.share_dir%` = `var/share/<env>` dans la release.
- Préfixe des clés de cache = chemin du projet (`CachePoolPass`), donc propre à chaque release : le signal de `messenger:stop-workers` lancé depuis la nouvelle release n'atteint pas le worker de l'ancienne.
- `config/packages/monolog.yaml` (prod) : `nested` en `stream` sur `var/log/prod.log`, sans rotation (`doc/bug.md`).
- `deploy/systemd/cryptobook-worker.service` : `www-data`, `/var/www/cryptobook`, `/usr/bin/php`.
- `prod.tar.gz` (local, ignoré) : dump `pg_dump -Ft` de la prod actuelle ; l'ancienne prod tourne avec des crons `crypto:*` / `fiat:*`, sans worker.
- `main` aligné sur `dev` par la PR GitHub #25, sans tag ; tag existant `0.1.0`.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Dépôt | écraser `smbpunt/cryptobook` / nouveau sous `smbpunt` / nouveau sous `smbai` | **`smbai/cryptobook`** (variables de groupe et clé `runner-ci` héritées). `smbpunt/cryptobook` renommé `cryptobook-old` et archivé |
| Source de vérité | GitHub + miroir Actions / GitLab | **GitLab** : MR, CI, déploiement. GitHub = miroir push de GitLab |
| PHP CLI (Deployer, Composer, `bin/console`, worker) | `frankenphp php-cli` / PHP système | **PHP système 8.5** (`/usr/bin/php`), comme le template et les autres sites. Web = FrankenPHP 8.5. `bin/php` non redéfini |
| Extensions web manquantes | apt à la main / framework | **MR sur `installation-vps`** (étape 09), prérequis |
| Sauvegarde `cryptobook_prod` | cron à la main / framework | **MR sur `installation-vps`** (étape 17 multi-sites), prérequis du point 2 |
| Pipeline | deploy seul / template complet | **Template complet** : lint, analyse, tests (service Postgres), deploy |
| Commandes CI | `make` du template / scripts dans `.gitlab-ci.yml` | **Scripts dans `.gitlab-ci.yml`** : le `Makefile` du projet passe par Docker ; `make ci` reste le point 4 |
| Postgres de la CI | 16 (Docker local) / 18 (serveurs) | **`postgres:18-alpine`**, comme les serveurs |
| Tag de prod | `0.2.0` / `v0.2.0` | **`v0.2.0`** (règle du template inchangée) |
| `APP_ENV` du staging | `dev` / `prod` | **`prod`** (`composer --no-dev`, convention de l'étape 19) |
| `var/cache` | partagé (template) / par release | **Par release** : partagé, la nouvelle release écraserait le conteneur de celle en service |
| `var/share` (cache applicatif, état du Scheduler) | partagé + `prefix_seed` / par release | **Par release** : le cache du dashboard repart vide à chaque déploiement (pas d'objet sérialisé d'une ancienne version) ; l'état du Scheduler ne survit pas à un déploiement |
| Redémarrage du worker | `prefix_seed` fixe / `stop-workers` depuis l'ancienne release | **`messenger:stop-workers` lancé depuis `{{previous_release}}`** : même chemin, donc même préfixe de cache que le worker en cours. Aucune règle sudo |
| Sessions | `php.ini` global / config du projet | **Config du projet** : fichiers dans `var/sessions/<env>`, dossier partagé |
| Binaire Tailwind | partagé / par release | **Par release** (téléchargé à chaque `tailwind:build`, quelques secondes) |
| `keep_releases` | 1 (groupe) / 3 | **3**, variable projet `DEPLOY_KEEP_RELEASES` (prime sur celle du groupe) |
| Retour arrière CI | `rollback:full` (template) / code seul | **Code seul** (`dep rollback` + reload) : `migrate prev` annulerait une migration au hasard et perdrait des données. Une migration se défait à la main |
| Worker | une unité par VPS / unité template | **`cryptobook-worker@.service`** (`%i` = `dev` ou `prod`) ; sur VPS1 installé mais **jamais activé** |
| Staging | public / basic auth / anonymisé | **Basic auth Caddy**, données non modifiées, `MAILER_DSN=null://null`, pas de `FIXER_API_KEY` |
| Ping de vie | — | **Reporté** : nouveau point de roadmap, hors de ce point |
| Rotation de `prod.log` | — | Corrigée au passage (`rotating_file`, 14 jours) |

## Prérequis (opérations, avant la MR)

Faits par Punt ou un compte sudo. Le code de la MR n'en dépend pas, mais le premier déploiement oui.

### P1. Framework `installation-vps`

1. MR sur `smbai/devops/installation-vps`, étape 09 :
   - installer `php-zts-intl`, `php-zts-pdo-pgsql` et `php-zts-zip` (noms exacts : `apt-cache search php-zts`) ;
   - `check_09` vérifie leur présence par `frankenphp php-cli -r 'echo implode(PHP_EOL, get_loaded_extensions());'`.
2. Rejouer l'étape 09 sur VPS1 puis VPS2, redémarrer FrankenPHP, re-`chown deployer` des logs (piège `validate`).
3. Contrôle, sur chaque VPS :
   ```bash
   frankenphp php-cli -r 'foreach (["intl","pdo_pgsql","zip"] as $e) echo $e, ": ", extension_loaded($e) ? "ok" : "MISSING", PHP_EOL;'
   php -r 'foreach (["intl","pdo_pgsql","zip","pcntl","mbstring"] as $e) echo $e, ": ", extension_loaded($e) ? "ok" : "MISSING", PHP_EOL;'
   php -r 'echo ini_get("memory_limit"), " | ", ini_get("disable_functions"), PHP_EOL;'
   ```
   Attendu : tout `ok`, `proc_open` absent de `disable_functions`.

### P2. Dépôt GitLab

1. Renommer `smbpunt/cryptobook` en `smbpunt/cryptobook-old`, puis l'archiver.
2. Vérifier qu'aucune PR n'est ouverte sur GitHub (`gh pr list`) et que `main` = `dev` (PR #25 mergée), sinon les traiter d'abord.
3. Créer `smbai/cryptobook` (privé, vide), puis pousser l'historique GitHub tel quel :
   ```bash
   git remote rename origin github
   git remote add origin git@gitlab.com:smbai/cryptobook.git
   git push origin main dev
   git push origin 0.1.0
   git ls-remote origin && git ls-remote github   # mêmes SHA pour main, dev, 0.1.0
   ```
4. `bash create-app.sh --app=cryptobook --template=symfony --dry-run`, puis sans `--dry-run`. Les branches existent déjà : l'étape ne pousse rien. Elle pose les protections (`main`/`dev` push = personne, tags `v*`), les deploy keys `deployer-vps1`/`deployer-vps2` et `DEPLOY_HOST`/`DEPLOY_PATH` (scopes `dev`/`production`).
5. Variable projet `DEPLOY_KEEP_RELEASES=3` (protégée). Vérifier que `SSH_PRIVATE_KEY`, `DEPLOY_USER`, `DEPLOY_PORT` sont hérités du groupe (`bash verify-vps.sh --role=vps3 --steps=23 --app=cryptobook --template=symfony`).
6. Miroir : GitLab → Settings → Repository → Mirroring repositories, **push** vers `https://github.com/smbpunt/CryptoBook.git`, token GitHub fine-grained (`Contents: read and write` sur ce seul dépôt). Description GitHub : « Mirror of gitlab.com/smbai/cryptobook ».

### P3. Site (étape 19)

1. VPS1 puis VPS2 :
   ```bash
   sudo bash install-vps.sh --role=vps1 --steps=site --site=cryptobook --domaine=cryptobook.fr --with-db --dry-run
   sudo bash install-vps.sh --role=vps1 --steps=site --site=cryptobook --domaine=cryptobook.fr --with-db
   # idem --role=vps2
   ```
   VPS1 sert `dev.cryptobook.fr` → `/var/www/cryptobook/dev/current/public` ; VPS2 sert `cryptobook.fr` → `/var/www/cryptobook/prod/current/public`.
2. Secrets finals dans `shared/.env.local` (600, `deployer`), mot de passe depuis `/var/log/vps-install/` :

   | Clé | VPS1 `dev/shared/.env.local` | VPS2 `prod/shared/.env.local` |
   |---|---|---|
   | `APP_ENV` | `prod` | `prod` |
   | `APP_SECRET` | `openssl rand -hex 16` | `openssl rand -hex 16` |
   | `DEFAULT_URI` | `https://dev.cryptobook.fr` | `https://cryptobook.fr` |
   | `DATABASE_URL` | `"postgresql://cryptobook_dev:…@127.0.0.1:5432/cryptobook_dev?serverVersion=18&charset=utf8"` | idem `cryptobook_prod` |
   | `MAILER_DSN` | `null://null` | valeur de l'ancienne prod |
   | `FIXER_API_KEY` | absente | valeur de l'ancienne prod |

3. Basic auth sur VPS1, dans le bloc `dev.cryptobook.fr` de `/etc/frankenphp/Caddyfile` :
   ```caddyfile
   basic_auth {
       punt <hash de `frankenphp hash-password`>
   }
   ```
   Puis `sudo systemctl reload frankenphp` et `sudo chown deployer:deployer /var/log/frankenphp/*.log`. Ne pas lancer `frankenphp validate` en root.
4. Unité du worker, sur chaque VPS (après le merge de la MR) : `sudo cp deploy/systemd/cryptobook-worker@.service /etc/systemd/system/ && sudo systemctl daemon-reload`. Ne rien activer.
5. DNS (OVH) : `dev.cryptobook.fr` A → `92.222.87.225` (VPS1). `dig +short dev.cryptobook.fr`. `cryptobook.fr` ne bouge pas avant le point 2.
6. `sudo bash verify-vps.sh --role=vps1 --steps=19 --site=cryptobook --domaine=cryptobook.fr` (idem vps2) : 0 ✗.

## Application (`config/`)

- `config/packages/framework.yaml` :
  ```yaml
  session:
      handler_id: session.handler.native_file
      save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'
  ```
  Le `when@test` (`mock_file`) ne change pas. En prod, `var/sessions` est un dossier partagé : les sessions survivent aux déploiements.
- `config/packages/monolog.yaml`, `when@prod`, handler `nested` : `type: rotating_file`, `max_files: 14`, même `path`, même `level`, même `formatter`. Fichiers produits : `var/log/prod-YYYY-MM-DD.log`.

## Deployer (`deploy.php`)

Copie du template, puis :

```php
require 'recipe/symfony.php';

set('repository', getenv('DEPLOY_REPOSITORY'));
set('shared_files', ['.env.local']);
set('shared_dirs', ['var/log', 'var/sessions']);
set('writable_dirs', ['var']);
set('http_user', 'deployer');

host(getenv('DEPLOY_HOST'))  // inchangé
    ...;

task('app:assets', function () {
    run('{{bin/console}} tailwind:build --minify');
    run('{{bin/console}} asset-map:compile');
});

// The running worker uses the previous release path, hence its cache namespace:
// the restart signal must be sent from there. systemd restarts it in `current`.
task('app:stop-workers', function () {
    if (!has('previous_release')) {
        return;
    }
    run('{{bin/php}} {{previous_release}}/bin/console messenger:stop-workers --no-interaction');
});

task('frankenphp:reload', function () {
    run('sudo systemctl reload frankenphp');
});

after('deploy:vendors', 'app:assets');
after('app:assets', 'database:migrate');
after('deploy:symlink', 'frankenphp:reload');
after('frankenphp:reload', 'app:stop-workers');
after('deploy:failed', 'deploy:unlock');
```

- `set()` et non `add()` : on remplace les listes de la recipe, sans `var/cache` ni `.env.local.php`.
- `importmap:install` tourne déjà dans les `auto-scripts` de `deploy:vendors` : ne pas le relancer.
- `tailwind:build` avant `asset-map:compile`, sinon le CSS compilé est vide.
- Pas de `bin/php` : PHP système (`which php`).
- Supprimer les tâches `database:rollback` et `rollback:full` du template.

## Pipeline (`.gitlab-ci.yml`, `ci/`)

- `ci/check-env.sh`, `ci/common.sh`, `ci/deploy.sh`, `ci/setup-ssh.sh` : copiés du template sans modification.
- `ci/rollback.sh` : `dep rollback` puis `dep frankenphp:reload`. Le worker reprend l'ancien code au plus tard à son `--time-limit` (1 h).
- `.gitlab-ci.yml` : `workflow`, `stages`, caches et jobs `deploy_*` / `rollback_*` repris du template. Différences :

  | Élément | Template | CryptoBook |
  |---|---|---|
  | `PHP_VERSION` | `8.4` | `8.5` |
  | `.base_template.before_script` | git, unzip, make, Composer | git, unzip, `install-php-extensions` (release `latest` de `mlocati/docker-php-extension-installer`, comme le `:2` du Dockerfile) puis `install-php-extensions intl pdo_pgsql zip`, Composer, `composer install` |
  | `lint:php-cs-fixer` | `make cs-ci` | `vendor/bin/php-cs-fixer fix --dry-run --diff --format=gitlab`, sortie dans le rapport du template |
  | `lint:symfony` | `make lint-symfony` | `composer validate`, `bin/console lint:container`, `lint:twig templates`, `lint:yaml config --parse-tags`, `doctrine:schema:validate --skip-sync` |
  | `lint:security` | `make security-audit` | `composer audit --locked` |
  | `analyze:phpstan` | `make phpstan-ci` | `vendor/bin/phpstan analyse --error-format=gitlab --memory-limit=1G` (le conteneur dev XML est construit par le `cache:clear` de `composer install`) |
  | `analyze:rector` | `make rector-check` | `vendor/bin/rector process --dry-run` |
  | `analyze:deptrac` | absent | `vendor/bin/deptrac analyse` |
  | `test:phpunit` | `make phpunit-ci` | voir ci-dessous |

  ```yaml
  test:phpunit:
    extends: .test_template
    services:
      - name: postgres:18-alpine
        alias: postgres
    variables:
      POSTGRES_DB: app
      POSTGRES_USER: app
      POSTGRES_PASSWORD: app
      DATABASE_URL: "postgresql://app:app@postgres:5432/app?serverVersion=18&charset=utf8"
    script:
      - bin/console tailwind:build
      - bin/console doctrine:database:create --env=test --if-not-exists -n
      - bin/console doctrine:migrations:migrate --env=test -n
      - bin/phpunit --log-junit var/reports/phpunit/junit.xml
  ```

  `tailwind:build` est requis : le compilateur AssetMapper du bundle est en mode strict et le smoke test rend `app.css`. La base créée est `app_test` (`dbname_suffix`).

## Worker (`deploy/systemd/`)

`git mv deploy/systemd/cryptobook-worker.service deploy/systemd/cryptobook-worker@.service` :

```ini
# CryptoBook worker: Scheduler (src/Shared/Schedule/MainSchedule.php) + async messages.
# Instance = Deployer stage: cryptobook-worker@prod on VPS2. Never enable @dev on VPS1 (shared Fixer quota).
#   sudo cp deploy/systemd/cryptobook-worker@.service /etc/systemd/system/ && sudo systemctl daemon-reload
#   sudo systemctl enable --now cryptobook-worker@prod
# Restarted on each deployment by the Deployer task app:stop-workers. Logs: journalctl -u cryptobook-worker@prod -f

[Unit]
Description=CryptoBook worker (%i)
After=network-online.target postgresql.service
Wants=network-online.target

[Service]
Type=simple
User=deployer
Group=deployer
WorkingDirectory=/var/www/cryptobook/%i/current
Environment=APP_ENV=prod
ExecStart=/usr/bin/php bin/console messenger:consume scheduler_default async --time-limit=3600 --memory-limit=192M --no-interaction
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

`--memory-limit=192M` reste sous le `memory_limit` du PHP système (256M, étape 08) : Messenger ne contrôle la mémoire qu'entre deux messages.

## Comptage des lignes (`deploy/sql/row-counts.sql`)

Servira au staging et à la bascule :

```sql
-- Exact row count per table, sorted: psql -X -At -d <db> -f deploy/sql/row-counts.sql > counts.txt
SELECT format('SELECT %L || '' '' || count(*) FROM %I.%I', tablename, schemaname, tablename)
FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename
\gexec
```

## Staging (après le premier `deploy_dev` vert)

1. Sur l'ancienne prod (lecture seule, rien n'y est modifié) : `pg_dump -Ft -f /tmp/cryptobook.tar <base>`, puis tout de suite `psql -X -At -d <base> -f row-counts.sql > /tmp/counts-old.txt`. Noter `psql --version`. Rapatrier les deux fichiers sur VPS1 (`scp -P 2222`). `prod.tar.gz` (10/11) n'est pas utilisé : trop ancien.
2. Restaurer :
   ```bash
   sudo -u postgres dropdb --if-exists cryptobook_dev
   sudo -u postgres createdb -O cryptobook_dev cryptobook_dev
   sudo -u postgres pg_restore -d cryptobook_dev --no-owner --no-privileges --role=cryptobook_dev --schema=public /tmp/cryptobook.tar
   ```
3. Avant les migrations : `psql -X -At -d cryptobook_dev -f deploy/sql/row-counts.sql > /tmp/counts-new.txt`, puis `diff /tmp/counts-old.txt /tmp/counts-new.txt`. Attendu : aucune différence. Une différence est à expliquer avant la bascule : c'est la répétition du contrôle du point 2.
4. `cd /var/www/cryptobook/dev/current` : `php bin/console doctrine:migrations:status`, puis `doctrine:migrations:migrate -n`, puis `doctrine:schema:validate`. Ne jamais marquer une version à la main.
5. Relancer le job `deploy_dev` (vide le cache du dashboard), puis le smoke test : `/`, login, dashboard, une liste, une page EasyAdmin, CSS chargé, `bin/console about` (`Environment prod`, `Debug false`).
6. Worker, sans l'activer :
   ```bash
   php bin/console crypto:updatePrices
   /usr/bin/time -v php bin/console wallet:snapshot    # mémoire max < 192M
   sudo systemctl start cryptobook-worker@dev           # quelques minutes
   ```
   Pendant que le worker tourne, relancer `deploy_dev` : `journalctl -u cryptobook-worker@dev` doit montrer un arrêt puis un redémarrage dans la nouvelle release (`readlink /proc/<pid>/cwd`). Puis `sudo systemctl stop cryptobook-worker@dev`.
7. Contrôler : `ls /var/www/cryptobook/dev/shared/var/sessions/prod` (une session après login), `var/log/prod-*.log` présent.

## Ordre des commits

1. `fix: rotate the prod log` : `monolog.yaml`.
2. `feat: keep sessions across deployments` : `framework.yaml`.
3. `build: add the Deployer recipe` : `deploy.php`.
4. `ci: add the GitLab pipeline` : `.gitlab-ci.yml`, `ci/*.sh`.
5. `build: adapt the worker unit to the Deployer layout` : `deploy/systemd/cryptobook-worker@.service`.
6. `build: add the row count script` : `deploy/sql/row-counts.sql`.
7. `docs: document the deployment` : `CLAUDE.md`, `doc/roadmap.md`, `doc/bug.md`.

## Tests

Pas de test PHP : la MR ne touche que de la config et de l'outillage. La preuve est le pipeline vert sur la MR (lint, analyse, tests avec Postgres 18), puis le premier `deploy_dev` vert et les contrôles du staging ci-dessus.

## Documentation

- `CLAUDE.md` :
  - « Projet » : worker `cryptobook-worker@prod` (`deploy/systemd/`).
  - Nouvelle section « Déploiement » : GitLab `smbai/cryptobook` (MR, `glab`), GitHub en miroir, push `dev` → staging `dev.cryptobook.fr`, tag `vX.Y.Z` → prod, `deploy.php`, PHP système pour la CLI.
  - « Langue » : « descriptions de MR ».
- `doc/roadmap.md`, point 1 :
  - « Un seul PHP… » devient « PHP 8.5 partout : FrankenPHP pour le web, PHP système pour Deployer, la CLI et le worker » ;
  - la ligne sauvegarde et ping devient « Sauvegarde quotidienne par l'étape 17 du framework (multi-sites) ».
- Nouveau point de roadmap « Ping de vie » : contrôle horaire de `wallet_snapshot` récent vers un dead-man's-switch externe, alerte si le ping manque.
- `doc/bug.md` :
  - retirer « `var/log/prod.log` grossit sans rotation » et « Staging : worker actif ou non » (tranchés ici) ;
  - ajouter sous « Repéré, non tranché » : « Docker local en Postgres 16, serveurs et CI en 18 ».

## Hors périmètre

- Bascule de la prod, activation du worker de prod, DNS de `cryptobook.fr` et `www` : point 2.
- Ping de vie : point de roadmap à part.
- Mode worker de FrankenPHP, previews par branche, montée de Postgres en local.
- `make ci` (point 4) : la CI n'utilise pas le `Makefile`.

## À vérifier

- Nom exact des paquets `php-zts-*` et redémarrage de FrankenPHP (P1).
- `create-app.sh` sur un dépôt déjà peuplé : relire le `--dry-run`, aucune branche ne doit être poussée.
- Nom de la variable de clé SSH du groupe : `SSH_PRIVATE_KEY` (exigée par `ci/check-env.sh`), pas `SSH_PRIVATE_KEY_FILE`.
- `writable_dirs` en mode ACL (défaut Deployer) : `setfacl` présent sur les VPS (le template déploie déjà sf-template).
- `memory_limit` réel du PHP système, à comparer aux 192M du worker.
- `pg_restore` : le dump vient d'un Postgres plus ancien que 18. Une erreur sur `--role` ou sur une extension stoppe la restauration ; relire la sortie.
