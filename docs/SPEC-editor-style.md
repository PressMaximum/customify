# SPEC — Editor Style: content-size + wide-size + Content Layout

Drives block alignment widths (`align=none`, `alignwide`, `alignfull`) across the frontend AND the block editor canvas, so authors see the rendered widths their visitors will see. Built on `--wp--style--global--content-size` + `--wp--style--global--wide-size` CSS variables, plus a per-post `_customify_content_layout` meta with values `''` (default) / `full-width` / `full-stretched` / `narrow`.

Related references:
- [`SPEC-customizer.md`](SPEC-customizer.md) §12 — Customizer → editor bridge (architectural view)
- [`SPEC-block-editor.md`](SPEC-block-editor.md) §4 — `Customify_Editor` class
- [`../AGENTS.md`](../AGENTS.md) §4.11 — container_width must sync to wide-size CSS var

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The system has **three input sources** that resolve to **two CSS variables** that block-layout SCSS + theme block consumers read. The variables are layout-driven (per body class) and content-layout-driven (per `.site-content` class), with editor JS mirroring the resolved values into the block-editor settings store so toolbar alignment dropdowns stay in sync.

| Surface | Role |
|---|---|
| `container_width` Customizer slider | Scales the typography readability cap proportionally per-site |
| `narrow_width` Customizer slider | Width used by the "Narrow" Content Layout option (default 800px) |
| `single_blog_post_content_width` Customizer slider | User override for single posts only |
| `_customify_content_layout` post meta | Per-post override: `''` / `full-width` / `full-stretched` / `narrow` |
| `--wp--style--global--content-size` | Block max-width for `align=none` (also read by Blocksify Section "Inherit ON") |
| `--wp--style--global--wide-size` | Block max-width for `alignwide` |
| Frontend SCSS (`_blocks.scss`) | Applies the vars + alignwide breakout |
| Editor PHP (`editor.php`) | Initial body var + per-post sidebar resolution |
| Editor JS (`page-settings/index.js::ContentSizeSync`) | Live updates body var + block-editor settings store |
| Anchor option (`customify_layout_content_size_anchor`) | Per-site upgrade-time snapshot for 30K zero-diff |

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/template-functions.php`](../inc/template-functions.php) | ~21-296 | `customify_get_container_width_value()`, `customify_get_layout_content_sizes()`, `customify_get_narrow_width_value()`, `customify_layout_content_size_css()`, anchor migration |
| [`inc/customizer/configs/layouts.php`](../inc/customizer/configs/layouts.php) | ~85-125 | `container_width` + `narrow_width` slider fields |
| [`inc/customizer/configs/single-blog-post.php`](../inc/customizer/configs/single-blog-post.php) | ~22-40 | `single_blog_post_content_width` slider |
| [`inc/admin/editor.php`](../inc/admin/editor.php) | ~178-240 | `Customify_Editor::css()` — emits body content-size + alignwide breakout for editor canvas |
| [`inc/admin/page-settings.php`](../inc/admin/page-settings.php) | ~131-200 | Localizes config (`contentSizeMap`, `postType`, `postContentSize`, `narrowWidth`, `wideSize`) |
| [`inc/class-metabox.php`](../inc/class-metabox.php) | ~60-72 | Classic editor Content Layout dropdown |
| [`src/backend/page-settings/index.js`](../src/backend/page-settings/index.js) | ~25-260 | `ContentSizeSync` + `syncEditorLayoutSettings` |
| [`src/frontend/scss/base/_blocks.scss`](../src/frontend/scss/base/_blocks.scss) | ~16-50 | Frontend alignwide breakout |

---

## 3. Storage shape & data contract

### 3.1 `theme_mod` keys

| Key | Stored type | Default | Notes |
|---|---|---|---|
| `container_width` | array `{ value: int, unit: 'px' }` | unsaved → falls back to 1248 (SCSS hardcode) | Slider, range 700–2000 |
| `narrow_width` | array `{ value: int, unit: 'px' }` | unsaved → 800 | Slider, range 400–1000 |
| `single_blog_post_content_width` | array `{ value: int, unit: 'px' }` | unsaved → 863 | Slider, range 400–1200 |

### 3.2 `wp_options` keys

| Option | Stored type | Notes |
|---|---|---|
| `customify_layout_content_size_anchor` | int | Per-site anchor for proportional scaling (= container_width snapshot at upgrade). Default 1248. |
| `customify_layout_anchor_migrated_v1` | string `'1'` | Idempotency flag for `customify_migrate_layout_content_size_anchor()` |

### 3.3 `post_meta` keys

| Meta key | Post type | Stored type | Possible values |
|---|---|---|---|
| `_customify_content_layout` | any | string | `''` (default) / `'full-width'` / `'full-stretched'` / `'narrow'` |

### 3.4 Anchor migration

`customify_migrate_layout_content_size_anchor()` hooked at `after_setup_theme:5`. Captures current `container_width` (or 1248 fallback when unsaved) into `customify_layout_content_size_anchor`. Idempotent via the `_v1` flag. See [`migration-guide.md`](migration-guide.md) §2.

**Why per-site**: a global anchor (e.g. hardcoded 1248) would silently scale content-size on sites that had explicitly saved `container_width = 1500`. Per-site capture means `factor = 1.0` immediately after upgrade — zero rendered diff for every existing site regardless of their saved values. Scaling only kicks in when the user moves the slider AFTER the upgrade.

### 3.5 Defaults

| Setting | Default at unsaved state | What renders |
|---|---|---|
| `container_width` | unsaved | SCSS hardcoded 1248 wins (Customizer Auto-CSS skips emission for default values) |
| `narrow_width` | 800 | `.site-content.content-narrow` rule emits this fallback |
| `single_blog_post_content_width` | 863 | `body.single-post` rule emits this fallback |
| anchor | first run → captured from container_width | factor = 1.0 |

---

## 4. Render pipeline

### 4.1 Resolution priority for `--wp--style--global--content-size` on body

Cascade order (later wins for same selector):

1. **`theme.json`** (static, loaded as `:root { --wp--style--global--content-size: 863px }`)
2. **`body.main-layout-<layout>`** — emitted by `customify_layout_content_size_css()` (inline CSS on `customify-style` handle):
   - `.main-layout-content` → no_sidebar formula
   - 1-sidebar variants → falls through to theme.json (no body rule), wide-size body rule sets wide explicitly
   - 2-sidebar variants → two_sidebars formula
3. **`body.single-post`** — emitted by `single_blog_post_content_width` field's `css_format` (auto-CSS pipeline). Wins for single posts over the layout rule by source order.
4. **`.site-content.content-<layout>`** — closer ancestor than body, overrides for per-post Content Layout:
   - `.content-narrow` → narrow_width Customizer value
   - `.content-full-width` → `calc(100vw - 64px)`
   - `.content-full-stretched` → `100vw`

### 4.2 Per-layout sizes (matrix)

At default state (`container_width` unsaved → anchor 1248):

| Layout / Content Layout | `--wp--style--global--content-size` | `--wp--style--global--wide-size` |
|---|---|---|
| Default no-sidebar (`.main-layout-content`) | **1184px** (= no_sidebar, scaled) | **1584px** (= content + 400) |
| 1-sidebar (content-sidebar, sidebar-content) | 863px (theme.json fallback, scaled cap) | **863px** (explicit body rule, no breakout into sidebar) |
| 2-sidebar (3 variants) | **542px** (scaled cap) | **542px** (= content) |
| `.content-narrow` | **800px** (Customizer narrow_width) | **1200px** (= narrow + 400) |
| `.content-full-width` | **calc(100vw - 64px)** (container content-box at 100% with 2em padding each side) | **calc(100vw - 64px)** |
| `.content-full-stretched` | **100vw** (no container padding) | **100vw** |

When `container_width` is saved away from anchor, the **scaled cap** scales proportionally: `value = round(default * container_width / anchor)`.

### 4.3 Formula: `customify_get_layout_content_sizes()`

```
cw     = customify_get_container_width_value()       // 1248 fallback when unsaved
anchor = get_option( 'customify_layout_content_size_anchor', 1248 )
factor = cw / anchor

cap_no_sidebar   = round( 1184 * factor )
cap_one_sidebar  = round(  863 * factor )
cap_two_sidebars = round(  542 * factor )

inner = max(0, cw - 64)        // .customify-container content-box (after 2em*2 padding)
grid  = inner + 32             // gridlex grid (negative 1em margin each side)

parent_no_sidebar   = grid - 32           // col padding only
parent_one_sidebar  = round(grid * 0.75) - 32 - 16   // col + 1-side content-inner pad
parent_two_sidebars = round(grid * 0.5)  - 32 - 32   // col + 2-side content-inner pad

return min(cap, parent)  per layout
```

**Why `min(cap, parent)`**: the cap is a typography readability target (~60–80 chars/line). The parent is the actual frontend column geometry. Returning the smaller value gives the width blocks actually reach on the frontend — which the editor canvas can then use directly (no nesting structure to constrain editor blocks naturally). Keeps editor WYSIWYG honest.

### 4.4 Frontend SCSS — alignwide breakout

[`_blocks.scss:29-58`](../src/frontend/scss/base/_blocks.scss):

```scss
.entry-content > .alignwide {
    --customify-alignwide-actual: max(
        100%,
        min(var(--wp--style--global--wide-size, 1200px), calc(100vw - 32px))
    );
    width: var(--customify-alignwide-actual);
    margin-left:  calc((100% - var(--customify-alignwide-actual)) / 2);
    margin-right: calc((100% - var(--customify-alignwide-actual)) / 2);
    box-sizing: border-box;
}
```

- **`max(100%, ...)`** — floor at parent width so a narrow viewport never makes alignwide visually smaller than `align=none`. Cascade intent: "wide ≥ none, always".
- **`min(wide-size, 100vw - 32px)`** — ceiling at viewport-32 so breakout never causes horizontal scroll. The breakout dynamically shrinks (down to 0/side on tiny viewports).
- **Negative inline margins** center the over-wide box across the parent's midline, same approach as alignfull's `calc(50% - 50vw)` but bounded.

### 4.5 Editor canvas mirror

[`editor.php::css()`](../inc/admin/editor.php):
1. Resolves layout from post sidebar meta + force-no-sidebar overrides
2. Resolves `$size` based on content_layout / post type:
   - `narrow` → narrow_width
   - `full-width` → `calc(100vw - 64px)`
   - `full-stretched` → `100vw`
   - post type `post` + saved single_blog_post_content_width → user value
   - else → layout-derived
3. Emits `body { --wp--style--global--content-size: $size; }` (no `!important`)
4. Emits `.editor-styles-wrapper > .is-root-container > .alignwide` rule (same formula as frontend SCSS)

[`page-settings/index.js::ContentSizeSync`](../src/backend/page-settings/index.js):
1. Watches `_customify_sidebar` + `_customify_content_layout` meta via `useEntityProp`
2. Computes `size` (priority: full-width/stretched calc, narrow, post override, layout)
3. Computes `wideSize` (size + 400 for no-sidebar family, same as size for viewport-bound or sidebar layouts)
4. `buildContentSizeCss()` → injects `<style id="customify-layout-content-size">` into editor iframe head with body content-size + per-layout max-width override on root blocks
5. `syncEditorLayoutSettings()` → dispatches `updateSettings({ layout, __experimentalFeatures })` so block-toolbar dropdown labels match runtime values
6. Subscribes to `core/block-editor` store to re-apply after WP overwrites settings during bootstrap

---

## 5. Design decisions

### 5.1 Per-site anchor migration vs global hardcoded anchor

- **Chose**: capture `container_width` per-site at first `after_setup_theme:5` run after upgrade
- **Rejected**: hardcoded anchor = 1248 globally
- **Reason**: a global anchor would shift content-size on sites that explicitly saved non-default container_width. Per-site capture gives `factor=1.0` immediately after migration → zero rendered diff for every existing site. Scaling only kicks in when the user moves the slider AFTER upgrade — which is when they actually want scaling.

### 5.2 `min(cap, parent)` formula for layout content sizes

- **Chose**: return `min(typography_cap, frontend_parent_geometry)` per layout
- **Rejected**: cap-only OR parent-only
- **Reason**: cap-only would let editor blocks render at sizes the frontend column can't actually reach (lying about WYSIWYG). Parent-only would let alignment dropdown labels grow without bound on wide containers, ignoring readability. Both bounds, smaller wins.

### 5.3 Viewport-bound content-size for Full Width / Full Stretched

- **Chose**: emit `calc(100vw - 64px)` (Full Width) and `100vw` (Stretched) directly to the CSS variable
- **Rejected**: keep variable at no_sidebar fallback (1184) and use SCSS exclusions to bypass the cap per layout
- **Reason**: third-party consumers (Blocksify Section's "Inherit Max Width from Theme") read `--wp--style--global--content-size` directly. If theme keeps it at 1184 even for Full Width, those blocks stay capped at reading-column width even though the section spans viewport. Emitting the variable correctly makes ALL consumers (Core blocks via SCSS, third-party blocks reading the var) naturally fill the available container space without per-block CSS hacks.

### 5.4 Wide-size = content for sidebar layouts (no breakout into sidebar)

- **Chose**: emit `--wp--style--global--wide-size: <content_size>` for 1-sidebar and 2-sidebar layouts
- **Rejected**: emit wide-size = content + 400 for ALL layouts, let alignwide breakout into sidebar visually
- **Reason**: alignwide extending into the sidebar area looks broken. User confirmed Q2: "if page has sidebar layout, don't worry — wide stays in content, doesn't overlap sidebar". Setting wide-size = column width lets alignwide visually = align=none in sidebar layouts (which IS the expected behavior).

### 5.5 Editor settings.layout sync via subscribe

- **Chose**: `syncEditorLayoutSettings()` subscribes to `core/block-editor` store, re-applies on every settings change
- **Rejected**: dispatch once on mount
- **Reason**: WP editor bootstrap loads theme.json values into settings AFTER plugin mount. A one-shot dispatch gets overwritten. Subscribe + re-apply with internal bail-out (compare current vs desired) keeps values stuck without infinite loop.

### 5.6 PHP no-op for full-width max-width override; JS owns it

- **Chose**: PHP `editor.php` only emits body content-size; JS handles the per-block max-width override for full-width / full-stretched
- **Rejected**: PHP also emits `max-width: none` based on saved meta
- **Reason**: PHP CSS is static for the page load. When user toggles content_layout in the metabox, PHP-emitted override stays applied and JS can't undo it (different CSS rules). Letting JS own the dynamic part keeps the metabox toggle responsive — JS `<style>` re-renders on every meta change.

### 5.7 Cap removal via dynamic content-size, not SCSS exclusion

- **Chose**: emit content-size = viewport-bound for full-width/stretched. SCSS rule applies uniformly.
- **Rejected**: keep content-size at fixed value, use `:not(.content-full-width):not(.content-full-stretched)` to exclude from cap rule
- **Reason**: dynamic value approach makes ALL consumers adapt automatically (Core blocks via SCSS, third-party blocks via var). The exclusion approach only handles Core blocks via custom SCSS and leaves third-party consumers stuck at 1184.

---

## 6. Adding / extending

### 6.1 Adding a new Content Layout value (similar to Narrow)

```php
// Step 1 — Add to CONTENT_LAYOUT_OPTIONS in src/backend/page-settings/index.js
{ label: __( 'My Layout', 'customify' ), value: 'my-layout' }

// Step 2 — If it forces no-sidebar, add to NO_SIDEBAR_CONTENT_LAYOUTS in JS
const NO_SIDEBAR_CONTENT_LAYOUTS = [ 'full-width', 'full-stretched', 'narrow', 'my-layout' ];

// Step 3 — Add to PHP force-no-sidebar guard in inc/template-functions.php
if ( in_array( $content_layout, array( 'full-width', 'full-stretched', 'narrow', 'my-layout' ), true ) ) {
    return 'content';
}

// Step 4 — If it needs a Customizer width slider, add to inc/customizer/configs/layouts.php
// (model on narrow_width)

// Step 5 — Emit .site-content.content-my-layout rule in customify_layout_content_size_css()

// Step 6 — Handle in editor.php editor canvas branch:
elseif ( 'my-layout' === $content_layout ) {
    $size = '...';
}

// Step 7 — Handle in JS buildContentSizeCss + size resolution

// Step 8 — Add to classic editor metabox in inc/class-metabox.php

// Step 9 — npm run build
```

### 6.2 Overriding sizes via filter

```php
// Override content-size values per layout (frontend + editor consume same function)
add_filter( 'customify/layout_content_sizes', function ( $sizes ) {
    $sizes['no_sidebar'] = '1400px';
    return $sizes;
} );
```

### 6.3 Reading the resolved sizes programmatically

```php
$sizes = customify_get_layout_content_sizes();
// array( 'no_sidebar' => '1184px', 'one_sidebar' => '863px', 'two_sidebars' => '542px' )

$narrow = customify_get_narrow_width_value();
// '800px' (CSS length string)

$cw = customify_get_container_width_value();
// 1248 (int, px)
```

---

## 7. Hooks & filters catalog

### 7.1 Filters

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/layout_content_sizes` | filter | `array{ no_sidebar:string, one_sidebar:string, two_sidebars:string }` | Override per-layout content-size values |
| `customify_get_layout` | filter | `string|null $layout` | Force no-sidebar for full-width/full-stretched/narrow content_layout (priority via `customify_force_no_sidebar_for_full_content_layout`) |

### 7.2 Actions

| Hook | Type | Purpose |
|---|---|---|
| `after_setup_theme` (priority 5) | action | `customify_migrate_layout_content_size_anchor()` — one-time anchor capture |

---

## 8. Known issues / edge cases

### Issue #1 — Customizer live preview slider drag doesn't re-render content-size body rules

When the user moves the `container_width` slider in Customizer live preview, only the field's own `css_format` re-renders (container max-width + wide-size). The static `customify_layout_content_size_css()` output (body content-size rules) is NOT in the auto-CSS rebuild pipeline.

**Effect**: live preview shows container max-width + wide-size updating; content-size body rules stay at page-load values until publish + reload.

**Workaround**: publish, then refresh the preview iframe.

### Issue #2 — theme.json `wideSize` is static (1200px)

Site Editor (if enabled) reads theme.json directly. Customizer changes don't propagate. Edge case for classic theme flow.

### Issue #3 — Blocksify Section + Inherit ON + Full Stretched overflows 64px

When Blocksify Section is set to alignfull with "Inherit Max Width from Theme" enabled, the inner uses `calc(var(content-size) + section_inner_padding * 2)`. In Full Stretched mode, content-size = 100vw → inner = 100vw + 64 → overflows viewport by 64px.

**Workaround**: turn OFF "Inherit Max Width from Theme" for Stretched sections, OR avoid Stretched layout when using Blocksify Section with inherit.

**Root cause**: Blocksify-side — they add inner padding to the content-size cap regardless of whether the section provides padding itself. Fix belongs in Blocksify CSS rule.

### Issue #4 — Editor dropdown labels for Full Width / Stretched are non-numeric

The toolbar alignment dropdown labels read settings.__experimentalFeatures.layout.contentSize. For Full Width / Stretched we push the calc-string `'calc(100vw - 64px)'` / `'100vw'` to keep CSS behaviour aligned. Labels display "Max calc(100vw - 64px) wide" instead of a numeric "Max 1780px wide".

**Cosmetic**: functional behavior is correct. Could be improved by computing a viewport-aware numeric snapshot for the settings push while keeping the body var as calc.

---

## 9. Pro plugin handoff

`Customify_Pro_Module_<Editor>` doesn't exist — this is theme-side only. If a future Pro module wants to extend, it must:

- Honor the `customify/layout_content_sizes` filter and pass through the array shape `{ no_sidebar, one_sidebar, two_sidebars }`
- Honor the `--wp--style--global--content-size` / `--wp--style--global--wide-size` CSS variable contract on body + `.site-content.content-<layout>`
- Honor the `_customify_content_layout` meta values (`'' / full-width / full-stretched / narrow`) and the body class derivation `.site-content.content-<value>`

See [`SPEC-pro-integration.md`](SPEC-pro-integration.md) for the full contract.

---

## 10. Troubleshooting

| Symptom | Likely cause |
|---|---|
| Editor block widths don't match frontend | Outdated browser cache → hard reload (Cmd+Shift+R). Also check `getSettings().__experimentalFeatures.layout.contentSize` in console matches `getComputedStyle(body).getPropertyValue('--wp--style--global--content-size')`. |
| Container width slider doesn't visibly change content area on existing sites | Site has `container_width` saved at a value that produces `factor=1.0` against the captured anchor. Expected — anchor migration zero-diff design. |
| Narrow Content Layout dropdown doesn't appear | Build outdated. Run `npm run build` and reload editor. |
| Block toolbar dropdown shows "Max 100vw wide" or "Max calc(...) wide" | Expected for Full Width / Stretched. Cosmetic — see Issue #4. |
| `_customify_content_layout = narrow` but sidebar still renders | Sidebar meta took precedence over theme support meta lookup. Check `customify_force_no_sidebar_for_full_content_layout` filter ran (`'narrow' in array`). |
| Blocksify Section inner doesn't fill section in alignfull | Either: (a) section is in Stretched mode → Issue #3 (Blocksify-side); (b) "Inherit Max Width from Theme" is OFF (per-block custom max-width set). |
| Single post `single_blog_post_content_width` doesn't show in editor | Customizer hasn't been saved (still default). OR post is being edited as `page` post type (single_blog_post_content_width is post-only). |

---

## 11. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Read resolved layout content sizes (PHP) | `customify_get_layout_content_sizes()` |
| Read resolved narrow_width (PHP) | `customify_get_narrow_width_value()` |
| Read resolved container_width (PHP) | `customify_get_container_width_value()` |
| Override content-sizes for a child theme | `add_filter( 'customify/layout_content_sizes', fn ($s) => [ ... ] );` |
| Detect Narrow content_layout (PHP, post context) | `get_post_meta( $post_id, '_customify_content_layout', true ) === 'narrow'` |
| Detect Narrow content_layout (JS, editor) | `wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' )._customify_content_layout === 'narrow'` |
| Force a content_layout via REST | `POST /wp-json/wp/v2/pages/<id>` with `{ meta: { _customify_content_layout: 'narrow' } }` |

---

## 12. Where to look next

**PHP**
- [`inc/template-functions.php`](../inc/template-functions.php) — helpers + `customify_layout_content_size_css()` + migration
- [`inc/customizer/configs/layouts.php`](../inc/customizer/configs/layouts.php) — Customizer fields
- [`inc/customizer/configs/single-blog-post.php`](../inc/customizer/configs/single-blog-post.php) — `single_blog_post_content_width`
- [`inc/admin/editor.php`](../inc/admin/editor.php) — `Customify_Editor` class, editor canvas inline CSS
- [`inc/admin/page-settings.php`](../inc/admin/page-settings.php) — block editor metabox + JS config localize
- [`inc/class-metabox.php`](../inc/class-metabox.php) — classic editor metabox

**JavaScript**
- [`src/backend/page-settings/index.js`](../src/backend/page-settings/index.js) — `ContentSizeSync` + `syncEditorLayoutSettings`

**SCSS**
- [`src/frontend/scss/base/_blocks.scss`](../src/frontend/scss/base/_blocks.scss) — `.entry-content > .alignwide` breakout, `> *` content-size cap
- [`src/frontend/scss/layouts/_layouts.scss`](../src/frontend/scss/layouts/_layouts.scss) — `.customify-container` padding, `.content-full-{width,stretched}` container overrides, per-sidebar `.content-inner` padding

**Related specs**
- [`SPEC-customizer.md`](SPEC-customizer.md) §12 — Customizer → editor bridge
- [`SPEC-block-editor.md`](SPEC-block-editor.md) §4 — `Customify_Editor` overview
- [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) — anchor migration pattern

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.1 — 30k-site safety (migration design rationale)
- [`../AGENTS.md`](../AGENTS.md) §4.7 — CSS handle must be `customify-style`
- [`../AGENTS.md`](../AGENTS.md) §4.11 — container_width sync to wide-size var
- [`../AGENTS.md`](../AGENTS.md) §4.13 — alignwide / alignfull modern pattern
