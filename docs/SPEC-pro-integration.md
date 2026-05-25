# SPEC — Pro Plugin Integration Contract

Canonical reference for the **bidirectional contract** between the Customify theme and the Customify Pro plugin: class names, shared options, REST namespaces, takeover convention, version compatibility.

For theme-side perspective only (where the theme calls into Pro), see [`pro-integration.md`](pro-integration.md). This SPEC covers both sides.

Related references:
- [`SPEC-dashboard.md`](SPEC-dashboard.md) — dashboard SPA + Pro bridge architecture (most concrete example of integration)
- [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §8 — example of a feature using the takeover convention
- [`migration-guide.md`](migration-guide.md) §7 — shared-key coordination protocol

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The Customify Pro plugin extends the free theme with paid-only modules (mega menu, sticky header, transparent header, hooks system, WooCommerce booster, Typekit, etc.). The two codebases are **separate** — different repos, different release cycles — but share a contract that lets them coexist on the same site.

Four things matter most:

1. **The contract is public API.** Any class name, filter name, option key, or REST endpoint listed in this SPEC is consumed by paying customers. Breaking changes affect 30k+ sites that have already paid for Pro.
2. **Pro modules use a `Customify_Pro_Module_<Name>` naming convention.** Theme code that detects Pro features checks for the specific module class, not the umbrella `Customify_Pro` class.
3. **Takeover convention: Pro module exists → theme port stays dormant.** Theme features that have a Pro counterpart guard their bootstrap at `after_setup_theme:30`.
4. **REST namespaces are strictly split.** `/customify/v1/` (theme) and `/customify-pro/v1/` (Pro). Never cross.

| Surface | Owner | Role |
|---|---|---|
| `Customify_Pro` class | Pro | Top-level "Pro is active" marker |
| `Customify_Pro_Module_*` classes | Pro | Per-module classes that subclass `Customify_Pro_Module_Base` |
| `class_exists()` guards in theme | Theme | Conditional behavior when Pro / specific module is active |
| Shared `wp_options` | Both | Cross-codebase state (Font Awesome version, dashboard settings, etc.) |
| Shared filters / actions | Both | Filters theme exposes for Pro consumption, vice versa |
| `/customify/v1/` REST | Theme | Theme settings + schema endpoints |
| `/customify-pro/v1/` REST | Pro | Module settings, license, asset regenerate |
| Dashboard SPA filter bus | Both | Pro bridge JS injects panels via kit filters (`customify.dashboard.*`) |

---

## 2. File map (theme side)

| File | Responsibility |
|---|---|
| [`inc/compatibility/customify-pro.php`](../inc/compatibility/customify-pro.php) | Top-level Pro compat — gate definitions, upsell registration |
| [`inc/customizer/configs/upsell.php`](../inc/customizer/configs/upsell.php) | Customizer upsell section — hidden when Pro active |
| [`inc/customizer/configs/header/transparent.php`](../inc/customizer/configs/header/transparent.php) | Takeover example — theme port of transparent header, dormant when Pro takes over |
| [`inc/customizer/configs/header/menus.php`](../inc/customizer/configs/header/menus.php) | Mega menu Customizer config (Pro-aware visibility) |
| [`inc/customizer/controls/class-control-base.php`](../inc/customizer/controls/class-control-base.php) | Sets `_pro` flag in control JSON for JS |
| [`inc/compatibility/breadcrumb.php`](../inc/compatibility/breadcrumb.php) | Adjusts breadcrumb when Pro transparent-header module owns the layout |
| [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) | Theme REST namespace; mirrors `customify_fa_ver` on `updated_option` |
| [`inc/admin/dashboard-v2.php`](../inc/admin/dashboard-v2.php) | Dashboard SPA boot data (filterable by Pro bridge) |

---

## 3. Pro module class catalog

All Pro modules subclass `Customify_Pro_Module_Base`. The list as of writing (paid customers depend on these names being stable):

| Class | Purpose |
|---|---|
| `Customify_Pro_Module_Base` | Abstract base — registration, settings, enable/disable |
| `Customify_Pro_Module_Typekit` | Adobe Typekit font integration |
| `Customify_Pro_Module_Hooks` | Custom Hooks CPT — inject HTML/PHP at WP action hooks |
| `Customify_Pro_Module_Blog` | Blog Pro — additional listing layouts |
| `Customify_Pro_Module_WooCommerce_Booster` | WC enhancements (single-product layouts, off-canvas filter, etc.) |
| `Customify_Pro_Module_Mega_Menu` | Mega menu builder |
| `Customify_Pro_Module_WC_Gallery_Slider` | WooCommerce product gallery slider |
| `Customify_Pro_Module_Cookie_Notice` | Cookie banner |
| `Customify_Pro_Module_Portfolio` | Portfolio CPT + layouts |
| `Customify_Pro_Module_Scrolltop` | Scroll-to-top button |
| `Customify_Pro_Module_Header_Sticky` | Sticky header with scroll-triggered animation |
| `Customify_Pro_Module_WC_Quick_View` | Product quick-view modal |
| `Customify_Pro_Module_Header_Footer_Items` | Extra header/footer builder items |
| `Customify_Pro_Module_HTML_2` | Second HTML footer builder item (extends `Customify_Builder_Footer_Item_HTML`) |
| `Customify_Pro_Module_Multilingual` | WPML language switcher |
| `Customify_Pro_Module_Hooks_Admin` | Admin UI for the Hooks module |
| `Customify_Pro_Module_Multiple_Headers` | Per-page header overrides |
| `Customify_Pro_Module_Advanced_Styling` | Advanced color/border/shadow controls |
| `Customify_Pro_Module_Infinity` | Infinite-scroll blog listings |
| `Customify_Pro_Module_Custom_Fonts` | Upload custom font files |
| `Customify_Pro_Module_WC_Off_Canvas_Filter` | Off-canvas WC product filter |
| `Customify_Pro_Module_Header_Transparent` | Transparent header (takes over the theme port) |
| `Customify_Pro_Module_WC_Single_Product_Layout` | Single product layout variants |

### 3.1 Stability promise

Every class name above is **public API**. Same deprecation discipline as theme functions ([`../AGENTS.md`](../AGENTS.md) §4.2):

1. Never delete — mark `@deprecated`, keep body
2. Never rename — add new class alongside; old becomes deprecated wrapper
3. Never change constructor signature — add a new class with a distinct name

A child theme or third-party plugin that does `if ( class_exists( 'Customify_Pro_Module_Hooks' ) )` must continue to find that class for at least one minor version after any rename.

---

## 4. Detection patterns

### 4.1 "Is Pro active?"

```php
if ( class_exists( 'Customify_Pro' ) ) {
    // Pro plugin is installed and active
}
```

Used to:
- Hide the Customizer upsell section
- Pass a `_pro` flag in control JSON so JS shows/hides Pro-only UI
- Conditionally render upgrade-prompt UI in metabox / dashboard

### 4.2 "Is a specific Pro module active?"

```php
if ( class_exists( 'Customify_Pro_Module_Header_Transparent' ) ) {
    return; // theme port stays dormant — Pro takes over
}
```

Used by features that have a Pro counterpart, to avoid double-registration.

### 4.3 "Is a specific Pro module enabled?"

```php
if ( function_exists( 'Customify_Pro' )
    && Customify_Pro()->is_enabled_module( 'header_transparent' )
) {
    // Pro is active AND user has toggled this module on
}
```

Used when behavior must change based on the user's module toggle (not just class existence). The module ID (`header_transparent`) is the snake-cased version of the class suffix.

### 4.4 Where the theme uses these guards today

| File | Class checked | Purpose |
|---|---|---|
| [`inc/class-metabox.php:94`](../inc/class-metabox.php) | `Customify_Pro` | Hide/show Pro-only metabox fields |
| [`inc/template-functions.php:332`](../inc/template-functions.php) | `Customify_Pro` | Pro-only template logic |
| [`inc/customizer/configs/upsell.php:8`](../inc/customizer/configs/upsell.php) | `Customify_Pro` | Hide upsell section when Pro active |
| [`inc/customizer/controls/class-control-base.php:111`](../inc/customizer/controls/class-control-base.php) | `Customify_Pro` | Set `_pro` flag in control JSON |
| [`inc/customizer/configs/header/menus.php:199`](../inc/customizer/configs/header/menus.php) | `Customify_Pro` | Mega menu visibility |
| [`inc/customizer/configs/header/transparent.php:407`](../inc/customizer/configs/header/transparent.php) | `Customify_Pro_Module_Header_Transparent` | **Takeover** — theme port dormant |
| [`inc/compatibility/breadcrumb.php:31`](../inc/compatibility/breadcrumb.php) | `Customify_Pro_Module_Header_Transparent` | Adjust breadcrumb position |

---

## 5. The takeover convention

When a Pro module replaces a theme feature, the theme's port should be **dormant** (no instantiation, no hook registration). Canonical pattern:

```php
add_action( 'after_setup_theme', function () {
    if ( class_exists( 'Customify_Pro_Module_<Name>' ) ) {
        return; // Pro takes over — theme port stays dormant
    }
    Customify_<Name>::get_instance();
}, 30 );
```

### 5.1 Why `after_setup_theme:30`

- Pro plugin loads on `plugins_loaded`, well before `after_setup_theme`
- By priority 30, all Pro modules have registered their classes
- Earlier hooks (e.g. `init`) create a race condition

### 5.2 Pro module contract when taking over

The Pro module is expected to:

1. Register the same Customizer section name (so saved `theme_mod`s remain valid)
2. Honour the same `theme_mod` keys
3. Honour the same `post_meta` keys
4. Apply the same body / row / element CSS classes so frontend SCSS keeps working
5. Expose the same filter signatures the theme port did

Concrete example: [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §8 documents the Header Transparent contract — Pro module must honour `header_{row}_transparent`, `header_transparent_display_pages`, `header_logo_tran*`, `logo_tran_max_width`, `_customify_header_transparent_display` post meta, body class `is-header-transparent`, row class `header--transparent`, logo classes `has-tran-logo` / `no-tran-logo`, and the `customify/render_header/is-transparent` filter.

---

## 6. Shared `wp_options`

| Option | Read by | Written by | Purpose |
|---|---|---|---|
| `customify_fa_ver` | Theme (icon loader) + Pro (dashboard) | Theme dashboard-v2 REST + Pro dashboard | Active Font Awesome version (`v4` / `v6` / `v456`) |
| `customify_dashboard_v2_settings` | Theme dashboard-v2 REST + Pro bridge | Theme dashboard-v2 REST | Dashboard panel settings store |
| `customify_pro_settings` | Pro | Pro | Pro-only settings (theme should not touch) |
| `customify_modules` | Pro | Pro | Pro module on/off flags |
| `elementor_load_fa4_shim` | Elementor compat | Theme (conditionally) | FA4 compat flag |

The theme writes `customify_fa_ver` via the dashboard REST controller and additionally mirrors on `updated_option` / `added_option` hooks ([`inc/admin/dashboard-v2-rest.php:140`](../inc/admin/dashboard-v2-rest.php)) so legacy code paths reading the option directly stay in sync.

Renaming any of the shared options = breakage on every site that has one codebase newer than the other. See [`migration-guide.md`](migration-guide.md) §7 for the coordination protocol.

---

## 7. REST API contract

Two namespaces, owned separately:

### 7.1 `/customify/v1/` — theme

Registered in [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php).

| Route | Methods | Permission | Purpose |
|---|---|---|---|
| `/settings` | GET, POST | `manage_options` | Get / set merged dashboard settings (POST `{}` to reset to defaults) |
| `/settings/schema` | GET | `manage_options` | JSON Schema for client hydration |

### 7.2 `/customify-pro/v1/` — Pro

Registered in `customify-pro/inc/admin/class-dashboard-v2-bridge.php`.

| Route | Methods | Permission | Purpose |
|---|---|---|---|
| `/module/<slug>` | GET, POST | `manage_options` | Per-module settings — slug from `MODULE_REST_SLUGS` map (currently only `/module/typekit`) |
| `/settings/assets` | GET, POST | `manage_options` | Asset combine flag |
| `/assets/regenerate` | POST | `manage_options` | Trigger `Customify_Pro_Assets::clear()` |
| `/license` | GET | `manage_options` | EDD license snapshot (key + status + customer + expiry) |
| `/license/activate` | POST | `manage_options` | Activate license (`{ license: '...' }`) |
| `/license/deactivate` | POST | `manage_options` | Deactivate license |

All endpoints gate on `manage_options` + require `X-WP-Nonce: wp_rest` header.

### 7.3 Namespace rules

- Never register `/customify-pro/v1/foo` from the theme
- Never register `/customify/v1/bar` from Pro
- Both sides assume `wp_rest` nonce
- Both sides return WP_Error or `array` (handled by WP REST controller)

---

## 8. Filter contract

### 8.1 Theme exposes (Pro consumes)

| Filter | Used by Pro for |
|---|---|
| `customify/customizer/config` | Append Pro Customizer sections (Mega Menu, Sticky Header, WC Booster, etc.) |
| `customify/builder/<builder>/items` | Register Pro builder items (transparent-header logo, WPML switcher, HTML #2, etc.) |
| `customify_dashboard_pro_active` | Pro bridge returns `true` → flips SPA's Pro flag |
| `customify_dashboard_localize` | Pro injects `proVersion` + extras into dashboard boot payload |
| `customify/render_header/is-transparent` | Pro Header_Transparent module hooks to extend display rules |
| `customify/breadcrumb/is-showing` | Pro can override per-template (e.g. sticky header offset) |

### 8.2 Pro exposes (theme MAY consume — discouraged)

The theme generally should NOT call into Pro filters/actions — that creates a one-way dependency that breaks if Pro is absent. The exception: theme code that conditionally invokes Pro via `Customify_Pro()` calls and is itself guarded.

### 8.3 Dashboard SPA filter bus

The dashboard SPA uses `@wordpress/hooks` filter namespace `customify.dashboard.*`. The Pro bridge JS registers into these:

| Filter | Purpose |
|---|---|
| `customify.dashboard.tabs` | Append/reorder top-level dashboard tabs |
| `customify.dashboard.routes` | Register nested routes |
| `customify.dashboard.welcome.checklist` | Onboarding item list |
| `customify.dashboard.welcome.sections` | Extra Welcome cards |
| `customify.dashboard.welcome.links` | Customizer quick-link tiles |
| `customify.dashboard.settings.panels` | Settings tab panels (composite / license / proPanel) |
| `customify.dashboard.settings.field-types` | Custom field renderers |
| `customify.dashboard.changelog.sources` | Changelog source list |
| `customify.dashboard.pro.modules` | Welcome-tab Pro module catalog |
| `customify.dashboard.pro.toggle` | Module on/off handler `(id, next) => Promise<{enabled}>` |

See [`SPEC-dashboard.md`](SPEC-dashboard.md) §7.2 for the full bus + boot data contract.

---

## 9. Design decisions

### 9.1 Separate codebases, not monorepo

- **Chose**: Theme and Pro in separate repos
- **Rejected**: Single repo with `pro/` subfolder
- **Reason**: Pro is paid product with its own release cadence, paying customer base, and EDD-managed licensing. Mixing would tie free theme releases to paid plugin releases — bad for both sides.

### 9.2 `class_exists` over plugin slug check

- **Chose**: `class_exists( 'Customify_Pro_Module_<Name>' )`
- **Rejected**: `is_plugin_active( 'customify-pro/customify-pro.php' )`
- **Reason**: Plugin slug check requires `wp-admin/includes/plugin.php` loaded (not always true on frontend). Class existence is the runtime truth — if the class is loaded, it's active. Cheaper and works everywhere.

### 9.3 Theme port + Pro takeover, not "theme stub + Pro implementation"

- **Chose**: Theme ships a full working implementation; Pro replaces wholesale
- **Rejected**: Theme ships an interface/abstract; Pro is required for the feature
- **Reason**: The free theme must work standalone. Transparent header, sticky behavior, basic mega menu — all must exist in the free theme even if Pro adds polish/configurability. Paying customers get the Pro module to override; free users still get the feature.

### 9.4 Hardcoded REST namespace split

- **Chose**: `/customify/v1/` vs `/customify-pro/v1/` — no shared routes
- **Rejected**: Single `/customify/v1/` namespace with Pro routes namespaced underneath (e.g. `/customify/v1/pro/license`)
- **Reason**: Cleaner deactivation story — uninstalling Pro removes its routes entirely; no half-broken state where Pro's URL prefix exists but handlers don't. Also lets Pro version its own REST surface independently (`customify-pro/v2` could ship without touching the theme).

### 9.5 Shared options instead of cross-codebase function calls

- **Chose**: Theme and Pro coordinate via `wp_options`
- **Rejected**: Pro exposes `Customify_Pro_API::get_font_awesome_version()` for theme to call
- **Reason**: Options are well-understood WP state — readable from anywhere, no class loading order concerns, survive plugin deactivation gracefully (option stays, theme uses default). Function calls would require the theme to handle "Pro might not be loaded yet" edge cases everywhere.

### 9.6 Dashboard filter bus for UI extension

- **Chose**: Dashboard SPA exposes named JS filters (`customify.dashboard.*`); Pro bridge registers callbacks
- **Rejected**: Pro bridge mounts its own React tree alongside the theme's
- **Reason**: Two React trees on one page = state sync hell. Pro's panels MUST live inside the theme's SPA shell to share routing, save bar, and settings store. The filter bus pattern (proven in Gutenberg) gives Pro a clean injection surface without controlling the mount.

---

## 10. Known issues / edge cases

### Issue #1 — `class_exists` race on `plugins_loaded`

If theme code checks `class_exists( 'Customify_Pro_Module_*' )` on `plugins_loaded` or earlier, the Pro module class may not be defined yet. Always use `after_setup_theme:30` or later for module-specific guards.

### Issue #2 — Filter cache for `customify/render_header/is-transparent`

The transparent-header `is_transparent()` result is cached in a class static on first call. Filter callbacks registered after the first call are ignored. Pro modules that want to influence this must register early (`after_setup_theme` or `init`). See [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §7 Issue #6.

### Issue #3 — Pro version label vs Pro readme.txt

`customifyDashboard.proVersion` comes from Pro plugin's `Plugin Header: Version:`. The dashboard's Changelog tab parses `readme.txt` `== Changelog ==`. When bumping Pro version, always add a matching `readme.txt` entry — otherwise the header shows a version the changelog doesn't have. Root cause of the 0.4.13 → 0.4.16 mismatch fixed in commit `aed3e8c`.

### Issue #4 — Removing a module from `MODULE_REST_SLUGS`

The map currently has one entry (`Customify_Pro_Module_Typekit` → `typekit`). When adding new modules with settings UI, append to the map AND ship a matching `settings()` form in the module class. The REST route is generated automatically from the map.

### Issue #5 — `_pro` flag in control JSON

[`class-control-base.php:111`](../inc/customizer/controls/class-control-base.php) sets `$this->json['_pro'] = class_exists( 'Customify_Pro' )`. JS reads this to enable/disable Pro-only UI. Don't rename or remove — control JS contracts depend on it.

### Issue #6 — Asset combine + `Customify_Pro_Assets::clear()`

The Pro `/assets/regenerate` route calls a Pro-side static. The theme has no equivalent — asset regeneration is Pro-only because asset combining itself is Pro-only. Don't try to invoke it from theme code.

---

## 11. Version compatibility

There is **no formal version matrix** between theme and Pro versions. Operating assumption: latest theme + latest Pro work together; users who upgrade only one side may hit edge cases.

### 11.1 What breaks on version mismatch

| Mismatch | Symptom |
|---|---|
| Newer theme + older Pro | Pro doesn't know about new filters/options the theme expects — old feature still works, new feature absent |
| Older theme + newer Pro | Pro tries to extend filters/options the theme hasn't added yet — Pro module silently no-ops |
| Same major, off-minor | Generally fine — minor versions are additive |
| Different major (when one exists) | Breaking — requires explicit user action |

### 11.2 Best practices for releases

1. Test theme + Pro together before tagging either side
2. If theme adds a new filter Pro will consume, ship Pro first (theme works without Pro consuming)
3. If theme renames anything Pro relies on, ship Pro first with both-shape support
4. Coordinate major-version bumps — never release theme `1.0.0` if Pro hasn't shipped a compatible release

---

## 12. Hooks & filters catalog (theme side)

Filters that exist primarily for Pro consumption — changing signatures requires Pro coordination:

| Hook | Type | Purpose |
|---|---|---|
| `customify/customizer/config` | filter | Append config items (Pro's primary extension point) |
| `customify/builder/<builder>/items` | filter | Register builder items (Pro extends header + footer) |
| `customify/builder/<builder>/rows` | filter | Override builder rows (Pro can add new rows) |
| `customify/render_header/is-transparent` | filter | Final transparent-header override |
| `customify/breadcrumb/is-showing` | filter | Conditional breadcrumb rendering |
| `customify_dashboard_pro_active` | filter | Pro flips to `true` |
| `customify_dashboard_localize` | filter | Pro injects boot payload extras |
| `customify_dashboard_v2_schema` | filter | Append schema panels |

Full filter / action catalog: [`api-reference.md`](api-reference.md).

---

## 13. Quick reference

| I want to… | How |
|---|---|
| Detect Pro is active in theme code | `if ( class_exists( 'Customify_Pro' ) ) { ... }` |
| Detect a specific Pro module is active | `if ( class_exists( 'Customify_Pro_Module_<Name>' ) ) { ... }` |
| Detect a Pro module is **enabled** by user | `Customify_Pro()->is_enabled_module( '<id>' )` (after the `class_exists` check) |
| Add a theme feature that Pro will replace | Use the takeover pattern in §5 |
| Share state between theme and Pro | Use a `wp_options` row with a `customify_` prefix; coordinate the key name |
| Expose a filter for Pro to consume | Add to [`api-reference.md`](api-reference.md) + commit to never changing the signature |
| Add a new REST route for theme | `/customify/v1/<route>` — register in `inc/admin/dashboard-v2-rest.php` |

---

## 14. Where to look next

**Theme files**
- [`inc/compatibility/customify-pro.php`](../inc/compatibility/customify-pro.php)
- [`inc/customizer/configs/upsell.php`](../inc/customizer/configs/upsell.php)
- [`inc/customizer/configs/header/transparent.php`](../inc/customizer/configs/header/transparent.php) — canonical takeover example
- [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) — theme REST namespace

**Pro plugin files**
- `customify-pro/customify-pro.php` — Pro entry, defines `Customify_Pro` class
- `customify-pro/inc/admin/class-dashboard-v2-bridge.php` — Pro REST namespace + dashboard bridge
- `customify-pro/inc/modules/class-customify-pro-module-base.php` — base class for all modules
- `customify-pro/assets/js/admin/dashboard-v2-bridge.js` — JS filter bus registration

**Related specs**
- [`pro-integration.md`](pro-integration.md) — theme-side perspective (shorter, less bidirectional)
- [`SPEC-dashboard.md`](SPEC-dashboard.md) — full dashboard SPA + Pro bridge architecture
- [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §8 — concrete takeover example

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.3 — Pro boundary rule
- [`migration-guide.md`](migration-guide.md) §7 — shared-key coordination
