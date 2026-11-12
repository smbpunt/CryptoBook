# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projet

CryptoBook, suivi de portefeuille crypto : positions, dépôts, prêts, NFT, stratégies (DCA, farming, LP) et suivi de projets. Symfony 8.1, PHP 8.5, PostgreSQL 16.

- Page d'accueil publique sur `/` (`src/Home/`, `templates/home/`) : vitrine statique, chiffres d'exemple en dur, aucune requête SQL. Connecté, elle redirige vers le dashboard (`/dashboard`, route `app_dashboard_index`). `public/robots.txt` autorise tout : le staging est fermé par la basic auth et un en-tête `X-Robots-Tag` de Caddy.
- Prix et données des cryptos via l'API CoinGecko (client maison dans `src/Coingecko/`), taux de change fiat via l'API Fixer.
- Mise à jour des données par commandes : `crypto:updatePrices`, `crypto:updateDatas`, `fiat:updateRates`, et snapshot horaire de la valeur des wallets : `wallet:snapshot`. Planifiées par Symfony Scheduler (`src/Shared/Schedule/MainSchedule.php`). En prod, worker `messenger:consume scheduler_default async` lancé par systemd (`cryptobook-worker@prod`, `deploy/systemd/`). En dev, pas de worker permanent (quota Fixer) : `make scheduler` à la demande. Historique rétroactif : `wallet:backfill`, à la main (30 jours avant le premier snapshot de chaque utilisateur, wallet actuel et prix horaires CoinGecko).
- Back-office EasyAdmin dans `src/Admin/Controller/`.
- Dashboard mis en cache par utilisateur (pool `dashboard.cache`, filesystem taggé), historique compris. Invalidé par un listener Doctrine (`src/Dashboard/EventListener/`) : prix et taux pour tous, données d'un utilisateur et ses snapshots pour lui seul. Bascule monnaie préférée / $ : cookie `dashboard_currency` de 30 jours (`DisplayCurrencyCookie`), route POST `app_dashboard_currency`, monnaie affichée résolue par `DisplayedDashboardResolver`, conversion au rendu par `DashboardData::inUsd()` (le cache reste en USD). Card « Wallet crypto » limitée par `TopHoldings::rowsFor()` (le reste dans une ligne « Autres ») : constantes de hauteur en px à réajuster si le gabarit de `_holdings.html.twig` ou `_allocation.html.twig` change, puis `DashboardCache::VERSION` à incrémenter. Coin sans couleur : couleur dérivée du symbole (`DashboardHolding::colorFromSymbol()`).
- Graphique du wallet : snapshots (USD, convertis au taux du jour) + dépôts cumulés, `symfony/ux-chartjs`. Changement de période dans le Turbo Frame `wallet-history`, rendu par la route `app_dashboard_history` (`/dashboard/history/{period}`, enum `HistoryPeriod`). `wallet_chart_controller.js` complète la vue PHP (couleurs des tokens, formats `Intl`, infobulle) sur `chartjs:pre-connect` : il n'est pas lazy, pour écouter avant le contrôleur `chart` (lazy). Une modification hors Doctrine (SQL brut) demande `bin/console cache:pool:clear dashboard.cache`.

Versions exactes dans `composer.json`, recettes Flex appliquées dans `symfony.lock`.

## Architecture

- Authentification par formulaire (`App\Security\Authenticator\UserAuthenticator`), mot de passe oublié via `symfonycasts/reset-password-bundle`
- Doctrine ORM 3 + PostgreSQL, Twig, AssetMapper, Stimulus, Turbo, EasyAdmin 5, KnpMenu
- UI : Tailwind CSS 4 (`symfonycasts/tailwind-bundle`) + kit shadcn du UX Toolkit. Composants du kit dans `templates/components/` (installés via `bin/console ux:install <nom> --kit shadcn`, modifiables, pas de mise à jour automatique), composants applicatifs dans `templates/components/App/`. Thème de formulaire : `templates/form/shadcn_layout.html.twig` (option `addon` pour un suffixe `$`/`%` ; les `DateType` en `single_text` s'affichent avec le `DatePicker` du kit, qui envoie une date `Y-m-d`). Selects : option `autocomplete` de `symfony/ux-autocomplete` (Tom Select, CSS dans `app.css`) ; une crypto se choisit avec `CryptocurrencyAutocompleteField` (AJAX). Icônes : `<twig:ux:icon name="lucide:…"/>`, puis `bin/console ux:icons:lock`.
- Thèmes clair et sombre : classe `.dark` sur `<html>`, posée avant le CSS par le script inline de `base.html.twig` (`localStorage.theme`, sinon `prefers-color-scheme`), basculée par `theme_controller.js` qui émet `theme:change` sur `window`. Aucune couleur en dur : tokens de `assets/styles/app.css` (`:root` / `.dark`). En plus de ceux de shadcn : `--brand` (orange, aplats), `--brand-text` (orange lisible), `--positive` / `--negative`, `--shadow-card`. Couleurs de graphique en hex (Chart.js ne lit pas `oklch()`). Montants en français par les filtres de `src/Shared/Twig/AmountExtension.php` (`amount`, `signed_amount`, `price`, `quantity`, `percent`, `signed_percent`, `decimal`), dates par `day` (`DateExtension`) : la locale de l'application est `en`, les messages de validation sont en français dans `translations/validators.en.yaml`. Pages de liste : `App:PageHeader`, KPI `App:Stat` avec icône, tableau dans `App:ListCard`, actions de ligne `App:RowActions`. Polices auto-hébergées (`@fontsource-variable`) : Inter (`font-sans`), Space Grotesk (`font-display`), montants en `tabular-nums`.
- Logo : `App:Logo` (`label`, `wordmark`, `size`). Favicon : `assets/images/favicon.svg`, d'où `make icons` tire `favicon-32.png`, `apple-touch-icon.png` et `icon-512.png` (Open Graph). Blocs de `base.html.twig` pour une page hors gabarit : `meta`, `page_width` (largeur du header, du contenu et du footer), `header_class` (classes en plus de la barre du header), `header_logo`, `header_nav`, `theme_toggle`, `header_account`, `footer`.
- Menus KnpMenu (`src/Shared/Builder/MenuBuilder.php`) : `main` (onglets du header) et `account` (popover du bouton compte, `templates/shared/account_menu.html.twig`).
- Piège TwigComponent : le contenu passé à un composant a sa propre portée (variables `set` dans une boucle, imports de macros, `block()` ne traversent pas). `outerBlocks` ne remonte que d'un niveau : capturer le contenu en haut du composant (`{% set _content %}{% block content %}{% endblock %}{% endset %}`).

## Organisation du code par feature

Le code est rangé par sujet métier, pas par type technique. Inspiré du DDD, sans ses couches.

- Une feature = un dossier dans `src/`, au singulier, nommé avec le vocabulaire métier du projet (`Position/`, `Deposit/`, `Loan/`, `Nft/`, `Strategy/`…).
- Dans une feature, chaque classe va dans un dossier de type, même seule. Nom Symfony quand il existe (`Controller/`, `Entity/`, `Repository/`, `Form/`, `Voter/`, `Command/`, `EventSubscriber/`, `Twig/`), sinon le suffixe de la classe (`Builder/MenuBuilder`, `Calculator/DcaCalculator`). DTO dans `Dto/`, enums dans `Enum/`, value objects dans `ValueObject/`.
- Le nom d'une classe dit ce qu'elle fait (`Builder`, `Finder`, `Resolver`, `Calculator`, `Factory`…). Éviter si possible `Service`, `Manager`, `Helper` ou `Utils`.
- Une feature peut contenir des sous-features : `src/Strategy/Dca/`. Une simple page n'en est pas une. Deux niveaux maximum.
- Chaque application externe appelée (CoinGecko, Fixer…) a son propre dossier : le client, ses DTO et ses exceptions, sans logique métier.
- Le code commun va dans `src/Shared/`.
- Les features peuvent s'utiliser entre elles, sans dépendance circulaire. Les dépendances autorisées sont déclarées dans `deptrac.yaml` et vérifiées par Deptrac : une nouvelle feature = une couche et une ligne de règles.
- Un mapping Doctrine par dossier `Entity` de feature dans `config/packages/doctrine.yaml` (pas de mapping unique sur `src/` : il chargerait `DataFixtures/`).
- Templates dans `templates/<feature>/`, à plat, avec un sous-dossier par sous-feature. Tests dans `tests/<Feature>/`, même arborescence que `src/`. Les tests transverses (`SmokeTest`, `OwnershipTest`…) restent dans `tests/Functional/`.
- Ne bougent pas : `src/Kernel.php`, `src/DataFixtures/`, `templates/base.html.twig`, `templates/bundles/`, `translations/`.
- Maker ne suit pas toujours ce rangement : donner le nom complet de la classe (`make:entity '\App\<Feature>\Entity\<Classe>'`) et déplacer ensuite ce qui est mal placé.

## Langue

- Code, commentaires, messages de commit, descriptions de MR : en anglais. L'historique ancien est en français, ne pas le copier.
- Messages de commit en Conventional Commits (`feat:`, `fix:`, `chore:`, `refactor:`, `docs:`, `test:`, `build:`…).

## Environnement

- Tout tourne dans Docker : `docker compose up -d`, puis `docker compose exec php composer install` / `bin/console …`. Application sur http://localhost:8888. Version de PHP en argument de build : `PHP_VERSION=8.5 docker compose build php`.
- Raccourcis via le `Makefile` (`make help` pour la liste) : `make up`, `make url`, `make migrate`, `make test`, `make ci`…
- CSS : `make tailwind` (ou `make tailwind c=--watch`) compile `assets/styles/app.css` vers `var/tailwind/`, requis avant d'afficher l'application.
- Le service `php` remplace `DATABASE_URL` par le service `database` (Postgres 16) ; la base `app_test` est créée au premier démarrage. `.env` pointe toujours sur `127.0.0.1` pour un usage depuis l'hôte.
- Résolution des dépendances fixée sur PHP 8.5 (`config.platform.php` de `composer.json`) : à changer avec la version de l'image.
- Front en AssetMapper + importmap (pas de Node) : paquets JS via `bin/console importmap:require`. Un seul entrypoint, `app` : avec Turbo, l'importmap ne change pas d'une page à l'autre et un module ne s'exécute qu'une fois. Le JS de page est un contrôleur Stimulus (`assets/controllers/`, `/* stimulusFetch: 'lazy' */`), qui nettoie dans `disconnect()` ou sur `turbo:before-cache` ce qu'il ajoute au DOM.
- Turbo Drive : redirection après un POST en 303, formulaire invalide en 422 (`render()` reçoit le formulaire). Liens hors Turbo en `data-turbo="false"` : Admin (layout EasyAdmin), Déconnexion (sinon préchargée au survol).
- En cas d'erreur : lire `var/log/dev.log` et le profiler (`/_profiler`) avant de modifier le code.
- `.env` est versionné et ne contient que des valeurs par défaut. Les secrets (`APP_SECRET`, `FIXER_API_KEY`) vont dans `.env.local` (ignoré par git).

## Déploiement

- Dépôt sur GitLab `smbai/cryptobook` (MR et CI, via `glab`), GitHub `smbpunt/CryptoBook` en miroir push.
- Pipeline `.gitlab-ci.yml` (lint, analyse et tests avec Postgres 18 en parallèle, puis deploy) : un push sur `dev` déploie le staging `dev.cryptobook.fr` (VPS1), un tag `vX.Y.Z` sur `main` déploie la prod `cryptobook.fr` (VPS2). Retour arrière manuel du code seul (`rollback_*`), une migration se défait à la main. Pas de pipeline si seuls `doc/` et des fichiers `.md` changent (MR et push sur `dev`). Pipeline planifiée hebdomadaire sur `dev` : `composer audit` seul (les autres jobs ont la règle `*not-scheduled`).
- Deployer : `deploy.php` (assets, migrations, reload de FrankenPHP, arrêt du worker depuis la release précédente). `var/log` et `var/sessions` partagés, `var/cache` et `var/share` par release.
- Web en FrankenPHP (PHP 8.5), CLI, Deployer et worker en PHP système 8.5 (`/usr/bin/php`).

## Commandes qualité

- `make ci` : vérification complète (composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, Symfony Language Tools, PHPUnit). S'arrête au premier échec, `make -k ci` pour tout dérouler. Une étape seule : `make lint`, `make phpstan`… (`make help`).
- `make fix` : Rector puis PHP CS Fixer.
- Symfony Language Tools (`symfony-lsp check`, bêta) : binaire installé dans l'image `php`, version épinglée dans `docker/php/install-symfony-lsp.sh`, partagé avec le job `lint:symfony-lsp` de la CI (`make build` après un changement). Il démarre l'application en `dev` et signale routes, templates, services ou options inexistants.

Tout est propre, sans baseline (PHPStan comme Symfony Language Tools) : corriger les erreurs, ne jamais les ignorer. Faux positif de `symfony-lsp` : `@symfony-lsp-ignore <code> (raison)` sur la ligne.

## Conventions Symfony

Suivre https://symfony.com/doc/current/best_practices.html.

- Nouvelle fonctionnalité : `composer require <package>` et laisser la recette Flex générer la config. Ne pas éditer `config/bundles.php` à la main.
- Attributs PHP pour les métadonnées : `#[Route]`, `#[IsGranted]`, `#[Assert\...]`, `#[AsCommand]`, `#[AsEventListener]`, `#[AsMessageHandler]`, `#[AsAlias]`, `#[Autoconfigure]`. Pas de routing YAML/XML.
- Autowiring et autoconfiguration. Si le type ne suffit pas : `#[Autowire]` ou `#[Target]` dans la classe. Une définition YAML dans `services.yaml` est le dernier recours.
- Contrôleurs fins qui étendent `AbstractController` et délèguent aux services.
- Chaque donnée appartient à un utilisateur : vérifier le propriétaire avant toute lecture, modification ou suppression (`OwnershipTest`).
- Utiliser les composants existants : Form, Validator, Serializer, Messenger, Security (voters), HttpClient (clients scopés), `path()`/`url()` dans Twig. Vérifier qu'un composant Symfony ne couvre pas le besoin avant d'écrire de l'infra ou d'ajouter une lib tierce.
- Données de requête : `#[MapRequestPayload]` / `#[MapQueryString]` plutôt que `json_decode()` ou le Serializer à la main.
- Promotion de propriétés dans le constructeur (hors entités), `readonly` pour les DTO et value objects.
- Tris Doctrine via `\SortDirection` plutôt que `'ASC'` / `'DESC'`.
- Schéma de base : toujours via migrations (`make:migration` puis `doctrine:migrations:migrate`), jamais `doctrine:schema:update`.
- Maker : passer tous les arguments et `--no-interaction`, sinon la commande bloque.

## Tests

Tests fonctionnels : `WebTestCase` (`tests/Functional/`, dont un smoke test de toutes les routes GET). Tests de services : `KernelTestCase`. Appels HTTP externes simulés avec `MockHttpClient`. Une fonctionnalité n'est terminée que lorsqu'un test l'exerce comme un appelant réel (requête HTTP pour un contrôleur, appel de service pour un service).

- Les tests tournent sur `app_test` (DAMA annule chaque test). Après une nouvelle migration : `bin/console doctrine:migrations:migrate --env=test -n`.
- Toute dépréciation, notice ou warning fait échouer les tests, y compris celles venant des vendors ou des templates.

## Vérifier plutôt que deviner

Les API du framework évoluent : consulter le projet plutôt que sa mémoire.

- `bin/console about`, `debug:router`, `debug:container`, `debug:autowiring <nom>`, `debug:config <bundle>`, `config:dump-reference <bundle>`
- Le code et les docblocks sous `vendor/`
- Doc : https://symfony.com/doc/8.1/
