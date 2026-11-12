# Plan : historique rétroactif sur 30 jours

Couvre le point 2 de `roadmap.md`. Une commande `wallet:backfill`, lancée à la main, crée des `WalletSnapshot` pour les 30 derniers jours à partir du wallet actuel et des prix horaires de CoinGecko. Aucun changement de schéma, de route, de template ni du Scheduler.

## État des lieux

- `WalletSnapshot` : `owner`, `recordedAt` (heure UTC pile), `valueUsd`, unique sur (`owner_id`, `recorded_at`). Créé chaque heure par `WalletSnapshotRecorder` (`wallet:snapshot`, cron `1 * * * *`).
- `WalletValueCalculator::forAllUsers()` : un `WalletValuation` par utilisateur ayant au moins une position, avec `cryptoHoldings` et `stableHoldings` (`coin`, `quantity`, `priceUsd`, `valueUsd`).
- `CoingeckoClient` : `getCoin()`, `getUsdPrices()`. Client scopé `coingecko.client`, sans clé d'API, timeout 10 s, 3 nouvelles tentatives espacées de 60 s sur un 429 (`config/packages/framework.yaml`).
- Mesuré sur l'API publique le 12/11/2026 : `GET coins/bitcoin/market_chart?vs_currency=usd&days=30` renvoie `{"prices": [[ms, prix], …], "market_caps": …, "total_volumes": …}`, 721 points, tous sur l'heure pile sauf le dernier (instant de l'appel). Coin inconnu : 404 `{"error":"coin not found"}`.
- `DashboardCacheInvalidator` invalide déjà le cache du propriétaire à chaque `WalletSnapshot` inséré par Doctrine.
- `WalletSnapshotRepository::findStats()` (KPI « Plus haut », variation 24 h) lit tous les snapshots, sans distinction.
- `deptrac.yaml` : `Wallet: [Position, User, Cryptocurrency, Shared]`.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Déclenchement | Commande manuelle / commande + Scheduler quotidien | Commande manuelle `wallet:backfill`, relançable. Rien dans `MainSchedule` |
| Heures remplies | Avant le 1er snapshot / toutes les heures manquantes | Seulement les heures antérieures au plus ancien snapshot de l'utilisateur. Les trous au milieu restent des trous |
| Stablecoins | Prix actuel constant / récupérés | Jamais récupérés : valeur actuelle appliquée sur toute la période |
| KPI « Plus haut » et variation 24 h | Sans distinction / colonne `backfilled` | Un snapshot rétroactif est un snapshot comme un autre. Pas de colonne |
| Miettes (coin non récupéré) | Valeur 0 / prix actuel constant | Prix actuel constant : la courbe rejoint le total affiché sans marche |
| Seuil de 5 $ | Tous les utilisateurs / utilisateurs à remplir | Calculé sur les seuls utilisateurs à remplir : aucun appel quand personne n'est à remplir |
| Échec CoinGecko sur un coin | Continuer sans ce coin / tout annuler | Tout annuler : rien n'est écrit, la commande échoue, on la relance. (Continuer figerait un historique faux, la règle « avant le 1er snapshot » empêchant de le refaire) |
| Coin inconnu de CoinGecko (404) | Échec / prix actuel constant | Prix actuel constant, comme une miette |
| Écriture | ORM / SQL brut | ORM, un seul `flush()` : une transaction, cache du dashboard invalidé par le listener existant |
| Pause entre les appels | `sleep` / rien | Rien : le `retry_failed` du client gère déjà le 429 |
| Wallet à 0 $ | Snapshots à 0 / ignoré | Utilisateur ignoré |

## CoinGecko (`src/Coingecko/`)

`CoingeckoClient::getHourlyUsdPrices(string $id, int $days): array`

- `GET coins/{rawurlencode($id)}/market_chart`, query `vs_currency=usd`, `days=(string) $days`. Pas de paramètre `interval` (réservé aux offres payantes ; l'API renvoie de l'horaire d'elle-même entre 2 et 90 jours).
- Retour : `array<int, float>`, clé = timestamp Unix en secondes (`intdiv(ms, 1000)`), valeur = prix USD, dans l'ordre de la réponse. Lit `prices` seulement.
- Entrée ignorée si ce n'est pas un tableau `[int|float, int|float]`. `prices` absent ou pas un tableau : `[]`.
- 404 : retourne `[]`. À détecter sur l'exception de `get()` : `$e->getPrevious()` est une `ClientExceptionInterface` dont `getResponse()->getStatusCode()` vaut 404.
- Toute autre erreur : `CoingeckoUnavailableException` (comportement de `get()`, inchangé).
- Docblock : `@return array<int, float>`, `@throws CoingeckoUnavailableException`.

## Données (`src/Wallet/`)

### `Wallet/ValueObject/HourlyPriceSeries`

`final readonly`, construit par `HourlyPriceSeries::fromPoints(array $points)` (`array<int, float>` du client).

- Chaque point est rangé sur l'heure la plus proche : `intdiv($timestamp + 1800, 3600) * 3600`. Deux points sur la même heure : le dernier gagne.
- `at(\DateTimeImmutable $hour): ?float` :
  - prix de cette heure s'il existe ;
  - sinon prix de l'heure connue la plus récente avant elle ;
  - sinon (avant le premier point) prix du premier point ;
  - `null` si la série est vide.

### `WalletSnapshotRepository::findFirstRecordedAtByOwner(): array`

- `array<int, \DateTimeImmutable>` : plus ancien `recordedAt` de chaque propriétaire, clé = id du propriétaire. Une seule requête (`MIN(recorded_at) … GROUP BY owner_id`), en DBAL, date convertie avec le type `DATETIMETZ_IMMUTABLE` comme dans `findPoints()`.

### `Wallet/Dto/BackfillReport`

`final readonly` : `int $users`, `int $snapshots`, `int $fetchedCoins`.

### `Wallet/Backfiller/WalletHistoryBackfiller`

`final readonly`, constructeur : `WalletValueCalculator`, `WalletSnapshotRepository`, `CoingeckoClient`, `EntityManagerInterface`, `ClockInterface`. Constantes `private const int DAYS = 30;` et `private const float MIN_VALUE_USD = 5.0;`.

`backfill(): BackfillReport` (`@throws CoingeckoUnavailableException`) :

1. `$currentHour` : heure courante tronquée en UTC (même calcul que `WalletSnapshotRecorder`). `$start = $currentHour->modify('-720 hours')`.
2. Pour chaque utilisateur de `forAllUsers()` :
   - ignoré si `totalUsd <= 0` ;
   - `$end` = plus ancien snapshot de l'utilisateur (`findFirstRecordedAtByOwner()`), ou `$currentHour` s'il n'en a pas ;
   - ignoré si `$end <= $start` ;
   - heures à remplir : de `$start` inclus à `$end` exclu, pas d'une heure. L'heure courante n'est jamais remplie (elle revient à `wallet:snapshot`). Sans snapshot : 720 heures.
3. Coins à récupérer : parmi les `cryptoHoldings` des utilisateurs retenus à l'étape 2, les coins dont au moins un holding a `valueUsd >= 5.0` et dont `getLibelleCoingecko()` n'est pas nul. Jamais les `stableHoldings`. Un appel `getHourlyUsdPrices($id, 30)` par coin, l'un après l'autre, tous avant la moindre écriture. Aucun utilisateur retenu : aucun appel.
4. Valeur d'un utilisateur à l'heure H = somme sur ses `cryptoHoldings` et `stableHoldings` de :
   - `quantity × série->at(H)` si le coin a été récupéré et que `at(H)` n'est pas nul (vaut pour tous ses détenteurs, même sous 5 $) ;
   - sinon `valueUsd` du holding (prix actuel constant : stablecoin, miette, coin inconnu de CoinGecko, coin sans prix).
5. Un `new WalletSnapshot($this->em->getReference(User::class, $ownerId), $hour, $valueUsd)` par heure, `persist()`, puis un seul `flush()` à la fin.
6. Retour : nombre d'utilisateurs remplis, de snapshots créés, de coins récupérés (appels faits, 404 compris).

Une exception CoinGecko à l'étape 3 remonte telle quelle : rien n'a été persisté.

### `Wallet/Command/WalletBackfillCommand`

- `#[AsCommand(name: 'wallet:backfill', description: 'Fills the last 30 days of wallet history from the current wallets and the CoinGecko hourly prices')]`, invocable, même forme que `WalletSnapshotCommand`.
- Succès : `$io->success(sprintf('%d snapshot(s) backfilled for %d user(s), %d coin(s) fetched.', …))`, `Command::SUCCESS`.
- `CoingeckoUnavailableException` : `$io->error('Nothing written: '.$e->getMessage())`, `Command::FAILURE`.

## Configuration

- `deptrac.yaml` : `Wallet: [Position, User, Cryptocurrency, Coingecko, Shared]`.
- Rien d'autre : pas de service YAML, pas de cible `make`, pas d'entrée dans `MainSchedule` (`MainScheduleTest` reste vert sans modification).

## Documentation

- `CLAUDE.md`, section « Projet », puce des commandes : ajouter à la fin `Historique rétroactif : `wallet:backfill`, à la main (30 jours avant le premier snapshot de chaque utilisateur, wallet actuel et prix horaires CoinGecko).`
- `doc/roadmap.md` : supprimer ce point et sa ligne `Spec`, renuméroter les suivants, compléter la ligne « Historique du wallet » de « Fait » avec `, historique rétroactif sur 30 jours (`wallet:backfill`)`.
- Supprimer `doc/specs/wallet-history-backfill.md` dans le même commit.

## Ordre des commits

1. `feat(coingecko): fetch the hourly price history of a coin` : `CoingeckoClient`, `CoingeckoClientTest`.
2. `feat(wallet): backfill 30 days of wallet history` : `HourlyPriceSeries`, repository, `BackfillReport`, `WalletHistoryBackfiller`, `WalletBackfillCommand`, `deptrac.yaml`, tests, `CLAUDE.md`.
3. `docs: mark the wallet history backfill as done` : `doc/roadmap.md`, suppression de la spec.

## Tests

Données de `Dataset` : `user` détient 0,5 BTC (50 000 $) et 1 000 USDC (stable, 1 $), soit 26 000 $ ; `admin` n'a aucune position. Horloge : `self::mockTime('2026-11-10 14:35:00 UTC')`, donc `$start` = 2026-10-11 14:00 UTC et dernière heure remplie = 2026-11-10 13:00 UTC. HTTP : `http_client.transport` (`MockHttpClient`), comme `CryptoUpdatePricesCommandTest`. Ne jamais simuler un 429 (le retry attendrait 60 s) : utiliser un 500 pour l'échec.

`tests/Coingecko/Client/CoingeckoClientTest` (ajouts) :

- `prices` `[[1791892800000, 83280.5], ['x', 1], [1791896400000, 83000]]` → `[1791892800 => 83280.5, 1791896400 => 83000.0]` ; URL `coins/bitcoin/market_chart?`, `vs_currency=usd`, `days=30`, pas de `interval`.
- 404 → `[]`.
- 500 → `CoingeckoUnavailableException`.

`tests/Wallet/ValueObject/HourlyPriceSeriesTest` (`TestCase`) :

- Points à 12:00 (100), 13:10:40 (110), 15:40 (130) : `at(12:00)` = 100, `at(13:00)` = 110, `at(14:00)` = 110 (heure manquante), `at(16:00)` = 130 (arrondi à l'heure la plus proche), `at(10:00)` = 100 (avant le premier point).
- Série vide : `null`.

`tests/Wallet/Repository/WalletSnapshotRepositoryTest` (ajout) :

- Deux snapshots pour `user`, un pour `admin` : le plus ancien de chacun, clé par id ; un utilisateur sans snapshot est absent.

`tests/Wallet/Backfiller/WalletHistoryBackfillerTest` (`KernelTestCase`, `ClockSensitiveTrait`) :

- Sans snapshot : BTC à 40 000 $ au premier point (2026-10-11 14:00) puis 60 000 $ à partir du 2026-11-01 00:00. Rapport `users: 1, snapshots: 720, fetchedCoins: 1`. Premier snapshot à 2026-10-11 14:00 = 21 000,0 ; dernier à 2026-11-10 13:00 = 31 000,0 ; aucun snapshot à 14:00 le 10/11. Une seule requête HTTP (bitcoin, pas `usd-coin`). `admin` n'a aucun snapshot.
- Snapshot réel existant le 2026-11-05 10:00 (valeur 123,0) : 596 snapshots créés, le dernier à 2026-11-05 09:00, le réel vaut toujours 123,0, aucun snapshot créé après lui. Second appel à `backfill()` : `snapshots: 0`, aucune requête HTTP.
- Miette seule : `admin` reçoit une position de 0,001 ETH (2 $). Aucune requête pour `ethereum`, 720 snapshots d'`admin` à 2,0.
- Coin récupéré pour tous : `user` reçoit en plus 1 ETH (2 000 $), `admin` 0,001 ETH ; ETH à 3 000 $ sur toute la série. Deux requêtes (bitcoin, ethereum), snapshots d'`admin` à 3,0.
- Série vide (`prices: []`) pour bitcoin : 720 snapshots de `user` à 26 000,0.
- Échec (500) : `CoingeckoUnavailableException`, aucun `WalletSnapshot` en base.
- Toutes les positions de `user` fermées : `snapshots: 0`, aucune requête HTTP.

`tests/Wallet/Command/WalletBackfillCommandTest` (`KernelTestCase`) :

- Succès : `720 snapshot(s) backfilled for 1 user(s), 1 coin(s) fetched.`
- 500 : code de sortie `Command::FAILURE`, sortie contenant `Nothing written`, aucun snapshot en base.

## Hors périmètre

- Reconstitution des achats et ventes passés.
- Remplissage automatique d'un nouvel utilisateur (Scheduler, inscription).
- Remplissage des trous entre deux snapshots réels.
- Distinction entre snapshots réels et rétroactifs (colonne, affichage, KPI).
- Clé d'API CoinGecko, cache des prix historiques, pause entre les appels.
- Bugs CoinGecko déjà listés dans `doc/bug.md` (`crypto:updateDatas`).

## À vérifier

- En dev (0 snapshot, 10 utilisateurs, 10 coins) : `bin/console wallet:backfill`, puis le dashboard affiche une courbe sur 24 h, 7 j et 30 j, et la période « 30 j » est notée comme couverte en entier.
- Limite de l'API publique sans clé : si la commande échoue sur un 429 malgré les 3 tentatives, la relancer (rien n'a été écrit). Ne pas ajouter de pause sans mesure.
- Après le déploiement : lancer `bin/console wallet:backfill` une fois sur le staging, puis sur la prod après le tag. Sur la prod, seules les heures antérieures au premier snapshot réel de chaque utilisateur sont créées.
