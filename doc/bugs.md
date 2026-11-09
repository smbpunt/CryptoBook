# Bugs trouvés pendant la montée de version

Bugs repérés au fil des phases, hors du périmètre de la phase en cours. Ils sont corrigés en fin de phase, dans des commits `fix:` dédiés.

⬜ à corriger · ✅ corrigé

## Phase 3

| Bug | Fichiers | État |
|---|---|---|
| **Faille de droits** : `$this->redirectToRoute()` sans `return` dans `edit` / `delete`, un utilisateur peut modifier ou supprimer les données d'un autre | `NftController`, `LoanController`, `DepositController`, `PositionController`, `ProjectMonitoringController`, `StrategyDcaController` | ⬜ |
| **Clé API Fixer en dur** (présente dans l'historique git) : à révoquer et passer en variable d'environnement | `src/Service/FiatExchangeRatesService.php` | ⬜ |
| Tableau concaténé dans une chaîne dans le `catch` de `updatePrices()` : warning « Array to string conversion » si l'appel échoue | `src/Service/CryptocurrencyService.php` | ⬜ |
| `favoriteFiatCurrency` (non nullable) jamais renseigné : `doctrine:fixtures:load` échoue | `src/DataFixtures/AppFixtures.php` | ⬜ |
| `codenix-sv/coingecko-api` lève une classe d'exception inexistante si la réponse n'est pas du JSON (résolu par le client maison en phase 6) | `vendor/codenix-sv/coingecko-api` | ⬜ |
