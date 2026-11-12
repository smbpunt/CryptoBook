# Plan : nouveau logo, favicon et page d'accueil publique

Couvre le point 2 de `roadmap.md`. `/` devient une page publique, le dashboard passe sur `/dashboard`, le logo et le favicon changent. Pas de migration, pas de nouvelle dépendance, pages de connexion, d'inscription et de mot de passe oublié inchangées (hors logo).

## État des lieux

- `/` est le dashboard (route `home`, `App\Dashboard\Controller\DashboardController::index`). Anonyme : redirection vers `/login` (`access_control` : `^/` en `ROLE_USER`).
- Routes voisines : `app_dashboard_history` (`/history/{period}`), `app_dashboard_currency` (`/currency/{currency}`).
- La route `home` est utilisée à 8 endroits hors tests : `UserAuthenticator`, `RegistrationController`, `Admin\Controller\DashboardController` (lien « Retour »), `MenuBuilder` (onglet « Tableau de bord »), `DashboardController` (2 redirections), `base.html.twig` (lien du logo), `error.html.twig` (« Retour à l'accueil »).
- 14 lignes de tests visent `'/'` (6 fichiers), 5 visent `/history/…` ou `/currency/…` (`DashboardControllerTest`).
- Logo : `templates/components/App/Logo.html.twig` (deux cartes et un anneau), utilisé par `base.html.twig` et `App:AuthCard`. Favicon : `assets/images/favicon.png` (512 px), un seul `<link rel="icon">` dans `base.html.twig`. EasyAdmin garde son favicon par défaut.
- `base.html.twig` : blocs `title`, `stylesheets`, `javascripts`, `header_nav`, `header_account`, `body`. Pas de footer, pas de bloc pour les balises `<meta>`. Header et `main` en `max-w-[85rem]`.
- `public/` ne contient que `index.php` : pas de `robots.txt`.
- Staging `dev.cryptobook.fr` : basic auth Caddy, aucun noindex explicite.
- Les tokens de la maquette sont ceux de `assets/styles/app.css` (mêmes valeurs en clair et en sombre). Le fond (halos et grain) existe déjà sur `body`.
- Base de tests : 263 tests verts, 40 s.
- Maquette : https://claude.ai/artifact/YLUF213hoKirnQg19f8dzj, version `1794485145-a550`. À lire avec l'outil Artifact : `action: "read"`, `paths: ["project/Main.dc.html"]`. Elle montre l'ancien logo et le thème clair, le sombre se lit dans la classe `.cb-dark`.
- Logo retenu : « Piste 3 · Le rayon » de https://claude.ai/artifact/Rra2AiUVx5uLfTq7Hzc4AB. Le SVG est recopié ci-dessous, inutile de relire l'artifact.

## Décisions

| Sujet | Options | Retenu |
|---|---|---|
| Logo | favicon seul / partout | « Le rayon » partout : `App:Logo` (header, `App:AuthCard`, page d'accueil) et favicon |
| Favicon | adaptatif au thème / dessin unique | Dessin unique : pastille `#17171C`, livres `#F4F2ED` et `#F26B1D`. Pas de `prefers-color-scheme` |
| Fichiers d'icône | `public/` / AssetMapper | `assets/images/`, liés par `asset()` : l'URL change avec le fichier |
| Rendu des PNG | ImageMagick / `rsvg-convert` | `rsvg-convert` dans un conteneur Alpine jetable (`make icons`). ImageMagick déplace le livre incliné (testé) |
| `/favicon.ico` | ajouté / non | Non |
| Emplacement de la page | `Shared` / `Security` / nouvelle feature | Feature `Home` : `src/Home/Controller/HomeController`, `templates/home/`, `tests/Home/` |
| Nom des routes | — | `home` reste le nom de `/`. Dashboard : `app_dashboard_index` |
| URL du dashboard | `/dashboard` seul / tout sous `/dashboard` | Préfixe de classe `/dashboard` : `/dashboard`, `/dashboard/history/{period}`, `/dashboard/currency/{currency}`. Noms de routes inchangés. Pas de redirection depuis les anciennes URL |
| Lien du logo | selon l'utilisateur / toujours `home` | Toujours `path('home')` : connecté, `/` redirige |
| Cookie `dashboard_currency` | chemin `/dashboard` / `/` | Chemin `/` inchangé (un cookie existant sur `/` ne pourrait plus être effacé) |
| Header de la page d'accueil | header de la maquette / header de l'appli | Header de `base.html.twig`, blocs `header_nav` et `header_account` surchargés. Le bouton de thème reste (absent de la maquette) |
| Fond | halos par section de la maquette / fond de l'appli | Fond de `body` existant, rien d'ajouté |
| Défilement doux (`scroll-behavior`) | repris / non | Non repris |
| Graphique de l'accroche | Chart.js / SVG statique | SVG statique de la maquette, sans JavaScript |
| Graduations du graphique | gardées / retirées | Retirées (44 k, 40 k, 36 k, 32 k €) : la courbe monte de 20 % sur ces graduations, le texte dit +3,2 %. Les 4 lignes pointillées restent |
| Logos des cryptos | images CoinGecko / pastille à lettre | Pastille à lettre de la maquette : aucune requête vers un tiers |
| Montants et pourcentages | texte en dur / filtres | Nombres en dur passés aux filtres de `AmountExtension` : le rendu de l'appli fait foi (quantités `0,285`, `3,1`, `14,5` au lieu de `0,2850`…) |
| Dates des dépôts d'exemple | calculées / en dur | En dur (« 3 nov. 2026 »…). L'année du footer est calculée |
| Ancres | français / anglais | `#dashboard`, `#positions`, `#cryptos`, `#deposits` |
| Image Open Graph | bannière 1200×630 / icône / aucune | Icône carrée 512 px, carte `summary` |
| `robots.txt` | route dynamique / fichier statique | `public/robots.txt`, identique partout |
| Noindex du staging | variable d'env / Caddy | Basic auth existante + en-tête `X-Robots-Tag` dans le Caddyfile du staging. Aucun code |
| Balise `robots` sur les autres pages | `noindex` / rien | Rien |

## Logo et favicon

`templates/components/App/Logo.html.twig` : deux props en plus, nouveau symbole.

```twig
{%- props
    ## string|null Accessible name; null when the logo sits next to text that already names it.
    label = null,
    ## bool False for the symbol alone.
    wordmark = true,
    ## string Size class of the symbol.
    size = 'size-7.5'
-%}
<span {{ attributes.defaults({class: 'inline-flex items-center gap-2 text-foreground'}) }}>
    <svg class="{{ size }} shrink-0" viewBox="4 3.5 56 56" {% if label %}role="img" aria-label="{{ label }}"{% else %}aria-hidden="true"{% endif %}>
        <rect x="7" y="30" width="10" height="24" rx="3" class="fill-foreground"/>
        <rect x="22" y="20" width="10" height="34" rx="3" class="fill-foreground"/>
        <rect x="37" y="10" width="10" height="44" rx="3" transform="rotate(14 47 54)" class="fill-brand"/>
    </svg>
    {% if wordmark %}…{% endif %}
</span>
```

- Le `<span>` du mot « CryptoBook » ne change pas, il passe seulement dans le `{% if wordmark %}`.
- Couleurs par tokens : livres en `--foreground`, livre incliné en `--brand` (orange du thème sombre compris).

`assets/images/favicon.svg` (nouveau), couleurs en dur, transformations déjà appliquées :

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
    <rect width="64" height="64" rx="14" fill="#17171C"/>
    <rect x="11.76" y="30.84" width="8" height="19.2" rx="2.4" fill="#F4F2ED"/>
    <rect x="23.76" y="22.84" width="8" height="27.2" rx="2.4" fill="#F4F2ED"/>
    <rect x="35.76" y="14.84" width="8" height="35.2" rx="2.4" transform="rotate(14 43.76 50.04)" fill="#F26B1D"/>
</svg>
```

PNG dérivés, dans `assets/images/`, commités :

| Fichier | Taille | Fond | Usage |
|---|---|---|---|
| `favicon-32.png` | 32×32 | transparent (coins arrondis) | favicon de secours |
| `apple-touch-icon.png` | 180×180 | `#17171C` plein cadre | iOS |
| `icon-512.png` | 512×512 | `#17171C` plein cadre | Open Graph |

`make/dx.mk` : cible `icons`, ajoutée à `.PHONY`, aide `## Regenerate the PNG icons from assets/images/favicon.svg`. Recette (commande `docker run` testée, `rsvg-convert` 2.60) :

```make
icons:
	@docker run --rm -v "$(CURDIR)/assets/images":/w -w /w alpine:3.22 sh -c '\
		apk add --no-cache rsvg-convert >/dev/null && \
		rsvg-convert -w 32 -h 32 favicon.svg -o favicon-32.png && \
		rsvg-convert -w 180 -h 180 -b "#17171C" favicon.svg -o apple-touch-icon.png && \
		rsvg-convert -w 512 -h 512 -b "#17171C" favicon.svg -o icon-512.png'
```

`templates/base.html.twig`, à la place du `<link rel="icon">` actuel, dans cet ordre (le PNG avant le SVG) :

```twig
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
```

`src/Admin/Controller/DashboardController::configureDashboard()` : `->setFaviconPath('images/favicon.svg')` (EasyAdmin passe le chemin à `asset()`).

Supprimer `assets/images/favicon.png`.

## Routes et sécurité

`src/Dashboard/Controller/DashboardController.php` : `#[Route('/dashboard')]` sur la classe.

| Méthode | Avant | Après |
|---|---|---|
| `index` | `#[Route('/', name: 'home')]` | `#[Route('', name: 'app_dashboard_index', methods: ['GET'])]` |
| `history` | `/history/{period}` | même attribut, URL `/dashboard/history/{period}` |
| `currency` | `/currency/{currency}` | même attribut, URL `/dashboard/currency/{currency}` |

Route `home` → `app_dashboard_index` :

| Fichier | Endroit |
|---|---|
| `src/Dashboard/Controller/DashboardController.php` | `history()` et `currency()` (2 redirections) |
| `src/Security/Authenticator/UserAuthenticator.php` | `onAuthenticationSuccess()` |
| `src/Security/Controller/RegistrationController.php` | redirection après inscription |
| `src/Shared/Builder/MenuBuilder.php` | onglet « Tableau de bord » |
| `src/Admin/Controller/DashboardController.php` | lien « Retour » |

Restent sur `home` : le lien du logo (`base.html.twig`) et « Retour à l'accueil » (`error.html.twig`).

`src/Home/Controller/HomeController.php` (nouveau, `final`, étend `AbstractController`) :

- `#[Route('/', name: 'home', methods: ['GET'])] public function index(): Response`
- `$this->getUser()` non nul (session ou remember me) : `redirectToRoute('app_dashboard_index')`, 302.
- Sinon : `render('home/index.html.twig')`, sans variable.
- Aucune dépendance vers une autre feature (pas d'import de `User`).

`config/packages/security.yaml`, `access_control`, avant la règle `^/` :

```yaml
- { path: ^/$, roles: PUBLIC_ACCESS }
```

`deptrac.yaml` : couche `Home` (`^App\\Home\\.*`), règle `Home: [Shared]`.

Effets attendus, sans code en plus : après connexion ou inscription, arrivée sur `/dashboard`. Après déconnexion, arrivée sur la page d'accueil.

## Layout (`templates/base.html.twig`)

- Bloc `meta`, vide, juste après `<title>`.
- Bloc `page_width` : `{% block page_width %}max-w-[85rem]{% endblock %}` à la place de `max-w-[85rem]` dans le `<div>` du header, `{{ block('page_width') }}` à la place de celui du `<main>`.
- Bloc `footer`, vide, après `</main>`.
- Rien d'autre ne change : les autres pages rendent le même HTML qu'avant, favicon mis à part.

## Page d'accueil (`templates/home/`)

Fichiers, à plat :

| Fichier | Contenu |
|---|---|
| `index.html.twig` | étend `base.html.twig`, blocs `title`, `meta`, `page_width`, `header_nav`, `header_account`, `body`, `footer`. Définit `coins`. Inclut les sections dans l'ordre de la maquette |
| `_hero.html.twig` | accroche, 2 boutons, carte du graphique |
| `_dashboard.html.twig` | section `#dashboard` |
| `_positions.html.twig` | section `#positions` |
| `_cryptos.html.twig` | section `#cryptos` |
| `_deposits.html.twig` | section `#deposits` |
| `_cta.html.twig` | appel à créer un compte |
| `_feature_text.html.twig` | colonne de texte d'une section : variables `id`, `eyebrow`, `title`, `text`, `points` (liste de 3 chaînes). Inclus avec `only` |
| `_coin_badge.html.twig` | pastille à lettre : variables `coin`, `size` (classe, `size-7` ou `size-6`). Inclus avec `only` |

Des `include`, pas de nouveaux composants Twig (piège de portée des composants).

Règles générales :

- Textes : ceux de la maquette, mot pour mot, `aria-label` des illustrations compris (`role="img"` sur chaque illustration, aucun élément focusable dedans).
- Styles : classes Tailwind, aucun `style` en ligne sauf la couleur d'une crypto et les largeurs de barre en `%`. Valeurs en px de la maquette : échelle Tailwind quand elle tombe juste, sinon valeur arbitraire (`text-[17px]`). Les `clamp()` sont repris en `rem`.
- Titres : `font-display`. Montants : `tabular-nums`.
- Liens : `path('app_login')`, `path('app_register')`, `path('app_forgot_password_request')`.

Tokens :

| Maquette | Appli |
|---|---|
| `--bg`, `--fg`, `--muted` | `bg-background`, `text-foreground`, `text-muted-foreground` |
| `--card` + bordure + ombre + flou | `<twig:Card>` (rayon de 22 px du composant, y compris pour la carte du graphique dessinée à 26 px) |
| `--solid` | `bg-popover` |
| `--border`, `--grid` | `border-border` |
| `--subtle` | `bg-muted` |
| `--ink` / `--inkfg` | `<twig:Button>` par défaut |
| bouton secondaire (`lp-ghost` avec bordure) | `<twig:Button variant="outline">` |
| `--line` | `chart-1` (`stroke-chart-1`, `bg-chart-1`, `border-chart-1`, `style="stop-color: var(--chart-1)"` dans le dégradé) |
| `--pos` / `--posbg`, `--neg` / `--negbg` | pastille de pourcentage : `<twig:App:Delta>`. Montant coloré : `text-positive` |
| `--accent` | `text-brand-text` |
| `--mark` | `bg-brand` |
| `--shadow` | ombre de `Card`. Liste de suggestions : `shadow-(--shadow-card)` |
| anneau du champ actif `rgba(79,70,229,.16)` | `border-chart-1 ring-3 ring-chart-1/15` |
| pastille de période active | classes de l'état `aria-[current]` de `dashboard/_history.html.twig` |
| halos, grain | rien |

Header (`index.html.twig`) :

- `page_width` : `max-w-[75rem]`.
- `header_nav` : `<nav aria-label="Sections de la page">` avec les classes du `<nav>` de `base.html.twig`, `<ul class="flex gap-0.5 whitespace-nowrap">`, 4 liens (Tableau de bord, Positions, Cryptos, Dépôts) avec les classes de l'état non courant de `shared/menu.html.twig`.
- `header_account` : `<twig:Button as="a" variant="ghost" class="h-11 px-3">Connexion</twig:Button>` puis `<twig:Button as="a" class="hidden h-11 px-5 sm:inline-flex">Créer un compte</twig:Button>`. Sous `sm`, seul « Connexion » reste dans le header.

Sections :

- Chaque section : `scroll-mt-32 lg:scroll-mt-24`, `aria-labelledby` vers son titre (`home-title`, `home-dashboard`, `home-positions`, `home-cryptos`, `home-deposits`, `home-cta`).
- Espacements verticaux de la maquette, moins les 32 px de `py-8` du `<main>` en haut de l'accroche et en bas de l'appel final : accroche `pt-[clamp(2rem,6vw,5.25rem)]`, sections `pt-[clamp(5.5rem,11vw,10rem)]`, appel final `pt-[clamp(6.875rem,14vw,12.5rem)] pb-[clamp(4rem,10vw,8.625rem)]`.
- Sections à deux colonnes : `flex flex-wrap items-center gap-x-22 gap-y-12`, texte `min-w-0 flex-[1_1_380px]`, illustration `min-w-0 flex-[1.25_1_440px]`. `#positions` et `#deposits` en `flex-row-reverse`.
- Boutons de l'accroche et de l'appel final : `class="h-13 gap-2.5 px-6.5 text-base"`, flèche `lucide:arrow-right`.
- Graphique : les 3 `<path>` et le point final de la maquette, les 4 lignes pointillées sans libellé, `id` du dégradé `home-chart-area`. Pastilles de période en `<span>` (7 j, 30 j actif, 90 j, 1 an, Tout).
- Indicateurs de `#dashboard` : `<twig:App:Stat>` comme `dashboard/_kpis.html.twig` (`lucide:coins`, `lucide:chart-pie`, bloc `footer` pour la barre crypto / stable).
- Tableau de `#positions` : `<table>` brut, classes de `dashboard/_holdings.html.twig`.
- Icône de recherche du faux champ : `lucide:search`, puis `bin/console ux:icons:lock`.

`coins` :

| Clé | `symbol` | `name` | `letter` | `color` | `dark` |
|---|---|---|---|---|---|
| `btc` | BTC | Bitcoin | B | `#F7931A` | `true` |
| `eth` | ETH | Ethereum | E | `#627EEA` | `false` |
| `sol` | SOL | Solana | S | `#0E9F8E` | `false` |
| `etc` | ETC | Ethereum Classic | E | `#2F7D4A` | `false` |
| `ethfi` | ETHFI | ether.fi | e | `#5B4BC4` | `false` |

Pastille : fond `style="background: {{ coin.color }}"`, lettre `text-white`, `text-black` si `dark`.

Chiffres d'exemple (nombres en dur, formatés par les filtres) :

| Bloc | Valeurs | Filtre |
|---|---|---|
| Accroche | 42 618,40 € · +3,2 % · +1 318,20 € · « sur 30 jours » | `amount('€', 2)`, `App:Delta :value="3.2"`, `signed_amount('€', 2)` |
| Investi | 31 500,00 € · « Dépôts fiat cumulés · 34 020,00 $ » | `amount(…, 2)` |
| Crypto / stable | 78 % / 22 % · 33 242,30 € · 9 376,10 € · barre à 78 % | `percent(0)`, `amount('€', 2)` |
| Wallet crypto | BTC 67,2 % · ETH 25,1 % · SOL 7,7 % | `percent` |
| Positions | BTC 0,285 · 14 800,00 € · 22 349,70 € · +51,0 % | `quantity`, `amount('€', 2)`, `App:Delta` |
| | ETH 3,1 · 7 100,00 € · 8 339,00 € · +17,5 % | |
| | SOL 14,5 · 2 900,00 € · 2 553,60 € · −11,9 % | |
| Nouvelle position | recherche « eth » : Ethereum, Ethereum Classic, ether.fi · quantité 3,1 · coût 7 100 € | `quantity`, `decimal` |
| Dépôts | 3 nov. 2026 Kraken 500,00 € · 2 oct. 2026 Kraken 500,00 € · 14 sept. 2026 Coinbase 1 200,00 $ · 1 sept. 2026 Kraken 500,00 € | `amount(…, 2)` |
| Totaux des dépôts | 31 500,00 € · 42 618,40 € · +11 118,40 € | `amount`, `signed_amount` |

Footer (bloc `footer`) : `<footer class="border-t border-border">`, conteneur `mx-auto {{ block('page_width') }} px-6`. À gauche `<twig:App:Logo :wordmark="false" size="size-6"/>` et `© {{ 'now'|date('Y') }} CryptoBook · Prix : CoinGecko · Taux de change : Fixer`. À droite `<nav aria-label="Accès au compte">` : Connexion, Inscription, Mot de passe oublié.

## Référencement

`index.html.twig` :

- `title` : `Suivi de portefeuille crypto` (rendu : « Suivi de portefeuille crypto · CryptoBook »).
- `meta` :

```twig
{% set description = "CryptoBook suit vos positions, vos dépôts en euros ou en dollars et l'évolution de votre portefeuille crypto, heure par heure." %}
<meta name="description" content="{{ description }}">
<link rel="canonical" href="{{ url('home') }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="CryptoBook">
<meta property="og:locale" content="fr_FR">
<meta property="og:title" content="CryptoBook · Suivi de portefeuille crypto">
<meta property="og:description" content="{{ description }}">
<meta property="og:url" content="{{ url('home') }}">
<meta property="og:image" content="{{ absolute_url(asset('images/icon-512.png')) }}">
<meta name="twitter:card" content="summary">
```

`public/robots.txt` (nouveau) :

```
User-agent: *
Disallow:
```

## Opérations serveur (hors PR, par Punt, sur VPS1)

1. Dans le bloc `dev.cryptobook.fr` de `/etc/frankenphp/Caddyfile` : `header X-Robots-Tag "noindex, nofollow"`
2. `sudo systemctl reload frankenphp`
3. `curl -sI -u punt https://dev.cryptobook.fr/ | grep -i x-robots-tag`

## Corrections

- `assets/images/btc.png` n'est référencé nulle part : supprimé dans le commit 1.
- `DashboardController::index` acceptait toutes les méthodes HTTP : `GET` seulement.
- Commentaire de `DashboardControllerTest::assertCurrencyCookieExtended()` (« the dashboard is on "/" ») : remplacé par `// Path "/": unchanged since the dashboard moved to /dashboard, an existing cookie must stay clearable.`

## Ordre des commits

1. `feat(ui): replace the logo and the favicon` : `App:Logo`, `favicon.svg` et ses 3 PNG, `make icons`, liens de `base.html.twig`, favicon d'EasyAdmin, suppression de `favicon.png` et `btc.png`, test des favicons.
2. `refactor(dashboard): move the dashboard to /dashboard` : préfixe et nom de route, 6 remplacements de `home`, `HomeController` qui redirige (connecté : dashboard, anonyme : `app_login`, provisoire), `access_control`, Deptrac, tests mis à jour.
3. `feat(home): add the public homepage` : blocs de `base.html.twig`, `templates/home/`, `HomeController` rend la page, icône `lucide:search`, tests.
4. `feat(home): add the SEO tags and robots.txt` : bloc `meta`, `public/robots.txt`, test.
5. `docs: document the homepage and mark it as done` : `CLAUDE.md`, `README.md`, `doc/roadmap.md`, suppression de cette spec.

Tests verts à chaque commit.

## Tests

À mettre à jour (commit 2) :

| Fichier | Changement |
|---|---|
| `tests/Dashboard/Controller/DashboardControllerTest.php` | `GET '/'` → `/dashboard` (6), `/history/…` → `/dashboard/history/…` (4), `/currency/btc` → `/dashboard/currency/btc`, `assertResponseRedirects('/')` → `/dashboard` (3). Chemin du cookie : toujours `/` |
| `tests/Functional/SmokeTest.php` | `testUserPages` : `/` → `/dashboard`. `publicUrls` : ajouter `'home' => ['/']` (commit 3) |
| `tests/Functional/CoinLogoTest.php` | `'home' => ['/']` → `'dashboard' => ['/dashboard']` |
| `tests/Functional/StimulusControllersTest.php` | `'/'` → `'/dashboard'` |
| `tests/Security/Controller/SecurityControllerTest.php` | `testLogin` : redirection vers `/dashboard` |
| `tests/Security/Controller/RegistrationControllerTest.php` | redirection vers `/dashboard` |

Nouveaux :

- `tests/Functional/LayoutTest::testEveryPageDeclaresTheFavicons` (sur `/login`) : `link[rel="icon"][type="image/svg+xml"]` en `#^/assets/images/favicon-[\w-]+\.svg$#`, `link[rel="icon"][sizes="32x32"]` en `favicon-32-…png`, `link[rel="apple-touch-icon"]` en `apple-touch-icon-…png`. Échoue si un fichier manque (l'URL retombe sur `/images/…`).
- `tests/Home/Controller/HomeControllerTest` (`WebTestCase`, `Dataset`) :
  - `testLoggedInUserIsRedirectedToTheDashboard` (commit 2) : `loginUser`, `GET /` → redirection vers `/dashboard`.
  - `testAnonymousSeesTheHomepage` : 200, `h1` contient « Ce que valent vos cryptos. » et « Ce qu'elles vous ont coûté. » (deux assertions : un `<br>` les sépare), titre « Suivi de portefeuille crypto · CryptoBook », au moins un lien vers `/login` et un vers `/register` dans `header` et dans `main`, `button[data-controller="theme"]` présent, ni `nav[aria-label="Navigation principale"]` ni `button[aria-label="Compte"]`.
  - `testEveryAnchorHasItsSection` : chaque `a[href^="#"]` de la page vise un `id` présent, et les 4 ancres du header sont `#dashboard`, `#positions`, `#cryptos`, `#deposits`.
  - `testShowsTheExampleFigures` : `42 618,40 €`, `+3,2 %`, `+11 118,40 €` et `−11,9 %` dans `main` (espaces insécables normalisés comme dans `DashboardControllerTest::normalize()`).
  - `testCarriesTheSeoTags` (commit 4) : `meta[name="description"]` non vide, `link[rel="canonical"]` = `http://localhost/`, `og:url` = `http://localhost/`, `og:image` en `#^http://localhost/assets/images/icon-512-[\w-]+\.png$#`, `twitter:card` = `summary`.

Déjà couvert, rien à ajouter : une page privée reste fermée à un anonyme (`SmokeTest::testAnonymousIsRedirectedToLogin`), ce qui protège d'une règle `access_control` trop large.

## Documentation (commit 5)

`CLAUDE.md`, « Projet », nouvelle puce après la première :

```markdown
- Page d'accueil publique sur `/` (`src/Home/`, `templates/home/`) : vitrine statique, chiffres d'exemple en dur, aucune requête SQL. Connecté, elle redirige vers le dashboard (`/dashboard`, route `app_dashboard_index`). `public/robots.txt` autorise tout : le staging est fermé par la basic auth et un en-tête `X-Robots-Tag` de Caddy.
```

`CLAUDE.md`, « Architecture », nouvelle puce après « Thèmes clair et sombre » :

```markdown
- Logo : `App:Logo` (`wordmark`, `size`). Favicon : `assets/images/favicon.svg`, d'où `make icons` tire `favicon-32.png`, `apple-touch-icon.png` et `icon-512.png` (Open Graph). Blocs de `base.html.twig` pour une page hors gabarit : `meta`, `page_width` (largeur du header, du contenu et du footer), `header_nav`, `header_account`, `footer`.
```

`README.md`, « Commands », après la ligne `make test` :

```markdown
- `make icons`: regenerate the PNG icons from `assets/images/favicon.svg`
```

`doc/roadmap.md` : supprimer le point 2 et sa ligne `Spec`, renuméroter le point 3 en 2, ajouter dans « Fait » :

```markdown
- **Page d'accueil** : page publique sur `/`, dashboard sur `/dashboard`, nouveau logo et favicon, référencement minimal.
```

Supprimer `doc/specs/homepage-favicon.md` dans le même commit.

## Hors périmètre

- Pages de connexion, d'inscription et de mot de passe oublié au style de la page d'accueil.
- Sitemap, bannière Open Graph 1200×630, manifeste web, `favicon.ico`.
- Redirections depuis `/history/…` et `/currency/…`.
- Version anglaise de la page.
- En-têtes de cache HTTP sur la page d'accueil.

## À vérifier

- Onglet de Chrome, Firefox et Safari : le nouveau favicon s'affiche, en thème clair et sombre du navigateur. iOS, « Sur l'écran d'accueil » : icône plein cadre.
- Admin (`/admin`) : nouveau favicon.
- Page d'accueil comparée à la maquette, en clair et en sombre, à 1440, 768 et 390 px.
- À 360 px de large, la première ligne du header tient logo, bouton de thème et « Connexion ». Si elle passe sur deux lignes : `px-2.5` sur « Connexion ».
- Profiler sur `/` en anonyme : 0 requête SQL, pas de cookie de session posé.
- `curl -i http://localhost:<port>/robots.txt` : 200, `text/plain`, servi par nginx sans passer par PHP.
- Après le déploiement en prod : `https://cryptobook.fr/robots.txt`, puis l'aperçu d'un lien partagé (titre, description, icône).
- `bin/console ux:icons:lock` n'ajoute que `assets/icons/lucide/search.svg`.
