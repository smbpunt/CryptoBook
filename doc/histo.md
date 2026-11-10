# Plan — Historique de la valeur du wallet : collecte

## Découpage : 2 PR

- **PR 1 — calcul + snapshot + commande `wallet:snapshot`.** Déployable seule avec une ligne de crontab en plus : la collecte démarre tout de suite.
- **PR 2 — Scheduler + systemd.** Remplace la crontab.

## PR 1 — Calcul centralisé et snapshot (`src/Wallet/`)

### Calcul
- `PositionRepository` : une requête groupée par propriétaire et coin, filtrable par utilisateur. Elle remplace les deux appels à `getSumCoinByUser()` du dashboard.
- `Wallet/Dto/WalletHolding` : coin, quantité, prix, valeur USD, part en %.
- `Wallet/Dto/WalletValuation` : holdings crypto, holdings stables, `cryptoUsd`, `stableUsd`, `totalUsd`.
- `Wallet/Calculator/WalletValueCalculator` : `forUser(User)` pour le dashboard, `forAllUsers()` pour les snapshots (une seule requête).
- `CryptobookController::index()` passe par le calculateur. La conversion en devise favorite reste à l'affichage.
- Corrigé au passage : division par zéro quand le total vaut 0.

### Snapshot
- `Wallet/Entity/WalletSnapshot` : `owner`, `recordedAt` (`datetimetz_immutable`, tronqué à l'heure), `valueUsd` (float). Index unique `(owner_id, recorded_at)`.
- `Wallet/Repository/WalletSnapshotRepository`.
- `Wallet/Recorder/WalletSnapshotRecorder::record()` : via `ClockInterface`, ignore les utilisateurs déjà enregistrés pour l'heure en cours.
- `Wallet/Command/WalletSnapshotCommand` (`wallet:snapshot`).
- Règle : un snapshot pour chaque utilisateur ayant au moins une position, ouverte ou non (0 si tout est clôturé).
- Config : mapping Doctrine pour `src/Wallet/Entity`, même exclusion Rector que `src/Entity`, migration.

### Tests (`tests/Wallet/`)
- Calculateur : 26 000 $ avec le `Dataset` (0,5 BTC + 1 000 USDC).
- Recorder avec `MockClock` : deux appels dans la même heure = 1 ligne, heure suivante = 2 lignes.
- Commande `wallet:snapshot`.
- Dashboard : test fonctionnel sur les totaux affichés.

## PR 2 — Scheduler

- `composer require symfony/scheduler dragonmantank/cron-expression`
- `src/Shared/Schedule/MainSchedule.php` (`#[AsSchedule]`), les 4 tâches en `RunCommandMessage` :

| Cron           | Commande             |
|----------------|----------------------|
| `*/15 * * * *` | `crypto:updatePrices`|
| `1 * * * *`    | `wallet:snapshot`    |
| `5 3 * * *`    | `crypto:updateDatas` |
| `0 6 * * *`    | `fiat:updateRates`   |

- Le worker traite les messages un par un : le snapshot de `:01` passe après la mise à jour des prix de `:00`, qu'elle ait réussi ou non.
- `stateful()` sur `cache.app` + `processOnlyLastMissedRun(true)` : après un redémarrage, une tâche manquée est rejouée une seule fois.
- Dev : pas de worker permanent (quota Fixer), `make scheduler` à la demande.
- Test : `MainSchedule` expose les 4 commandes avec leurs fréquences.
- `CLAUDE.md` : mentionner le Scheduler à côté des commandes.

### Prod (VPS)
- `deploy/systemd/cryptobook-worker.service` versionné : `messenger:consume scheduler_default async --time-limit=3600`, `Restart=always`.
- Mise en service : migration, `systemctl enable --now`, suppression des lignes de crontab, `wallet:snapshot` à la main pour le premier point.
- À chaque déploiement : `messenger:stop-workers`.
