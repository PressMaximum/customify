# SPEC — Typography (CSS Variables)

Customify renders every typography Customizer setting (font family, size, weight, line-height, letter-spacing, text-decoration, text-transform, font-style) as **`:root { --customify-typo-*: value }` CSS custom properties** instead of selector-scoped literal CSS. SCSS layer consumes the vars via `var(--customify-typo-…, fallback)` at the same selectors PHP previously targeted. Landed in theme `0.5.0` on branch `claude/wonderful-goldberg-556f83`.

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
| Customizer Typography panel + per-component typography fields | User-facing settings (20 registered fields — see [`SPEC-customizer.md §5.3`](SPEC-customizer.md)) |
| `theme_mod` storage (unchanged) | Persistent typography values per setting; per-device map for `font_size` and `line_height` |
| `Customify_Customizer_Auto_CSS::typography()` | PHP emit — produces `:root { --customify-typo-…: value }` blocks (or legacy selector-scoped CSS if filter on) |
| `CustomifyAutoCSS.prototype.typography` (`auto-css.js`) | JS mirror for Customizer live preview |
| `src/frontend/scss/*` consumers | Read vars via `var(--customify-typo-…, fallback)` at the relevant selectors |
| Block editor canvas | Inherits via [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) which imports the frontend `_base`/`_blocks`/`_widgets` partials |

**Default mode is vars.** Sites can opt back into selector-scoped literal CSS via the filter `customify/typography/legacy_output` — see §7.

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) | `typography()` ~1088-1228, `code_to_root_vars()` ~1230-1265, `typography_var_name()` ~1061-1086, `legacy_typography_enabled()` ~1038-1048, `$css_root` bucket + `render_css()` flush | PHP generator + helpers |
| [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) | Helpers ~73-160, `typography()` rewrite ~1382-1408, `css_root` flush ~196-210 | JS live preview mirror |
| [`inc/customizer/class-customizer.php`](../inc/customizer/class-customizer.php) | ~245-265 `wp_localize_script` payload | Pipes the legacy-filter resolved value to JS as `Customify_Preview_Config.legacy_typography_output` |
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

Global panel — [`inc/customizer/configs/typography.php`](../inc/customizer/configs/typography.php):

| Setting name | Semantic name | Default selector |
|---|---|---|
| `global_typography_base_p` | `base-p` | `body` |
| `global_typography_base_heading` | `base-heading` | `h1..h6, .h1..h6` (family/weight only) |
| `global_typography_base_widget_title` | `base-widget-title` | `.site-content .widget-title` |
| `global_typography_site_tt_title` | `site-tt-title` | `.site-branding .site-title, …` |
| `global_typography_site_tt_desc` | `site-tt-desc` | `.site-branding .site-description` |
| `global_typography_heading_h1` … `h6` | `heading-h1` … `heading-h6` | `.entry-content h1, .wp-block h1, .entry-single .entry-title` (h1) and similar per-level |

Per-component (9):

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

1. Strip prefix `global_typography_`
2. Strip the **first matching** suffix: `_modal_font_size`, `_typography`, `_font_size`, `_typo`
3. Replace `_` with `-`

CSS properties (already kebab): `font-family`, `font-style`, `font-weight`, `font-size`, `line-height`, `letter-spacing`, `text-decoration`, `text-transform`.

Worked examples:

| Setting | Property | Resulting var |
|---|---|---|
| `global_typography_base_p` | font-family | `--customify-typo-base-p-font-family` |
| `global_typography_heading_h1` | font-size | `--customify-typo-heading-h1-font-size` |
| `header_button_typography` | font-weight | `--customify-typo-header-button-font-weight` |
| `blog_post_more_typography` | letter-spacing | `--customify-typo-blog-post-more-letter-spacing` |
| `search_icon_modal_font_size` | font-family | `--customify-typo-search-icon-font-family` |

PHP and JS helpers must stay in lockstep — the JS mirror is at `CustomifyAutoCSS.prototype.typography_var_name`.

### 4.2 Output shape (vars mode — default)

Non-device properties land in the `all` bucket at `:root`:

```css
:root {
    --customify-typo-base-p-font-family: "Inter";
    --customify-typo-base-p-font-weight: 400;
    --customify-typo-heading-h1-font-family: "Inter";
    --customify-typo-heading-h1-font-weight: 700;
    --customify-typo-heading-h1-font-size: 2.5rem;
    --customify-typo-heading-h1-line-height: 1.2;
}
```

Device-scoped properties (`font_size`, `line_height`) wrap in the matching media query:

```css
@media screen and (max-width: 1024px) {
    :root {
        --customify-typo-heading-h1-font-size: 1.8rem;
    }
}
@media screen and (max-width: 568px) {
    :root {
        --customify-typo-heading-h1-font-size: 1.2rem;
    }
}
```

### 4.3 Render flow

1. `Customify_Customizer_Auto_CSS::typography($field)` is called per field by `loop_fields()`.
2. `setup_font()` runs **regardless of mode** — it populates `$this->fonts`, `$this->library_fonts`, `$this->theme_fonts` for Google Fonts URL / WP Font Library / theme.json `@font-face` emission. **These side effects must always run.**
3. Non-device props built into `$code` (literal `property: value;` lines); device props into `$devices_css[device]` (same shape).
4. `apply_filters('customify/customizer/auto_css', $devices_css, $field, $this)` — contract unchanged.
5. If `legacy_typography_enabled()` returns `true` → emit selector-scoped CSS into `$this->css[device]` (legacy path; identical to pre-`0.5.0` output).
6. Otherwise (default) → `code_to_root_vars()` parses each `property: value;` line, derives the var name via `typography_var_name()`, and appends to `$this->css_root[device]`.
7. `render_css()` flushes `$this->css_root` first (each device wrapped in its media query template), then `$this->css`.
8. Final CSS lands inline via `wp_add_inline_style('customify-style', ...)` — handle unchanged.

### 4.4 SCSS consumer placement

PHP no longer controls which selector receives typography in vars mode (vars are global at `:root`). **SCSS owns the selectors** — the same ones PHP previously emitted.

| Setting group | Primary SCSS file |
|---|---|
| `base_p`, `base_heading`, `base_widget_title` (group rules) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) (~58 typography hits; heaviest file) |
| `heading_h1` … `h4` (+ responsive `@include for_device`) | [`base/_base.scss`](../src/frontend/scss/base/_base.scss) |
| `site_tt_title`, `site_tt_desc` | [`header/builder_items/_logo_site_identity.scss`](../src/frontend/scss/header/builder_items/_logo_site_identity.scss) |
| `primary_menu_typography` | [`header/builder_items/_navigation.scss`](../src/frontend/scss/header/builder_items/_navigation.scss) |
| `header_button_typography` | [`header/builder_items/_button.scss`](../src/frontend/scss/header/builder_items/_button.scss) |
| `header_html_typo` | [`header/builder_items/_html_1.scss`](../src/frontend/scss/header/builder_items/_html_1.scss) |
| `search_box_font_size`, `search_icon_modal_font_size` | [`header/builder_items/_search.scss`](../src/frontend/scss/header/builder_items/_search.scss) |
| `breadcrumb_typo` | [`header/_header_builder_common.scss`](../src/frontend/scss/header/_header_builder_common.scss) (`.page-breadcrumb`) |
| `footer_copyright_typography` | [`footer/_footer-common.scss`](../src/frontend/scss/footer/_footer-common.scss) |
| `base_widget_title` | [`widgets/_widgets.scss`](../src/frontend/scss/widgets/_widgets.scss) |
| `blog_post_more_typography` | [`layouts/_blogs.scss`](../src/frontend/scss/layouts/_blogs.scss) (`.readmore-button`) |
| `wc_cart_typography` | [`compatibility/wc/_wc-cart.scss`](../src/frontend/scss/compatibility/wc/_wc-cart.scss) |

**Two consumer patterns:**

- **Direct `var(...)` calls** — used where SCSS already had typography literals. Preserves breakpoint-specific fallbacks (e.g. h1 `font-size` differs per `@include for_device`):
  ```scss
  h1, .h1 {
      font-size: var(--customify-typo-heading-h1-font-size, 2.42em);
      @include for_device(tablet) {
          font-size: var(--customify-typo-heading-h1-font-size, 2.1em);
      }
  }
  ```
- **`customify-typography` mixin** — used where SCSS had no existing literals (tagline, search-box, search-icon, wc-cart, footer copyright builder item). Emits all 8 properties in one call with sane no-override fallbacks (`inherit` / `normal` / `none`):
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

- **Chose**: Default to `:root` vars; gate legacy selector-scoped output behind a filter (`customify/typography/legacy_output`).
- **Rejected**: Emit BOTH `:root { --var: ... }` and `{selector} { property: var(--var) }` in PHP.
- **Reason**: Single source of truth. Child themes and JS read the same `:root` variable name regardless of which selector binds it. Parallel emission would double the inline CSS payload and re-introduce the specificity fight the migration aims to remove.

### 5.2 Decision: prefix `--customify-typo-` (not `--cfy-`)

- **Chose**: Extend the existing `--customify-*` color-token namespace with an explicit `typo` infix.
- **Rejected**: Shorter `--cfy-typo-` or `--cfy-`. Original plan in memory `typography-vars-migration` used `--cfy-`.
- **Reason**: Consistency with the color token convention already in [`src/frontend/scss/`](../src/frontend/scss/) (e.g. `var(--customify-body-text, #686868)`). One namespace for the whole theme is easier to document and override.

### 5.3 Decision: `:root` scope always; per-instance vars share a name

- **Chose**: Every typography var lives at `:root`. The setting name encodes the semantic role (`header-button`, `wc-cart`); the SCSS consumer selector decides where it applies.
- **Rejected**: Scoping vars to the field's `selector` (e.g. `.builder-item--header_button_1 { --customify-typo-header-button-font-size: ... }`) so per-instance overrides cascade.
- **Reason**: Theme-side typography settings use **hardcoded** prefixes (`header_button`, `primary_menu`, `wc_cart`) — there's no instance ID in the setting name. Per-instance differentiation would require scoping in PHP AND per-instance SCSS rules, which SCSS (static, pre-compiled) cannot match for unknown future instances. The tradeoff: all `.customify-builder-btn` elements share one var → uniform typography across multiple button instances. Documented as a known issue (§8).

### 5.4 Decision: SCSS owns selectors; PHP owns var values

- **Chose**: Post-migration, `$field['selector']` is **ignored** by the vars emit path. SCSS is the only layer that decides which selectors consume which vars.
- **Rejected**: PHP emits `{$field['selector']} { property: var(--customify-typo-…, …) }` alongside `:root` vars.
- **Reason**: SCSS already had partial typography rules on these selectors (body, h1-h6, widget title). Having PHP also emit selector-scoped rules in vars mode would duplicate work and complicate the cascade. With SCSS as sole consumer, the SCSS layer is the canonical "where typography applies" — easier to audit, easier to override.

### 5.5 Decision: keep `setup_font()` side effects unconditional

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

See [`SPEC-customizer.md §5.3`](SPEC-customizer.md) — register a `'type' => 'typography'` field with `'css_format' => 'typography'`. The generator routes it through the same pipeline; vars emit automatically. The semantic name follows the strip rule, so prefer setting names that match the rule cleanly (`my_thing_typography`, `my_thing_typo`).

---

## 7. Hooks & filters catalog

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/typography/legacy_output` | filter | `bool` (default `false`) | Return `true` to opt the site back into selector-scoped literal CSS (pre-`0.5.0` behavior). Resolved server-side; PHP also localizes the value to JS as `Customify_Preview_Config.legacy_typography_output` so the Customizer live preview stays in sync. |
| `customify/customizer/auto_css` | filter | `(array $devices_css, $field, Customify_Customizer_Auto_CSS $instance)` | Unchanged — runs before the bucket split. In vars mode, modifying a CSS line here changes the var value that gets emitted; in legacy mode, it changes the selector-scoped CSS line. |
| `customify/auto-css` | filter | `string $css` — final assembled CSS | Unchanged. Receives the `:root { ... }` blocks ahead of any selector-scoped CSS in vars mode. |

Example — re-enable legacy CSS site-wide:

```php
// functions.php or mu-plugin
add_filter( 'customify/typography/legacy_output', '__return_true' );
```

After applying, the inline CSS reverts to the pre-`0.5.0` shape and any child-theme overrides on the Customizer selectors continue to work as before.

---

## 8. Known issues / edge cases

### Issue #1 — Per-instance items share a typography var

For builder items registered with a **hardcoded prefix** (header_button, primary_menu, search_box, search_icon, wc_cart), every instance of that item type reads the same `--customify-typo-{prefix}-*` var. Sites that customized typography per-instance under the legacy mode (each instance had its own selector-scoped block) get uniform typography across instances after the upgrade.

**Workaround for now**: enable the legacy filter (`customify/typography/legacy_output → true`).

**Long-term fix (out of scope this migration)**: change PHP to emit the var at the field's `selector` scope so CSS cascade gives each instance its own value. Tracked in memory `typography-vars-migration` follow-ups.

### Issue #2 — Sidebar primary menu consumer not yet added

`primary_menu_typography` registers TWO selectors: the desktop nav (`.nav-menu-desktop .primary-menu-ul > li > a`) AND the sidebar variant (`.builder-item-sidebar .primary-menu-sidebar .primary-menu-ul > li > a`). Only the desktop variant has a var consumer in [`_navigation.scss`](../src/frontend/scss/header/builder_items/_navigation.scss). User values still emit at `:root` correctly; sidebar nav just doesn't consume them yet.

**Workaround**: child theme rule:
```css
.builder-item-sidebar .primary-menu-sidebar .primary-menu-ul > li > a {
    font-size: var(--customify-typo-primary-menu-font-size, 0.85em);
    /* …repeat per property as needed */
}
```

### Issue #3 — Theme version 0.5.0 must NOT be tagged until Pro lands the version-gate helper

Customify Pro plans to ship `customify_pro_theme_supports_vars()` that checks `version_compare( customify_theme_version, '0.5.0', '>=' )` and switches Pro's own typography emission to vars mode when true. The helper does not exist yet. If the theme is tagged `0.5.0` while Pro still emits legacy CSS, Pro typography will run alongside the theme's vars — both modes active, double payload, possible cascade conflict.

**Hold the version tag** until the Pro session lands. See [`SPEC-pro-integration.md`](SPEC-pro-integration.md).

### Issue #4 — Naming collision: `_font_size` strip on a setting named `*_font_size`

The strip rule trims `_font_size` from the suffix to handle the misleadingly-named `search_box_font_size` / `search_icon_modal_font_size` typography fields. If a future setting genuinely IS a single `font_size` field named `something_font_size`, its var would also lose the `_font_size` suffix. Today no such setting exists. If one is added, prefer a non-conflicting name (e.g. `something_size`) or update `typography_var_name()` to special-case it.

---

## 9. Pro plugin handoff

Pro typography fields all use `'css_format' => 'typography'` and route through the theme's `typography()` generator — no separate Pro emission path. Once Pro adds `customify_pro_theme_supports_vars()`:

- Pro's own inline-CSS callsites (currently selector-scoped) check the helper and switch to vars mode when the active theme is `>= 0.5.0`.
- Pro's SCSS partials (blog, header-footer-items, scrolltop per memory `typography-vars-migration`) rewrite to consume `--customify-typo-*` vars with `var(..., fallback)`.
- Pro fields automatically gain the same `customify/typography/legacy_output` escape hatch via the theme generator.

This is **public API** under the 30k-sites rule. See [`SPEC-pro-integration.md`](SPEC-pro-integration.md).

---

## 10. Troubleshooting

| Symptom | Likely cause |
|---|---|
| Customizer setting saves but nothing changes on frontend | Setting's CSS field schema disables the property (e.g. `base_heading` has font_size disabled — only family/weight emit). Verify via the field config. |
| Setting changes apply on frontend but not in block editor | Editor canvas selector differs from frontend. Add an editor-specific consumer rule in [`src/backend/admin/scss/editor.scss`](../src/backend/admin/scss/editor.scss) targeting `.editor-styles-wrapper {your-selector}`. |
| Per-device font-size value ignored on smaller screen | The SCSS consumer's `@include for_device(tablet) { ... }` block sets a literal value AFTER the base rule, winning by source order. Convert the responsive block to also use `var(..., breakpoint-fallback)` — see h1/h2 in `_base.scss` for the pattern. |
| Google Fonts URL missing after migration | `setup_font()` skipped. The vars-mode emit path MUST still call `setup_font()` unconditionally — see §4.3 step 2. |
| Live preview iframe doesn't reflect typography changes | Check `Customify_Preview_Config.legacy_typography_output` in the browser console. If `undefined`, [`class-customizer.php`](../inc/customizer/class-customizer.php) localization didn't run; verify the typography fields are enqueued with `customify-customizer-auto-css`. |
| Sites breaking on `0.5.0` upgrade (child theme override stopped working) | Suggest the legacy filter as a stopgap:<br>`add_filter( 'customify/typography/legacy_output', '__return_true' );` |

---

## 11. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Override a typography value site-wide | Child theme CSS: `:root { --customify-typo-heading-h1-font-size: 3rem; }` |
| Override at a specific page | `body.page-id-42 { --customify-typo-base-p-font-size: 18px; }` |
| Re-enable pre-`0.5.0` CSS output | `add_filter( 'customify/typography/legacy_output', '__return_true' );` |
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
