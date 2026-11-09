# Montée de version Symfony 6.3 → 8.1

Faire passer CryptoBook de Symfony 6.3 / PHP 8.1 à Symfony 8.1 / PHP 8.4, sécurisé par des tests.

Tout le plan se fait sur une seule branche, `chore/upgrade-symfony-8`, mergée dans `main` à la fin. Chaque phase y est livrée par un ou plusieurs commits dédiés.

Les bugs trouvés pendant une phase et hors de son périmètre sont notés dans [`bugs.md`](bugs.md), puis corrigés en fin de phase dans des commits `fix:` dédiés.

## Suivi

| # | Phase | PHP | Symfony | État |
|---|---|---|---|---|
| 0 | Docker avec version PHP paramétrable | 8.3 | 6.3 | ✅ |
| 1 | Suppression de l'API | 8.3 | 6.3 | ✅ |
| 2 | Outillage qualité | 8.3 | 6.3 | ✅ |
| 3 | Tests de non-régression | 8.3 | 6.3 | ✅ |
| 4 | Symfony 6.4, zéro dépréciation | 8.3 | 6.4 | ✅ |
| 5 | Webpack → Asset Mapper | 8.3 | 6.4 | ✅ |
| 6 | Symfony 7.4, zéro dépréciation | 8.3 | 7.4 | ✅ |
| 7 | Doctrine ORM 3 / DBAL 4 | 8.3 | 7.4 | ⬜ |
| — | ⚠️ VPS de prod en PHP 8.4 | 8.4 | 7.4 | ⬜ |
| 8 | Symfony 8.1 | 8.4 | 8.1 | ⬜ |
| 9 | Refonte UI : Bootstrap → Shadcn | 8.4 | 8.1 | ⬜ |

⬜ à faire · 🟡 en cours · ✅ fait

## Détail

### 0. Docker
- Image PHP avec `ARG PHP_VERSION`, alignée sur la prod (8.3)
- Composer via `COPY --from=composer:2`
- Service Postgres 16 + base de test

### 1. Suppression de l'API
- API Platform, lexik/jwt, gesdinet/jwt-refresh-token, nelmio/cors
- Entité `RefreshToken`, firewalls `login` / `api`, `#[ApiResource]`, `CurrentUserExtension`

### 2. Outillage
- PHPUnit 11 + `dama/doctrine-test-bundle`
- PHP CS Fixer (`@Symfony`, `@Symfony:risky`, `@PHP81Migration`)
- Rector (configuré, appliqué en phase 4)
- PHPStan niveau 6 avec baseline
- Symfony 6.3 étant EOL avec des failles non corrigées, les commandes Composer qui résolvent des dépendances passent par `--no-security-blocking` jusqu'à la phase 4

### 3. Tests de non-régression
- Smoke test de toutes les routes GET (user + admin)
- Services de calcul (positions, farming, deposit, DCA)
- Commandes de mise à jour des prix (`MockHttpClient`)
- Formulaires principaux

### 4. Symfony 6.4
- Retrait de `sensio/framework-extra-bundle`, `doctrine/annotations`, `symfony/proxy-manager-bridge`, `symfony/webapp-meta`
- Rector + `recipes:update`
- Zéro dépréciation
- Plus besoin de `--no-security-blocking` ; vérifier que les tests « risky » (handlers d'erreurs non restaurés) disparaissent
- Reste une dépréciation indirecte dans un template d'EasyAdmin 4 (`macro` hors racine, Twig 3.28), corrigée par EasyAdmin 5 en phase 8

### 5. Asset Mapper
- Encore → importmap + `stimulus-bundle` : chaque entrée Webpack devient un entrypoint de `importmap.php`, appelé via le bloc `importmap` des templates
- Bootstrap conservé tel quel (CSS compilé) ; les variables Sass du thème sont reportées en variables CSS dans `assets/styles/app.css`
- Bootstrap Icons via le paquet Composer `twbs/bootstrap-icons` (l'importmap ne télécharge pas les polices)
- `moment` et `pikaday` retirés (inutilisés)
- Suppression de `webpack.config.js`, `package.json`, `public/build`
- En prod, `bin/console asset-map:compile` à chaque déploiement (`importmap:install` passe par `composer install`)

### 6. Symfony 7.4
- Passage direct 6.4 → 7.4 (possible grâce à l'étape 4), contrainte PHP relevée à `>=8.3`
- Bundles tiers à jour (EasyAdmin 4.x, knp-menu, reset-password, monolog-bundle 4)
- Client CoinGecko maison dans `src/Coingecko/` (client, DTO, exception) en remplacement de `codenix-sv/coingecko-api` ; client HTTP scopé `coingecko.client` qui attend et réessaie sur un 429 (rate limit)
- `symfony/phpunit-bridge` + `SymfonyExtension` (comme gaia), absent jusque-là car la 6.x ne gère que PHPUnit 9
- `recipes:update` : `APP_SECRET` sort de `.env` (à définir en prod dans `.env.local`), routes via `routing.controllers`
- Zéro dépréciation, y compris indirecte dans les tests ; reste celle du template EasyAdmin 4 au warmup (phase 8)

### 7. Doctrine ORM 3
- ORM 3 + DBAL 4, isolé dans ses propres commits

### 8. Symfony 8.1
- Prérequis : PHP 8.4 en prod
- Rector, EasyAdmin 5.x
- `ignoreIndirectDeprecations` à `false` dans `phpunit.dist.xml` : les dépréciations venant des templates Twig ou des vendors font alors échouer les tests (impossible avant, à cause du template d'EasyAdmin 4)

### 9. Refonte UI
- Tailwind + kit Shadcn (Symfony UX Toolkit)
- JS Bootstrap (modales, tooltips, onglets) → Stimulus
- Thème de formulaires à remplacer
