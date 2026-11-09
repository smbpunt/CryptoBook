# Roadmap

## 1. Historique de la valeur du wallet : collecte

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

## 4. Refonte de l'UI

Donner une identité à l'application, qui reste aujourd'hui trop basique.

- Thème clair et sombre : suit le système par défaut, avec un bouton pour changer
- Fond à dégradés colorés granuleux, discret, sur toutes les pages. Le contenu reste sur des cartes opaques (référence : `doc/grin.jpg`)
- Accents orange (principal) et lavande (secondaire)
- Typo sans-serif moderne, chiffres tabulaires pour les montants
- Toute l'app : layout, navigation, dashboard, listes, formulaires, pages d'auth
- Mobile : dashboard lisible sur téléphone, le reste suit les recommandations shadcn/Tailwind sans effort particulier
- Ajouter Symfony UX Turbo (navigation sans rechargement complet) et vérifier qu'il fonctionne avec le JS existant

## 5. Historique de la valeur du wallet : graphique

Afficher l'évolution de la valeur totale sur le dashboard, en pièce centrale de la nouvelle UI.

- Périodes : 24h, 7j, 30j, 1an, Tout (agrégées par jour au-delà de 24h)
- Changement de période sans rechargement (Turbo Frame)
- En plus de la courbe : ligne du total déposé, plus haut et plus bas de la période
- `symfony/ux-chartjs` à la place du Chart.js 3 actuel
