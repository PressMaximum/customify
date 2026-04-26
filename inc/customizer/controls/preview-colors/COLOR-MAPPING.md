# Color mapping — quick reference

A practical guide to **which palette slot paints which element** in the
theme, **how dark-mode colours are computed**, and **how to slot-bind a
new rule** in your own SCSS / child-theme code.

For deeper architectural context see PHASE-2/3/6/7 plan files; this is
the cheat sheet.

---

## TL;DR

The theme exposes **6 palette slots** + **8 auto-computed companions** as
CSS variables on `:root`. Every theme rule that paints a colour reads
from these vars (with a hex fallback for sites that haven't set up a
palette yet). Switching palettes in Customizer instantly repaints
everything; switching to dark mode (via the `.dark-mode` class on a
section or `<html>`) swaps each var to its `-dark` companion.

---

## The 6 slots

| Slot | Role | Visual identity |
|---|---|---|
| `base` | Page background canvas | The blank page underneath everything (body bg) |
| `text` | Body text / headings | The "ink" colour |
| `primary` | Brand action | Buttons, links, focus rings, active states |
| `secondary` | Dark sectional band | Header/footer dark rows, callouts, dark CTAs |
| `accent` | Decorative pop | Badges, sale tags, hover glow, sticky-note highlights |
| `surface` | Elevated container | Card backgrounds, modals, dropdowns, tooltips |

Each slot is exposed as **two CSS variables**:

```css
--customify-color-<slot>       /* e.g. #B35932 */
--customify-color-<slot>-rgb   /* e.g. 179, 89, 50 (for rgba() consumers) */
```

---

## The 8 auto-computed companions

The theme derives these from the 6 slots — users never pick them
directly. They're emitted on `:root` alongside the slots so SCSS rules
can consume them without re-deriving.

| Companion var | Computed from | Formula |
|---|---|---|
| `--customify-color-on-primary` | `primary` | `pickOn(primary)` — WCAG luminance test → returns `#1A1A1A` (dark) or `#FFFFFF` (light) |
| `--customify-color-on-secondary` | `secondary` | `pickOn(secondary)` |
| `--customify-color-on-surface` | `surface` | `pickOn(surface)` |
| `--customify-color-text-muted` | `text` | `rgba(text-rgb, 0.55)` — meta text, breadcrumbs |
| `--customify-color-text-subtle` | `text` | `rgba(text-rgb, 0.35)` — disabled state |
| `--customify-color-border-default` | `text` | `rgba(text-rgb, 0.12)` — dividers, hr, table borders |
| `--customify-color-primary-hover` | `primary` + `base` | `color-mix(in srgb, primary, #000 15%)` — link/button hover |
| `--customify-color-primary-subtle` | `primary` + `base` | `color-mix(in srgb, primary, base 92%)` — primary wash bg |

**`pickOn()` algorithm** (used by all `on-*` companions):

```
1. Convert hex → RGB
2. Compute WCAG relative luminance L = 0.2126*r + 0.7152*g + 0.0722*b
3. If L > 0.45 → return #1A1A1A (dark text)
   Else        → return #FFFFFF (light text)
```

Threshold `0.45` (instead of WCAG default `0.5`) is nudged slightly
toward white-text — warm tones (terracotta, amber) read better with
white text than black.

---

## Element → slot mapping

The full list of theme elements with the **actual CSS selectors** that
paint them. Two views below: by component (what does X paint?) and by
slot (where is Y consumed?).

### View 1 — By component

#### Page chrome / typography

| What | Selector(s) | Property | Slot |
|---|---|---|---|
| Body text | `body` | `color` | `text` (fallback `#686868`) |
| Headings | `h1, h2, h3, h4, h5, h6` | `color` | `text` (heading default `#2b2b2b`) |
| Widget titles | `.widget-title, .widget-title a` | `color` | `text` (fallback `#444444`) |
| Default link | `a, a:visited` | `color` | `primary` (fallback `#1e4b75`) |
| Link hover | `a:hover, a:focus, a:active` | `color` | `primary-hover` (fallback `#111111`) |
| Abbreviation underline | `abbr[title]` | `border-bottom-color` | `text` |
| Mark / ins highlight | `mark, ins` | `background` | `text` (fallback `#6d6d6d`) |
| Meta text | `.entry-meta, .posted-on, .byline, .breadcrumbs` | `color` | `text-muted` |
| `<hr>` | `hr` | `border-top-color` | `border-default` |
| Blockquote left bar | `blockquote` | `border-left-color` | `border-default` |
| Table borders | `table th, table td` | `border-color` | `border-default` |
| Subtle box-shadow | various card-like elements | `box-shadow` (color stop) | `text-rgb @ 0.08` |

Source: [overrides.scss](wp-content/themes/customify/src/frontend/scss/overrides.scss) lines 88-256.

#### Header

| Section | Selector(s) | Property | Slot (fallback) |
|---|---|---|---|
| Header top light-mode bg | `.header-top .light-mode` | `background` | `surface` (`#f0f0f0`) |
| Header top dark-mode bg | `.header-top .dark-mode` | `background` | `secondary` (`$color_primary`) |
| Header main light-mode bg | `.header-main .light-mode` | `background` | `surface` (`#FFFFFF`) |
| Header main dark-mode bg | `.header-main .dark-mode` | `background` | `secondary` (`#1a1a1a`) |
| Header bottom light-mode bg | `.header-bottom .light-mode` | `background` | `surface` (`#f0f0f0`) |
| Header bottom dark-mode bg | `.header-bottom .dark-mode` | `background` | `secondary` (`#303030`) |
| Header top inner row bg | `html .header-top .header--row-inner` | `background-color` | `primary` (`#235787`) |
| Active nav item | `.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-item > a`<br/>`.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-ancestor > a` | `background-color` | `primary` |
| Nav link hover | `.nav-menu-desktop.style-full-height .primary-menu-ul > li > a:hover` | `background-color` | `primary` |

Source: [_header_top.scss](wp-content/themes/customify/src/frontend/scss/header/_header_top.scss), [_header_main.scss](wp-content/themes/customify/src/frontend/scss/header/_header_main.scss), [_header_bottom.scss](wp-content/themes/customify/src/frontend/scss/header/_header_bottom.scss), [overrides.scss](wp-content/themes/customify/src/frontend/scss/overrides.scss).

#### Footer

| Section | Selector(s) | Property | Slot (fallback) |
|---|---|---|---|
| Footer top light-mode bg | `.footer-top .light-mode` | `background` | `surface` (`#f0f0f0`) |
| Footer top dark-mode bg | `.footer-top .dark-mode` | `background` | `secondary` (`#292929`) |
| Footer main light-mode bg | `.footer-main .light-mode` | `background` | `surface` (`#f9f9f9`) |
| Footer main dark-mode bg | `.footer-main .dark-mode` | `background` | `secondary` (`#303030`) |
| Footer bottom light-mode bg | `.footer-bottom .light-mode` | `background` | `surface` (`#ededed`) |
| Footer bottom dark-mode bg | `.footer-bottom .dark-mode` | `background` | `secondary` (`#1a1a1a`) |
| Light-mode row text | `.footer--row-inner.light-mode` | `color` | `on-surface` (`$dark_color`) |
| Light-mode list border | `.footer--row-inner.light-mode .product_list_widget li` | `border-color` | `border-default` (`$dark_color_border`) |
| Dark-mode row text | `.footer--row-inner.dark-mode` | `color` | `on-secondary` (`$light_color`) |
| Dark-mode link | `.footer--row-inner.dark-mode a:not(.button)` | `color` | `primary` (`$light_color_link`) |
| Dark-mode link hover | `.footer--row-inner.dark-mode a:not(.button):hover` | `color` | `primary-hover` (`$light_color_link_hover`) |
| Dark-mode list border | `.footer--row-inner.dark-mode .product_list_widget li` | `border-color` | `border-default` (`$light_color_border`) |
| Site footer bg | `.site-footer` | `background` | `secondary` (`#1c2147`) |
| Footer text generic | `.site-footer, .site-footer p, .site-footer a` | `color` | `on-secondary` (`#ffffff`) |

Source: [_footer-common.scss](wp-content/themes/customify/src/frontend/scss/footer/_footer-common.scss), [overrides.scss](wp-content/themes/customify/src/frontend/scss/overrides.scss).

#### Buttons & form controls

| What | Selector(s) | Property | Slot (fallback) |
|---|---|---|---|
| Button bg | `body:not(.fl-builder-edit) .button`<br/>`body:not(.fl-builder-edit) button:not(.menu-mobile-toggle, .components-button, .customize-partial-edit-shortcut-button)`<br/>`body:not(.fl-builder-edit) input[type="button"]:not(.ed_button)`<br/>`.button:not(.components-button):not(.customize-partial-edit-shortcut-button)`<br/>`input[type="button"]:not(.ed_button)`<br/>`input[type="button"]:not(.components-button):not(.customize-partial-edit-shortcut-button)`<br/>`input[type="reset"]:not(.components-button):not(.customize-partial-edit-shortcut-button)`<br/>`input[type="submit"]:not(.components-button):not(.customize-partial-edit-shortcut-button)` | `background` | `primary` (`$color_primary`) |
| Button text | (same selectors as bg) | `color` | `on-primary` (`#ffffff`) |
| Button :focus text | (same + `:focus`) | `color` | `on-primary` (`#ffffff`) |
| Disabled buttons | `.button[disabled]:not(...)`, etc. | `opacity` | (no slot — `0.5`) |
| Builder buttons | `.customify-builder-btn` | `background-color` + `color` | `secondary` + `on-secondary` (`#c3512f` / `#ffffff`) |

Source: [_base.scss:453-466](wp-content/themes/customify/src/frontend/scss/base/_base.scss#L453-L466), [overrides.scss:80-83](wp-content/themes/customify/src/frontend/scss/overrides.scss#L80-L83).

#### Pagination & post navigation

| What | Selector(s) | Property | Slot |
|---|---|---|---|
| Pagination hover bg | `.pagination .nav-links > *:hover` | `background-color` + `color` + `border-color` | `primary` + `on-primary` + `primary` |
| Pagination current page | `.pagination .nav-links span` | (same) | (same) |
| Read-more button | `.posts-layout .readmore-button` | `color` + `border-color` | `primary` |
| Read-more button hover | `.posts-layout .readmore-button:hover` | `background-color` + `color` + `border-color` | `primary` + `on-primary` + `primary` |
| Tag/category link hover border | `.entry-single .tags-links a:hover, .entry-single .cat-links a:hover` | `border-color` | `primary` |

Source: [overrides.scss:43-74](wp-content/themes/customify/src/frontend/scss/overrides.scss#L43-L74).

#### Mobile / off-canvas chrome

| What | Selector(s) | Property | Slot |
|---|---|---|---|
| Hamburger sidebar inner | `.header-menu-sidebar-inner` | `background` | `base` (`#ffffff`) |
| Mobile nav strip | `.menu-mobile-strip` | `background` | `secondary` (`#1c2147`) |
| Mobile nav text | `.menu-mobile-strip a, .menu-mobile-strip` | `color` | `on-secondary` (`#ffffff`) |
| Search modal | `.search-modal` | `background-color` | `surface` (`#ffffff`) |

Source: [overrides.scss:260-295](wp-content/themes/customify/src/frontend/scss/overrides.scss#L260-L295).

#### Accent / highlight

| What | Selector(s) | Property | Slot |
|---|---|---|---|
| Highlight chips, badges | `.tag-pill, .badge-accent` | `background-color` + `color` | `accent` + `text` (`#f5de9a` / `#1A3A28`) |

Source: [overrides.scss:300-305](wp-content/themes/customify/src/frontend/scss/overrides.scss#L300-L305).

#### WooCommerce

| What | Selector(s) | Property | Slot |
|---|---|---|---|
| Sale / promo button bg | `.woocommerce .button.alt`, `.woocommerce-page .button.alt`, `.woocommerce a.added_to_cart` | `background-color` + `color` | `primary` + `on-primary` (`#a46497` / `#ffffff`) |
| Store notice link | `.woocommerce-store-notice a, .woocommerce p.demo_store a` | `color` | `on-primary` |
| Note text | `.woocommerce small.note` | `color` | `text` (`#777777`) |
| Onsale tag | `.woocommerce span.onsale` | `border-color` + `background` + `color` | `secondary` + `on-secondary` (`#c3512f` / `#ffffff`) |
| Cart-count badge | `.cart-count` | `background-color` + `color` | `secondary` + `on-secondary` |
| Single product price | `.woocommerce .price` | `color` | `secondary` |
| Star rating | `.star-rating::before` | `color` | `secondary @ 0.4 alpha` |
| Star rating active | `.star-rating span::before` | `color` | `secondary` |
| Header cart icon | `.header-cart .cart-icon` | `color` | `primary` |
| Single product top divider | `.product-summary-wrap` | `border-top-color` | `primary` |
| Tab navigation accent | `.woocommerce-tabs .tabs li.active` | `background-color` + `color` | `accent` + `text` |

Source: [overrides.scss:340-410](wp-content/themes/customify/src/frontend/scss/overrides.scss#L340-L410).

#### Block editor (Gutenberg)

The slots are also re-exported as `--wp--preset--color--<name>` so the
block-editor colour picker shows the active palette + colour-related
block CSS picks up palette changes automatically:

| WP preset slug | Bound to |
|---|---|
| `--wp--preset--color--primary` | `--customify-color-primary` |
| `--wp--preset--color--secondary` | `--customify-color-secondary` |
| `--wp--preset--color--text` | `--customify-color-text` |
| `--wp--preset--color--link` | `--customify-color-primary` |
| `--wp--preset--color--on-primary` | `--customify-color-on-primary` |
| `--wp--preset--color--on-secondary` | `--customify-color-on-secondary` |
| `--wp--preset--color--on-surface` | `--customify-color-on-surface` |
| `--wp--preset--color--text-muted` | `--customify-color-text-muted` |
| `--wp--preset--color--text-subtle` | `--customify-color-text-subtle` |
| `--wp--preset--color--border-default` | `--customify-color-border-default` |
| `--wp--preset--color--primary-hover` | `--customify-color-primary-hover` |
| `--wp--preset--color--primary-subtle` | `--customify-color-primary-subtle` |

Source: [overrides.scss:322-334](wp-content/themes/customify/src/frontend/scss/overrides.scss#L322-L334).

### View 2 — By slot (reverse index)

For "where is `--customify-color-X` consumed?" lookup.

#### `--customify-color-base`

| Selector | Property | File |
|---|---|---|
| `.header-menu-sidebar-inner` | `background` | overrides.scss |

#### `--customify-color-text`

| Selector | Property | File |
|---|---|---|
| `body` | `color` | _base.scss:22 |
| `h1, h2, h3, h4, h5, h6` | `color` | _base.scss:49 + overrides.scss |
| `.widget-title, .widget-title a` | `color` | overrides.scss:140 |
| `abbr[title]` | `border-bottom-color` | overrides.scss:96 |
| `mark, ins` | `background` | overrides.scss:149 |
| Highlight chip text | `color` | overrides.scss:304 |
| WC `.woocommerce small.note` | `color` | overrides.scss:361 |

#### `--customify-color-primary`

| Selector | Property | File |
|---|---|---|
| `a, a:visited` | `color` | overrides.scss:104 |
| Button group (8-selector chain in #buttons-and-form-controls table) | `background` | _base.scss:462, overrides.scss:58 |
| `html .header-top .header--row-inner` + active/hover nav | `background-color` | overrides.scss:44-58 |
| `.posts-layout .readmore-button` | `color` + `border-color` | overrides.scss:63, 73 |
| Pagination current/hover | `background-color` + `border-color` | overrides.scss:52-58, 67-74 |
| Tag/category link hover | `border-color` | overrides.scss:67-74 |
| `.footer--row-inner.dark-mode a:not(.button)` | `color` | _footer-common.scss:89 |
| Header cart icon | `color` | overrides.scss:407 |
| WC `.button.alt` | `background-color` | overrides.scss:349 |
| Single product top divider | `border-top-color` | overrides.scss:382 |

#### `--customify-color-on-primary`

| Selector | Property | File |
|---|---|---|
| Button group | `color` (incl. `:focus`) | _base.scss:461, 464; overrides.scss:59 |
| `.pagination .nav-links > *:hover, span` | `color` | overrides.scss:58 |
| `.posts-layout .readmore-button:hover` | `color` | overrides.scss:59 |
| WC `.button.alt`, store-notice links | `color` | overrides.scss:350, 355 |

#### `--customify-color-primary-hover`

| Selector | Property | File |
|---|---|---|
| `a:hover, a:focus, a:active` | `color` | overrides.scss:117 |
| `.footer--row-inner.dark-mode a:not(.button):hover` | `color` | _footer-common.scss:91 |

#### `--customify-color-secondary`

| Selector | Property | File |
|---|---|---|
| `.header-top .dark-mode` | `background` | _header_top.scss:15 |
| `.header-main .dark-mode` | `background` | _header_main.scss:4 |
| `.header-bottom .dark-mode` | `background` | _header_bottom.scss:11 |
| `.footer-top .dark-mode` | `background` | _footer-common.scss:28 |
| `.footer-main .dark-mode` | `background` | _footer-common.scss:43 |
| `.footer-bottom .dark-mode` | `background` | _footer-common.scss:65 |
| `.site-footer` | `background` | overrides.scss:272 |
| `.menu-mobile-strip` | `background` | overrides.scss |
| `.customify-builder-btn` | `background-color` | overrides.scss:81 |
| WC onsale tag | `border-color` + `background` | overrides.scss:366, 374 |
| WC cart-count badge | `background-color` | overrides.scss |
| WC single product price | `color` | overrides.scss:396 |
| WC star rating active | `color` | overrides.scss:400-407 |

#### `--customify-color-on-secondary`

| Selector | Property | File |
|---|---|---|
| `.footer--row-inner.dark-mode` | `color` | _footer-common.scss:87 |
| `.site-footer, .site-footer p, .site-footer a` | `color` | overrides.scss:280 |
| `.menu-mobile-strip a, .menu-mobile-strip` | `color` | overrides.scss |
| `.customify-builder-btn` | `color` | overrides.scss:82 |
| WC onsale tag | `color` | overrides.scss:375 |

#### `--customify-color-on-surface`

| Selector | Property | File |
|---|---|---|
| `.footer--row-inner.light-mode` | `color` | _footer-common.scss:75 |

#### `--customify-color-surface`

| Selector | Property | File |
|---|---|---|
| `.header-top .light-mode` | `background` | _header_top.scss:12 |
| `.header-main .light-mode` | `background` | _header_main.scss:8 |
| `.header-bottom .light-mode` | `background` | _header_bottom.scss:7 |
| `.footer-top .light-mode` | `background` | _footer-common.scss:25 |
| `.footer-main .light-mode` | `background` | _footer-common.scss:40 |
| `.footer-bottom .light-mode` | `background` | _footer-common.scss:62 |
| `.search-modal` | `background-color` | overrides.scss:292 |

#### `--customify-color-accent`

| Selector | Property | File |
|---|---|---|
| `.tag-pill, .badge-accent` | `background-color` | overrides.scss:303 |
| WC tab active | `background-color` | overrides.scss:388 |

#### `--customify-color-text-muted`

| Selector | Property | File |
|---|---|---|
| `.entry-meta, .posted-on, .byline, .breadcrumbs` | `color` | overrides.scss:158 |

#### `--customify-color-border-default`

| Selector | Property | File |
|---|---|---|
| `hr` | `border-top-color` | overrides.scss:171 |
| `blockquote` | `border-left-color` | overrides.scss:176 |
| `table, td, th` | `border-color` | overrides.scss:181-239 |
| `.footer--row-inner.{light,dark}-mode .product_list_widget li` | `border-color` | _footer-common.scss:83, 95 |

---

## Dark-mode computation

A "dark mode" engages when an element (or any ancestor) carries one of
these classes:

```
.dark-mode    .is-dark-mode    [data-theme="dark"]
```

Inside that subtree, every active slot var **rebinds** to its `-dark`
companion via a single CSS rule emitted at page load:

```css
.dark-mode, .is-dark-mode, [data-theme="dark"] {
  --customify-color-base:      var(--customify-color-base-dark);
  --customify-color-text:      var(--customify-color-text-dark);
  --customify-color-primary:   var(--customify-color-primary-dark);
  --customify-color-secondary: var(--customify-color-secondary-dark);
  --customify-color-accent:    var(--customify-color-accent-dark);
  --customify-color-surface:   var(--customify-color-surface-dark);
  /* rgb triplets + 8 auto-computed companions also rebind */
}
```

Rules that consume the active vars don't need to know about dark mode —
they automatically repaint when the trigger fires.

### How the `-dark` value is computed

5-tier resolve chain, evaluated server-side in PHP. First tier with a
value wins:

| Tier | Source | When it applies |
|---|---|---|
| **L1** | `palette.dark.<slot>` (explicit hex in config) | Theme presets that ship a curated dark companion (Ashwood, Midnight, Ocean, Moss) |
| **L2** | Auto-derived from `palette.colors.<slot>` via HSL transform | User-created palettes without explicit dark; see formulas below |
| **L3** | Derived from legacy `theme_mod` (Phase 6 compat) | Sites upgrading from pre-palette settings |
| **L4** | SCSS baseline (`_skins.scss` literal) | No palette + no theme_mod |
| **L5** | Hex baseline (Midnight preset values) | Last resort |

The PHP renderer composes the chain and emits **a single resolved hex**
per `-dark` var. CSS only sees the answer, never the chain.

### Auto-derive formulas (L2)

Per slot, when no explicit dark companion exists. Each starts with `RGB
→ HSL` conversion, then transforms.

#### `base`

```
L_new = clamp(100 − L, 5, 12)
S, H unchanged
```

Invert lightness, clamp to a near-black band. Hue preserved so brand
warmth carries to dark mode.

#### `text`

```
L_new = clamp(100 − L, 88, 96)
S_new = min(S, 30)
H unchanged
```

Invert lightness, near-white band. Cap saturation so warm inks don't
read as colour casts on dark canvas.

#### `surface`

```
1. Compute base-dark first: derive('base', palette.colors.base)
2. Read its HSL → (h_b, s_b, l_b)
3. surface-dark = HSL(h_b, s_b, min(l_b + 6, 18))
```

Lift base-dark by 6% lightness, capped at 18 — distinct from canvas
without becoming "light surface".

#### `primary`

```
L_new = clamp(L, 55, 70)
S, H unchanged
```

Boost lightness into a band that maintains AA contrast on dark
surfaces. Hue + saturation preserved for brand fidelity.

#### `secondary` (auto-flip based on context)

```
IF luminance(secondary) < luminance(surface)
   /* typical "dark band on light page" — flip to "light band on dark page" */
   L_new = clamp(100 − L, 80, 95)
   S_new = max(S − 10, 0)
ELSE
   /* secondary is already lighter than surface — just darken slightly */
   L_new = max(L − 10, 20)
   S unchanged
H unchanged
```

#### `accent`

```
S_new = min(S + 10, 95)
L_new = clamp(L, 55, 80)
H unchanged
```

Boost saturation for visibility on dark canvas; mid-range lightness.

### Auto-computed companions in dark mode

Same formulas as light mode, but applied to the **resolved dark slot
values**. So `--customify-color-on-secondary-dark = pickOn(resolved
secondary-dark)`, etc.

Note one direction-flip: `--customify-color-primary-hover-dark` blends
toward white instead of black:

```
primary-hover       = color-mix(in srgb, primary,      #000 15%)   /* light mode */
primary-hover-dark  = color-mix(in srgb, primary-dark, #fff 12%)   /* dark mode */
```

Hover stays visible against dark surfaces.

---

## Per-palette examples

Concrete values for the 4 shipping presets.

### Ashwood (warm earth tones)

| Slot | Light | Dark |
|---|---|---|
| `base` | `#F9F3E4` cream | `#1A1410` near-black warm |
| `text` | `#1A3A28` forest ink | `#F2EAD8` cream ink |
| `primary` | `#B35932` terracotta | `#E07A4F` brighter terracotta |
| `secondary` | `#1C2147` navy | `#E8DCC4` cream (auto-flip) |
| `accent` | `#F5DE9A` butter | `#FFD56B` saffron |
| `surface` | `#FFFFFF` white | `#28201A` lifted base-dark |

A footer `.dark-mode` band paints navy in light context, cream in dark
trigger context.

### Midnight (already-dark palette)

| Slot | Light | Dark |
|---|---|---|
| `base` | `#0B0D10` near-black | `#0B0D10` (mirror) |
| `text` | `#F2F0EB` cream ink | `#F2F0EB` |
| `primary` | `#FF7A45` orange | `#FF7A45` |
| `secondary` | `#FFFFFF` white | `#FFFFFF` |
| `accent` | `#FFD36A` saffron | `#FFD36A` |
| `surface` | `#1C1F26` lifted | `#1C1F26` |

Midnight ships `dark` = `colors` (mirror) — toggling the trigger
produces no visual change because the palette IS already dark.

### Ocean (cool blues)

| Slot | Light | Dark |
|---|---|---|
| `base` | `#F5F6F4` near-white | `#0A1124` deep navy |
| `text` | `#0F1C33` ink navy | `#E5ECF7` light ink |
| `primary` | `#0055FF` electric blue | `#3D8BFF` brighter blue |
| `secondary` | `#001D4A` navy | `#FFFFFF` (auto-flip) |
| `accent` | `#B8E6FF` ice blue | `#7AB8FF` mid-sky |
| `surface` | `#FFFFFF` white | `#152043` lifted navy |

### Moss (greens)

| Slot | Light | Dark |
|---|---|---|
| `base` | `#F4FAF5` mint white | `#0B1A11` forest dark |
| `text` | `#0F2616` deep green | `#E7F3EB` mint ink |
| `primary` | `#2B9348` leaf green | `#52B86C` brighter leaf |
| `secondary` | `#2B3D28` dark olive | `#E0EAD9` mint band |
| `accent` | `#D9F0B5` lime | `#A8D87E` brighter lime |
| `surface` | `#FFFFFF` white | `#162619` lifted forest |

---

## For developers — how to slot-bind a rule

Pattern: replace any hardcoded hex / SCSS colour literal with a `var()`
that uses the slot var as the primary value and the literal as the
fallback. Sites without an active palette still see the original look;
sites with a palette get palette-driven values.

```scss
/* Before */
.my-section {
    background: #303030;
    color: #ffffff;
    border: 1px solid rgba(0,0,0,0.12);
}

/* After */
.my-section {
    background: var(--customify-color-secondary,        #303030);
    color:      var(--customify-color-on-secondary,     #ffffff);
    border:     1px solid var(--customify-color-border-default, rgba(0,0,0,0.12));
}
```

### Rules of thumb for picking a slot

| Want… | Use |
|---|---|
| Page bg | `base` |
| Body text / headings | `text` |
| Card / modal bg | `surface` |
| Card text | `on-surface` |
| Brand button bg | `primary` |
| Brand button text | `on-primary` |
| Dark sectional band bg | `secondary` |
| Dark sectional band text | `on-secondary` |
| Decorative highlight (badge, sale tag) | `accent` |
| Muted text (date, meta) | `text-muted` |
| Disabled text | `text-subtle` |
| Divider, hr, table border | `border-default` |
| Hover for primary | `primary-hover` |
| Subtle primary-tinted bg | `primary-subtle` |

### Don't

- **Don't** use `!important` to win specificity wars — investigate
  cascade order instead.
- **Don't** hardcode hex without a `var()` wrapper — visitors with an
  active palette will see your hex instead of their palette.
- **Don't** use both `background` shorthand and `background-color`
  longhand on the same element across different rules — they race on
  specificity. Pick one form per concern.
- **Don't** add `--customify-color-on-secondary: var(...)` overrides
  on slot-bound rules — `pickOn` derives correctly when bg is
  slot-bound; the override blocks it. (See PHASE-7-PLAN §17 for the
  history of why this used to be necessary.)

### Block patterns / inline styles

Block patterns (`patterns/*.php`) often inject inline `style="…"` —
those win the cascade automatically (specificity `(1,0,0,0)`). If you
need a pattern to follow the palette, use `var()` directly inside the
inline style or remove the inline style and let the theme's slot-bound
SCSS take over.

---

## Where it's all defined

| Concern | File |
|---|---|
| 6 slot definitions, theme presets, descriptions | `class-preview-colors-config.php` |
| Customizer setting registration + control | `class-preview-colors-customizer.php` |
| `<style>:root { … }</style>` emission | `class-preview-colors.php::output_root_vars()` |
| Trigger block (`.dark-mode { rebind }`) | same file, same method |
| Dark-mode derive algorithm + 5-tier chain | `class-preview-colors-dark.php` |
| L4/L5 fallback baselines | `dark-baselines.php` |
| The override layer that consumes all the vars | `src/frontend/scss/overrides.scss` |
| Theme-internal rules (button base, etc.) | `src/frontend/scss/base/_base.scss` + others |
