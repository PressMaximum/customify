# SPEC — Block Editor Integration

Canonical reference for how Customify integrates with the WordPress block editor (Gutenberg). Three layers: `theme.json` declarations, `Customify_Editor` Customizer → editor CSS bridge, registered block styles + patterns.

Related references:
- [`SPEC-customizer.md`](SPEC-customizer.md) §12 — Customizer side of the editor bridge
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §3.7 — color palette ↔ `theme.json` sync
- [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) — how editor CSS is enqueued

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

Customify is a **classic theme** (not FSE). Its block editor integration is targeted: it doesn't ship templates or template parts, but it does sync Customizer values into the editor iframe so authors see what the frontend will render.

Three things matter most:

1. **`theme.json` declares the static palette + sizes** — block editor reads it at boot. Changing values here doesn't reach existing posts with hardcoded class names.
2. **`Customify_Editor::css()` syncs a curated subset of Customizer values into the editor iframe.** Adding a key here makes its CSS visible while editing — but the field still has to render frontend-side via auto-CSS.
3. **Block styles + patterns are registered statically.** Block styles via `register_block_style()` in [`inc/admin/block-styles.php`](../inc/admin/block-styles.php), patterns auto-discovered from `patterns/*.php` by WordPress 6.0+.

| Surface | Role |
|---|---|
| [`theme.json`](../theme.json) | Static palette, contentSize, wideSize, typography presets |
| [`inc/admin/editor.php`](../inc/admin/editor.php) | `Customify_Editor` — pipes Customizer-generated CSS into the block editor |
| [`inc/admin/block-styles.php`](../inc/admin/block-styles.php) | `register_block_style()` calls — Ghost button, Rounded image, etc. |
| [`patterns/*.php`](../patterns/) | Block patterns — auto-registered by WP 6.0+ |
| `src/backend/admin/editor/` | Admin JS for the editor screen |
| `src/frontend/scss/base/_blocks.scss` | Frontend CSS for blocks (also injected into editor) |

---

## 2. File map

| File | Responsibility |
|---|---|
| [`theme.json`](../theme.json) | Static declarations — palette, sizes, typography |
| [`inc/admin/editor.php`](../inc/admin/editor.php) | `Customify_Editor` class — hooks `enqueue_block_editor_assets`, generates editor-scoped CSS |
| [`inc/admin/block-styles.php`](../inc/admin/block-styles.php) | `customify_register_block_styles()` — 7 block style variations |
| [`patterns/cta-banner.php`](../patterns/cta-banner.php) | CTA banner pattern |
| [`patterns/features-three-columns.php`](../patterns/features-three-columns.php) | Three-column features pattern |
| [`patterns/hero-centered.php`](../patterns/hero-centered.php) | Centered hero pattern |
| [`patterns/media-text-left.php`](../patterns/media-text-left.php) | Media + text pattern (image left) |
| [`patterns/testimonial.php`](../patterns/testimonial.php) | Testimonial pattern |
| `src/backend/admin/editor/` | Editor admin JS bundle source |
| `src/frontend/scss/base/_blocks.scss` | Block-related frontend CSS (also reused in editor) |

---

## 3. `theme.json` declarations

[`theme.json`](../theme.json) declares the values the block editor reads at boot. These are STATIC — changing them doesn't propagate to posts that already have hardcoded class references.

### 3.1 Layout sizes

```json
{
  "settings": {
    "layout": {
      "contentSize": "780px",
      "wideSize":    "1200px"
    }
  }
}
```

| Property | Used by |
|---|---|
| `contentSize` | Default block width inside `.entry-content` |
| `wideSize` | `.alignwide` max-width — must sync with the Customizer `container_width` setting via the `css_format` in [`inc/customizer/configs/layouts.php`](../inc/customizer/configs/layouts.php) |

When `container_width` changes in the Customizer, its `css_format` must also update `--wp--style--global--wide-size` so theme.json, Customizer, and the editor stay aligned. See [`../AGENTS.md`](../AGENTS.md) §4.11.

### 3.2 Color palette

`settings.color.palette` declares 8 + 3 = 11 entries:

| Slug | Default | Source |
|---|---|---|
| `primary` | `#235787` | Original 8 |
| `secondary` | `#c3512f` | Original 8 |
| `text` | `#2b2b2b` | Original 8 |
| `link` | `#1e4b75` | Original 8 |
| `heading` | `#2b2b2b` | Original 8 |
| `background` | `#FFFFFF` | Original 8 |
| `light-gray` | (gray) | Original 8 |
| `dark-gray` | (gray) | Original 8 |
| `base` | `#FFFFFF` | Added Phase 1 (colors-improve) |
| `surface` | `#ECECEC` | Added Phase 2.4 |
| `accent` | `#FFD042` | Added Phase 1 |

Posts use the slugs via `.has-<slug>-color` class names. **Renaming a slug breaks posts that reference it** — slugs are public API. See [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §3.3.

Trade-off documented in colors SPEC: when a user changes a slot value in the Customizer, `:root --customify-<slot>` updates but `--wp--preset--color--<slug>` stays at the static default. Block editor color pickers always show the static palette.

### 3.3 Typography presets

Defined in `settings.typography.fontSizes`. Synced with the Customizer typography fields where applicable.

---

## 4. `Customify_Editor` — Customizer → editor bridge

[`inc/admin/editor.php`](../inc/admin/editor.php) defines `Customify_Editor`, a singleton that:

1. Generates editor-scoped CSS from a curated subset of Customizer values
2. Attaches the CSS to the `wp-edit-blocks` style handle
3. Sets `:root` custom properties (`--wp--style--global--wide-size`, etc.) so block layout matches the frontend

### 4.1 Hooks registered

| Hook | Method | Purpose |
|---|---|---|
| `block_editor_settings_all` (WP 5.8+) or `block_editor_settings` | `editor_settings()` | Inject editor settings (colors, font sizes, etc.) |
| `enqueue_block_editor_assets` (priority 10) | `assets()` | Enqueue editor JS + CSS |
| `wp_ajax_customify_load_editor_style` | `css_file()` | Stream editor CSS file via AJAX (alternative load path) |

### 4.2 The `$keys` array — synced Customizer values

`Customify_Editor::css()` walks a curated list of Customizer field names and generates editor-scoped CSS for each:

```php
$keys = array(
    'container_width',
    'site_content_styling',
    'content_background',
    'single_blog_post_content_width',
    'global_typography_heading_h1',
    'global_typography_base_heading',
    'global_styling_color_heading',
);
```

For each key:

1. Read the field definition via `Customify()->customizer->get_field_setting( $key )`
2. Rewrite the `selector` to target editor markup (`.editor-styles-wrapper .wp-block-post-title`, etc.)
3. Run through `Customify_Customizer_Auto_CSS::render_css()` to produce editor-scoped CSS
4. Attach via `wp_add_inline_style( 'wp-edit-blocks', $css )`
5. Mirror numeric values into CSS custom properties on `:root` (e.g. `--wp--style--global--content-size`)

### 4.3 Adding a new synced key

```php
// In inc/admin/editor.php::css(), append to $keys array
$keys[] = 'my_new_color';
```

If the editor needs a different `selector` than the frontend, add a `case` branch right after the field lookup (the file already has examples like `single_blog_post_content_width` which only applies on post-type `post`).

### 4.4 Post meta read by the editor

| Meta key | Used for |
|---|---|
| `_customify_content_layout` | Decide whether to widen contentSize for full-width posts |
| `_customify_sidebar` | Decide whether to constrain contentSize for sidebar layouts |
| `_customify_disable_page_title` | Hide post title in editor when frontend hides it |

Read in `Customify_Editor::css()` and used to adjust the editor's `--wp--style--global--content-size` per-post.

---

## 5. Registered block styles

[`inc/admin/block-styles.php`](../inc/admin/block-styles.php) registers 7 block style variations on `init`:

| Block | Style name | Label | CSS class | Purpose |
|---|---|---|---|---|
| `core/button` | `ghost` | Ghost | `.wp-block-button.is-style-ghost` | Outline button, no fill |
| `core/image` | `rounded` | Rounded | `.wp-block-image.is-style-rounded` | Rounded corners |
| `core/image` | `shadow` | Shadow | `.wp-block-image.is-style-shadow` | Box shadow |
| `core/quote` | `accent` | Accent Border | `.wp-block-quote.is-style-accent` | Left-border accent |
| `core/separator` | `thick` | Thick | `.wp-block-separator.is-style-thick` | Thick variant |
| `core/group` | `card` | Card | `.wp-block-group.is-style-card` | White bg + border + padding |
| `core/columns` | `no-gap` | No Gap | `.wp-block-columns.is-style-no-gap` | Zero column gap |

CSS for each lives in `src/frontend/scss/base/_blocks.scss` and is injected into the editor via `Customify_Editor::css()` so authors see the variation while editing.

### 5.1 Adding a new block style

1. **Register** in [`inc/admin/block-styles.php`](../inc/admin/block-styles.php):

```php
register_block_style(
    'core/heading',
    array(
        'name'  => 'underline',
        'label' => __( 'Underline', 'customify' ),
    )
);
```

2. **Add CSS** to `src/frontend/scss/base/_blocks.scss`:

```scss
.wp-block-heading.is-style-underline {
    text-decoration: underline;
    text-underline-offset: 0.2em;
}
```

3. **Rebuild**: `npm run build`.

The variation appears in the editor's block sidebar under "Styles" automatically.

---

## 6. Block patterns

WordPress 6.0+ auto-registers PHP files in the `patterns/` folder. Each file declares its title, slug, categories, keywords in a header comment, then outputs the pattern markup as block HTML.

### 6.1 Pattern file format

```php
<?php
/**
 * Title: Hero Centered
 * Slug: customify/hero-centered
 * Categories: customify, header
 * Keywords: hero, cta, banner
 */
?>
<!-- wp:cover { ... } -->
<div class="wp-block-cover ...">
    <!-- nested blocks -->
</div>
<!-- /wp:cover -->
```

### 6.2 Currently registered patterns

| File | Title | Slug |
|---|---|---|
| [`patterns/cta-banner.php`](../patterns/cta-banner.php) | CTA Banner | `customify/cta-banner` |
| [`patterns/features-three-columns.php`](../patterns/features-three-columns.php) | Features (Three Columns) | `customify/features-three-columns` |
| [`patterns/hero-centered.php`](../patterns/hero-centered.php) | Hero Centered | `customify/hero-centered` |
| [`patterns/media-text-left.php`](../patterns/media-text-left.php) | Media + Text (Left) | `customify/media-text-left` |
| [`patterns/testimonial.php`](../patterns/testimonial.php) | Testimonial | `customify/testimonial` |

### 6.3 Adding a new pattern

Create a PHP file in `patterns/` with the standard header comment. **No registration code required** — WordPress 6.0+ discovers the file automatically.

Naming convention: `<concept>.php` (kebab-case). Pattern slug: `customify/<concept>` (namespace matches theme slug).

---

## 7. Design decisions

### 7.1 Classic theme, not FSE

- **Chose**: Classic theme architecture — PHP templates, Customizer, theme.json as a static declaration
- **Rejected**: Full Site Editing migration
- **Reason**: 30k-site install base. FSE migration would invalidate every saved Customizer layout, every header/footer builder configuration, every child theme override. Customify's value to its users IS the Customizer + builder workflow — FSE is a different product. Cost: doesn't ship `templates/` or `parts/`; benefits classical authors over block-first authors.

### 7.2 Static palette in theme.json instead of dynamic injection

- **Chose**: Color palette hardcoded in `theme.json`
- **Rejected**: `wp_theme_json_data_theme` filter to inject palette from Customizer values
- **Reason**: WP's filter origin tracking misbehaves on append — the existing 8 theme entries got shadowed in rendered CSS preset output. Static declarations avoid this entirely. Trade-off (documented in [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §3.7): when user changes a slot value, `:root --customify-<slot>` updates but `--wp--preset--color--<slug>` stays static. Matches pre-Phase 1 behavior (Customizer color changes never propagated to the editor palette either).

### 7.3 Curated `$keys` list instead of syncing all Customizer values

- **Chose**: Hand-maintained list of 7 keys in `Customify_Editor::css()`
- **Rejected**: Auto-sync every field with `selector` + `css_format`
- **Reason**: Most Customizer fields target frontend-only markup (headers, footers, sidebars) that the editor doesn't render. Auto-syncing all of them = noisy editor stylesheet + selector rewriting overhead for no visual benefit. The curated list captures only the values that affect the post body — the parts the editor actually shows.

### 7.4 Block CSS in `_blocks.scss`, not per-style files

- **Chose**: One SCSS partial holds all block style variations
- **Rejected**: One SCSS file per registered block style
- **Reason**: 7 small CSS rules don't need 7 source files. The frontend bundles them together regardless of source layout, so per-file separation buys nothing. If a single style grew large enough to warrant isolation, extract then.

### 7.5 Patterns as PHP files, not block JSON

- **Chose**: PHP files in `patterns/` (WP 6.0+ auto-discovery)
- **Rejected**: `register_block_pattern()` calls with inline HTML strings
- **Reason**: PHP files let patterns include localized strings via `__()`. They're also more maintainable — block HTML is easier to read in a `.php` file with syntax highlighting than as an escaped string. WP 6.0+ auto-discovery removes the registration boilerplate.

---

## 8. Hooks & filters catalog

The block editor integration is mostly read-side — it doesn't expose many filters. The keys to watch:

| Hook | Type | Purpose |
|---|---|---|
| `block_editor_settings_all` | WP core filter | `Customify_Editor::editor_settings()` injects color/font settings |
| `enqueue_block_editor_assets` | WP core action | `Customify_Editor::assets()` enqueues editor JS + CSS |
| `init` | WP core action | `customify_register_block_styles()` registers the 7 block styles |

For adding custom synced keys, add to the `$keys` array in [`inc/admin/editor.php`](../inc/admin/editor.php) `css()` method (no filter — must edit the array).

---

## 9. Known issues / edge cases

### Issue #1 — `alignwide` / `alignfull` must use modern CSS

The frontend SCSS uses CSS custom properties and `calc(50% - 50vw)` for align rules. The OLD `transform: translateX(-50%)` technique conflicts with `margin: auto` on `.entry-content > *`. See [`../AGENTS.md`](../AGENTS.md) §4.13 for the canonical pattern.

### Issue #2 — Deprecated WP 6.0+ selectors

| Old (deprecated) | Use instead |
|---|---|
| `.editor-post-title__input` | `.wp-block-post-title` |
| `.edit-post-visual-editor` | `.editor-styles-wrapper` |
| `.edit-post-layout__content` | `.editor-styles-wrapper` |
| `.wp-block[data-align="wide"]` | `.alignwide` |
| `wp-edit-post` style handle | `wp-edit-blocks` (WP 6.2+) |

Using deprecated selectors silently produces no-op CSS in current WP versions.

### Issue #3 — Verify core block selectors against source

Don't assume class names for WP core blocks. Verify with:

```bash
grep -r 'class=' /path/to/wordpress/wp-includes/blocks/<block-name>/
```

Known assumed-vs-real mismatches that broke styles:

| Assumed | Real (from WP source) |
|---|---|
| `.wp-block-categories > li` | `.wp-block-categories-list .cat-item` |
| `.wp-block-archives > li` | `.wp-block-archives-list > li` |
| `tfoot td#prev` / `tfoot td#next` | `.wp-calendar-nav .wp-calendar-nav-prev` / `.wp-calendar-nav-next` |
| `.wp-block-categories__post-count` | (class doesn't exist — remove) |

### Issue #4 — `theme.json` palette doesn't live-update with Customizer

Changing a Customizer color slot updates `:root --customify-<slot>` on the frontend (and in the Customizer preview iframe) but **not** the block editor's color picker — which always shows the static `theme.json` palette. Accepted trade-off — see §7.2.

### Issue #5 — Block style CSS only loads in editor via `Customify_Editor::css()`

If a new block style variation doesn't appear in the editor preview, it's likely missing from the editor CSS pipeline. The SCSS lives in `_blocks.scss` (frontend), but the editor needs `Customify_Editor::assets()` to enqueue it via `wp-edit-blocks` handle — confirm both sides ship together.

---

## 10. Quick reference

| I want to… | How |
|---|---|
| Sync a Customizer field's CSS into the editor | Append the field name to `$keys` in [`inc/admin/editor.php`](../inc/admin/editor.php) `css()` method |
| Add a block style variation | `register_block_style()` in [`inc/admin/block-styles.php`](../inc/admin/block-styles.php) + SCSS in `_blocks.scss` + `npm run build` |
| Add a block pattern | Create `patterns/<name>.php` with the standard header comment — auto-discovered by WP 6.0+ |
| Add a new color slug to `theme.json` palette | Append to `settings.color.palette` array in `theme.json`. Note slug is public API — never rename existing entries |
| Override editor's `contentSize` for a post type | Branch on post type in [`inc/admin/editor.php`](../inc/admin/editor.php) `css()` method (see `single_blog_post_content_width` for the pattern) |

---

## 11. Where to look next

**PHP**
- [`inc/admin/editor.php`](../inc/admin/editor.php) — `Customify_Editor` class
- [`inc/admin/block-styles.php`](../inc/admin/block-styles.php) — block style registration
- [`theme.json`](../theme.json) — static palette + sizes
- [`patterns/`](../patterns/) — block patterns

**SCSS**
- `src/frontend/scss/base/_blocks.scss` — block-related CSS (frontend + editor)

**Related specs**
- [`SPEC-customizer.md`](SPEC-customizer.md) §12 — Customizer → editor bridge (architectural view)
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §3.7 — palette ↔ theme.json sync trade-off
- [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) — editor CSS enqueue path

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.11 — container width must sync `--wp--style--global--wide-size`
- [`../AGENTS.md`](../AGENTS.md) §4.12 — deprecated editor selectors table
- [`../AGENTS.md`](../AGENTS.md) §4.13 — `alignwide` / `alignfull` modern pattern
