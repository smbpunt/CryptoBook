# Bugs

## DCA Auto : pas d'erreur visible sur la crypto manquante

Sur `/strategy/dca/`, dialogue « DCA Auto » : valider sans choisir de crypto bloque bien le formulaire, mais aucun message ne s'affiche sur le champ.

- Choices.js masque le vrai `<select>` : `reportValidity()` (`dca_controller.js`) ne peut pas le cibler.
- Console : `An invalid form control with name='dca_auto[coin]' is not focusable.`
- Déjà présent avant Turbo (Choices était chargé par `select2.js`).
- Touche tout select `js-select2` requis validé côté navigateur.
