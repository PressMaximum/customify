# SPEC — Typography (CSS Variables)

Customify renders **Global Typography panel settings** (font family, size, weight, line-height, letter-spacing, text-decoration, text-transform, font-style) as **`:root { --customify-typo-*: value }` CSS custom properties**. SCSS layer consumes the vars via `var(--customify-typo-…, fallback)` at the matching selectors. **Per-component typography** (header builder items, footer copyright, blog read-more, breadcrumb, WC cart) keeps the legacy selector-scoped output because its SCSS layer doesn't carry generic consumer rules — vars mode would silently drop the user's value. Landed in theme `0.5.0`.

The scope boundary is the field's setting `name`: vars emit only when `name` starts with `global_typography_` (the Base / Site Title & Tagline / Heading family — 11 settings). Everything else falls through to the legacy emit path. The gate keys off `name` rather than `section` because the Typography IA was flattened into a single `typography_panel` section, so the section id no longer encodes which group a field belongs to.

Related references:
- [`SPEC-customizer.md`](SPEC-customizer.md) — underlying Customizer architecture and the typography control type registration (`§5.3`).
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) — sibling spec for color tokens at `:root`; the typography vars extend the same `--customify-*` namespace.
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — Pro plugin handoff for the version-gate helper (pending).
- [`migration-guide.md`](migration-guide.md) — 30k-site safety policy. This migration changes CSS output shape but not storage shape, so no DB migration is needed.

This file is permanent. For the in-flight rollout notes used during implementation, see the memory file `typography-vars-migration` in the user's project memory store.

---

## 1. Overview

| Surface | Role |
|---|---|
| Customizer Typography panel + per-component typography fields | 20 registered fields (11 global panel, 9 per-component) — see [`SPEC-customizer.md §5.3`](SPEC-customizer.md) |
| `theme_mod` storage (unchanged) | Persistent typography values per setting; per-device map for `font_size` and `line_height` |
| `Customify_Customizer_Auto_CSS::typography()` | PHP emit — vars for global fields, selector-scoped literal CSS for per-component (route decided by `typography_field_uses_vars()`) |
| `CustomifyAutoCSS.prototype.typography` (`auto-css.js`) | JS mirror for Customizer live preview |
| `src/frontend/scss/*` consumers | Read vars via `var(--customify-typo-…, fallback)` at the relevant selectors — only for the 11 global settings |
| Block editor canvas | Inherits via [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) which imports the frontend `_base`/`_blocks`/`_widgets` partials |

**Default mode is vars for global typography only.** One filter knob:
- `customify/typography/field_uses_vars` (default = setting `name` starts with `global_typography_`) — per-field override. Return `false` to force a global field through legacy, or `true` to opt a per-component field into vars (requires adding an SCSS consumer at the matching selector). See §7.

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) | `typography()`, `code_to_root_vars()`, `typography_var_name()`, `typography_field_uses_vars()`, `$css_root` bucket + `render_css()` flush | PHP generator + helpers |
| [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) | `typography()`, `code_to_root_vars()`, `typography_var_name()`, `typography_field_uses_vars()`, `css_root` flush | JS live preview mirror |
| [`src/frontend/scss/utils/_mixins.scss`](../src/frontend/scss/utils/_mixins.scss) | `@mixin customify-typography($name, $props, $fallbacks)` | Emits 8 var consumers in one call for selectors without existing typography literals |
| ~10 frontend SCSS partials (see §4) | inline | Var consumers — direct `var(...)` calls or via the mixin |
| [`inc/admin/editor.php`](../inc/admin/editor.php) | Unchanged for vars mode | Editor inline CSS via `Customify_Editor::css()` + `load_style()`; both call `Customify_Customizer_Auto_CSS::render_css()` which flushes `$css_root` automatically |

---

## 3. Storage shape & data contract

Storage is **unchanged** by this migration. All existing typography `theme_mod` values continue to read and apply identically — only the CSS output shape changes.

### 3.1 `theme_mod` keys (unchanged)

Each typography setting stores a JSON object (or URL-encoded variant) with the sub-field structure defined by `Customify_Customizer::get_typo_fields()`:

| Sub-key | Type | Device-scoped? |
|---|---|---|
| `font` | string | No |
| `font_type` | string (`google`, `library`, `theme`, `custom`) | No |
| `languages` | array of strings (Google Fonts subsets) | No |
| `variant` | string | No |
| `font_weight` | string (`100`-`900`, `regular`, `normal`, `default`) | No |
| `style` | string (`normal`, `italic`, `oblique`, `default`) | No |
| `text_decoration` | string | No |
| `text_transform` | string | No |
| `letter_spacing` | object `{value, unit}` | No |
| `font_size` | object `{value, unit, desktop, tablet, mobile}` | **Yes** |
| `line_height` | object `{value, unit, desktop, tablet, mobile}` | **Yes** |

See [`SPEC-customizer.md §5.3`](SPEC-customizer.md) for the full per-field config recipe.

### 3.2 Registered typography settings (20 total)

#### Vars mode — Global panel (11 settings, file: [`inc/customizer/configs/typography.php`](../inc/customizer/configs/typography.php))

These emit `:root { --customify-typo-…: value }` at frontend. SCSS consumers live in `_base.scss`, `_widgets.scss`, `_logo_site_identity.scss`.

All 11 settings live in the flattened `typography_panel` section (the former Base / Site Title & Tagline / Content sub-sections are now `heading` separators inside it).

| Setting name | Semantic name | SCSS consumer selector |
|---|---|---|
| `global_typography_base_p` | `base-p` | `body` |
| `global_typography_base_heading` | `base-heading` | `h1..h6, .h1..h6` (family/weight only) |
| `global_typography_base_widget_title` | `base-widget-title` | `.widget-title` |
| `global_typography_site_tt_title` | `site-tt-title` | `.site-branding .site-title` |
| `global_typography_site_tt_desc` | `site-tt-desc` | `.site-branding .site-description` |
| `global_typography_heading_h1` … `h6` | `h1` … `h6` | `h1, .h1` … `h6, .h6` (with `@include for_device` responsive blocks for h1/h2) |

#### Selector-scoped mode — Per-component (9 settings)

These emit selector-scoped literal CSS at the field's `selector` exactly as pre-`0.5.0`. No SCSS var consumers exist for these — adding one and toggling `customify/typography/field_uses_vars` for the field is the opt-in path.

| Setting (file) | Semantic name | Selector |
|---|---|---|
| `primary_menu_typography` ([menus.php](../inc/customizer/configs/header/menus.php)) | `primary-menu` | `{builder-item} .nav-menu-desktop .primary-menu-ul > li > a, .builder-item-sidebar …` |
| `search_box_font_size` ([search-box.php](../inc/customizer/configs/header/search-box.php)) | `search-box` | `{selector} .search-form-fields` |
| `search_icon_modal_font_size` ([search-icon.php](../inc/customizer/configs/header/search-icon.php)) | `search-icon` | `{selector} .header-search-form .search-field` |
| `header_html_typo` ([html.php](../inc/customizer/configs/header/html.php)) | `header-html` | `.builder-header-html-item.item--html p, .builder-header-html-item.item--html` |
| `header_button_typography` ([button.php](../inc/customizer/configs/header/button.php)) | `header-button` | `a.item--button` |
| `wc_cart_typography` ([cart.php](../inc/compatibility/woocommerce/config/header/cart.php)) | `wc-cart` | `.builder-header-cart-item` |
| `footer_copyright_typography` ([copyright.php](../inc/customizer/configs/footer/copyright.php)) | `footer-copyright` | `.builder-item--footer_copyright, .builder-item--footer_copyright p` |
| `blog_post_more_typography` ([blogs.php](../inc/customizer/configs/blogs.php)) | `blog-post-more` | `#blog-posts .entry-readmore a` |
| `breadcrumb_typo` ([breadcrumb.php](../inc/compatibility/breadcrumb.php)) | `breadcrumb` | `#page-breadcrumb` |

---

## 4. Render pipeline

### 4.1 Var naming rule

`--customify-typo-{semantic-name}-{css-property-kebab}`

**Semantic name** derives from the setting `name` via these strips (in order — PHP helper `Customify_Customizer_Auto_CSS::typography_var_name()`):

1. Strip prefix `global_typography_` (if present)
2. Strip prefix `heading_` (if still present after step 1 — collapses `heading_h1` → `h1`)
3. Strip the **first matching** suffix: `_modal_font_size`, `_typography`, `_font_size`, `_typo`
4. Replace `_` with `-`

CSS properties (already kebab): `font-family`, `font-style`, `font-weight`, `font-size`, `line-height`, `letter-spacing`, `text-decoration`, `text-transform`.

Worked examples (only Global Typography fields actually emit vars today; the strip rule covers the others so the helper output is well-defined if a site opts a per-component field in via `customify/typography/field_uses_vars`):

| Setting | Property | Resulting var |
|---|---|---|
| `global_typography_base_p` | font-family | `--customify-typo-base-p-font-family` |
| `global_typography_heading_h1` | font-size | `--customify-typo-h1-font-size` |
| `header_button_typography` (legacy by default) | font-weight | `--customify-typo-header-button-font-weight` |
| `search_icon_modal_font_size` (legacy by default) | font-family | `--customify-typo-search-icon-font-family` |

PHP and JS helpers must stay in lockstep — the JS mirror is at `CustomifyAutoCSS.prototype.typography_var_name`.

### 4.2 Output shape (vars mode — default)

Non-device properties land in the `all` bucket at `:root`:

```css
:root {
    --customify-typo-base-p-font-family: "Inter";
    --customify-typo-base-p-font-weight: 400;
    --customify-typo-h1-font-family: "Inter";
    --customify-typo-h1-font-weight: 700;
    --customify-typo-h1-font-size: 2.5rem;
    --customify-typo-h1-line-height: 1.2;
}
```

Device-scoped properties (`font_size`, `line_height`) wrap in the matching media query:

```css
@media screen and (max-width: 1024px) {
    :root {
        --customify-typo-h1-font-size: 1.8rem;
    }
}
@media screen and (max-width: 568px) {
    :root {
        --customify-typo-h1-font-size: 1.2rem;
    }
}
```

### 4.3 Render flow

1. `Customify_Customizer_Auto_CSS::typography($field)` is called per field by `loop_fields()`.
2. `setup_font()` runs **regardless of mode** — it populates `$this->fonts`, `$this->library_fonts`, `$this->theme_fonts` for Google Fonts URL / WP Font Library / theme.json `@font-face` emission. **These side effects must always run.**
3. Non-device props built into `$code` (literal `property: value;` lines); device props into `$devices_css[device]` (same shape).
4. `apply_filters('customify/customizer/auto_css', $devices_css, $field, $this)` — contract unchanged.
5. **Route decision** — if `typography_field_uses_vars($field)` returns `false` (i.e. field's setting `name` doesn't start with `global_typography_`), emit selector-scoped CSS into `$this->css[device]` (legacy path — byte-identical to pre-`0.5.0` for that field).
6. Otherwise → `code_to_root_vars()` parses each `property: value;` line, derives the var name via `typography_var_name()`, and **appends the raw `--var: value;` lines** to `$this->css_root[device]`. Multiple fields accumulate into the same bucket.
7. `render_css()` wraps each non-empty `$this->css_root[device]` in a **single** `:root { ... }` block and the matching media query, then flushes `$this->css[device]` afterwards.
8. Final CSS lands inline via `wp_add_inline_style('customify-style', ...)` — handle unchanged.

### 4.4 SCSS consumer placement (Global panel only)

For the 11 Global Typography settings, **SCSS owns the selector** — vars at `:root` don't apply until something consumes them. Per-component fields stay in legacy mode and PHP still emits the selector-scoped CSS directly, so no SCSS var consumer exists for them.

| Setting group | Primary SCSS file |
|---|---|
| `base_p`, `base_heading` (group rules h1-h6) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) lines 21-51 |
| `base_widget_title` | [`widgets/_widgets.scss`](../src/frontend/scss/widgets/_widgets.scss) lines 1-12 |
| `heading_h1` … `h6` (with `@include for_device` responsive for h1/h2) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) lines 53-113 |
| `site_tt_title`, `site_tt_desc` | [`header/builder_items/_logo_site_identity.scss`](../src/frontend/scss/header/builder_items/_logo_site_identity.scss) lines 18-32 |
| `footer_copyright_typography` | [`footer/_footer-common.scss`](../src/frontend/scss/footer/_footer-common.scss) |
| `base_widget_title` | [`widgets/_widgets.scss`](../src/frontend/scss/widgets/_widgets.scss) |
| `blog_post_more_typography` | [`layouts/_blogs.scss`](../src/frontend/scss/layouts/_blogs.scss) (`.readmore-button`) |
| `wc_cart_typography` | [`compatibility/wc/_wc-cart.scss`](../src/frontend/scss/compatibility/wc/_wc-cart.scss) |

**Two consumer patterns:**

- **Direct `var(...)` calls** — preferred for selectors that already had typography literals. Preserves breakpoint-specific fallbacks (e.g. h1 `font-size` differs per `@include for_device`):
  ```scss
  h1, .h1 {
      font-size: var(--customify-typo-h1-font-size, 2.42em);
      @include for_device(tablet) {
          font-size: var(--customify-typo-h1-font-size, 2.1em);
      }
  }
  ```
- **`customify-typography` mixin** in [`utils/_mixins.scss`](../src/frontend/scss/utils/_mixins.scss) — used for selectors that had no typography literal to start (currently the tagline `.site-description`). Emits all 8 properties in one call with safe no-override fallbacks (`inherit` / `normal` / `none`); kept available for opt-in scenarios via `customify/typography/field_uses_vars`:
  ```scss
  .site-description {
      margin: 5px 0 7px;
      @include customify-typography('site-tt-desc');
  }
  ```

Every `var(...)` must be 2-arg (1-arg banned per [`../AGENTS.md`](../AGENTS.md)).

### 4.5 Live preview & block editor

- **Customizer live preview**: `CustomifyAutoCSS.prototype.typography` mirrors PHP — builds `:root { ... }` strings into `that.css_root[device]`, flushed before `that.css[device]`. CSS injects into `<style id="customify-style-inline-css">` in the preview iframe head; Google Fonts URL into `<link id="customify-google-font-css">`. Both unchanged from pre-`0.5.0`.
- **Block editor**: [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) imports the frontend `_base.scss`, `_blocks.scss`, and `_widgets.scss` — the same consumers run inside `.editor-styles-wrapper`. `Customify_Editor::load_style()` calls `render_css($all_config_fields)` so every typography setting's `:root` vars land in the editor canvas via the editor ajax stylesheet. `Customify_Editor::css()` (lines 121-127) still rewrites typography field selectors to `.editor-styles-wrapper .wp-block-post-title` for legacy mode — dormant no-op in vars mode.

---

## 5. Design decisions

### 5.1 Decision: vars only, no parallel emission

- **Chose**: Always emit `:root` vars for Global Typography fields. Per-component fields stay selector-scoped (no SCSS consumers exist for them — see §5.3).
- **Rejected**: A global `customify/typography/legacy_output` kill switch to revert all typography to selector-scoped output.
- **Rejected**: Emit BOTH `:root { --var: ... }` and `{selector} { property: var(--var) }` in PHP.
- **Reason**: Single source of truth. Child themes and JS read the same `:root` variable name regardless of which selector binds it. The kill switch was originally designed to coordinate with the Pro plugin during rollout; since the Pro plugin doesn't reference these vars at all, the escape hatch added complexity without buying anything.

### 5.2 Decision: prefix `--customify-typo-` (not `--cfy-`)

- **Chose**: Extend the existing `--customify-*` color-token namespace with an explicit `typo` infix.
- **Rejected**: Shorter `--cfy-typo-` or `--cfy-`. Original plan in memory `typography-vars-migration` used `--cfy-`.
- **Reason**: Consistency with the color token convention already in [`src/frontend/scss/`](../src/frontend/scss/) (e.g. `var(--customify-body-text, #686868)`). One namespace for the whole theme is easier to document and override.

### 5.3 Decision: vars limited to the Global Typography panel

- **Chose**: Only fields whose setting `name` starts with `global_typography_` (the 11 Global settings — Base, Site Title & Tagline, Heading family) emit `:root` vars. Per-component typography (header builder items, footer copyright, blog read-more, breadcrumb, WC cart — 9 fields) stays in legacy selector-scoped output.
- **Rejected**: Emit vars for ALL 20 fields, with SCSS consumers everywhere.
- **Reason**: Per-component selectors are dynamic (`a.item--button`, `.builder-header-cart-item`, `#blog-posts .entry-readmore a`) and often paired with **hardcoded** field prefixes — there's no per-instance setting, so a single var would apply uniformly across every button / cart / read-more on the page. Mixing this with the Global panel's "single source of truth" promise creates confusion: users would expect their per-button typography to differ per instance, but the var-mode plumbing can't deliver that. Legacy emit keeps the existing per-component selectors authoritative without inventing instance vars.

### 5.4 Decision: `:root` scope for the Global vars (no per-instance scoping)

- **Chose**: Every Global Typography var lives at `:root`. Var name encodes the semantic role (`base-p`, `heading-h1`, `site-tt-title`); the SCSS consumer selector decides where it applies.
- **Rejected**: Scoping vars to per-section selectors.
- **Reason**: The 11 Global fields target site-wide elements (body, headings, site title) — there's no "per-instance" of `<body>` or `<h1>`. `:root` is the natural home.

### 5.5 Decision: SCSS owns selectors; PHP owns var values (for global fields)

- **Chose**: For the Global fields, `$field['selector']` is **ignored** by the vars emit path. SCSS is the only layer that decides which selectors consume which vars.
- **Rejected**: PHP emits `{$field['selector']} { property: var(--customify-typo-…, …) }` alongside `:root` vars.
- **Reason**: SCSS already had partial typography rules on the global selectors (body, h1-h6, widget title, site title). Having PHP also emit selector-scoped rules in vars mode would duplicate work and complicate the cascade. With SCSS as sole consumer for vars-mode fields, the SCSS layer is the canonical "where typography applies" — easier to audit, easier to override.

### 5.6 Decision: keep `setup_font()` side effects unconditional

- **Chose**: `setup_font()` runs regardless of legacy-vs-vars mode. Google Fonts URL building, WP Font Library `@font-face` tracking, and theme.json font registry stay intact.
- **Rejected**: Skip font tracking in vars mode (e.g. assume CSS vars alone are enough).
- **Reason**: CSS `font-family: var(--…, fallback)` references a family by NAME — the browser still needs the `@font-face` declaration (Library) or external stylesheet (`<link href="fonts.googleapis.com/…">`) to actually load the font. Skipping `setup_font()` would silently break Google Fonts and WP Font Library on every site that uses them.

---

## 6. Adding / extending

### 6.1 Add a typography consumer for an existing field

For a typography Customizer field that already has an SCSS rule with literal values (font-size, font-weight, etc.) — replace the literals with `var(...)` calls:

```scss
// Before
.my-component {
    font-size: 14px;
    font-weight: 500;
}

// After
.my-component {
    font-size: var(--customify-typo-{semantic}-font-size, 14px);
    font-weight: var(--customify-typo-{semantic}-font-weight, 500);
}
```

`{semantic}` derives from the field's setting `name` per the strip rule in §4.1.

### 6.2 Add a typography consumer for a fresh selector

For an SCSS rule with NO existing typography literals (only structural CSS) — use the mixin so all 8 properties wire up at once with safe no-override fallbacks:

```scss
.my-fresh-component {
    margin-top: 1em;                       // structural
    @include customify-typography('my-semantic-name');
}
```

To limit which properties wire (e.g. only family / size):

```scss
@include customify-typography('my-semantic-name', $props: (font-family, font-size));
```

To override the fallback for a specific property (e.g. theme-default font-weight should be 600 instead of `inherit`):

```scss
@include customify-typography('my-semantic-name', $fallbacks: (font-weight: 600));
```

### 6.3 Register a new typography field

See [`SPEC-customizer.md §5.3`](SPEC-customizer.md) — register a `'type' => 'typography'` field with `'css_format' => 'typography'`. The generator routes it through the same pipeline. **Emit mode is decided by the setting `name`:** if `name` starts with `global_typography_` the field emits `:root` vars (and you must add an SCSS consumer at the matching selector — see §6.2); otherwise it emits selector-scoped CSS at `$field['selector']` exactly as pre-`0.5.0`.

### 6.4 Opt a per-component field into vars mode

If you want a per-component typography field (e.g. `header_button_typography`) to emit vars instead of legacy CSS:

1. Add the SCSS consumer at the field's selector — see §6.1 / §6.2.
2. Filter the field through:

   ```php
   add_filter( 'customify/typography/field_uses_vars', function ( $uses_vars, $field ) {
       if ( 'header_button_typography' === ( $field['name'] ?? '' ) ) {
           return true;
       }
       return $uses_vars;
   }, 10, 2 );
   ```

Without step 1 the var lands at `:root` but no selector consumes it — the user's saved value silently no-ops on the frontend.

---

## 7. Hooks & filters catalog

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/typography/field_uses_vars` | filter | `(bool $uses_vars, array $field)` (default = setting `name` starts with `global_typography_`) | **Per-field route override.** Return `false` to force a Global field through selector-scoped emit, or `true` to opt a per-component field into vars mode. The vars-mode field still needs an SCSS consumer at the matching selector — see §6.4. |
| `customify/customizer/auto_css` | filter | `(array $devices_css, $field, Customify_Customizer_Auto_CSS $instance)` | Unchanged — runs before the route decision. In vars mode, modifying a CSS line here changes the var value that gets emitted; in selector-scoped mode, it changes the CSS line. |
| `customify/auto-css` | filter | `string $css` — final assembled CSS | Unchanged. Receives the `:root { ... }` blocks ahead of any selector-scoped CSS in vars mode. |

---

## 8. Known issues / edge cases

### Issue #1 — Per-component typography stays in selector-scoped mode

This is **intentional**. The 9 per-component fields (header menu/button/HTML/search, footer copyright, blog read-more, breadcrumb, WC cart) all emit selector-scoped CSS at their field `selector` — byte-identical to pre-`0.5.0`. Sites opting them into vars mode must add SCSS consumers and toggle `customify/typography/field_uses_vars` — see §6.4. Don't simply flip the filter without adding the consumer, or the user's saved value silently no-ops.

### Issue #2 — Naming collision: `_font_size` strip on a setting named `*_font_size`

The strip rule trims `_font_size` from the suffix to handle the misleadingly-named `search_box_font_size` / `search_icon_modal_font_size` typography fields. If a future setting genuinely IS a single `font_size` field named `something_font_size`, its var would also lose the `_font_size` suffix. Today no such setting exists. If one is added, prefer a non-conflicting name (e.g. `something_size`) or update `typography_var_name()` to special-case it.

---

## 9. Pro plugin handoff

Pro typography fields all use `'css_format' => 'typography'` and route through the theme's `typography()` generator — no separate Pro emission path. They follow the same `typography_field_uses_vars()` gate: Pro fields whose setting `name` starts with `global_typography_` would emit `:root` vars; everything else falls through to selector-scoped CSS at `$field['selector']`.

Pro's own inline-CSS callsites (header builder items, scrolltop, blog) write literal CSS at component selectors — no `--customify-typo-*` references today. The migration to read theme typography vars from Pro's SCSS is a separate task tracked in [`SPEC-pro-integration.md`](SPEC-pro-integration.md).

---

## 10. Troubleshooting

| Symptom | Likely cause |
|---|---|
| Customizer setting saves but nothing changes on frontend | Setting's CSS field schema disables the property (e.g. `base_heading` only enables family + weight — see [`typography.php`](../inc/customizer/configs/typography.php)). Verify via the field config. |
| Setting changes apply on frontend but not in block editor | Editor canvas selector differs from frontend. Add an editor-specific consumer rule in [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) targeting `.editor-styles-wrapper {your-selector}`. |
| Per-device font-size value ignored on smaller screen | The SCSS consumer's `@include for_device(tablet) { ... }` block sets a literal value AFTER the base rule, winning by source order. Convert the responsive block to also use `var(..., breakpoint-fallback)` — see h1/h2 in `_base.scss` for the pattern. |
| Google Fonts URL missing | `setup_font()` skipped. The vars-mode emit path MUST still call `setup_font()` unconditionally — see §4.3 step 2. |

---

## 11. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Override a typography value site-wide | Child theme CSS: `:root { --customify-typo-h1-font-size: 3rem; }` |
| Override at a specific page | `body.page-id-42 { --customify-typo-base-p-font-size: 18px; }` |
| Inspect the resolved var in the browser | DevTools → `<html>` → Computed → search `--customify-typo-` |
| Get the var name for a setting programmatically | `Customify_Customizer_Auto_CSS::get_instance()->typography_var_name( $setting, $property );` |

---

## 12. Where to look next

**PHP**
- [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) — generator + helpers
- [`inc/customizer/class-customizer.php`](../inc/customizer/class-customizer.php) — JS localization pipe
- [`inc/admin/editor.php`](../inc/admin/editor.php) — block editor sync

**JavaScript**
- [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) — live preview mirror

**SCSS**
- [`src/frontend/scss/utils/_mixins.scss`](../src/frontend/scss/utils/_mixins.scss) — `customify-typography` mixin
- [`src/frontend/scss/base/_base.scss`](../src/frontend/scss/base/_base.scss) — heaviest consumer

**Related specs**
- [`SPEC-customizer.md`](SPEC-customizer.md) §5.3 — typography control type registration
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) — sibling `--customify-*` namespace at `:root`
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — pending Pro version-gate helper

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) — 2-arg `var()` rule, English-only, 30k-sites policy
- [`api-reference.md`](api-reference.md) §2.2 — filter signatures
