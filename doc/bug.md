# Bugs

## DCA Auto : pas d'erreur visible sur la crypto manquante

Sur `/strategy/dca/`, dialogue « DCA Auto » : valider sans choisir de crypto bloque bien le formulaire, mais aucun message ne s'affiche sur le champ.

- Choices.js masque le vrai `<select>` : `reportValidity()` (`dca_controller.js`) ne peut pas le cibler.
- Console : `An invalid form control with name='dca_auto[coin]' is not focusable.`
- Déjà présent avant Turbo (Choices était chargé par `select2.js`).
- Touche tout select `js-select2` requis validé côté navigateur.

## DatePicker coupé par la carte

Sur les formulaires dans une carte (ex. `/strategy/farming/new`), le calendrier du DatePicker passe sous le bas de la carte : les dernières semaines sont invisibles.

- `Card` a `overflow-hidden` (`templates/components/Card.html.twig`), le contenu du popover est en `absolute`.
- `overflow-hidden` ne sert qu'aux images en premier ou dernier enfant, déjà arrondies par `*:[img:first-child]:rounded-t-[22px]`.
- Touchera aussi la liste déroulante de Tom Select (roadmap, point 1).

## Repéré pendant la refacto, non corrigé

- Routes `/{id}/infos` (positions, emprunts, NFT, farming, LP) en POST alors qu'elles ne font que lire. Touche `remote_dialog_controller.js` et `App:InfoButton`.
- `Cryptocurrency::symbol` fait 8 caractères : un symbole CoinGecko plus long fait échouer le flush de `crypto:updateDatas` pour tous les coins. Demande une migration.
- `crypto:updateDatas` fait un appel CoinGecko par coin, sans pause : au-delà de la limite de l'API, les coins suivants sont ignorés. Un appel groupé `coins/markets` réglerait le problème.
- Coin créé dans l'admin pendant une panne CoinGecko : `libelle` reste nul, erreur SQL.
- Dépôts convertis en USD au taux du jour : le total « Investi » bouge avec le change. Il faudrait stocker le taux à la date du dépôt.
- DCA Auto : jusqu'à 1000 positions dans un seul formulaire.
- Cache du dashboard : un calcul commencé avant un flush peut être enregistré après son invalidation. Données périmées jusqu'à la prochaine mise à jour des prix ou la fin du TTL.
