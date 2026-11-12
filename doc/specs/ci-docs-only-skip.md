# Plan : légère optimisation de la CI

Couvre le point 1 de `roadmap.md`. Seul le bloc `workflow:rules` de `.gitlab-ci.yml` change : aucun job, aucun script de `ci/`, aucun réglage du projet GitLab n'est modifié.

## État des lieux

- `.gitlab-ci.yml` (lignes 1 à 5) crée une pipeline dans trois cas, sans condition sur les fichiers : MR, tag `vX.Y.Z`, push sur `dev`.
- Une pipeline complète = 8 jobs (3 lint, 3 analyse, 1 test, 1 deploy), dont 6 refont `apt-get` + `install-php-extensions` + `composer install`.
- Fichiers de doc versionnés : `doc/` (2 fichiers) et 3 `.md` à la racine (`README.md`, `CLAUDE.md`, `AGENTS.md`). Aucun `.md` ailleurs.
- GitLab.com est en 19.6 : `rules:changes:regexp` (19.2) est disponible. Les globs de `rules:changes:paths` ne savent pas exclure un chemin.
- Config cible validée par l'API CI Lint du projet (`valid: true`, aucun warning).
- Projet GitLab : `only_allow_merge_if_pipeline_succeeds = false`. Une MR sans pipeline reste fusionnable.
- Fichiers comparés (vérifié dans le code de GitLab) : toute la MR contre sa branche cible pour une pipeline de MR, tout le push (`before..after`) pour une pipeline de branche.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Mécanisme | `rules:changes:regexp` / liste de globs des dossiers de code / `[skip ci]` dans le commit | `rules:changes:regexp` : une seule ligne, un nouveau dossier de code est couvert sans y penser |
| Niveau | `workflow:rules` / `rules` de chaque job | `workflow:rules` : aucune pipeline créée, même vide |
| Fichiers de doc | `doc/` + `.md` de la racine / `doc/` + tout `.md` | `doc/` en entier (images comprises) et tout fichier `.md`, à n'importe quelle profondeur |
| Changement mixte (doc + code) | — | Pipeline complète |
| Tag `vX.Y.Z` | — | Règle inchangée, sans `changes` : toujours une pipeline |
| Ordre des règles | — | Inchangé (MR, tag, `dev`) |

## Pipeline (`.gitlab-ci.yml`)

Remplacer le bloc `workflow` par :

```yaml
# No pipeline when only doc/ and .md files change. Tags always run.
workflow:
  rules:
    - if: $CI_PIPELINE_SOURCE == "merge_request_event"
      changes: &code-changes
        regexp: '\A(?!doc/)(?!.*\.md\z).*'
    - if: $CI_COMMIT_TAG =~ /^v\d+\.\d+\.\d+$/
    - if: $CI_COMMIT_BRANCH == "dev"
      changes: *code-changes
```

- La regexp est vraie dès qu'un fichier modifié n'est ni sous `doc/` ni un `.md`. Ancres `\A` et `\z`, pas `^` et `$` (recommandation de la doc GitLab).
- Les `rules` des jobs `deploy_*` et `rollback_*` ne changent pas.

Comportement attendu :

| Cas | Fichiers modifiés | Pipeline |
|---|---|---|
| MR | `doc/roadmap.md`, `CLAUDE.md` | non |
| MR | `doc/specs/x.md` + `src/Foo.php` | oui |
| MR | `.gitlab-ci.yml` seul | oui |
| MR | `templates/a.md.twig` | oui |
| Push sur `dev` (merge d'une MR de doc) | `README.md` | non, pas de déploiement du staging |
| Push sur `dev` de plusieurs commits, dont un de code | `src/…` + `doc/…` | oui |
| Tag `v1.2.3` | peu importe | oui |

## Documentation

- `CLAUDE.md`, section « Déploiement », ligne « Pipeline `.gitlab-ci.yml` » : ajouter à la fin `Pas de pipeline si seuls `doc/` et des fichiers `.md` changent (MR et push sur `dev`).`
- `doc/roadmap.md` : supprimer le point 1 et la ligne `Spec`, renuméroter les points suivants (2 → 1 … 6 → 5), compléter la ligne « Déploiement » de « Fait » avec `, pas de pipeline pour un changement de doc seule`.
- Supprimer `doc/specs/ci-docs-only-skip.md` dans le même commit (comme pour les specs précédentes).

## Ordre des commits

1. `ci: skip the pipeline for docs-only changes` : `.gitlab-ci.yml`, `CLAUDE.md`.
2. `docs: mark the CI docs-only skip as done` : `doc/roadmap.md`, suppression de la spec.

## Tests

Pas de test PHPUnit : rien d'exécutable dans l'application. Vérification avant le push, avec `glab` :

```
python3 -c 'import json;print(json.dumps({"content":open(".gitlab-ci.yml").read()}))' > /tmp/lint.json
glab api --method POST projects/smbai%2Fcryptobook/ci/lint --input /tmp/lint.json -H 'Content-Type: application/json'
```

Attendu : `"valid": true`, `"errors": []`, `"warnings": []`.

## Hors périmètre

- Accélérer les jobs (image avec extensions préinstallées, cache) : la future pipeline du point `make ci`.
- Sauter seulement certains jobs selon les fichiers (ex. pas de PHPUnit pour du CSS).
- Réglage « Pipelines must succeed » du projet : il reste désactivé. S'il est activé un jour, cocher aussi « Skipped pipelines are considered successful », sinon les MR de doc seront bloquées.

## À vérifier

- La MR de ce point modifie `.gitlab-ci.yml` : elle doit avoir une pipeline complète, verte.
- Après le merge : le push sur `dev` lance une pipeline et déploie le staging (le commit 1 touche du code CI).
- Première MR de doc suivante : onglet « Pipelines » vide, bouton « Merge » actif, aucun déploiement après le merge. Si une pipeline se lance quand même, relever les fichiers modifiés de la MR avant de toucher à la regexp.
