# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Current work: Symfony 6.3 → 8.1 upgrade

@doc/upgrade-symfony-8.md

- Everything happens on a single branch, `chore/upgrade-symfony-8`, merged into `main` at the end. Work one phase at a time, in the order of the table, with dedicated commits per phase (never mix two phases in one commit). Don't pull work from a later phase into an earlier one (e.g. no Asset Mapper before phase 5, no ORM 3 before phase 7).
- When a phase is done, update its status in the tracking table.
- Phase 1 removes the API entirely (API Platform, JWT, CORS, `CurrentUserExtension`): don't extend or fix API code before then.

## Language

- Code, comments, commit messages, MR descriptions: English. The existing history is in French — don't copy it.
- `doc/` stays in French.
- Commit messages follow Conventional Commits (`feat:`, `fix:`, `chore:`, `refactor:`, `docs:`, `test:`, `build:`…).

## Checking changes

Inside the `php` container:

```
bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer fix --dry-run
vendor/bin/rector process --dry-run
bin/console lint:container
bin/console lint:twig templates
bin/console lint:yaml config
bin/console doctrine:schema:validate
```

- PHPStan uses a baseline (`phpstan-baseline.neon`): don't add new errors to it, fix them. Regenerate it only when errors are removed.
- Rector must stay clean (`--dry-run` with no changes). Constructor promotion is skipped on `src/Entity` (it can widen the types Doctrine uses to infer relations).
- Tests run on `app_test` (DAMA rolls back each test). After a new migration: `bin/console doctrine:migrations:migrate --env=test -n`.

## Local setup gotchas

- Run everything in Docker: `docker compose up -d`, then `docker compose exec php composer install` / `bin/console …`. App on http://localhost:8888. PHP version is a build arg: `PHP_VERSION=8.4 docker compose build php`.
- The `php` service overrides `DATABASE_URL` to the `database` service (Postgres 16); `app_test` is created on first start. `.env` still points to `127.0.0.1` for host use.
- Host PHP is 8.5, the project targets 8.1 (then 8.3/8.4). Don't run `composer update` on the host: it resolves dependencies for the wrong PHP version.
- Frontend is AssetMapper + importmap (no Node): add JS packages with `bin/console importmap:require`, page scripts are entrypoints in `importmap.php`.

## Data

`init_bdd.sql` contains real user emails and password hashes. Never copy from it into fixtures, tests or docs: use `src/DataFixtures` with Faker.
