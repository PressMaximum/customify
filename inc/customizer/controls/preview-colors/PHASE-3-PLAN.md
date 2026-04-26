# Preview Colors — Phase 3 Plan

Apply the **Style Pack 6-slot color system** to the Customify Preview Colors module. Reconcile what is already shipping with the doctrine in the Customify Style Pack discussion, fill the gaps, and document slot usage so future work (demo packs, theme.json bridge, frontend switcher) lands consistently.

Status: planning only — no code yet.

Source reference: <https://wpassets.kienpc81089.workers.dev/Customify-Style-Pack/>

## Doctrine recap (from the Style Pack)

The Style Pack collapses the legacy 9-slot Customify palette into **6 user-picked slots** plus a small set of **auto-computed vars** the theme derives. Users only ever pick 6 colours; the rest of the design tokens flow from those 6.

### The 6 slots

| Slot | Role | Used for | Frequency |
|---|---|---|---|
| `base` | Page canvas / default neutral | `<body>`, neutral sections, form wrappers | 100% of demos |
| `text` | Ink foreground | Body, headings, labels, nav links at rest | 100% |
| `primary` | Brand action / CTA | Primary button, link, focus ring, accent word, active tab | 100% |
| `surface` | Elevated container | Cards, modals, dropdowns, tooltips | 95% (skip on full-bleed editorial) |
| `secondary` | Dark sectional band | Dark callout, footer, header dark variant, secondary button | 60% (editorial / e-comm; skip minimal SaaS) |
| `accent` | Decorative pop | Badge, sticky note, stat number, hover glow, sale tag | 40% (editorial / playful; skip corporate / minimal) |

**4 mandatory + 2 optional.** Demos that don't need `secondary`/`accent` should be able to set them to `null` so the panel hides them — keeps the user-facing palette to 4 squares and avoids over-decision.

### Auto-computed derivatives (theme provides, user never picks)

| Computed var | Source | Method |
|---|---|---|
| `on-primary` | `primary` | JS WCAG luminance → pick `#1A1A1A` or `#FFFFFF` |
| `on-secondary` | `secondary` | Same as `on-primary` |
| `on-surface` | `surface` | Same as `on-primary` |
| `primary-hover` | `primary` | `color-mix(in srgb, primary, #000 15%)` |
| `primary-subtle` | `primary` | `color-mix(primary, base 92%)` (very light wash) |
| `text-muted` | `text` | `rgba(text-rgb, 0.55)` |
| `text-subtle` | `text` | `rgba(text-rgb, 0.35)` |
| `border-default` | `text` | `rgba(text-rgb, 0.12)` |

`success` / `warning` / `danger` / `info` stay fixed-hue — not brand decisions, the theme ships its own.

### Slot usage matrix (Style Pack reference)

| Demo type | base | text | primary | surface | secondary | accent |
|---|:--:|:--:|:--:|:--:|:--:|:--:|
| SaaS / landing minimal | ✅ | ✅ | ✅ | ✅ | — | — |
| Agency / portfolio | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Dashboard / admin | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| E-commerce | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editorial / magazine | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Blog / personal | ✅ | ✅ | ✅ | ✅ | — | ✅ |
| Restaurant / café | ✅ | ✅ | ✅ | ✅ | — | ✅ |
| Medical / legal (formal) | ✅ | ✅ | ✅ | ✅ | ✅ | — |

### Do / Don't

- **`base`** — ✅ page, neutral section. ❌ card (use `surface`), button, text.
- **`text`** — ✅ body, heading, label. ❌ meta/caption (use `text-muted`), disabled (`text-subtle`).
- **`primary`** — ✅ one CTA per hero, link, accent word. ❌ large bg, body text, default border.
- **`surface`** — ✅ card, modal, tooltip. ❌ page bg, CTA, text.
- **`secondary`** — ✅ dark band, footer, secondary button. ❌ primary action, page bg.
- **`accent`** — ✅ badge, note, stat, icon. ❌ body text, CTA, default border, large bg.

## Current state vs Style Pack

What we already ship (Phase 1 + 2):

| Feature | Status |
|---|---|
| 6 user-picked slots match Style Pack vocabulary | ✅ same names |
| Slot UI: pick / preview, save palette to options | ✅ both panels (frontend overlay + Customizer control) |
| Render `<style>:root{ --customify-color-<slot>: … }</style>` for visitors | ✅ `output_root_vars()` |
| RGB triplet vars for rgba composition | ✅ `--customify-color-<slot>-rgb` |
| Bridge to Gutenberg `.has-<slug>-color` via `--wp--preset--color--<slug>` | ✅ in `overrides.scss` |
| Border derived from `text @ 12%` | ✅ already implemented as `rgba(text-rgb, 0.12)` |
| Shadow derived from `text @ 8%` | ✅ for the two color-tinted theme shadows |
| **`on-primary` / `on-secondary` / `on-surface` companions** | ❌ button text + footer text use literal `#fff` |
| **JS luminance to flip `on-*` between black / white** | ❌ |
| **`text-muted`, `text-subtle`** | ❌ meta uses direct `text` slot (no opacity reduction) |
| **`primary-hover`** | ❌ link hover uses direct `primary` slot |
| **`primary-subtle`** | ❌ |
| Demo type → slot exposure (allow `null` slots) | ❌ all 6 always rendered |
| Style variations / theme.json bridge for block themes | ❌ |
| Demo-import preload palette data | ❌ |
| Frontend switcher modal | ❌ (panel itself can switch but is admin-only) |

## Phase 3 — fill the auto-computed companions

The biggest gap is the **auto-computed layer**. The Style Pack specifically calls this out as the bit "user không thấy, theme lo" — picking 6 colours should be enough; the theme expands them into a richer token set without exposing more knobs.

### 3.1 — Add JS luminance helper + emit `on-*` companions

In both panel JS bundles (`src/preview-colors/preview-colors.js` and `src/preview-colors/customizer/customizer.js`), extend `applyColorVars()` so it also writes:

```
--customify-color-on-primary
--customify-color-on-secondary
--customify-color-on-surface
```

Compute via WCAG luminance (matching the Style Pack's reference impl):

```js
function luminance([r, g, b]) {
  const f = v => {
    v /= 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  };
  return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
}
function pickOn(hex) {
  return luminance(hexToRgbArray(hex)) > 0.45 ? '#1A1A1A' : '#FFFFFF';
}
```

Threshold `0.45` (slightly nudged from WCAG default `0.5`) reads better on warm tones (terracotta, amber, etc.) per the Style Pack note.

PHP-side `output_root_vars()` mirrors the same compute so the server renders matching values for visitors who never have JS run for the panel.

### 3.2 — Emit derived text + border + hover vars

Add the rest of the Style Pack's auto-computed table:

```
--customify-color-primary-hover    /* color-mix(primary, #000 15%) — fallback hex per palette */
--customify-color-primary-subtle   /* color-mix(primary, base 92%) */
--customify-color-text-muted       /* rgba(var(--customify-color-text-rgb), 0.55) */
--customify-color-text-subtle      /* rgba(var(--customify-color-text-rgb), 0.35) */
--customify-color-border-default   /* rgba(var(--customify-color-text-rgb), 0.12) */
```

`text-muted`, `text-subtle`, `border-default` are pure RGB-triplet rules → emit unconditionally, work everywhere.

`primary-hover`, `primary-subtle` use `color-mix(in srgb, …)` → require Chrome 111+ / FF 113+ / Safari 16.2+. Phase 3 acceptable target. Fallback hex baked into the rule.

### 3.3 — Migrate the override stylesheet to consume the new vars

Replace literal companions in `src/frontend/scss/overrides.scss`:

| Current rule | Phase 3 rewrite |
|---|---|
| `body:not(.fl-builder-edit) .button { … color: #ffffff; }` | `… color: var(--customify-color-on-primary, #ffffff); }` |
| `.woocommerce-store-notice { … color: #ffffff; }` | `… color: var(--customify-color-on-primary, #ffffff); }` |
| `.woocommerce nav.woocommerce-pagination ul li span.current { … color: #ffffff; }` | `… color: var(--customify-color-on-secondary, #ffffff); }` |
| `.site-footer { color: var(--customify-color-base, #fff); }` | `… color: var(--customify-color-on-secondary, #fff); }` (footer is on `secondary` slot, not `base`) |
| `mark, ins { color: var(--customify-color-text, #1A3A28); }` | leave on `text` (mark sits on `accent` bg which the user is meant to keep light per Style Pack convention) |
| `.pagination .nav-links > *, .link-meta, .color-meta { color: var(--customify-color-text, #6d6d6d); }` | `… color: var(--customify-color-text-muted, #6d6d6d); }` |
| (group "Border" block) `border-color: rgba(var(--customify-color-text-rgb, …), 0.12)` | `border-color: var(--customify-color-border-default, rgba(0,0,0,0.12)); }` |
| `a:hover, a:focus { color: var(--customify-color-primary, #111111); }` | `… color: var(--customify-color-primary-hover, #111111); }` |

These reads cleaner in DevTools and match the Style Pack vocabulary.

### 3.4 — Bridge to `--wp--preset--color--*` for the new computed slots

Block editor's preset CSS vars currently mirror the 6 user-picked slots. Phase 3 also ships:

```
:root {
  --wp--preset--color--on-primary: var(--customify-color-on-primary);
  --wp--preset--color--on-secondary: var(--customify-color-on-secondary);
  --wp--preset--color--text-muted: var(--customify-color-text-muted);
  /* etc. */
}
```

Whether to register matching `editor-color-palette` entries (`{slug:'on-primary', name:'Text on primary', color:'#fff'}`) is a UX call — they pollute the block colour picker. Recommendation: **don't register them in the editor palette**, only ship the CSS vars so theme/template SCSS can use them. Block authors reach for `primary` not `on-primary`.

## Phase 4 — slot-exposure rules

Style Pack flags `secondary` and `accent` as optional. Demos that don't use them should be able to declare them `null` and the panel should hide them.

### 4.1 — Theme-side slot config

Add a filter / theme-mod that lets a demo (or theme.json variation) declare which slots are active:

```php
add_filter('customify_preview_colors/active_slots', function () {
  return ['base', 'text', 'primary', 'surface']; // SaaS-minimal demo
});
```

`Customify_Preview_Colors_Config::SLOTS` becomes filterable; `slot_descriptions()` and `settings_rows()` already return arrays so they pick up filtered slot names automatically.

### 4.2 — Panel JS hides hidden slots

Panel JS reads the active-slot list from `wp_localize_script` and skips deck cards / setting rows / popover entries for slots that aren't active. Existing palettes with values for skipped slots keep them on disk (no destructive write); they reappear if a theme switch re-enables the slot.

### 4.3 — Demo-import preload (Style Pack §"Demo import")

When the user imports a demo, the theme also injects:

- A pre-built `customify_preview_user_palettes` set (5 palettes: original + 4 alternates that match the demo's mood)
- A pre-set `customify_preview_active_palette` id

Hooked from the theme's existing demo-import handler (`customify/demo_imported` action) so it runs after content import.

## Phase 5 — broader integration (deferred / nice-to-have)

These aren't hard blockers for Phase 3 but the Style Pack discussion lays them out explicitly:

| Feature | Pickup point |
|---|---|
| Frontend switcher modal | Add a small modal anywhere on the live site (gated by `manage_options`) → POSTs to a small REST endpoint → reload. Reuses our AJAX handlers; trivial. |
| Deep-link `?palette=<slug>` after demo install | Hook into the demo-import success action; read query param; set active palette before the user lands on admin. |
| Customizer postMessage transport for live preview | Phase 3.x — currently the Customizer setting uses `transport: 'refresh'`. Switching to `postMessage` would update the preview iframe without reload (panel JS already updates `:root` inline; just need a postMessage bridge). |
| Block editor palette injection (`enqueue_block_editor_assets`) | Mirror `output_root_vars()` into the editor iframe so `.has-X-color` swatches in the block editor reflect the active palette without saving + reloading. |
| theme.json color presets | Block themes can read `customify_active_palette` at runtime via a small `get_theme_file_path( 'theme.json' )` rewrite — out of scope for Phase 3. |

## Risks & open questions

| Question | Decision needed |
|---|---|
| `on-*` literal threshold (0.45 vs 0.5)? | Plan adopts `0.45` (Style Pack value). Verify on warm-mid palettes (#F5DE9A butter, #FF7A45 orange) during 3.1 implementation. |
| Persist `on-*` to options or recompute every render? | **Recompute server-side in `output_root_vars()` PHP each request.** Skip persistence — keeps option payload small and adapts when slot defs change. JS panel uses the same algo for symmetry. |
| `color-mix` browser support for `primary-hover` / `primary-subtle`? | Plan accepts Chrome 111+ / FF 113+ / Safari 16.2+. Fallback hex is the Customizer setting's own default so older browsers see today's behaviour. |
| Does `border-default` replace the inline `rgba(text-rgb, 0.12)` calls in `overrides.scss`? | **Yes** — single source of truth. Migration is mechanical. |
| Should the panel UI display the auto-computed swatches? | Recommendation: yes, in a separate "Auto-computed (read-only)" group at the bottom of the panel — patterned chips with a small indicator that they aren't user-editable. Style Pack §"Panel legend giờ có 2 group". |

## Phasing

| Phase | Deliverable |
|---|---|
| **3.1** | JS luminance helper + emit `on-primary` / `on-secondary` / `on-surface` from both panel bundles. PHP `output_root_vars()` mirrors. Override stylesheet swaps literal `#fff` button/footer text for `var(--customify-color-on-*, #fff)`. |
| **3.2** | Emit `text-muted`, `text-subtle`, `border-default`, `primary-hover`, `primary-subtle`. Override stylesheet migrates the meta block + link-hover + (eventually) divider borders to consume vars. |
| **3.3** | Mirror computed vars onto `--wp--preset--color--*` so block editor + frontend block CSS pick them up consistently. |
| **3.4** | Read-only "Auto-computed" group at the bottom of the panel UI for both bundles. |
| **4.1** | `customify_preview_colors/active_slots` filter; PHP + JS skip hidden slots. |
| **4.2** | Demo import preload of `customify_preview_user_palettes` + `customify_preview_active_palette`. |
| **5.x** | Frontend switcher modal, deep-link param, postMessage transport, block editor injection. |

## Out of scope (this plan)

- Typography slots (Style Pack mentions a parallel pattern but typography is its own module).
- Gradients (same).
- `success` / `warning` / `danger` / `info` semantic colours — not brand decisions; theme ships fixed values.
- theme.json variations bridge for block themes (Customify is classic + block hybrid; phase 3 favours classic-side options).
