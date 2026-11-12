# Plan : bascule entre le $ et la monnaie préférée

Couvre le point 4 de `roadmap.md`. Un contrôle « EUR | USD » sur le dashboard, un cookie, une route POST. Aucun changement de schéma, des données en cache (toujours en USD) ni des autres pages.

## État des lieux

- `DashboardData` (en cache par utilisateur) porte les montants en USD, plus `currencySymbol`, `favoritePerUsd`, `usdPerFavorite`. La conversion se fait au rendu : `dashboard.toFavorite(…)|amount(symbol)`, 17 occurrences dans 5 templates (`_history` 3, `_side` 5, `_kpis` 5, `_allocation` 2, `_holdings` 2).
- `WalletHistory` (en cache) est en USD. `WalletHistoryChartFactory::create($history, $favoritePerUsd)` convertit les points, `wallet_chart_controller.js` reçoit le symbole par `data-wallet-chart-currency-value`.
- Conséquence : remplacer le taux par 1,0 et le symbole par `$` au rendu convertit tout le dashboard, graphique compris. Aucune clé de cache par monnaie, aucun JavaScript.
- Deux mentions « équivalent en dollars » existent en monnaie préférée, sous la condition `symbol != '$'` : `_history.html.twig` ligne 25 (« soit 26 000 $ ») et `_kpis.html.twig` ligne 5 (aide du KPI « Investi »).
- Monnaies en base : EUR `€`, USD `$`, AUD `$A`. `DashboardData` ne porte pas le code ISO.
- Aucun cookie applicatif aujourd'hui. Jetons CSRF des POST hors formulaire : `csrf_token('<id>')` dans un champ `_token`, vérifié par `#[IsCsrfTokenValid]` (`App:DeleteButton`, `DepositController::delete`).
- Turbo 8.0.23 vide son cache de snapshots après un POST de formulaire.
- Maquette (https://claude.ai/artifact/2g7JtSLYbx78MeF4pJtwHT) : contrôle segmenté « EUR | USD » dans le header global, à gauche du bouton de thème.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Enregistrement du choix | Formulaire POST / lien GET / cookie posé en JS | Formulaire POST avec jeton CSRF, redirection 303 vers le dashboard |
| Emplacement | Header global (maquette) / en-tête de la page dashboard / card du graphique | En-tête de la page dashboard, avant « Ajouter une position ». Pas dans le header global : la bascule n'agit que sur le dashboard. Style de la maquette |
| Libellés | Codes ISO / symboles | Codes ISO, comme la maquette : `EUR` / `USD` (`AUD` / `USD`…) |
| Monnaie préférée = USD | — | Aucun contrôle affiché, cookie ignoré et non prolongé |
| Équivalent « soit … $ » en mode $ | Masqué / inversé (« soit … € ») | Masqué, ainsi que l'aide en dollars du KPI « Investi » |
| Prolongation du cookie | Rendu du dashboard / toute page | À chaque rendu du dashboard en mode $ (page et changement de période). Pas de listener global |
| Contenu du cookie | Choix du $ seul / les deux choix | Le cookie n'existe qu'en mode $. Revenir à la monnaie préférée le supprime |
| Cache | Clé par monnaie / conversion au rendu | Conversion au rendu : `DashboardData::inUsd()`. Le cache reste en USD |
| `toFavorite()` | Renommer / garder | Gardé tel quel (17 occurrences) : en mode $, il multiplie par 1,0 |
| Période du graphique après une bascule | Conservée / 30 j | Retour à 30 j (rechargement de la page) |
| Jeton CSRF invalide | — | Comportement du framework (comme les suppressions), pas de traitement ni de test |

## Données (`src/Dashboard/`)

### `Dashboard/Dto/DashboardData`

- Nouveau paramètre de constructeur `public string $currencyCode` (code ISO, ex. `EUR`), placé juste avant `$currencySymbol`.
- `isUsd(): bool` : `FiatCurrency::KEY_USD === $this->currencyCode`.
- `inUsd(): self` : nouvelle instance avec les mêmes holdings, montants USD et `snapshotStats`, et `currencyCode = FiatCurrency::KEY_USD`, `currencySymbol = '$'`, `usdPerFavorite = 1.0`, `favoritePerUsd = 1.0`. Docblock : `The same dashboard, displayed in USD.`

### `Dashboard/Builder/DashboardBuilder`

- Passe `(string) $currency->getFixerKey()` pour `currencyCode`.

### `Dashboard/Cache/DashboardCache`

- `VERSION` : `'v2'` → `'v3'` (le DTO en cache change).

### `Dashboard/Enum/DisplayCurrency`

```php
enum DisplayCurrency: string
{
    case Favorite = 'favorite';
    case Usd = 'usd';
}
```

### `Dashboard/Cookie/DisplayCurrencyCookie`

`final readonly`, constructeur : `ClockInterface`. Constantes `public const string NAME = 'dashboard_currency';` et `private const string LIFETIME = '+30 days';`.

- `read(Request $request): DisplayCurrency` : `Usd` si la valeur du cookie est exactement `'usd'`, sinon `Favorite` (cookie absent ou toute autre valeur).
- `write(Response $response, DisplayCurrency $currency): void` :
  - `Usd` : `$response->headers->setCookie(Cookie::create(self::NAME, DisplayCurrency::Usd->value, $this->clock->now()->modify(self::LIFETIME)))`. Valeurs par défaut de `Cookie::create()` gardées : chemin `/`, HttpOnly, SameSite `lax`, `secure` automatique.
  - `Favorite` : `$response->headers->clearCookie(self::NAME)`.

## Contrôleur (`Dashboard/Controller/DashboardController`)

Injecte `DisplayCurrencyCookie` en plus.

Monnaie affichée, calculée de la même façon dans `index()` et `history()` (méthode privée) :

1. `$favorite = $this->dashboardCache->get($user)`.
2. `$display` = `DisplayCurrency::Favorite` si `$favorite->isUsd()`, sinon `$cookie->read($request)`.
3. `$dashboard` = `$favorite->inUsd()` si `$display` vaut `Usd`, sinon `$favorite`.
4. Après `render()` : si `$display` vaut `Usd`, `$cookie->write($response, DisplayCurrency::Usd)` (prolongation). Sinon la réponse ne touche pas au cookie.

- `index(Request $request, #[CurrentUser] User $user)` : passe au template `dashboard` (étape 3), `displayCurrency` (`$display`) et `favoriteCode` (`null` si `$favorite->isUsd()`, sinon `$favorite->currencyCode`).
- `history()` : passe `dashboard` (étape 3). Le graphique reçoit `$dashboard->favoritePerUsd`, donc 1,0 en mode $. `DashboardCache::getHistory()` inchangé.
- Nouvelle action :

```php
#[Route('/currency/{currency}', name: 'app_dashboard_currency', methods: ['POST'])]
#[IsCsrfTokenValid('dashboard-currency')]
public function currency(DisplayCurrency $currency): Response
```

  Redirige vers la route du dashboard (`home`) en `Response::HTTP_SEE_OTHER`, puis `$cookie->write($response, $currency)`. Valeur inconnue : 404 (résolution de l'enum). Elle ne lit pas la monnaie préférée : un cookie posé par un utilisateur en USD est simplement ignoré à l'affichage.

## Templates (`templates/dashboard/`)

### `_currency_switch.html.twig` (nouveau)

Un formulaire par segment, hors du Turbo Frame `wallet-history`.

```twig
{# Display currency of the dashboard. One POST per segment: the choice is kept in a cookie, the page is rendered server-side. #}
<div role="group" aria-label="Devise d’affichage" class="flex rounded-full bg-muted p-[3px]">
    {% for value, code in {favorite: favoriteCode, usd: 'USD'} %}
        <form method="post" action="{{ path('app_dashboard_currency', {currency: value}) }}" class="flex">
            <input type="hidden" name="_token" value="{{ csrf_token('dashboard-currency') }}">
            <button type="submit" aria-pressed="{{ displayCurrency.value == value ? 'true' : 'false' }}"
                    class="inline-flex min-h-[38px] min-w-11 cursor-pointer items-center justify-center rounded-full px-3 text-[13px] font-semibold text-muted-foreground outline-none hover:text-foreground focus-visible:ring-3 focus-visible:ring-ring/50 aria-pressed:bg-popover aria-pressed:text-foreground aria-pressed:shadow-[0_1px_2px_rgba(0,0,0,.08),0_0_0_1px_var(--border)]">{{ code }}</button>
        </form>
    {% endfor %}
</div>
```

Hauteur totale 44 px (38 + 2 × 3), comme les boutons voisins (`h-11`). Le segment actif reste un bouton : le renvoyer repose le même cookie.

### `index.html.twig`

- Dans le `<div class="flex flex-wrap gap-2">` de l'en-tête, en premier enfant : `{% if favoriteCode %}{{ include('dashboard/_currency_switch.html.twig') }}{% endif %}`. Ajouter `items-center` à ce `div`.

### `_history.html.twig`, `_kpis.html.twig`

- Remplacer les deux conditions `symbol != '$'` par `not dashboard.isUsd`. Rien d'autre : en mode $, « soit … $ » et l'aide en dollars disparaissent d'eux-mêmes.

### Autres templates

- `_side`, `_allocation`, `_holdings` : inchangés.

## Documentation

- `CLAUDE.md`, section « Projet », à la fin de la puce « Dashboard mis en cache… » : `Bascule monnaie préférée / $ : cookie `dashboard_currency` de 30 jours (`DisplayCurrencyCookie`), route POST `app_dashboard_currency`, conversion au rendu par `DashboardData::inUsd()` (le cache reste en USD).`
- `doc/roadmap.md` : supprimer ce point et sa ligne `Spec`, renuméroter les suivants, compléter la ligne « Dashboard » de « Fait » avec `, bascule entre le $ et la monnaie préférée`. La ligne « Bascule $ / monnaie préférée sur les autres pages » de « Hors périmètre » reste.
- Supprimer `doc/specs/dashboard-currency-switch.md` dans le même commit.

## Ordre des commits

1. `refactor(dashboard): carry the currency code in the dashboard data` : `DashboardData` (`currencyCode`, `isUsd()`), `DashboardBuilder`, `VERSION` v3, conditions `not dashboard.isUsd` des deux templates.
2. `feat(dashboard): switch between the favorite currency and USD` : `inUsd()`, `DisplayCurrency`, `DisplayCurrencyCookie`, contrôleur, `_currency_switch.html.twig`, `index.html.twig`, tests, `CLAUDE.md`.
3. `docs: mark the dashboard currency switch as done` : `doc/roadmap.md`, suppression de la spec.

## Tests

Tout dans `tests/Dashboard/Controller/DashboardControllerTest` (horloge déjà figée au 2026-11-10 14:20 UTC, `user` en EUR, `admin` en USD, 1 EUR = 1,073941 USD).

Pièges :

- Le cookie de la réponse expire selon l'horloge simulée, le `CookieJar` du client selon l'heure réelle : ne jamais compter sur le cookie renvoyé par une réponse pour la requête suivante (le test casserait après le 10/12/2026). Mode $ : `$this->client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie('dashboard_currency', 'usd'))`. Cookie renvoyé : le lire dans `$this->client->getResponse()->headers->getCookies()` (helper privé `currencyCookie(): ?Cookie`, par nom).
- POST : soumettre le formulaire du crawler (`$crawler->selectButton('USD')->form()`), pour porter le vrai jeton CSRF.

Cas :

- `testShowsTheBlocksOfTheDashboard` (existant, complété) : segments `['EUR', 'USD']`, celui en `aria-pressed="true"` est `EUR`, aucun cookie `dashboard_currency` dans la réponse.
- Mode $ (cookie `usd`, `user`, snapshots 24 000 $ le 2026-11-09 12:00 et 30 000 $ le 2026-11-10 14:00) :
  - total `26 000 $` ; ligne sous le total `['+8,3 %', '+2 000 $', 'depuis le 9 nov.']` (plus de « soit ») ;
  - résultat latent `+24 926 $`, variation 24 h `+2 000 $`, plus haut `30 000 $` ;
  - KPI « Investi » `1 074 $`, `50 $ / mois` dans `main`, aucun `·` dans l'aide d'« Investi » ;
  - stablecoins `['USDC', '1 000 $', '100,0 %']`, ligne du wallet `['BTCBitcoin', '0,5', '50 000 $', '25 000 $', '100,0 %']` ;
  - `canvas[data-wallet-chart-currency-value="$"]`, et le premier `y` du premier dataset de `data-chart-view-value` vaut 24 000 (22 347,6 en euros) ;
  - segment actif `USD` ;
  - cookie de la réponse : valeur `usd`, expiration `2026-12-10 14:20 UTC`.
- Changement de période en mode $ : `GET /history/7d` avec `Turbo-Frame` et le cookie → total `26 000 $`, `canvas[data-wallet-chart-currency-value="$"]`, cookie prolongé au `2026-12-10 14:20 UTC`.
- Monnaie préférée USD (`admin`, cookie `usd` dans le jar) : aucun `[aria-label="Devise d’affichage"]`, aucun cookie `dashboard_currency` dans la réponse.
- Bascule vers le $ : `GET /` puis soumission du bouton `USD` → redirection 303 vers `/`, cookie `usd` expirant le `2026-12-10 14:20 UTC`, HttpOnly.
- Retour à la monnaie préférée : cookie `usd` dans le jar, `GET /`, soumission du bouton `EUR` → 303 vers `/`, cookie `dashboard_currency` supprimé (`isCleared()`).
- Valeur inconnue : `POST /currency/btc` → 404.

Tests existants à adapter : aucun autre (`DashboardBuilderTest` et `DashboardCacheTest` passent par le builder).

## Hors périmètre

- Bascule dans le header global et sur les autres pages (positions, prêts, NFT, stratégies), qui restent en $.
- Équivalent inversé (« soit … € ») en mode $.
- Conservation de la période du graphique après une bascule.
- Renommage de `toFavorite()`, `favoritePerUsd`, `usdPerFavorite`.
- Prolongation du cookie hors du dashboard.

## À vérifier

- Si le point 5 (dashboard sur `/dashboard`) est fusionné avant : rediriger vers la nouvelle route du dashboard et adapter les URL des tests. La route `/currency/{currency}` et le chemin `/` du cookie ne changent pas.
- Navigateur : après une bascule, aucun montant dans l'ancienne monnaie n'apparaît, ni en revenant sur le dashboard par l'onglet (préchargement Turbo au survol), ni avec le bouton Précédent.
- Navigateur : l'infobulle et l'axe Y du graphique sont en `$` en mode $, y compris après un changement de période.
- Thèmes clair et sombre, et mobile : le contrôle passe à la ligne proprement avec les deux boutons de l'en-tête.
- Tailwind : la variante `aria-pressed:` est bien générée (`make tailwind`).
