# Roadmap

## Fait

- **Socle** : Symfony 8.1, PHP 8.5, code rangé par feature, outils qualité sans baseline, vérification complète par `make ci` (Symfony Language Tools compris).
- **Historique du wallet** : snapshot horaire de la valeur totale, planifié par Symfony Scheduler, historique rétroactif sur 30 jours (`wallet:backfill`).
- **Dashboard** : cache par utilisateur, graphique de l'historique par période, bascule entre le $ et la monnaie préférée, wallet crypto limité aux plus grosses lignes.
- **Refonte de l'UI** : thèmes clair et sombre, Turbo, kit shadcn, selects en `ux-autocomplete`.
- **Page d'accueil** : page publique sur `/`, dashboard sur `/dashboard`, nouveau logo et favicon, référencement minimal.
- **Déploiement** : dépôt sur GitLab (GitHub en miroir), Deployer + GitLab CI, staging `dev.cryptobook.fr` sur VPS1, prod `cryptobook.fr` sur VPS2 (`v0.2.0`), données de l'ancienne prod migrées, worker du Scheduler en systemd, pas de pipeline pour un changement de doc seule, jobs de vérification en parallèle.

## À faire

Un point = une PR vers `dev`.

Rien de prévu.

## Hors périmètre

- Pages de connexion et d'inscription au style de la page d'accueil.
- Sitemap.
- Bascule $ / monnaie préférée sur les autres pages (positions, prêts, NFT, stratégies), qui restent en $.
- Copie externe des sauvegardes.
- Previews par branche.
- Mode worker de FrankenPHP.
