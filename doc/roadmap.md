# Roadmap

## Fait

- **Socle** : Symfony 8.1, PHP 8.5, code rangé par feature, outils qualité sans baseline.
- **Historique du wallet** : snapshot horaire de la valeur totale, planifié par Symfony Scheduler.
- **Dashboard** : cache par utilisateur, graphique de l'historique par période.
- **Refonte de l'UI** : thèmes clair et sombre, Turbo, kit shadcn, selects en `ux-autocomplete`.

## À faire

Un point = une PR vers `dev`.

### 1. Déploiement par le framework FrankenPHP, staging sur VPS1

Prérequis de la bascule. La prod tourne encore sur l'ancien code : aucun snapshot n'y est enregistré, chaque heure d'attente est perdue. On reprend le framework existant (step 19, template `smbai/symfony`) plutôt que d'écrire un outillage maison.

- Site et base créés par le step 19 (`--site=cryptobook --with-db`) : Caddy, `serverVersion` aligné sur le Postgres installé
- `deploy.php` Deployer et job CI repris du template, avec les étapes propres au projet : `importmap:install`, `tailwind:build`, `asset-map:compile`
- Code sur GitHub, GitLab en miroir : le pipeline déploie depuis le miroir
- Un seul PHP, le 8.5 embarqué dans FrankenPHP (`frankenphp php-cli`) pour le web, Deployer et le worker. Le PHP 8.4 du VPS ne fait pas tourner l'app
- Unité systemd du worker adaptée au layout Deployer (`deployer`, `current`), redémarrée à chaque déploiement
- Audit root avant tout : extensions du binaire (`intl`, `pdo_pgsql`, `zip`, `pcntl`), version de Postgres
- Sauvegarde locale (`pg_dump` quotidien, avec rotation) et ping de vie externe après chaque snapshot horaire, sauf si le framework les fournit déjà
- Staging sur VPS1, alimenté par un dump de la prod actuelle : sert de répétition de la bascule

Spec : `doc/specs/vps-deploy-staging.md`

### 2. Bascule de la prod vers VPS2

Release `v0.2.0` (`dev` vers `main`, commits du point 1) et migration des données.

- VPS2 préparé par le step 19 et déployé par le même pipeline que le staging
- Bascule avec une courte coupure : dump final de l'ancien VPS, restauration, migrations, changement DNS
- Après la restauration : comparer le nombre de lignes par table entre l'ancienne et la nouvelle base
- Worker de prod activé seulement après la bascule (sinon deux machines appellent Fixer)
- L'ancien VPS reste intact pour le retour arrière, coupé 2 semaines, puis résilié

Spec : `doc/specs/prod-cutover.md`

### 3. Historique rétroactif sur 30 jours

Après la bascule : donne tout de suite un graphique rempli à chaque utilisateur, sans attendre un mois de snapshots.

- 30 jours, un point par heure, comme les snapshots réels
- Wallet actuel appliqué sur toute la période : on ne reconstitue pas les achats et ventes passés
- Prix historiques pris chez CoinGecko, un appel par coin
- Miettes : un coin n'est récupéré que si au moins un utilisateur en détient 5 $ ou plus. Un coin récupéré compte pour tous ses détenteurs
- Les snapshots réels déjà enregistrés ne sont jamais écrasés

### 4. `make ci` et Symfony Language Tools

Indépendant du reste, peut passer en premier. Une seule commande pour tout vérifier pendant le développement, en prévision d'une future pipeline.

- `make ci` lance toute la vérification : composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, PHPUnit
- Ajout du contrôle Symfony Language Tools (`symfony-lsp check`), en bêta expérimentale
- `CLAUDE.md` renvoie vers `make ci`

### 5. Bascule entre le $ et la monnaie préférée

Indépendant du reste.

- Bouton sur le dashboard, qui convertit tout ce qu'il affiche : KPI, graphique, répartition, lignes
- Monnaie préférée par défaut
- Choix du $ gardé par appareil dans un cookie de 30 jours, prolongé à chaque visite. Sans visite, retour à la monnaie préférée
- Rendu côté serveur : pas de clignotement, pas de conversion en JavaScript
- Bouton masqué si la monnaie préférée est déjà le $

### 6. Nouveau favicon

Indépendant du reste.

- Icône SVG aux couleurs de l'application, lisible en clair et en sombre
- 2 ou 3 propositions à valider avant l'intégration
- PNG de secours et icône pour iOS

## Hors périmètre

- Pipeline CI : `make ci` la prépare, elle viendra plus tard.
- Bascule $ / monnaie préférée sur les autres pages (positions, prêts, NFT, stratégies), qui restent en $.
- Copie externe des sauvegardes.
- Previews par branche.
- Mode worker de FrankenPHP.
