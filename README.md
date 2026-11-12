# CryptoBook

Crypto portfolio tracker: positions, deposits, loans, NFTs, strategies (DCA, farming, LP) and project follow-up.

## Stack

- **Symfony** 8.1, **PHP** 8.5, **PostgreSQL** 16
- **Front**: Twig, AssetMapper, Stimulus, Turbo, Tailwind CSS 4, shadcn kit (UX Toolkit)
- **Back-office**: EasyAdmin 5
- **Data**: CoinGecko (crypto prices), Fixer (fiat rates), refreshed by Symfony Scheduler

## Getting started

Requires Docker. Put `APP_SECRET` and `FIXER_API_KEY` in `.env.local`.

```bash
make build
make up
docker compose exec php composer install
make migrate
make fixtures
make tailwind
make url
```

Fixtures users log in with the password `password` (`make users:show` to list them).

## Commands

- `make help`: list every target
- `make test`: run PHPUnit (`c="--filter …"` to filter)
- `make ci`: run every check (lints, static analysis, tests)
- `make fix`: fix the code style (Rector, PHP CS Fixer)
- `make icons`: regenerate the PNG icons from `assets/images/favicon.svg`
- `make tailwind c=--watch`: rebuild the CSS on change
- `make scheduler`: run the price and rate updates on demand (no permanent worker in dev)
- `make bash`: shell into the `php` container

## Documentation

- `CLAUDE.md`: architecture, code organization and conventions
- `doc/roadmap.md`: planned work
