# Preview Colors — Phase 6: Backward compatibility with `styling.php`

The new 6-slot palette and the legacy 9-color settings registered in
[`inc/customizer/configs/styling.php`](../customizer/configs/styling.php)
both target the same selectors. This plan documents the relationship,
identifies the conflict points, and lays out a Style-Pack-aligned
migration path that does **not** break existing sites.

Style Pack reference (v2.0 → v3.1 timeline): `Q2 — Migration từ Customify cũ`
in <https://wpassets.kienpc81089.workers.dev/Customify-Style-Pack/>.

## 1 — Setting inventory + slot mapping

`styling.php` registers 9 colour-type Customizer settings. Each renders
inline CSS via `Customify_Customizer_Auto_CSS` (output via
`wp_add_inline_style('customify-style', …)`) and uses its own selector
list. Mapping into the 6-slot vocabulary:

| Customizer setting | Default | New 6-slot home | Notes |
|---|---|---|---|
| `global_styling_color_primary` | `#235787` | `primary` (slot) | direct |
| `global_styling_color_secondary` | `#c3512f` | `secondary` (slot) | direct |
| `global_styling_color_text` | `#686868` | `text` (slot) | direct |
| `global_styling_color_link` | `#1e4b75` | `primary` (slot) | collapses; legacy hex deviates from slot default |
| `global_styling_color_link_hover` | `#111111` | `primary-hover` (auto-computed) | derived from primary via `color-mix(primary, #000 15%)` |
| `global_styling_color_heading` | `#2b2b2b` | `text` (slot) | collapses; legacy distinction (heading darker than body) is lost |
| `global_styling_color_w_title` | `#444444` | `text` (slot) | collapses |
| `global_styling_color_border` | `#eaecee` | `border-default` (auto-computed) | derived from text @ 12% alpha |
| `global_styling_color_meta` | `#6d6d6d` | `text-muted` (auto-computed) | derived from text @ 55% alpha |

Only **3 slots** (primary, secondary, text) round-trip with no information
loss. The other six legacy settings either collapse (heading + widget-
title → text) or become derived (link-hover, border, meta).

`base` slot has no styling.php counterpart — it covers `body { background }`
which sits in `inc/customizer/configs/background.php` (out of scope for
this analysis).

## 2 — Today's conflict matrix

How the two systems currently interact when both are present:

| Loaded asset | Order in `<head>` | Selector specificity | Source |
|---|---|---|---|
| `style-theme.css` (theme SCSS, includes `@import "overrides"`) | 1 | original SCSS rules: `(0,0,1)` for `body` etc. **Override block: `(0,0,2)` from `html` wrap** | `wp_enqueue_style('customify-style', …)` priority 95 |
| `<style id="customify-style-inline-css">` (auto_css from styling.php) | 2 (appended to handle 95) | `(0,0,1)` for `body`, `(0,2,0)` for class chains | `wp_add_inline_style('customify-style', $auto_css)` |
| `<style id="customify-preview-colors-vars">:root{ --customify-color-* }</style>` | 3 (printed by `output_root_vars()` on `wp_head` priority 1) | sets vars on `:root` | only printed when active palette exists |
| Panel JS sets vars via `documentElement.style.setProperty` (admin in preview / Customizer iframe) | runtime | inline style on `<html>` (1,0,0,0) | wins over any stylesheet |

**Cascade in detail** for `body { color: … }`:

1. Theme SCSS (`_base.scss`): `body { color: $color_text; }` → compiles to `body { color: #686868; }`.
2. Customizer inline (`styling.php` Group T): `body { color: #abc; }` (user's saved value).
3. Override (`overrides.scss` wrapped in `html`): `html body { color: var(--customify-color-text, #686868); }` — specificity `(0,0,2)`, beats both above.

Result depending on palette state:
- **Active palette set**: `--customify-color-text` is defined → override resolves to palette value → palette wins.
- **No active palette**: var is undefined → fallback `#686868` is used → override forces `#686868` → **user's customizer setting `#abc` is silently ignored**.

The second row is the **regression**. Existing sites that haven't enabled
the panel still get the override layer (it's compiled into
`style-theme.css`), and the override layer paints SCSS-default hexes over
user-configured Customizer values.

## 3 — Two-pass fix for the regression

The fallback inside `var(--customify-color-X, <hex>)` defeats the
cascade. Two coordinated changes restore correct fallthrough:

### 3a — Drop hex fallbacks from the override layer

Change every `var(--customify-color-<slot>, <hex>)` in `overrides.scss` to
either:

- `var(--customify-color-<slot>)` — no fallback. When the var is
  undefined the property's value is invalid and the rule contributes
  nothing to the cascade; the next-most-specific rule wins. That brings
  the user's customizer-set value back as the actual paint.
- For derived values: keep the formula valid only when its source var is
  defined. `rgba(var(--customify-color-text-rgb), 0.12)` already
  collapses to invalid when `--customify-color-text-rgb` is undefined —
  same fallthrough behaviour.

### 3b — Don't emit override-layer "default fallback" vars from JS

Phase 3 emits eight derived vars (`on-primary`, `text-muted`, …) every
time `applyColorVars()` runs. When no palette is active those vars must
also stay undefined. Easy fix: only run `applyColorVars` when an active
palette exists (already the case for the current code path, since
`getActive()` returns null when `cfg.activeId === ''`).

### 3c — Emit `--legacy-*` from saved theme_mods (`compat layer`)

Mirror the Style Pack v2.0 strategy. New PHP class
`Customify_Preview_Colors_Compat` registered in the bootstrap:

```php
add_action( 'wp_head', static function () {
  $map = [
    '--legacy-primary'       => get_theme_mod( 'global_styling_color_primary' ),
    '--legacy-secondary'     => get_theme_mod( 'global_styling_color_secondary' ),
    '--legacy-text'          => get_theme_mod( 'global_styling_color_text' ),
    '--legacy-link'          => get_theme_mod( 'global_styling_color_link' ),
    '--legacy-link-hover'    => get_theme_mod( 'global_styling_color_link_hover' ),
    '--legacy-heading'       => get_theme_mod( 'global_styling_color_heading' ),
    '--legacy-widget-title'  => get_theme_mod( 'global_styling_color_w_title' ),
    '--legacy-border'        => get_theme_mod( 'global_styling_color_border' ),
    '--legacy-meta'          => get_theme_mod( 'global_styling_color_meta' ),
  ];
  $decls = '';
  foreach ( $map as $name => $hex ) {
    if ( $hex && preg_match( '/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex ) ) {
      $decls .= $name . ':' . $hex . ';';
    }
  }
  if ( $decls ) {
    echo "\n<style id=\"customify-preview-colors-legacy\">:root{" . $decls . "}</style>\n";
  }
}, 2 );
```

Now the override layer can chain palette → legacy → invalid:

```scss
html body {
  color: var(
    --customify-color-text,
    var(--legacy-text)
  );
}

h1, h2, h3, h4, h5, h6 {
  color: var(
    --customify-color-text,
    var(--legacy-heading,
    var(--legacy-text))
  );
}
```

For the auto-computed slots that have no exact legacy counterpart we
fall through to the closest legacy var:

| Override consumer | Fallback chain |
|---|---|
| `body { color }` | `palette text → legacy text` (then unset → SCSS default) |
| `h1..h6 { color }` | `palette text → legacy heading → legacy text` |
| `.widget-title { color }` | `palette text → legacy widget-title → legacy text` |
| `a { color }` | `palette primary → legacy link → legacy primary` |
| `a:hover { color }` | `palette primary-hover → legacy link-hover → legacy primary` |
| meta selectors | `palette text-muted → legacy meta → legacy text` |
| divider borders | `palette border-default → legacy border` |
| primary buttons bg | `palette primary → legacy primary` |
| secondary surfaces | `palette secondary → legacy secondary` |

## 4 — Migration timeline (3 versions over 6–9 months, per Style Pack)

### v2.0 — Dual-track (this release)

Both systems coexist. No data loss for anyone. Concretely:

1. Ship sections 3a / 3b / 3c above.
2. `styling.php` controls remain visible and functional in the
   Customizer.
3. The 6-slot panel is opt-in: a site that never opens the panel writes
   no `customify_preview_user_palettes` / `customify_preview_active_palette`
   options. Visitor render is identical to pre-Phase-3 behaviour because
   the override stylesheet falls through cleanly.
4. Once a user activates a palette, the palette wins for slots it
   covers; legacy controls remain functional for slots they touch and
   the palette doesn't (e.g. `border` if not derived, advanced selectors
   filter-injected by plugins).

**Acceptance criteria**:
- Pre-Phase-3 site (no palette saved, customizer values set) — no
  rendering change after upgrade.
- Pre-Phase-3 site, after activating a palette — palette overrides the
  customizer values for the 9 settings; reverting active id back to
  empty restores the customizer values.

### v2.1 — Migrate button + UX nudge

Add a one-click migration in the Customizer "Global Colors" section
(above the existing controls):

```
┌────────────────────────────────────────────────┐
│  ⓘ  These individual color controls are the    │
│     legacy 9-color system. The new 6-slot     │
│     palette below covers them all and adds    │
│     auto-computed muted / hover / border      │
│     tones.                                    │
│                                                │
│     [ Migrate my colors to the palette → ]   │
└────────────────────────────────────────────────┘
```

The button:

1. Reads the 9 `theme_mod` values.
2. Composes a palette `{ id: 'migrated', name: 'My current colors', colors: { … } }`:
   - `primary` ← `color_primary` (or `color_link` if `color_primary` is default)
   - `secondary` ← `color_secondary`
   - `text` ← `color_text`
   - `surface` ← `#FFFFFF` (no legacy equivalent — sane default)
   - `accent` ← `color_link` if it differs from primary, else first theme preset's accent
   - `base` ← reads `background.php`'s body bg setting if present, else theme preset's base
3. `update_option(OPTION_PALETTES, [...$existing, $migrated])`.
4. `update_option(OPTION_ACTIVE, 'migrated')`.
5. (Optional) ask the user if they want to clear the legacy theme_mods.
   Default: keep them so reverting is trivial.
6. Show admin notice `Your colors have been migrated to the new palette ✓`.

The 9 individual controls stay visible but the description marks them
as legacy and links to a help article.

### v3.0 — Hide legacy controls

Add a feature flag (theme-mod or constant) that hides the 9
`styling.php` controls from the Customizer UI. The compat filter at
v2.0 keeps emitting `--legacy-*` so unmigrated sites continue rendering
via the fallback chain.

The migration button stays in place (now in a more prominent position)
for users discovering the panel for the first time.

### v3.1 — Drop the compat layer

Remove the `styling.php` color settings + the legacy CSS-var emitter +
the fallback chain from `overrides.scss`. The 6-slot palette becomes
the single source of truth. Sites that haven't migrated by now revert
to the SCSS defaults if no palette exists; they're prompted to migrate
on first admin visit (one-time admin notice).

## 5 — Implementation tasks (Phase 6.1 = v2.0)

The v2.0 deliverables that can ship immediately:

| # | File | Change |
|---|---|---|
| 1 | `src/frontend/scss/overrides.scss` | Drop hex fallback from every `var(--customify-color-<slot>)`. Replace with single-arg form. Add `var(--legacy-*)` chain where the slot maps to a styling.php setting (Groups P, S, T, L, LH, H, WT, Border, Meta). Auto-computed companions get `var(--customify-color-X) → var(--legacy-X)` chains where applicable; rgba derivations require both source vars to be defined to remain valid. |
| 2 | `inc/preview-colors/class-preview-colors-compat.php` (NEW) | Render `:root { --legacy-*: <theme_mod_value>; }` block on `wp_head` priority 2. Skip vars whose theme_mod is empty or default-equal so unset values don't accidentally override. |
| 3 | `inc/preview-colors/class-preview-colors.php` | `require_once` the compat class + `Customify_Preview_Colors_Compat::init()` from bootstrap. |
| 4 | `inc/preview-colors/PHASE-6-PLAN.md` (this file) | document the strategy. |

After 6.1 ships:

| Phase | Deliverable |
|---|---|
| 6.2 | Migration button (UX nudge) — Customizer-side + a CLI fallback (`wp customify migrate-colors`). |
| 6.3 | Feature flag to hide legacy controls. Admin notice prompting unmigrated sites. |
| 6.4 | Drop compat layer + legacy controls entirely. |

## 6 — Open questions

| Question | Resolution path |
|---|---|
| Do we map `color_link` → `primary` slot or keep a separate `link` companion? | Plan adopts Style Pack's collapse: `link === primary`. Hover differentiation comes from `primary-hover` auto-computed. Acceptance criteria check: existing sites that distinguished link vs primary will lose that distinction at migration time. Document. |
| `color_meta` → `text-muted` derivation always uses 55% alpha. Existing sites might have set `meta` to a colour HUE different from `text` (e.g. blueish meta on warm body). Migration loses that. | Style Pack accepts collapse. v2.0 keeps both visible so users with bespoke meta colours continue to see their setting until they explicitly migrate. |
| Filter `customify/styling/<setting>-color` extensions add custom selectors. Are those covered by the override fallback chain? | No — those are user-/plugin-specific selectors not in the overrides.scss baseline. Acceptable gap; documented. |
| Do we drop fallback hex from override layer NOW (potentially affecting active panel sites that briefly see SCSS default before JS bootstraps)? | Yes — the brief flash from `unset` to `--legacy-X` to palette is acceptable and matches Style Pack's recommended pattern. The flash only lasts a few ms in practice. |
| Should the migrate button delete the legacy theme_mods after copying? | No by default. Offer as an opt-in checkbox "Also clear the individual color controls". Default to keep — easier rollback. |
