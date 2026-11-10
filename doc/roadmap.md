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

## À faire

Un point = une PR vers `dev`.

### 1. Ménage du front

Des restes sans aucun usage, à supprimer sans risque. Indépendant du reste.

- `assets/vendor/flatpickr`, `bootstrap`, `@popperjs` : ignorés par git et absents d'`importmap.php`, un `rm -rf` suffit.
- `assets/vendor/installed.php` : entrée `chart.js/auto` 3.9.1, reste de l'ancien graphique.
- `assets/controllers/hello_controller.js` : utilisé nulle part.

### 2. Choices.js → Symfony UX Autocomplete

C'est le plus gros gain : Choices.js 9.1.0 date de 2019, et il demande un contournement dans le thème de formulaire et du CSS à part.

- Aujourd'hui : classe `js-select2` (19 occurrences), `choices_controller.js`, environ 14 règles CSS dans `app.css`, contournement dans `form/shadcn_layout.html.twig` (`choice_widget_collapsed`).
- `symfony/ux-autocomplete` (Tom Select, compatible AssetMapper) : `'autocomplete' => true` sur les `EntityType`.
- Liste des cryptos, qui est longue : autocomplete AJAX avec `#[AsEntityAutocompleteField]`.
- Blockchain → dapp (loan, farming, LP) : remplacer `dependent_select_controller.js` par une option `filter` sur la blockchain si elle suffit, sinon garder le contrôleur.
- CSS de Tom Select repris avec les tokens, comme aujourd'hui pour Choices.
- Corrige le bug de `doc/bug.md` : un select requis vide bloque l'envoi sans afficher de message (le vrai `<select>` est masqué par Choices).

### 3. Pages : listes, titres, auth

Finit la refonte de l'UI hors formulaires. En parallèle du point 2.

- `App:PageHeader` : titre en `font-display`, à la taille du dashboard.
- Listes (dépôts, emprunts, NFT, positions, projets, farming, DCA) : tableau dans une carte, en-tête comme le dashboard, montants au format français (filtres `amount`, `percent`…, il reste environ 40 `number_format`), `App:Delta` pour les variations.
- Auth : titres homogènes, page « Email envoyé » en français.
- `<title>` homogènes, page d'erreur.
- Mobile : aucune page ne défile horizontalement aujourd'hui. Dashboard à revérifier à 390 px.

### 4. Pages : formulaires

Après le point 2, pour styler Tom Select et non Choices.

- Champs, autocomplete, DatePicker et erreurs, dans les deux thèmes.
- « Valider & continuer » en `variant="secondary"`.

### 5. Routes `/{id}/infos` en GET

Positions, emprunts, NFT, farming, LP : en POST alors qu'elles ne font que lire. Touche `remote_dialog_controller.js` et `App:InfoButton`.

### 6. Symbole crypto plus long

`Cryptocurrency::symbol` fait 8 caractères : un symbole CoinGecko plus long fait échouer le flush de `crypto:updateDatas` pour tous les coins. Demande une migration.

### 7. `crypto:updateDatas` groupé

Un appel CoinGecko par coin, sans pause : au-delà de la limite de l'API, les coins suivants sont ignorés. Un appel groupé `coins/markets` réglerait le problème.

### 8. Coin sans nom dans l'admin

Coin créé dans l'admin pendant une panne CoinGecko : `libelle` reste nul, erreur SQL.

### 9. Taux de change des dépôts

Dépôts convertis en USD au taux du jour : le total « Investi » bouge avec le change. Stocker le taux à la date du dépôt.

### 10. DCA Auto

Jusqu'à 1000 positions dans un seul formulaire.

### 11. Cache du dashboard

Un calcul commencé avant un flush peut être enregistré après son invalidation : données périmées jusqu'à la prochaine mise à jour des prix ou la fin du TTL.

## Hors périmètre

- Renommer entités et colonnes en anglais (`Vente`, `libelle`).
- Montants en float.
