# Plan : `make ci` et Symfony Language Tools

Couvre le point 3 de `roadmap.md`. Touche le Makefile, l'image Docker `php` et la doc. `.gitlab-ci.yml` ne change pas, aucun code applicatif ne change sauf correction d'un diagnostic remonté par le nouvel outil.

## État des lieux

- La vérification complète est une liste de 11 commandes dans `CLAUDE.md` (« Commandes qualité »), à lancer une par une dans le conteneur. Seule cible Makefile de qualité : `make test`.
- Toutes passent aujourd'hui, sauf une (voir « Corrections »). Durées mesurées dans le conteneur : 0 à 2 s par contrôle statique, PHPUnit 19 s (241 tests), `tailwind:build` 1 s. `make ci` complet : environ 30 s hors Symfony Language Tools.
- La pipeline GitLab lance les mêmes outils avec des options un peu différentes : `composer validate --no-check-publish`, `composer audit --locked`, `lint:yaml config --parse-tags`, `doctrine:schema:validate --skip-sync` (pas de base dans le job de lint).
- Symfony Language Tools (`symfony/language-tools`, bêta) : dernière version `v0.24.2` (22/10/2026). Exécutable autonome `symfony-lsp`, archives `linux-arm64` et `linux-x64` liées statiquement (glibc et musl). Vérifié : `symfony-lsp --version` s'exécute dans le conteneur `php` (Alpine 3.24, arm64).
- `symfony-lsp check` démarre l'application (env `dev`, debug) avec le `php` du `PATH`, analyse `src/`, `config/`, `templates/`… et écrit son index dans `var/symfony-lsp/` (déjà ignoré par `/var/`). 31 codes de diagnostic (`symfony-lsp check --list-codes`).
- Codes de sortie : `0` rien de bloquant, `10` diagnostics bloquants, `11` appel ou config invalide, `12` analyse incomplète (l'application ne démarre pas, timeout). Par défaut, les diagnostics de niveau erreur bloquent, les warnings non.
- Non mesuré : le nombre de diagnostics sur le projet (l'exécution du binaire sur le code a été refusée pendant la rédaction). Voir « Premier lancement ».
- Doc de référence : `docs/check.rst`, `docs/configuration.rst`, `docs/docker.rst` du dépôt `symfony/language-tools`.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Installation de `symfony-lsp` | image Docker `php` / Symfony CLI sur l'hôte / binaire sur l'hôte | Image Docker `php`, version et sha256 épinglés dans `docker/php/Dockerfile` |
| Diagnostics de niveau erreur | bloquants / informatifs (`--fail-on=`) | Bloquants : comportement par défaut, aucune option `--fail-on` |
| Diagnostics existants | tout corriger / baseline | Tout corriger, pas de `.symfony-lsp-baseline.json`. Faux positif : commentaire `@symfony-lsp-ignore <code> (raison)` |
| Cible de correction | `make fix` / rien | `make fix` : Rector puis PHP CS Fixer |
| Emplacement des cibles | `make/docker.mk` / nouveau fichier | Nouveau `make/qa.mk`, inclus dans `Makefile` après `make/docker.mk` |
| Granularité | une recette unique / une cible par outil | Une cible par outil, `ci` les enchaîne : arrêt au premier échec, `make -k ci` pour tout dérouler |
| Options des commandes | celles de `CLAUDE.md` / celles de la pipeline | Celles de la pipeline, sauf `doctrine:schema:validate` (ci-dessous) |
| `doctrine:schema:validate` | base `dev` / `--skip-sync` / base de test | `--env=test`, après migration de `app_test` : vérifie le mapping et que les migrations donnent le bon schéma, sans dépendre de l'état de la base `dev` |
| Préparation de PHPUnit | à la charge du développeur / dans `make ci` | Dans `make ci` : `tailwind:build` et migration de `app_test`. La base `dev` n'est jamais migrée par `make ci` |
| Fichier `.symfony-lsp.json` | créé / non | Non créé : les valeurs par défaut conviennent (`php`, env `dev`, application à la racine) |
| Diagnostics de traduction | activés / non | Non activés (valeur par défaut de l'outil) |
| Job GitLab | ajouté / non | Non : la pipeline ne change pas |

## Image Docker (`docker/php/Dockerfile`)

Ajouter après le `RUN apk add …` :

```dockerfile
# Symfony Language Tools (beta), run by `make ci`. Static binary: same archive on glibc and musl.
ARG SYMFONY_LSP_VERSION=0.24.2
ARG SYMFONY_LSP_SHA256_ARM64=61ab69f7dbfe2a3216584fdde9e2247e56085461796f96fa1e14711dcb729400
ARG SYMFONY_LSP_SHA256_X64=d7e39420ad9058fb1edeaf13f0846c5e7c351467b90702336c12b8ce13f8a09f

RUN set -eux; \
    case "$(uname -m)" in \
        aarch64) arch=arm64; sha="$SYMFONY_LSP_SHA256_ARM64" ;; \
        x86_64) arch=x64; sha="$SYMFONY_LSP_SHA256_X64" ;; \
        *) echo "Unsupported architecture: $(uname -m)"; exit 1 ;; \
    esac; \
    name="symfony-lsp-v${SYMFONY_LSP_VERSION}-linux-${arch}"; \
    curl -sSfL -o /tmp/symfony-lsp.tar.gz "https://github.com/symfony/language-tools/releases/download/v${SYMFONY_LSP_VERSION}/${name}.tar.gz"; \
    echo "${sha}  /tmp/symfony-lsp.tar.gz" | sha256sum -c -; \
    tar -xzf /tmp/symfony-lsp.tar.gz -C /tmp; \
    mv "/tmp/${name}/symfony-lsp" /usr/local/bin/symfony-lsp; \
    rm -rf /tmp/symfony-lsp.tar.gz "/tmp/${name}"; \
    symfony-lsp --version
```

- Les deux sha256 viennent du fichier `SHA256SUMS` de la release `v0.24.2`. Celui de l'archive arm64 a été recalculé après téléchargement.
- `curl`, `tar` et `sha256sum` sont déjà dans l'image.
- Montée de version : changer les trois `ARG`, puis `make build`.
- Rien dans `docker-compose.yml`, `docker/nginx/`, ni dans les images de la CI et des serveurs.

## Makefile

`Makefile` : ajouter `include make/qa.mk` entre les deux `include` existants.

`make/qa.mk` (nouveau), section d'aide `## ---- Quality ----`, toutes les cibles dans `.PHONY`. Chaque cible affiche une ligne de titre (`@echo "==> PHPStan"`) puis lance sa commande via `$(EXEC)`, `$(COMPOSER)`, `$(SYMFONY)` ou `$(PHP)`.

| Cible | Aide (`##`) | Commandes, dans l'ordre |
|---|---|---|
| `test-db` | Migrate the test database | `bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration --env=test` |
| `composer-check` | Validate composer.json and audit the dependencies | `composer validate --no-check-publish`, `composer audit --locked` |
| `lint` | Lint the container, Twig, YAML and the Doctrine mapping (test database) | `bin/console lint:container`, `bin/console lint:twig templates`, `bin/console lint:yaml config --parse-tags`, `bin/console doctrine:schema:validate --env=test` |
| `cs-fixer` | Check the code style (PHP CS Fixer, dry run) | `vendor/bin/php-cs-fixer fix --dry-run --diff` |
| `rector` | Check Rector rules (dry run) | `vendor/bin/rector process --dry-run` |
| `phpstan` | Run PHPStan | `vendor/bin/phpstan analyse` |
| `deptrac` | Check the dependencies between features (Deptrac) | `vendor/bin/deptrac analyse` |
| `symfony-lsp` | Run the Symfony Language Tools diagnostics (beta) | garde, puis `symfony-lsp check` |
| `fix` | Fix the code: Rector, then PHP CS Fixer | `vendor/bin/rector process`, `vendor/bin/php-cs-fixer fix` |
| `ci` | Run every check: composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, Symfony Language Tools, PHPUnit. `make -k ci` to keep going after a failure | aucune recette, seulement des prérequis |

```make
ci: tailwind test-db composer-check lint cs-fixer rector phpstan deptrac symfony-lsp test
```

- L'ordre des prérequis est l'ordre d'exécution (pas de `-j`). `tailwind` et `test` sont les cibles existantes de `make/docker.mk`, inchangées.
- `lint` suppose `app_test` migrée : c'est le cas dans `make ci`. Lancée seule après une nouvelle migration : `make test-db lint`.
- Garde de `symfony-lsp`, avant le contrôle : si `$(EXEC) sh -c 'command -v symfony-lsp'` échoue, afficher `symfony-lsp not found in the php image: run "make build" (in the main repo for a worktree), then "make up".` et sortir en `1`. Les worktrees réutilisent l'image du dépôt principal (`setup-worktree`), elle peut dater d'avant cette PR.
- `symfony-lsp check` sans argument ni option : format `human`, répertoire courant `/app`, env `dev`. Tout code de sortie non nul fait échouer la cible, `12` compris.
- `make migrate` ne change pas (il garde sa propre commande pour la base de test).

## Premier lancement de `symfony-lsp check`

À faire après `make build && make up`, avant d'écrire le reste :

| Résultat | Action |
|---|---|
| Sortie `0`, aucun warning | Rien |
| Sortie `12` | Relancer avec `--verbose`, corriger la cause du démarrage. Ne pas passer en `--source-only` |
| Erreurs, vrai problème (route, template, service, option inexistants…) | Corriger le code, commit `fix:` dédié (voir « Corrections ») |
| Erreurs, faux positif confirmé à l'exécution (la page ou la commande fonctionne) | Commentaire `@symfony-lsp-ignore <code> (raison)` sur la ligne, en PHP, Twig ou YAML, raison en anglais |
| Plus de 15 diagnostics de niveau erreur | S'arrêter et les lister à l'utilisateur avant toute correction |
| Warnings (`config.unknown_key`, `config.invalid_type`, `env.incompatible_type`…) | Non bloquants : ne pas les corriger, les lister dans la description de la MR |

Interdits : `--generate-baseline`, `--fail-on=`, `excludePaths`, désactiver un code globalement.

## Corrections

- `composer validate`, tel qu'écrit dans `CLAUDE.md`, sort en `2` (« name / description : property is required », contrôle de publication sans objet pour un projet). `make composer-check` utilise `--no-check-publish` comme la pipeline, et la liste de commandes disparaît de `CLAUDE.md`. Pas de commit séparé.
- Diagnostics réels trouvés au premier lancement : un commit `fix(<feature>): …` par sujet, avant le commit qui ajoute `symfony-lsp` à `make ci`. Si le correctif change un comportement (route ou template réellement cassé), ajouter le test fonctionnel qui le reproduit.

## Documentation

`CLAUDE.md` :

- Section « Commandes qualité », remplacée en entier par :

  ```markdown
  ## Commandes qualité

  - `make ci` : vérification complète (composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, Symfony Language Tools, PHPUnit). S'arrête au premier échec, `make -k ci` pour tout dérouler. Une étape seule : `make lint`, `make phpstan`… (`make help`).
  - `make fix` : Rector puis PHP CS Fixer.
  - Symfony Language Tools (`symfony-lsp check`, bêta) : binaire installé dans l'image `php`, version épinglée dans `docker/php/Dockerfile` (`make build` après un changement). Il démarre l'application en `dev` et signale routes, templates, services ou options inexistants.

  Tout est propre, sans baseline (PHPStan comme Symfony Language Tools) : corriger les erreurs, ne jamais les ignorer. Faux positif de `symfony-lsp` : `@symfony-lsp-ignore <code> (raison)` sur la ligne.
  ```

- Section « Environnement », ligne « Raccourcis » : `make up`, `make url`, `make migrate`, `make test`, `make ci`…

`README.md`, section « Commands », après la ligne `make test` :

```markdown
- `make ci`: run every check (lints, static analysis, tests)
- `make fix`: fix the code style (Rector, PHP CS Fixer)
```

`doc/roadmap.md` : supprimer le point 3 et sa ligne `Spec`, renuméroter les points suivants, compléter la ligne « Socle » de « Fait » avec `, vérification complète par `make ci` (Symfony Language Tools compris)`. Supprimer `doc/specs/make-ci-symfony-lsp.md` dans le même commit.

## Ordre des commits

1. `build: add the make ci and make fix targets` : `Makefile`, `make/qa.mk` sans la cible `symfony-lsp` ni son prérequis dans `ci`. `make ci` vert.
2. `build(docker): install Symfony Language Tools in the php image` : `docker/php/Dockerfile`.
3. `fix(<feature>): …` : zéro, un ou plusieurs commits, selon le premier lancement.
4. `build: run Symfony Language Tools in make ci` : cible `symfony-lsp`, ajoutée aux prérequis de `ci`.
5. `docs: point to make ci for the quality checks` : `CLAUDE.md`, `README.md`.
6. `docs: mark make ci as done` : `doc/roadmap.md`, suppression de la spec.

## Tests

Pas de test PHPUnit : rien d'exécutable dans l'application (hors correctif du commit 3). Vérifications manuelles, à rapporter dans la MR :

- `make ci` : sortie `0`, les 8 titres des nouvelles étapes affichés dans l'ordre des prérequis (`tailwind` et `test`, inchangées, n'en ont pas).
- Étape cassée : remplacer un nom de route par `app_does_not_exist` dans un `path()` de `templates/dashboard/` → `make symfony-lsp` sort en erreur avec `route.not_found`, `make ci` s'arrête avant PHPUnit, `make -k ci` lance quand même PHPUnit et sort en erreur. Annuler la modification.
- Garde : `docker compose exec -T php sh -c 'command -v symfony-lsp'` répond `/usr/local/bin/symfony-lsp`.
- `make help` liste les 10 nouvelles cibles sous leur nom exact.
- `git status` propre après `make ci` (rien de généré hors `var/`).

## Hors périmètre

- Job `symfony-lsp` dans `.gitlab-ci.yml`, image de CI avec extensions préinstallées, pipeline qui appelle `make` : la future pipeline.
- Diagnostics de traduction (`translationDiagnostics`).
- Intégration à un éditeur (extension VS Code, LSP4IJ), `.symfony-lsp.json`.
- Alignement de Postgres local (16) sur la CI (18) : déjà dans `doc/bug.md`.
- Hook de pre-commit.

## À vérifier

- Nombre et nature des diagnostics du premier lancement (non mesurés), et durée de `symfony-lsp check` à froid puis à chaud, à noter dans la MR.
- Le `RUN` du Dockerfile sur une machine x86_64 n'a pas été testé : seul le sha256 arm64 a été recalculé.
- `symfony-lsp check` démarre l'application en `dev` : vérifier qu'il n'appelle ni CoinGecko ni Fixer (quota), et qu'il ne laisse rien hors de `var/symfony-lsp/` et `var/cache/`.
- Avant l'implémentation, regarder si une version plus récente que `v0.24.2` est sortie : si oui, prendre la dernière et ses sha256 (`SHA256SUMS` de la release).
