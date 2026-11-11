# Roadmap

## Fait

- **Historique du wallet** : snapshot horaire de la valeur totale par utilisateur, en USD (`wallet:snapshot`). Calcul centralisé dans `WalletValueCalculator`. Tout est planifié par Symfony Scheduler (`MainSchedule`), avec en prod un worker lancé par systemd.
- **Cache du dashboard** : cache filesystem taggé par utilisateur, invalidé par un listener Doctrine.
- **Refacto par feature** : code rangé par sujet métier, classes `*Service` découpées, `OwnerVoter`, DTO, Deptrac, PHPStan sans baseline.
- **Refonte de l'UI** (plan : @ui.md) :
  - thèmes clair et sombre, tokens, typo Inter + Space Grotesk, header en onglets ;
  - Turbo avec un seul entrypoint : les scripts de page sont des contrôleurs Stimulus (`collection`, `dependent-select`, `row-toggle`, `dca`…) ;
  - dashboard de la maquette, avec le graphique du wallet en `symfony/ux-chartjs` (Chart.js 4).
- Calendrier : les `DateType` utilisent le DatePicker du kit shadcn, flatpickr n'est plus chargé.
- Ménage du front : contrôleurs Stimulus inutilisés (`hello`, démos `calendar-display` et `input-group-display` du kit) supprimés.
- Selects : Choices.js remplacé par `symfony/ux-autocomplete` (Tom Select), cryptos en AJAX (`CryptocurrencyAutocompleteField`). `dependent_select_controller.js` est conservé : ux-autocomplete n'a pas de filtre dépendant d'un autre champ sans JS.

## À faire

Un point = une PR vers `dev`.

### 1. Pages : listes, titres, auth

Finit la refonte de l'UI hors formulaires.

- `App:PageHeader` : titre en `font-display`, à la taille du dashboard.
- Listes (dépôts, emprunts, NFT, positions, projets, farming, DCA) : tableau dans une carte, en-tête comme le dashboard, montants au format français (filtres `amount`, `percent`…, il reste environ 40 `number_format`), `App:Delta` pour les variations.
- Auth : titres homogènes, page « Email envoyé » en français.
- `<title>` homogènes, page d'erreur.
- Mobile : aucune page ne défile horizontalement aujourd'hui. Dashboard à revérifier à 390 px.

### 2. Pages : formulaires

- Champs, autocomplete, DatePicker et erreurs, dans les deux thèmes.
- « Valider & continuer » en `variant="secondary"`.

## Hors périmètre

- Renommer entités et colonnes en anglais (`Vente`, `libelle`).
- Montants en float.
