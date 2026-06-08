# SPEC — Typography (CSS Variables)

Customify renders typography in **two tiers**. **Foundation roles** — Base body (`global_typography_base_p` → `body`), the shared Heading family/weight (`global_typography_base_heading` → `heading`), and the h1–h6 type scale (`global_typography_heading_h1..h6` → `h1`..`h6`) — emit **`:root { --customify-typo-*: value }` CSS custom properties** (8 settings → 18 emitted tokens). The SCSS layer consumes those tokens via `var(--customify-typo-…, fallback)` at the matching selectors. **Leaf roles** (site title, tagline, widget title) and **per-component typography** (header builder items, footer copyright, blog read-more, breadcrumb, WC cart) mint NO token of their own — they emit selector-scoped **literal** CSS at the field selector. Leaf SCSS rules still *reuse* a foundation token where it makes sense (site title and widget title default their `font-family` to `var(--customify-typo-heading-font-family, inherit)`; the tagline `.site-description` inherits the body font and carries no typography var consumer at all). Landed in theme `0.4.19`.

The scope boundary is the field's setting `name`: tokens emit only for the **8 foundation names** in `Customify_Customizer_Auto_CSS::TYPO_VAR_MAP` — `typography_field_uses_vars()` returns `true` for exactly those. The 3 leaf settings that *also* start with `global_typography_` (`global_typography_site_tt_title`, `global_typography_site_tt_desc`, `global_typography_base_widget_title`) do **not** emit tokens. Everything else falls through to the selector-scoped literal emit path. The gate keys off the explicit `TYPO_VAR_MAP` rather than a `section` id or a `name` prefix because the Typography IA was flattened into a single `typography_panel` section (so the section id no longer encodes which group a field belongs to) and because not every `global_typography_` setting is a foundation role.

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
| Customizer Typography panel + per-component typography fields | 20 registered fields (11 in the global panel, 9 per-component) — see [`SPEC-customizer.md §5.3`](SPEC-customizer.md) |
| `theme_mod` storage (unchanged) | Persistent typography values per setting; per-device map for `font_size` and `line_height` |
| `Customify_Customizer_Auto_CSS::typography()` | PHP emit — `:root` tokens for the 8 foundation settings, selector-scoped literal CSS for the 3 leaf-global + 9 per-component settings (route decided by `typography_field_uses_vars()` against `TYPO_VAR_MAP`) |
| `CustomifyAutoCSS.prototype.typography` (`auto-css.js`) | JS mirror for Customizer live preview |
| `src/frontend/scss/*` consumers | Read tokens via `var(--customify-typo-…, fallback)` at the relevant selectors — for the 8 foundation settings. Leaf selectors (site title, widget title) reuse the heading token; they own no token of their own |
| Block editor canvas | Inherits via [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) which imports the frontend `_base`/`_blocks`/`_widgets` partials |

**Default mode is tokens for the 8 foundation settings only.** Everything else emits selector-scoped literal CSS. One filter knob:
- `customify/typography/field_uses_vars` (default = setting `name` is one of the 8 foundation roles in `TYPO_VAR_MAP`) — per-field override. Return `false` to force a foundation field through literal emit, or `true` to opt a non-foundation field into tokens (requires adding an SCSS consumer at the matching selector). See §7.

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) | `typography()`, `code_to_root_vars()`, `typography_var_name()`, `typography_field_uses_vars()`, `$css_root` bucket + `render_css()` flush | PHP generator + helpers |
| [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) | `typography()`, `code_to_root_vars()`, `typography_var_name()`, `typography_field_uses_vars()`, `TYPO_VAR_MAP`, `css_root` flush | JS live preview mirror |
| ~10 frontend SCSS partials (see §4) | inline | Token consumers — direct 2-arg `var(...)` calls at the foundation selectors |
| [`inc/admin/editor.php`](../inc/admin/editor.php) | `Customify_Editor::css()`, `load_style()` | Editor inline CSS; both call `Customify_Customizer_Auto_CSS::render_css()` which flushes `$css_root` automatically |

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

#### Foundation / tokenized (8 settings, file: [`inc/customizer/configs/typography.php`](../inc/customizer/configs/typography.php))

These are the ONLY settings that emit `:root { --customify-typo-…: value }` at frontend — the 8 entries in `Customify_Customizer_Auto_CSS::TYPO_VAR_MAP`. SCSS token consumers live in `_base.scss`. Per-role field gating (see §6, and the `fields` arrays in `typography.php`) restricts `base_heading` to family + weight and `heading_h1..h6` to font-size + line-height, so the 8 settings expand to **18 emitted tokens**.

The `selector` column below is the field's configured `selector` (from `typography.php`). For tokenized fields PHP **ignores** that selector — SCSS owns the consumer selector — but it stays the field's identity and drives where literal cosmetic-property CSS lands when set (see §4.3 step 6).

| Setting name | Semantic name (token segment) | Field `selector` | SCSS token consumer |
|---|---|---|---|
| `global_typography_base_p` | `body` | `body` | `body` in `_base.scss` |
| `global_typography_base_heading` | `heading` (family + weight only) | `h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6` | the shared heading rule in `_base.scss` |
| `global_typography_heading_h1` … `h6` | `h1` … `h6` (font-size + line-height only) | `.entry-content {tag}, .wp-block {tag}` (h1 also adds `, .entry-single .entry-title`) | `h1, .h1` … `h6, .h6` in `_base.scss` (with `@include for_device` responsive blocks for h1/h2) |

#### Leaf / literal — global panel (3 settings)

These live in the `typography_panel` section and start with `global_typography_`, but they are **not** in `TYPO_VAR_MAP`, so `typography_field_uses_vars()` returns `false` for them. They mint **no token of their own** — when the user sets a value the generator emits selector-scoped **literal** CSS at the field's `selector`. Their SCSS default font-family *reuses* the foundation heading token (site title and widget title), or simply inherits the body font (tagline).

| Setting name | Field `selector` | Default font-family source |
|---|---|---|
| `global_typography_site_tt_title` | `.site-branding .site-title, .site-branding .site-title a` | reuses `var(--customify-typo-heading-font-family, inherit)` |
| `global_typography_site_tt_desc` (tagline) | `.site-branding .site-description` | none — `.site-description` has no typography var consumer; inherits the body font |
| `global_typography_base_widget_title` | `.site-content .widget-title` | reuses `var(--customify-typo-heading-font-family, inherit)` |

All 11 panel settings (8 foundation + 3 leaf) live in the flattened `typography_panel` section (the former Base / Site Title & Tagline / Content sub-sections are now `heading` separators inside it).

#### Selector-scoped / literal — per-component (9 settings)

These emit selector-scoped literal CSS at the field's `selector`. No SCSS token consumers exist for these — adding one and toggling `customify/typography/field_uses_vars` for the field is the opt-in path.

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

**Semantic name** is resolved by the PHP helper `Customify_Customizer_Auto_CSS::typography_var_name()` in two stages:

1. **Primary path — explicit map.** If the setting `name` is one of the 8 foundation roles in `self::TYPO_VAR_MAP`, the semantic segment is the mapped value directly (`global_typography_base_p` → `body`, `global_typography_base_heading` → `heading`, `global_typography_heading_h1..h6` → `h1`..`h6`). This is how every token actually emitted today is named.
2. **Fallback path — strip + kebab.** Only reached for a NON-foundation field that a site has opted into tokens via the `customify/typography/field_uses_vars` filter. The helper derives the segment from the setting `name`:
   1. Strip prefix `global_typography_` (if present)
   2. Strip the **first matching** suffix: `_modal_font_size`, `_typography`, `_font_size`, `_typo`
   3. Replace `_` with `-`

CSS properties (already kebab): `font-family`, `font-style`, `font-weight`, `font-size`, `line-height`, `letter-spacing`, `text-decoration`, `text-transform`.

Worked examples (only the 8 foundation fields emit tokens today — the first two rows; the fallback rows show what the helper would produce if a site opts a per-component field in via `customify/typography/field_uses_vars`):

| Setting | Property | Path | Resulting var |
|---|---|---|---|
| `global_typography_base_p` | font-family | map | `--customify-typo-body-font-family` |
| `global_typography_heading_h1` | font-size | map | `--customify-typo-h1-font-size` |
| `header_button_typography` (literal by default) | font-weight | fallback | `--customify-typo-header-button-font-weight` |
| `search_icon_modal_font_size` (literal by default) | font-family | fallback | `--customify-typo-search-icon-font-family` |

PHP and JS helpers must stay in lockstep — the JS mirror is `CustomifyAutoCSS.prototype.typography_var_name`, and both read the shared `TYPO_VAR_MAP` (PHP const / JS `var`).

### 4.2 Output shape (tokens — default for foundation fields)

Non-device properties land in the `all` bucket at `:root`:

```css
:root {
    --customify-typo-body-font-family: "Inter";
    --customify-typo-body-font-weight: 400;
    --customify-typo-heading-font-family: "Inter";
    --customify-typo-heading-font-weight: 700;
    --customify-typo-h1-font-size: 2.5rem;
    --customify-typo-h1-line-height: 1.2;
}
```

Note `base_heading` emits `--customify-typo-heading-font-family` / `--customify-typo-heading-font-weight` (family + weight only — the per-role gate drops the rest), and `heading_h1..h6` emit `--customify-typo-h{n}-font-size` / `--customify-typo-h{n}-line-height` (the gate drops family/weight). That gating is why 8 settings yield 18 tokens, not the full Cartesian product.

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
5. **Route decision** — if `typography_field_uses_vars($field)` returns `false` (i.e. the field's setting `name` is NOT one of the 8 foundation roles in `TYPO_VAR_MAP`), take the early-return literal path: emit selector-scoped CSS at `$field['selector']` into `$this->css[device]` / `$this->css['all']`. Leaf-global and per-component fields go here.
6. Otherwise (foundation field) → **per-property split**. Core props (font-family, font-weight, font-size, line-height) go through `code_to_root_vars()`, which parses each `property: value;` line, derives the token name via `typography_var_name()`, and **appends the raw `--var: value;` lines** to `$this->css_root[device]`. Cosmetic props (letter-spacing, text-decoration, text-transform, font-style) are NOT tokenized — they emit as selector-scoped literal CSS at `$field['selector']` only when the user set them. Multiple fields accumulate into the same `:root` bucket.
7. `render_css()` wraps each non-empty `$this->css_root[device]` in a **single** `:root { ... }` block and the matching media query, then flushes `$this->css[device]` afterwards.
8. Final CSS lands inline via `wp_add_inline_style('customify-style', ...)` — handle unchanged.

### 4.4 SCSS consumer placement (foundation tokens only)

For the 8 foundation settings, **SCSS owns the selector** — tokens at `:root` don't apply until something consumes them. Leaf-global and per-component fields emit selector-scoped literal CSS directly from PHP, so they have **no SCSS token consumer** of their own.

| Foundation token | Primary SCSS file |
|---|---|
| `body` (`base_p`) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) — the `body` rule |
| `heading` (`base_heading`, family + weight) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) — the shared `h1..h6, .h1..h6` rule |
| `h1` … `h6` (`heading_h1..h6`, font-size + line-height; with `@include for_device` responsive for h1/h2) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) — the per-level `h1, .h1` … `h6, .h6` rules |

**Leaf reuse note.** Leaf selectors don't mint their own tokens but two of them *reuse* the foundation heading token as their default font-family:

- `.site-branding .site-title` (and `.widget-title` / `.site-content .widget-title`) default to `font-family: var(--customify-typo-heading-font-family, inherit)` — see [`header/builder_items/_logo_site_identity.scss`](../src/frontend/scss/header/builder_items/_logo_site_identity.scss) and [`widgets/_widgets.scss`](../src/frontend/scss/widgets/_widgets.scss). When the user sets Site Title / Widget Title typography, PHP emits selector-scoped literal CSS that overrides these defaults.
- The tagline `.site-description` carries **no** typography var consumer (only `margin`); it inherits the body font and is overridden by the literal CSS that the Tagline field emits when set.

**Consumer pattern — direct 2-arg `var(...)`.** Preferred for selectors that already had typography literals. Preserves breakpoint-specific fallbacks (e.g. h1 `font-size` differs per `@include for_device`):

```scss
h1, .h1 {
    font-size: var(--customify-typo-h1-font-size, 2.42em);
    @include for_device(tablet) {
        font-size: var(--customify-typo-h1-font-size, 2.1em);
    }
}
```

For a fresh selector with no existing literals, hand-write one 2-arg `var()` per property with a safe no-override fallback (`inherit` / `normal` / `none`):

```scss
.my-component {
    font-family: var(--customify-typo-my-name-font-family, inherit);
    font-weight: var(--customify-typo-my-name-font-weight, inherit);
    font-size:   var(--customify-typo-my-name-font-size, inherit);
    line-height: var(--customify-typo-my-name-line-height, inherit);
}
```

Every `var(...)` must be 2-arg (1-arg banned per [`../AGENTS.md`](../AGENTS.md)).

### 4.5 Live preview & block editor

- **Customizer live preview**: `CustomifyAutoCSS.prototype.typography` mirrors PHP — builds `:root { ... }` strings into `that.css_root[device]`, flushed before `that.css[device]`. CSS injects into `<style id="customify-style-inline-css">` in the preview iframe head; Google Fonts URL into `<link id="customify-google-font-css">`.
- **Block editor**: [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) imports the frontend `_base.scss`, `_blocks.scss`, and `_widgets.scss` — the same token consumers run inside `.editor-styles-wrapper`. `Customify_Editor::load_style()` calls `render_css($all_config_fields)` so every foundation setting's `:root` tokens land in the editor canvas via the editor ajax stylesheet. `Customify_Editor::css()` still rewrites typography field selectors to `.editor-styles-wrapper .wp-block-post-title`, but for the foundation fields (`global_typography_base_heading` / `global_typography_heading_h1`) this is a **dormant no-op**: the token path ignores `$field['selector']` (core props emit at `:root`) and no cosmetic prop survives their per-role `fields` gate. The post-title block tracks the heading family/scale because it renders as `<h1>`, which the `_base.scss` `h1` token consumer (imported by `editor.scss`) already covers.

---

## 5. Design decisions

### 5.1 Decision: tokens only, no parallel emission

- **Chose**: Always emit `:root` tokens for the 8 foundation fields. Leaf-global and per-component fields stay selector-scoped literal (no SCSS token consumers exist for them — see §5.3).
- **Rejected**: A global `customify/typography/legacy_output` kill switch to revert all typography to selector-scoped output.
- **Rejected**: Emit BOTH `:root { --var: ... }` and `{selector} { property: var(--var) }` in PHP.
- **Reason**: Single source of truth. Child themes and JS read the same `:root` token name regardless of which selector binds it. The kill switch was originally designed to coordinate with the Pro plugin during rollout; since the Pro plugin doesn't reference these tokens at all, the escape hatch added complexity without buying anything.

### 5.2 Decision: prefix `--customify-typo-` (not `--cfy-`)

- **Chose**: Extend the existing `--customify-*` color-token namespace with an explicit `typo` infix.
- **Rejected**: Shorter `--cfy-typo-` or `--cfy-`. Original plan in memory `typography-vars-migration` used `--cfy-`.
- **Reason**: Consistency with the color token convention already in [`src/frontend/scss/`](../src/frontend/scss/) (e.g. `var(--customify-body-text, #686868)`). One namespace for the whole theme is easier to document and override.

### 5.3 Decision: tokens limited to the 8 foundation roles

- **Chose**: Only the 8 foundation fields in `TYPO_VAR_MAP` (Base body, the shared Heading family/weight, the h1–h6 type scale) emit `:root` tokens. The 3 leaf-global settings (Site Title, Tagline, Widget Title) and the 9 per-component fields (header builder items, footer copyright, blog read-more, breadcrumb, WC cart) emit selector-scoped **literal** CSS. The leaf settings do **not** emit `:root` tokens — but their SCSS defaults reuse the heading token (site title, widget title) so they still track the heading family without minting a token.
- **Rejected**: Emit tokens for ALL 20 fields, with SCSS consumers everywhere.
- **Reason**: Foundation roles are 1-to-many (body text is inherited document-wide; the heading family/scale is shared across every heading), so a `:root` token earns its place. Leaf and per-component selectors are 1-to-1 and often dynamic (`a.item--button`, `.builder-header-cart-item`, `#blog-posts .entry-readmore a`) and paired with **hardcoded** field prefixes — there's no per-instance setting, so a single token would apply uniformly across every button / cart / read-more on the page. Literal emit keeps those selectors authoritative without inventing instance tokens.

### 5.4 Decision: `:root` scope for the foundation tokens (no per-instance scoping)

- **Chose**: Every foundation token lives at `:root`. The token name encodes the semantic role (`body`, `heading`, `h1`); the SCSS consumer selector decides where it applies.
- **Rejected**: Scoping tokens to per-section selectors.
- **Reason**: The foundation fields target site-wide elements (body, headings) — there's no "per-instance" of `<body>` or `<h1>`. `:root` is the natural home.

### 5.5 Decision: SCSS owns selectors; PHP owns token values (for foundation fields)

- **Chose**: For the foundation fields, `$field['selector']` is **ignored** by the token emit path (cosmetic properties aside — see §4.3 step 6). SCSS is the only layer that decides which selectors consume which tokens.
- **Rejected**: PHP emits `{$field['selector']} { property: var(--customify-typo-…, …) }` alongside `:root` tokens.
- **Reason**: SCSS already had partial typography rules on the foundation selectors (body, h1–h6). Having PHP also emit selector-scoped rules in token mode would duplicate work and complicate the cascade. With SCSS as sole consumer for foundation fields, the SCSS layer is the canonical "where typography applies" — easier to audit, easier to override.

### 5.6 Decision: keep `setup_font()` side effects unconditional

- **Chose**: `setup_font()` runs regardless of token-vs-literal mode. Google Fonts URL building, WP Font Library `@font-face` tracking, and theme.json font registry stay intact.
- **Rejected**: Skip font tracking in token mode (e.g. assume CSS tokens alone are enough).
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

`{semantic}` resolves from the field's setting `name` per §4.1 — the `TYPO_VAR_MAP` value for a foundation field, or the strip-rule fallback for an opted-in non-foundation field.

### 6.2 Add a typography consumer for a fresh selector

For an SCSS rule with NO existing typography literals (only structural CSS) — hand-write one 2-arg `var()` per property you want to wire, each with a safe no-override fallback (`inherit` / `normal` / `none`):

```scss
.my-fresh-component {
    margin-top: 1em;                                                   // structural
    font-family: var(--customify-typo-my-semantic-name-font-family, inherit);
    font-weight: var(--customify-typo-my-semantic-name-font-weight, inherit);
    font-size:   var(--customify-typo-my-semantic-name-font-size, inherit);
    line-height: var(--customify-typo-my-semantic-name-line-height, inherit);
}
```

Wire only the properties you need (e.g. just family + size) by omitting the rest. To give a property a different theme default (e.g. font-weight 600 instead of `inherit`), set that as the 2nd `var()` arg: `font-weight: var(--customify-typo-my-semantic-name-font-weight, 600);`. Every `var()` must be 2-arg (1-arg banned per [`../AGENTS.md`](../AGENTS.md)).

### 6.3 Register a new typography field

See [`SPEC-customizer.md §5.3`](SPEC-customizer.md) — register a `'type' => 'typography'` field with `'css_format' => 'typography'`. The generator routes it through the same pipeline. **Emit mode is decided by the setting `name`:** if `name` is one of the 8 foundation roles in `TYPO_VAR_MAP` the field emits `:root` tokens (and you must add an SCSS consumer at the matching selector — see §6.2); otherwise it emits selector-scoped literal CSS at `$field['selector']`. To tokenize a brand-new role you must also add it to `TYPO_VAR_MAP` (PHP const + JS mirror) — the explicit map is the primary naming path.

### 6.4 Opt a non-foundation field into token mode

If you want a non-foundation typography field (e.g. `header_button_typography`) to emit tokens instead of literal CSS:

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

The token name comes from the §4.1 fallback strip rule (the field isn't in `TYPO_VAR_MAP`). Without step 1 the token lands at `:root` but no selector consumes it — the user's saved value silently no-ops on the frontend.

---

## 7. Hooks & filters catalog

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/typography/field_uses_vars` | filter | `(bool $uses_vars, array $field)` (default = setting `name` is one of the 8 foundation roles in `TYPO_VAR_MAP`) | **Per-field route override.** Return `false` to force a foundation field through selector-scoped emit, or `true` to opt a non-foundation field into token mode. The token-mode field still needs an SCSS consumer at the matching selector — see §6.4. |
| `customify/customizer/auto_css` | filter | `(array $devices_css, $field, Customify_Customizer_Auto_CSS $instance)` | Unchanged — runs before the route decision. For a foundation field, modifying a core CSS line here changes the token value that gets emitted; otherwise it changes the literal CSS line. |
| `customify/auto-css` | filter | `string $css` — final assembled CSS | Unchanged. Receives the `:root { ... }` token blocks ahead of any selector-scoped literal CSS. |

---

## 8. Known issues / edge cases

### Issue #1 — Leaf and per-component typography stay in literal mode

This is **intentional**. The 3 leaf-global fields (site title, tagline, widget title) and the 9 per-component fields (header menu/button/HTML/search, footer copyright, blog read-more, breadcrumb, WC cart) all emit selector-scoped literal CSS at their field `selector` — they mint no token of their own. Site title and widget title still *reuse* the foundation heading token (`--customify-typo-heading-font-family`) as their SCSS default; the tagline inherits the body font. Sites opting any of these into token mode must add SCSS consumers and toggle `customify/typography/field_uses_vars` — see §6.4. Don't simply flip the filter without adding the consumer, or the user's saved value silently no-ops.

### Issue #2 — Fallback naming collision: `_font_size` strip on a setting named `*_font_size`

This only affects the **fallback** naming path (§4.1 step 2) — reached for a non-foundation field opted into tokens, since the 8 foundation fields are named explicitly via `TYPO_VAR_MAP`. The fallback strip rule trims `_font_size` from the suffix to handle the misleadingly-named `search_box_font_size` / `search_icon_modal_font_size` typography fields. If a future opted-in setting genuinely IS a single `font_size` field named `something_font_size`, its token would also lose the `_font_size` suffix. Today no such setting is opted in. If one is added, prefer a non-conflicting name (e.g. `something_size`), add it to `TYPO_VAR_MAP`, or update the `typography_var_name()` fallback to special-case it.

---

## 9. Pro plugin handoff

Pro typography fields all use `'css_format' => 'typography'` and route through the theme's `typography()` generator — no separate Pro emission path. They follow the same `typography_field_uses_vars()` gate: a Pro field would emit `:root` tokens only if its setting `name` is one of the foundation roles in `TYPO_VAR_MAP` (none are today) or a site opts it in via `customify/typography/field_uses_vars`; everything else falls through to selector-scoped literal CSS at `$field['selector']`.

Pro's own inline-CSS callsites (header builder items, scrolltop, blog) write literal CSS at component selectors — no `--customify-typo-*` references today. The migration to read theme typography tokens from Pro's SCSS is a separate task tracked in [`SPEC-pro-integration.md`](SPEC-pro-integration.md).

---

## 10. Troubleshooting

| Symptom | Likely cause |
|---|---|
| Customizer setting saves but nothing changes on frontend | Setting's CSS field schema disables the property (e.g. `base_heading` only enables family + weight — see [`typography.php`](../inc/customizer/configs/typography.php)). Verify via the field config. |
| Setting changes apply on frontend but not in block editor | Editor canvas selector differs from frontend. Add an editor-specific consumer rule in [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) targeting `.editor-styles-wrapper {your-selector}`. |
| Per-device font-size value ignored on smaller screen | The SCSS consumer's `@include for_device(tablet) { ... }` block sets a literal value AFTER the base rule, winning by source order. Convert the responsive block to also use `var(..., breakpoint-fallback)` — see h1/h2 in `_base.scss` for the pattern. |
| Google Fonts URL missing | `setup_font()` skipped. The token emit path MUST still call `setup_font()` unconditionally — see §4.3 step 2. |

---

## 11. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Override a typography value site-wide | Child theme CSS: `:root { --customify-typo-h1-font-size: 3rem; }` |
| Override at a specific page | `body.page-id-42 { --customify-typo-body-font-size: 18px; }` |
| Inspect the resolved token in the browser | DevTools → `<html>` → Computed → search `--customify-typo-` |
| Get the token name for a setting programmatically | `Customify_Customizer_Auto_CSS::get_instance()->typography_var_name( $setting, $property );` |

---

## 12. Where to look next

**PHP**
- [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) — generator + helpers
- [`inc/customizer/class-customizer.php`](../inc/customizer/class-customizer.php) — JS localization pipe
- [`inc/admin/editor.php`](../inc/admin/editor.php) — block editor sync

**JavaScript**
- [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) — live preview mirror

**SCSS**
- [`src/frontend/scss/base/_base.scss`](../src/frontend/scss/base/_base.scss) — the foundation token consumers (body + heading + h1–h6)
- [`src/frontend/scss/header/builder_items/_logo_site_identity.scss`](../src/frontend/scss/header/builder_items/_logo_site_identity.scss), [`src/frontend/scss/widgets/_widgets.scss`](../src/frontend/scss/widgets/_widgets.scss) — leaf selectors that reuse the heading token

**Related specs**
- [`SPEC-customizer.md`](SPEC-customizer.md) §5.3 — typography control type registration
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) — sibling `--customify-*` namespace at `:root`
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — pending Pro version-gate helper

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) — 2-arg `var()` rule, English-only, 30k-sites policy
- [`api-reference.md`](api-reference.md) §2.2 — filter signatures
