# Handoff — Customify: editor fonts, Customizer-preview CSS, palette-loss guard

**For**: a fresh Customify product session. Three independent theme bugs, all found by the PressMaximum **Templator** template-factory (AI builds demo sites on Customify + Blocksify; symptoms reproduced on `barber-demo-3.wp.local`, 2026-07-08). Every one affects **real end customers**, not just the factory — any Customify site hits them.

**Status**: root-caused, not yet fixed. File:line refs below are against the barber site's copy `wp-content/themes/customify/` (== this repo's theme). Verify line numbers drift before editing.

**Priority order**: #1 typography (buyers see wrong fonts in the editor — most visible), #2 palette-loss guard (silent data loss), #3 Customizer-preview CSS.

---

## Bug 1 — Block editor renders fallback fonts, not the site fonts

**Symptom.** Site typography is set via Customizer (theme_mods: `global_typography_base_heading` = e.g. Oswald, body = IBM Plex Sans). Frontend renders correctly. But in the **block editor canvas**, headings/body fall back to a default font — the buyer edits in the wrong typeface.

**Root cause (two compounding gaps).**
1. **`theme.json` declares zero `settings.typography.fontFamilies`** — only `fontSizes`/`customFontSize` (`theme.json:73-108`). So WordPress never auto-prints an `@font-face` into the editor iframe, and the Customizer font picker / the theme's own `Customify_Customizer_Theme_Fonts` bridge (`inc/customizer/class-customizer-font-loader.php`) never light up for these families.
2. **The editor typography CSS uses bare `body`/`h1..h6` selectors and enqueues the font `<link>` into the wrong document.** `Customify_Editor::load_style()` correctly emits `--customify-typo-*-font-family` vars + `body{font-family:var(...)}` / `h1..h6{...}` — but bare `body`/`h1` are out-specified by WP core's `.editor-styles-wrapper` typography, so the family loses the cascade. The sibling `css()` method already re-scopes colors/background to `.editor-styles-wrapper` for exactly this reason (`inc/admin/editor.php:60-76, 143-154`) — the typography path never got that scoping (`inc/admin/editor.php:337-343, 379-399`; config selectors at `inc/customizer/configs/typography.php:200-221`). Separately, `Customify_Editor::assets()` enqueues the Google-Fonts `<link>` on `enqueue_block_editor_assets` (`inc/admin/editor.php:21, 31-38`) — WP 6.5+ renders the canvas in an **iframe**, and only `enqueue_block_assets` / `block_editor_settings_all['styles']` reach inside it, so the font file is never downloaded in the iframe. (Same outer-shell-vs-iframe hazard Blocksify documents at `blocksify.php:258-284`.)

**Fix (do both).**
- **Scope the typography rules to `.editor-styles-wrapper`** in `Customify_Editor::load_style()` (put `--customify-typo-*` vars on `.editor-styles-wrapper`, and emit `.editor-styles-wrapper, .editor-styles-wrapper h1, … h6 { font-family: … }`), mirroring the color/background re-scoping already at `editor.php:60-76`.
- **Get the font FILE into the iframe.** Preferred WP-native route: **add `settings.typography.fontFamilies` (Oswald + IBM Plex Sans, with `fontFace` src or Google URLs) to `theme.json`.** This (a) makes WP auto-emit `@font-face` into both the editor iframe and the frontend, and (b) activates the existing `Customify_Customizer_Theme_Fonts` bridge + the Customizer picker. Alternatively move the editor font `<link>` to `enqueue_block_assets` (admin-guarded) so it loads inside the iframe — but the theme.json route is preferred.
  - Factory note: demos use arbitrary Google fonts per template. Consider making the theme register whatever fonts the active typography theme_mods reference into the editor iframe dynamically (not just a static theme.json list), so every demo's fonts show in the editor without per-demo theme.json edits.

**Verify.** Open a page in the editor → headings render in the heading font (not fallback); `document.fonts` inside the canvas iframe lists the families; the `.editor-styles-wrapper h1` computed `font-family` is the site font.

---

## Bug 2 — Seeded/changed brand colors are silently lost when a preset palette is clicked

**Symptom.** Customify's Colors panel = "pick 6 brand colors, theme derives the rest" (slots Primary/Secondary/Accent/Text/Surface/Base; "Sunrise"/"Midnight" are read-only THEME presets). When the 6 slots are set to brand values that don't byte-match a preset (which is what happens whenever **anyone — an AI, an importer, or a user — edits the slot colors**), the panel shows a **"Modified — diverges from Sunrise" + "Save as new"** strip, and the colors sit as loose slot values not bound to any saved palette. **Clicking any preset card overwrites all six slots → the brand colors are gone**, with only that passive strip as warning.

**Why it matters / who hits it.** The Templator factory seeds colors programmatically and hits this (worked around factory-side by also writing `customify_color_palettes` + `customify_active_palette`). But the **theme itself should make color changes safe** so any AI tool or hand-editing customer doesn't lose colors. This is the owner's explicit ask: *"những session khác dùng AI để change color kiểu này cũng bị — có hướng xử lí ở theme không?"*

**Root cause.** Palettes live in `theme_mods_customify`: 6 slot keys + `customify_color_palettes` (JSON array of `{id,name,slots}`) + `customify_active_palette` (id). Presets are hardcoded (`inc/color-palette-switcher.php:34-67`). When slots diverge and there's no active *custom* palette, `render()` shows the "diverges" strip (`color-palette-switcher.php:454-479, 506-512`) but leaves the colors unbound; `cardActivate` overwrites all slots with the clicked preset (the data-loss path, `~690-701`). The notice is **passive** — nothing prevents the destructive click.

**Fix (theme-level hardening — pick one or both).**
1. **Auto-persist diverged colors (recommended).** On Customizer load (`render()`), if the slots diverge from all presets AND there is no active custom palette, auto-create an `customify_color_palettes` entry (id `user-auto-…`, name e.g. "Custom colors") from the current slots and set `customify_active_palette` to it. Now the colors are a real saved card — clicking a preset switches *away* but the custom card remains one click to restore; nothing is silently lost. (Storage shape ref: sanitizer `color-palette-switcher.php:94-158`; getCustoms/getActive `284-290`.)
2. **Confirm-on-switch guard.** In `cardActivate` (`~690-701`), if the current colors are diverged/unsaved, show a confirm ("You have unsaved brand colors — Save as a palette first?") with Save/Discard before overwriting the slots.

**Verify.** Set 6 arbitrary slot colors → reload Customizer: a named custom palette appears active (option 1); or clicking a preset prompts before discarding (option 2). Click Sunrise then back → brand colors restore, not lost.

---

## Bug 3 — Customizer preview loses page body styling on header-builder / palette change

**Symptom.** In `/wp-admin/customize.php`, the preview loads styled, but the moment the user opens the **Header builder** or **changes the color palette**, the page **body** loses its background/section styling and renders unstyled (white/grey), while the header still looks right.

**Root cause.** Header-builder + color settings use `postMessage` + **selective-refresh partials** (colors: `inc/customizer/configs/colors.php:427-503`; builder partials + transport: `inc/customizer/class-customizer.php:1266-1354`). A WP selective/partial refresh re-renders only the matched partial's HTML — it does **not** re-run `wp_enqueue_scripts`/`wp_head`, so page-level inline `<style>` blocks are neither preserved (if inside a replaced container) nor re-emitted. The casualties are the **Customify auto-CSS `body{background}` block** (emitted only on `wp_enqueue_scripts` pri 95, `inc/class-customify.php:436-459`) and the **Blocksify per-instance CSS** (see the Blocksify handoff). The theme only wired `customize_preview_init` for **fonts** (`class-customizer.php:55-62`), never for the auto-CSS/body-background.

**Fix (Customify side).** Emit the auto-CSS `body`/background block as a **standalone `<style id="customify-auto-css">` in `<head>`/footer, outside any builder/selective-refresh container**, and register a `customize_preview_init` re-render so a header/footer/palette partial swap can't strip it — extend the pattern the palette partial already proves (`colors.php:493-502` re-renders `#customify-palette-tokens-inline-css` with `container_inclusive => false`) to also cover the auto-CSS/body-background. Ensure content-wrapping partials use `container_inclusive => false` and never wrap the page's inline `<style>`. (Blocksify must do the matching fix for its dynamic CSS — separate handoff.)

**Verify.** In the Customizer, open Header builder / nudge the palette → page body keeps its dark backgrounds and section styling (no unstyled flash).

---

## Blast radius & references
All three affect **every Customify site**, not just factory demos. Fixing them in the theme is high-leverage: it improves the shipped product and unblocks ~150 AI-built templates at once.

Dev setup + rituals: this repo's `CLAUDE.md` / `AGENTS.md` / `STUDIO.md` (Studio site, `studio wp`, build/sync). Key files: `theme.json`, `inc/admin/editor.php`, `inc/class-customify.php`, `inc/customizer/class-customizer.php`, `inc/customizer/configs/{colors,typography}.php`, `inc/color-palette-switcher.php`, `inc/customizer/class-customizer-font-loader.php`.

Source of report: Templator template-factory audit, 2026-07-08 (barber-demo-3). Cross-product dependency: Bug 3 needs the Blocksify dynamic-CSS fix too — see Blocksify `docs/handoffs/HANDOFF-customizer-preview-dynamic-css.md`.
