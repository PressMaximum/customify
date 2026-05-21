# Customify Theme — Claude Reference

## Overview

**Customify** is a lightweight, SEO-optimized, multipurpose WordPress classic theme developed by PressMaximum.

- **Version**: 0.4.13 | **Text domain**: `customify`
- **Requires**: WordPress 4.9+, PHP 5.6+ | **Tested up to**: 6.7.1
- **License**: GPL v2+
- **Architecture**: Classic theme (not FSE / Full Site Editing)
- **Entry point**: `functions.php` → bootstraps the `Customify()` singleton from `inc/class-customify.php`

Key features: WYSIWYG Header & Footer builder inside the Customizer, compatible with Elementor / Beaver Builder / Divi, WooCommerce, BuddyPress, and bbPress.

---

## Project Structure

```
customify/
├── functions.php              # Entry point — registers filters for customify_the_content/title, loads Customify class
├── style.css                  # Theme header + compiled CSS (SASS output — do not edit directly)
├── style.min.css              # Minified production CSS
├── theme.json                 # Block editor settings (WP 6.x) — contentSize, wideSize, color palette, typography
├── page.php / single.php      # Standard WP template files
├── archive.php / search.php
├── header.php / footer.php
├── woocommerce.php            # WooCommerce override entry
│
├── inc/                       # Theme logic
│   ├── class-customify.php    # Main singleton — theme_setup, scripts, sidebars, admin_includes
│   ├── class-metabox.php      # Per-page/post meta (layout override, sidebar, etc.)
│   ├── element-classes.php    # body_class, site_content_class, main_content_class filters
│   ├── template-functions.php # customify_get_layout() and other helper functions
│   ├── template-tags.php      # Template tags (entry meta, pagination, etc.)
│   ├── template-class.php     # Template rendering utilities
│   ├── extras.php             # Miscellaneous hooks
│   │
│   ├── admin/
│   │   ├── editor.php         # Customify_Editor class — injects CSS/fonts into block editor
│   │   ├── block-styles.php   # register_block_style() — Ghost, Rounded, Shadow, Card, etc.
│   │   └── dashboard.php      # Dashboard widgets
│   │
│   ├── customizer/
│   │   ├── class-customizer.php          # Initializes the entire Customizer
│   │   ├── class-customizer-auto-css.php # Auto-generates CSS from Customizer settings
│   │   ├── class-customizer-fonts.php    # Google Fonts loader
│   │   ├── configs/                      # Per-section/panel configuration
│   │   │   ├── styling.php              # Colors (primary, secondary, text, link, heading)
│   │   │   ├── typography.php           # Font family, size, line-height
│   │   │   ├── layouts.php              # Container width, sidebar layout, content layout
│   │   │   ├── single-blog-post.php     # Content width for single posts
│   │   │   ├── blogs.php                # Blog listing settings
│   │   │   ├── header/ footer/          # Builder panels
│   │   │   └── ...
│   │   └── controls/                    # Custom Customizer controls (typography, slider, color, etc.)
│   │
│   ├── blog/
│   │   ├── class-post-entry.php         # Renders individual post entries
│   │   ├── class-posts-layout.php       # Grid/list layout for blog listings
│   │   └── class-related-posts.php      # Related posts widget
│   │
│   ├── panel-builder/                   # Header & Footer WYSIWYG builder
│   │
│   └── compatibility/
│       ├── elementor.php                # Elementor integration
│       ├── breadcrumb.php               # Breadcrumb compatibility
│       └── woocommerce/                 # WooCommerce templates & hooks
│
├── assets/
│   ├── sass/
│   │   ├── site/
│   │   │   ├── style.scss              # Root SASS import file
│   │   │   ├── base/
│   │   │   │   ├── _base.scss          # Typography, reset, core elements
│   │   │   │   └── _blocks.scss        # Block editor styles (alignwide, alignfull, block-specific CSS)
│   │   │   ├── layouts/
│   │   │   │   ├── _layouts.scss       # Sidebar layouts, container, grid
│   │   │   │   └── _blogs.scss         # Blog listing layouts
│   │   │   ├── utils/                  # Variables, mixins, functions
│   │   │   └── vendors/               # Gridlex grid system
│   │   └── admin/
│   │       └── editor.scss            # Block editor admin styles (imports from site/)
│   │
│   ├── css/                           # Compiled CSS — never edit these files directly
│   │   └── admin/editor.css
│   ├── js/                            # JavaScript
│   └── fonts/                         # Font assets
│
└── patterns/                          # Block patterns (WP 6.0+ auto-registers from this folder)
    ├── hero-centered.php
    ├── features-three-columns.php
    ├── cta-banner.php
    ├── media-text-left.php
    └── testimonial.php
```

---

## Architecture

### Singleton Access

Use the `Customify()` helper anywhere to access the main instance:

```php
Customify()->get_setting('container_width');
Customify()->customizer->get_field_setting('key');
```

### Layout System

`customify_get_layout()` determines the layout and adds a `main-layout-{layout}` body class:

| Layout value | Body class | Description |
|---|---|---|
| `content` | `main-layout-content` | No sidebar (full width) |
| `content-sidebar` | `main-layout-content-sidebar` | Content + right sidebar |
| `sidebar-content` | `main-layout-sidebar-content` | Left sidebar + content |
| `sidebar-content-sidebar` | `main-layout-sidebar-content-sidebar` | Three columns |

### Block Editor Integration

1. `theme.json` — declares `contentSize: 780px`, `wideSize: 1200px`, color palette, typography presets
2. `inc/admin/editor.php` (`Customify_Editor`) — injects Customizer-generated CSS into the block editor
3. `inc/admin/block-styles.php` — registers custom block style variations
4. `patterns/` — block patterns auto-registered by WP from PHP files in this folder

### Build Pipeline — webpack only

```
src/  →  npm run build  →  build/
```

All SCSS and JS sources live in `src/`. All compiled output goes to `build/`. The `assets/` directory is **legacy** and is no longer updated by the build.

**Grunt is packaging-only** (`grunt zipfile`, i18n pot generation). It calls `npm run build` internally. Never use grunt for CSS/JS compilation.

### JS Build Pipeline (webpack via @wordpress/scripts)

```
src/<entry>/index.js  →  npm run build  →  assets/build/<entry>/index.js
                                            assets/build/<entry>/index.asset.php
```

| Command | Description |
|---|---|
| `npm run build` | Production build (minified) |
| `npm run start` | Watch mode with hot rebuild |
| `npm run lint:js` | ESLint via wp-scripts |

**Entry points** are declared in [`webpack.config.js`](webpack.config.js) under the `entries` object. Add one line per new script.

**Asset loading in PHP** — always use `index.asset.php` (generated by webpack) for dependencies and version hash. See [`inc/admin/page-settings.php`](inc/admin/page-settings.php) for the pattern:

```php
$asset = require get_template_directory() . '/assets/build/<entry>/index.asset.php';
wp_enqueue_script( 'handle', '...assets/build/<entry>/index.js', $asset['dependencies'], $asset['version'] );
```

`assets/build/` is in `.gitignore` — run `npm run build` after cloning.

---

## Rules

### Production scale — 30,000+ live sites

Customify + Customify Pro are deployed on **30,000+ real user sites in production**. This is not a green-field project; every change ships to a live install base that:

- Already has saved `theme_mod` data (Customizer settings), `wp_options` rows (`customify_pro_settings`, `customify_modules`, `customify_fa_ver`, etc.), and Pro module settings (`customify_pro_settings`).
- Already has live header/footer builder layouts stored as URL-encoded JSON in `theme_mod 'header_builder_panel_v2'` / `'footer_builder_panel_v2'`.
- Already has `customify_hook` CPT posts authored by users (Hooks module).
- May be on older PHP / WP versions, older Pro versions, older child themes.
- Cannot be expected to manually re-configure anything after an update.

**Any change that touches persistent data — `theme_mod` keys, `wp_options` keys, `post_meta` keys, CPT slugs, sanitize callbacks, value shapes, default values — must explicitly plan for:**

1. **Backward compatibility** — read both old AND new shapes; new code accepts existing data without throwing or silently dropping fields. Never assume a field exists; always provide a sensible default.
2. **Migration** — if the storage shape genuinely needs to change, write a one-time migration that runs on `init` / `upgrader_process_complete` / a version-stamped option flag. Migration must be idempotent (safe to run twice), reversible-by-defaults (an aborted migration leaves the site usable), and logged so support can debug.
3. **Renames are migrations** — changing a `theme_mod` key, an option name, or a sanitize callback signature is a data migration even if the code change looks small. Keep the old key as a read-only fallback for at least one minor version cycle.
4. **Defaults must not change silently** — if a field's `default` value changes, existing sites with the old value still render correctly; sites with no saved value get the new default. Never assume "users will just re-save".
5. **Pro ↔ theme contracts** — module class names (`Customify_Pro_Module_*`), filter names, action names, REST endpoints under `/customify/v1/` and `/customify-pro/v1/` are public API consumed by paid customers. Same rules as Never-Delete-Or-Rename below: deprecate, don't break.
6. **Public selectors / classes / IDs** — frontend CSS classes (`.customify-header`, `.col-v2-left`, `#header-menu-sidebar`, etc.) are referenced by user custom CSS, page builders, and child theme overrides. Treat them as public API. Change at the cost of breaking thousands of customizations.

Concrete pattern for any storage-touching change:

```php
// In a config file or migration callback, on init:
$current = get_option( 'customify_pro_settings', array() );

// 1. Read both old + new shape
$value = isset( $current['new_key'] ) ? $current['new_key']
       : ( isset( $current['old_key'] ) ? $current['old_key'] : $default );

// 2. If migrating, stamp a version flag so the migration runs once
$migrated_to = get_option( 'customify_migrations', array() );
if ( ! isset( $migrated_to['feature_x_v2'] ) ) {
    customify_migrate_feature_x( $current ); // idempotent
    $migrated_to['feature_x_v2'] = time();
    update_option( 'customify_migrations', $migrated_to );
}
```

**When in doubt, ask before changing storage.** A 5-minute clarification is cheaper than a support escalation across 30,000 sites.

### Never delete or rename existing functions

Existing PHP functions, methods, and hooks are part of the public API. Child themes, plugins, and third-party code may call them at any time. Deleting or renaming them causes fatal errors on sites that depend on the old names.

**Rules:**

1. **Never delete** an existing function — mark it `@deprecated` instead and keep the body intact (or forward to the new function).
2. **Never rename** an existing function — add the new name alongside the old one; leave the old name as a `@deprecated` wrapper.
3. **New function with overlapping purpose** — use a *similar but distinct* name (e.g. `customify_render_header_v2()` alongside `customify_render_header()`). Never reuse the original name.
4. **Deprecated wrapper pattern:**

```php
/**
 * @deprecated 0.5.0 Use customify_render_header_v2() instead.
 */
function customify_render_header() {
    _deprecated_function( __FUNCTION__, '0.5.0', 'customify_render_header_v2' );
    customify_render_header_v2();
}
```

5. **Class methods follow the same rule** — mark with `@deprecated` docblock and `_deprecated_function()` call; keep the method in the class.

This rule applies to: standalone functions, class methods, action/filter callback names, and template tags.

---

### Language — English only

All code comments, docblocks, inline notes, `.md` files, commit messages, and any other text written inside the codebase must be in **English**. No Vietnamese in source files of any kind.

### alignwide / alignfull — modern approach (no transform hack)

Use CSS custom properties from `theme.json`. Do not use the old `transform + left` technique — it conflicts with the `margin: auto` rule on `.entry-content > *`.

```scss
// CORRECT
.entry-content > .alignwide {
    max-width: var(--wp--style--global--wide-size, 1200px);
    width: 100%;
    // margin: auto is inherited from `.entry-content > *`
}
.entry-content > .alignfull {
    width: 100vw;
    margin-left: calc(50% - 50vw) !important;
    margin-right: calc(50% - 50vw) !important;
}

// WRONG — conflicts with margin: auto, shifts block off-center
.entry-content > .alignwide {
    transform: translateX(-50%);
    left: 50%;
    position: relative;
}
```

### Container width must sync the CSS custom property

When `container_width` changes in the Customizer, it must also update `--wp--style--global--wide-size` so the Customizer, `theme.json`, and the block editor stay in sync. This is handled in `inc/customizer/configs/layouts.php` via the `css_format` of the `container_width` field.

### Deprecated block editor selectors (WP 6.0+)

| Old (deprecated) | Use instead |
|---|---|
| `.editor-post-title__input` | `.wp-block-post-title` |
| `.edit-post-visual-editor` | `.editor-styles-wrapper` |
| `.edit-post-layout__content` | `.editor-styles-wrapper` |
| `.wp-block[data-align="wide"]` | `.alignwide` |
| `wp-edit-post` style handle | `wp-edit-blocks` (WP 6.2+) |

### Adding a new block style

1. Register in `inc/admin/block-styles.php` via `register_block_style()`.
2. Add CSS to `assets/sass/site/base/_blocks.scss` using selector `.wp-block-{name}.is-style-{slug}`.
3. Run `grunt sass && grunt cssmin`.

### Adding a new block pattern

Create a PHP file in `patterns/` with a standard header comment:

```php
<?php
/**
 * Title: Display Name
 * Slug: customify/unique-slug
 * Categories: customify, category
 * Keywords: keyword1, keyword2
 */
?>
<!-- wp:... -->
```

WP 6.0+ auto-registers all PHP files in the `patterns/` folder — no additional code required.

### Adding a new Customizer field

1. Add a config array to the appropriate file in `inc/customizer/configs/`.
2. Set `selector` and `css_format` so CSS is auto-generated by `Customify_Customizer_Auto_CSS`.
3. If the field must also apply inside the block editor, add its key to the `$keys` array in `inc/admin/editor.php::css()`.

### Never edit compiled CSS or JS directly

All CSS and JS are compiled from `src/`. Edit `.scss` / `.js` source files in `src/`, then run `npm run build`. Files inside `build/` are artifacts — never edit them by hand.

### CSS handle must match between enqueue and inline style

`wp_add_inline_style()` silently drops its CSS if the handle it references is not enqueued. The main stylesheet is registered under the handle `customify-style`. Any call that attaches generated CSS to this handle must use exactly that string.

```php
// CORRECT — handle matches the enqueued key
wp_enqueue_style( 'customify-style', ... );
wp_add_inline_style( 'customify-style', $css );

// WRONG — typo or different handle → inline CSS is silently discarded
wp_add_inline_style( 'customify', $css );
```

If you rename the enqueue handle, update every `wp_add_inline_style()` call that references it.

---

### Every AJAX handler needs nonce + capability + sanitized input

All `wp_ajax_*` handlers must follow this pattern without exception:

```php
add_action( 'wp_ajax_my_action', function () {
    // 1. Verify nonce.
    check_ajax_referer( 'my_nonce_action', 'nonce' );

    // 2. Check capability.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Forbidden', 403 );
    }

    // 3. Sanitize every input field before use.
    $value = sanitize_text_field( wp_unslash( $_POST['value'] ?? '' ) );

    // 4. Respond and exit.
    wp_send_json_success( $result );
} );
```

---

### Always null-check DOM queries before using the result

`getElementById()` and `querySelector()` return `null` when the element does not exist (e.g. on admin pages, widget editor, or pages where the header is not rendered). Calling `.contains()`, `.getBoundingClientRect()`, or any property on `null` throws a TypeError that breaks the entire script.

```js
// WRONG — crashes if element is absent
var sidebar = document.getElementById('header-menu-sidebar');
var inside = sidebar.contains(e.target);

// CORRECT
var sidebar = document.getElementById('header-menu-sidebar');
if ( ! sidebar ) { return; }
var inside = sidebar.contains(e.target);
```

**Known unsafe spots in `src/frontend/js/theme.js`** (fix before adding code nearby):

- Line ~232: `menuSidebar.contains(e.target)` — no null guard on `menuSidebar`
- Line ~607: `menuSidebarInner.getBoundingClientRect()` — no null guard
- Line ~682: `button.getBoundingClientRect()` — `button` can be null inside `searchFormAutoAlign`

---

### Verify third-party HTML selectors against real source before writing CSS

Do not assume class names for WP core blocks or WooCommerce elements. The actual markup frequently differs from what the documentation or intuition suggests. Always verify against the source.

```bash
# Check a WP core block's rendered HTML
grep -r "class=" /path/to/wordpress/wp-includes/blocks/<block-name>/
```

**Known selector mistakes that have caused broken styles:**

| Wrong (assumed) | Correct (from WP source) |
|---|---|
| `.wp-block-categories > li` | `.wp-block-categories-list .cat-item` |
| `.wp-block-archives > li` | `.wp-block-archives-list > li` |
| `tfoot td#prev` / `tfoot td#next` | `.wp-calendar-nav .wp-calendar-nav-prev` / `.wp-calendar-nav-next` |
| `.wp-block-categories__post-count` | (class does not exist — remove) |

---

## Registered Sidebars

- `sidebar-1` — Primary Sidebar
- `sidebar-2` — Secondary Sidebar
- `footer-1` through `footer-6` — Footer Sidebars

---

## Plugin Compatibility

| Plugin / Feature | Handler |
|---|---|
| WooCommerce | `inc/compatibility/woocommerce/`, `woocommerce.php` |
| Elementor | `inc/compatibility/elementor.php` |
| Breadcrumb | `inc/compatibility/breadcrumb.php` |
| Page builders | Supported natively via fullwidth layout |

---

## Useful Filters

```php
// Override layout for a specific page
add_filter( 'customify_get_layout', function( $layout ) {
    if ( is_page( 123 ) ) return 'content';
    return $layout;
} );

// Extend the primary color CSS targets
add_filter( 'customify/styling/primary-color', function( $css ) {
    return $css . '.my-element { color: {{value}}; }';
} );
```
