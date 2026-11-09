# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Current work: Symfony 6.3 → 8.1 upgrade

@doc/upgrade-symfony-8.md

- Work one phase at a time, one MR per phase, in the order of the table. Don't pull work from a later phase into an earlier one (e.g. no Asset Mapper before phase 5, no ORM 3 before phase 7).
- When a phase is done, update its status in the tracking table.
- Phase 1 removes the API entirely (API Platform, JWT, CORS, `CurrentUserExtension`): don't extend or fix API code before then.

## Language

- Code, comments, commit messages, MR descriptions: English. The existing history is in French — don't copy it.
- `doc/` stays in French.

## Checking changes

No tests, PHPStan or CS Fixer yet (they arrive in phase 2). Until then, check with:

```
bin/console lint:container
bin/console lint:twig templates
bin/console lint:yaml config
bin/console doctrine:schema:validate
```

## Local setup gotchas

- The local environment isn't working yet (phase 0 fixes it): `docker-compose.yml` has no database, and `.env` points to a Postgres on `127.0.0.1`.
- Host PHP is 8.5, the project targets 8.1 (then 8.3/8.4). Don't run `composer update` on the host: it resolves dependencies for the wrong PHP version.
- Frontend is Webpack Encore (`npm run dev` / `watch` / `build`) until phase 5.

## Data

`init_bdd.sql` contains real user emails and password hashes. Never copy from it into fixtures, tests or docs: use `src/DataFixtures` with Faker.
