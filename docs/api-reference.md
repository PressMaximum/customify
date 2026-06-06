# Customify Theme — API Reference

Catalog of public filters, actions, and template tags exposed by the theme. Use this file to look up signatures — not to learn how a subsystem works (read the matching `SPEC-*.md` for that).

For storage keys (`theme_mod` / `wp_options` / `post_meta`) see [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md).

---

## 1. Layout & element classes

| Hook | Type | Returns | File |
|---|---|---|---|
| `customify_get_layout` | filter | `string\|null` (`content` / `content-sidebar` / `sidebar-content` / `sidebar-content-sidebar`) | [`inc/template-functions.php:143`](../inc/template-functions.php) |
| `customify_content_width` | filter | `int` (pixels), default `843` | [`inc/class-customify.php:128`](../inc/class-customify.php) |
| `customify_site_classes` | filter | `array` — site element classes | [`inc/element-classes.php:81`](../inc/element-classes.php) |
| `customify_site_content_class` | filter | `array` — site content wrapper classes | [`inc/template-class.php:50`](../inc/template-class.php) |
| `customify_site_content_grid_class` | filter | `array` — site grid wrapper classes | [`inc/template-class.php:230`](../inc/template-class.php) |
| `customify_site_content_container_class` | filter | `array` — site container classes | [`inc/template-class.php:275`](../inc/template-class.php) |
| `customify_sidebar_primary_class` | filter | `array` — primary sidebar classes | [`inc/template-class.php:95`](../inc/template-class.php) |
| `customify_sidebar_secondary_class` | filter | `array` — secondary sidebar classes | [`inc/template-class.php:140`](../inc/template-class.php) |
| `customify_main_content_class` | filter | `array` — main content area classes | [`inc/template-class.php:185`](../inc/template-class.php) |
| `customify_is_header_display` | filter | `bool` — render site `<header>`? | [`inc/template-functions.php:318`](../inc/template-functions.php) |
| `customify_is_footer_display` | filter | `bool` — render site `<footer>`? | [`inc/template-functions.php:346`](../inc/template-functions.php) |
| `customify_is_post_title_display` | filter | `bool` — render the page/post title? | [`inc/template-functions.php:391`](../inc/template-functions.php) |
| `customify_is_builder_row_display` | filter | `bool` — render builder row? (`$builder_id`, `$row_id`, `$post_id` passed) | [`inc/template-functions.php:374`](../inc/template-functions.php) |
| `customify_builder_row_display_get_post_id` | filter | `int` — post ID used for builder row display logic | [`inc/template-functions.php:365`](../inc/template-functions.php) |

Example — override layout for a specific page:

```php
add_filter( 'customify_get_layout', function ( $layout ) {
    if ( is_page( 123 ) ) {
        return 'content';
    }
    return $layout;
} );
```

---

## 2. Customizer & styling

### 2.1 Registration & config

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/customizer/config` | filter | `array $items` — append your panels/sections/settings | [`inc/customizer/class-customizer.php:166`](../inc/customizer/class-customizer.php) |
| `customify/customize/settings-default` | filter | `(mixed $default, string $name)` | [`inc/customizer/class-customizer.php:285`](../inc/customizer/class-customizer.php) |
| `customify/customize/register-controls` | filter | `array $controls` — control configs before registration | [`inc/customizer/class-customizer.php:946`](../inc/customizer/class-customizer.php) |
| `customify/customize/register_completed` | action | `(Customify_Customizer $customizer)` — fires after all registration | [`inc/customizer/class-customizer.php:1195`](../inc/customizer/class-customizer.php) |
| `customify/customizer/panel_groups` | filter | `array` — panel grouping for UI organization | [`inc/customizer/class-customizer.php:1261`](../inc/customizer/class-customizer.php) |
| `customify/get_styling_config` | filter | `array` — styling field config | [`inc/customizer/class-customizer.php:791`](../inc/customizer/class-customizer.php) |

### 2.2 Auto-CSS pipeline

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/customizer/auto_css` | filter | `(array $css_lines, $field, Customify_Customizer_Auto_CSS $instance)` | [`inc/customizer/class-customizer-auto-css.php:790`](../inc/customizer/class-customizer-auto-css.php) |
| `customify/auto-css` | filter | `string $css` — final assembled CSS before `wp_add_inline_style` | [`inc/customizer/class-customizer-auto-css.php:1261`](../inc/customizer/class-customizer-auto-css.php) |
| `customify/styling/<field>` | filter | `string $css` — append CSS template to a specific field (e.g. `customify/styling/primary-color`) | dynamic — emitted per-field by auto-CSS |
| `customify/typography/legacy_output` | filter | `bool` (default `false`) — return `true` to revert typography emit to pre-`0.5.0` selector-scoped CSS instead of `:root { --customify-typo-*: … }` vars. Resolved value is also localized to `Customify_Preview_Config.legacy_typography_output` for the JS live preview. | [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) `legacy_typography_enabled()` |

Example — extend the primary color CSS targets without forking the config file:

```php
add_filter( 'customify/styling/primary-color', function ( $css ) {
    return $css . '.my-element { color: {{value}}; }';
} );
```

Example — re-enable legacy typography output (selector-scoped CSS) site-wide; useful when child-theme overrides depend on the pre-`0.5.0` cascade. See [`SPEC-typography.md`](SPEC-typography.md) for full details:

```php
add_filter( 'customify/typography/legacy_output', '__return_true' );
```

### 2.3 Fonts & icons

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/customizer/font_icons` | filter | `array` — icon font list (font_awesome, etc.) | [`inc/customizer/class-customizer-icons.php:64`](../inc/customizer/class-customizer-icons.php) |
| `customify/load-icons` | filter | `bool` — enqueue icon fonts? | [`inc/customizer/class-customizer-icons.php:75`](../inc/customizer/class-customizer-icons.php) |
| `customify/icon_used` | filter | `array` — used icon slugs (for subset loading) | [`inc/customizer/class-customizer-icons.php:80`](../inc/customizer/class-customizer-icons.php) |
| `customify/customizer/font_icons/font_awesome_icons` | filter | `array` — Font Awesome catalog + metadata | [`inc/customizer/class-customizer-icons.php:943`](../inc/customizer/class-customizer-icons.php) |

### 2.4 Control internals

| Hook | Type | Payload | File |
|---|---|---|---|
| `Customify_Control_Args` | filter | `array` — control JS args | [`inc/customizer/controls/class-control-base.php:309`](../inc/customizer/controls/class-control-base.php) |
| `Customify_Control_Color_Picker_L10n_Args` | filter | `array` — color picker i18n strings | [`inc/customizer/controls/class-control-base.php:319`](../inc/customizer/controls/class-control-base.php) |

---

## 3. Bootstrap & enqueue

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/init` | action | — — fires at the end of `Customify::init()`, after customizer init | [`inc/class-customify.php:667`](../inc/class-customify.php) |
| `customify/load-scripts` | action | — — fires before CSS/JS enqueue | [`inc/class-customify.php:338`](../inc/class-customify.php) |
| `customify/theme/css` | filter | `array` — CSS files to enqueue (id => url \| array) | [`inc/class-customify.php:340`](../inc/class-customify.php) |
| `customify/theme/js` | filter | `array` — JS files to enqueue (id => url \| array) | [`inc/class-customify.php:348`](../inc/class-customify.php) |
| `customify/theme/scripts` | action | — — fires after scripts enqueue | [`inc/class-customify.php:421`](../inc/class-customify.php) |

---

## 4. Header / Footer Builder

### 4.1 Registration

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/customize-builder/init` | action | — — register additional builders before Header/Footer load | [`inc/panel-builder/class-layout-builder.php:18`](../inc/panel-builder/class-layout-builder.php) |
| `customify/builder/<builder_id>/rows` | filter | `array $rows` — row id => label (e.g. `top`, `main`, `bottom`, `sidebar`) | [`inc/panel-builder/class-builder-panel.php:71`](../inc/panel-builder/class-builder-panel.php) |
| `customify/builder/<builder_id>/items` | filter | `array $items` — registered item config (item_id => config) | [`inc/panel-builder/class-layout-builder.php:127`](../inc/panel-builder/class-layout-builder.php) |
| `customify/builder/<builder_id>/section_configs` | filter | `array` — row Customizer section config | [`inc/customizer/configs/footer/panel.php:240`](../inc/customizer/configs/footer/panel.php) |
| `customify/customize-menu-config-more` | filter | `array $config` — extends menu builder Customizer config | [`inc/customizer/configs/header/menus.php:215`](../inc/customizer/configs/header/menus.php) |

### 4.2 Render

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/before-header` | action | — — output before site `<header>` | [`inc/panel-builder/builder-functions.php`](../inc/panel-builder/builder-functions.php) |
| `customify/after-header` | action | — — output after site `</header>` | same |
| `customify/before-footer` | action | — — output before site `<footer>` | same |
| `customify/after-footer` | action | — — output after site `</footer>` | same |
| `customify/builder/row-classes` | filter | `(array $classes, string $row_id, $builder)` | [`inc/panel-builder/class-layout-builder-frontend-v2.php`](../inc/panel-builder/class-layout-builder-frontend-v2.php) |
| `customify/builder/row-attrs` | filter | `(array $attrs, string $row_id, string $builder_id)` — custom HTML attrs | same |
| `customify/builder/inner-row-classes` | filter | `(array $classes, string $row_id, string $builder_id)` | same |
| `customify/builder/<builder>/before-item/<item>` | action | — — inject before item render | same |
| `customify/builder/<builder>/after-item/<item>` | action | — — inject after item render | same |
| `customify/logo-classes` | filter | `array $classes` — `<img>` wrapper classes around the logo | [`inc/customizer/configs/header/logo.php`](../inc/customizer/configs/header/logo.php) |
| `customizer/after-logo-img` | action | — — fires after the standard logo `<img>` (used by transparent-logo) | same |

### 4.3 Transparent header

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/render_header/is-transparent` | filter | `bool $is_tran` — final override before result is cached | [`inc/customizer/configs/header/transparent.php`](../inc/customizer/configs/header/transparent.php) |

Note: the result is cached on first call. Register the filter as early as possible (`after_setup_theme`, `init`) — late hooks won't run.

### 4.4 WooCommerce-specific items

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/product-designer/part` | filter | `(callable\|false $callback, $item_id, $designer)` — custom render callback | [`inc/compatibility/woocommerce/config/catalog-designer.php:25`](../inc/compatibility/woocommerce/config/catalog-designer.php) |
| `customify/product-designer/render_html` | filter | `(string $html, array $items, $designer)` — rendered product HTML | same `:69` |
| `customify/product-designer/body-items` | filter | `array` — product body items config | same `:202` |
| `customify_wc_catalog_designer/configs` | filter | `array` — WC product designer configs | same `:48` |
| `customify/before_render_woocommerce_product` | action | — | same `:41` |
| `customify/after_render_woocommerce_product` | action | — | same `:130` |
| `customify/wc-product/before-media` | action | — | same `:438` |
| `customify/wc-product/after-media` | action | — | same |
| `customify_after_loop_product_media` | action | — | same `:442` |

---

## 5. Blog & post display

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify_is_post_title_display` | filter | `bool` | [`inc/template-functions.php:391`](../inc/template-functions.php) |
| `customify_the_title` | filter | `string $title` — page cover title | [`inc/customizer/configs/page-header.php:955`](../inc/customizer/configs/page-header.php) |
| `customify_the_content` | filter | `string $content` — copyright / footer content | [`inc/customizer/configs/footer/copyright.php:90`](../inc/customizer/configs/footer/copyright.php) |
| `customify/titlebar/config` | filter | `array` — titlebar Customizer config | [`inc/customizer/configs/page-header.php:384`](../inc/customizer/configs/page-header.php) |
| `customify/cover/config` | filter | `array` — page cover Customizer config | [`inc/customizer/configs/page-header.php:595`](../inc/customizer/configs/page-header.php) |
| `customify/page-header/get-settings` | filter | `array` — page header runtime settings | [`inc/customizer/configs/page-header.php:906`](../inc/customizer/configs/page-header.php) |
| `customify/page-cover/before` | action | — | [`inc/customizer/configs/page-header.php:950`](../inc/customizer/configs/page-header.php) |
| `customify/page-cover/after` | action | — | same `:965` |

---

## 6. Compatibility & breadcrumb

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/breadcrumb/is-showing` | filter | `bool` — render breadcrumbs? | [`inc/compatibility/breadcrumb.php:330`](../inc/compatibility/breadcrumb.php) |
| `customify_qty_add_plus_minus` | filter | `int (0\|1)` — enable WC quantity +/- buttons | [`inc/compatibility/woocommerce/woocommerce.php:151`](../inc/compatibility/woocommerce/woocommerce.php) |
| `customify_is_shop_title_display` | filter | `bool` — show WC shop page title | [`inc/compatibility/woocommerce/woocommerce.php:534`](../inc/compatibility/woocommerce/woocommerce.php) |
| `customify_get_default_catalog_view_mod` | filter | `string` (`grid`\|`list`) — default WC catalog view | [`inc/compatibility/woocommerce/woocommerce.php:791`](../inc/compatibility/woocommerce/woocommerce.php) |

---

## 7. Metabox & admin

| Hook | Type | Payload | File |
|---|---|---|---|
| `customify/metabox/init` | action | `(Customify_Metabox $instance)` | [`inc/class-metabox.php:30`](../inc/class-metabox.php) |
| `customify_render_field_cb` | filter | `(callable\|false $callback, $field)` — custom metabox field render | [`inc/class-metabox-fields.php:210`](../inc/class-metabox-fields.php) |
| `customify_dashboard_v2_schema` | filter | `SchemaBuilder $schema` — extend dashboard v2 settings schema | [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) |
| `customify_dashboard_pro_active` | filter | `bool` — Pro detection flag for dashboard | [`inc/admin/dashboard-v2.php`](../inc/admin/dashboard-v2.php) |
| `customify_dashboard_localize` | filter | `(array $boot, string $context)` — mutate dashboard boot payload | same |

---

## 8. Template tags

Template tags are PHP functions you call from theme template files. Most live in [`inc/template-tags.php`](../inc/template-tags.php), [`inc/template-functions.php`](../inc/template-functions.php), and [`inc/template-class.php`](../inc/template-class.php).

| Function | Purpose |
|---|---|
| `customify_get_layout()` | Returns the current layout slug |
| `customify_get_layout()` | Resolves to one of the 4 layout values |
| `Customify()->get_setting( $key, $device = null )` | Read a Customizer setting (device-aware) |
| `Customify()->customizer->get_field_setting( $name )` | Read the config definition for a field |
| `Customify()->is_woocommerce_active()` | Boolean — WC plugin active |
| `Customify()->is_using_post()` | Boolean — current request is a singular post-like context |
| `Customify()->get_current_post_id()` | Best-guess current post ID across is_singular / blog page / etc. |
| `Customify()->get_media( $value, $size )` | Resolve an `image` control value to a URL |
| `customify_customize_render_header()` | Render the active header via the V2 builder |
| `customify_customize_render_footer()` | Render the active footer via the V2 builder |
| `customify_site_content_class( $context )` | Echo the site content wrapper class chain |
| `customify_main_content_class( $context )` | Echo the main content area class chain |
| `customify_sidebar_primary_class( $context )` | Echo the primary sidebar class chain |
| `customify_sidebar_secondary_class( $context )` | Echo the secondary sidebar class chain |
| `customify_site_content_grid_class()` | Echo the site grid wrapper class chain |
| `customify_site_content_container_class()` | Echo the site container class chain |

---

## 9. Stability promise

All hooks and template tags above are **public API** under the 30k-sites rule:

1. Never rename a hook/function — add a new name alongside; old becomes a `@deprecated` wrapper
2. Never delete — mark `@deprecated`, keep the body
3. Never change a signature — add a new function with a distinct name

See [`../AGENTS.md`](../AGENTS.md) §4.2 for the deprecation discipline.
