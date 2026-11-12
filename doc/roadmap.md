# Roadmap

## Fait

- **Socle** : Symfony 8.1, PHP 8.5, code rangé par feature, outils qualité sans baseline.
- **Historique du wallet** : snapshot horaire de la valeur totale, planifié par Symfony Scheduler, historique rétroactif sur 30 jours (`wallet:backfill`).
- **Dashboard** : cache par utilisateur, graphique de l'historique par période.
- **Refonte de l'UI** : thèmes clair et sombre, Turbo, kit shadcn, selects en `ux-autocomplete`.
- **Déploiement** : dépôt sur GitLab (GitHub en miroir), Deployer + GitLab CI, staging `dev.cryptobook.fr` sur VPS1, prod `cryptobook.fr` sur VPS2 (`v0.2.0`), données de l'ancienne prod migrées, worker du Scheduler en systemd, pas de pipeline pour un changement de doc seule, jobs de vérification en parallèle.

## À faire

Un point = une PR vers `dev`.

### 1. `make ci` et Symfony Language Tools

Indépendant du reste, peut passer en premier. Une seule commande pour tout vérifier pendant le développement, en prévision d'une future pipeline.

- `make ci` lance toute la vérification : composer, lints, PHP CS Fixer, Rector, PHPStan, Deptrac, PHPUnit
- Ajout du contrôle Symfony Language Tools (`symfony-lsp check`), en bêta expérimentale
- `CLAUDE.md` renvoie vers `make ci`

Spec : `doc/specs/make-ci-symfony-lsp.md`

### 2. Bascule entre le $ et la monnaie préférée

Indépendant du reste.

- Bouton sur le dashboard, qui convertit tout ce qu'il affiche : KPI, graphique, répartition, lignes
- Monnaie préférée par défaut
- Choix du $ gardé par appareil dans un cookie de 30 jours, prolongé à chaque visite. Sans visite, retour à la monnaie préférée
- Rendu côté serveur : pas de clignotement, pas de conversion en JavaScript
- Bouton masqué si la monnaie préférée est déjà le $

Spec : `doc/specs/dashboard-currency-switch.md`

### 3. Nouveau favicon et page d'accueil publique

Indépendant du reste. Un visiteur non connecté tombe aujourd'hui sur le formulaire de connexion, sans rien savoir de l'application. La page d'accueil affiche le logo : favicon et page dans la même PR.

Favicon :

- Icône SVG aux couleurs de l'application, lisible en clair et en sombre
- 2 ou 3 propositions à valider avant l'intégration
- PNG de secours et icône pour iOS

Page d'accueil :

- `/` devient une page publique, d'après la maquette : https://claude.ai/artifact/YLUF213hoKirnQg19f8dzj
- Sections de la maquette : accroche avec graphique, tableau de bord, positions, cryptos, dépôts, appel à créer un compte
- Chiffres d'exemple en dur, thèmes clair et sombre
- Le dashboard passe sur `/dashboard`. Connecté, `/` y redirige
- Pages de connexion, d'inscription et de mot de passe oublié inchangées
- Référencement minimal : titre, meta description, Open Graph, `robots.txt`. Le staging reste en noindex

### 4. Wallet crypto du dashboard limité aux plus grosses lignes

Indépendant du reste, peut passer en premier. La card liste tout le wallet, miettes comprises, et dépasse largement la card « Répartition » à côté.

- Les plus grosses lignes seulement, pour que la card fasse la hauteur de « Répartition »
- Le reste regroupé dans une ligne « Autres » : nombre d'actifs et valeur cumulée
- Détail complet par le lien « Voir les positions », pas de scroll dans la card
- Même limite sur mobile, où les deux cards sont empilées

## Hors périmètre

- Pages de connexion et d'inscription au style de la page d'accueil.
- Sitemap.
- Bascule $ / monnaie préférée sur les autres pages (positions, prêts, NFT, stratégies), qui restent en $.
- Copie externe des sauvegardes.
- Previews par branche.
- Mode worker de FrankenPHP.
