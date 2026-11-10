# Roadmap

## 1. Historique de la valeur du wallet : collecte @histo.md

Enregistrer la valeur totale du wallet de chaque utilisateur au fil du temps. Les quantités passées ne se reconstruisent pas : chaque heure sans enregistrement est perdue.

- Valeur totale = positions crypto + stables (comme le total actuel du dashboard)
- Un snapshot par heure et par utilisateur, avec la valeur totale seule, en USD (convertie dans la devise favorite à l'affichage)
- Pas de reconstitution du passé : l'historique commence au premier snapshot
- On garde tous les snapshots, l'agrégation se fait à la lecture
- Centraliser le calcul de la valeur dans une seule classe, utilisée par le dashboard et par les snapshots
- Symfony Scheduler pour tout planifier : mise à jour des prix, puis snapshot, données CoinGecko, taux fiat. Il remplace les crons actuels
- En prod (VPS) : worker du Scheduler lancé par un service systemd

## 2. Cache du dashboard

Le dashboard recalcule tout à chaque chargement, alors que les données ne changent qu'à la mise à jour des prix ou quand l'utilisateur modifie quelque chose.

- Mesurer d'abord dans le profiler : corriger les requêtes en trop avant de mettre du cache
- Mettre en cache toutes les données du dashboard, par utilisateur
- Cache sur le filesystem, sans infra en plus
- Vider le cache à la mise à jour des prix ou des taux, et quand l'utilisateur modifie ses données

## 3. Refacto SOLID + organisation par feature

Ranger le code par sujet métier (voir `CLAUDE.md`) et le nettoyer au passage.

- Une feature à la fois, une PR par feature
- Dans chaque PR : d'abord déplacer les fichiers sans rien changer, ensuite refactorer
- Commencer par une feature simple (`Deposit`, `Loan`), finir par `Strategy`
- Vérifier que les tests couvrent la feature avant d'y toucher
- Une feature par référentiel (`Cryptocurrency/`, `FiatCurrency/`, `Blockchain/`…)
- Back-office EasyAdmin regroupé dans une feature `Admin/`
- Objectifs :
  - contrôleurs fins
  - fin des classes `*Service`, découpées en classes à responsabilité unique
  - voters pour vérifier le propriétaire
  - DTO à la place des tableaux associatifs
- Les montants restent en float

## 4. Refonte de l'UI + graphique du wallet @ui.md

Donner une identité à l'application, avec l'évolution de la valeur du wallet en pièce centrale du dashboard. Maquette : https://claude.ai/artifact/2g7JtSLYbx78MeF4pJtwHT

Déjà fait : le fond à dégradés granuleux (référence : `doc/grin.jpg`).

### PR 1 — Fondations

- Thème clair et sombre : suit le système par défaut, avec un bouton pour changer
- Couleurs de la maquette : orange (marque) et lavande (graphique), cartes translucides sur le fond
- Typo Inter + Space Grotesk, chiffres tabulaires pour les montants
- Nouveau header : logo, navigation en pastilles, menu compte

### PR 2 — Turbo

- Symfony UX Turbo : navigation sans rechargement complet
- Un seul entrypoint JS, les scripts de page deviennent des contrôleurs Stimulus

### PR 3 — Dashboard + graphique

- Dashboard de la maquette, avec ses données réelles
- Courbe de la valeur totale et ligne du total déposé, avec `symfony/ux-chartjs` à la place du Chart.js 3 actuel
- Périodes : 24h, 7j, 30j, 90j, 1an, Tout (horaire jusqu'à 7j, par jour au-delà)
- Changement de période sans rechargement (Turbo Frame)
- Variation sur 24 h, plus haut historique
- Répartition en donut SVG, sans JS

### PR 4 — Reste des pages

- Listes, formulaires, pages d'auth, page d'erreur
- Mobile : dashboard lisible sur téléphone, le reste suit les recommandations shadcn/Tailwind sans effort particulier
