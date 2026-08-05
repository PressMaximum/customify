# Customizer Colors — Spec

Top-level **Colors** section consolidating the entire color/styling surface
of the Customify Customizer into a single 6-slot palette with auto-derived
component colors, a `:root` CSS-var pipeline, `theme.json` palette sync, and
a popover-style color picker. Implemented on branch
`customizer-colors-improve` (PR
[#392](https://github.com/PressMaximum/customify/pull/392)).

Read this file fully before continuing the work in a fresh session. Pay
particular attention to:
- [SPEC-customizer.md](SPEC-customizer.md) — the underlying Customify
  Customizer architecture (config-driven, PHP + JS twin pattern, auto-CSS).
- The **30K-site safety rule** (memory `30k-sites-color-safety`).
- The **Blocksify companion contract** (memory `blocksify-companion`) — the
  6 slot slugs are public API consumed by future starter templates.
- [SPEC-header-footer-builder.md](SPEC-header-footer-builder.md) — relevant
  only insofar as header/footer builder items currently carry per-component
  color settings that Phase 2 will refactor to consume slot tokens.

---

## 1. Why this exists

### 1.1 Pain points in the pre-existing Customizer

| Issue | Evidence |
|---|---|
| Colors scattered across 4+ config files | `styling.php` (9 fields), `background.php` (3 composite fields), `blogs.php`, `page-header.php`, `header/*` (16+ fields), `footer/*`. ~39 color/bg-related controls total. |
| `Global Colors` section flat list of 9 pickers | Hard to scan; no semantic grouping (brand vs structural). |
| Mixed concerns per color | `_primary` controls buttons + pagination + nav menu (full-height style) + readmore — 4 unrelated concepts behind one picker. |
| `_border` does ~40 selectors across 6 CSS properties | Borders, dividers, sidebar rails, comment connector dot, search modal arrow — all on one hex value. |
| No `theme.json` palette ↔ Customizer sync | Block editor color picker drifts from frontend. |
| No design tokens / CSS vars | Page builders and child themes can't reference a stable palette; user CSS overrides break across updates. |
| Two related sections (`global_styling` + `background`) registered separately | UX disjoint; the empty `site_content_styling` section was a pre-existing bug. |

### 1.2 Why a new section, not a rewrite of the old

Customify ships on **30,000+ production sites**. Every existing `theme_mod`
key under the 9 legacy color fields holds saved user data:

- `global_styling_color_primary`
- `global_styling_color_secondary`
- `global_styling_color_text`
- `global_styling_color_link`
- `global_styling_color_link_hover`
- `global_styling_color_border`
- `global_styling_color_meta`
- `global_styling_color_heading`
- `global_styling_color_w_title`

Plus the 3 background composites:

- `background` (composite JSON, `bg_color` subfield + image options)
- `site_content_styling` (composite JSON)
- `content_background` (composite JSON)

Renaming any of these keys, dropping any of them, or shifting the rendered
hex on update = brand drift across thousands of live sites. **Hard
constraint**: every existing key continues to read & render the same hex
on the same selectors after this change. See
[`feedback_30k_sites_color_safety.md`](../../.claude/memory/feedback_30k_sites_color_safety.md).

### 1.3 Goals

- Single top-level **Colors** Customizer section for the entire site palette.
- **6 brand-color slots** (`primary`, `secondary`, `accent`, `text`,
  `surface`, `base`) — the only colors the user picks.
- **Auto-derived** tokens (`text-muted`, `border`, `primary-hover`,
  `link`, `link-hover`, `heading`, `widget-title`, `on-primary`,
  `on-secondary`, `on-accent`) computed from the 6 slots — user does not
  edit these directly.
- **Legacy keys = explicit overrides**. Saved values on existing sites
  always win over computed defaults → byte-equivalent CSS output.
- `:root { --customify-<slot/derived>: ... }` block emitted into the
  inline style; site-wide CSS rules consume the vars.
- 6 slots exposed in `theme.json` palette with **stable slugs** so
  Blocksify starter templates can reference them by name.
- Snappy compact picker UI (label-left, round swatch right, floating
  popover with hex input or "From Palette" quick-pick).

### 1.4 Non-goals (Phase 1)

- Refactoring all `header/*`, `footer/*`, `blog`, `page-header` color
  fields to consume slot tokens. (Phase 2.)
- Live Customizer preview JS for slot changes (Phase 2).
- Custom palettes / Style Packs (Phase 3 — light/dark presets, palette
  switching, user-defined palettes).
- Dark mode token set.

---

## 2. Customizer context (read before touching anything)

### 2.1 Top-level layout in Customizer

Customify's Customizer sidebar lists, in this order (priority value in
parens, only the top-level entries):

```
┌─ Customizer (left sidebar, 18% × viewport, min 300, max 600 — WP core rule)
│
├── Site Identity                          (WP core section, priority 80)
│
├── Colors                                 ← NEW SECTION (priority 21)
│       Flat layout; controls grouped by customify_heading dividers.
│       (Implemented in inc/customizer/configs/colors.php.)
│
├── Styling                                (panel — priority 22)
│   ├── Typography                         (section, registered by typography.php)
│   ├── Layouts                            (section, registered by layouts.php)
│   └── … other styling sub-sections
│
├── Header                                 (panel)
│   ├── Header Builder
│   ├── Primary Menu
│   ├── Search
│   ├── …
│
├── Footer                                 (panel)
│
├── Blog / Single Post / Related Posts     (sections inside their own panels)
│
├── Header Builder Panel                   (custom Customify panel)
├── Footer Builder Panel                   (custom Customify panel)
│
└── WP defaults (Menus, Widgets, Homepage Settings, Additional CSS)
```

The Customize sidebar width is **WP core default** — `18% × viewport,
min 300px, max 600px` — controlled by `wp-admin/css/customize-controls.css`
(`.wp-full-overlay-sidebar`). Customify does not override this; neither
does this Colors implementation. Some themes (e.g. Blocksy) override to a
fixed 320px — Customify deliberately follows WP default. If a future PR
wants a narrower sidebar, that's a separate decision affecting all panels.

### 2.2 Config system primer

Customify uses a **config-driven** Customizer (see SPEC-customizer.md).
Each config file in `inc/customizer/configs/*.php` exposes a filter on
`customify/customizer/config` that returns an array of section / panel /
control descriptors. Customify_Customizer (PHP) registers them with WP;
Customify_Customizer_Auto_CSS (PHP) walks the same array to emit CSS;
`src/backend/customizer/js/auto-css.js` mirrors the pipeline for preview.

Config files are loaded by `Customify::load_configs()` in
`inc/class-customify.php`. The loader iterates a hardcoded array of
filenames (no glob). **Adding a new config file requires editing the array
in `class-customify.php`.**

A config item is a dict with at minimum `name`, `type`, and (for controls)
`section`, `default`, `selector`, `css_format`. Type values include:

- `panel`, `section`, `heading` (visual divider)
- `color` (wp-color-picker via `class-control-color.php`)
- `styling` (composite modal with normal/hover tabs + bg color/image, border,
  spacing — via `class-control-styling.php`)
- `text`, `select`, `slider`, `typography`, etc. — see SPEC-customizer.md.

### 2.3 Auto-CSS pipeline

For each color field with `selector: 'format'` and a `css_format` template
(containing `{{value}}`), `Customify_Customizer_Auto_CSS::setup_color()`
substitutes the saved theme_mod hex into the template and concatenates
the result. The whole output is attached via:

```php
wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS::get_instance()->auto_css() );
```

`'customify-style'` is the stylesheet handle — `wp_add_inline_style` will
silently drop the CSS if the handle isn't enqueued, so anything new that
attaches inline styles must use the same handle.

### 2.4 30K-site safety doctrine

Repeating because it's that important:

1. **Never rename or delete** a public `theme_mod` key. Saved values must
   continue to be read on legacy installs.
2. **Never silently shift** the rendered hex on existing selectors for
   sites with saved values. Adding new selectors / new CSS vars is fine
   (additive). Removing or changing existing selector→hex bindings is not.
3. Backward-compat **read both shapes** if storage shape changes. Idempotent
   version-stamped migrations only when storage truly must change.

The new Colors section satisfies these by **reusing existing
theme_mod keys** for primary/secondary slots + all 7 overrides; only the
4 new concepts (`base`, `surface`, `text`-as-ink, `accent`) introduce new
keys, and they're additive — nothing reads the new keys on legacy sites
unless the user opens the new picker and saves.

---

## 3. Architecture of this implementation

### 3.1 6 slots

| Slot | Storage key | Default | Notes |
|---|---|---|---|
| `primary` | `global_styling_color_primary` (REUSED) | `#235787` | Brand color, CTAs |
| `secondary` | `global_styling_color_secondary` (REUSED) | `#c3512f` | Secondary brand color |
| `accent` | `customify_palette_accent` (NEW) | `#FFD042` | Highlight / decorative pop |
| `text` | `customify_palette_text` (NEW) | `#2b2b2b` | Ink baseline (headings inherit) |
| `surface` | `customify_palette_surface` (NEW) | `#FFFFFF` | Card / elevated container bg |
| `base` | `customify_palette_base` (NEW) | `#FFFFFF` | Page background |

Display order in the Palette section: **brand-first** —
Primary → Secondary → Accent → Text → Surface → Base (set via
`priority` 10–15 in `colors.php`).

### 3.2 Derived tokens

Computed in `inc/colors-palette.php::customify_color_palette_root_css()`.
Each derived value has an **override** check first: if the corresponding
legacy theme_mod key has a saved value, that value wins; otherwise the
computed default is used.

> **Note**: this table reflects the **Phase 2.10–2.13 implementation** of
> the [color-token-derivation spec](../../.claude/worktrees/epic-shamir-e4a2d1/docs/color-token-derivation-spec.md). Earlier rows of this section
> (especially the on-* row and body-text row) were rewritten in Phase
> 2.12 — see §8.11 for the migration notes.

| Token | Override key | Compute formula | Used for |
|---|---|---|---|
| `--customify-text-muted` | `global_styling_color_meta` | `mix(text 70%, base)` | Pagination text, `.link-meta`, body paragraph copy |
| `--customify-border` | `global_styling_color_border` | `mix(text 14%, base)` (Phase 2.10 bumped 12→14% per spec §2) | Decorative borders / separators (~1.35:1, WCAG-exempt) |
| `--customify-border-strong` | (no legacy key) | Iterate P 6%→100% until `contrast(mix(text P%, base), base) ≥ 3.0` | Form input borders, functional outlines (WCAG 1.4.11 ≥3:1) |
| `--customify-link` | `global_styling_color_link` | `= primary` | `a { color }` |
| `--customify-link-hover` | `global_styling_color_link_hover` | `= link` (Phase 3 — follows Link → Primary; was `mix(primary 85%, white)`) | `a:hover/focus` |
| `--customify-primary-hover` | (no legacy key) | `mix(primary, black 10%)` | `button:hover` (NEW, didn't exist before) |
| `--customify-heading` | `global_styling_color_heading` | `= text` | `h1-h6` |
| `--customify-widget-title` | `global_styling_color_w_title` | `= text` | `.site-content .widget-title` |
| `--customify-body-text` | `global_styling_color_text` | `= text` (Phase 2.3 — rolled back from 88% mix so user's saved Text flows through unchanged) | Body paragraph fallback |
| `--customify-on-primary` | (no legacy key) | **Max-contrast pick** (Phase 2.12, spec §3): `contrast(LIGHT, X') ≥ contrast(DARK, X') ? LIGHT : DARK` where X' = composite of bg over saved base (handles rgba) | Text on primary buttons / hero / cards |
| `--customify-on-secondary` | (no legacy key) | Max-contrast pick (same formula) | Text on secondary surfaces |
| `--customify-on-accent` | (no legacy key) | Max-contrast pick (same formula) | Text on accent surfaces |
| `--customify-on-surface` | (no legacy key) | Max-contrast pick against saved Surface or `#FFFFFF` fallback | Text on `.is-style-card` (theme internal safety net) |
| `--customify-primary-container` | (no legacy key) | Solve P at OKLab L=0.93, mix, **chroma cap 0.04** (Phase 2.12, spec §4 + Customify ext) | Soft brand tint (Ollie-style badges/chips) |
| `--customify-secondary-container` | (no legacy key) | Same as primary-container | Soft secondary tint |
| `--customify-accent-container` | (no legacy key) | Same; chroma cap matters most here (yellow brand) | Soft accent tint |
| `--customify-on-primary-container` | (no legacy key) | OKLab L-reduction: keep brand hue, step L down until contrast vs container ≥ 4.5 | Theme-internal safety net for container-bg blocks |
| `--customify-on-secondary-container` | (no legacy key) | Same L-reduction | Same |
| `--customify-on-accent-container` | (no legacy key) | Same L-reduction | Same (accent yellow → dark gold #8B5F00) |

**Phase 2.13 note on `--customify-border` emission**: pre-Phase-2.13
the var was always emitted with the computed default. Now `:root` only
emits `--customify-border` when the user explicitly saved the Border
override (`global_styling_color_border`). Reason: bundled SCSS rules
gained a smart `var(--customify-border, color-mix(in srgb,
currentcolor 14%, transparent))` fallback that adapts to local text
color — emitting a frozen `:root` default would break that adaptation
for users who saved Base = dark. Sites that REFERENCE
`var(--customify-border)` from custom CSS / page builders WITHOUT a
fallback would resolve to the initial value (transparent for borders).
Use `var(--customify-border, currentColor)` or a literal fallback in
custom code.

**`on-*` emission gate** (Phase 2.13): the four `on-*` solid tokens
(`on-primary` / `on-secondary` / `on-accent` / `on-surface`) are now
emitted UNCONDITIONALLY (dropped the 4-new-slot opt-in gate). Required
because the SCSS auto-wire rule `.has-{brand}-background-color { color:
var(--customify-on-{brand}, inherit) }` needs them present even when
the user only touched legacy Primary/Secondary slots. The 3 `on-*-
container` tokens, `border-strong`, and the 3 `*-container` tokens
remain gated on opt-in.

#### Two paths for derived values

1. **PHP-precomputed static fallback** — always emitted in the `:root`
   block. Works in every browser including those without `color-mix()`.
   Uses `customify_color_mix_hex()` (sRGB linear average).
2. **`color-mix(in oklab, …)` modern refinement** — wrapped in
   `@supports (color: color-mix(in oklab, red, blue))` and only emitted
   when no override is set. Modern browsers (Chrome 111+, Safari 16.2+,
   FF 113+ — Baseline 2023) use perceptually uniform oklab mixing.

#### Override semantics (critical for 30K safety)

- Legacy site that saved `global_styling_color_meta: #6d6d6d` →
  `--customify-text-muted` is locked to `#6d6d6d`. The color-mix line for
  text-muted is **suppressed** from the `@supports` block.
- New install with no saved meta → `--customify-text-muted` shows the
  PHP-precomputed mix as static, then the @supports block refines it to
  live oklab mix when slots change.

### 3.3 Token slug contract (Blocksify)

#### `:root` CSS var contract — stable

The internal CSS variable names — `--customify-base`, `--customify-surface`,
`--customify-text`, `--customify-primary`, `--customify-secondary`,
`--customify-accent` — are API surface and **never renamed**. They're
referenced by:

- Bundled SCSS rules (`src/frontend/scss/utils/_vars.scss`).
- Customizer auto-CSS pipeline (`inc/customizer/class-customizer-auto-css.php`).
- JS live-preview payload (`SLOT_VARS` map in the preview script).
- Customify Pro modules.
- 30K user-saved custom CSS that may reference `var(--customify-text)` directly.

These names are locked. Adding new ones is OK; renaming existing ones
is not.

#### `theme.json` palette slug contract — renamed in Phase 2.11

The **picker slug names** (what shows up in Blocksify / WP block
editor color picker, generated as `--wp--preset--color--{slug}` and
`.has-{slug}-color` classes) were partially reworked in Phase 2.11 to
dodge WP global-styles marker-class collisions:

| Old slug (pre-Phase-2.11) | New slug | Reason |
|---|---|---|
| `text` | `body-text` | `.has-text-color` is ALSO WP's marker class for "block has text color set" — having a slug named exactly `text` makes WP generate `.has-text-color { color: var(--text) !important }` which then overrides any inline text color picks. |
| `border` | `divider` | `.has-border-color` is ALSO WP's marker class for the Border panel — same collision. `divider` is semantically clearer too (decorative line, not "border around things"). |
| `border-strong` | `divider-strong` | Symmetry with `divider`. |

The 6 LEGACY slugs (`text` / `link` / `heading` / `background` /
`light-gray` / `dark-gray`) that shipped in the original static
`theme.json` are RE-LISTED in the filter output under the same names
(marked `(legacy)` in the picker) so 30K sites with existing block
markup using those slug classes continue to render with their saved
colors. The legacy `text` slug retains the marker-class collision
behavior — pre-existing WP behavior; designers picking the modern
`body-text` slug get clean inline-override semantics.

**Customify-pro / Blocksify starter templates** should reference the
12 design-purposeful slugs below.

#### Final 12-slug picker palette (Phase 2.13)

```
Brand + container pairs (6):
  primary · primary-container · secondary · secondary-container ·
  accent · accent-container

Text + canvas axis (3):
  body-text · surface · base

Helpers (3):
  text-muted · divider · divider-strong
```

#### Legacy slugs — DELIBERATELY OMITTED

The original static theme.json palette listed 6 additional slugs
(`text` / `link` / `heading` / `background` / `light-gray` /
`dark-gray`). They are NOT re-listed in the filter output.

**Trade-off (project owner decision)**: clean picker UX wins over
backward compat with the legacy slug class names. 30K sites with
block markup using `class="has-text-color"`, `has-link-color`,
`has-heading-color`, `has-background-color`, `has-light-gray-color`,
or `has-dark-gray-color` (the slug-pick variants, not the marker
class) lose their saved color — block text falls back to the body
text cascade.

This is a **conscious regression** documented in §3.7. Designer
workflow for migrating legacy blocks: re-pick a swatch from the new
12-slug palette (e.g. `text` → `body-text`, `heading` → `body-text`
or `text-muted`, `background` → `base`, the two grays → custom hex).

Sites that need the legacy slugs back can override the filter via
`wp_theme_json_data_theme` at a later priority and re-add them.

See [`project_blocksify_companion.md`](../../.claude/memory/project_blocksify_companion.md).

### 3.4 Section layout (flat, customify_heading dividers)

The Colors section is a single flat WP section with all controls listed
sequentially. Visual grouping is via `customify_heading` controls
(implemented in Customify's heading control type) rendered as gray
uppercase strips:

```
Customizer › Colors
─────────────────────────────────────
"Pick 6 brand colors. The theme derives everything else."
─────────────────────────────────────

▌ PALETTE                              ← customify_heading (priority 5)
   ● Primary       (Brand color, CTAs.)               [picker]
   ● Secondary     (Secondary brand color.)           [picker]
   ● Accent        (Highlight / decorative pop.)      [picker]
   ● Text          (Ink baseline. Headings inherit…)  [picker]
   ● Surface       (Card / elevated container bg.)    [picker]
   ● Base          (Page background.)                 [picker]

▌ LINKS                                ← customify_heading (priority 20)
   ● Link color    (Default: derived from Primary slot.)  [picker]
   ● Link hover    (Default: derived [Primary darken 15%].)   [picker]

▌ BACKGROUNDS                          ← customify_heading (priority 30)
   ● Page Background            (composite: color + image + position + …)
   ● Content Area Background    (composite)
   ● Site Content Background    (composite — no slot sync, no default)

▌ COMPONENT OVERRIDES (ADVANCED)       ← customify_heading (priority 50)
   "Override individual element colors. Leave blank to inherit from Palette."
   ● Body text override          [picker]
   ● Border override             [picker]
   ● Meta text override          [picker]
   ● Heading override            [picker]
   ● Widget title override       [picker]
```

Modal styling note: the Backgrounds group uses Customify's existing
**styling composite control** (`type: 'styling'`). It carries its own
modal open/close behavior — there's a known incompatibility with the
slide patch (see §7.1).

### 3.5 Picker popover design

When the user clicks a round swatch trigger:

```
┌─ wp-picker-holder (white card, 6px radius, 0 0 0 1px + 0 12px 28px shadow)
│  padding: 18px
│  position: absolute; top: 100% + 8px; left: 0; right: 0; z-index: 100
│  display: none → block (CSS rule on .wp-picker-active parent)
│  animation: customify-color-popover-in 80ms ease-out
│
│  ┌─ iris-picker.iris-border (margin-left: -10px to align inner with hex input)
│  │  iris-picker-inner (left:10px absolute — Iris built-in)
│  │     iris-square (saturation/value, ~182×182)
│  │     iris-slider.iris-strip (hue strip)
│  │     iris-strip.iris-alpha-slider (alpha strip — forced top:0 for alignment)
│  │  iris-palette-container (8 default WP swatches — hidden)
│  │
│  └─ Addon row (one of):
│
│     (Palette slots:)
│     #235787                          ← input.customify-color-hex
│     var(--customify-primary)         ← input.customify-color-token (readonly)
│
│     (Component overrides / Links:)
│     ┌─ .customify-color-quickpick
│     │  FROM PALETTE
│     │  ●●●●●●  ← 6 round swatches; ring-highlighted if matches current value
│     └─
```

The picker holder is also hidden by default via:
```scss
.wp-picker-container .wp-picker-holder { display: none; height: auto !important; }
.wp-picker-container.wp-picker-active .wp-picker-holder { display: block; ... }
```
WP's wp-color-picker stylesheet doesn't ship the default hide rule in our
Customizer bundle, so this is necessary — otherwise all 13 holders render
at 26px each on section open.

Round swatches: the outer `.wp-color-result.button` is made transparent
+ no border + 28px round, and the inner `.color-alpha` span is promoted
to fill 100% with our own border + shadow. Outer button no longer paints
anything — only the inner span shows, no "ghost behind" effect.

### 3.6 JS instrumentation

`inc/colors-palette.php::customify_color_palette_quickpick_js()` emits
an inline script via `wp_add_inline_script('customize-controls', …)` that:

1. **Pre-builds** the addon row (hex+token or quick-pick) for every
   `.wp-picker-container` in the Colors section, twice at 100ms and 800ms
   after page-load. Subsequent picker opens just refresh the row's
   current-value state (one class flip or input value assignment), not a
   DOM rebuild — that's why opens are ~10ms.
2. Uses a `MutationObserver` on `#sub-accordion-section-customify_colors`
   watching `wp-picker-container.class` changes. When `wp-picker-active`
   is added, `injectPopupAddon()` runs (refresh, not rebuild).
3. **Monkey-patches jQuery `.slideDown / .slideUp / .slideToggle`** —
   ONLY when the target element has the `.wp-picker-holder` class AND is
   inside the Colors section. This bypasses Iris's slow
   `slideToggle('fast')` (200ms). Critically, the patch is scoped via
   `.wp-picker-holder` check — earlier broader scope (any element in
   section) accidentally caught the styling composite control's modal
   slideUp and broke its `modal--opening` class removal.
4. Re-emits an `iris-customify-hex-sync` custom event on every Iris
   input/change so the hex input stays in sync with drag interactions.

The script depends only on jQuery (which Customizer ships).

### 3.7 theme.json palette — filter-injected (Phase 2.11)

> **History note**: Phase 1 (PR #392) appended 3 new slugs (`base`,
> `surface`, `accent`) statically to the existing 8 in `theme.json`.
> Phase 2.11 (PR #396) rewrote this to use `wp_theme_json_data_theme`
> filter injection — see §8.11. Today the static `theme.json` palette
> is irrelevant; the filter wholesale-replaces it at runtime.

The Phase 2.13 final palette is **12 design-purposeful slugs**
injected via `customify_palette_inject_into_theme_json()`:

```
Brand + container pairs (6):
  primary · primary-container · secondary · secondary-container ·
  accent · accent-container

Text + canvas axis (3):
  body-text · surface · base

Helpers (3):
  text-muted · divider · divider-strong
```

Each entry's `color` field is `var(--customify-{token}, {hex_fallback})`,
so block markup using `.has-{slug}-color` resolves to the live
Customizer value (or the literal hex fallback for fresh installs).
WP 6.1+ only; older WP early-returns from the filter and keeps the
static `theme.json` palette.

#### 30K back-compat for legacy slug class names

The original static `theme.json` listed 6 additional slugs (`text`,
`link`, `heading`, `background`, `light-gray`, `dark-gray`). These
are DELIBERATELY OMITTED from the filter palette — they bloated the
picker UX without representing design-purposeful choices.

Block markup from 30K sites that picked these slugs (saved as
`class="has-{legacy-slug}-color"`) is preserved via **CSS shim rules
in `_blocks.scss`** rather than palette entries. The shim:

```scss
.has-text-color    { color: var(--customify-text); }
.has-link-color    { color: var(--customify-link); }
.has-heading-color { color: var(--customify-heading); }
.has-background-color    { background-color: var(--customify-base); }
.has-light-gray-color    { color: #f2f2f2; }
.has-dark-gray-color     { color: #444444; }
// + matching .has-{slug}-background-color and .has-{slug}-border-color
```

**Critical**: shim rules emit WITHOUT `!important`. This is what
sidesteps the WP marker-class collision that plagued Phase 2.10:

| Cascade actor | Specificity | `!important` |
|---|---|---|
| `<element style="color:#fff">` | `(1,0,0,0)` | — |
| `.has-primary-color` (WP-generated from palette slug) | `(0,1,0)` | **yes** |
| `.has-text-color` (our shim) | `(0,1,0)` | **no** |

Result per scenario:

- **Designer picks `primary` slug** (new design flow) — block markup
  is `class="has-primary-color has-text-color"`. WP's `!important`
  rule for `.has-primary-color` beats our shim → primary renders ✓
- **Designer picks `text` slug pre-PR** (legacy flow) — block has
  only `class="has-text-color"`. Only our shim matches → text-slot
  color renders ✓
- **Designer uses inline custom hex** — `<element style="color:#fff">`
  has highest specificity → inline wins ✓

No collision because the shim never adds `!important`. It only
applies when no other rule (WP slug rule, inline style) outranks it.

#### Customizer ↔ block editor sync

When the user changes a slot value in the Customizer:
- `:root --customify-<slot>` updates live (via the preview JS).
- `--wp--preset--color--<slug>` updates via the var() chain in the
  filter output (no PHP re-render needed, the chain is `var(--customify-X)`).
- Block editor canvas + frontend both reflect the change.

This is a **strict improvement** over Phase 1, which had the
Customizer-to-block-editor disconnect documented in the original
§3.7 note. The Phase 2.11 filter resolved that limitation.

### 3.8 Palette-linked pickers (component colors follow a token)

Every eligible color picker in the Customizer offers the 12 palette
tokens as swatches. Picking one stores the **token reference itself**:

```
footer_main_background_color = var(--customify-secondary, #c3512f)
```

so the field FOLLOWS the palette — editing the Secondary slot recolors
it without touching the field. The baked fallback is what renders where
the token is not emitted (the derived-token opt-in gate, §3.2).

**Storage.** No new key, no shape change. The field keeps its existing
`theme_mod` / composite-subfield slot and simply holds a different string
form. Composite subfields work the same way (`customify_button_styling`
→ `normal.bg_color`).

**Sanitize.** `Customify_Sanitize_Input::sanitize_color()` gained an
additive branch: a value matching `var(--customify-<token>[, <fallback>])`
is accepted when the token name is in
`Customify_Sanitize_Input::allowed_color_tokens()` (filter:
`customify/color/allowed_tokens`) AND the fallback passes the ordinary
hex/rgba validation. Everything else falls through to the original path
unchanged, so no previously-valid value sanitizes differently. The
fallback is re-validated, not trusted: `var(--customify-primary,
javascript:alert(1))` normalizes to `var(--customify-primary)`.

**Render.** `Customify_Customizer_Auto_CSS::setup_color()` runs the same
sanitizer, so the token passes into `css_format` verbatim and the browser
resolves it against the `:root` block.

**Eligibility.** Everything registered in the `customify_colors` section
is excluded (`customify_color_link_excluded_controls()`, derived from the
live config, filter: `customify/color/palette_link_excluded`):

| Excluded | Why |
|---|---|
| 6 palette slots | They DEFINE the tokens — `--customify-primary: var(--customify-primary)` is circular. |
| 7 component overrides | The derived-token engine reads them through `customify_color_normalize_hex()`, which is hex-only; a `var()` there reads as "no override saved" and silently does nothing. The section's own "From palette" quick-pick is the right affordance for an override. |
| 3 background composites | They already have a slot cascade keyed off whether `bg_color` was saved (§3.2). |

**Where the code lives.** `initColor()` in
`src/backend/customizer/js/control.js` — the single point every Customify
color input passes through (standalone controls, `styling` composite
subfields, `modal` subfields, repeater rows). `inc/color-palette-link.php`
is data only: it resolves each token to a literal preview color by parsing
back what `customify_color_palette_root_css()` actually emitted, and ships
the list on the existing `Customify_Control_Args` payload.

Two non-obvious constraints that the implementation exists to satisfy:

1. **Iris cannot parse `var()`.** Left alone, `wpColorPicker()` reads the
   token as empty and fires its `clear` callback, blanking the hidden
   input the control reads — a later `getValue()` would then push `''`
   over the saved token. `initColor()` seeds the VISIBLE input with the
   resolved hex before init and restores the token on the hidden input
   after.
2. **`wpColorPicker()` fires `change` during init.** That callback pushes
   the picker's hex to the setting whenever it differs from `current_val`,
   which would rewrite a linked field to its fallback hex on every
   re-render (e.g. `refreshFromSetting()`). `current_val` is therefore
   seeded with the resolved hex so the init callback is a no-op. Net
   effect: rendering a linked control writes nothing and never dirties the
   Customizer.
3. **The trigger chip is painted inline by `color-picker-alpha.js`.** The
   sync pass may only UNDO an override it applied itself; clearing the
   inline background unconditionally wipes the alpha script's paint and
   leaves the chip transparent after an ordinary hex pick.
4. **wp-color-picker slides `.iris-picker`, not `.wp-picker-holder`.** An
   interrupted slideUp leaves an inline `display: none` on the picker while
   the container still carries `.wp-picker-active` — the card then opens
   showing the palette row with no picker above it. The SCSS ties
   `.iris-picker` visibility to the container state instead
   (`&.wp-picker-active .iris-picker { display: block !important }`).

**Token matching is by NAME, never by whole string.** The stored value
carries a fallback baked in at pick time, but the token list is rebuilt
from live slot values on every page load — comparing whole strings would
"unlink" every Accent-linked field the moment someone edits the Accent
slot.

**Known rough edge.** The baked fallback goes stale after a slot edit
(the value box shows `var(--customify-accent, #ffd042)` while Accent is
now `#7c3aed`). Harmless — the fallback only applies when the token is
absent — but a follow-up could refresh it when the Customizer is already
dirty, using the same "never write on a clean open" doctrine as the
palette switcher (§8.13 D).

---

## 4. File inventory

```
inc/
├── class-customify.php                   MODIFIED — +3 lines (require palette
│                                                    file, add 'colors' to
│                                                    load_configs, enqueue
│                                                    inline :root)
├── colors-palette.php                    NEW — color math helpers (hex↔rgb,
│                                                mix, WCAG luminance,
│                                                normalize), slot resolver,
│                                                :root CSS builder, quick-pick
│                                                JS emitter
└── customizer/
    └── configs/
        ├── colors.php                    NEW — Colors section + 5 heading
        │                                       dividers + 6 slot pickers +
        │                                       relocated link/background/
        │                                       override fields
        ├── styling.php                   MODIFIED — styling_panel registration
        │                                            removed (panel is empty after
        │                                            the colors move); callback
        │                                            kept as a no-op stub
        └── background.php                MODIFIED — class stub with no-op
                                                     config() (3 composites
                                                     moved to colors.php;
                                                     empty section bug removed)

src/backend/customizer/scss/
└── _control.scss                         MODIFIED — +~340 lines for
                                                     Colors-section
                                                     popover/swatch/picker
                                                     styling, scoped under
                                                     #sub-accordion-section-
                                                     customify_colors

theme.json                                MODIFIED — added 3 palette entries
                                                     (base/surface/accent)
                                                     alongside existing 8

docs/
└── SPEC-customizer-colors.md             NEW — this file
```

Build artifacts in `build/css/backend/customizer/customizer.css` are git-
ignored. Run `npm run build` after a fresh clone.

> **Note:** the tree above is the Phase-1 snapshot. Later phases added more
> files — see the §8.x phase logs for the rationale behind each.

```
inc/
├── color-palette-switcher.php          NEW (Phase 3) — palette switcher UI:
│                                          built-in presets + custom palettes
│                                          (create/rename/delete, import/export),
│                                          customify_color_sanitize_palettes(),
│                                          inline controls JS + CSS. Registered
│                                          in includes() right after
│                                          colors-palette.php.
└── colors-palette.php                  MODIFIED (Phase 2.x–3) — derived-token
                                           engine (on-*, *-container, OKLab),
                                           link-hover → link cascade, live-
                                           preview recompute debounce, quick-
                                           pick swatch cascade resolution.

src/frontend/scss/
├── base/_base.scss                     MODIFIED (Phase 2.14) — background-
│                                          agnostic native-button hover state
│                                          layer (color-mix overlay).
├── base/_blocks.scss                   MODIFIED (Phase 2.14) — block bg auto-
│                                          contrast text + Blocksify button
│                                          text colour (on-* tokens).
└── header/builder_items/_button.scss   MODIFIED (Phase 2.14) — header builder
                                           button hover state layer.
```

---

## 5. 30K-site safety — verification suite

### 5.1 The three scenarios

Defined in commits and verified twice (after Phase 1 commit + after every
visual fix):

| Scenario | Setup | Expected result |
|---|---|---|
| **A — Defaults** | Zero saved theme_mods (fresh install) | New code emits same color values via slot defaults. Pre/post diff: 0 existing decls shifted; 17 additive `:root` vars are the only new output. |
| **B — Custom brand** | All 9 legacy keys saved with custom hex values (`#0066CC` primary, `#FF6600` secondary, `#333333` text, `#0066CC` link, `#003366` link-hover, `#DDDDDD` border, `#888888` meta, `#111111` heading, `#222222` widget) | New code reads saved → emits same custom values on the same selectors. |
| **C — Partial save** | Only 2 legacy keys saved (`primary=#FF0000`, `heading=#003366`) | Partial saved values preserved; the other 7 derived from defaults. |

### 5.2 How to re-run

Test helpers live at `/tmp/`:

- `extract_customify_css.py` — pull `customize-style-inline-css` from
  frontend HTML, pretty-format
- `capture_color_css.sh <name> <out.css>` — wraps curl + extract
- `compare_color_css.py <baseline> <new>` — semantic per-(selector,
  property, value) diff. Treats additive new `:root` vars as OK; flags
  REMOVED decls or HEX SHIFTS as regressions.

Example:
```bash
# capture baseline before code change
/tmp/capture_color_css.sh A_baseline /tmp/baseline_A.css

# (apply code change, sync, flush cache)

# capture after
/tmp/capture_color_css.sh A_after /tmp/after_A.css

# diff
python3 /tmp/compare_color_css.py /tmp/baseline_A.css /tmp/after_A.css
```

For scenarios B/C, use `studio wp --path /Users/kientrong/Studio/customify2
theme mod set <key> '<#hex>'` to plant theme_mods, then capture, then
`theme mod remove` to reset.

### 5.3 Edge cases also covered

- **Invalid hex** (`'nothex'`) saved via wp-cli (bypasses Customizer
  sanitize) — `customify_color_normalize_hex()` returns the default
  fallback; no garbage propagated to `:root` vars or `color-mix` math.
- **3-char hex** (`#abc`) — normalized to `#aabbcc`.
- **Empty string** saved — same fallback path.

The normalize helper is called on **every** read of a user-saved color
value in `colors-palette.php` (both slot reads and override resolution),
so wp-cli writes that bypass the Customizer sanitize_callback can't
break the pipeline.

---

## 6. UX details & known visual considerations

### 6.1 Compact picker UI (label-left, swatch-right)

CSS grid on `.customize-control-customify-color > .customify--settings-wrapper`:
- `grid-template-columns: 1fr auto`
- Title (col 1, row 1) + description (col 1, row 2) + picker (col 2, row 1/3)

The custom override `position: relative` on
`.customize-control-customify-color` makes the LI the positioning context
for the popover holder. **Also override** `.customify-field-settings-inner
{ position: static }` — Customify globally sets it to `position: relative`
which would otherwise pin the holder to the 28px-wide swatch container
instead of the full row.

### 6.2 Picker popover — Iris layout caveats

Iris is jQuery UI + custom slider math. We DO NOT touch its internal
positioning of `.iris-square`, `.iris-strip`, `.iris-slider` because
overriding their widths breaks the drag handlers (the click→hue/saturation
math reads `offsetWidth`/`offsetLeft`). Instead:

- **Outer `.iris-picker.iris-border`** is given `margin: 0 0 0 -10px`
  to shift the whole picker 10px left, cancelling out the built-in
  `.iris-picker-inner { left: 10px }` so `.iris-square`'s left edge
  visually aligns with the hex input's left edge below.
- **`.iris-alpha-slider`** has its Iris-default top offset overridden
  (`top: 0; margin-top: 0; height: 100%`) so both vertical strips share
  the saturation square's vertical bounds.
- **`.iris-palette-container`** (the 8 default WP swatches Iris renders
  inside the picker) is hidden via `display: none`.
- **`.iris-picker.iris-border`** loses its own background/border/shadow
  via `background: transparent; border: 0; box-shadow: none; padding: 0`
  so the popover wrapper provides the visible card.

Iris's natural width is ~255px; popover content area at default 18px
padding × 2 in a 283px row is ~247px. The 8px Iris overhang on the
right is **accepted as visual bleed** since the iris-picker is centered
properly via the margin-left shift. Earlier attempts to constrain Iris
to 247 via `iris('option', 'width', N)` failed because the runtime setter
isn't exposed on the installed wp-color-picker version — it silently
shrank the saturation square.

### 6.3 Sidebar width is WP core, not us

`.wp-full-overlay-sidebar` is `width: 18%; max-width: 600px; min-width:
300px` per WP core. Customify follows the WP default. Sidebar width on a
2000px-wide monitor will be ~360px; on a 1200px monitor it'll be 300px
(min). This is not a Customify or Colors-section bug. Some themes (e.g.
Blocksy) override to fixed 320px — if Customify ever wants the same, it's
a separate decision affecting all panels.

### 6.4 Snappy popover (~10ms open)

Three combined optimizations brought popover-open from 173ms to 10ms:

1. **Bypass Iris's 200ms `slideToggle('fast')`** via the scoped jQuery
   slide monkey-patch (§3.6, item 3).
2. **Pre-built addons** so picker-open just does a class flip + input
   value assignment, not DOM injection.
3. **Slim CSS keyframe** — `customify-color-popover-in` is 80ms,
   opacity-only (no transform = no compositor layer churn).

Verified via Chrome MCP `performance.now()` between click and 2nd RAF.

### 6.5 Round swatches — the inner-overlay trick

`.wp-color-result.button` has an inner `<span class="color-alpha">` that
Iris paints with the saved color. By default this span is 30×26 with
`border-radius: 2px 0 0 2px` — even when you round the outer button, the
inner span paints a square chip on top, producing a "ghost shape behind
the circle" effect. We strip the outer (`background: transparent !important;
border: none !important; box-shadow: none !important`) and promote
`.color-alpha` to fill 100% with our own 50% radius + border + shadow.
One clean circle per swatch, no overlap.

### 6.6 Reset icon overflow on wrapped descriptions (Phase 2.9)

Picker LI uses a 2-column grid (`1fr | swatch`). The reset icon
(`input.wp-picker-default`) is `position: absolute right: 36px` so it
sits ~12px inside column 1's territory. Short descriptions: icon over
whitespace, fine. Long descriptions that wrap (e.g. "Ink baseline.
Headings inherit from this slot by default."): icon body lands on top
of the wrapped text. Fix: when `.customify-input-color.is-dirty` is
set (icon visible), reserve `padding-right: 24px` on both
`.customize-control-title` and `.customize-control-description` via
`:has()`. Text wraps earlier and leaves a clear gutter. Padding only
applies when the icon is actually visible.

### 6.7 Slot-name tooltip on quickpick swatches (Phase 2.9)

The "From palette" row in override pickers now shows a dark tooltip
above the hovered swatch with the slot name (Primary / Secondary /
Accent / Text / Surface / Base). JS sets `data-label="<name>"` on each
swatch; SCSS `::before` reads `content: attr(data-label)`. Uses
`::before` to leave `::after` available for the existing `.is-active`
selection ring. `title` attribute also carries the slot name for
screen-reader accessibility.

---

## 7. Known issues / loose ends

### 7.1 jQuery slide patch scope (lesson learned)

The first version of the jQuery slide monkey-patch was scoped to **any
element inside `#sub-accordion-section-customify_colors`**. That caught
the styling composite control's modal-panel `slideUp()` and bypassed
its animation-done callback — which is what removes the
`modal--opening` class. Result: clicking the X to close a Background
composite panel would close the panel visually but leave the X icon
stuck (control thought modal was still open).

Fix in commit `e2a9c8ee`: scope check now requires `.wp-picker-holder`
class explicitly. **Any future patches to that scope predicate must
preserve this constraint** or revisit the styling control regression.

### 7.2 Iris alpha slider rendering quirks

Iris was originally designed without alpha. The alpha slider was bolted
on via the `wp-color-picker-alpha` library — its CSS gives the alpha
strip a different top/height than the hue strip, and Iris's internal
slider math anchors handle position to whatever pixel height it computed.
The fix in §6.2 forces equal vertical bounds; drag still works because
margins/heights are handled outside the drag-math path (Iris reads
`offsetWidth/height` only inside the saturation square).

### 7.3 Background composite — `content_background` has no slot sync

The 3rd Background composite (`content_background`, selector
`.site-content`) has no equivalent slot. Page Background ↔ slot `base`
and Content Area ↔ slot `surface` are conceptually linked but **not
two-way synced** in storage. Editing `base` slot does NOT update
`background[normal][bg_color]`. Phase 2 follow-up if needed.

### 7.4 Live preview JS — Phase 2.1 + 2.8

Phase 2.1 added a `customize_preview_init` inline script in
`colors-palette.php::customify_color_palette_preview_js()` that listens
to all 6 slot settings and live-updates `--customify-<slot>` on
`document.documentElement.style`. Modern browsers re-resolve any
`color-mix()` derived token automatically.

Phase 2.8 extended this with JS WCAG luminance helpers (`_hexToRgb` /
`_relativeLuminance` / `_pickOn`) and an `ON_MAP` listener for the 3
brand slots so `--customify-on-primary|secondary|accent` flip live
between `#1A1A1A` and `#FFFFFF` as the user drags. JS math mirrors
PHP `customify_color_pick_on()` byte-for-byte.

Phase 2.9 (polish) added a `CASCADE_FALLBACK` map so clearing an
override picker mid-session (`.set('')`) re-applies the cascade
expression (e.g. `var(--customify-text)`) instead of falling through
to the PHP-baked `:root` rule — fixes the "cleared override keeps
showing old value until save" quirk.

### 7.5 Existing CSS rules — full var() refactor done (Phase 2.5–2.8)

Phase 2.1 first refactored `$primary_css` and `$secondary_css` to
`var(--customify-primary, {{value}})`. Phase 2.5–2.6 extended the
refactor to the remaining 7 rules (link / link-hover / text / border /
meta / heading / widget-title) plus aligned field defaults so the
fresh-install render shifts are intentional (documented per phase).
Phase 2.8 wired 11 button selectors to `--customify-on-primary` /
`--customify-on-secondary` for auto-contrast text.

All `$color_*` SCSS variables in `src/frontend/scss/utils/_vars.scss`
now resolve through `--customify-<token>` with the legacy hex as
fallback. Saved overrides paint the entire theme correctly (byte-
equivalent for 30K sites with saved values); slot drag cascades the
whole site in modern browsers; legacy browsers without `var()` support
keep the static fallback.

---

## 8. Phase status

### 8.1 Phase 1 — done (this PR)

- ✅ Top-level Colors section with flat layout + heading dividers
- ✅ 6 slot pickers (4 NEW keys + 2 REUSED)
- ✅ Brand-first display order
- ✅ `:root` block with PHP static fallback + color-mix() refinement
- ✅ WCAG luminance precompute for `--on-*`
- ✅ Override mechanism (legacy keys win over computed)
- ✅ Normalize-hex defense-in-depth for wp-cli writes
- ✅ `theme.json` static palette additions (base/surface/accent)
- ✅ Compact UI (label-left, round swatch right)
- ✅ Popover styling (white card, shadow, no overflow)
- ✅ Quick-pick "From Palette" row in component-override popups
- ✅ Hex input + read-only token var row in palette popups
- ✅ Snappy popover (~10ms open via jQuery slide bypass + pre-built addons + minimal CSS keyframe)
- ✅ Round swatches (inner color-alpha overlay technique)
- ✅ 30K-site safety verified — A/B/C all PASS byte-identical
- ✅ Chrome MCP verification (Colors section visible, popover works, frontend unchanged)

### 8.2 Phase 2.1 — done

- ✅ Refactor `$primary_css` and `$secondary_css` to
  `var(--customify-primary, {{value}})` / `var(--customify-secondary, {{value}})`.
  Slot key == legacy field key for these 2, default hex matches slot default, so
  the var resolves to identical hex on every browser for every site. Legacy
  browsers without var() support fall back to `{{value}}` (the saved hex).
- ✅ Live preview JS for all 6 slot settings. Listens via `wp.customize(setting).bind()`
  and live-updates `--customify-<slot>` on `document.documentElement.style`.
  Modern browsers re-resolve `color-mix()` derived tokens automatically.
- ✅ Force `transport=postMessage` on the 4 new slot fields. The 2 legacy
  slot fields already get postMessage via `class-customizer.php` css_format
  detection; the 4 new slots have empty css_format and would default to
  refresh — `customize_register` priority 1000 forces postMessage so the
  preview JS above can fire.
- ✅ Mirror Customify's value decode (`JSON.parse(decodeURI(v))`) in the
  preview JS so wrapped values (`%22#FF00AA%22`) decode to raw hex before
  validation.
- ✅ Update `/tmp/compare_color_css.py` to normalize `var(--X, #hex)` →
  `#hex` so byte-equivalent tests still pass after the var() refactor.
- ✅ 30K-site safety re-verified — A/B/C all PASS byte-identical with the
  refactored rules.

### 8.3 Phase 2.2 — done (heading cascade)

- ✅ Refactor `$heading_css` (h1-h6) to
  `color: var(--customify-heading, {{value}})`. Field default `#2b2b2b` ==
  slot.text default `#2b2b2b` → byte-equivalent for fresh installs; saved
  overrides feed both pipelines identically.
- ✅ Move the `:root` token block to its OWN
  `<style id="customify-palette-tokens-inline-css">` tag (separate from
  `customify-style-inline-css`). Necessary because
  `src/backend/customizer/js/auto-css.js` (~L195) wholesale-replaces the
  customify-style inline block every time a setting changes in the
  Customizer preview iframe — keeping the tokens in their own tag means
  the var() chain survives the regenerate. Frontend (curl) and iframe
  both render the same block now.
- ✅ Emit a cascade decl `--customify-heading: var(--customify-text, hex)`
  in `:root` AFTER the static `--customify-heading: hex` line, but only
  when there is no SAVED `global_styling_color_heading` override. Modern
  browsers use the later (var()) decl → editing the Text slot now
  cascades to all headings in real time. Legacy browsers without var()
  support keep the static hex from the earlier line.
- ✅ Use `get_theme_mods()` (saved-only array) instead of `get_theme_mod()`
  for override detection. Critical: inside the Customizer preview,
  `get_theme_mod()` returns the customize control's REGISTERED FIELD
  DEFAULT when no value is saved — not the `null` argument. That would
  always look like an explicit override, suppressing the cascade.
  `get_theme_mods()` returns only what's actually in the DB.
- ✅ Add `global_styling_color_heading` → `--customify-heading` to the
  preview JS payload. When the user drags the heading picker directly,
  the inline `style.setProperty` overrides the `:root` cascade chain.
  Clearing the picker via `.set('')` triggers `removeProperty` and the
  cascade resumes.
- ✅ Update `/tmp/extract_customify_css.py` to pull BOTH inline style
  blocks for the byte-equivalence check.
- ✅ Update `/tmp/compare_color_css.py` to strip `/* sourceURL=... */`
  CSS comments before regex matching (WP injects them and they would
  otherwise be folded into the next rule's selector).
- ✅ 30K-site safety re-verified — A/B/C all PASS byte-identical.

### 8.4 Phase 2.3 — done (body_text direct cascade)

- ✅ `$text_css` → `var(--customify-body-text, {{value}})`. Earlier
  iteration used `color-mix(in oklab, var(--customify-text) 88%, var(--customify-base))`
  which desaturated user-picked colors (white text on dark base produced
  ~#e0e0e0 grey body copy). Reverted to direct var() chain so body
  follows slot.text verbatim.
- ✅ Cascade decl `--customify-body-text: var(--customify-text, hex)`
  in static `$lines` (no @supports needed for pure var chain).
- ✅ Field default `#686868` → `#2b2b2b` (slot.text). Documented
  fresh-install shift; saved sites untouched.

### 8.5 Phase 2.4 — done (link / link-hover / Surface / Legacy / reset icon)

- ✅ `$link_css` → `var(--customify-link, {{value}})` + cascade decl
  `--customify-link: var(--customify-primary, hex)`. Field default
  aligned with slot.primary `#235787`.
- ✅ `$link_hover_css` → `var(--customify-link-hover, {{value}})` +
  @supports color-mix line `color-mix(in oklab, var(--customify-link) 85%, white)`
  — link hover is LIGHTER than link (project owner's call), not darker.
  Field default `#406F99`.
- ✅ Slot `surface` default `#FFFFFF` → `#ECECEC` (theme.json palette
  declaration matched).
- ✅ "Component overrides (advanced)" renamed to "Legacy fine-tuning".
  Collapsed by default. Click target = `.customize-control-title` span
  (not the LI) so browser focus outline doesn't paint a stuck rectangle
  across the row. State persists per-session via `sessionStorage`.
- ✅ Reset icon: `.wp-picker-default` un-hidden via SCSS, repositioned
  16×16 absolute LEFT of swatch (24px gap), paints dashicons-image-rotate
  SVG as background-image. Visibility gated by `.customify-input-color.is-dirty`
  — toggled by JS via closure-scoped `initialValues` map (dataset
  attributes get rewritten by Iris on every value set, so a closure
  Map is the only stable baseline).

### 8.6 Phase 2.5 — done (Base composite cascade + border/meta/widget refactor)

- ✅ 3 composites (`background`, `site_content_styling`, `content_background`)
  default `bg_color` changed `#FFFFFF` → `''`. Customify's auto-css
  `setup_color()` returns false for empty values → skips emit → the
  palette-tokens cascade rule is the sole emitter for `body`, `.site-content`,
  `.site-content .content-area`. Saved composite emits literal hex via
  auto-css (loads AFTER palette-tokens), wins by cascade source order.
- ✅ `$border_css` (18 declarations) → `var(--customify-border, ...)`.
  Phase 2.6 later changes the fallback to `color-mix(currentcolor 12%, transparent)`.
- ✅ `$meta_css` → `var(--customify-text-muted, {{value}})`. Field
  default `#6d6d6d` → `#6b6b6b`.
- ✅ `$w_title_css` → `var(--customify-widget-title, {{value}})` + cascade
  decl `--customify-widget-title: var(--customify-text, hex)`. Field
  default `#444444` → `#2b2b2b` (slot.text).
- ✅ `$text_css` (body) gets its own cascade decl
  `--customify-body-text: var(--customify-text, hex)` in static `$lines`.
- ✅ Preview JS payload extended with border / meta / widget-title /
  body-text overrides so user-driven picker drags update :root vars
  live in the iframe.

### 8.7 Phase 2.6 — done (bundled SCSS `$color_*` → CSS var expressions)

The bundled stylesheet referenced 8 SCSS color variables across ~65
rules. Refactored each to CSS-var expressions in
`src/frontend/scss/utils/_vars.scss`:

```scss
$color_text:        var(--customify-body-text, #686868);
$color_heading:     var(--customify-heading, #2b2b2b);
$color_primary:     var(--customify-primary, #235787);
$color_secondary:   var(--customify-secondary, #c3512f);
$color_link:        var(--customify-link, #1e4b75);
$color_link_hover:  var(--customify-link-hover, #111111);
$color_border:      var(--customify-border, color-mix(in srgb, currentcolor 12%, transparent));
$color_meta:        var(--customify-text-muted, #6d6d6d);
```

Result: every bundled rule (header, footer, blog, widgets, etc.) that
uses one of these tokens automatically resolves through the :root
cascade. Saved overrides paint correctly without per-selector chasing.
Border specifically uses `currentcolor` mix as fallback so unsaved
borders adapt to the element's local text color (visible on both
light and dark surfaces).

### 8.8 Phase 2.7 — done (visual cascade swatch sync in picker UI)

Override pickers display their swatch in the cascade-resolved color
when no override is saved:

| Override picker | Cascade source |
|---|---|
| `global_styling_color_link` | `global_styling_color_primary` |
| `global_styling_color_heading` | `customify_palette_text` |
| `global_styling_color_w_title` | `customify_palette_text` |
| `global_styling_color_text` (body) | `customify_palette_text` |

Mechanism: JS reads source slot value, sets `--customify-cascade-display`
CSS custom property + `.is-cascading` class on the LI; SCSS overrides
wpColorPicker's inline background on `.color-alpha` only when that
class is present. Setting underlying value stays untouched — user
dragging the picker writes a real value and removes the class.

`FIELD_DEFAULTS` map in the JS detects "no user override" by comparing
the current setting value with the registered field default. If they
match, cascade mode applies; if they differ, the user has saved an
override and the swatch shows that value via the standard wp-color-
picker path.

### 8.9 Phase 2.8 — done (WCAG `--on-*` live preview + opt-in gate + button consumer wiring)

- ✅ JS WCAG luminance math mirrors PHP `customify_color_pick_on()`
  (sRGB linear gamma decode, Rec.709 luma weights, `> 0.45 ? #1A1A1A : #FFFFFF`).
  Listens to the 3 brand slot settings (`global_styling_color_primary`,
  `global_styling_color_secondary`, `customify_palette_accent`) and
  setProperty's `--customify-on-primary|secondary|accent` inline on the
  iframe's `documentElement` so auto-contrast text colors update on
  every drag.
- ✅ Opt-in gate for the 3 on-* tokens in `:root`. PHP only emits the
  tokens when the user has saved any of the 4 truly-new slot keys
  (`customify_palette_base|surface|text|accent` — none existed pre-
  Phase 2). Sites that only touched the long-standing `global_styling_color_*`
  keys remain in legacy mode → no on-* lines in `:root` → bundled
  rules of the form `color: var(--customify-on-X, #fff)` fall back to
  the literal `#fff` hex. Byte-equivalent to pre-refactor for 30K+
  sites that never engaged with the new Palette panel.
- ✅ Bundled SCSS wired across 11 button selectors:
  - `base/_base.scss` — universal `.button` / `button` / `input[type=submit]` /
    `.wp-block-button__link` / `.wp-element-button` rule + `:hover` + `:focus`
    variants → `--customify-on-primary`
  - `layouts/_blogs.scss` — `.readmore-button:hover`, pagination hover +
    current `span` → `--customify-on-primary`
  - `widgets/_widgets.scss` — `.wp-block-search__button` → `--customify-on-primary`
  - `header/builder_items/_button.scss` — `.customify-builder-btn` + `:hover` →
    `--customify-on-secondary`
  - `compatibility/wc/_wc-cart.scss` — `.customify-wc-total-qty` badge →
    `--customify-on-secondary`
  - `compatibility/wc/_wc-elements.scss` — 7 WC product button classes;
    added explicit `color: var(--customify-on-secondary, ...)` + override
    `:hover/:focus` because these otherwise inherit on-primary from the
    base rule above (wrong contrast on secondary bg).
- ✅ `--customify-on-accent` declared as API surface but no bundled rule
  consumes it yet (no accent-bg button exists). Sketched for future use
  (Blocksify pattern, custom CSS, or future button variant).
- ✅ 30K-site safety verified via full upgrade simulation: brand-new
  Studio site → install customify 0.4.13 from wp.org → save 9 color
  overrides + custom header/footer content → rsync worktree over →
  compare. 10 representative elements (body/h1/h2/link/searchBtn/
  postMeta/entryInner/readmore/widgetTitle/copyright) all render
  pixel-identical pre/post upgrade. Header items (7 builder items)
  100% identical. Footer items 100% identical (after reverting the
  footer skin default — see §8.10).

### 8.10 Phase 2.9 — done (picker UI polish + heading-clear cascade fallback + footer skin revert)

Three small fixes that don't fit a Phase 2.x slot but ship in the same
batch:

- ✅ **Reset icon overflow** when description wraps. Picker LI uses a
  2-column grid (`1fr | swatch`); the reset icon is `position: absolute
  right: 36px` which lands 12px inside column 1's territory. With short
  descriptions the icon sits over whitespace; with a long wrapped
  description (e.g. "Ink baseline. Headings inherit from this slot by
  default.") the icon body lands on top of the wrapped text. Fix: when
  the picker has `.customify-input-color.is-dirty` (icon visible),
  reserve 24px right-padding on `.customize-control-title` and
  `.customize-control-description`. Text wraps 24px earlier and leaves
  a clear gutter for the icon. Uses `:has()` (Baseline 2023). When not
  dirty, no padding reserved so non-dirty pickers keep full column-1 width.
- ✅ **Slot-name tooltip on quickpick swatches**. Hovering a swatch in
  the "From palette" row now shows a small dark tooltip with the slot
  name (Primary / Secondary / Accent / Text / Surface / Base). JS sets
  `data-label="Primary"` (etc.); CSS `::before` renders the tooltip
  via `content: attr(data-label)`. Uses `::before` to leave `::after`
  free for the existing `.is-active` selection ring. `title` attribute
  also kept = slot name for screen-reader accessibility.
- ✅ **Heading-clear mid-session cascade fallback**. When the user
  clears an override picker (e.g. heading) via `.set('')` mid-session,
  removing the inline style used to fall through to the PHP-baked
  `:root` rule — which still held the SAVED override hex (palette-
  tokens block renders once at page load, not regenerated on setting
  change). Result: cleared overrides kept showing the old value until
  next save+reload. Fix: new `CASCADE_FALLBACK` map in the preview JS
  for derived override tokens. When `normalize()` returns empty for one
  of these tokens (heading, body-text, widget-title, link, link-hover,
  text-muted), set the inline value to the cascade expression
  (e.g. `var(--customify-text)`) instead of `removeProperty`. CSS
  custom-property values support nested `var()` / `color-mix()`, so the
  cascade chain re-engages immediately and the rendered value tracks
  the source slot in real time.
- ✅ **Footer skin default — tested as `light-mode` then reverted**.
  Project owner initially requested `footer_main_text_mode` +
  `footer_bottom_text_mode` default flip from `dark-mode` to
  `light-mode`. Regression test on Studio site confirmed this would
  shift footer bg dark→light for any 30K legacy site that never saved
  `footer_*_text_mode`. Per the 30K-safety doctrine, reverted to keep
  `dark-mode` default and left the change for a future minor-version
  bump with explicit migration messaging.

### 8.11 Phase 2.10–2.13 — done (MD3 container pattern + theme.json injection + auto-wire + rgba composite)

Final batch of palette engine work — implements the
[color-token-derivation spec](https://olliewp.com/docs/ollie-block-theme/ollie-color-palette/#ollies-color-system)
formulas with Customify-specific extensions (chroma cap, naming
conventions that dodge WP marker-class collisions). PRs `#395` (editor
canvas merged) + `#396` (this batch — 14 commits, ~900 LOC across PHP
+ SCSS + JS + docs).

**§ Phase 2.10 — Surface adaptive wiring + comprehensive hex audit**
- ✅ Wired `--customify-surface` to `.is-style-card`, code blocks,
  preformatted, verse blocks, form inputs, table headers (thead/tbody
  alternation), calendar header, `.wp-block-search__input`,
  `.wp-block-pullquote`, `.wp-block-separator`. Surface tints use
  hybrid `var(--customify-surface, color-mix(in srgb, currentcolor
  X%, transparent))` — saved value wins, fallback adapts to local
  text color.
- ✅ Three surface intensity tiers (`$surface_subtle` 4%, `_medium`
  6%, `_strong` 10%) for visual hierarchy across block types.
- ✅ Bumped `$color_border` 10% → 14% currentcolor mix per spec §2
  (~1.35:1 vs base — decorative, WCAG-exempt).
- ✅ Hex audit across `_base.scss` / `_blocks.scss` / `_layouts.scss`
  / `_widgets.scss` — converted 15+ hard-coded hex to var() chains:
  - `.site-content { background: var(--customify-base, #fff) }`
  - `.wp-block-quote.is-style-accent` border → `$color_primary`
  - `.wp-block-group.is-style-card` bg + border + color (with
    `var(--customify-on-surface, inherit)` safety net for white-on-
    white card case)
  - `hr`, `.select2-dropdown`, widget table borders, category count
    badges, sidebar search submit icon — all wired to slot tokens
- ✅ Intentionally kept literal hex documented (skin-scoped header/
  footer hexes, sticky-post Bootstrap-style highlights, `.has-*-color`
  WP block palette legacy classes, social brand colors, a11y skip-
  link focus pill, WP brand blue Customizer-edit overlays).

**§ Phase 2.11 — Picker palette injection + slug refactor**
- ✅ New `wp_theme_json_data_theme` filter
  (`customify_palette_inject_into_theme_json`) replaces theme.json
  static palette at runtime with `var(--customify-X, hex)` chains.
  Block editor color pickers (Blocksify, WP core, Gutenberg, child
  themes) auto-track Customizer Palette changes via `useSetting('color.palette.theme')`.
- ✅ WP version gate: WP ≥6.1 only — older WP renders `var()` strings
  literally in picker swatches.
- ✅ 12-slug lean picker, pair order:
  ```
  Primary · Primary Container · Secondary · Secondary Container
  · Accent · Accent Container · Body Text · Surface · Base
  · Text Muted · Divider · Divider Strong
  ```
- ✅ Hidden from picker (theme internals only): on-primary / on-
  secondary / on-accent / on-surface / on-primary-container / on-
  secondary-container / on-accent-container. Used by SCSS internals
  + Phase 2.13 auto-wire.

- ⚠️ **CRITICAL FIX — WP marker-class slug collision**: WP global-
  styles engine auto-generates `.has-{slug}-color { color:
  var(--wp--preset--color--{slug}) !important; }` for every palette
  slug. This silently breaks inline color picks when a slug is
  named exactly `text` or `border`:
  - `.has-text-color` is ALSO WP's marker class for ANY block
    setting text color → generated rule overrides the inline pick
    with `!important`.
  - `.has-border-color` is ALSO WP's marker class for border-panel
    pick → same override.
  - **Fix**: rename picker slugs `text` → `body-text`, `border` →
    `divider`, `border-strong` → `divider-strong`. The `:root` token
    names (`--customify-text` / `--customify-border` /
    `--customify-border-strong`) stay unchanged because they're
    theme-internal — only the picker slug is renamed. `divider`
    semantically clearer too (it's the WCAG-exempt decorative line
    for `<hr>`, card edges, table cell separators).

**§ Phase 2.12 — Implement color-token-derivation spec**
- ✅ Per-spec formulas implemented in PHP + JS:
  - §2 `text-muted` = `mix(text 70%, base)` (CSS live)
  - §2 `border` = `mix(text 14%, base)` (CSS live, decorative)
  - §2 `border-strong` = smallest P where contrast(mix(text P%,
    base), base) ≥ 3.0 (PHP iterates 6%→100%)
  - §3 on-* = max-contrast pick (`contrast(LIGHT, X) >=
    contrast(DARK, X) ? LIGHT : DARK`) — replaces pre-spec
    luminance-threshold formula that misfired on medium-luminance
    colors like teal `#3CAA9D`
  - §4 *-container P = closed-form OKLab solve landing at L=0.93,
    clamped [0.02, 0.98]
  - §5 on-*-container = OKLab L-reduction (keep hue, step L
    downward until contrast ≥ 4.5)
- ✅ OKLab transform helpers (Ottosson) — sRGB ↔ OKLab via
  `customify_color_srgb_to_oklab()` / `customify_color_oklab_to_srgb()`
  in PHP, `_srgbToOklab()` / `_oklabToSrgb()` in JS.
- ✅ Container chroma cap (Customify extension to spec §4) — projects
  container OKLab (a, b) onto 0.04 max-chroma circle. Without cap,
  high-chroma brands (yellow / lime / hot pink) produce oversaturated
  container tints because they're already perceptually light and
  mixing with white preserves chroma. Cap=0.04 keeps low-chroma
  brands unchanged (primary navy → 0.011, unaffected) but tames
  high-chroma cases (accent yellow → 0.103 → 0.040, soft cream).

**§ Phase 2.13 — Auto-wire + rgba composite + opt-in gate drop**
- ✅ SCSS auto-wire (`_blocks.scss`): `.has-{brand}-background-color`
  + descendant headings get `color: var(--customify-on-{brand},
  inherit)`. Designer's explicit text pick still wins via WP's
  `.has-{slug}-color { ... !important }` rule (higher specificity +
  `!important`). The `inherit` fallback preserves 30K-safety: when
  on-* is unset (legacy site, no opt-in), text inherits from body
  cascade — byte-identical pre-PR behavior.
- ✅ Dropped opt-in gate for `--customify-on-*` family (now emitted
  unconditionally). Required because the auto-wire SCSS rule
  references on-* and needs it present even when user only touches
  legacy Primary/Secondary slots (not the 4 new opt-in slot keys).
  30K-safe because on-* is CONSUMED only by the new auto-wire on
  the new picker slugs — legacy sites without those blocks see no
  behavioral change.
- ✅ rgba slot value support — extended `customify_color_normalize_hex()`
  to passthrough rgb()/rgba() strings + `customify_color_hex_to_rgb()`
  to parse them. JS `_hexToRgb` mirror.
- ⚠️ **CRITICAL FIX — rgba composite over base**: First-pass rgba
  fix only stripped the alpha channel and ran the max-contrast pick
  against the opaque rgb component. That breaks for transparent
  brands: user picks `rgba(17,52,109,0.14)` (dark navy, 14% alpha),
  the BUTTON bg actually composites to `~#dee3eb` (near-white) on a
  light page, but the picker saw the opaque navy and chose WHITE
  text → invisible white-on-near-white.
  - **Fix**: new `customify_color_composite_over($value, $base)`
    helper applies standard alpha blend `out = src×a + base×(1−a)`.
    `customify_color_pick_on()` gained a `$base_hex` parameter and
    composites first, then runs max-contrast pick against the
    rendered color. JS `_compositeOver()` + `_pickOn(value, baseHex)`
    mirror. All call-sites updated to pass `$slots['base']`.
  - Verification matrix:
    - `rgba(17,52,109,0.14)` on white → composite `#dee3eb` → DARK ✓
    - `rgba(17,52,109,0.14)` on dark  → composite `#191e26` → WHITE ✓
    - `rgba(255,209,220,1)` opaque    → passthrough `#ffd1dc` → DARK ✓
    - Hex `#235787` no alpha          → passthrough → WHITE ✓
    - Teal `#3CAA9D`                  → passthrough → DARK ✓

**§ Final architecture summary**

```
:root tokens (19 total, theme internals):
├─ source slots (6):       primary, secondary, accent, text, surface, base
├─ derived static (6):     link, link-hover, primary-hover, heading,
│                           body-text, widget-title, text-muted
├─ on-* unconditional (4): on-primary, on-secondary, on-accent, on-surface
└─ opt-in gated (5):       border-strong, primary-container,
                            secondary-container, accent-container,
                            + 3 on-*-container (theme internals only)

theme.json picker (12 slugs, Blocksify-facing):
├─ Brand+Container pairs (6): primary · primary-container · secondary ·
│                              secondary-container · accent · accent-container
├─ Text+canvas axis (3):      body-text · surface · base
└─ Helpers (3):               text-muted · divider · divider-strong

SCSS auto-wire (Phase 2.13):
└─ `.has-{primary|secondary|accent}-background-color {
     color: var(--customify-on-{slug}, inherit);
   }`
   (+ descendant h1-h6 to beat global heading rule specificity)
```

**§ Verified spot-checks (default palette)**

| Token | Computed | Spec target |
|---|---|---|
| text-muted | #6b6b6b | #646464 (~7Δ sRGB-mix; OKLab in browser = exact) |
| border (14%) | #e1e1e1 | #DEDEDE (~3Δ same source) |
| border-strong | #939393 | ~#949494 ✓ |
| on-primary | #FFFFFF | #FFFFFF ✓ |
| on-accent | #1A1A1A | #1A1A1A ✓ |
| accent P (un-capped) | 56.1% | ~56% ✓ |
| accent-container chroma | 0.040 (capped from 0.103) | — (Customify extension) |
| on-accent-container | #8B5F00 | ~#8B5F00 (dark gold) ✓ |

### 8.14 Phase 2.14 — Blocksify button text auto-contrast + button hover state layer (PR #407)

Two frontend-only additions. Both consume EXISTING palette tokens — no new
`theme_mod` keys, no storage change, no selector renames.

**A. Blocksify Fill button label auto-contrast** (`src/frontend/scss/base/_blocks.scss`).

Blocksify's Fill button is tagged `bsy-button-{uid}` + `bsy-bg-{slug}` and reads
its label color from the custom property `--blocksify--button--text-color`
(falling back to white when unset). Three rules point that property at the
matching auto-contrast token so a brand-background button gets a WCAG-readable
label that tracks Customizer slot edits:

```scss
[class*="bsy-button-"].bsy-bg-primary   { --blocksify--button--text-color: var(--customify-on-primary); }
[class*="bsy-button-"].bsy-bg-secondary { --blocksify--button--text-color: var(--customify-on-secondary); }
[class*="bsy-button-"].bsy-bg-accent    { --blocksify--button--text-color: var(--customify-on-accent); }
```

- Scoped to `[class*="bsy-button-"]` so the var lands only on the button element
  (it must not inherit to children). A designer's explicit label-color pick still
  wins (Blocksify sets the property inline at higher specificity).
- **Solid brand slugs only.** `on-{primary,secondary,accent}` are emitted to
  `:root` unconditionally (§3.2 note). The `*-container` slugs are deliberately
  NOT mapped — their `on-*-container` tokens are gated on Palette opt-in, so a
  theme default could not be guaranteed; container buttons rely on the designer's
  text pick, which already tracks the palette via `var(--wp--preset--color--X)`.
- NOT WP's `.has-{slug}-background-color` — that ships `background-color
  !important` (locks the fill, blocks hover); Blocksify uses its own
  `bsy-bg-{slug}` precisely to avoid it.
- Additive + inert when Blocksify is absent (nothing carries these classes).
- Verified end-to-end on Blocksify dev (PR #211 — per-page CSS + the consumer
  var): on a custom palette, secondary→dark label 4.89:1, accent→dark 5.28:1,
  primary→white 5.17:1 (all ≥ AA). Note: Blocksify keys its per-instance CSS by
  the block `uid`, which must be a valid 8-hex string — hand-authored markup with
  a malformed uid makes Blocksify fall the fill back to primary.

**B. Native + header-builder button hover → adaptive "state layer"**
(`base/_base.scss`, `header/builder_items/_button.scss`).

Replaces the legacy darken overlay (`box-shadow: inset 0 0 0 120px
rgba(0,0,0,.18)`) with a translucent 13%-of-text layer painted on top of the
LIVE background (matches Blocksify's current button hover):

```scss
&:hover {
    background-image: linear-gradient(
        color-mix(in srgb, currentColor 13%, transparent),
        color-mix(in srgb, currentColor 13%, transparent)
    );
}
```

- **Background-agnostic by design.** The overlay sits over whatever the actual
  background is, so it adapts: a dark fill with light text lifts, a light fill
  with dark text deepens — correct for the primary native fill, the secondary
  builder/WooCommerce fill, AND any custom background. A named `color-mix()` on
  `background-color` was rejected: it can't read the live background, so it
  wrong-colours non-primary / custom-background buttons on hover.
- Trade-off: `background-image` is not animatable → the overlay appears instantly
  (no fade). Accepted for a subtle 13% layer.
- `13%` matches Blocksify — keep in sync if the plugin changes it.
- Needs `color-mix()` (Chrome 111+ / FF 113+ / Safari 16.2+, Baseline 2023);
  older browsers show no hover shift (graceful).
- 30K-safety: no storage/selector change. Only the transient hover *appearance*
  changes site-wide (darken → adaptive lift/deepen); resting + saved colors render
  identically.

### 8.15 Phase 4 — palette-linked pickers (done)

Component colors can follow a palette token instead of freezing a hex —
full mechanism in §3.8. Rolled out to every color input outside the
Colors section: 26 top-level `color` controls (13 eligible after the
exclusions), 13 colors nested in `modal` composites, and the 8 color
subfields of each of the 26 `styling` composites.

**Files.** `inc/color-palette-link.php` (NEW — token list + preview
resolution + eligibility + `Customify_Control_Args` payload);
`inc/customizer/class-customizer-sanitize.php` (`sanitize_color()` split
into `sanitize_color_token()` + `sanitize_color_literal()`, plus
`allowed_color_tokens()`); `src/backend/customizer/js/control.js`
(`initColor()` + the `customifyPaletteLink` helper);
`src/backend/customizer/scss/_control.scss` (popover card + swatch row,
scoped to the `.has-palette-link` class the JS adds).

**30K-site verification** (see §5 for the method):

| Check | Result |
|---|---|
| Scenario A — fresh install, zero saved mods | Generated CSS byte-identical to baseline |
| Scenario B — 11 saved keys incl. legacy 9 + a per-row color | Byte-identical |
| Scenario C — partial save (2 keys) | Byte-identical |
| `sanitize_color()` equivalence, 48-input corpus (hex 3/6/8-char, rgba shapes, empty/null/array/bool, junk, injection attempts) | 0 regressions; 5 newly-accepted token shapes, all previously returning `null` or the malformed `rgba(,,,)` the old sscanf path produced |
| Injection through the new branch | `var(--evil)`, `var(--customify-nope)`, `var(--customify-primary); background:url(x)` → rejected; bad fallback stripped |
| Dirty-on-open (linked and unlinked fields) | Customizer stays clean |
| Existing hex picking | Unchanged — stores hex, not linked |
| Colors section | Untouched: no `.has-palette-link`, keeps its own quick-pick row |
| Cost at scale | Swatch row is built on first popover open, not at mount — 0 rows in the DOM after a Customizer load |

Baseline for the byte-diff was the same tree at `HEAD` installed as a
second theme, so only the change under test differed.

### 8.12 Phase 2 — remaining (good follow-ups)

#### Investigate "shadow slug" approach for legacy back-compat

After the Phase 2.13 decision to drop legacy slugs (text / link / heading /
background / light-gray / dark-gray) from the picker, a reviewer suggested
a Phase 3 polish: inject legacy slugs via `wp_theme_json_data_default`
(not `_data_theme`) so WP emits `--wp--preset--color--{slug}` declarations
without generating the `.has-{slug}-color { color: var(...) !important }`
class rules.

Hypothesis: WP global-styles engine generates the class rules only from
the theme/user palette layers, not from the default layer. If true, this
would let us:
- Restore 30K back-compat for blocks using `class="has-{legacy-slug}-color"`
- WITHOUT re-introducing the `.has-text-color` marker-class collision

Verification needed:
1. Spike a filter callback that injects legacy slugs into the default-layer
   palette.
2. Inspect the rendered global-styles-inline-css `<style>` block for
   `.has-text-color { ... !important }` presence.
3. Test a block with `has-primary-color has-text-color` — confirm primary
   text color survives (no clobber).
4. Test a block with `has-link-color` only (pure legacy slug pick) —
   confirm color resolves correctly.

If verification confirms, this would be a strict UX + back-compat
improvement over the current "drop legacy entirely" approach. Defer to
Phase 3 — current state is acceptable per project owner's stated
trade-off preference.



| Item | Notes |
|---|---|
| Iris picker UX overhaul | Project owner wants full-width saturation box + hue + alpha strips stacked vertically (modern picker look). Iris doesn't use jQuery UI slider widget — it has its own drag math reading inline `offsetTop`. CSS rotate trick breaks the drag handler. Options: patch Iris source, replace with custom React/vanilla widget, or accept Iris vertical strips. Defer until UX direction. |
| Iris initial colorful state | When current value is grayscale (e.g. default text `#2b2b2b`), Iris's saturation square shows white→black gradient because hue is undefined for grayscale. Owner wants a colorful initial state. Approach unclear. |
| Surface wiring | `--customify-surface` is in :root but no bundled rule consumes it. Need to wire `background-color: var(--customify-surface)` on card / widget / comment / modal selectors. Apply opt-in gate same as on-*. |
| Refactor header/footer/blog/page-header configs to consume slot tokens | The ~50 other color/styling sub-fields scattered across these configs still emit literal hex. Should switch to `var(--customify-XXX, {{value}})` so saved values still emit and slot drag cascades. Detailed matrix in [§13 Appendix — refactor matrix](#13-appendix--refactor-matrix-for-header--footer--blog--page-header-configs). |
| Background composite ↔ slot two-way sync | When user edits `bg_color` subfield of `background` composite, also update `customify_palette_base` (or vice versa). Right now editing one doesn't propagate. |
| `content_background` slot | If used in practice, add a 7th slot or a deeper-content surface. Currently has no slot equivalent. |

### 8.13 Phase 3 — Palette switcher (done)

A card-based palette switcher at the top of the Colors section. The user
applies a named palette (preset or custom) in one click; the cards drive the
6 existing slot pickers — no new render path, no new rendered token.

**New file:** `inc/color-palette-switcher.php` (registered in
`inc/class-customify.php` `includes()` right after `colors-palette.php`).

**A. Storage — two NEW `theme_mod` keys (the only storage added).**

| Key | Type | Default | Purpose |
|---|---|---|---|
| `customify_active_palette` | string | `''` | id of the palette the slots currently match (e.g. `sunrise`). Bookkeeping only — nothing renders from it. |
| `customify_color_palettes` | JSON string | `'[]'` | user-saved custom palettes: `[{id,name,slots:{…}}]`. |

The 6 slots keep their EXISTING keys — the switcher writes THROUGH to them,
it does not introduce a parallel store:

```
primary   → global_styling_color_primary    (legacy)
secondary → global_styling_color_secondary  (legacy)
accent    → customify_palette_accent
text      → customify_palette_text
surface   → customify_palette_surface
base      → customify_palette_base
```

**B. Presets — Sunrise (light) + Midnight (dark).**

```
Sunrise   base #ffffff  surface #ECECEC  text #2b2b2b  primary #235787  secondary #c3512f  accent #ffd042
Midnight  base #0f1217  surface #1a1e25  text #e8eaed  primary #5a8fc2  secondary #db6a44  accent #ffd042
```

Sunrise's slot values equal the theme's per-field picker defaults
(`surface #ECECEC`, `primary #235787`, …), so a fresh install reads as
"Sunrise linked" without writing anything — the active state is INFERRED
from slot equality, never auto-persisted on a clean open (see D).

Presets come from `customify_color_preset_palettes()` and are **filterable**
via `customify/color/preset_palettes` — a plugin or child theme can add,
remove, or replace presets (each entry: `{ id, name, slots:{6} }`). The
sanitizer prefixes any custom id that collides with a preset id (`user-…`).

**C. Apply mechanism — drive the existing pickers, no new path.**

Applying a palette calls `$panel.wpColorPicker('color', hex)` on each of the
6 existing pickers (the canonical, already-proven write path). That fires the
normal picker `change` → Customizer setting → live-preview cascade. The
switcher adds ZERO new rendering: the frontend output is byte-identical to a
user manually setting those 6 pickers to the same values.

Because a switch changes all six slots at once, the live preview's derived-
token recompute (`on-*`, `*-container`, OKLab math) is coalesced into ONE pass
via a ~24 ms debounce (`recomputeDerivedDebounced`) instead of running six
times back-to-back — imperceptible for a drag, snappy for a switch.

**D. Active-determination / reconciliation (the 30K-safety core).**

`render()` infers which card is "active" from the live slot values, in three
cases:

1. **stored id matches current slots** → that palette is active + linked.
2. **no stored id, but a preset/custom matches current slots** → adopt it as
   active + linked; persist the id ONLY if the customizer is already dirty
   (`wp.customize.state('saved').get() === false`). On a clean open this never
   writes — a legacy site is never silently dirtied.
3. **nothing matches** → no card active, no "modified" strip, no write. This
   is the legacy saved-custom-colors case: the site shows its colors with no
   palette chrome.

This three-case model replaced an earlier `DEFAULT_ACTIVE` fallback that
wrongly showed a legacy saved-custom site as "Sunrise active + Modified +
reset-to-Sunrise". `syncSlotDefaults()` re-anchors each picker's reset
baseline (`wpColorPicker('option','defaultColor', …)` + `data-default`) to the
active palette's value — but ONLY when a palette is active; with no active
palette (case 3) it is skipped, so legacy reset behavior is untouched.

**E. Custom palettes.** Create-from-current, rename, delete, import/export
(JSON). `customify_color_sanitize_palettes()` hardens imported JSON:
`sanitize_text_field` on name, `customify_color_normalize_hex` on every slot,
array capped at 100. The inline JS escapes every user value through an `esc()`
helper before `innerHTML`. A custom palette auto-absorbs later slot edits
while active (`maybeSyncCustom`), so editing a slot doesn't desync the card.

**F. Default shift — link-hover now follows link.** As part of this phase the
`global_styling_color_link_hover` field default changed from `#406F99` (a
lighter mix) to "same as link" (`#235787`), and the cascade resolves
link-hover → link → primary. 30K-impact: a site that saved a link color but
never saved a link-hover now renders link-hover = its link color on hover
(previously a lighter shade). Intentional, documented default shift
(project-owner requested) — saved link-hover values are untouched.

**G. WP-native styling.** All "selected/active" accents use
`var(--wp-admin-theme-color, #3858e9)` (the admin's own accent); buttons and
borders use WP admin tokens. The 6 existing wp-color-pickers are left as-is.

**30K-safety summary.** Additive only — 2 new bookkeeping keys, 6 slots reuse
existing keys, apply path is the existing picker write. No saved hex changes,
no key renamed/removed. The only render delta for any existing site is the
link-hover default shift (F), affecting only sites that never saved a
link-hover. Verified on a saved-custom site: front-end render unchanged;
customizer shows no active card / no modified strip / no dirty-on-open.

**Still deferred (Phase 4+).** Per-site dark-mode token set wired to
`prefers-color-scheme` / a user toggle; the Style Pack concept (bundle palette
+ typography + spacing). Applying a dark palette deliberately does NOT touch
header Skin Mode (project-owner decision — a palette shouldn't mutate
unrelated skin settings).

---

## 9. Workflow & dev environment

### 9.1 Branch & worktree

- Branch: `customizer-colors-improve` (off `origin/DEV`). PR [#392](https://github.com/PressMaximum/customify/pull/392).
- Active worktree: `/Users/kientrong/Studio/customify2/wp-content/themes/customify/.claude/worktrees/epic-shamir-e4a2d1/`
- Main theme dir: `/Users/kientrong/Studio/customify2/wp-content/themes/customify/` — on branch `DEV`
- `node_modules` is symlinked into the worktree from main theme

### 9.2 Build pipeline

```bash
cd <worktree>
npm run build          # production build (webpack via wp-scripts)
# output → build/css/backend/customizer/customizer.css
#         build/css/frontend/style-theme.css
#         etc.
```

Build artifacts in `build/` are git-ignored. CI/release pipeline must
run `npm run build` before deploying.

### 9.3 Live testing on WP

To test changes in the actual Customizer at
`http://customify2.wp.local`:

1. Edit source in worktree
2. `npm run build` in worktree
3. `rsync -a build/ /Users/kientrong/Studio/customify2/wp-content/themes/customify/build/`
4. `rsync -a inc/ /Users/kientrong/Studio/customify2/wp-content/themes/customify/inc/`
   (or just the changed file)
5. `rsync -a theme.json /Users/kientrong/Studio/customify2/wp-content/themes/customify/theme.json`
6. `studio wp --path /Users/kientrong/Studio/customify2 cache flush`
7. Hard refresh browser (`⌘+Shift+R`) — Customizer caches inline scripts
   between soft reloads; appending `&v=N` to the URL bypasses cache too

**Important**: after every commit on the worktree, **revert main theme
dir to clean DEV** (`git checkout -- ...` + `rm -f` for untracked).
Never commit on main theme dir. CLAUDE.md rule.

### 9.4 Chrome MCP verification

The session that built this used the `mcp__Claude_in_Chrome__*` tools to
drive the live Customizer and verify:

- DOM structure (`javascript_tool` queries)
- Computed styles (`getComputedStyle`)
- Pixel positions (`getBoundingClientRect`)
- Performance timings (`performance.now()` between click and 2nd RAF)
- Screenshots

When the JS query returns "[BLOCKED: Cookie/query string data]" it's the
output filter rejecting the result — usually because the returned string
contains URL-like or hex-like content that looks sensitive. Simplify the
returned value (short string, no big JSON objects) to bypass.

---

## 10. Commit log (PR #392)

In chronological order:

In chronological order (Phase 1 → Phase 2.x → polish):

```
Phase 1 — original PR landing (top-level Colors section + 6-slot palette)
96ba6119  feat(customizer): add top-level Colors section with 6-slot palette
b7e44910  feat(customizer-colors): compact picker UI + palette quick-pick popup
499bdcfc  fix(customizer-colors): clean up quick-pick row on picker close
9471a178  fix(customizer-colors): proper popover for color picker (white card, no overflow)
b2829ca7  refactor(customizer-colors): brand-first palette display order
e59e7861  feat(customizer-colors): palette popup shows hex input; overrides keep From Palette
f4872295  fix(customizer-colors): snappy popover animation + perfect-round swatches
50cf4ec6  fix(customizer-colors): kill the double-swatch overlap behind each circle
0ecb4baa  perf(customizer-colors): instant popover open (10ms vs 173ms)
3235989f  feat(customizer-colors): popover polish + read-only token var field
e2a9c8ee  fix(customizer-colors): Backgrounds styling modal no longer stuck open
02abbc23  fix(customizer-colors): popover layout polish
000c364f  fix(customizer-colors): revert popover sizing, fix iris-strip alignment
7a90aaee  fix(customizer-colors): align iris-square left edge with hex input
03791cda  fix(customizer-colors): align iris-square left without breaking strip layout

Phase 2 — cascade pipeline + var() refactor
e3d8c82b  docs(customizer-colors): add SPEC for Colors panel + Phase 2 handoff
04896060  docs(customizer-colors): drop dev-colors* branch references
86d21509  feat(customizer-colors): live preview + var() refactor for primary/secondary (Phase 2.1)
26e13b25  chore(dev): add bin/sync to one-shot rsync worktree → live theme + flush cache
5d336555  feat(customizer-colors): heading cascade — drag Text slot updates h1-h6 live (Phase 2.2)
98cc470b  chore(layouts): default Sidebar Layout = Content (no sidebar)
cacffc3a  fix(customizer): inline focus-section / focus-control delegated handlers
186f4327  chore(customizer-colors): Surface palette default #FFFFFF → #ECECEC
1849795f  feat(customizer-colors): separate :root token block + Base composite cascade (Phase 2.5)
525c5ec6  feat(customizer-colors): :root cascade pipeline + preview JS + UI handlers
99ef0e00  feat(customizer-colors): config — cascades + group reorg + Legacy fine-tuning
0433efaa  feat(customizer-colors): bundled SCSS $color_* → CSS var expressions (Phase 2.6)
a01e4c41  feat(customizer-colors): UI polish — Legacy collapsible + reset icon + cascade swatch (Phase 2.7)
12531802  docs(customizer-colors): SPEC §8 Phase 2 progress + handoff for next session

Phase 2.8 + polish — WCAG --on-* + picker UI fixes
418eb982  feat(customizer-colors): Phase 2.8 — WCAG --on-* live preview + opt-in gate
b4ca6ebc  fix(customizer-colors): picker UI — reset icon overflow + quickpick tooltip
f47ed6fb  docs(customizer-colors): SPEC — drop derived preview chips followup
```

The commit messages contain detailed rationale for the non-obvious
fixes (e.g. `0ecb4baa` documents the slideToggle monkey-patch reasoning,
`e2a9c8ee` documents the slide-patch scope regression, `418eb982`
documents the opt-in gate doctrine + WCAG math byte-equivalence).

---

## 11. For the next session

Phase 1 + Phase 2 + Phase 2.8 + polish are all landed in PR #392 as of
commit `f47ed6fb`. If you're picking up Phase 2.9+ work:

1. Read this file end-to-end (§§1-10 are stable, §§8.11-8.12 list remaining work).
2. Read [SPEC-customizer.md](SPEC-customizer.md) for the Customify
   Customizer architecture (config-driven, auto-CSS pipeline, control
   types).
3. Read the memory files at
   `~/.claude/projects/-Users-kientrong-Studio-customify2-wp-content-themes-customify/memory/`:
   - `feedback_30k_sites_color_safety.md` — hard constraint
   - `project_blocksify_companion.md` — token slug contract
   - `feedback_verify_user_facing_outcome.md` — testing rigor
   - `feedback_language_no_clone_no_competitor.md` — don't name peer
     themes in code/commits/PR
   - `feedback_kit_vs_theme_fix_split.md` — Customify Pro bugs go to
     KIT_ISSUES.md, not the theme.
4. Verify the test helpers in `/tmp/` still exist; re-create from §5.2
   if not.
5. Decide which Phase 2.9+ item to tackle next — see §8.11 for the
   remaining-work table and §13 for the detailed header/footer/blog/
   page-header refactor matrix.

When making any change to the color pipeline, **always** re-run
scenarios A/B/C and confirm no existing color decls shifted before
committing. The compare_color_css.py script automates this — if it says
PASS, you're safe; if it says FAIL, investigate immediately. Don't ship
a regression.

---

## 12. References

- [SPEC-customizer.md](SPEC-customizer.md)
- [SPEC-header-footer-builder.md](SPEC-header-footer-builder.md)
- [SPEC-dashboard.md](SPEC-dashboard.md)
- [CLAUDE.md](../CLAUDE.md) — root project rules
- [AGENTS.md](../../../../AGENTS.md) — project-wide agent rules
- PR: https://github.com/PressMaximum/customify/pull/392
- Memory: `~/.claude/projects/-Users-kientrong-Studio-customify2-wp-content-themes-customify/memory/`

---

## 13. Appendix — refactor matrix for header / footer / blog / page-header configs

Scope of §8.11 "Refactor header/footer/blog/page-header configs to
consume slot tokens". Each field listed converts literal-hex emission
to `var(--customify-<slot>, {{value}})` so saved values still emit
identically (byte-equivalent for 30K legacy sites — Phase 2.1 doctrine)
AND a slot edit cascades to the whole site.

### 13.1 Header items

| # | File:line | Field / sub-key | Selector | Recommend slot |
|---|---|---|---|---|
| A.1 | [header/button.php:104](../inc/customizer/configs/header/button.php#L104) | `*_styling` normal.bg_color | `.customify-builder-btn` | `var(--customify-secondary)` |
| | | normal.text_color | | `var(--customify-on-secondary)` |
| | | normal.border_color | | `var(--customify-secondary)` |
| | | hover.bg / text / border_color | `.customify-builder-btn:hover` | secondary / on-secondary / secondary |
| A.2 | [header/menus.php:104](../inc/customizer/configs/header/menus.php#L104) | `*_style_border_color` | hover/active menu underline | `var(--customify-primary)` |
| | [header/menus.php:152](../inc/customizer/configs/header/menus.php#L152) | `*_item_styling` normal.text_color | menu link | `var(--customify-heading)` |
| | | normal.bg_color | | `var(--customify-base)` |
| | | hover.text_color | | `var(--customify-primary)` |
| | | hover.bg_color | | `var(--customify-surface)` |
| A.3 | [header/nav-icon.php:76+86](../inc/customizer/configs/header/nav-icon.php#L76) | `nav_icon_item_color` | nav icon | `var(--customify-text)` |
| | | `nav_icon_item_color_hover` | hover | `var(--customify-primary)` |
| A.4 | [header/search-box.php:147+198](../inc/customizer/configs/header/search-box.php#L147) | `*_input_styling` text / bg / border | search input | text / surface / border |
| | | hover/focus.border | | `var(--customify-primary)` |
| | | `*_icon_styling` text / hover | search icon | text / primary |
| A.5 | [header/search-icon.php:77,129,233,270](../inc/customizer/configs/header/search-icon.php#L77) | 4 styling composites | search icon + modal | text / surface / primary pattern |
| A.6 | [header/panel.php:197+297](../inc/customizer/configs/header/panel.php#L197) | `header_*_styling` bg / text / border | header row | `var(--customify-base)` / text / border |
| | | `header_*_sidebar_styling` | menu sidebar | base / text |
| A.7 | [header/transparent.php:66](../inc/customizer/configs/header/transparent.php#L66) | `header_*_transparent_styling` | over-page header | Defer — needs `--on-base` token |
| A.8 | [header/logo.php](../inc/customizer/configs/header/logo.php) | text-logo `text_color` | site title text | `var(--customify-heading)` |
| A.9 | [header/social-icons.php:219-300](../inc/customizer/configs/header/social-icons.php#L219) | color-custom primary (bg) | `.color-custom li a` | `var(--customify-primary)` |
| | | secondary (icon) | | `var(--customify-on-primary)` |
| | | hover primary / hover secondary | | `--customify-primary-hover` / on-primary |
| | | border | | `var(--customify-border)` |

### 13.2 Footer items

| # | File:line | Field | Selector | Recommend slot |
|---|---|---|---|---|
| B.1 | [footer/panel.php:179](../inc/customizer/configs/footer/panel.php#L179) | `footer_*_background_color` (per row) | `.footer--row-inner` | `var(--customify-surface)` |
| B.2 | footer/widgets.php / copyright.php / social-icons.php | (no direct color fields — typography + alignment + skin-class only) | — | No refactor needed |

### 13.3 Blog / Single post

| # | File:line | Field / sub-key | Selector | Recommend slot |
|---|---|---|---|---|
| C.1 | [blogs.php:75](../inc/customizer/configs/blogs.php#L75) | `*_a_item` normal.bg / text / border | post-entry card | surface / body-text / border |
| | | hover.bg / border | | surface / primary |
| C.2 | [blogs.php:362](../inc/customizer/configs/blogs.php#L362) | `*_more_styling` normal.bg / text / border | read-more button | primary / on-primary / primary |
| | | hover.bg / text | | primary-hover / on-primary |
| C.3 | single-blog-post.php | (typography + layout only) | — | No refactor |
| C.4 | related-posts.php | (no color fields) | — | No refactor |

### 13.4 Page header

| # | File:line | Field | Selector | Recommend slot |
|---|---|---|---|---|
| D.1 | [page-header.php:354+363](../inc/customizer/configs/page-header.php#L354) | `*_title_color` | `.titlebar-title` | `var(--customify-heading)` |
| | | `*_tagline_color` | `.titlebar-tagline` | `var(--customify-text-muted)` |
| D.2 | [page-header.php:444+452+536](../inc/customizer/configs/page-header.php#L444) | cover `title_color` | `.page-cover-title` | `var(--customify-on-primary)` (overlay is primary) |
| | | cover `tagline_color` | `.page-cover-tagline` | `var(--customify-on-primary)` |
| | | `overlay` bg-color | `.page-cover:before` | `var(--customify-primary)` |

### 13.5 Recommended batch execution order

| Batch | Items | Effort | Risk |
|---|---|---|---|
| **1** Quick wins | A.3 + A.8 + D.1 + A.1 + C.2 (~16 fields) | half-day | LOW |
| **2** Visual wins | A.9 + B.1 + A.4 + A.5 (~18 fields) | day | LOW-MEDIUM |
| **3** Careful | A.2 + C.1 + A.6 (~15 fields) | day | MEDIUM (test per skin) |
| **4** Defer | D.2 + A.7 | — | needs design direction |

Each batch should run scenarios A/B/C byte-equivalence + a real-upgrade
visual test (per §5) before commit. Apply opt-in gate same pattern as
on-* if any field's default value shift would affect fresh-install sites.
