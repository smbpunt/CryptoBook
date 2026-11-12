# Roadmap

## Fait

- **Socle** : Symfony 8.1, PHP 8.5, code rangé par feature, outils qualité sans baseline, vérification complète par `make ci` (Symfony Language Tools compris).
- **Historique du wallet** : snapshot horaire de la valeur totale, planifié par Symfony Scheduler, historique rétroactif sur 30 jours (`wallet:backfill`).
- **Dashboard** : cache par utilisateur, graphique de l'historique par période, bascule entre le $ et la monnaie préférée.
- **Refonte de l'UI** : thèmes clair et sombre, Turbo, kit shadcn, selects en `ux-autocomplete`.
- **Page d'accueil** : page publique sur `/`, dashboard sur `/dashboard`, nouveau logo et favicon, référencement minimal.
- **Déploiement** : dépôt sur GitLab (GitHub en miroir), Deployer + GitLab CI, staging `dev.cryptobook.fr` sur VPS1, prod `cryptobook.fr` sur VPS2 (`v0.2.0`), données de l'ancienne prod migrées, worker du Scheduler en systemd, pas de pipeline pour un changement de doc seule, jobs de vérification en parallèle.

## À faire

Un point = une PR vers `dev`.

### 1. Wallet crypto du dashboard limité aux plus grosses lignes

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
