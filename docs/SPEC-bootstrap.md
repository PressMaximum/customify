# SPEC — Core Architecture

Canonical reference for Customify's foundational layer: singleton bootstrap, layout resolution, element class system, and template hierarchy. The pieces that don't belong to a more specific subsystem.

Related references:
- [`SPEC-customizer.md`](SPEC-customizer.md) — Customizer system that the bootstrap brings up
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) — builder layer that hooks into the render pipeline
- [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) — build pipeline + asset loading
- [`README.md`](README.md) §"How it all fits together" — cross-cutting flow diagram + SPEC index

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The "core" is everything that has to be in place before a Customizer field, builder item, or block style can exist. Three things matter most:

1. **The `Customify()` singleton is the entry to every theme API.** All settings reads, all subsystem access, all sidebars and hooks go through it.
2. **Layout is resolved per-request by `customify_get_layout()`.** Its result drives body classes, sidebar visibility, and grid widths.
3. **Element classes are a filterable chain.** Every wrapper in the template (`body`, site content, sidebar, main content, grid, container) gets its classes from a named filter.

| Surface | Role |
|---|---|
| `Customify()` helper | Global singleton accessor |
| `Customify::init()` | Bootstrap orchestrator |
| `customify_get_layout()` | Per-request layout resolver |
| Body / wrapper class filters | Templates compose classes from filterable chains |
| Standard WP template files | `page.php`, `single.php`, etc. |

---

## 2. File map

| File | Responsibility |
|---|---|
| [`functions.php`](../functions.php) | Entry point. Registers `customify_the_content` / `customify_the_title` filters, instantiates `Customify()`. |
| [`inc/class-customify.php`](../inc/class-customify.php) | Main singleton — bootstrap, init, includes, scripts, sidebars, configs loader, compatibility loader. |
| [`inc/template-functions.php`](../inc/template-functions.php) | `customify_get_layout()` + render-time helpers (`is_post_title_display`, `is_header_display`, etc.). |
| [`inc/template-tags.php`](../inc/template-tags.php) | Template tags called from theme files (entry meta, pagination, comments). |
| [`inc/template-class.php`](../inc/template-class.php) | Class-chain echo helpers (`customify_site_content_class`, `customify_main_content_class`, etc.). |
| [`inc/element-classes.php`](../inc/element-classes.php) | `body_class` filter + base class additions for every wrapper. |
| [`inc/extras.php`](../inc/extras.php) | Misc utilities used across the theme. |
| [`page.php`](../page.php), [`single.php`](../single.php), [`archive.php`](../archive.php), [`search.php`](../search.php), [`404.php`](../404.php), [`index.php`](../index.php) | Standard WP template files. |
| [`header.php`](../header.php), [`footer.php`](../footer.php) | Site chrome. Call `customify_customize_render_header()` / `customify_customize_render_footer()`. |

---

## 3. Singleton bootstrap

### 3.1 Entry

```php
// functions.php
function Customify() {
    return Customify::get_instance();
}
Customify();  // instantiate on theme load
```

### 3.2 `Customify::__construct()`

Reads theme header (Version, ThemeURI, Name, Author) into static properties, then calls `init()`.

### 3.3 `Customify::init()` — execution order

```php
public function init() {
    $this->init_hooks();                                 // 1
    $this->includes();                                   // 2
    $this->customizer = Customify_Customizer::get_instance();
    $this->customizer->init();                           // 3
    do_action( 'customify/init' );                       // 4
}
```

Step-by-step:

1. **`init_hooks()`** registers the long-lived WordPress hooks:
   - `after_setup_theme` → `theme_setup()` (theme supports, fonts setup)
   - `after_setup_theme` → `content_width()` priority 0 (applies `customify_content_width` filter)
   - `widgets_init` → `register_sidebars()`
   - `wp_enqueue_scripts` → `scripts()` priority 95
   - `excerpt_more` → `excerpt_more()` filter
   - `excerpt_length` → `excerpt_length()` filter
   - `wp_head` priority 2 → `customify_style()` (placeholder for inline style block)
   - `wp_head` priority 8 → `print_palette_tokens()` (`<style id='customify-palette-tokens-inline-css'>`)

2. **`includes()`** requires core files in order:
   - `inc/class-metabox.php`, `template-class.php`, `colors-palette.php`, `extras.php`
   - `inc/element-classes.php`, `template-tags.php`, `template-functions.php`
   - `inc/customizer/class-customizer.php`
   - `inc/panel-builder/class-panel-builder.php`
   - `inc/blog/class-related-posts.php`, `class-post-entry.php`, `class-posts-layout.php`, `functions-posts-layout.php`
   - `inc/admin/block-styles.php`, `page-settings.php`, `dashboard-v2-rest.php`

   Then hooks `after_setup_theme` priority 2 → `load_configs()`, `load_compatibility()`.

   If `is_admin()`, calls `admin_includes()` which requires `inc/admin/editor.php`, `dashboard.php`, `dashboard-v2.php`.

3. **`Customify_Customizer::init()`** hooks `customize_register` priority 666 and registers control classes. See [`SPEC-customizer.md`](SPEC-customizer.md) §1.3.

4. **`do_action( 'customify/init' )`** — extension point for code that needs to run after Customify is fully booted.

### 3.4 Config loader

`load_configs()` (priority 2 on `after_setup_theme`) requires Customizer config files in this order:

```
inc/customizer/configs/upsell.php
inc/customizer/configs/layouts.php
inc/customizer/configs/blogs.php
inc/customizer/configs/single-blog-post.php
inc/customizer/configs/related-posts.php
inc/customizer/configs/search.php
inc/customizer/configs/styling.php
inc/customizer/configs/typography.php
inc/customizer/configs/page-header.php
inc/customizer/configs/background.php
inc/customizer/configs/colors.php
inc/customizer/configs/compatibility.php
inc/customizer/configs/header/*  (panel + every item)
inc/customizer/configs/footer/*  (panel + every item)
```

The list is hardcoded — adding a new config file requires editing the loader array in `class-customify.php`.

### 3.5 Compatibility loader

`load_compatibility()` (priority 2 on `after_setup_theme`) requires:

```
inc/compatibility/customify-pro.php      Pro guards + upsell
inc/compatibility/elementor.php          Elementor (FA4 shim, fullwidth handoff)
inc/compatibility/breadcrumb.php         Yoast / NavXT
inc/compatibility/woocommerce/woocommerce.php
```

See [`SPEC-compat-overview.md`](SPEC-compat-overview.md) for per-plugin details.

### 3.6 Singleton access patterns

```php
Customify()->get_setting( 'container_width' );
Customify()->get_setting( 'logo_width', 'tablet' );     // device-aware
Customify()->customizer                                  // Customify_Customizer instance
Customify()->customizer->get_field_setting( 'name' )    // config definition
Customify()->is_woocommerce_active()
Customify()->is_using_post()
Customify()->get_current_post_id()
Customify()->get_media( $value, 'full' )                // image control → URL
```

`get_setting()` is device-aware: pass `'desktop'`, `'tablet'`, or `'mobile'` as the second arg if the field has `device_settings: true`.

---

## 4. Layout system

### 4.1 The four layouts

`customify_get_layout()` ([`inc/template-functions.php:141`](../inc/template-functions.php)) returns exactly one of:

| Value | Body class | Description |
|---|---|---|
| `content` | `main-layout-content` | No sidebar (full width) |
| `content-sidebar` | `main-layout-content-sidebar` | Content + right sidebar |
| `sidebar-content` | `main-layout-sidebar-content` | Left sidebar + content |
| `sidebar-content-sidebar` | `main-layout-sidebar-content-sidebar` | Three columns |

### 4.2 Resolution order

```
                ┌────────────────────────────────────────────┐
                │ customify_get_layout filter — overrides?   │
                └─────┬──────────────────────────────────────┘
                      │ no override
                      ▼
                ┌────────────────────────────────────────────┐
                │ Post meta _customify_content_layout       │
                │  = 'full-width' | 'full-stretched'        │
                │ → force 'content'                          │
                └─────┬──────────────────────────────────────┘
                      │ no force
                      ▼
                ┌────────────────────────────────────────────┐
                │ Post meta _customify_sidebar              │
                │ (only when context allows override)        │
                └─────┬──────────────────────────────────────┘
                      │ no per-post override
                      ▼
                ┌────────────────────────────────────────────┐
                │ Context-aware Customizer setting           │
                │ - is_home / page_for_posts → blog_sidebar  │
                │ - is_single → single_blog_post_sidebar     │
                │ - is_archive → archive_sidebar             │
                │ - is_page → page_sidebar                   │
                │ - else → sidebar_layout (global default)   │
                └─────┬──────────────────────────────────────┘
                      ▼
                  Final layout value
```

### 4.3 Per-post overrides

Two `post_meta` keys interact with the layout decision:

| Meta key | Values | Effect |
|---|---|---|
| `_customify_content_layout` | `'default'` / `'full-width'` / `'full-stretched'` | When `full-*`, forces layout to `content` (no sidebar) |
| `_customify_sidebar` | `'default'` / `'content'` / `'content-sidebar'` / `'sidebar-content'` / `'sidebar-content-sidebar'` | When set, becomes the layout (unless `full-*` above overrides) |

Both metaboxes are registered in [`inc/class-metabox.php`](../inc/class-metabox.php).

### 4.4 Programmatic override

```php
add_filter( 'customify_get_layout', function ( $layout ) {
    if ( is_singular( 'product' ) ) {
        return 'content';
    }
    return $layout;
} );
```

Return any of the four layout values to override. Return `null` to fall through to the next priority tier.

---

## 5. Element class system

Every wrapper in the template gets its classes from a filter. The template tags in [`inc/template-class.php`](../inc/template-class.php) echo the filtered chain.

### 5.1 The class filter chain

| Filter | Wrapper | Default classes added |
|---|---|---|
| `body_class` | `<body>` | `{layout}`, `main-layout-{layout}`, site layout class, animate mode class, page-cover flag |
| `customify_site_content_class` | Site content wrapper | `customify-site-content` |
| `customify_site_content_grid_class` | Grid wrapper inside site content | `gridlex-row` + layout-specific grid mods |
| `customify_site_content_container_class` | Container inside grid | Gridlex container classes |
| `customify_sidebar_primary_class` | Primary sidebar | `col-3` (in `sidebar-content` / `content-sidebar`), `col-3` (in three-col layouts) |
| `customify_sidebar_secondary_class` | Secondary sidebar | `col-3` in three-col layouts |
| `customify_main_content_class` | Main content area | `col-9` (with one sidebar), `col-6` (three-col), `col-12` (no sidebar) |

### 5.2 Class additions per layout

| Layout | Primary sidebar | Secondary sidebar | Main content |
|---|---|---|---|
| `content` | — | — | `col-12` |
| `content-sidebar` | `col-3` (right) | — | `col-9` |
| `sidebar-content` | `col-3` (left) | — | `col-9` |
| `sidebar-content-sidebar` | `col-3` | `col-3` | `col-6` |

Class chain reference: [`inc/element-classes.php`](../inc/element-classes.php), [`inc/template-class.php`](../inc/template-class.php).

### 5.3 Extending the class chain

```php
add_filter( 'customify_main_content_class', function ( $classes ) {
    if ( is_singular( 'portfolio' ) ) {
        $classes[] = 'has-portfolio-grid';
    }
    return $classes;
} );
```

Return the modified array. Don't echo — the template tag handles output.

---

## 6. Template hierarchy

Customify uses standard WordPress template files. Special files:

| File | Used by |
|---|---|
| [`header.php`](../header.php) | Every page — calls `customify_customize_render_header()` |
| [`footer.php`](../footer.php) | Every page — calls `customify_customize_render_footer()` |
| [`woocommerce.php`](../woocommerce.php) | WooCommerce pages — overrides core's `woocommerce_content()` call to integrate with the layout system |
| [`page.php`](../page.php), [`single.php`](../single.php), [`archive.php`](../archive.php), [`search.php`](../search.php), [`404.php`](../404.php), [`index.php`](../index.php) | Standard WP hierarchy |

### 6.1 Header / footer render

```php
add_action( 'customify/site-start', 'customify_customize_render_header' );
add_action( 'customify/site-end',   'customify_customize_render_footer' );
```

`customify_customize_render_header()` / `_footer()` dispatch to the V2 builder. See [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) §5.

### 6.2 Visibility filters

The header and footer can be conditionally hidden:

```php
// Hide site header on all 404s
add_filter( 'customify_is_header_display', function ( $show ) {
    return is_404() ? false : $show;
} );
```

| Filter | When called | Default |
|---|---|---|
| `customify_is_header_display` | Before header render | `true` |
| `customify_is_footer_display` | Before footer render | `true` |
| `customify_is_post_title_display` | Before page/post title render | `true` |
| `customify_is_builder_row_display` | Per-row inside builder | `true` (per-row) |

Per-post override: `_customify_disable_header` post meta (truthy = hide).

---

## 7. Registered sidebars

| ID | Name | Used in |
|---|---|---|
| `sidebar-1` | Primary Sidebar | `sidebar-content`, `content-sidebar`, three-col layouts |
| `sidebar-2` | Secondary Sidebar | `sidebar-content-sidebar` (three-col only) |
| `footer-1` … `footer-6` | Footer Sidebars 1–6 | Footer Widgets builder items |

Registered in `Customify::register_sidebars()` ([`inc/class-customify.php`](../inc/class-customify.php)).

---

## 8. Design decisions

### 8.1 Singleton pattern via global helper

- **Chose**: `Customify()` global function returning `Customify::get_instance()`
- **Rejected**: Direct `Customify::get_instance()` calls everywhere; dependency injection container
- **Reason**: WordPress is procedural — child themes and plugins already expect global helpers. Two competing accessor styles fragment usage; one well-known helper wins.

### 8.2 Hardcoded config loader list

- **Chose**: `load_configs()` requires a hand-maintained file list
- **Rejected**: `glob( inc/customizer/configs/*.php )` auto-discovery
- **Reason**: Load order matters (e.g. `styling.php` defines panel groups consumed by later files). Glob ordering is filesystem-dependent and would make load order unstable across hosts.

### 8.3 Layout decision lives in PHP, not JS

- **Chose**: `customify_get_layout()` runs server-side once per request; result baked into body classes + sidebar markup
- **Rejected**: Client-side layout switching (e.g. JS reading data attrs and toggling classes)
- **Reason**: SEO + accessibility — search engines and screen readers see the final DOM, not a post-render mutation. Also avoids FOUC and reflow.

### 8.4 Element classes via filters, not inheritance

- **Chose**: Each wrapper has its own filter (`customify_main_content_class`, etc.)
- **Rejected**: Single mega-filter that returns all wrapper classes at once
- **Reason**: Per-wrapper filters let plugins/child themes target a single wrapper without inheriting context they don't need. Keeps each filter signature minimal (just `$classes`).

### 8.5 `theme_mod` over `wp_options` by default

- **Chose**: All Customizer settings stored as `theme_mod` (per-theme)
- **Rejected**: All-options storage
- **Reason**: `theme_mod` data is automatically scoped to the active theme — switching themes doesn't pollute the new theme's state. Use `option` only when the value semantically belongs to the site, not the theme.

---

## 9. Hooks & filters catalog

### 9.1 Bootstrap

| Hook | Type | When | File |
|---|---|---|---|
| `customify/init` | action | End of `Customify::init()`, after Customizer init | [`inc/class-customify.php:667`](../inc/class-customify.php) |
| `customify/load-scripts` | action | Before CSS/JS enqueue in `scripts()` | [`inc/class-customify.php:338`](../inc/class-customify.php) |
| `customify/theme/css` | filter | List of CSS files to enqueue | [`inc/class-customify.php:340`](../inc/class-customify.php) |
| `customify/theme/js` | filter | List of JS files to enqueue | [`inc/class-customify.php:348`](../inc/class-customify.php) |
| `customify/theme/scripts` | action | After scripts enqueue | [`inc/class-customify.php:421`](../inc/class-customify.php) |

### 9.2 Layout

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify_get_layout` | filter | `string` | [`inc/template-functions.php:143`](../inc/template-functions.php) |
| `customify_content_width` | filter | `int` (pixels) | [`inc/class-customify.php:128`](../inc/class-customify.php) |
| `customify_is_header_display` | filter | `bool` | [`inc/template-functions.php:318`](../inc/template-functions.php) |
| `customify_is_footer_display` | filter | `bool` | [`inc/template-functions.php:346`](../inc/template-functions.php) |
| `customify_is_post_title_display` | filter | `bool` | [`inc/template-functions.php:391`](../inc/template-functions.php) |
| `customify_is_builder_row_display` | filter | `bool` | [`inc/template-functions.php:374`](../inc/template-functions.php) |
| `customify_builder_row_display_get_post_id` | filter | `int` | [`inc/template-functions.php:365`](../inc/template-functions.php) |

### 9.3 Element classes

| Hook | Wrapper | File |
|---|---|---|
| `body_class` | `<body>` | [`inc/element-classes.php`](../inc/element-classes.php) |
| `customify_site_classes` | Site root classes | [`inc/element-classes.php:81`](../inc/element-classes.php) |
| `customify_site_content_class` | Site content wrapper | [`inc/template-class.php:50`](../inc/template-class.php) |
| `customify_site_content_grid_class` | Grid wrapper | [`inc/template-class.php:230`](../inc/template-class.php) |
| `customify_site_content_container_class` | Container | [`inc/template-class.php:275`](../inc/template-class.php) |
| `customify_sidebar_primary_class` | Primary sidebar | [`inc/template-class.php:95`](../inc/template-class.php) |
| `customify_sidebar_secondary_class` | Secondary sidebar | [`inc/template-class.php:140`](../inc/template-class.php) |
| `customify_main_content_class` | Main content area | [`inc/template-class.php:185`](../inc/template-class.php) |

---

## 10. Storage keys (canonical)

`theme_mod` keys touched by the core layer:

| Key | Stored type | Default | Notes |
|---|---|---|---|
| `sidebar_layout` | string | `content` | Global default layout (one of the 4 values) |
| `blog_sidebar_layout` | string | inherits | Layout for `is_home()` / blog page |
| `single_blog_post_sidebar_layout` | string | inherits | Layout for `is_single()` |
| `archive_sidebar_layout` | string | inherits | Layout for `is_archive()` |
| `page_sidebar_layout` | string | inherits | Layout for `is_page()` |

`post_meta` keys (any post type — applies to standard `post`, `page`, custom post types):

| Meta key | Stored type | Possible values |
|---|---|---|
| `_customify_content_layout` | string | `''` / `'default'` / `'full-width'` / `'full-stretched'` |
| `_customify_sidebar` | string | `''` / `'default'` / `'content'` / `'content-sidebar'` / `'sidebar-content'` / `'sidebar-content-sidebar'` |
| `_customify_disable_header` | bool (`1` / `''`) | Toggle |
| `_customify_disable_{builder_id}` | bool | Per-builder toggle (e.g. `_customify_disable_footer`) |
| `_customify_disable_page_title` | bool | Toggle |

`wp_options` consumed:

| Option | Notes |
|---|---|
| `page_on_front` | Standard WP — static homepage ID |
| `page_for_posts` | Standard WP — blog page ID (used in layout context decisions) |
| `thread_comments` | Standard WP — checked for comment-thread script enqueue |

All keys above are **public API**. Renaming or dropping breaks 30k sites — see [`migration-guide.md`](migration-guide.md).

---

## 11. Troubleshooting

| Symptom | Likely cause |
|---|---|
| New config file's settings don't appear in Customizer | File not added to `load_configs()` array in [`inc/class-customify.php`](../inc/class-customify.php) — auto-discovery is intentionally not used |
| `customify_get_layout` filter callback doesn't fire | Callback registered too late — hook from `init` or earlier, not from inside a template |
| Body class missing `main-layout-*` | `customify_get_layout()` returned an unexpected value; check that custom filter returns one of the 4 valid strings |
| Per-post `_customify_sidebar` ignored | Context doesn't allow per-post override (e.g. `is_archive()`); only singular contexts honour it |
| `Customify()->get_setting()` returns the WRONG default | Calling inside `customize_preview_init` — preview proxy returns the saved value, not the field default; use the field's saved value instead |
| Filter return type mismatch breaks class chain | Filter callback returned a string instead of array — always preserve the array contract |

---

## 12. Where to look next

**PHP**
- [`inc/class-customify.php`](../inc/class-customify.php) — singleton + init orchestration
- [`inc/template-functions.php`](../inc/template-functions.php) — `customify_get_layout()` + display predicates
- [`inc/template-class.php`](../inc/template-class.php) — class-chain template tags
- [`inc/element-classes.php`](../inc/element-classes.php) — `body_class` filter + base additions
- [`inc/class-metabox.php`](../inc/class-metabox.php) — per-post layout metabox

**Templates**
- [`functions.php`](../functions.php) — entry point
- [`header.php`](../header.php), [`footer.php`](../footer.php) — site chrome

**Related specs**
- [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) — build + enqueue
- [`SPEC-customizer.md`](SPEC-customizer.md) — the subsystem this layer brings up
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) — render pipeline that hooks into `customify/site-start` / `customify/site-end`

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) — never-rename rule (template tags + filter names are public API)
