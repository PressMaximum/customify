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

### CSS Build Pipeline

```
assets/sass/ → grunt sass → style.css + assets/css/admin/editor.css
                → grunt cssmin → style.min.css + *.min.css
```

**Always run `grunt sass && grunt cssmin` after editing any `.scss` file.**

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

### Never edit compiled CSS directly

All CSS is generated from SASS source files. Edit `.scss` files, then run `grunt sass && grunt cssmin`. The `style.css` and `assets/css/admin/editor.css` files are build artifacts.

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
