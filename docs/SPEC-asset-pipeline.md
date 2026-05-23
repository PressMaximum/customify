# SPEC — Core Asset Pipeline

Canonical reference for how Customify's CSS and JavaScript are built, named, and enqueued. The webpack entry list, the `index.asset.php` sidecar contract, the `customify-style` handle convention, and the `src/` / `build/` / `assets/` directory roles.

Related references:
- [`DEVELOPMENT.md`](DEVELOPMENT.md) — setup, daily build commands
- [`SPEC-bootstrap.md`](SPEC-bootstrap.md) — singleton that hosts the `scripts()` enqueue method
- [`SPEC-customizer.md`](SPEC-customizer.md) §7 — Customizer-side JS architecture (the consumer of most entries)

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The build pipeline runs webpack via `@wordpress/scripts` and emits hash-versioned JS/CSS plus an `index.asset.php` sidecar that PHP code consumes to enqueue with the correct dependency list and version string.

Three things matter most:

1. **`src/` is the only source of truth.** Never edit `build/` by hand — it's overwritten by `npm run build`.
2. **PHP enqueues read `index.asset.php`.** Never hardcode `array( 'jquery', 'wp-customize-preview' )` — webpack already knows.
3. **The main stylesheet handle is `customify-style`.** Any `wp_add_inline_style` call MUST reference this string exactly.

| Surface | Role |
|---|---|
| `src/` | All JS + SCSS source files |
| `webpack.config.js` `entries` map | Single declaration list for every bundle |
| `build/` | Compiled output — committed for deploy convenience |
| `assets/` | **Legacy** — no longer updated by the build, do not edit |
| `index.asset.php` | Per-entry sidecar carrying dependencies + version hash |
| `Customify::scripts()` | Frontend enqueue orchestrator |
| Per-feature admin enqueues | Each admin SPA / Customizer JS file uses the same `asset.php` pattern |

---

## 2. File map

| File | Responsibility |
|---|---|
| [`webpack.config.js`](../webpack.config.js) | `entries` object — single source of truth for what bundles exist |
| [`package.json`](../package.json) | npm scripts (`build`, `start`, `lint:js`, `makepot`, `release:assets`) + JS dep list |
| [`Gruntfile.js`](../Gruntfile.js) | Packaging only — `grunt zipfile` calls `npm run build` then zips the theme |
| [`inc/class-customify.php`](../inc/class-customify.php) | `scripts()` enqueue method for the frontend |
| `inc/customizer/controls/class-control-base.php` | Customizer control CSS enqueue |
| `inc/admin/editor.php` | Block editor asset enqueue |
| `inc/admin/dashboard.php` | Legacy dashboard asset enqueue |
| `inc/admin/dashboard-v2.php` | New dashboard SPA asset enqueue |
| `inc/class-metabox.php` | Metabox asset enqueue |

---

## 3. Webpack entries

Declared in [`webpack.config.js`](../webpack.config.js) `entries` object (~L292):

### 3.1 Frontend

| Entry | Source | Built |
|---|---|---|
| `frontend/theme` | `src/frontend/theme/index.js` | `build/js/frontend/theme.js`, `build/css/frontend/style-theme.css` |
| `frontend/woocommerce` | `src/frontend/woocommerce/index.js` | `build/js/frontend/woocommerce.js`, `build/css/frontend/style-woocommerce.css` |

### 3.2 Backend React apps

| Entry | Source | Built |
|---|---|---|
| `backend/header-builder` | `src/backend/header-builder/index.js` | `build/js/backend/header-builder.js` (~61 KB) |
| `backend/footer-builder` | `src/backend/footer-builder/index.js` | `build/js/backend/footer-builder.js` (~70 KB) |
| `backend/page-settings` | `src/backend/page-settings/index.js` | `build/js/backend/page-settings.js` |

### 3.3 Customizer

| Entry | Context (Customizer side) |
|---|---|
| `backend/customizer/customizer` | customize-preview (entry imports preview JS + SCSS) |
| `backend/customizer/auto-css` | customize-preview (live preview CSS generator) |
| `backend/customizer/control` | customize-controls (custom control init) |
| `backend/customizer/color-picker-alpha` | customize-controls (alpha-channel picker) |
| `backend/customizer/builder` | customize-controls (V1/V2 router) |
| `backend/customizer/builder-v1` | customize-controls (legacy grid layout builder) |
| `backend/customizer/builder-v2` | customize-controls (V2 jQuery builder) |

Customizer context details: [`SPEC-customizer.md`](SPEC-customizer.md) §7.1.

### 3.4 Admin

| Entry | Used by |
|---|---|
| `backend/admin/dashboard` | Legacy dashboard at `themes.php?page=customify-legacy` |
| `backend/admin/dashboard-v2` | New SPA dashboard at `admin.php?page=customify` |
| `backend/admin/metabox` | Per-post metabox UI |
| `backend/admin/editor` | Block editor admin scripts |

---

## 4. Output layout

```
build/
├── js/
│   ├── frontend/
│   │   ├── theme.js
│   │   ├── theme.min.js
│   │   ├── theme.asset.php
│   │   └── …
│   ├── backend/
│   │   ├── header-builder.js + .asset.php
│   │   ├── footer-builder.js + .asset.php
│   │   ├── customizer/
│   │   │   ├── auto-css.js + .asset.php
│   │   │   ├── control.js + .asset.php
│   │   │   └── …
│   │   └── admin/
│   │       ├── dashboard-v2.js + .asset.php
│   │       └── …
└── css/
    ├── frontend/
    │   ├── style-theme.css + .min.css + .rtl.css
    │   └── style-woocommerce.css + …
    └── backend/
        ├── customizer/customizer.css + …
        └── admin/
            ├── dashboard-v2.css
            └── style-dashboard-v2.css   ← kit sibling CSS chunk
```

Each bundle gets:

- `*.js` — development bundle
- `*.min.js` — production minified bundle
- `*.rtl.css` — RTL-flipped CSS (for `direction: rtl` locales)
- `*.asset.php` — dependencies + version hash sidecar

---

## 5. The `index.asset.php` sidecar

`@wordpress/scripts` generates a PHP file alongside each bundle:

```php
// build/js/backend/customizer/auto-css.asset.php
<?php return array(
    'dependencies' => array( 'wp-customize-preview', 'jquery' ),
    'version'      => 'a3b9d2c7f4e1...',
);
```

PHP enqueues MUST use it — never hand-roll the dependency list:

```php
$asset = require get_template_directory() . '/build/js/backend/customizer/auto-css.asset.php';
wp_enqueue_script(
    'customify-customizer-auto-css',
    get_template_directory_uri() . '/build/js/backend/customizer/auto-css.js',
    $asset['dependencies'],
    $asset['version'],
    true
);
```

Why this matters:

- `dependencies` are extracted from the bundle's `import` statements — webpack knows what `wp.customize`, `wp.element`, jQuery, etc. resolve to and emits the right handle list.
- `version` is a hash of the bundle's content — bumps automatically on any source change, making cache-busting reliable.
- Hand-rolling either invites either over-loading deps (bigger waterfall) or under-loading deps (runtime errors).

---

## 6. The `customify-style` handle (CSS contract)

The main stylesheet is enqueued under handle `customify-style`. Every `wp_add_inline_style` call that attaches generated CSS MUST use this exact handle — otherwise WordPress silently drops the inline CSS.

```php
// CORRECT
wp_enqueue_style( 'customify-style', '…/build/css/frontend/style-theme.css', ... );
wp_add_inline_style( 'customify-style', $auto_css );

// WRONG — typo, inline CSS silently discarded
wp_add_inline_style( 'customify', $auto_css );
```

Call sites that attach inline CSS to `customify-style`:

| File | Purpose |
|---|---|
| [`inc/class-customify.php:406`](../inc/class-customify.php) | Auto-CSS output (`Customify_Customizer_Auto_CSS::auto_css()`) + layout content-size CSS |
| `inc/colors-palette.php` | `:root` token block (`customify-palette-tokens-inline-css` ID — actually attached to a separate handle so it survives auto-css regenerate; see [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §8.3) |

If you ever rename the enqueue handle, grep `wp_add_inline_style` across the theme and update every reference in lockstep.

---

## 7. Frontend enqueue flow (`Customify::scripts()`)

`scripts()` ([`inc/class-customify.php:336`](../inc/class-customify.php)) fires on `wp_enqueue_scripts` priority 95.

```php
public function scripts() {
    // 1. Fire load-scripts action (extension point)
    do_action( 'customify/load-scripts' );

    // 2. CSS list via filter
    $css = apply_filters( 'customify/theme/css', array(
        'google-font'    => $google_fonts_url,
        'customify-style' => 'build/css/frontend/style-theme' . $suffix . '.css',
    ) );
    foreach ( $css as $id => $url ) {
        wp_enqueue_style( $id, $url, array(), $version );
    }

    // 3. JS list via filter
    $js = apply_filters( 'customify/theme/js', array(
        'customify-theme'      => 'build/js/frontend/theme' . $suffix . '.js',
        'customify-font-icons' => ...,
    ) );
    foreach ( $js as $id => $url ) {
        wp_enqueue_script( $id, $url, $deps, $version, true );
    }

    // 4. Inline auto-CSS
    wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS::get_instance()->auto_css() );
    wp_add_inline_style( 'customify-style', $layout_content_size_css );

    // 5. Fire post-enqueue action
    do_action( 'customify/theme/scripts' );
}
```

Extension points: `customify/theme/css` filter (append CSS), `customify/theme/js` filter (append JS), `customify/load-scripts` + `customify/theme/scripts` actions (before/after hooks).

---

## 8. Asset suffix selection (`.min` vs source)

`Customify::get_asset_suffix()` ([`inc/class-customify.php:336`](../inc/class-customify.php)) returns:

- `''` (empty) when `WP_DEBUG` is on — loads source/dev bundles
- `'.min'` when `WP_DEBUG` is off — loads minified production bundles

So `'build/js/frontend/theme' . $suffix . '.js'` resolves to `theme.js` (dev) or `theme.min.js` (prod).

This lets `npm start` (watch mode, dev bundles, no minification) work on a `WP_DEBUG = true` site without needing to swap URLs.

---

## 9. Source ↔ build map

| Source | Build output | Used by |
|---|---|---|
| `src/frontend/theme/` | `build/js/frontend/theme.js` + `build/css/frontend/style-theme.css` | Frontend pages |
| `src/frontend/woocommerce/` | `build/js/frontend/woocommerce.js` + `build/css/frontend/style-woocommerce.css` | WooCommerce pages |
| `src/backend/customizer/` | `build/js/backend/customizer/*.js` + `build/css/backend/customizer/customizer.css` | Customizer admin + preview iframe |
| `src/backend/header-builder/` | `build/js/backend/header-builder.js` | Customizer (Header Builder canvas) |
| `src/backend/footer-builder/` | `build/js/backend/footer-builder.js` | Customizer (Footer Builder canvas) |
| `src/backend/admin/dashboard-v2/` | `build/js/backend/admin/dashboard-v2.js` + `build/css/backend/admin/dashboard-v2.css` + `style-dashboard-v2.css` (kit chunk) | New dashboard SPA |
| `src/backend/admin/dashboard/` | `build/js/backend/admin/dashboard.js` + `build/css/backend/admin/dashboard.css` | Legacy dashboard |
| `src/backend/admin/metabox/` | `build/js/backend/admin/metabox.js` + `build/css/backend/admin/metabox.css` | Per-post metabox |
| `src/backend/admin/editor/` | `build/js/backend/admin/editor.js` + `build/css/backend/admin/editor.css` | Block editor |

---

## 10. The legacy `assets/` directory

`assets/` predates the webpack build. It's **no longer updated** — neither by `npm run build` nor any other automated process. Files inside reference outdated source layouts (e.g. `assets/sass/site/style.scss` is not the current source).

Two rules:

1. **Don't edit `assets/` for new work.** Edit `src/` instead.
2. **Don't delete `assets/` files** unless you've grep'd every PHP enqueue and verified nothing references them — some legacy bundles may still be enqueued for back-compat.

If you find an `assets/` file still enqueued by current PHP, that's a migration tail-end — document it and ideally migrate the consumer to `build/`.

---

## 11. npm scripts

| Command | Purpose | When to use |
|---|---|---|
| `npm run build` | Production build — minified + RTL + asset.php sidecars | Before commit / release |
| `npm start` | Watch mode — rebuilds on save, no minification | Active development |
| `npm run lint:js` | ESLint via wp-scripts | Pre-commit, CI |
| `npm run makepot` | Regenerate `.pot` translation template via `bin/makepot.js` | Before release if strings changed |
| `npm run release:assets` | Build + makepot in one step | Release packaging |

`grunt zipfile` calls `npm run build` internally and zips the theme — used for distribution. Grunt is packaging-only; never use it for CSS/JS compilation.

---

## 12. Design decisions

### 12.1 Commit `build/` to the repo

- **Chose**: `build/` is tracked in git
- **Rejected**: Build on deploy / `.gitignore` `build/`
- **Reason**: Production deploys (and WP marketplace ZIP releases) shouldn't require Node toolchain. Tracking `build/` lets `git clone` + `wp theme install` work end-to-end without `npm install`. Cost: PR diffs are noisier — accepted.

### 12.2 `index.asset.php` over manual dep arrays

- **Chose**: PHP enqueues require the sidecar
- **Rejected**: Hand-maintained `array( 'jquery', ... )` per enqueue site
- **Reason**: Webpack knows the real dependency tree. Manual lists drift — under-listing causes runtime crashes, over-listing slows page load. The asset.php sidecar is regenerated on every build, so it stays accurate by construction.

### 12.3 One CSS handle (`customify-style`)

- **Chose**: All Customizer-generated CSS attaches to `customify-style`
- **Rejected**: Per-feature handles (`customify-colors-style`, `customify-typography-style`, etc.)
- **Reason**: One handle = one inline `<style>` tag = one cache invalidation key. Easier to reason about output order and easier to debug "where is this CSS coming from". The single exception is the palette tokens block — see [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §8.3 for why it gets its own handle.

### 12.4 Asset suffix from `WP_DEBUG`

- **Chose**: `WP_DEBUG = true` loads non-minified bundles
- **Rejected**: Separate config switch
- **Reason**: Developers already toggle `WP_DEBUG`; one fewer thing to remember. Production sites with `WP_DEBUG = false` (the WP-recommended default) always get the minified bundle.

### 12.5 webpack via `@wordpress/scripts`

- **Chose**: `@wordpress/scripts` (curated wp-friendly defaults)
- **Rejected**: Bare webpack config, Vite, esbuild
- **Reason**: `@wordpress/scripts` handles JSX, SCSS, asset.php generation, and `wp.*` externals out of the box. Matches what Gutenberg blocks ship — reduces "WordPress-specific webpack config" maintenance.

---

## 13. Hooks & filters catalog

| Hook | Type | Purpose |
|---|---|---|
| `customify/load-scripts` | action | Before any CSS/JS enqueue in `scripts()` |
| `customify/theme/css` | filter | Append/replace CSS files to enqueue (id => url \| array) |
| `customify/theme/js` | filter | Append/replace JS files to enqueue (id => url \| array) |
| `customify/theme/scripts` | action | After all enqueues complete |

Example — append a custom stylesheet:

```php
add_filter( 'customify/theme/css', function ( $css ) {
    $css['my-overrides'] = get_stylesheet_directory_uri() . '/overrides.css';
    return $css;
} );
```

---

## 14. Known issues / edge cases

### Issue #1 — Adding an entry to `webpack.config.js` requires rebuild

`webpack.config.js` `entries` is read at build time. Adding a new entry without running `npm run build` = the new bundle doesn't exist = PHP enqueue 404s. There's no auto-discovery.

### Issue #2 — Editing `build/*.js` directly is overwritten on next build

Common foot-gun for new contributors. Edit `src/`, run `npm run build`. The build clobbers `build/` deterministically.

### Issue #3 — RTL `.rtl.css` regeneration

`@wordpress/scripts` generates `.rtl.css` automatically for every CSS bundle. PHP doesn't need to enqueue them — WordPress core's RTL stylesheet loader picks them up from the same handle when `is_rtl()`.

### Issue #4 — Inline CSS handle silently drops

`wp_add_inline_style( 'customify', ... )` (note typo: `customify` not `customify-style`) emits no PHP error — the CSS is silently discarded. If you can't find your inline CSS in the rendered page, check the handle FIRST.

### Issue #5 — `assets/` is referenced in CLAUDE.md examples but is legacy

Older code examples in [`../CLAUDE.md`](../CLAUDE.md) historical drafts referenced `assets/build/`. The current path is `build/`. Trust the actual file paths, not stale examples.

---

## 15. Quick reference

| I want to… | Code |
|---|---|
| Add a new webpack entry | Add to `entries` in [`webpack.config.js`](../webpack.config.js), then `npm run build` |
| Enqueue a bundle from PHP | Require its `index.asset.php`, pass `$asset['dependencies']` + `$asset['version']` to `wp_enqueue_script` |
| Append inline CSS to the main stylesheet | `wp_add_inline_style( 'customify-style', $css );` — exact handle |
| Rebuild after src change | `npm run build` (prod) or `npm start` (watch mode) |
| Generate translation template | `npm run makepot` |

---

## 16. Where to look next

**Build pipeline**
- [`webpack.config.js`](../webpack.config.js) — entry list
- [`package.json`](../package.json) — npm scripts + JS deps
- [`Gruntfile.js`](../Gruntfile.js) — packaging only
- [`DEVELOPMENT.md`](DEVELOPMENT.md) — daily workflow

**PHP enqueue sites**
- [`inc/class-customify.php`](../inc/class-customify.php) — frontend (`scripts()`)
- [`inc/customizer/controls/class-control-base.php`](../inc/customizer/controls/class-control-base.php) — Customizer control CSS
- [`inc/admin/editor.php`](../inc/admin/editor.php) — block editor
- [`inc/admin/dashboard-v2.php`](../inc/admin/dashboard-v2.php) — dashboard SPA
- [`inc/class-metabox.php`](../inc/class-metabox.php) — metabox

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.6 — compiled assets off-limits
- [`../AGENTS.md`](../AGENTS.md) §4.7 — `customify-style` handle rule
