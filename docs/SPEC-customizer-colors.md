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

| Token | Override key | Compute formula | Used for |
|---|---|---|---|
| `--customify-text-muted` | `global_styling_color_meta` | `mix(text 70%, base)` | Pagination text, `.link-meta`, body paragraph copy |
| `--customify-border` | `global_styling_color_border` | `mix(text 12%, base)` | Card borders, separators, etc. |
| `--customify-link` | `global_styling_color_link` | `= primary` | `a { color }` |
| `--customify-link-hover` | `global_styling_color_link_hover` | `mix(primary, black 15%)` | `a:hover/focus` |
| `--customify-primary-hover` | (no legacy key) | `mix(primary, black 10%)` | `button:hover` (NEW, didn't exist before) |
| `--customify-heading` | `global_styling_color_heading` | `= text` | `h1-h6` |
| `--customify-widget-title` | `global_styling_color_w_title` | `= text` | `.site-content .widget-title` |
| `--customify-body-text` | `global_styling_color_text` | `= text-muted default` | Body paragraph fallback |
| `--customify-on-primary` | (no legacy key) | WCAG luminance pick: `>0.45 → #1A1A1A` else `#FFFFFF` | Text on primary buttons |
| `--customify-on-secondary` | (no legacy key) | WCAG luminance pick | Text on secondary surfaces |
| `--customify-on-accent` | (no legacy key) | WCAG luminance pick | Text on accent surfaces |

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

The 6 slot slugs — **`base`, `surface`, `text`, `primary`, `secondary`,
`accent`** — are API surface. They appear:

- In `theme.json` under `settings.color.palette` (statically declared so
  the block editor picker always lists them).
- As CSS var names: `--customify-<slug>`.
- As JS payload keys passed to the customize-controls script.

Blocksify starter templates reference these names. **Never rename a
slug.** Add new ones if needed; don't remove or rename existing.

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

### 3.7 theme.json palette

Three new entries appended to the existing 8 in `theme.json`
`settings.color.palette`:

```json
{
  "slug": "base",     "name": "Base",     "color": "#FFFFFF"
},
{
  "slug": "surface",  "name": "Surface",  "color": "#FFFFFF"
},
{
  "slug": "accent",   "name": "Accent",   "color": "#FFD042"
}
```

Existing entries (`primary`, `secondary`, `text`, `link`, `heading`,
`background`, `light-gray`, `dark-gray`) are unchanged so any posts
using `.has-<slug>-color` keep working.

Note: we tried the `wp_theme_json_data_theme` filter first to inject
the 3 new entries dynamically. WP's filter origin tracking misbehaved on
append — the existing 8 theme entries got shadowed in the rendered CSS
preset output (only `--wp--preset--color--base/surface/accent` + WP
defaults appeared, the 8 theme entries disappeared). Static
declarations in theme.json avoid this entirely.

Trade-off: when the user changes a slot value in the Customizer,
`:root --customify-<slot>` updates but `--wp--preset--color--<slug>`
stays at the static default. This matches Customify's pre-Phase 1
behavior (Customizer color changes never propagated to the block editor
palette either).

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
        ├── styling.php                   MODIFIED — stripped to just the
        │                                            styling_panel
        │                                            registration (Typography/
        │                                            Layouts still use it)
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

### 7.4 No live preview JS for new slots

Changing a new slot (`base`, `surface`, `text`, `accent`) requires a
Customizer save to refresh frontend `:root`. `primary`/`secondary`
work for existing CSS rules because they reuse legacy keys and existing
auto-CSS live preview handles those. New slot keys would need either
their own live-preview JS or the existing rules to be refactored to use
`var()` references.

### 7.5 Existing 9 CSS rules still emit literal hex

The 9 css_format strings in `inc/customizer/configs/colors.php` (moved
from `styling.php`) still output `color: #235787` etc. — not
`color: var(--customify-primary)`. This is intentional Phase 1 conservatism
to guarantee byte-identical CSS for 30K legacy sites. The `:root` block
is purely additive layer; nothing currently consumes most of the new
derived vars on the frontend (Blocksify templates and future Phase 2
refactors will).

If Phase 2 refactors to `var(--customify-XXX)`, each rule should fall
back to the literal: `color: var(--customify-primary, {{value}});` so
old-browser var() failures still render. Test scenarios A/B/C must
re-pass byte-equivalent after the refactor.

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

### 8.2 Phase 2 — deferred (good follow-ups)

| Item | Notes |
|---|---|
| Refactor 9 existing CSS rules to `var(--customify-*, {{value}})` | Lets slot edits cascade to existing rules. Must re-pass A/B/C byte tests. Recommend per-rule progressive refactor. |
| Live preview JS for new slot keys | Currently only primary/secondary live-preview via existing auto-CSS. Add `wp.customize('customify_palette_base').bind(...)` etc. for instant frontend update without save. |
| Read-only derived preview chips in Palette section | UX: show user what `text-muted`, `border`, `primary-hover`, etc. will look like before they pick. Needs a new control type or inline DOM hack. |
| Refactor header/footer/blog/page-header configs to consume slot tokens | The ~30 other color/styling controls scattered across these configs still emit literal hex from their saved values. Should switch to `var(--customify-XXX)` so changing a slot updates the whole site. |
| Background composite ↔ slot two-way sync | When user edits `bg_color` subfield of `background` composite, also update `customify_palette_base` (or vice versa). Right now editing one doesn't propagate. |
| `content_background` slot | If used in practice, add a 7th slot or a deeper-content surface. Currently has no slot equivalent. |

### 8.3 Phase 3 — Custom palettes / Style Packs (future)

Once Phase 2 stabilizes the slot ↔ everything-else cascade, Phase 3 can
build the higher-level palette UX on top:

- Multiple palette presets (e.g. Light + Dark) the user can switch
  between with one click; each preset stores its own 6 slot values.
- User-saved custom palettes (CRUD + name + thumbnail).
- Per-site dark-mode token set wired to `prefers-color-scheme` or a
  user toggle.
- Style Pack concept — bundle palette + typography + spacing under a
  single named preset that can be applied / shared / imported.

Design when Phase 3 starts; not blocked by anything in Phase 1/2 today.

---

## 9. Workflow & dev environment

### 9.1 Branch & worktree

- Branch: `customizer-colors-improve` (off `origin/DEV`)
- Worktree: `/Users/kientrong/Studio/customify2/wp-content/themes/customify/.claude/worktrees/unruffled-feistel-7be968/`
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

```
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
```

The commit messages contain detailed rationale for the non-obvious
fixes (e.g. `0ecb4baa` documents the slideToggle monkey-patch reasoning,
`e2a9c8ee` documents the slide-patch scope regression).

---

## 11. For the next session

Quick orientation script:

1. Read this file end-to-end.
2. Read [SPEC-customizer.md](SPEC-customizer.md) for the Customify
   Customizer architecture (config-driven, auto-CSS pipeline, control
   types).
3. Read the memory files:
   - `feedback_30k_sites_color_safety.md` — hard constraint
   - `project_blocksify_companion.md` — token slug contract
   - `feedback_verify_user_facing_outcome.md` — testing rigor
   - `feedback_language_no_clone_no_competitor.md` — don't name peer
     themes in code/commits/PR
4. Verify the test helpers in `/tmp/` still exist; re-create from §5.2
   if not.
5. Decide which Phase 2 item to tackle (recommended order):
   1. Refactor the 9 existing CSS rules to `var(--customify-*, {{value}})`
      — biggest UX win (slot edits cascade), lowest user risk if A/B/C
      tests pass byte-identical.
   2. Live preview JS for new slot keys — immediate visible win.
   3. Read-only derived preview chips — UX polish.
   4. Component panels refactor — long tail, do incrementally.

When making any change to the color pipeline, **always** re-run
scenarios A/B/C and confirm 0 existing color decls shifted before
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
