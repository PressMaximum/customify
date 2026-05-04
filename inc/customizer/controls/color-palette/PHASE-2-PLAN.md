# Preview Colors — Phase 2 Plan

Apply the six panel slots (`--customify-color-base|text|primary|secondary|accent|surface`) to live theme + WooCommerce styling so admins viewing `?preview-colors=1` see the chosen palette in the actual page.

Status: planning only — no code yet.

## Decisions (locked)

| Topic | Decision |
|---|---|
| `!important` | **Forbidden.** Win the cascade by selector match + load order, never by `!important`. |
| Scope | **Theme + WooCommerce only.** Elementor, Gutenberg blocks, other plugins are out of scope for this phase. |
| Dark mode | **Not supported.** No automatic contrast adjustment. If the user picks a low-contrast palette the preview will look bad — that's accepted. |
| RGB component vars | **Yes.** In addition to `--customify-color-<slot>` (hex), emit `--customify-color-<slot>-rgb` (comma-separated R, G, B integers) so the override stylesheet can build `rgba(var(--customify-color-primary-rgb), 0.5)` for translucent overlays the theme already uses. |

## Architecture

A single override stylesheet, generated at build time, enqueued only when `is_active() === true`:

```
src/preview-colors/overrides.scss   →   build/css/frontend/preview-colors-overrides.css
```

Webpack entry name: `frontend/preview-colors-overrides`. The file imports nothing else; it is pure CSS that consumes the live `--customify-color-*` vars set on `document.documentElement` by the JS bundle.

PHP enqueue (in `Customify_Preview_Colors::enqueue()`):

```
build/css/frontend/preview-colors-overrides.min.css
```

— added with `wp_enqueue_style` so it lands in `<head>` and beats cached vs. customizer inline styles via load order. `wp_enqueue_scripts` priority remains `100`; theme styles are at `95`, so our override is always after them.

### Specificity strategy (no `!important`)

The override file mirrors the *exact* SCSS selectors from `src/frontend/scss/` and re-declares only the color-related properties. Because our stylesheet loads after the theme's compiled CSS *and* after the `customify-style` inline block produced by `Customify_Customizer_Auto_CSS::auto_css()`, equal-specificity rules win on cascade order.

For rules where the theme already wins via higher specificity, the override matches that higher specificity verbatim (compound selectors, descendant chains, body class qualifiers) — never `!important`.

## CSS Vars surface

Phase 1 already emits 6 hex vars onto `document.documentElement`:

```
--customify-color-base
--customify-color-text
--customify-color-primary
--customify-color-secondary
--customify-color-accent
--customify-color-surface
```

Phase 2 extends `applyColorVars()` (in `src/preview-colors/preview-colors.js`) to additionally emit the RGB triplet for each slot:

```
--customify-color-base-rgb       /* e.g. "255, 255, 255" */
--customify-color-text-rgb
--customify-color-primary-rgb
--customify-color-secondary-rgb
--customify-color-accent-rgb
--customify-color-surface-rgb
```

Conversion logic (added to `applyColorVars`):

```
hex "#RRGGBB" or "#RGB" -> [r, g, b] integers -> "r, g, b" string
```

Override stylesheet usage:

```
.example { background: rgba(var(--customify-color-primary-rgb), 0.08); }
```

The triplet form works as the input to `rgba()` / `hsl()` syntax. We pick the comma-separated string form (vs space-separated CSS Color 4) for compatibility with `rgba()` legacy syntax already used in the theme.

## Mapping table

The theme's color usage maps to the panel slots through the SCSS variables defined in `src/frontend/scss/utils/_vars.scss` and `src/frontend/scss/compatibility/wc/_woocommerce-vars.scss`. The override stylesheet re-declares each rule that consumes one of those SCSS variables.

**Slot ↔ SCSS variable assignments:**

| Slot | SCSS variable(s) | Rationale |
|---|---|---|
| `base` | `$background_body` (#ffffff) | The whole-site canvas. |
| `text` | `$color_text` (#686868), `$color_heading` (#2b2b2b) | Both body copy and headings collapse to the same slot — panel does not split text/heading. |
| `primary` | `$color_primary` (#235787), `$color_link` (#1e4b75), `$color_link_hover` (#111111) | All "brand action" surfaces collapse here. The hover variant becomes an `rgba()` darken via the `-rgb` triplet, no new slot needed. |
| `secondary` | `$color_secondary` (#c3512f), WC `$primary` (#a46497) | Footer / dark sections / WooCommerce primary button. |
| `accent` | `mark`, `ins` highlights, badges | No SCSS variable maps here today; new override targets these elements directly. |
| `surface` | sidebar widget bg, cards, dropdowns, modal panels | No single SCSS variable; targets concrete selectors. |
| (border) | `$color_border` (#eaecee) | Out of scope for phase 2 — borders stay theme default. |
| (meta) | `$color_meta` (#6d6d6d), `$subtext` (#777) | Out of scope — keeps the muted look distinct. |

### Customizer settings bridge — `inc/customizer/configs/styling.php`

`styling.php` registers nine `color`-type Customizer settings under the **Global Colors** section. `Customify_Customizer_Auto_CSS::auto_css()` reads each setting's `css_format` template, substitutes the user-saved value for `{{value}}`, and emits the result via `wp_add_inline_style('customify-style', …)` — i.e. inline CSS appended to the `customify-style` stylesheet at runtime.

When a site owner has saved a non-default Customizer value, that inline CSS targets specific selectors with the user's color. **Our override stylesheet must redeclare each of those selectors verbatim so cascade order wins.** Specificity is identical (the override copies the selectors as-is), so `<link>` ordering is what selects the winner — and our override is enqueued at priority 100, after `customify-style` (95), after which its inline block sits. Result: when `?preview-colors=1` is active, panel slot wins; when not active, override file is not loaded and the Customizer value remains unchanged.

The fallback inside each `var(--customify-color-<slot>, …)` matches the **SCSS literal default** (= Customizer setting `default` field, identical here), so during the brief moment between page load and `applyColorVars()` running the user sees the theme default rather than a missing-variable artifact.

Settings inventory:

| Customizer setting | Default | Slot | css_format selector groups |
|---|---|---|---|
| `global_styling_color_primary` | `#235787` | `primary` | See "Group P" below |
| `global_styling_color_secondary` | `#c3512f` | `secondary` | See "Group S" below |
| `global_styling_color_text` | `#686868` | `text` | See "Group T" below |
| `global_styling_color_link` | `#1e4b75` | `primary` | See "Group L" below |
| `global_styling_color_link_hover` | `#111111` | `primary` (direct) | See "Group LH" below |
| `global_styling_color_heading` | `#2b2b2b` | `text` | See "Group H" below |
| `global_styling_color_w_title` | `#444444` | `text` | See "Group WT" below |
| `global_styling_color_border` | `#eaecee` | derived from `text` | `rgba(var(--customify-color-text-rgb), 0.12)` — see "Border + Shadow" below |
| `global_styling_color_meta` | `#6d6d6d` | `text` (direct) | See "Meta" below |

#### Group P — Primary (`background-color`)

```
.header-top .header--row-inner,
body:not(.fl-builder-edit) .button,
body:not(.fl-builder-edit) button:not(.menu-mobile-toggle, .components-button, .customize-partial-edit-shortcut-button),
body:not(.fl-builder-edit) input[type="button"]:not(.ed_button),
button.button,
input[type="button"]:not(.ed_button, .components-button, .customize-partial-edit-shortcut-button),
input[type="reset"]:not(.components-button, .customize-partial-edit-shortcut-button),
input[type="submit"]:not(.components-button, .customize-partial-edit-shortcut-button),
.pagination .nav-links > *:hover,
.pagination .nav-links span,
.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-item > a,
.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-ancestor > a,
.nav-menu-desktop.style-full-height .primary-menu-ul > li > a:hover,
.posts-layout .readmore-button:hover {
  background-color: var(--customify-color-primary, #235787);
}
```

Plus two satellite blocks from the same setting:

```
.posts-layout .readmore-button {
  color: var(--customify-color-primary, #235787);
}

.pagination .nav-links > *:hover,
.pagination .nav-links span,
.entry-single .tags-links a:hover,
.entry-single .cat-links a:hover,
.posts-layout .readmore-button,
.posts-layout .readmore-button:hover {
  border-color: var(--customify-color-primary, #235787);
}
```

#### Group S — Secondary (`background-color`)

```
.customify-builder-btn {
  background-color: var(--customify-color-secondary, #c3512f);
}
```

#### Group T — Text (`color`)

```
body { color: var(--customify-color-text, #686868); }
abbr, acronym { border-bottom-color: var(--customify-color-text, #686868); }
```

#### Group L — Link (`color`)

```
a { color: var(--customify-color-primary, #1e4b75); }
```

(Note the fallback `#1e4b75` matches the Customizer `link` default, not the panel `primary` default. The slot map collapses link → primary, but the fallback hex stays per-setting so non-preview rendering matches the theme's expected look. Same applies to other groups — fallback always equals the Customizer default for that specific setting.)

#### Group LH — Link Hover (`color`)

```
a:hover, a:focus,
.link-meta:hover, .link-meta a:hover {
  color: var(--customify-color-primary, #111111);
}
```

Direct slot — same `primary` as resting links. Hover affordance comes from theme-default `text-decoration` / `cursor` styles that are not overridden.

#### Group H — Heading (`color`)

```
h1, h2, h3, h4, h5, h6 {
  color: var(--customify-color-text, #2b2b2b);
}
```

(Heading collapses to `text` slot; fallback stays `#2b2b2b` from Customizer default.)

#### Group WT — Widget Title (`color`)

```
.site-content .widget-title {
  color: var(--customify-color-text, #444444);
}
```

#### Border + Shadow — derived from `text` slot

Per the simplified scope: only **border-color** and **box-shadow** properties get a derivation; every other property uses a slot color directly.

```css
border-color: rgba(var(--customify-color-text-rgb), 0.12);  /* dividers */
box-shadow: 0 1px 2px 0 rgba(var(--customify-color-text-rgb), 0.08);  /* header underline */
```

The override stylesheet redeclares the full selector list from `styling.php` `color_border` `css_format` (every divider, vertical sidebar border, comment-list left rail, etc.) using this formula. The two color-tinted shadow rules in the theme — `src/frontend/scss/header/_header_main.scss:7` and `src/frontend/scss/header/_header_bottom.scss:8` (both `box-shadow: 0 1px 2px 0 #e1e5ea`) — are redeclared with the same `text-rgb @ 0.08` value. All remaining `box-shadow` rules in the theme are `rgba(0, 0, 0, X)` neutrals and stay untouched.

**Accent borders** (e.g. styling.php Group P's `border-color` block on pagination, post tags, readmore button) are *not* dividers — they're meant to show the brand color. Those keep `var(--customify-color-primary, #235787)` directly per Group P. Distinction:

- **Divider border** (gray, separation) → `rgba(var(--customify-color-text-rgb), 0.12)`
- **Accent border** (visible brand tint) → direct `var(--customify-color-primary, …)`

#### Meta — direct `text` slot, no derivation

`global_styling_color_meta` (`#6d6d6d`) collapses to direct `var(--customify-color-text, #6d6d6d)`. Result: in any palette, breadcrumbs / post meta share the body text color. Visual distinction (the original "muted gray") is lost; that is accepted.

Selectors copied verbatim from `styling.php` `color_meta` `css_format`:

```css
article.comment .comment-post-author { background: var(--customify-color-text, #6d6d6d); }
.pagination .nav-links > *,
.link-meta, .link-meta a, .color-meta,
.entry-single .tags-links:before, .entry-single .cats-links:before {
  color: var(--customify-color-text, #6d6d6d);
}
```

#### `apply_filters` extension points

Each `css_format` is wrapped in `apply_filters('customify/styling/<setting-slug>', '...')`. If a third-party plugin appends selectors via these filters, our override won't cover them. **Phase 2 does not chase filter-injected selectors** — accepted gap, document at implementation time.

### Derivation strategy — border + shadow only

Every property except `border-*` and `box-shadow` uses a panel slot **directly** (`color: var(--customify-color-<slot>, …)`). No `color-mix`, no opacity reduction. This keeps the override file simple and removes the browser-support question.

Two exceptions where direct-slot use produces poor results (a divider border the same color as body text would paint a bold line; a shadow the same color as text would look like a duplicate underline), so those are derived from `text` at low alpha:

#### Primitive

```css
rgba(var(--customify-color-text-rgb), <alpha>)
```

Universal browser support — needs only the `-rgb` triplet phase 2.1 emits.

#### Application

| Property | Derivation | Used by |
|---|---|---|
| `border-color` (divider intent) | `rgba(var(--customify-color-text-rgb), 0.12)` | All selectors in `styling.php` `color_border` `css_format` (page breadcrumb, comment dividers, sidebar vertical rails, widget-area menu rows, post navigation, page titlebar, header search modal, …) |
| `box-shadow` (color-tinted variants) | `0 1px 2px 0 rgba(var(--customify-color-text-rgb), 0.08)` | `header/_header_main.scss:7`, `header/_header_bottom.scss:8` (the only two non-`rgba(0,0,0,*)` shadows in the theme) |

#### Not derived (kept as-is)

- All `box-shadow: rgba(0, 0, 0, X)` rules (~60 occurrences) — neutral shadows, not theme-color-tied.
- `box-shadow: $boxshadow_dropdown` (= `0 3px 30px rgba(25,30,35,.1)`) — near-black neutral, kept literal.
- **Accent borders** in `styling.php` Group P (pagination, tags, readmore-button border-color block) — those *are* meant to display the brand primary; they use direct `var(--customify-color-primary, …)`, not the divider derivation.

#### Everything else uses a slot directly

Direct mapping table (no derivation, no opacity, no color-mix):

| Property class | Slot | Example |
|---|---|---|
| body bg | `base` | `body { background: var(--customify-color-base, #ffffff); }` |
| body text + headings + meta + widget title | `text` | `body, h1..h6, .link-meta, .widget-title { color: var(--customify-color-text, …); }` |
| accent decorations (`mark`, `ins`) | `accent` | `mark { background: var(--customify-color-accent, …); }` |
| widget container, card, modal | `surface` | `.widget { background: var(--customify-color-surface, …); }` |
| primary action (button bg, link, accent border) | `primary` | (Group P, L, LH) |
| secondary surface (footer bg, dark callout) | `secondary` | (Group S + footer) |
| white text on primary/secondary buttons | literal `#ffffff` | Slot system has no "on-X" companion; not derived |
| footer text on `secondary` bg | `base` (direct) | `.site-footer { color: var(--customify-color-base, #fff); }` — falls back when secondary == base inverted |
| `pre` / `code` background | not overridden | Theme keeps SCSS-computed `lighten/darken(base, 5%)` |

### Theme selectors — `src/frontend/scss/`

Each row lists the *exact* selector and which property the override re-declares. Selectors are taken verbatim from the SCSS source so cascade order alone wins.

#### `base/_base.scss`

| # | Source line | Selector | Property | Slot | Notes |
|---|---|---|---|---|---|
| 1 | 22 | `body` | `color` | `text` | Default body color |
| 2 | 50,55,67,75,83,88 | `.h6, h6, .h5, h5, .h4, h4, .h3, h3, .h2, h2, .h1, h1` | `color` | `text` | Headings |
| 3 | 142 | `abbr, acronym` | `border-bottom-color` | `text` | Dotted underline |
| 4 | 173 | `body` | `background` | `base` | Site canvas |
| 5 | 204 | `a` | `color` | `primary` | Default link |
| 6 | 209 | `a:hover, a:focus, a:active` | `color` | `primary` (direct, identical to resting link) |
| 7 | 462 | `body:not(.fl-builder-edit) .button, body:not(.fl-builder-edit) button:not(...), .button:not(...), input[type="button"], input[type="reset"], input[type="submit"]` (full chain) | `background` | `primary` | Primary button bg |
| 8 | 461,464 | same chain | `color` | derived from primary contrast | Keep `#ffffff` literal — color-on-primary is not part of slot system; theme assumes light text on primary |
| 9 | 802 | `.menu-toggle, .menu-toggle.toggled, .nav-menu .menu-toggle:hover` (etc) | `color` | `text` |  |
| 10 | 937 | `.search-form .search-submit:hover` | `color` | `primary` |  |

#### `header/_header_top.scss`, `header/builder_items/`

| # | File | Selector | Property | Slot |
|---|---|---|---|---|
| 11 | `_header_top.scss` | `.site-header-section--top` (or whatever uses `$color_primary`) | `background` | `primary` |
| 12 | `builder_items/_button.scss` | `.header--button` | `background` | `secondary` |
| 13 | `builder_items/_navigation.scss` | nav `:hover`, current-menu link bg states using `$color_link` | `background-color` | `primary` |
| 14 | `builder_items/_search.scss` | search input `color` using `$color_text` | `color` | `text` |

(Exact selector names verified during phase 2 implementation by re-grepping each file — listed here only as targets.)

#### `layouts/_blogs.scss`

| # | Selector class | Property | Slot |
|---|---|---|---|
| 15 | category/tag pill `border` + `color` | `border-color`, `color` | `primary` |
| 16 | active state `background`, `border-color` | `background`, `border-color` | `primary` |
| 17 | post meta link `:hover` | `color` | `primary` |

#### `widgets/_widgets.scss`

| # | Selector | Property | Slot |
|---|---|---|---|
| 18 | `.widget .widget-title::after` (or accent strip) | `background` | `primary` |
| 19 | widget container `background` | `background` | `surface` |

#### `footer/_footer-common.scss`

| # | Selector | Property | Slot |
|---|---|---|---|
| 20 | `.site-footer` (root) | `background` | `secondary` |
| 21 | `.site-footer` text | `color` | derived: `rgba(var(--customify-color-base-rgb), 0.8)` |

#### `base/_blocks.scss` (Gutenberg-rendered classes shipped with theme)

| # | Selector | Property | Slot |
|---|---|---|---|
| 22 | `mark`, `ins` | `background` | `accent` |
| 23 | `blockquote` `border-left` | `border-left-color` | `primary` |
| 24 | `pre` | `background` | *not overridden* — theme keeps SCSS-computed `lighten/darken(base, 5%)` literal |

### WooCommerce selectors — `src/frontend/scss/compatibility/wc/`

WC has its own color variable file (`_woocommerce-vars.scss`) with `$primary`, `$secondary`, `$primarytext`, `$secondarytext`, `$subtext`. Override file remaps these:

| WC var | Slot |
|---|---|
| `$primary` | `primary` |
| `$secondary` | `secondary` |
| `$primarytext` | white literal kept (contrast on primary, out of slot system) |
| `$secondarytext` | white literal kept |
| `$subtext` | `text` |

#### `_woocommerce-main.scss`

| # | Source line | Selector chain | Property | Slot |
|---|---|---|---|---|
| 25 | 25 | `.woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit` | `background-color` | `primary` |
| 26 | 113,122,124 | breadcrumbs / muted text | `color` | `text` |
| 27 | 142,147 | `.woocommerce ul.products li.product` (border) | `border-color` | derived: `rgba(var(--customify-color-secondary-rgb), 0.5)` (replaces SCSS `darken($secondary, 10%)`) |
| 28 | 170 | hover `background` for product cards | `background` | `secondary` |
| 29 | 203 | `.cart_totals` `border-top` | `border-top-color` | `primary` |
| 30 | 462 | tab/nav active state bg (already `$color_primary` from base) | `background` | `primary` |

#### `_woocommerce-layout.scss`, `_woocommerce-fonts.scss`, `_woocommerce-animation.scss`

| # | Selector | Property | Slot |
|---|---|---|---|
| 31 | `.price`, `.amount` | `color` | `primary` |
| 32 | `.onsale` badge | `background`, `color` | `accent` (bg), kept-white text |
| 33 | `.star-rating::before, .star-rating span::before` | `color` | `accent` |
| 34 | `.woocommerce-message`, `.woocommerce-info` border-left | `border-left-color` | `primary` |
| 35 | mini-cart sticky-cart `background` | `background` | `surface` |

(Exact line numbers + full selectors verified at implementation time by re-grepping each file.)

### Gutenberg/Block utility classes the theme ships

Theme registers `editor-color-palette` in `Customify::theme_setup()` with hex literals (`primary` #235787, `secondary` #c3512f, `text` #686868, `link` #1e4b75, `light-gray` #f2f2f2, `dark-gray` #444444). The frontend uses `.has-primary-color`, `.has-primary-background-color`, etc. The override stylesheet maps these:

| # | Selector | Property | Slot |
|---|---|---|---|
| 36 | `.has-primary-color` | `color` | `primary` |
| 37 | `.has-primary-background-color` | `background-color` | `primary` |
| 38 | `.has-secondary-color` | `color` | `secondary` |
| 39 | `.has-secondary-background-color` | `background-color` | `secondary` |
| 40 | `.has-text-color` (the WP-named `text` slug) | `color` | `text` |
| 41 | `.has-link-color` | `color` | `primary` |

(Note: `.has-light-gray-*` and `.has-dark-gray-*` keep the literal hex from `theme_setup()` — they are theme-fixed neutrals, not part of the panel.)

## Phasing

### 2.1 — RGB triplets + scaffold (small PR)

1. Extend `applyColorVars()` in `src/preview-colors/preview-colors.js` to emit `--customify-color-<slot>-rgb` alongside the hex.
2. Add `hexToRgb(hex) -> "r, g, b"` helper.
3. Create empty `src/preview-colors/overrides.scss` (header comment only).
4. Add webpack entry `frontend/preview-colors-overrides`.
5. PHP `enqueue()` adds `wp_enqueue_style` for the overrides bundle when `is_active()`.
6. Verify build outputs land at `build/css/frontend/preview-colors-overrides.{css,min.css,-rtl.css,-rtl.min.css}` and the `<link>` appears in `<head>` on `?preview-colors=1`.
7. Smoke test: switch palette in panel; vars + RGB triplets update on `:root`.

### 2.2 — Core theme overrides (Customizer bridge groups + SCSS rows 1–24)

Implement override rules in this order so cascade-order issues surface early:

1. **Customizer bridge groups P, S, T, L, LH, H, WT** (from styling.php). Copy each `css_format` block verbatim, swap the literal in `{{value}}` for `var(--customify-color-<slot>, <Customizer default hex>)`. Verify on a fresh install (defaults active) and on a site with all 7 settings changed via Customizer — both should reflect panel slot when `?preview-colors=1` is on.
2. **SCSS-source rows 1–24** for selectors *not* covered by styling.php (`mark`, `ins`, `blockquote`, widget surface, `.site-footer`, etc).

After each group, manually verify on a representative page (homepage, single post, archive). No `!important` anywhere — if a rule doesn't win, copy the *full* original selector chain (incl. `body:not(.fl-builder-edit)` qualifiers from styling.php verbatim).

Definition of done:
- `body` background, headings, body text reflect panel (vs. defaults *and* vs. Customizer-modified site)
- Links + button group reflect `primary` (overrides Group P, L, LH inline output)
- Footer reflects `secondary`
- Widget container reflects `surface`
- `mark`/`ins` reflect `accent`

### 2.3 — WooCommerce overrides (rows 25–35)

Only run on a site with WC active. Test pages: shop, single product, cart, checkout, my-account.

Definition of done:
- WC buttons (add to cart, place order) reflect `primary`
- `.onsale`, star ratings reflect `accent`
- `.price`, message borders reflect `primary`
- Product card border / hover reflect `secondary`

### 2.4 — Block utility classes (rows 36–41)

Adds the `.has-*-color` / `.has-*-background-color` overrides. Verify on a post containing a Gutenberg block with each color slug applied.

### 2.5 — Border + shadow derivations

Single phase, no `color-mix` dependency:

1. Redeclare every selector in `styling.php` `color_border` `css_format` (page breadcrumb, comment dividers, sidebar vertical rails, widget-area menu rows, post navigation, page titlebar, header search modal, etc.) using `rgba(var(--customify-color-text-rgb), 0.12)` for the border-color property.
2. Redeclare the two color-tinted shadows (`header/_header_main.scss:7`, `header/_header_bottom.scss:8`) using `0 1px 2px 0 rgba(var(--customify-color-text-rgb), 0.08)`.
3. Verify on default + Customizer-modified site, in light *and* dark palettes from the panel (Ashwood, Midnight).

Definition of done:
- Dividers visible against canvas in any palette without painting a strong line.
- Header underline shadow follows palette tone.
- Accent borders (Group P) remain explicitly primary, not muted.

## Risks & mitigations

| Risk | Mitigation |
|---|---|
| Theme uses inline `style="…"` attributes (not CSS rules) for some colors. | Inline styles always win. Identified instances during 2.2/2.3 implementation are reported back as out-of-scope; phase 3 may rewrite those templates. |
| Customizer-set values defeat overrides on equal-specificity rules. | Our overrides load *after* the `customify-style` inline block. Cascade order wins. The "Customizer settings bridge" section above lists every `styling.php` selector group the override must include verbatim — missing any one of them re-opens the gap. Verified in 2.2 by inspecting computed styles in DevTools on a site where every Global Colors setting has been changed via Customizer. |
| Third-party plugin filters `customify/styling/<setting>-color` and appends extra selectors to a `css_format`. | Out of scope. Override stylesheet ships with the *baseline* selectors only. Filter-injected selectors retain their Customizer-set color in preview mode. Documented gap. |
| Borders / shadows would look wrong if painted directly with the `text` slot (a strong line the same colour as body text). | Two derivations only — `rgba(var(--customify-color-text-rgb), 0.12)` for divider borders and `rgba(var(--customify-color-text-rgb), 0.08)` for the two color-tinted shadows. RGB-triplets emitted in 2.1, universal browser support, no `color-mix` dependency. |
| Theme color palette registered in `editor-color-palette` (PHP) is hex-literal — block editor in admin still sees hex. | Out of scope. Frontend rendering of `.has-primary-color` etc. is covered (rows 36–41); editor visualization stays on the literal. |
| `.fl-builder-edit` body-class selectors used to guard against Beaver Builder edit context. | Override copies the same `body:not(.fl-builder-edit)` qualifier verbatim. |
| Specificity ties may resolve unpredictably across browsers if order changes. | Pin enqueue priority to `100` (theme styles `95`) and document it in `class-preview-colors.php`. |

## Open items

- Confirm exact selectors per row by re-grepping at implementation time (some selectors compress in the table for readability).
- Decide whether `--customify-color-*-rgb` should be set independently or always together with hex (recommend always together — single conversion in `applyColorVars`).
- Whether to emit a CSS comment header in `overrides.css` listing the row numbers it implements (helps debugging in DevTools).

## Out of scope (this phase)

- Elementor widget colors
- Gutenberg block library colors (`core/columns`, `core/cover`, etc. beyond `.has-*-color` utilities)
- Plugins other than WooCommerce
- Dark/light auto-contrast for the `*text` companion colors
- Persistent application (committing the panel palette into theme_mods so visitors see it). Currently visitors never receive the vars — that's the design.
