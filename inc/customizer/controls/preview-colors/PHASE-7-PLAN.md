# Preview Colors — Phase 7: Dark mode integration

Add a **dark-mode companion** to the 6-slot palette so any element carrying
`.dark-mode` (or an equivalent trigger) re-paints its subtree using
inverted-luminosity slot colours, while the rest of the page stays in the
default (light) mode.

Status: planning only — no code yet.

Source reference: Customify Style Pack — §"Dark mode (local skin)".
Existing in-theme convention: see [`src/frontend/scss/base/_skins.scss`](../../src/frontend/scss/base/_skins.scss)
and [`inc/customizer/configs/header/panel.php`](../customizer/configs/header/panel.php).

## 1 — Existing `.dark-mode` in the theme

`.dark-mode` is already a **local skin class**, NOT a global / OS dark
mode. Today it's applied per header / footer row via the
`<section>_skin_mode` Customizer setting; the row markup ends up as:

```html
<div class="header--row-inner header_top-inner dark-mode">…</div>
<div class="footer--row-inner footer_top-inner dark-mode">…</div>
```

The current SCSS in `_skins.scss` swaps a small set of header / footer
specific colours to `$light_color`, `$light_bg`, etc. — fixed greyscale
tints from `utils/_vars.scss`. It does **not** touch:

- body / heading / link colours
- WooCommerce surfaces
- the new `--customify-color-<slot>` token set

Phase 7 generalises the pattern: any subtree with `.dark-mode` (or any of
the additional triggers in §6) re-binds the 6 slot vars + their
auto-computed companions to a **dark variant** of the active palette, so
all of `overrides.scss`'s rules paint correctly inside that subtree
without per-rule duplication.

## 2 — Doctrine

| Principle | Detail |
|---|---|
| **Local, not global** | A page can host both a light header and a dark footer. Dark mode is class-scoped, not `:root`-scoped. |
| **No new selectors in `overrides.scss`** | The override layer keeps painting via `var(--customify-color-<slot>)`. Dark mode works by **rebinding those vars** inside `.dark-mode { … }`. Zero rule duplication. |
| **Palette declares dark, theme derives if missing** | A palette can ship a `dark` companion (six hex values). If absent, the theme auto-derives one server-side (invert lightness, keep hue + saturation). |
| **Auto-computed companions follow** | `on-*`, `text-muted`, `border-default`, `primary-hover`, etc. are recomputed from the dark variant — same algos, different inputs. |
| **No `prefers-color-scheme` auto-toggle** | Style Pack convention: respecting OS dark mode is opt-in via theme setting (deferred to Phase 7.4); default behaviour stays explicit class trigger only. |
| **Backward compatible with `_skins.scss`** | Existing `.dark-mode` rules still resolve — they reference SCSS vars, not the slot tokens. The new layer adds slot rebinding without removing the existing skin rules. |

## 3 — Storage shape

Extend the user-palette JSON. Today:

```json
{ "id": "user_1234", "name": "My brand",
  "colors": { "base": "#FFF", "text": "#111", "primary": "#0055FF",
              "secondary": "#001D4A", "accent": "#B8E6FF", "surface": "#FFF" } }
```

Phase 7:

```json
{ "id": "user_1234", "name": "My brand",
  "colors": { "base": "#FFF", "text": "#111", "primary": "#0055FF",
              "secondary": "#001D4A", "accent": "#B8E6FF", "surface": "#FFF" },
  "dark": {
    "base": "#0B0D10", "text": "#F2F0EB", "primary": "#3D8BFF",
    "secondary": "#FFFFFF", "accent": "#7AB8FF", "surface": "#1C1F26"
  } }
```

The `dark` key is **optional**. Sanitiser accepts either:

- 6 hex strings (one per slot) — strict.
- A subset of slots — the rest fall through to auto-derivation per §5.
- Missing key entirely — fully auto-derived.

`Customify_Preview_Colors_Ajax::sanitize_palettes()` extends to accept
`dark` with the same hex regex as the existing `colors` payload.

Theme presets get explicit `dark` companions baked in — see §4.

## 4 — Theme-preset dark companions (curated)

The four shipped presets get hand-picked dark variants. Auto-derivation
exists as a fallback for user palettes; for the bundled presets we ship
explicit values so the demo experience is intentional, not arithmetic.

| Preset (light) | Slot | Light hex | Dark hex | Rationale |
|---|---|---|---|---|
| **Ashwood** | base | `#F9F3E4` | `#1A1410` | Cream paper → warm near-black |
| | text | `#1A3A28` | `#F2EAD8` | Dark forest ink → cream ink |
| | primary | `#B35932` | `#E07A4F` | Same hue, +12% lightness for contrast on dark |
| | secondary | `#1C2147` | `#E8DCC4` | Inverted: dark navy band → cream band |
| | accent | `#F5DE9A` | `#FFD56B` | Warmer / more saturated to pop on dark |
| | surface | `#FFFFFF` | `#28201A` | Card surface inverted (slightly lighter than base) |
| **Midnight** | — | (already dark by design) | (`dark` mirrors `colors` 1:1) | Already inverted; dark variant = light variant |
| **Ocean** | base | `#F5F6F4` | `#0A1124` | Cool grey → deep navy |
| | text | `#0F1C33` | `#E5ECF7` | Inverted |
| | primary | `#0055FF` | `#3D8BFF` | Lifted lightness for AA on dark surface |
| | secondary | `#001D4A` | `#FFFFFF` | Dark band → white band (Midnight pattern) |
| | accent | `#B8E6FF` | `#7AB8FF` | Slightly more saturated for visibility |
| | surface | `#FFFFFF` | `#152043` | Card lifted from base |
| **Moss** | base | `#F4FAF5` | `#0B1A11` | Pale green → forest near-black |
| | text | `#0F2616` | `#E7F3EB` | Inverted |
| | primary | `#2B9348` | `#52B86C` | +15% lightness |
| | secondary | `#2B3D28` | `#E0EAD9` | Inverted |
| | accent | `#D9F0B5` | `#A8D87E` | More saturated |
| | surface | `#FFFFFF` | `#162619` | Card lifted from base |

Source file: [`class-preview-colors-config.php`](class-preview-colors-config.php)
— extend each preset with a `'dark' => array(…)` sibling to `'colors'`.

## 5 — Auto-derivation (when palette has no `dark`)

For user palettes (or theme presets that haven't specified one yet), the
theme computes a dark variant on first use. The algorithm runs server-side
in PHP and is mirrored in JS for the live panel.

| Slot | Light → Dark derivation |
|---|---|
| `base` | Invert lightness (HSL): `L_dark = 100 − L_light`, clamp to `[5, 12]`. Keeps the original hue but produces a near-black canvas. |
| `text` | Invert lightness: `L_dark = 100 − L_light`, clamp to `[88, 96]`. Yields a near-white ink that retains the source hue (warm-ink palettes stay warm in dark). |
| `surface` | Lift `base_dark` by +6% lightness, same hue. Gives a "card slightly lighter than canvas" relationship matching the light-mode convention. |
| `primary` | Boost lightness toward `[55, 70]` while preserving hue + saturation. Skip if already in range. Ensures AA contrast on dark `surface`. |
| `secondary` | If light `secondary` was darker than `surface`: invert (becomes a light band on dark). If lighter: nudge slightly darker but stay above `surface`. (Detected by comparing source luminances.) |
| `accent` | Increase saturation by +10% absolute, lightness clamped to `[55, 80]`. Decorative slot needs to pop visibly on dark canvas. |

Pseudo-code:

```js
function deriveDarkSlot(slot, hex, sourceColors) {
  const [h, s, l] = rgbToHsl(hexToRgbArray(hex));
  switch (slot) {
    case 'base':      return hslToHex([h, s, clamp(100 - l, 5, 12)]);
    case 'text':      return hslToHex([h, Math.min(s, 30), clamp(100 - l, 88, 96)]);
    case 'surface': {
      const baseDark = deriveDarkSlot('base', sourceColors.base);
      const [bh, bs, bl] = rgbToHsl(hexToRgbArray(baseDark));
      return hslToHex([bh, bs, Math.min(bl + 6, 18)]);
    }
    case 'primary':   return hslToHex([h, s, clamp(l, 55, 70)]);
    case 'secondary': {
      const lumLight = relativeLuminance(hexToRgbArray(sourceColors.surface));
      const lumThis  = relativeLuminance(hexToRgbArray(hex));
      return lumThis < lumLight
        ? hslToHex([h, Math.max(s - 10, 0), clamp(100 - l, 80, 95)])  // dark band → light band
        : hslToHex([h, s, Math.max(l - 10, 20)]);                       // light band → darker (but > base_dark)
    }
    case 'accent':    return hslToHex([h, Math.min(s + 10, 95), clamp(l, 55, 80)]);
  }
}
```

Lives in:

- PHP: a new helper class `Customify_Preview_Colors_Dark::derive($colors)` returning the 6-slot dark array.
- JS: `deriveDark(colors)` in both `src/preview-colors/preview-colors.js` and `src/preview-colors/customizer/customizer.js`. The Customizer panel uses it for live preview without an AJAX round-trip.

## 6 — Trigger selectors

Phase 7 binds the dark-mode rebinding to **multiple selector forms** so
existing markup, future page-builder blocks, and Gutenberg block-style
variations all light up consistently:

| Selector | Source / Why |
|---|---|
| `.dark-mode` | Existing theme convention (header / footer rows). |
| `.is-dark-mode` | Block-style alias (`is-style-dark` from `register_block_style`). |
| `[data-theme="dark"]` | Common page-builder convention (Elementor, Bricks, GenerateBlocks). |
| `:root.dark-mode` (or `<html class="dark-mode">`) | Site-wide dark mode if a child theme / plugin opts in (e.g. WP DarkMode plugin). |

The actual CSS just enumerates them with the same body, generated once:

```css
.dark-mode,
.is-dark-mode,
[data-theme="dark"] {
  --customify-color-base:      var(--customify-color-base-dark);
  --customify-color-text:      var(--customify-color-text-dark);
  --customify-color-primary:   var(--customify-color-primary-dark);
  --customify-color-secondary: var(--customify-color-secondary-dark);
  --customify-color-accent:    var(--customify-color-accent-dark);
  --customify-color-surface:   var(--customify-color-surface-dark);

  --customify-color-base-rgb:      var(--customify-color-base-dark-rgb);
  --customify-color-text-rgb:      var(--customify-color-text-dark-rgb);
  --customify-color-primary-rgb:   var(--customify-color-primary-dark-rgb);
  /* …etc for the other 3 slots */

  --customify-color-on-primary:    var(--customify-color-on-primary-dark);
  --customify-color-on-secondary:  var(--customify-color-on-secondary-dark);
  --customify-color-on-surface:    var(--customify-color-on-surface-dark);
  --customify-color-text-muted:    var(--customify-color-text-muted-dark);
  --customify-color-text-subtle:   var(--customify-color-text-subtle-dark);
  --customify-color-border-default: var(--customify-color-border-default-dark);
  --customify-color-primary-hover:  var(--customify-color-primary-hover-dark);
  --customify-color-primary-subtle: var(--customify-color-primary-subtle-dark);
}
```

Because `overrides.scss` is wrapped in `html { … }`, every consumer ends
up at specificity `(0,0,2)` (e.g. `html body { color: var(--customify-color-text); }`).
The dark-mode rebinding above lives at `(0,1,0)` — beats `html body`,
which is exactly what we want for elements **inside** a `.dark-mode`
container. Cascade math:

| Painted property | Specificity | Wins over |
|---|---|---|
| Theme SCSS `body { color: $color_text; }` | `(0,0,1)` | — |
| Customizer auto_css `body { color: #abc; }` | `(0,0,1)` | theme SCSS (later in document) |
| `overrides.scss` wrapped: `html body { color: var(--customify-color-text); }` | `(0,0,2)` | both above |
| `.dark-mode { --customify-color-text: …; }` | `(0,1,0)` | rebinds the var resolved by `html body` |

The var-rebinding doesn't even compete on the property; it just changes
what `var()` resolves to inside the subtree. Result: every consumer rule
in `overrides.scss` (Group P, Group T, WooCommerce, etc.) repaints
automatically inside `.dark-mode`. Zero override-layer rule duplication.

## 7 — `:root` emission shape

`Customify_Preview_Colors::output_root_vars()` extends to print **two
sets of vars**: the existing 6 + 8 `--customify-color-*` (light) plus
the same 14 with a `-dark` suffix.

Concretely:

```html
<style id="customify-preview-colors-vars">
:root {
  /* light — already present */
  --customify-color-base: #F9F3E4;
  --customify-color-base-rgb: 249, 243, 228;
  --customify-color-text: #1A3A28;
  --customify-color-text-rgb: 26, 58, 40;
  /* …4 more slots + 8 auto-computed */

  /* dark companion — Phase 7 */
  --customify-color-base-dark: #1A1410;
  --customify-color-base-dark-rgb: 26, 20, 16;
  --customify-color-text-dark: #F2EAD8;
  --customify-color-text-dark-rgb: 242, 234, 216;
  /* …4 more slots + 8 auto-computed-dark */
}

/* trigger selectors rebind the active var to the -dark companion */
.dark-mode,
.is-dark-mode,
[data-theme="dark"] {
  --customify-color-base: var(--customify-color-base-dark);
  /* …14 lines */
}
</style>
```

The trigger block ships once at render time as part of the `<style>`
tag — it's static (always the same 14 reassignments) and small enough
to inline alongside the var defs.

## 8 — `overrides.scss` — what changes, what stays

| Section | Phase 7 change |
|---|---|
| All `var(--customify-color-<slot>, …)` consumer rules | **No change.** Same selectors, same property: value pairs, same fallback hex. They paint correctly inside `.dark-mode` because the `var()` they reference resolves to the rebinding done in §6. |
| Hard-coded literals (`#fff`, `#000`) for text on coloured surfaces | **Already migrated** in Phase 3 to `var(--customify-color-on-*)`. Inside dark mode, `on-*` resolves to the dark variant computed from the dark slot — automatic. |
| `box-shadow: 0 1px 2px 0 rgba(var(--customify-color-text-rgb, …), 0.08);` | **No change.** The `text-rgb` var is also rebound by §6, so the tinted shadow flips to the dark-mode text rgb automatically. Visual effect: shadow tint matches local mode. |
| Border-default, text-muted derivations | **No change** — same reasoning. They consume `--customify-color-border-default` / `--customify-color-text-muted` which §6 rebinds to their `-dark` companions. |
| Gutenberg `--wp--preset--color--*` mirrors | **No change** for the rebound layer — the `--wp--preset--color--text` var maps to `var(--customify-color-text)`, so blocks inside `.dark-mode` automatically pick up the dark text colour without re-registering presets. |

In short: **Phase 7 ships zero edits to `overrides.scss`.** All work is
in PHP (emit twice + emit trigger block) and the panel JS (deriveDark +
expose to the panel UI).

## 9 — Slot mapping table (single-source-of-truth)

| Slot | Light role (Phase 3) | Dark mapping rule | Computed via |
|---|---|---|---|
| `base` | Page canvas | Invert lightness → near-black, hue preserved | `deriveDarkSlot('base', …)` or palette-declared `dark.base` |
| `text` | Body ink | Invert lightness → near-white, hue preserved | `deriveDarkSlot('text', …)` or `dark.text` |
| `primary` | CTA / link | Lift lightness to `[55, 70]` for AA on dark surface | `deriveDarkSlot('primary', …)` or `dark.primary` |
| `secondary` | Dark band | Auto-flip: dark band → light band, light band → darker band | `deriveDarkSlot('secondary', …)` or `dark.secondary` |
| `accent` | Decorative pop | +saturation, mid-lightness | `deriveDarkSlot('accent', …)` or `dark.accent` |
| `surface` | Card background | Lift `base_dark` by +6% lightness | `deriveDarkSlot('surface', …)` or `dark.surface` |
| `on-primary` | text on primary | Recompute WCAG luminance against `primary_dark` | `pickOn(primary_dark)` |
| `on-secondary` | text on secondary | `pickOn(secondary_dark)` | — |
| `on-surface` | text on surface | `pickOn(surface_dark)` | — |
| `text-muted` | meta | `rgba(text_dark_rgb, 0.55)` | — |
| `text-subtle` | disabled | `rgba(text_dark_rgb, 0.35)` | — |
| `border-default` | divider | `rgba(text_dark_rgb, 0.12)` | — |
| `primary-hover` | link hover | `color-mix(in srgb, primary_dark, #fff 12%)` (note: blend toward white in dark mode, not black) | — |
| `primary-subtle` | tinted bg | `color-mix(in srgb, primary_dark, base_dark 92%)` | — |

Note the `primary-hover` direction flip: in light mode it darkens
toward `#000`; in dark mode it lightens toward `#fff`. Without this
flip, hover states on dark backgrounds become harder to distinguish.

## 10 — Fallback chain (resolve order when a value is missing)

A site that hasn't created a palette, or whose palette ships only the
light slots, must still render dark mode correctly. Phase 7 layers the
derivation in five tiers — same shape as Phase 6 v2.0's legacy chain,
extended to cover the dark variant.

### 10.1 — Chain (light mode, recap from Phase 6)

```
palette.colors.<slot>
 → --legacy-<slot>            (Phase 6 compat from theme_mods)
 → SCSS baseline (styling.php default)
 → hex literal (override fallback in overrides.scss)
```

### 10.2 — Chain (dark mode)

```
palette.dark.<slot>
 → deriveDarkSlot(palette.colors.<slot>)
 → deriveDarkSlot(theme_mod legacy value)            // Phase 6 compat
 → SCSS baseline from _skins.scss (existing dark literals)
 → hex literal
```

PHP composes the resolved hex server-side; the `:root { --customify-color-<slot>-dark: <hex>; }`
declaration always emits a final hex, **never** a chained `var()`. Keeps
the CSS payload small and avoids browser variation in `var()` chain
length limits.

### 10.3 — Per-slot fallback table

| Slot | L1 — palette.dark | L2 — derive(palette.light) | L3 — derive(legacy theme_mod) | L4 — SCSS baseline (dark) | L5 — hex |
|---|---|---|---|---|---|
| `base` | `palette.dark.base` | invert(lightness) of `palette.colors.base` | invert of `body { background }` setting | `$dark_bg` ≈ `rgba(0,0,0,0.9)` flattened → `#1A1A1A` | `#0B0D10` |
| `text` | `palette.dark.text` | invert(lightness) of `palette.colors.text` | invert of `theme_mod.color_text` | `$light_color` ≈ `#FCFCFC` | `#F2F0EB` |
| `primary` | `palette.dark.primary` | lift to L `[55,70]` from `palette.colors.primary` | lift from `theme_mod.color_primary` (or `color_link`) | `$light_color_link_hover` ≈ `#FCFCFC` | `#3D8BFF` |
| `secondary` | `palette.dark.secondary` | auto-flip of `palette.colors.secondary` | auto-flip of `theme_mod.color_secondary` | — (no `_skins.scss` counterpart) | `#FFFFFF` |
| `accent` | `palette.dark.accent` | +saturation, mid-L of `palette.colors.accent` | — (no theme_mod counterpart) | — (no `_skins.scss` counterpart) | `#FFD36A` |
| `surface` | `palette.dark.surface` | `base_dark + 6% L` | from L3 of `base` then `+6% L` | `$dark_bg` lifted | `#1C1F26` |

For slots without a layer-3 source (`accent`, `surface`) the chain
collapses cleanly — PHP's `??` operator skips the missing tier without
emitting a malformed value.

### 10.4 — Auto-computed companions in dark mode

Auto-computed vars (Phase 3) recompute against the **resolved** dark
slot value, regardless of which tier the resolution came from:

| Var | Computation in dark mode |
|---|---|
| `on-primary-dark` | `pickOn(resolved_primary_dark)` |
| `on-secondary-dark` | `pickOn(resolved_secondary_dark)` |
| `on-surface-dark` | `pickOn(resolved_surface_dark)` |
| `text-muted-dark` | `rgba(resolved_text_dark_rgb, 0.55)` |
| `text-subtle-dark` | `rgba(resolved_text_dark_rgb, 0.35)` |
| `border-default-dark` | `rgba(resolved_text_dark_rgb, 0.12)` |
| `primary-hover-dark` | `color-mix(in srgb, resolved_primary_dark, #fff 12%)` (note: blend toward white in dark, opposite of light mode) |
| `primary-subtle-dark` | `color-mix(in srgb, resolved_primary_dark, resolved_base_dark 92%)` |

### 10.5 — Which tier renders for which site state

| Site state | Tier hit | Visual outcome |
|---|---|---|
| Active palette ships explicit `dark` (theme presets after 7.3) | L1 | Designer-curated dark variant — best path |
| Active palette, no `dark` (user-created via panel) | L2 | Algorithmically inverted; same hue family, AA-safe contrast |
| No active palette, Phase 6 seeded `--legacy-*` from theme_mods | L3 | Existing site upgrading: dark mode derived from their old `styling.php` colours, so the dark sections feel related to their brand |
| No active palette, no legacy theme_mods (fresh install) | L4 | Visually identical to today's pre-Phase-7 dark headers/footers (which already use these `_skins.scss` literals) |
| `var()` itself unsupported (legacy IE) | L5 | Static hex — degrades to `Midnight` preset's defaults |

### 10.6 — JS resolution (live panel preview)

The Customizer panel + frontend overlay need the same chain so live
edits match what visitors will see post-publish. `wp_localize_script`
ships two extra blobs:

```js
window.CustomifyPreviewColors.legacyMods   // { color_text: '#abc', … } from Phase 6
window.CustomifyPreviewColors.scssBaseline // { base: '#1A1A1A', text: '#FCFCFC', … }
```

Then in `customizer.js` / `preview-colors.js`:

```js
function resolveDarkSlot(slot, palette) {
  const cfg = window.CustomifyPreviewColors;
  return (
    palette?.dark?.[slot] ||
    (palette?.colors?.[slot] && deriveDarkSlot(slot, palette.colors[slot])) ||
    (cfg.legacyMods?.[legacyKeyFor(slot)] && deriveDarkSlot(slot, cfg.legacyMods[legacyKeyFor(slot)])) ||
    cfg.scssBaseline[slot] ||
    cfg.hexBaseline[slot]
  );
}
```

`legacyKeyFor(slot)` maps `text → color_text`, `primary → color_primary`,
etc. — the same mapping table from PHASE-6-PLAN §1.

### 10.7 — Implementation notes

- **Pure & memoised**: `Customify_Preview_Colors_Dark::derive()` is
  input → output, cache the result in a per-request static array (no
  transient). At ~60µs / palette the cost is negligible; static cache
  is just an obvious win.
- **Never persist derived values**: derivation lives in compute, not
  storage. A future tweak to the algorithm propagates on next render —
  no one-shot migration needed.
- **PHP and JS share fixtures**: a small JSON file
  (`inc/preview-colors/dark-baselines.json`) holds the L4 + L5 tables
  so PHP and JS agree without two source-of-truths drifting.
- **Cascade lives server-side**: `output_root_vars()` resolves the
  whole chain and emits a single hex per `--…-dark` var. CSS doesn't
  see the chain, just the answer.

## 11 — Panel UI changes

### 11.1 Frontend overlay (`?preview-colors=1`)

- Add a **mode toggle** in the deck footer: `[ ☀ Light | 🌙 Dark ]`.
  - State held client-side in a React state var (or vanilla equivalent),
    not persisted to options. Switching the toggle adds a `.dark-mode`
    class to the `<html>` element so the entire frontend reflects the
    choice for preview purposes.
  - Toggle is preview-only — does NOT save anything to options. The
    real "is this site dark by default?" decision lives in §12.

- The HeroDeck and `.color-dot` rows show **two columns of swatches**
  per slot when a `dark` companion exists: light hex on the left, dark
  hex on the right. Editing either opens the colour picker for that
  variant.

- For palettes without an explicit `dark`, the right column shows the
  auto-derived value with a small "auto" tag and a **"Customise dark
  mode"** button that materialises the derived values into editable
  hexes (turns the palette into one with explicit `dark`).

### 11.2 Customizer control

- Same toggle + dual-column swatches.
- Live preview iframe receives both light + dark vars on every change
  via the existing `postMessage` bridge in `preview.js`. Adding the
  `<html class="dark-mode">` toggle from the panel rebinds in real time.

## 12 — Site-wide dark mode (deferred to Phase 7.4)

A site setting `customify_preview_dark_default` (theme-mod or option)
that, when enabled, adds `.dark-mode` to `<html>` for every visitor.
Optional helper: respect `@media (prefers-color-scheme: dark)` instead
of an explicit class — implemented as:

```css
@media (prefers-color-scheme: dark) {
  :root:not(.force-light-mode) {
    --customify-color-base: var(--customify-color-base-dark);
    /* …14 lines */
  }
}
```

Two trigger modes (theme-mod):

1. `'off'` (default) — no auto-application; dark mode only inside
   explicit `.dark-mode` subtrees.
2. `'auto'` — respects `prefers-color-scheme`.
3. `'always'` — `<html class="dark-mode">` always.

This lives in Phase 7.4 because it touches the visitor experience and
needs a clear admin UX (toggle in the Customizer "Color palette"
control or a new "Dark mode" sub-section).

## 13 — Backward compatibility with `_skins.scss`

The existing skin SCSS in `base/_skins.scss` paints fixed greyscale
tints (`$light_color`, `$light_bg`, …) inside `.dark-mode` for header
/ footer specific elements (search bar, nav menu hover). After Phase 7:

- Those rules **continue to paint** — they use SCSS literals, not the
  slot tokens. No regression for existing sites.
- New rules in `overrides.scss` (the var-rebinding layer) **layer on
  top** — same `.dark-mode` subtree now also gets palette-aware text /
  bg / border colours.
- A future cleanup (out of scope here) could migrate `_skins.scss` rules
  to consume the slot tokens too, removing the duplication. Not
  required for v7.0.

## 14 — Phasing

| Phase | Deliverable |
|---|---|
| **7.1** | PHP: extend `output_root_vars()` to emit `-dark` suffixed vars + the trigger block. Server-side `Customify_Preview_Colors_Dark::derive()` for palettes without explicit `dark`. Full fallback chain (§10) implemented including legacy + `_skins.scss` baseline tiers. Sanitiser accepts `dark` payload. |
| **7.2** | JS: `deriveDark()` + `resolveDarkSlot()` in both panel bundles. `wp_localize_script` ships `legacyMods` + `scssBaseline` blobs. Mode toggle in HeroDeck, dual-swatch column in SettingsRows. Customizer preview rebroadcasts both var sets. |
| **7.3** | Theme presets (Ashwood / Ocean / Moss) ship explicit `dark` companions per §4. Midnight already inverted — `dark` = `colors`. |
| **7.4** | Site-wide setting (`'off'` / `'auto'` / `'always'`) per §12. Frontend visitor receives the chosen behaviour. |
| **7.5** | Optional cleanup pass on `_skins.scss` to consume slot tokens (removes ~30 lines of fixed-tint duplication). |

## 15 — Open questions

| Question | Resolution path |
|---|---|
| Auto-derivation algorithm — clamp ranges OK on extreme palettes (very saturated / very desaturated)? | Validate during 7.1 implementation against Ashwood / Midnight / Ocean / Moss + 5 user-test palettes (warm, cool, monochrome, pastel, neon). Adjust clamps if any preset's auto-derived dark fails AA contrast against its derived `surface_dark`. |
| Does the panel persist the light/dark preview toggle? | No — preview-only. The real default lives in §12 (Phase 7.4). Persisting the toggle would conflict with the per-section skin-mode setting in `header/panel.php`. |
| Backward compat: existing sites using `_skins.scss` for header / footer dark — do their colours change after 7.1 ships? | **Yes, slightly.** Body / link / heading colours inside the dark header now follow the active palette's dark variant, not `$light_color`. Acceptance: this is the intended behaviour (a single source of truth for colours); pre-7 behaviour is documented as "header/footer dark used a fixed greyscale, not palette-aware". Migration note in changelog. |
| Should `dark.<slot>` accept partial values (e.g. only `text` + `primary`)? | Yes — per §3 sanitiser. Missing slots fall through to auto-derivation. Allows demos to override only the slots that need correction (e.g. brand `primary` needs +15% lightness, the rest can stay derived). |
| `prefers-color-scheme` precedence vs explicit `.dark-mode`? | Explicit class always wins over media query. Trigger order: explicit `.dark-mode` subtree → `:root.dark-mode` (site-wide always) → `@media (prefers-color-scheme: dark)`. Encoded by selector specificity + cascade, no JS needed. |
| Block editor: does dark mode apply inside Gutenberg's editor iframe? | Out of scope for 7.x. The editor uses `editor-styles-wrapper` not `.dark-mode`. Future phase could mirror the trigger inside the editor when WP introduces "dark editor" UX. |

## 16 — Contrast safety net for hardcoded `.dark-mode` rules (Phase 7.6)

### 16.1 — The issue

Phase 7.1's trigger block rebinds `--customify-color-<slot>` and the
auto-computed `on-*` companions inside `.dark-mode`. This works perfectly
when **bg comes from a slot var** (text + bg rebind together). It fails
when **bg is a hardcoded literal** in SCSS or auto_css output.

Concrete example reported in production:

```scss
/* src/frontend/scss/footer/_footer-common.scss:42 */
.footer-main { .dark-mode { background: #303030; } }

/* src/frontend/scss/overrides.scss */
.site-footer p { color: var(--customify-color-on-secondary, #fff); }
```

For palette **Ashwood** in dark mode:
- `secondary-dark` = `#E8DCC4` (light cream — auto-flip pattern from §5)
- `on-secondary-dark` = `pickOn(#E8DCC4)` = `#1A1A1A` (black)
- Visible bg = `#303030` (literal, not rebound)
- Painted text = black on `#303030` → **unreadable**

The rebound `on-secondary` was correct for the rebound `secondary` slot
(black text on cream bg), but the actual bg never participated in the
rebinding because it's a literal.

### 16.2 — Audit of hardcoded `.dark-mode` / `.light-mode` literals

Found via `grep -rn "light-mode\|dark-mode" src/frontend/scss/`:

| File | Lines | Light bg | Dark bg | Migration option |
|---|---|---|---|---|
| `footer/_footer-common.scss` | 24, 27, 39–44, 61–65, 71–83 | `#f9f9f9` | `#303030` | M1 (slot-bind) |
| `header/_header_main.scss` | 3, 6 | `#FFFFFF` | `#1a1a1a` | M1 (slot-bind) |
| `header/_header_top.scss` | 11–15 | `#f0f0f0` | `$color_primary` | already palette-aware |
| `header/_header_bottom.scss` | 6, 10 | `#f0f0f0` | `#303030` | M1 (slot-bind) |
| `header/_header_mobile_sidebar.scss` | 65, 109, 252, 259 | `$dark_color` etc. | `$dark_bg` | M2 (already alpha-tinted, paints text+bg together) |
| `base/_skins.scss` | 3–100 | `$light_color` | `$dark_bg` | M2 (alpha-on-white system) |
| `widgets/_widgets.scss` | 353, 775 | — | only borders/text via `$light_color_border` | M2 (alpha-only, no bg conflict) |

Two patterns dominate:

- **Pattern A — opaque hex bg, no paired text rule** (`_footer-common.scss`,
  `_header_*`, `_header_bottom.scss`): bg painted in standalone
  `.dark-mode { background: <hex> }` rule, text paint comes from
  `overrides.scss` via slot-bound `on-*` var. This is where the contrast
  break happens.
- **Pattern B — paired greyscale tints in same rule** (`_skins.scss`,
  `_widgets.scss`, `_header_mobile_sidebar.scss`): SCSS vars
  (`$light_color`, `$dark_bg`) painted alongside their text companion in
  the same selector. Already self-consistent — bg + text move together.

Phase 7.6 only addresses Pattern A. Pattern B is correct as-is.

### 16.3 — Two new semantic vars (slot-independent)

Emit at `:root` from `output_root_vars()`. **Never rebound** by the
trigger block — these are FIXED contrast tokens.

```css
:root {
  --customify-color-on-dark-bg:  #FCFCFC;  /* light text for fixed dark surfaces */
  --customify-color-on-light-bg: #1A1A1A;  /* dark text for fixed light surfaces */
}
```

Use case: rules whose bg is a literal (not bound to a slot) pair their
text with `var(--customify-color-on-dark-bg)` / `--on-light-bg` instead
of slot-bound `on-*`. Guaranteed contrast regardless of palette state.

### 16.4 — Two-track authoring rule

Going forward, every `.dark-mode` rule belongs to exactly one of these
tracks:

| Track | When to use | bg paint | text paint |
|---|---|---|---|
| **Slot-bound** | Section meant to follow the active palette (footer, headers, dark CTAs) | `var(--customify-color-secondary)` etc. | `var(--customify-color-on-secondary)` etc. |
| **Fixed-literal** | UI chrome / utility surfaces that should stay visually identical regardless of palette | `#303030` literal | `var(--customify-color-on-dark-bg)` |

Mixing tracks (slot-bound bg + fixed-literal text or vice versa) is the
exact bug §16.1 describes. Lint check could catch this in CI later;
out-of-scope for v7.6.

### 16.5 — Migration options (per audit row)

#### M1 — Bind bg to a slot (recommended where the surface is brand-thematic)

```scss
/* before */
.footer-main { .dark-mode { background: #303030; } }

/* after */
.footer-main { .dark-mode { background: var(--customify-color-secondary, #303030); } }
```

After M1, the bg **does** rebind in dark mode (becomes
`var(--customify-color-secondary-dark)` via the trigger block). The text
rule's existing `var(--customify-color-on-secondary)` is now correct
again — both bg and text rebind in lockstep.

Visual change: footer / header band now reflects the active palette's
secondary slot. Sites that intentionally wanted a fixed `#303030` band
regardless of palette pick **M2** instead.

#### M2 — Keep literal bg, force-light text via local var override

```scss
.footer-main {
  .dark-mode {
    background: #303030;  /* unchanged */
    /* Local override beats the trigger block via specificity:
       (.footer-main .dark-mode) is more specific than (.dark-mode). */
    --customify-color-on-secondary: var(--customify-color-on-dark-bg);
  }
}
```

Pros: visual unchanged for sites not using the panel.
Cons: keeps the literal hex hardcoded; bg can't follow palette.

#### M3 — Direct text override on the consumer rule

Skip the trigger machinery entirely for this subtree:

```scss
/* in overrides.scss */
.footer-main .dark-mode :is(.site-footer, .site-footer p, .site-footer a) {
  color: var(--customify-color-on-dark-bg);
}
```

Use only when the consumer rule is co-located with the `.dark-mode` host
(e.g. footer text rules in overrides.scss already, footer dark-mode rule
in `_footer-common.scss`). Cleaner than M2 if the override layer is the
right place to encode the "this subtree gets force-light" rule.

### 16.6 — Recommended decision matrix

| Section | Choice | Rationale |
|---|---|---|
| `.footer-main .dark-mode` (`#303030`) | **M1** | Footer is the canonical "secondary band". Slot-binding makes it palette-aware automatically. |
| `.footer-bottom .dark-mode` | **M1** | Same as above. |
| `.header-main .dark-mode` (`#1a1a1a`) | **M1** | Same. |
| `.header-bottom .dark-mode` (`#303030`) | **M1** | Same. |
| `.header-top .dark-mode` (`$color_primary`) | **already correct** | Bound to primary slot via SCSS var; participates in palette already. No-op. |
| `_skins.scss` rules (`$light_color`, `$dark_bg`) | **leave** | Pattern B — text+bg painted in same rule. Self-consistent. |
| `_widgets.scss` `.dark-mode` blocks | **leave** | Only paint borders/text via alpha tints (`$light_color_border` etc.). No literal-bg conflict. |
| `_header_mobile_sidebar.scss` | **leave** | Pattern B — all paints use SCSS greyscale vars; no slot interference. |

After M1 migration the visible band colour changes from a fixed grey to
the active palette's secondary slot. **Two acceptance scenarios** to
verify:

1. Default install (no panel activated) — `--customify-color-secondary`
   resolves to `var(--legacy-secondary)` (Phase 6 chain) → falls back to
   `#c3512f` if no theme_mod set. Footer goes from grey to terracotta.
   This IS a behaviour change for fresh installs; document in changelog.
2. Existing palette in light mode — footer paints `secondary` slot
   (e.g. Ashwood `#1C2147` navy). In dark mode → `secondary-dark` (Ashwood
   cream `#E8DCC4`). Auto-flip works correctly.

If the behaviour change in scenario 1 is unacceptable, switch matrix
default to M2 for the four hardcoded rows; the contrast-safety win still
applies but the bg stays grey.

### 16.7 — `output_root_vars()` change

Add the two slot-independent semantic vars to the `:root { … }` block —
emitted unconditionally, not gated on having an active palette:

```php
$decls .= '--customify-color-on-dark-bg:#FCFCFC;';
$decls .= '--customify-color-on-light-bg:#1A1A1A;';
```

Trigger block does NOT touch these. They stay constant across light /
dark / palette switches.

### 16.8 — Phasing

| Phase | Deliverable |
|---|---|
| **7.6.1** | Emit `--customify-color-on-dark-bg` + `--customify-color-on-light-bg` from PHP. Document the two-track pattern (this section). |
| **7.6.2** | Apply M1 to the four hardcoded literal rows (`_footer-common.scss`, `_header_main.scss`, `_header_bottom.scss`). Visual regression test against Ashwood + Ocean + Moss in both light + dark. |
| **7.6.3** | (Optional) M3 cleanup pass on `overrides.scss` for sections where matrix prefers M2 — adds local on-* overrides per subtree without touching the SCSS literals. |

### 16.9 — Open questions

| Question | Resolution |
|---|---|
| Default to M1 (palette-aware) or M2 (preserve `#303030` band)? | **M1** for v7.6.2 — aligns with the Style Pack doctrine that `secondary` is the canonical dark-band slot. Sites that need a fixed band can opt out via a child-theme override. Document in changelog. |
| Should `on-dark-bg` / `on-light-bg` honour `prefers-color-scheme`? | No — they're FIXED tokens. Honoring the media query would defeat their purpose. Site-wide dark mode (Phase 7.4) handles auto-toggle separately. |
| Add a lint check for "literal bg + slot-bound text" combination? | Out of scope. Future improvement: a small `npm run lint:dark-mode` script that greps SCSS for literal `background:` paired with same selector inside `.dark-mode`, warns if no matching local `--customify-color-on-*` override. |
| Do existing block patterns (`patterns/*.php`) carry inline `style="background:…"` that could exhibit the same bug? | Probably yes — block patterns hardcode styles via `wp:cover` etc. Out of scope for Phase 7.6 (block-level fix is a separate Gutenberg-specific phase). |

## 17 — Out of scope (this plan)

- **Per-block dark variant** (e.g. a Cover block with `.is-style-dark`)
  beyond what the §6 trigger list already covers — those automatically
  work because `[data-theme="dark"]` and `.is-dark-mode` are listed.
- **Auto-tone images** (filter / blend-mode in dark mode) — separate
  module.
- **Colour-blind / high-contrast modes** — distinct accessibility
  feature, not part of dark mode.
- **`success` / `warning` / `danger` / `info` semantic colours dark
  variants** — the theme ships fixed-hue values in both modes; users
  don't theme these (Style Pack convention).
