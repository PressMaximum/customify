# Dashboard v2 — SPEC

> Authoritative reference for the Customify Theme's top-level admin
> dashboard (slug `customify`). Pairs with the [Customify Pro bridge
> handoff](handoffs/temp/2026-05-20-dashboard-v2-pro-bridge.md) for the
> Pro extension side.

## 1. Identity

| | |
|---|---|
| **Slug** | `customify` (top-level menu via `add_menu_page`) |
| **Hook** | `toplevel_page_customify` |
| **PHP entry** | [`inc/admin/dashboard-v2.php`](../inc/admin/dashboard-v2.php) |
| **REST entry** | [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) |
| **JS entry** | [`src/backend/admin/dashboard-v2/index.js`](../src/backend/admin/dashboard-v2/index.js) |
| **Build output** | `build/js/backend/admin/dashboard-v2.{js,asset.php}` + `build/css/backend/admin/{dashboard-v2,style-dashboard-v2}.css` |
| **Webpack entry name** | `backend/admin/dashboard-v2` (see [`webpack.config.js`](../webpack.config.js)) |
| **Kit dependency** | `@pressmaximum/dashboard-kit` (consumer of the kit SPA infrastructure) |
| **Replaces** | Legacy `themes.php?page=customify-legacy` ([`inc/admin/dashboard.php`](../inc/admin/dashboard.php) — kept intact for back-compat; slated for full removal in this release) |

## 2. Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│  PHP — boot                                                     │
│  inc/admin/dashboard-v2.php                                     │
│    add_menu_page('customify') → render <div id=…>               │
│    admin_enqueue_scripts → AssetEnqueue::enqueueOn(...)         │
│    boot_data: name, themeVersion, urls, rest, settings,         │
│               changelog, proActive (filterable)                 │
│  inc/admin/dashboard-v2-rest.php                                │
│    /customify/v1/settings (GET/POST via kit                     │
│       SettingsControllerBase)                                   │
│    /customify/v1/settings/schema (GET — JS hydrate)             │
└─────────────────────┬───────────────────────────────────────────┘
                      │ window.customifyDashboard (PHP-localized)
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│  JS — SPA                                                       │
│  src/backend/admin/dashboard-v2/index.js                        │
│    mountDashboard({ filterNamespace: 'customify', brand,        │
│                     baseTabs, baseRoutes, helpItems, … })       │
│    Deferred to DOMContentLoaded so Pro filters register first   │
│                                                                  │
│  tabs/ → Welcome, Settings, Changelog                           │
│  sections/ → ProModulesSection, ProModuleSettingsPanel,         │
│              LicensePanel                                       │
│  data/ → customizerLinks, checklist, proModules,                │
│          settingsStore                                          │
│  ui/ → ModuleList, ThemeGridCard                                │
└─────────────────────┬───────────────────────────────────────────┘
                      │ kit filters (customify.dashboard.*)
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│  Customify Pro plugin (when active)                             │
│  customify-pro/inc/admin/class-dashboard-v2-bridge.php          │
│    apply_filter customify_dashboard_pro_active → true           │
│    apply_filter customify_dashboard_localize → injects          │
│       proVersion + extras                                       │
│    /customify-pro/v1/{module/*, settings/assets, license/*,     │
│       assets/regenerate}                                        │
│                                                                  │
│  customify-pro/assets/js/admin/dashboard-v2-bridge.js           │
│    addFilter customify.dashboard.pro.modules                    │
│    addFilter customify.dashboard.pro.toggle                    │
│    addFilter customify.dashboard.settings.panels                │
│    addFilter customify.dashboard.changelog.sources              │
└─────────────────────────────────────────────────────────────────┘
```

**Layer split**:
- **Theme PHP**: menu registration, boot data assembly, REST controller
- **Theme JS**: shell mount + tab components + theme-specific data hooks
- **Pro PHP bridge**: extends boot data + adds REST routes + ships JS bridge enqueue
- **Pro JS bridge**: registers into kit filters to inject panels / modules / changelog sources / toggle handler

## 3. File layout

### 3.1 PHP

| File | Responsibility |
|---|---|
| [`inc/admin/dashboard-v2.php`](../inc/admin/dashboard-v2.php) | Menu + submenu mirror + render `<div>` + boot data + enqueue + admin body class + submenu active-state sync (inline JS) + WP menu icon CSS |
| [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) | `Customify_Dashboard_V2_Settings_Controller` (extends kit `SettingsControllerBase`); legacy `customify_fa_ver` mirror on `updated_option` |

Legacy that stays intact (back-compat for child themes / future Pro hooks):
- [`inc/admin/dashboard.php`](../inc/admin/dashboard.php) — `Customify_Dashboard` class under `themes.php?page=customify-legacy`

### 3.2 JS

| File | Responsibility |
|---|---|
| [`index.js`](../src/backend/admin/dashboard-v2/index.js) | `mountDashboard({...})` call; defers to `DOMContentLoaded`; builds `versionLabel` ("v0.4.x — Free version" / "Pro version") |
| [`brand-icon.js`](../src/backend/admin/dashboard-v2/brand-icon.js) | Inlined Customify logo SVG with `fill="currentColor"` for kit's `brand.icon` config |
| [`tabs/Welcome.jsx`](../src/backend/admin/dashboard-v2/tabs/Welcome.jsx) | Hero + Checklist (gated by `SHOW_CHECKLIST` flag) + Customizer quick links + Pro modules section |
| [`tabs/FreeVsPro.jsx`](../src/backend/admin/dashboard-v2/tabs/FreeVsPro.jsx) | Free-only upsell tab. Heading + tagline + kit `<CompareTable>` matrix + CTA banner. Registered conditionally in `index.js` — dropped from `baseTabs` AND `baseRoutes` when Pro is active. |
| [`tabs/Settings.jsx`](../src/backend/admin/dashboard-v2/tabs/Settings.jsx) | SubNav over panels; dispatch on `panel.kind` (`composite` / `license` / `proPanel` / default `ThemePanelCard`); shared SaveBar for the theme panel |
| [`tabs/Changelog.jsx`](../src/backend/admin/dashboard-v2/tabs/Changelog.jsx) | SubNav over sources (`customify.dashboard.changelog.sources` filter); `<ReleaseBlock>` per release |
| [`sections/ProModulesSection.jsx`](../src/backend/admin/dashboard-v2/sections/ProModulesSection.jsx) | Pro modules card. **Free path**: every row renders a disabled `@wordpress/components` FormToggle wrapped in `<Tooltip>` ("Available in Pro version") + Upgrade Now button with the `external` icon. **Pro path**: live toggle per row + cascade-gated WC modules show "WooCommerce not activated" notice. |
| [`sections/ProModuleSettingsPanel.jsx`](../src/backend/admin/dashboard-v2/sections/ProModuleSettingsPanel.jsx) | Generic schema-driven Pro panel renderer + `PanelActionButton` for `section.actions[]`. SaveBar passes `resetDisabledWhenNotDirty` so the Discard button greys out when the form is clean (revert-dirty-edits semantic). |
| [`sections/LicensePanel.jsx`](../src/backend/admin/dashboard-v2/sections/LicensePanel.jsx) | EDD activate/deactivate flow against the Pro bridge's REST routes |
| [`data/customizerLinks.js`](../src/backend/admin/dashboard-v2/data/customizerLinks.js) | Welcome tab's Customizer quick-link grid; filterable via `customify.dashboard.welcome.links` |
| [`data/checklist.js`](../src/backend/admin/dashboard-v2/data/checklist.js) | Onboarding tasks (currently `check: () => false` placeholders) |
| [`data/proModules.js`](../src/backend/admin/dashboard-v2/data/proModules.js) | Free path's marketing module list (Pro replaces via `customify.dashboard.pro.modules`) |
| [`data/freeVsPro.js`](../src/backend/admin/dashboard-v2/data/freeVsPro.js) | `buildFreeVsProMatrix()` — hand-curated compare matrix (6 sections, ~30 rows). Mirrors the legacy `pro_modules_box()` module list. Static copy; no REST. |
| [`data/settingsStore.js`](../src/backend/admin/dashboard-v2/data/settingsStore.js) | `createSettingsStore` register; wires `apiFetch` middleware with REST root + nonce |
| [`ui/ModuleList.jsx`](../src/backend/admin/dashboard-v2/ui/ModuleList.jsx) | `ModuleList`, `ModuleRow` (with `notice` slot for K-011-style messaging), `ModuleSubmodules` group |
| [`ui/ThemeGridCard.jsx`](../src/backend/admin/dashboard-v2/ui/ThemeGridCard.jsx) | Customizer quick-link tile (title + description + href) |
| [`dashboard-v2.scss`](../src/backend/admin/dashboard-v2/dashboard-v2.scss) | All dashboard-side SCSS; uses kit tokens where available (`--pmdk-font-weight-heading`, etc.) |

## 4. Boot data contract

Localized as `window.customifyDashboard` from `customify_dashboard_v2_boot_data()`.

```ts
{
  name: 'Customify',
  themeVersion: string,         // wp_get_theme()->get('Version')
  wpVersion: string,            // get_bloginfo('version')
  user: {
    id: number,
    displayName: string,
  },
  urls: {                       // Customizer deep-links via autofocus query args
    customize, logoIdentity, layout, headerBuilder, footerBuilder,
    styling, typography, sidebar, blog, homepage,
    legacyDashboard,            // themes.php?page=customify-legacy
    docs, proUpgrade,
  },
  rest: {
    root: string,               // rest_url()
    nonce: string,              // wp_create_nonce('wp_rest')
    settingsEndpoint: string,   // rest_url('customify/v1/settings')
    schemaEndpoint: string,
  },
  settings: {
    faVersion: string,          // legacy customify_fa_ver option (mirror)
    values: SettingsShape,      // merged saved + defaults from schema
    schema: { panels: [...] },  // kit SchemaBuilder output
  },
  changelog: Release[],         // parsed from changelog.txt
  proActive: boolean,           // apply_filters customify_dashboard_pro_active
  proVersion?: string,          // injected by Pro bridge inject_pro_version()
}
```

Extension contract: PHP filter `customify_dashboard_localize` runs before
the payload ships (Pro bridge adds `proVersion`; child themes / future
add-ons can append anything). Filter signature:
`(array $boot, string $context = 'dashboard') => array`.

## 5. Routes

Hash-based via kit's `HashRouter`. Declared in `mountDashboard.baseRoutes`:

```js
{
  '#welcome':              { component: Welcome,    type: 'page' },
  // Free-only — see §6.2.
  '#free-vs-pro':          { component: FreeVsPro,  type: 'page' },
  '#settings':             { component: Settings,   type: 'page' },
  '#settings/:panelId':    { component: Settings,   type: 'page' },
  '#changelog':            { component: Changelog,  type: 'page' },
  '#changelog/:sourceId':  { component: Changelog,  type: 'page' },
}
```

**Sub-route redirect**: bare `#settings` redirects to `#settings/<firstPanel.id>` when 2+ panels are registered (so SubNav has a resolved active row). Same for `#changelog`. See [`Settings.jsx`](../src/backend/admin/dashboard-v2/tabs/Settings.jsx) + [`Changelog.jsx`](../src/backend/admin/dashboard-v2/tabs/Changelog.jsx).

**Conditional registration**: `#free-vs-pro` is registered ONLY when `boot.proActive === false`. When Pro is active `index.js` drops both the tab strip entry AND the route from `baseRoutes` — kit's `HashRouter` falls back to `#welcome` for any leftover deep link. Pattern mirrors Blocksify Free's FreeVsPro tab.

## 6. Tabs

### 6.1 Welcome

Single-page scroll composition (no inner navigation):

1. **Hero** — kit primitive, ships its own card chrome. Theme overrides `margin-bottom: 0` + box-shadow to match WP Card hairline.
2. **Checklist** — kit primitive, currently hidden behind `const SHOW_CHECKLIST = false` in [`Welcome.jsx`](../src/backend/admin/dashboard-v2/tabs/Welcome.jsx). Flip to `true` once each item's `check()` callback wires real detection against boot data (logo set, header configured, etc.). Data hook in [`data/checklist.js`](../src/backend/admin/dashboard-v2/data/checklist.js) returns 5 placeholder items.
3. **Customizer quick links** — 3-col grid of `<ThemeGridCard>` tiles. Data hook [`data/customizerLinks.js`](../src/backend/admin/dashboard-v2/data/customizerLinks.js). Each link uses `add_query_arg('autofocus', ...)` to deep-link into a Customizer section/panel. Filterable via `customify.dashboard.welcome.links`.
4. **Pro modules** — 2-col grid of `<ModuleRow>` with FormToggle leading slot. **Free path** (Pro inactive): every row's toggle is disabled + wrapped in `<Tooltip text="Available in Pro version">`; card head shows an "Upgrade now" button with `external` icon (matches help-panel external items). **Pro path**: live toggle per row; WC Booster + sub-modules cascade-gate when WooCommerce isn't active (disabled toggle + "WooCommerce not activated" notice). See §7 for the toggle handler + module snapshot contract.

Typography: card heads use `var(--pmdk-font-weight-heading, 500)`; in-body item titles use `var(--pmdk-font-weight-label, 400)` — both tokens land via the kit's K-010 fix, theme consumes them without per-class overrides.

Extension surface: `customify.dashboard.welcome.sections` appends additional cards below; Hero/Checklist/Quick-links are not filterable as a group but each accepts kit-level filters per the kit SPEC.

### 6.2 Free vs Pro

Free-only upsell tab. Renders kit's `<CompareTable>` with a consumer-side heading + tagline above the matrix and a CTA banner attached via the table's `footer` prop.

**Conditional registration** (in [`index.js`](../src/backend/admin/dashboard-v2/index.js)): inserted into `baseTabs` between Welcome and Settings, AND into `baseRoutes` under `#free-vs-pro`, only when `! boot.proActive`. When Pro is active both entries are dropped — kit's `HashRouter` falls back to `#welcome` for any leftover deep link. No runtime guard inside the component itself; visibility is purely a registration concern.

**Matrix data**: hand-curated in [`data/freeVsPro.js`](../src/backend/admin/dashboard-v2/data/freeVsPro.js) via `buildFreeVsProMatrix()`. Static copy — no REST, no boot dependency. The function form defers `__()` calls so they run after the `customify` text domain is hydrated. Module names + descriptions mirror legacy `Customify_Dashboard::pro_modules_box()` and the Welcome tab's [`data/proModules.js`](../src/backend/admin/dashboard-v2/data/proModules.js) so the three surfaces stay consistent.

Section layout (6 sections, ~30 rows total):

| Section | Coverage |
|---|---|
| Site composition | Header / footer builders, container widths + sidebar layouts, block editor, multiple headers, mega menu |
| Header & footer | Standard items, sticky header, Header & Footer Builder Booster, WPML multilingual switcher |
| Typography & styling | Google Fonts, typography tokens, global colors, custom fonts, Typekit, advanced styling |
| Blog & portfolio | Blog listing + single post, Blog Pro layouts, Portfolio, Infinity scroll |
| WooCommerce | WC compatibility, WC Booster, single product layouts, off-canvas filter, gallery slider, quick view |
| Workflow & support | Page builder compat, auto-updates, scroll-to-top, Customify Hooks, Support tier |

Cell shapes per kit's `<CompareTable>` dispatch:
- `true` → green-circle check badge
- `false` → gray-circle em-dash badge
- `string` → literal text (e.g. `'Priority'` for support tier)
- `{ value, muted: true }` → muted text (e.g. `'Community'` for free support tier)

**CTA banner**: pulled from `boot.urls.proUpgrade` (the same EDD upgrade URL used by the Welcome help panel's "Upgrade to Pro" item and the Pro modules card's Upgrade button).

### 6.3 Settings

`<SubNav>` layout when 2+ panels registered, single-panel renders full-width without rail.

**Panel kinds** (dispatch in [`Settings.jsx`](../src/backend/admin/dashboard-v2/tabs/Settings.jsx) `renderActivePanel()`):

| `panel.kind` | Renderer | When used |
|---|---|---|
| `'composite'` | Stack `panel.sections[]` with `renderSection()` | Group multiple sections under one SubNav row (e.g. "Customify Pro" = License + Assets) |
| `'license'` | `<LicensePanel>` | Self-contained EDD activate/deactivate; no shared SaveBar |
| `proPanel: true` (any kind) | `<ProModuleSettingsPanel>` | Schema-driven Pro panel; owns its own SaveBar + REST endpoint |
| (default) | `<ThemePanelCard>` | Theme General panel; shares the dashboard-v2 settings store + global SaveBar |

**Save UI principle**: one SaveBar per active panel pane. Theme panel uses the kit's global store SaveBar (Reset to defaults always enabled — factory-defaults semantic). Pro panels each render their own SaveBar inside `ProModuleSettingsPanel` and pass `resetDisabledWhenNotDirty` so the Discard button greys out when the form is clean (revert-dirty-edits semantic). Composite panels stack sections, each carrying its own SaveBar (or no SaveBar for license).

**Filter**: `customify.dashboard.settings.panels` appends panels (Pro bridge ships `modules` composite + `customify-pro` composite).

### 6.4 Changelog

Multi-source `<SubNav>` when 2+ sources registered. Single source renders without rail.

```ts
type ChangelogSource = {
  id: string,
  label: string,
  fetch: () => Promise<Release[]>,
};
```

Free path ships the Customify source (parsed PHP-side from
[`changelog.txt`](../changelog.txt) via `customify_dashboard_v2_changelog()`).
Pro bridge appends a Customify Pro source (parsed from Pro's
`readme.txt` `== Changelog ==` section by `changelog_snapshot()`).

Each release renders via kit's `<ReleaseBlock>` (category-coded items).

## 7. Pro extension contract

### 7.1 PHP filters

| Filter | Signature | Purpose |
|---|---|---|
| `customify_dashboard_pro_active` | `(bool) => bool` | Flips `boot.proActive`; Pro bridge returns true |
| `customify_dashboard_localize` | `(array $boot, string $context) => array` | Mutate boot payload (Pro injects `proVersion`) |
| `customify_dashboard_v2_schema` | `(SchemaBuilder $s) => SchemaBuilder` | Append theme-side panels to the General-tab settings schema (Pro typically uses settings.panels JS filter instead) |

### 7.2 JS filters (kit, consumer namespace `customify`)

| Filter | Purpose |
|---|---|
| `customify.dashboard.tabs` | Append/reorder top-level tabs |
| `customify.dashboard.routes` | Register nested routes |
| `customify.dashboard.welcome.checklist` | Onboarding item list |
| `customify.dashboard.welcome.sections` | Extra Welcome cards |
| `customify.dashboard.welcome.links` | Customizer quick-link tiles |
| `customify.dashboard.settings.panels` | Settings tab panels (composite / license / proPanel) |
| `customify.dashboard.settings.field-types` | Custom field renderers |
| `customify.dashboard.changelog.sources` | Changelog source list |
| `customify.dashboard.pro.modules` | Welcome-tab Pro module catalogue |
| `customify.dashboard.pro.toggle` | Module on/off handler `(id, next) => Promise<{enabled}>` |

### 7.3 REST surface

**Theme** (registered in [`dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php)):
- `GET  /customify/v1/settings` — merged saved + defaults
- `POST /customify/v1/settings` — sanitize + save (`{}` body = reset to defaults)
- `GET  /customify/v1/settings/schema` — schema for client hydration

**Pro bridge** (registered in `class-dashboard-v2-bridge.php`):
- `GET/POST /customify-pro/v1/module/typekit` — Typekit module settings
- `GET/POST /customify-pro/v1/settings/assets` — combineAssets flag
- `POST     /customify-pro/v1/assets/regenerate` — calls `Customify_Pro_Assets::clear()`
- `GET      /customify-pro/v1/license` — snapshot
- `POST     /customify-pro/v1/license/activate` — EDD active($key)
- `POST     /customify-pro/v1/license/deactivate`

All endpoints gate on `manage_options` + accept `X-WP-Nonce: wp_rest` header.

### 7.4 Module snapshot shape

`Customify_Pro_Dashboard_V2_Bridge::module_snapshot()` returns:

```ts
{
  id: string,                     // theme-side id, e.g. 'woocommerce-booster'
  classKey: string,               // PHP class, e.g. 'Customify_Pro_Module_WooCommerce_Booster'
  name: string,
  description: string,
  docHref: string,
  enabled: boolean,
  canToggle: boolean,             // false when runtime dep missing (e.g. WC inactive)
  toggleDisableNotice: string,    // "WooCommerce not activated" for WC Booster
  hasSettings: boolean,
  parent?: string,                // sub-module's parent id (cascades canToggle: false)
  subModules?: string[],          // parent's sub-module ids
}
```

Sub-modules cascade-inherit parent's `canToggle: false` via a post-loop pass; notice text stays on the parent only (matches legacy `render_module()` behaviour).

## 8. WP admin integration

### 8.1 Top-level menu

`add_menu_page` with slug `customify`, capability `manage_options`, position 59 (between Comments and Appearance). Icon is a data-URI SVG with `fill="currentColor"` so WP recolors per the user's admin scheme.

Custom CSS in `customify_dashboard_v2_menu_icon_css()` pins the icon to 18×18 — the data:image URI has no intrinsic dimensions, WP defaults too large.

### 8.2 Submenu mirror

`customify_dashboard_v2_register_submenu()` adds 2 entries:
- "Dashboard" → `admin.php?page=customify#welcome`
- "Settings" → `admin.php?page=customify#settings`

Changelog tab stays in-page but isn't mirrored (less direct navigation, prefer tight sidebar). The auto-injected parent mirror entry at `$submenu[$slug][0]` gets unset so the list reads as just the two entries.

### 8.3 Active-state sync

WP only inspects `?page=` server-side, so the active highlight is reapplied client-side by an inline `<script>` in `customify_dashboard_v2_sync_submenu()` (hooked on `admin_footer-toplevel_page_customify`). Listens for `hashchange` and toggles `.current` on the matching `<li>` + `<a>`.

### 8.4 Admin body class

`customify-dashboard-page` body class added via `admin_body_class` filter when `get_current_screen()->id === 'toplevel_page_customify'`. CSS in [`dashboard-v2.scss`](../src/backend/admin/dashboard-v2/dashboard-v2.scss) zeros out `#wpcontent` padding-left + `#wpbody-content` padding-bottom so the SPA sits flush against the admin sidebar.

## 9. Build pipeline

### 9.1 Webpack entry

```js
// webpack.config.js entries
{
  'backend/admin/dashboard-v2': 'src/backend/admin/dashboard-v2/index.js',
}
```

Build outputs:
- `build/js/backend/admin/dashboard-v2.js` — main bundle
- `build/js/backend/admin/dashboard-v2.asset.php` — wp-scripts manifest (deps + version hash)
- `build/css/backend/admin/dashboard-v2.css` — theme SCSS compiled
- `build/css/backend/admin/style-dashboard-v2.css` — kit CSS sibling chunk (wp-scripts splits `import '@pressmaximum/dashboard-kit/style.css'` into a `style-` prefixed file)

Theme enqueues both via `AssetEnqueue::enqueueOn()` — the main + the kit sibling chunk under separate handles (`customify-dashboard` + `customify-dashboard-kit`).

### 9.2 Kit symlink dev setup

```bash
# theme's node_modules
ls -la node_modules/@pressmaximum/dashboard-kit
# → symlink to /Users/kientrong/Studio/dashboard-kit
```

Package reference in [`package.json`](../package.json):
```json
"@pressmaximum/dashboard-kit": "file:/Users/kientrong/Studio/dashboard-kit"
```

Effect: theme builds pick up the kit's working-tree source directly. Editing kit files + rebuilding theme = the change ships. Production / co-worker setup: replace `file:` reference with the published npm version once the kit publishes.

Important: in this dev setup, **uncommitted kit changes are picked up by the theme bundle**. If you see unexpected behaviour, check the kit's `git status` first.

### 9.3 Build commands

```bash
npm run build        # production
npm start            # watch mode
npm run lint:js      # ESLint via wp-scripts
```

After source change: rebuild + hard-reload the admin page (cache busts via `asset.php` version hash, but browser may have cached the previous bundle).

## 10. Known landmines

### 10.1 DOMContentLoaded mount gate

Pro bridge JS registers kit filters AFTER the theme bundle loads (the theme bundle calls `mountDashboard()` synchronously by default). Without a defer, the first React render's `applyFilters` calls miss Pro's filter contributions — Pro panels disappear at first paint until something triggers a re-render.

[`index.js`](../src/backend/admin/dashboard-v2/index.js) wraps the mount in a `DOMContentLoaded` listener:

```js
if ( document.readyState === 'loading' ) {
  document.addEventListener( 'DOMContentLoaded', mount );
} else {
  mount();
}
```

Do not remove this gate. It mirrors Blocksify's bootstrap pattern for the same reason.

### 10.2 useMemo trap on `applyFilters`

For every consumer-side `applyFilters` call where a later-loading bundle (Pro) may mutate the filter list, the call MUST recompute per render. A `useMemo` over `[boot]` (stable) caches the pre-Pro values forever — Pro registers its filter, but the cached array is stale.

Affected call sites (all carry NB comments):
- [`Settings.jsx`](../src/backend/admin/dashboard-v2/tabs/Settings.jsx) `applyFilters('customify.dashboard.settings.panels', ...)`
- [`Changelog.jsx`](../src/backend/admin/dashboard-v2/tabs/Changelog.jsx) `applyFilters('customify.dashboard.changelog.sources', ...)`
- [`ProModulesSection.jsx`](../src/backend/admin/dashboard-v2/sections/ProModulesSection.jsx) `applyFilters('customify.dashboard.pro.toggle', null)`

Recomputing per render is cheap; the kit's filter mechanism is a lookup, not a render.

### 10.3 Inline CSS handle mismatch

`wp_add_inline_style($handle, $css)` silently drops the CSS when `$handle` isn't a registered/enqueued style. The dashboard's handle is `customify-dashboard` (set by `AssetEnqueue::enqueueOn`). Any inline-style call attaching to this handle must use exactly that string.

If you rename the enqueue handle, search `wp_add_inline_style` callers across the theme and update them in lockstep.

### 10.4 PHP opcache stale data

After PHP source change in `customify-pro/`, the boot payload + REST responses may serve stale data. Hard-reload (`location.reload(true)`) clears browser cache; PHP opcache typically clears on file mtime change but in some setups manual reset is needed (`studio wp cache flush` or restarting the WP environment).

If `customifyDashboard.proActive` or `customifyProBridge.modules` looks wrong after editing Pro PHP, **hard-reload first** before debugging deeper.

### 10.5 Tooltip on disabled `<input>`

WP's `<Tooltip>` from `@wordpress/components` shows on hover/focus of its child. Disabled `<input>` elements don't fire pointer events, so a bare `<Tooltip><FormToggle disabled /></Tooltip>` doesn't trigger.

Solution: wrap in a `<span>` that catches events. The Tooltip auto-adds `tabindex=0` to the wrapper so keyboard focus also works. See [`ProModulesSection.jsx`](../src/backend/admin/dashboard-v2/sections/ProModulesSection.jsx) `customify-dashboard-module-row__toggle-wrap` pattern.

### 10.6 Composite panel render contract

`panel.kind === 'composite'` stacks `panel.sections[]` children, each rendered via `renderSection()` based on its own `kind` (`schema`, `license`, future). Each child owns its own SaveBar (or none — license panel uses its own activate/deactivate flow). Don't add a wrapping SaveBar around the composite — that breaks the "one save UI per panel" principle.

### 10.7 Pro version source mismatch

`customifyDashboard.proVersion` (header label) comes from Pro plugin's `Plugin Header: Version:` via `get_plugin_data()`. The Changelog tab's Pro source parses `readme.txt` `== Changelog ==` section. If these diverge (e.g. `customify-pro.php` bumps version without adding a readme entry), the header shows a version that the changelog doesn't have.

When bumping Pro version, **always** add a matching `readme.txt` entry. This was the root cause of the 0.4.13 → 0.4.16 mismatch fixed in commit `aed3e8c`.

### 10.8 Kit working-tree picked up by theme build

Per §9.2, the theme's `node_modules/@pressmaximum/dashboard-kit` is a symlink to the kit repo working tree. If the kit has uncommitted changes, the theme bundle ships them. Always check `git -C /Users/kientrong/Studio/dashboard-kit status` if dashboard behaviour seems unrelated to recent theme changes.

## 11. Test surface map

For each PR touching dashboard-v2, smoke-test:

| Surface | Expected |
|---|---|
| WP sidebar submenu | `[Dashboard, Settings]` (no Changelog) |
| Top-bar tabs (Free) | `[Welcome, Free vs Pro, Settings, Changelog]` |
| Top-bar tabs (Pro) | `[Welcome, Settings, Changelog]` (Free vs Pro hidden) |
| Top-right header | `v{themeVersion} — Free version` (Free) / `v{proVersion} — Pro version` (Pro) |
| Welcome → Hero | Card with hairline outline matching siblings; CTA "Open the Customizer" |
| Welcome → Customizer quick links | 3-col grid, 6 tiles, opens Customizer in new tab |
| Welcome → Pro modules (Free) | All rows show **disabled** FormToggle + Tooltip "Available in Pro version" on hover/focus; card head has "Upgrade now" button with external-link icon |
| Welcome → Pro modules (Pro active, WC inactive) | WC Booster + 4 sub-modules disabled + "WooCommerce not activated" pill on parent |
| Welcome → Pro modules (Pro active, WC active) | All toggles live; toggling fires snackbar + persists |
| Free vs Pro (Pro inactive) | Tab present at position 2 (Welcome / **Free vs Pro** / Settings / Changelog); kit `<CompareTable>` renders 6 sections, ~30 rows, green checks + gray em-dashes + muted "Community" / literal "Priority" on support row; CTA banner links to `boot.urls.proUpgrade` |
| Free vs Pro (Pro active) | Tab strip entry absent; visiting `#free-vs-pro` directly → kit `HashRouter` rewrites to `#welcome` and renders the Welcome tab |
| Card titles vs item titles | Card heads computed weight 500 (`--pmdk-font-weight-heading`); module row + grid tile titles 400 (`--pmdk-font-weight-label`) |
| Settings → Theme settings | SaveBar idle: "No pending changes" muted gray; Reset to defaults always enabled |
| Settings → Module settings (Pro) | Typekit section schema fields + SaveBar |
| Settings → Customify Pro (Pro composite) | License section (activate/deactivate, status pill) + Assets section (combineAssets toggle + "Regenerate assets" button) |
| Settings → Pro panel Discard | Disabled when clean; clicking when dirty fires "Changes discarded" snackbar |
| Changelog (Pro inactive) | Single Customify source, no SubNav rail |
| Changelog (Pro active) | SubNav `[Customify, Customify Pro]`; Pro source shows v0.4.16 at top |
| Header version link | When Pro active, points at `#changelog/customify-pro` |
| Help panel (?) | 4 items: Documentation / Changelog / Contact support (`pressmaximum.com/contact`) / Upgrade to Pro |
| AJAX security | `curl -X POST /wp-admin/admin-ajax.php?action=customify/customizer/ajax/get_icons` returns 403 without nonce |

Run sequence:
```bash
npm run build                                                    # rebuild theme
studio wp --path /Users/kientrong/Studio/customify2 plugin {de,re}activate customify-pro  # toggle Pro for path tests
```

Browse to `http://customify2.wp.local/wp-admin/admin.php?page=customify` and walk the table.

## 12. References

- [`DEVELOPMENT.md`](DEVELOPMENT.md) — first-time setup (SSH, composer, npm), daily workflow, troubleshooting. New sessions / co-workers read this first.
- [`@pressmaximum/dashboard-kit` SPEC](https://github.com/PressMaximum/dashboard-kit/blob/main/docs/SPEC.md) — kit public API surface (§5.1 mountDashboard, §5.4 settings building blocks, §5.10 SettingsControllerBase, §9 filter contracts, §16 theming)
- [`@pressmaximum/dashboard-kit` KIT_ISSUES](https://github.com/PressMaximum/dashboard-kit/blob/main/KIT_ISSUES.md) — kit defect log (K-001 … K-011 all closed; relevant context for legacy code comments)
- [Session handoff](handoffs/temp/2026-05-20-dashboard-v2-pro-bridge.md) — narrative history of how the dashboard was built; pairs with the SPEC for "why" context
- Pro branch [`customify-pro-dashboard-compat`](https://github.com/PressMaximum/customify-pro/tree/customify-pro-dashboard-compat) — bridge implementation (no PR; merges directly into theme release flow)
- Legacy dashboard: [`inc/admin/dashboard.php`](../inc/admin/dashboard.php) — `Customify_Dashboard` class at `themes.php?page=customify-legacy`; slated for full removal in this release
