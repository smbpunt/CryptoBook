# Roadmap

## Fait

- **Socle** : Symfony 8.1, PHP 8.5, code rangé par feature, outils qualité sans baseline, vérification complète par `make ci` (Symfony Language Tools compris).
- **Historique du wallet** : snapshot horaire de la valeur totale, planifié par Symfony Scheduler, historique rétroactif sur 30 jours (`wallet:backfill`).
- **Dashboard** : cache par utilisateur, graphique de l'historique par période, bascule entre le $ et la monnaie préférée, wallet crypto limité aux plus grosses lignes, axe du graphique recadré sur la valeur.
- **Refonte de l'UI** : thèmes clair et sombre, Turbo, kit shadcn, selects en `ux-autocomplete`.
- **Page d'accueil** : page publique sur `/`, dashboard sur `/dashboard`, nouveau logo et favicon, référencement minimal.
- **Déploiement** : dépôt sur GitLab (GitHub en miroir), Deployer + GitLab CI, staging `dev.cryptobook.fr` sur VPS1, prod `cryptobook.fr` sur VPS2 (`v0.2.0`), données de l'ancienne prod migrées, worker du Scheduler en systemd, pas de pipeline pour un changement de doc seule, jobs de vérification en parallèle.

## À faire

Un point = une PR vers `dev`.

### 1. Règles de validation sur les entités

Seules les positions et les ventes refusent les valeurs incohérentes : un emprunt, un farming, une LP, un NFT ou un dépôt acceptent des quantités ou des prix négatifs.

- Quantités, prix, APR et montants : positifs (ou nuls quand zéro a un sens)
- Dépôt : montant strictement positif, pas de retrait par un montant négatif
- Pourcentages bornés, textes obligatoires non vides et limités à la taille de leur colonne, dates cohérentes entre elles (vente après achat)
- Contraintes du Validator sur les entités, messages en français, sans contrainte en base ni migration
- Une donnée existante hors règle n'est signalée qu'à sa prochaine modification

### 2. Bugs de `doc/bug.md`

À détailler avant de commencer : choisir les bugs à corriger et les découper en points.

Ensuite : point avec les utilisateurs sur les fonctionnalités à retirer ou à ajouter.

## Hors périmètre

- Pages de connexion et d'inscription au style de la page d'accueil.
- Sitemap.
- Bascule $ / monnaie préférée sur les autres pages (positions, prêts, NFT, stratégies), qui restent en $.
- Copie externe des sauvegardes.
- Previews par branche.
- Mode worker de FrankenPHP.
