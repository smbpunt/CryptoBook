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
- Pages : titres en `font-display` (`App:PageHeader`), listes en carte (`App:ListCard`, onglets en pastilles, actions en icônes), montants en français (filtres `amount`, `price`, `quantity`, `percent`, `day`), `App:Delta` pour les variations, totaux en KPI comme le dashboard. Auth et « Email envoyé » en français, `<title>` « Page · CryptoBook », header déconnecté à 60 px. Aucune page ne défile horizontalement à 390 px.
- Formulaires : messages de validation en français (`translations/validators.en.yaml`), champs alignés, DatePicker plus coupé par la carte, boutons « Ajouter » / « Enregistrer », « Valider & continuer » en `secondary`.

## Hors périmètre

- Renommer entités et colonnes en anglais (`Vente`, `libelle`).
- Montants en float.
