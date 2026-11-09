# Bugs trouvés pendant la montée de version

Bugs repérés au fil des phases, hors du périmètre de la phase en cours. Ils sont corrigés en fin de phase, dans des commits `fix:` dédiés.

⬜ à corriger · ✅ corrigé

## Phase 3

| Bug | Fichiers | État |
|---|---|---|
| **Faille de droits** : `$this->redirectToRoute()` sans `return` dans `edit` / `delete`, un utilisateur peut modifier ou supprimer les données d'un autre | `NftController`, `LoanController`, `DepositController`, `PositionController`, `ProjectMonitoringController`, `StrategyDcaController` | ✅ |
| **Clé API Fixer en dur** (présente dans l'historique git) : passée en variable `FIXER_API_KEY` ; reste à révoquer l'ancienne clé et à définir la nouvelle en prod | `src/Service/FiatExchangeRatesService.php` | ✅ |
| Tableau concaténé dans une chaîne dans le `catch` de `updatePrices()` : warning « Array to string conversion » si l'appel échoue | `src/Service/CryptocurrencyService.php` | ✅ |
| `favoriteFiatCurrency` et `Deposit::fiatCurrency` (non nullables) jamais renseignés : `doctrine:fixtures:load` échoue | `src/DataFixtures/AppFixtures.php` | ✅ |
| `toUsd()` lit `rates['USD']` de la devise favorite, absent pour un utilisateur en USD : `/strategy/dca/` plante dès qu'il a une stratégie DCA | `src/Service/FiatExchangeRatesService.php`, `templates/strategy_dca/index.html.twig` | ✅ |
| `codenix-sv/coingecko-api` lève une classe d'exception inexistante si la réponse n'est pas du JSON (résolu par le client maison en phase 6) | `vendor/codenix-sv/coingecko-api` | ⬜ |

## Phase 4

| Bug | Fichiers | État |
|---|---|---|
| Les fixtures appellent CoinGecko (listener `prePersist`) pour remplir les cryptos : `doctrine:fixtures:load` échoue sans réseau ou en cas de rate limit | `src/DataFixtures/AppFixtures.php` | ✅ |
