# Bugs

## Repéré pendant la refacto, non corrigé

- Routes `/{id}/infos` (positions, emprunts, NFT, farming, LP) en POST alors qu'elles ne font que lire. Touche `remote_dialog_controller.js` et `App:InfoButton`.
- `Cryptocurrency::symbol` fait 8 caractères : un symbole CoinGecko plus long fait échouer le flush de `crypto:updateDatas` pour tous les coins. Demande une migration.
- `crypto:updateDatas` fait un appel CoinGecko par coin, sans pause : au-delà de la limite de l'API, les coins suivants sont ignorés. Un appel groupé `coins/markets` réglerait le problème.
- Coin créé dans l'admin pendant une panne CoinGecko : `libelle` reste nul, erreur SQL. Piste : appeler CoinGecko sur `FormEvents::SUBMIT` dans `CryptocurrencyCrudController` (comme `FiatCurrencyCrudController::fetchRates`), puis `NotBlank` sur `libelle` et `symbol` : la panne devient une erreur de formulaire.
- Email unique sensible à la casse : `A@x.fr` et `a@x.fr` créent deux comptes. Mettre l'email en minuscules dans `User::setEmail()`.
- Admin : l'action NEW de `UserCrudController` est active, mais son formulaire n'a que les rôles : il répond toujours « Veuillez saisir un email. ». Désactiver `Action::NEW`.
- `StrategyLp::lpDeposit` (« Nombre dans la LP ») n'est utilisé nulle part : lui donner un usage ou le retirer.
- Dépôts convertis en USD au taux du jour : le total « Investi » bouge avec le change. Il faudrait stocker le taux à la date du dépôt.
- DCA Auto : jusqu'à 1000 positions dans un seul formulaire.
- Cache du dashboard : un calcul commencé avant un flush peut être enregistré après son invalidation. Données périmées jusqu'à la prochaine mise à jour des prix ou la fin du TTL.

## Repéré, non tranché

- Docker local en Postgres 16, serveurs et CI en 18.
- Admin : le CRUD des types de dépôt (`/admin/deposit-type`) n'est pas dans le menu.
- PHP en UTC : « aujourd'hui » est la date UTC, en retard d'un jour sur la France entre minuit et 1 h ou 2 h.
- `/favicon.ico` n'existe pas : un client qui le demande sans lire le HTML reçoit la page 404.
