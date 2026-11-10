# Plan — Refonte de l'UI + graphique du wallet

Couvre la refonte de l'UI de `roadmap.md`. Maquette : https://claude.ai/artifact/2g7JtSLYbx78MeF4pJtwHT (dashboard, clair et sombre).

## Découpage

Les deux phases sont menées comme un seul chantier. La première ligne de la maquette (valeur totale, variation 24 h, plus haut) ne vit que des snapshots : intégrer le dashboard sans le graphique laisserait un tiers de la page vide, et faire le graphique avant obligerait à le poser dans l'ancienne UI, sans Turbo, pour le refaire ensuite.

- **PR 1 — Fondations.** Thème clair/sombre, couleurs, typo, layout, navigation. Toutes les pages changent d'allure par les tokens et les composants du kit.
- **PR 2 — Turbo.** Un seul entrypoint, les scripts de page deviennent des contrôleurs Stimulus. Prérequis du Turbo Frame.
- **PR 3 — Dashboard + graphique.** La maquette, avec ses données réelles. Commits back d'abord (invisibles, testés), front ensuite.
- **Reste des pages** : points 3 et 4 de `roadmap.md`.

Chaque PR est déployable seule. Hors périmètre : EasyAdmin, e-mail de reset.

## Écarts entre la maquette et la roadmap

| Sujet | Roadmap | Maquette | Retenu |
|---|---|---|---|
| Cartes | opaques | translucides (72 %) + flou | maquette, opacité dans le seul token `--card` |
| Fond | fait (`body::before/after`) | autre technique (blobs + overlay) | l'existant, sans y toucher |
| Graphique | `symfony/ux-chartjs` | SVG fait main | ux-chartjs, rendu de la maquette |
| Camemberts | Chart.js | donut SVG + listes | SVG rendu par Twig, sans JS |
| Périodes | 24h, 7j, 30j, 1an, Tout | + 90j | les 6 |
| Agrégation | par jour au-delà de 24h | — | horaire pour 24h et 7j (7 points par jour = courbe trop pauvre), par jour ensuite |
| Stats | plus haut et plus bas de la période | plus haut historique + écart, variation 24 h | maquette |
| Bascule EUR/USD | — | dans le header | non : montants dans la devise favorite, équivalent `$` en secondaire |
| Menu mobile | — | onglets défilants | maquette, le burger `<details>` disparaît |

## PR 1 — Fondations

### Thème clair / sombre
- `base.html.twig` : retirer `class="dark"`. Script inline dans le `<head>`, avant le CSS : pose `.dark` selon `localStorage.theme`, sinon `prefers-color-scheme`. `<meta name="color-scheme" content="light dark">`.
- `assets/controllers/theme_controller.js` : bascule, enregistre, émet `theme:change` sur `window`. Sans choix enregistré, suit les changements du système.
- `App:ThemeToggle` dans le header (icônes `lucide:sun` / `lucide:moon`, `aria-label`).
- Le thème clair n'a jamais été affiché : passer chaque page en clair et corriger les couleurs en dur.

### Tokens (`assets/styles/app.css`)

| Token | Clair | Sombre |
|---|---|---|
| `--background` | `#F4F2ED` | `#0C0C11` |
| `--foreground`, `--card-foreground`, `--popover-foreground` | `#17171C` | `#ECECF2` |
| `--card` | `rgba(255,255,255,.72)` | `rgba(22,22,31,.70)` |
| `--popover` | `#FFFFFF` | `#1C1C27` |
| `--primary` / `--primary-foreground` | `#17171C` / `#FAFAF7` | `#ECECF2` / `#111117` |
| `--muted`, `--secondary`, `--accent` | `rgba(23,23,28,.055)` | `rgba(255,255,255,.06)` |
| `--muted-foreground` | `#5C5C6A` | `#9D9DB0` |
| `--border`, `--input` | `rgba(23,23,28,.09)` | `rgba(255,255,255,.08)` |
| `--positive` | `#137A43` | `#4ADE80` |
| `--negative` | `#B42318` | `#FF8A7A` |
| `--brand` (aplats, logo) — nouveau | `#F26B1D` | `#FB923C` |
| `--brand-text` (orange lisible) — nouveau | `#C2410C` | `#FB923C` |
| `--chart-1` (courbe, lavande) | `#4F46E5` | `#A5A8FF` |
| `--chart-2` | `var(--brand)` | `var(--brand)` |
| `--shadow-card` — nouveau | `0 1px 2px rgba(23,23,28,.04), 0 10px 28px -14px rgba(23,23,28,.16)` | `0 1px 2px rgba(0,0,0,.3), 0 14px 34px -16px rgba(0,0,0,.65)` |
| `--radius` | `1rem` (cartes à 22 px) | idem |

- L'orange de la maquette s'appelle `--brand`, pas `--accent` : chez shadcn, `--accent` est le fond de survol.
- `--negative` passe de l'ambre au rouge : l'orange devient la couleur de marque.
- Les couleurs du graphique restent en hex : Chart.js ne lit pas `oklch()`.
- Fonds des badges : `bg-positive/10`, `bg-negative/10`, sans token en plus.

### Typo
- Inter (texte) et Space Grotesk (titres, gros montants), auto-hébergées : `importmap:require @fontsource-variable/inter/index.css @fontsource-variable/space-grotesk/index.css`, importées dans `assets/app.js` comme le CSS de Choices. Pas de Google Fonts.
- `@theme` : `--font-sans`, `--font-display` (classe `font-display`). Montants en `tabular-nums`.
- À vérifier : AssetMapper télécharge bien les `.woff2` référencés par le CSS.

### Layout et navigation
- `App:Logo` (pictogramme SVG + « Crypto**Book** »), utilisé dans le header et `App:AuthCard` (à la place de `btc.png`). `<title>` : `CryptoBook`.
- Header : logo, navigation en onglets (`overflow-x-auto` sur mobile), bouton de thème, bouton compte.
- `MenuBuilder` : `main` garde les 8 entrées métier (« Tableau de bord », « Positions », « Dépôts »). Nouveau menu `account` (Préférences, Admin, Déconnexion), rendu dans un `Popover` du kit derrière le bouton compte.
- `templates/shared/menu.html.twig` : onglets de 60 px sans fond, page courante en `font-semibold` soulignée de `--brand` (2 px, sur la bordure du header).
- Conteneur : `max-w-[85rem]`, `px-6 py-8`.

### Composants du kit
- `Card` : `border border-border shadow-(--shadow-card) backdrop-blur-lg` à la place du `ring`, padding 5–6.
- `Button` : `rounded-full`.
- `Popover`, `Dialog`, `Tooltip` : restent opaques (`bg-popover`).
- Bloc Choices.js de `app.css` : contrôle visuel dans les deux thèmes.

### Tests
- `MenuBuilder` : contenu des menus `main` et `account`, Admin réservé à `ROLE_ADMIN`.
- Fonctionnel : header avec bouton de thème et menu compte, plus de `class="dark"` en dur, page d'erreur sans menu.

## PR 2 — Turbo

- `composer require symfony/ux-turbo`.
- Un seul entrypoint, `app` : supprimer les 20 `{% block importmap %}` et les entrypoints de page d'`importmap.php`. Avec Turbo, un module de page ne s'exécute qu'une fois et l'importmap ne peut pas changer entre deux pages.

| Script actuel | Devient |
|---|---|
| `select2.js`, `new Choices(…)` de `farming/form.js`, `lp/form.js` | `choices_controller.js`, `destroy()` dans `disconnect()` |
| `collection.js` | `collection_controller.js` (targets et actions) |
| `blockchain-dapp.js`, `loan/form.js` | `dependent_select_controller.js` (`fetch`, garde l'en-tête `X-Requested-With` lu par `isXmlHttpRequest()`) |
| `position/index.js` | `row_toggle_controller.js` |
| `dca/index.js` | `dca_controller.js` (`Turbo.visit`), ou formulaire GET natif pour « DCA Auto » |
| `home.js` | `allocation_chart_controller.js`, tel quel : supprimé en PR 3 |

- Contrôleurs de page en `/* stimulusFetch: 'lazy' */`.
- Lien Admin en `data-turbo="false"` : EasyAdmin a son propre layout.
- Les 13 `redirectToRoute()` sans `Response::HTTP_SEE_OTHER` passent en 303.
- Déjà bon : les formulaires invalides répondent 422 (`render()` reçoit le formulaire, aucun `createView()`).
- À vérifier au navigateur : la classe `.dark` de `<html>` survit à la navigation, un `<dialog>` ouvert ne revient pas avec le bouton retour, `csrf_protection_controller`, DatePicker, `InfoDialog`.

### Tests
- Fonctionnel : chaque page porte ses `data-controller` (`choices`, `collection`, `dependent-select`…).
- Parcours manuel de chaque formulaire, avec navigation avant/arrière : Choices, ajout/suppression dans une collection, blockchain → dapp, détail des positions, dialogues DCA.

## PR 3 — Dashboard + graphique

### Données (`src/Wallet/`, `src/Deposit/`)
- `Wallet/Enum/HistoryPeriod` : `24h`, `7d`, `30d`, `90d`, `1y`, `all`. Méthodes `since(now)`, `isHourly()`, `label()`.
- `Wallet/Dto/SnapshotPoint` : `recordedAt`, `valueUsd`.
- `WalletSnapshotRepository::findPoints(User, HistoryPeriod, now)` : tous les snapshots pour 24h et 7j ; au-delà, le dernier snapshot de chaque jour UTC (SQL natif, `DISTINCT ON (date_trunc('day', …))`).
- `WalletSnapshotRepository::findStats(User, now)` → `Wallet/Dto/WalletSnapshotStats` : plus haut et sa date, valeur d'il y a 24 h (dernier snapshot entre −48 h et −24 h, sinon `null`), date du dernier snapshot.
- `Deposit/Calculator/DepositTimelineCalculator` : cumul des dépôts en USD, par date. Un dépôt sans `depositedAt` compte depuis le début. Taux du jour, comme le total « Investi ».

### Assemblage (`src/Dashboard/`)
Le dashboard est le seul à croiser snapshots et dépôts : aucune règle Deptrac à ajouter.

- `Dto/WalletHistory` : période, points, paliers du total déposé, variation (montant et %), début réel de la série.
- `Builder/WalletHistoryBuilder` : ajoute le point « maintenant » (total du dashboard) pour que la courbe finisse sur le montant affiché. Moins de 2 points : historique vide.
- `Factory/WalletHistoryChartFactory` (`ChartBuilderInterface`) : 2 datasets en points `{x: ms, y}`, axe X linéaire (pas d'adaptateur de dates), valeurs dans la devise favorite.
- `Dto/DashboardData` : + `snapshotStats`. `Dto/DashboardHolding` : + `priceUsd`. Nouveau `Dto/AllocationSlice` (libellé, couleur, part, décalage) : regroupement « Autres » sous 0,3 % et couleur de repli (`var(--chart-3…5)`), repris de `home.js`.
- `DashboardCache::getHistory(User, HistoryPeriod)` : même pool, mêmes tags. `DashboardCacheInvalidator` : un `WalletSnapshot` invalide son propriétaire.
- `DashboardController::history()` : `GET /history/{period}`, route `app_dashboard_history`, rend `dashboard/_history.html.twig`. Période inconnue : 404. `index()` rend le même partiel en `30d`.
- Les snapshots sont en USD : la courbe est convertie au taux du jour.

### Graphique
- `composer require symfony/ux-chartjs` (Chart.js 4). Retirer `chart.js/auto` 3.9.1 d'`importmap.php`, supprimer `home.js` et `allocation_chart_controller.js`.
- `{{ render_chart(chart, {'data-controller': 'wallet-chart'}) }}`.
- `assets/controllers/wallet_chart_controller.js`, sur `chartjs:pre-connect`, ajoute ce que PHP ne sérialise pas :
  - couleurs lues dans les variables CSS, dégradé sous la courbe
  - formats `Intl` (`fr-FR`) des axes et de l'infobulle
  - infobulle Valeur / Investi / Résultat, `interaction: {mode: 'index', intersect: false}`
  - point marqué sur la dernière valeur, grille horizontale en pointillés, 5 graduations en X
- Ligne « Investi » : `stepped`, `borderDash`.
- Sur `theme:change` : relire les couleurs, `chart.update()`.
- `role="img"` et `aria-label` résumant la période, comme la maquette.

### Templates (`templates/dashboard/`)

| Fichier | Contenu | Données |
|---|---|---|
| `index.html.twig` | titre, « Dernier snapshot », actions (`app_position_new`, `app_deposit_new`), grille | `snapshotStats.lastRecordedAt` |
| `_history.html.twig` | `<turbo-frame id="wallet-history">` : total, variation, périodes, graphique, légende | `totalUsd`, `WalletHistory` |
| `_side.html.twig` | Résultat latent, Variation 24 h, Plus haut historique | `depositUsd`, `snapshotStats` |
| `_kpis.html.twig` | Investi, Revenus farming, Emprunts, Crypto / stable | `depositUsd`, `yearFarmingUsd`, `loanUsd`, `cryptoUsd`, `stableUsd` |
| `_allocation.html.twig` | donut SVG (`stroke-dasharray`), légende, stablecoins | `AllocationSlice`, `stableHoldings` |
| `_holdings.html.twig` | tableau Actif / Quantité / Prix / Valeur / Poids | `cryptoHoldings` |

- Périodes : des liens vers `app_dashboard_history` dans le frame, `aria-current` sur la période active.
- Historique plus court que la période : le libellé dit « depuis le 10 nov. », pas « sur 30 jours ».
- Historique vide : composant `Empty` du kit dans la carte. Variation 24 h et plus haut : « — ».
- Plus haut = max(snapshots, total actuel). Résultat négatif : barre pleine, montant en `text-negative`.
- Nouveaux composants : `App:Delta` (badge signé vert/rouge). `App:Stat` gagne une icône et un texte secondaire. `App:CoinLogo` reste (pas les initiales de la maquette).
- Montants : `format_number` (`twig/intl-extra`, déjà installé).
- Disparaît : la double colonne `$` / devise et le taux de change en en-tête de tableau.

### Tests
- `HistoryPeriodTest` : début et granularité de chaque période.
- `WalletSnapshotRepositoryTest` : plage horaire, dernier snapshot du jour, stats, snapshots d'un autre utilisateur exclus.
- `DepositTimelineCalculatorTest` : cumul, dépôt sans date.
- `WalletHistoryBuilderTest` (`MockClock`) : point « maintenant », variation, historique court, historique vide.
- `DashboardControllerTest` : blocs de la maquette, `/history/7d` rend le frame, période inconnue en 404, anonyme redirigé.
- `DashboardCacheInvalidator` : un snapshot invalide son propriétaire seulement.
- `SmokeTest` : `/history/30d`.

## Reste des pages (points 3 et 4 de `roadmap.md`)

- `App:PageHeader` : titre en `font-display`, taille de la maquette.
- Listes (dépôts, emprunts, NFT, positions, projets, farming, DCA) : tableau dans une carte, `overflow-x-auto`, montants en `tabular-nums`, `App:Delta` pour les pourcentages.
- Formulaires (`form/shadcn_layout.html.twig`) : champs, Choices, DatePicker, erreurs, dans les deux thèmes.
- Auth : `App:AuthCard` avec `App:Logo`.
- Page d'erreur.
- Mobile (390 px) : dashboard complet (onglets défilants, total en taille fluide, périodes sur deux lignes, tableau défilant). Autres pages : pas de défilement horizontal de la page, rien de plus.

## À chaque PR

- Vérification complète de `CLAUDE.md`, `make tailwind`.
- Contrôle visuel en clair et en sombre, à 1440 px et 390 px.
- `CLAUDE.md` mis à jour : thèmes et tokens (PR 1), entrypoint unique et JS de page en Stimulus (PR 2), graphique et route `app_dashboard_history` (PR 3).
