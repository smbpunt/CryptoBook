# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projet

CryptoBook, suivi de portefeuille crypto : positions, dépôts, prêts, NFT, stratégies (DCA, farming, LP) et suivi de projets. Symfony 8.1, PHP 8.4, PostgreSQL 16.

- Prix et données des cryptos via l'API CoinGecko (client maison dans `src/Coingecko/`), taux de change fiat via l'API Fixer.
- Mise à jour des données par commandes : `crypto:updatePrices`, `crypto:updateDatas`, `fiat:updateRates`.
- Back-office EasyAdmin dans `src/Controller/Admin/`.

Versions exactes dans `composer.json`, recettes Flex appliquées dans `symfony.lock`.

## Architecture

- Authentification par formulaire (`App\Security\UserAuthenticator`), mot de passe oublié via `symfonycasts/reset-password-bundle`
- Doctrine ORM 3 + PostgreSQL, Twig + Bootstrap v5.3, AssetMapper, Stimulus, EasyAdmin 5, KnpMenu
- Code rangé par type technique (`Controller/`, `Entity/`, `Service/`…), une application externe par dossier (client, DTO, exception, sans logique métier)

## Langue

- Code, commentaires, messages de commit, descriptions de PR : en anglais. L'historique ancien est en français, ne pas le copier.
- Messages de commit en Conventional Commits (`feat:`, `fix:`, `chore:`, `refactor:`, `docs:`, `test:`, `build:`…).

## Environnement

- Tout tourne dans Docker : `docker compose up -d`, puis `docker compose exec php composer install` / `bin/console …`. Application sur http://localhost:8888. Version de PHP en argument de build : `PHP_VERSION=8.4 docker compose build php`.
- Raccourcis via le `Makefile` (`make help` pour la liste) : `make up`, `make url`, `make migrate`, `make test`…
- Le service `php` remplace `DATABASE_URL` par le service `database` (Postgres 16) ; la base `app_test` est créée au premier démarrage. `.env` pointe toujours sur `127.0.0.1` pour un usage depuis l'hôte.
- PHP de l'hôte en 8.5, projet en 8.4 : ne pas lancer `composer update` sur l'hôte, il résout les dépendances pour la mauvaise version.
- Front en AssetMapper + importmap (pas de Node) : paquets JS via `bin/console importmap:require`, scripts de page déclarés comme entrypoints dans `importmap.php`.
- En cas d'erreur : lire `var/log/dev.log` et le profiler (`/_profiler`) avant de modifier le code.
- `.env` est versionné et ne contient que des valeurs par défaut. Les secrets (`APP_SECRET`, `FIXER_API_KEY`) vont dans `.env.local` (ignoré par git).

## Commandes qualité

Dans le conteneur `php`.

Correction automatique (dans cet ordre) :

1. `vendor/bin/rector process` — refactoring automatique
2. `vendor/bin/php-cs-fixer fix` — formatage

Vérification complète :

```
composer validate
composer audit
bin/console lint:container
bin/console lint:twig templates
bin/console lint:yaml config
bin/console doctrine:schema:validate
vendor/bin/php-cs-fixer fix --dry-run
vendor/bin/rector process --dry-run
vendor/bin/phpstan analyse
bin/phpunit
```

- PHPStan (niveau 6) utilise une baseline (`phpstan-baseline.neon`) : ne pas y ajouter d'erreurs, les corriger. La régénérer uniquement quand des erreurs disparaissent.
- Rector doit rester propre. La promotion de propriétés est désactivée sur `src/Entity` (elle peut élargir les types dont Doctrine se sert pour deviner les relations).

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
