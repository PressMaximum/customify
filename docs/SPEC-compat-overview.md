# SPEC — Compatibility Layer Overview

Canonical reference for Customify's compatibility integrations: WooCommerce, Elementor, Breadcrumb plugins (Yoast SEO, NavXT), and the Customify Pro guard layer. The file structure, integration patterns common across them, and the public hooks each integration exposes.

Related references:
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — Pro plugin contract (separate from generic plugin compat)
- [`SPEC-bootstrap.md`](SPEC-bootstrap.md) §3.5 — how the compat loader fires
- [`SPEC-customizer.md`](SPEC-customizer.md) — Customizer system that compat files extend

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The `inc/compatibility/` folder contains theme code that integrates with non-core plugins. Each integration is self-contained — its file requires nothing from the others. The compat loader fires on `after_setup_theme:2` ([`SPEC-bootstrap.md`](SPEC-bootstrap.md) §3.5).

Three things matter most:

1. **Each compat file guards itself** — `class_exists` / `defined` / `function_exists` checks at the top decide whether to bootstrap. Theme code outside `inc/compatibility/` should NOT assume any plugin is active.
2. **WooCommerce gets the deepest treatment.** It's the only integration with its own config files, its own templates, and a dedicated `woocommerce.php` override in the theme root.
3. **Compat code is shipped, never lazy-loaded.** The files are required at compat-loader time even if the plugin is absent. The class/function guards inside prevent any code from running if the plugin isn't there.

| Integration | File / folder | Plugin |
|---|---|---|
| Customify Pro | [`customify-pro.php`](../inc/compatibility/customify-pro.php) | Customify Pro plugin |
| Elementor | [`elementor.php`](../inc/compatibility/elementor.php) | Elementor + Elementor Pro |
| Breadcrumb | [`breadcrumb.php`](../inc/compatibility/breadcrumb.php) | Yoast SEO + Breadcrumb NavXT |
| WooCommerce | [`woocommerce/`](../inc/compatibility/woocommerce/) | WooCommerce + extensions |

---

## 2. File map

```
inc/compatibility/
├── customify-pro.php           Pro guards + upsell registration
├── elementor.php               Elementor theme location + container width
├── breadcrumb.php              Customify_Breadcrumb class — Yoast / NavXT integration
└── woocommerce/
    ├── woocommerce.php         Top-level WC support, shop page, sidebar, designer
    ├── config/
    │   ├── catalog.php         Catalog Customizer settings
    │   ├── catalog-designer.php  Product designer (custom loop markup)
    │   ├── colors.php          WC-specific color fields
    │   ├── cart.php            Cart page Customizer
    │   ├── single-product.php  Single product page Customizer
    │   └── header/
    │       └── cart.php        Header cart builder item
    └── inc/
        ├── template-hooks.php                       WC template hooks + filters
        └── class-wc-product-cat-list-walker.php    Product category list walker
```

Also relevant:
- [`woocommerce.php`](../woocommerce.php) at theme root — overrides `woocommerce_content()` to integrate with the layout system

---

## 3. Integration pattern (common to all)

Every compat file follows the same shape:

```php
<?php
// 1. Guard — bail if the integration target isn't available
if ( ! defined( 'TARGET_PLUGIN_CONST' ) && ! class_exists( 'Target_Plugin_Class' ) ) {
    return;
}

// 2. Hook into the target plugin's extension points
add_action( 'target_plugin/init', 'customify_integrate_target' );

// 3. Add theme-side filters that depend on the integration
add_filter( 'customify/customizer/config', 'customify_target_add_settings' );

// 4. Provide theme template overrides if the plugin supports them
function customify_integrate_target() {
    // ...
}
```

Some files use a class wrapper (`Customify_Breadcrumb`) instead of bare functions — both styles coexist for historical reasons. The pattern is otherwise identical.

---

## 4. Customify Pro compat

[`inc/compatibility/customify-pro.php`](../inc/compatibility/customify-pro.php).

Different from the other integrations because Pro is **first-party**. The full contract is in [`SPEC-pro-integration.md`](SPEC-pro-integration.md).

This compat file primarily:

- Defines guard helpers used elsewhere
- Hosts hooks/filters that are Pro-aware but live in the theme
- Registers the Customizer upsell section (hidden when Pro is active)

---

## 5. Elementor compat

[`inc/compatibility/elementor.php`](../inc/compatibility/elementor.php).

### 5.1 Activation

```php
if ( defined( 'ELEMENTOR_VERSION' ) ) {
    // bootstrap
}
```

The integration only activates when Elementor is loaded.

### 5.2 What it does

1. **Registers theme locations** via Elementor Pro's Theme Builder (`elementor/theme/register_locations`). Lets users override header / footer / single / archive via Elementor templates.
2. **Sets default container width** to `1184px` via `default_option_elementor_container_width` / `option_elementor_container_width` filters. Aligns Elementor's content width with Customify's `container_width` default when the user hasn't customized.
3. **FA4 shim coordination** — sets `elementor_load_fa4_shim` option conditionally based on `customify_fa_ver` (Customify's Font Awesome version choice).

### 5.3 Public helper

```php
customify_is_e_theme_location( $location )  // bool
```

Returns true if Elementor Pro's Theme Builder has a template assigned to `$location` (e.g. `'header'`, `'footer'`). Theme code uses this to skip rendering its own header/footer when Elementor takes over.

### 5.4 Storage interaction

| Option | Read | Written |
|---|---|---|
| `elementor_container_width` | Elementor | Theme (default value filter) |
| `elementor_load_fa4_shim` | Elementor | Theme (conditional, on FA version change) |

---

## 6. Breadcrumb compat

[`inc/compatibility/breadcrumb.php`](../inc/compatibility/breadcrumb.php).

### 6.1 `Customify_Breadcrumb` class

Singleton accessed via `Customify_Breadcrumb::get_instance()`. Bootstraps on first call.

### 6.2 What it does

1. **Registers Customizer config** via `customify/customizer/config` filter (breadcrumb position, separator, exclusions).
2. **Hooks into Yoast SEO** — `wpseo_breadcrumb_separator` returned `null` (Customify draws its own separator); `wpseo_breadcrumb_single_link` wraps each link in `<li>`.
3. **Renders on `wp_head`** — `display()` method emits the breadcrumb at the configured position (before/after header, in title bar, etc.).
4. **Honours per-post `_customify_breadcrumb_display` meta** — bool toggle to hide breadcrumb on specific posts.

### 6.3 Supported plugins

| Plugin | Detection | Integration |
|---|---|---|
| Yoast SEO | `function_exists( 'yoast_breadcrumb' )` | Wraps output, suppresses default separator |
| Breadcrumb NavXT | `function_exists( 'bcn_display' )` | Calls `bcn_display()` for output |
| (built-in fallback) | always | Customify's own breadcrumb generator if no plugin |

### 6.4 Position interaction with Pro transparent header

When `Customify_Pro_Module_Header_Transparent` is active, the breadcrumb position adjusts to compensate for the transparent header overlay. See [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §8.

### 6.5 Public hook

| Hook | Type | Purpose |
|---|---|---|
| `customify/breadcrumb/is-showing` | filter | Boolean — show breadcrumbs on this request? |

```php
// Hide breadcrumbs on all archive pages
add_filter( 'customify/breadcrumb/is-showing', function ( $show ) {
    return is_archive() ? false : $show;
} );
```

---

## 7. WooCommerce compat

The deepest integration. Lives in [`inc/compatibility/woocommerce/`](../inc/compatibility/woocommerce/) with multiple sub-folders.

### 7.1 Activation

```php
if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}
```

### 7.2 What it does

1. **`woocommerce.php` (theme root)** — overrides WC's default content rendering to integrate with Customify's layout system. Reads `customify_get_layout()` and outputs the same sidebar / content arrangement as non-WC pages.
2. **WC support declarations** — `add_theme_support( 'woocommerce' )` + gallery features (`wc-product-gallery-zoom`, `wc-product-gallery-lightbox`, `wc-product-gallery-slider`).
3. **Shop page integration** — honours `_customify_wc_show_page_title` post meta (per-shop toggle for page title display).
4. **Product loop integration** — registers the **Catalog Designer** (custom product loop markup, see §7.3).
5. **Customizer settings** — registers WC-specific sections via `customify/customizer/config` filter (catalog layout, single product, cart, header cart).
6. **Header cart item** — registers `Customify_Builder_Item_WC_Cart` for the header builder.
7. **Template hooks** — `inc/template-hooks.php` re-orders / removes WC actions to match Customify's layout.

### 7.3 Catalog Designer

The Catalog Designer ([`config/catalog-designer.php`](../inc/compatibility/woocommerce/config/catalog-designer.php)) is a custom product loop markup generator. Users can reorder, hide, and customize the product card elements (image, title, price, rating, button, etc.) without editing template files.

It exposes the most filters of any compat file — see §7.5 for the catalog.

### 7.4 Storage interaction

| Storage | Key | Used for |
|---|---|---|
| `theme_mod` | `woocommerce_catalog_tablet_columns` (and similar) | WC catalog grid columns |
| `post_meta` | `_customify_wc_show_page_title` | Per-shop-page title toggle |

### 7.5 Public hooks

| Hook | Type | Purpose |
|---|---|---|
| `customify_qty_add_plus_minus` | filter | `int (0\|1)` — enable WC quantity +/- buttons |
| `customify_is_shop_title_display` | filter | `bool` — show WC shop page title |
| `customify_get_default_catalog_view_mod` | filter | `string` (`grid`\|`list`) — default WC catalog view |
| `customify/product-designer/part` | filter | `callable\|false` — custom render callback for product designer item |
| `customify/product-designer/render_html` | filter | `string` — rendered product HTML |
| `customify/product-designer/body-items` | filter | `array` — product body items config |
| `customify_wc_catalog_designer/configs` | filter | `array` — WC product designer configs |
| `customify/before_render_woocommerce_product` | action | Before product loop item render |
| `customify/after_render_woocommerce_product` | action | After product loop item render |
| `customify/wc-product/before-media` | action | Before product media |
| `customify/wc-product/after-media` | action | After product media |
| `customify_after_loop_product_media` | action | After product loop media |

Full signatures + file:line: [`api-reference.md`](api-reference.md) §4.4 + §6.

---

## 8. Design decisions

### 8.1 Compat files require self-contained guards

- **Chose**: Each `inc/compatibility/*.php` checks `class_exists` / `defined` at the top
- **Rejected**: Conditional `require_once` in the compat loader
- **Reason**: Lazy detection — the file is loaded unconditionally, but does nothing if the target plugin isn't there. Cheaper than checking plugin existence in the loader (which would need every compat target hardcoded in `class-customify.php`). Also lets child themes drop in their own compat files without touching the loader.

### 8.2 WooCommerce gets its own subfolder

- **Chose**: `inc/compatibility/woocommerce/` with `config/` + `inc/` subfolders
- **Rejected**: Flat `inc/compatibility/woocommerce-*.php` files
- **Reason**: WC integration is order of magnitude larger than the others (10+ files vs 1 file). Subfolder isolates the complexity and mirrors WC's own template organization.

### 8.3 `Customify_Breadcrumb` as a class, others as bare functions

- **Chose**: Breadcrumb wraps state in a singleton; Elementor / WC compat use bare functions
- **Rejected**: One consistent style for all
- **Reason**: Breadcrumb genuinely needs cached state (settings + transparent-header check across multiple methods). Bare functions for the others avoid unnecessary boilerplate. Style consistency is less valuable than fit-for-purpose code.

### 8.4 WC `woocommerce.php` in theme root

- **Chose**: Top-level [`woocommerce.php`](../woocommerce.php) overrides WC's default `woocommerce_content()` call
- **Rejected**: Hook into WC templates from compat folder
- **Reason**: WC checks for a top-level `woocommerce.php` first in the template hierarchy — using that path is the cleanest override, and it's WC-idiomatic. Hooking via filters would require duplicating WC's own template-loading logic.

### 8.5 No lazy-load for compat files

- **Chose**: All compat files required unconditionally on `after_setup_theme:2`
- **Rejected**: Plugin-detection conditional requires
- **Reason**: PHP opcache means the cost of requiring a file with a top-of-file guard is negligible (the file compiles once, then runs the guard check on every request — which is also opcached). Plugin detection would require `is_plugin_active()` which loads `wp-admin/includes/plugin.php` — heavier than the file load.

---

## 9. Adding a new compat integration

```php
// inc/compatibility/my-plugin.php
<?php
if ( ! defined( 'MY_PLUGIN_VERSION' ) && ! class_exists( 'My_Plugin' ) ) {
    return;
}

add_action( 'customify/customizer/config', 'customify_my_plugin_add_settings' );
add_filter( 'my_plugin_some_filter', 'customify_my_plugin_adjust' );

function customify_my_plugin_add_settings( $items ) {
    // Register a Customizer section / fields for this integration
    return $items;
}

function customify_my_plugin_adjust( $value ) {
    // Adjust the plugin's behavior to fit Customify
    return $value;
}
```

Then register the file in `Customify::load_compatibility()` ([`inc/class-customify.php`](../inc/class-customify.php)):

```php
require_once get_template_directory() . '/inc/compatibility/my-plugin.php';
```

The list is hardcoded — auto-discovery is intentionally not used (load order matters; some compats depend on Customizer config being registered first).

---

## 10. Known issues / edge cases

### Issue #1 — WC `woocommerce.php` overrides shop list template

The theme-root [`woocommerce.php`](../woocommerce.php) takes precedence over WC's default `archive-product.php` etc. Child themes that want to override individual WC templates must drop them in `woocommerce/` subfolder in the child theme — overriding `woocommerce.php` itself in the child = the child takes over the entire WC integration.

### Issue #2 — Elementor template location detection requires Elementor Pro

`customify_is_e_theme_location()` calls `elementor_theme_do_location()`, which only exists in Elementor PRO. On Elementor Free, the function returns `false` always — the theme always renders its own header/footer.

### Issue #3 — Yoast breadcrumb separator suppression

Setting `wpseo_breadcrumb_separator` to `null` silences Yoast's default. If a user has CSS that depended on the separator span, removing the theme's Customify_Breadcrumb instance won't restore it — they have to also unhook the filter manually.

### Issue #4 — Breadcrumb cache interaction

The breadcrumb display position is read from `Customify()->get_setting( 'breadcrumb_display_pos' )` on every page load — no per-request cache. For sites with heavy breadcrumb usage (e.g. WC catalog pages with deep category trees), this is one DB-backed `get_theme_mod` call per page; cheap, but cumulative if the breadcrumb is re-rendered multiple times. Don't call `display()` more than once per page.

### Issue #5 — Customify_Breadcrumb singleton bootstraps on `get_instance()` call

If no code ever calls `Customify_Breadcrumb::get_instance()`, the breadcrumb never renders. The current bootstrap calls it from `inc/compatibility/breadcrumb.php` top-level, but moving that call out of top-level scope would silently disable breadcrumbs.

---

## 11. Quick reference

| I want to… | How |
|---|---|
| Add Customizer settings for a plugin | Hook `customify/customizer/config` from the compat file |
| Detect Elementor template covers the current location | `customify_is_e_theme_location( 'header' )` |
| Disable breadcrumbs conditionally | `add_filter( 'customify/breadcrumb/is-showing', '__return_false' )` (early registration) |
| Customize WC product loop markup | Hook `customify/product-designer/*` filters — see §7.5 |
| Add a new header/footer builder item that's WC-aware | Register via `Customify_Customize_Layout_Builder()->register_item()`, guard with `class_exists( 'WooCommerce' )` |

---

## 12. Where to look next

**Compat files**
- [`inc/compatibility/customify-pro.php`](../inc/compatibility/customify-pro.php)
- [`inc/compatibility/elementor.php`](../inc/compatibility/elementor.php)
- [`inc/compatibility/breadcrumb.php`](../inc/compatibility/breadcrumb.php)
- [`inc/compatibility/woocommerce/woocommerce.php`](../inc/compatibility/woocommerce/woocommerce.php)
- [`inc/compatibility/woocommerce/config/`](../inc/compatibility/woocommerce/config/) — WC Customizer configs
- [`inc/compatibility/woocommerce/inc/template-hooks.php`](../inc/compatibility/woocommerce/inc/template-hooks.php) — WC template hook re-ordering

**Theme root**
- [`woocommerce.php`](../woocommerce.php) — WC content override

**Related specs**
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — first-party Pro integration contract
- [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §8 — breadcrumb position interaction with Pro
- [`SPEC-bootstrap.md`](SPEC-bootstrap.md) §3.5 — compat loader

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.10 — verify third-party HTML selectors against source
