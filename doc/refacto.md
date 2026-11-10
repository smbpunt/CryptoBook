# Plan — Refacto SOLID + organisation par feature

Une seule PR, un commit par étape, chaque commit vert. Aucun changement de schéma, d'URL ni de nom de route.

Les bugs simples trouvés en lisant le code sont corrigés au passage, dans la feature concernée (rubriques « Corrections »). Les autres sont listés à la fin.

## État des lieux

- Déjà rangé par feature : `Coingecko/`, `Fixer/`, `Dashboard/`, `Wallet/`, `Shared/Schedule/`.
- À ranger : `Controller/` (14 + 9 admin), `Entity/` (20), `Repository/` (20), `Form/` (18), `Service/` (5), `Command/`, `EventListener/`, `EventSubscriber/`, `Menu/`, `Security/`, `Dto/`.
- Voters : déjà faits. `OwnerVoter` protège toutes les routes `{id}`, `OwnershipTest` les couvre. Il reste à le déplacer.
- Les 5 classes `*Service` sont à découper.
- Tableaux associatifs à remplacer : liste des positions (`grouped` / `coutMoyen` / `splitted`), `sumQuantitiesByOwnerAndCoin()`, les 7 variables de la page farming.
- Requêtes en trop repérées : page positions (1 requête par coin), page farming (6 requêtes au lieu de 3).

## Arborescence cible

```
src/
  Admin/Controller/                  les 9 contrôleurs EasyAdmin
  Blockchain/                        Blockchain, Dapp
  Coingecko/  Fixer/                 inchangés
  Cryptocurrency/                    entité, repo, commandes, listener, Updater/
  Dashboard/                         + Controller/DashboardController (ex CryptobookController)
  Deposit/                           Deposit, DepositType, Calculator/
  Exchange/
  FiatCurrency/                      entité, repo, commande, Updater/, Converter/
  Loan/
  Nft/
  Position/                          Position, Vente, StrategyPosition, Builder/, Dto/
  ProjectMonitoring/                 ProjectMonitoring, TypeProject
  Security/                          login, inscription, mot de passe oublié, Authenticator/, InvalidCsrfTokenListener
  Shared/                            Schedule/, Builder/MenuBuilder, Entity/DescriptionTrait
  Strategy/Dca/  Strategy/Farming/  Strategy/Lp/
  User/                              User, préférences, OwnedInterface, OwnerTrait, Voter/OwnerVoter, Resolver/
  Wallet/                            inchangé
```

Sens des dépendances, sans cycle, vérifié par Deptrac (étape 9) :

```
Shared, Coingecko, Fixer, Exchange   ne dépendent de rien
FiatCurrency       → Fixer
User               → FiatCurrency
Cryptocurrency     → Coingecko
Blockchain         → Cryptocurrency
Deposit            → User, FiatCurrency, Exchange
Loan, Nft          → User, Cryptocurrency, Blockchain
ProjectMonitoring  → User, Cryptocurrency
Position           → User, Cryptocurrency
Strategy           → User, Cryptocurrency, Blockchain, Position, FiatCurrency
Wallet             → Position, User, Cryptocurrency
Dashboard          → Wallet, Deposit, Loan, Strategy, FiatCurrency, User, Cryptocurrency, Position
Security           → User, FiatCurrency
Admin              → référentiels, User
```

## Méthode

### Déplacement (commit `refactor(<feature>): move to src/<Feature>`)

1. `git mv` des classes, des templates et des tests. Aucun autre changement que `namespace` et `use`.
2. Renommage des FQCN : « Move Class » de PhpStorm, ou une config Rector jetable avec `RenameClassRector` (elle gère les classes utilisées sans `use` dans l'ancien namespace commun).
3. FQCN en chaînes : `grep` dans `config/` et `templates/`.
4. Bloc de mapping Doctrine pour `src/<Feature>/Entity`.
5. Chemins de `phpstan-baseline.neon` mis à jour au `sed`, sans nouvelle entrée.
6. Rector, PHP-CS-Fixer, vérification complète. Les tests ne changent que par leurs `use`.

### Refacto en TDD (commits `test(<feature>): …` puis `refactor(<feature>): …`)

1. **Filet** : tests de caractérisation sur les comportements non couverts, verts sur l'ancien code.
2. **Rouge** : test de la nouvelle classe, avec les cas de l'ancien test repris.
3. **Vert** : la classe, au plus simple.
4. **Bascule** : les appelants passent sur la nouvelle classe, le filet reste vert sans être modifié.
5. **Ménage** : suppression de l'ancienne classe et de son test.

Test unitaire pour un calcul pur, `KernelTestCase` pour un repository, `WebTestCase` pour un contrôleur.

### Validation (dans chaque feature)

Aucune entité n'a de contrainte : un champ obligatoire envoyé vide donne une 500, le formulaire plante sur le setter typé avant de valider.

- Rouge : POST du formulaire avec un champ obligatoire vide → 422 attendu.
- Vert : setter nullable et `#[Assert\NotNull]` sur la propriété, `#[Assert\Valid]` sur les collections de sous-formulaires (`ventes`, `strategies`, `parts`).

### Corrections

Un bug corrigé commence par un test rouge qui le reproduit. Commit `fix(<feature>): …`, séparé du refacto.

## Étape 0 — Préparation

- Brancher depuis `main` une fois `feat/dashboard-cache` mergée.
- Photo de référence : `bin/console debug:router --format=json` et `doctrine:mapping:info` (21 entités), à comparer à la fin.
- `doctrine.yaml` : un mapping par dossier `Entity` de feature, ajouté au fil des déplacements. Pas de mapping unique sur `src/` : le driver charge tous les fichiers du dossier, dont `DataFixtures/`, qui étend une classe d'un bundle installé seulement en dev.
- `rector.php` : l'exclusion de la promotion de propriétés passe de `src/Entity` à `src/*/Entity/*` et `src/*/*/Entity/*`.

## Étape 1 — Deposit

Déplacement : `Deposit`, `DepositType` (entité), leurs repos, `Form/DepositType`, `DepositController`, `templates/deposit/`.

- Filet : modification d'un dépôt, total affiché dans la devise favorite.
- `DepositService` → `Deposit/Calculator/DepositTotalCalculator::totalUsd(User)`.
- Suppression de `DepositRepository::getTotal()`, jamais appelée.

Corrections :

- La page charge deux fois les dépôts (liste, puis total) : une seule requête, le calculateur prend la liste.

## Étape 2 — Loan

Déplacement : `Loan`, `LoanRepository`, `LoanType`, `LoanController`, `templates/loan/`.

- Filet : création d'un emprunt (blockchain puis dapp), le POST XHR réaffiche le formulaire sans enregistrer.
- `LoanRepository::getTotal()` → `sumValueUsd(User)` : fin du `try/catch \Exception`, 0.0 sans emprunt. Test : 500 $ avec le `Dataset`, 0 pour un utilisateur sans emprunt.

## Étape 3 — Nft et ProjectMonitoring

Déplacement : `Nft/` ; `ProjectMonitoring/` avec `TypeProject`.

- Filet : création d'un NFT, d'un projet, bénéfice total affiché.
- Somme des bénéfices du contrôleur → `Nft/Calculator/NftProfitCalculator`.
- `NftRepository::findBySold()` → `findHeld(User)` et `findSold(User)`, typées, sans DQL concaténé.

## Étape 4 — Référentiels

Déplacement : `Exchange/`, `Blockchain/` (avec `Dapp`), `Cryptocurrency/`, `FiatCurrency/`, avec leurs commandes, listener et tests.

### Relations inverses
- Suppression des collections inverses qui créent des cycles : `Cryptocurrency` (`positions`, `nfts`, `loans`, `projectMonitorings`, `farmingStrategies`, `blockchains`), `Dapp` (`loans`, `farmingStrategies`), `Blockchain::nfts`, `Exchange::deposits`, `FiatCurrency` (`deposits`, `users`).
- Conservée : `Blockchain::dapps`, lue par les formulaires Loan, Farming et LP.
- Deux requêtes passent par `c.positions` : `sumQuantitiesByOwnerAndCoin()` se réécrit avec une jointure `WITH`, `getSumCoinByUser()` disparaît à l'étape 6.
- Aucune colonne touchée : `doctrine:migrations:diff` doit rester vide.

### Cryptocurrency
- `CryptocurrencyService` → `Updater/CoinDataUpdater` (`update(Cryptocurrency)`, `updateAll()`) et `Updater/CoinPriceUpdater` (`updateAll()`).
- Les 4 cas de `CryptocurrencyServiceTest` sont repris. Les tests des commandes et du listener servent de filet.

### FiatCurrency
- `FiatExchangeRatesService` se découpe en trois :
  - `FiatCurrency/Updater/FiatRateUpdater` : mise à jour depuis Fixer.
  - `FiatCurrency/Converter/FiatConverter` : `toUsd(float, FiatCurrency)`, `fromUsd(float, FiatCurrency)`. Il ne connaît plus `User`.
  - `User/Resolver/FavoriteCurrencyResolver` : devise favorite, USD par défaut.
- `FiatCurrency::$KEY_USD` et `$KEY_EUR` (statiques modifiables) → constantes. `getsymbol()` → `getSymbol()`.
- Global Twig `fiatRates` supprimé : les deux templates qui s'en servent reçoivent le montant déjà converti.

Corrections :

- Taux absent (devise jamais mise à jour, symbole inconnu de Fixer) : aujourd'hui un warning PHP et un montant à 0. `FiatCurrency::rateTo(string)` lève une exception claire, utilisée par le convertisseur et par `Deposit::getAmountUsd()`.
- `FiatCurrency::setUpdatedAtValue()` : `#[ORM\PreUpdate]` sans `#[ORM\HasLifecycleCallbacks]`, jamais appelé. Supprimé.
- `DashboardBuilder` passe sur le convertisseur et le resolver. `DashboardBuilderTest` et `DashboardCacheTest` servent de filet.

## Étape 5 — User et Security

Déplacement :

- `User/` : `User`, `UserRepository`, `UserController`, `UserPreferencesType`, `OwnedInterface`, `OwnerTrait`, `OwnerVoter`.
- `Security/` : `UserAuthenticator`, `SecurityController`, `RegistrationController`, `ResetPasswordController`, leurs 3 formulaires, `ResetPasswordRequest` et son repo, `InvalidCsrfTokenListener`.
- `Shared/Entity/DescriptionTrait`.
- `AuthenticationSubscriber` → `User/EventListener/LastConnectionListener`.
- Config : `security.yaml` (provider, authenticator), `reset_password.yaml`.
- Suppression de `templates/user/index.html.twig`, reste de Maker jamais rendu.

`ResetPasswordController` vient du bundle : déplacé tel quel.

Corrections :

- Après l'inscription, l'utilisateur est renvoyé sur `/login` : il n'est pas connecté. Connexion automatique avec `Security::login()`.
- Suppression de `templates/registration/confirmation_email.html.twig`, jamais utilisé.

## Étape 6 — Position

Déplacement : `Position`, `Vente`, `StrategyPosition`, leurs repos et formulaires, `PositionController`, `templates/position/`, `PositionTest`.

- Filet : page `/position/` avec deux positions BTC à des prix d'entrée différents (quantité et coût moyen attendus), onglets stable et fermée, modification, suppression.
- `Position/Dto/PositionGroup` : `coin`, `quantity`, `averageCost`, `positions`, plus `valueUsd()` et `evolutionPercent()` aujourd'hui calculés dans le template.
- `Position/Builder/PositionGroupBuilder::build(list<Position>)` : regroupe par coin et calcule le coût moyen pondéré. Test unitaire, sans base.
- `PositionRepository::getPositions()` et ses drapeaux booléens → `findOpen(User, bool $stable)` et `findClosed(User)`. Une seule requête pour la page au lieu d'une par coin.
- `Position/Calculator/ExitPlanCalculator` et `Position/Dto/ExitPlan` : le plan de sortie (ventes prévues, récupéré, gain) est calculé aujourd'hui dans `_strategy_position.html.twig`.
- `Position/Dto/CoinQuantity` remplace le tableau de `sumQuantitiesByOwnerAndCoin()`. `WalletValueCalculatorTest` sert de filet, inchangé.
- Suppression de `CryptocurrencyRepository::getSumCoinByUser()`.

Corrections :

- Position à 0 coin : acceptée, puis `/position/` répond 500 (division par zéro). `#[Assert\Positive]` sur `nbCoins`, `#[Assert\PositiveOrZero]` sur `entryCost`.
- Vente à plus de 100 % : acceptée, la position passe en quantité négative et reste ouverte. `#[Assert\Range]` de 0 à 100 sur `percent` (ventes et plan de sortie), `#[Assert\PositiveOrZero]` sur `priceSold`.
- Position gratuite (coût 0) avec un plan de sortie : la fenêtre d'infos répond 500. Le calculateur renvoie un pourcentage nul, affiché « + ∞ ».
- Coût moyen faux pour les coins sous 1 centime : il part de `getEntryCoinValue()`, arrondi à 2 décimales, donc 0 et « + ∞ » affiché. Le builder calcule sur la valeur brute.
- Positions stables fermées : visibles dans aucun onglet, donc ni modifiables ni supprimables. `findClosed()` ne filtre plus sur le type de coin.
- `getPercentEvolution()` renvoie 9999 quand le coût est nul, affiché « 9 999 % ». Retour `?float`, affiché « + ∞ » comme la ligne du coin.
- Bouton « Valider & continuer » : fait la même chose que « Valider » (TODO dans le contrôleur). Il renvoie sur le formulaire de création.

## Étape 7 — Strategy

Déplacement en trois sous-features. Templates dans `templates/strategy/{dca,farming,lp}/`.

### Farming et Lp
- Filet : totaux annuels affichés sur `/strategy/farming/`, création d'un farming et d'une LP.
- `Strategy/Farming/Dto/FarmingOverview` : les trois listes et les quatre totaux.
- `Strategy/Farming/Builder/FarmingOverviewBuilder::build(User)` : 3 requêtes au lieu de 6, sert la page et le dashboard.
- `FarmingService` disparaît, son test est repris.

Corrections :

- Onglet LP : les colonnes « Investi » et « Live » affichent le même montant, `StrategyLp::getValueUsd()` utilise les prix saisis. Elle passe sur les prix courants, comme `StrategyFarming`, et `getInvestedUsd()` garde les prix saisis.
- Conséquence : les gains par ligne (prix saisis) ne correspondaient pas au total de l'onglet et du dashboard (prix courants). Tout passe sur `getAnnualRewardsUsd()`, plus besoin d'un calcul à part pour les LP.

### Dca
- Filet : le POST de `generatePositions` et de `dcaAuto` enregistre les positions, création et modification de la stratégie.
- `Strategy/Dca/Enum/DcaRecurrence` (`h`, `d`, `w`, `m`, avec `interval()`) remplace les constantes de `DcaAutoType`. Valeurs inchangées. Les cas 422 de `SmokeTest::testDcaAuto` servent de filet.
- `DcaService` → `Generator/DcaPositionGenerator` (avec `ClockInterface` à la place de `date()`) et `Generator/DcaAutoPositionGenerator`.
- `Calculator/DcaAmountCalculator` : montant à investir, calculé aujourd'hui dans le contrôleur et une seconde fois dans le template. Il convertit `fiatToDcaEur` avec le taux EUR → USD de Fixer (`FiatConverter`). La recherche du coin jEUR et son repli à 1,04 disparaissent.
- `StrategyDcaRepository::findOneByOwner(User)` remplace les 4 `findOneBy(['owner' => …])` du contrôleur.
- Enregistrement des positions générées en un seul flush, dans une méthode commune aux deux actions.
- `Dto/DcaAutoQuery` rejoint la sous-feature.

Corrections :

- Coin à prix 0 dans le DCA (coin tout juste créé, CoinGecko en panne) : `/strategy/dca/` et la génération répondent 500. Le générateur met une quantité à 0, à saisir à la main, et le tableau affiche « — ».
- Le montant FIAT est en euros mais s'affiche avec le symbole de la devise favorite : toujours « € ».
- `DcaAutoType::HOURLY` vaut « toutes les 4 heures » : le cas de l'enum s'appelle `FourHours`.
- Parts du DCA : rien ne vérifie que la somme fait 100 %, le total généré peut différer du montant investi. `#[Assert\Callback]` sur `StrategyDca` (somme à 100, à 0,01 près) et `#[Assert\Range]` de 0 à 100 sur `CoinPercentDca::percent`. Test : enregistrer un DCA à 60 + 30 → 422.

## Étape 8 — Dashboard, Shared, Admin

- `CryptobookController` → `Dashboard/Controller/DashboardController`, `templates/cryptobook/` → `templates/dashboard/`. La route `home` ne change pas.
- `Menu/MenuBuilder` → `Shared/Builder/MenuBuilder`, `templates/menu/template.html.twig` → `templates/shared/menu.html.twig` (`knp_menu.yaml`).
- `Controller/Admin/` → `Admin/Controller/`. `SmokeTest::testAdminPages` sert de filet.
- Correction : `CryptocurrencyCrudController` déclare `priceEur` et `mcapEur`, qui n'existent pas sur l'entité. Supprimés.
- `WalletValueCalculator` : les tableaux internes `$line` deviennent des `WalletHolding` construits en deux passes.

## Étape 9 — Ménage et garde-fous

- Dossiers vides supprimés (`src/Entity`, `src/Service`…), mapping Doctrine `App` retiré.
- Repositories : code commenté de Maker supprimé, `add()` / `remove()` retirés là où personne ne les appelle.
- Restes : `console.log` de `assets/farming/form.js` et `assets/lp/form.js`, argument inutilisé de `StrategyDcaController::index()`.
- Tests : `tests/Unit/`, `tests/Command/`, `tests/EventListener/` répartis dans `tests/<Feature>/`. `FormTest` découpé par feature. Restent dans `tests/Functional/` : `SmokeTest`, `OwnershipTest`, `ErrorPageTest`, `CoinLogoTest`.
- `phpstan-baseline.neon` régénérée : elle ne doit que diminuer.
- `CLAUDE.md` : chemins à jour, retrait de la phrase sur l'existant rangé par type.
- Comparaison avec la photo de l'étape 0 : mêmes routes, 21 entités, `doctrine:migrations:diff` vide.
- Déploiement : le FQCN de `User` change, les sessions ouvertes sont à reconnecter. `cache:pool:clear dashboard.cache` et `messenger:stop-workers`.

### Deptrac

Bloque les dépendances interdites entre features. Ajouté en dernier : avant l'étape 4, les relations inverses le feraient échouer.

- `composer require --dev deptrac/deptrac` (4.7, compatible Symfony 8, 3 paquets installés).
- `deptrac.yaml` à la racine : une couche par dossier de `src/` (`Strategy` en une seule), et pour chacune la liste des features qu'elle a le droit d'utiliser. C'est le tableau des dépendances ci-dessus, qui devient la référence.
- `vendor/bin/deptrac analyse` ajouté à la vérification complète de `CLAUDE.md`.
- `.deptrac.cache` dans `.gitignore`.
- Par la suite : une nouvelle feature = une couche et une ligne de règles.

```yaml
deptrac:
  paths: [./src]
  layers:
    - name: Deposit
      collectors:
        - { type: classLike, value: ^App\\Deposit\\.* }
    # … une entrée par feature
  ruleset:
    Deposit: [Shared, User, FiatCurrency, Exchange]
    # … une ligne par feature
```

Syntaxe à confirmer avec `vendor/bin/deptrac init` et la doc de la version installée.

## Changements de comportement

- DCA : un seul taux EUR → USD, celui de Fixer. Aujourd'hui le contrôleur prend le prix du coin jEUR (1,04 par défaut) et le template le taux de la devise favorite.
- DCA Auto : coin inconnu → 404 au lieu d'une liste vide.
- Messages de log de CoinGecko en anglais.
- Formulaires : un champ obligatoire vide ou une valeur hors bornes donne une erreur de formulaire (422) au lieu d'une 500 ou d'une donnée incohérente.
- LP : valeur « Live » et gains par ligne calculés aux prix courants.
- Inscription : l'utilisateur arrive connecté sur le dashboard.
- Positions : les stables fermées apparaissent dans l'onglet « Fermée », « Valider & continuer » rouvre le formulaire.
- Taux de change absent : erreur explicite au lieu d'un montant à 0.
- DCA : une stratégie dont les parts ne font pas 100 % est refusée à l'enregistrement.
- DCA Auto mensuel : les dates gardent le jour du premier achat (ou le dernier jour d'un mois plus court) au lieu de glisser au 2 ou 3 du mois suivant.
- Positions : « Valider & continuer » rouvre aussi le formulaire de création depuis la modification.
- Inscription : la page n'annonce plus de mail de vérification.

Les données déjà en base ne sont pas corrigées : une position à 0 coin ou une vente à plus de 100 % existante reste à reprendre à la main. Un DCA existant qui ne fait pas 100 % continue de générer des positions tant qu'il n'est pas modifié.

## Repéré, non corrigé ici

- `Cryptocurrency::symbol` fait 8 caractères : un symbole CoinGecko plus long fait échouer le flush de `crypto:updateDatas` pour tous les coins. Demande une migration.
- `crypto:updateDatas` fait un appel CoinGecko par coin, sans pause : au-delà de la limite de l'API, les coins suivants sont ignorés (erreur loguée). Un appel groupé `coins/markets` réglerait le problème.
- Coin créé dans l'admin sans nom pendant une panne CoinGecko : `libelle` reste nul, erreur SQL.
- Dépôts convertis en USD au taux du jour : le total « Investi » bouge avec le change. Il faudrait stocker le taux à la date du dépôt.
- Routes `/{id}/infos` en POST alors qu'elles ne font que lire. Touche le JS, à voir en phase 4.
- DCA Auto : jusqu'à 1000 positions dans un seul formulaire.

## Hors périmètre

- Renommer entités et colonnes en anglais (`Vente`, `libelle`, `TypeProject`) : demande des migrations.
- Montants en float.
- JS de `assets/` et templates au-delà de leur déplacement (phase 4).
- Contrôleur CRUD générique : les 7 contrôleurs restent explicites.

## Décisions prises

- `DepositType` et `TypeProject` sont rangés dans la feature qui les utilise (`Deposit/`, `ProjectMonitoring/`), pas dans une feature à eux.
- DCA : taux EUR → USD de Fixer.
- Deptrac en dev, dans la vérification complète.
- DCA : les parts doivent faire 100 %.
