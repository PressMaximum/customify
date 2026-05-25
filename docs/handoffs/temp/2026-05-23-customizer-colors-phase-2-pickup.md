# Customizer Colors — Phase 2 Pickup Handoff

**Branch**: `claude/epic-shamir-e4a2d1` (off DEV)
**Worktree**: `.claude/worktrees/epic-shamir-e4a2d1/`
**Date**: 2026-05-23
**Status**: 8 files uncommitted, Phase 2.1–2.7 done, Phase 2.8+ open

---

## STOP — read these first, in order

The Colors work spans ~15 design decisions, 30K-site safety constraints,
and bundled-SCSS refactors. Skipping any of the following will produce
regressions:

1. **[docs/SPEC-customizer-colors.md](../../SPEC-customizer-colors.md)** —
   full Phase 1 architecture + the §8 Phase 2 progress table. Treat it as
   the canonical state document. Update §8 as you ship items.
2. **[docs/SPEC-customizer.md](../../SPEC-customizer.md)** — the underlying
   config-driven Customizer architecture. Required for any new control or
   section work.
3. **Memory files** at
   `~/.claude/projects/-Users-kientrong-Studio-customify2-wp-content-themes-customify/memory/`:
   - `feedback_30k_sites_color_safety.md` — hard constraint, every change
     must preserve rendered output for sites with saved theme_mods.
   - `project_blocksify_companion.md` — the 6 slot slugs are API surface.
   - `feedback_verify_user_facing_outcome.md` — verify with computed style
     and the test page, not just config inspection.
   - `feedback_language_no_clone_no_competitor.md` — never name competitor
     themes in code, commits, or PR titles. "Clone" is also off-limits in
     chat.
   - `feedback_kit_vs_theme_fix_split.md` — Customify Pro bugs go to
     KIT_ISSUES.md, not the theme.
4. **[CLAUDE.md](../../../CLAUDE.md)** + **[AGENTS.md](../../../../AGENTS.md)**
   — the live-theme rsync rule + the worktree commit discipline.

---

## What the user wants from this work

A Customizer Colors panel where the user picks **6 brand slots**
(base / surface / text / primary / secondary / accent) and **everything
else cascades**. Border, link, heading, body, meta, etc. all derive
from those 6 unless the user explicitly overrides them. The pickers
should show **what will render** (cascade-resolved colors in the swatch
even before save) and the override pickers should sit below the slot
list in collapsed "Legacy fine-tuning" so they don't clutter the
primary flow.

30K production sites are reading the same theme_mod keys today; the
cascade refactor MUST NOT shift a single saved hex for those sites.
Fresh-install shifts are documented design choices the project owner
has signed off on.

---

## What's been shipped this session (uncommitted)

8 files, roughly +540/-90 lines. NO commits yet — the user is reviewing
in customizer.wp.local before any are written.

```
inc/class-customify.php
inc/customizer/configs/colors.php
inc/customizer/configs/layouts.php
inc/customizer/controls/class-control-base.php
inc/colors-palette.php
src/backend/customizer/scss/_control.scss
src/frontend/scss/utils/_vars.scss
theme.json
```

### Phase 2.1 — primary/secondary var() + live preview JS
- `$primary_css` / `$secondary_css` → `var(--customify-primary, {{value}})`.
- Live-preview JS binds 6 slot settings + heading/link/link-hover/body
  override; updates `--customify-<token>` on `document.documentElement`
  inline so cascade fires in real time.
- `customize_register` priority 1000 forces `transport=postMessage`
  on the 4 new slot keys (the legacy two get it via css_format auto-detect).
- Value decode mirrors Customify's `JSON.parse(decodeURI(v))` since
  values arrive URL-encoded JSON in customize preview.

### Phase 2.2 — heading cascade + Customizer infra
- `$heading_css` → `var(--customify-heading, {{value}})`.
- :root cascade decl: `--customify-heading: var(--customify-text, hex)`
  emitted ONLY when no `global_styling_color_heading` override saved
  (uses `get_theme_mods()` saved-only check — `get_theme_mod()` returns
  the field default inside Customize preview which would always look
  like an override).
- Moved the `:root` token block to its own
  `<style id="customify-palette-tokens-inline-css">` printed at
  `wp_head` priority 8 — BEFORE `customify-style-inline-css` (the
  auto-CSS block). Reason: the customize preview iframe's auto-css JS
  wholesale-replaces the customify-style inline on every setting
  change; a separate tag keeps the :root cascade stable for the
  iframe's life.

### Phase 2.3 — body_text direct cascade (no mix)
- Earlier iteration used `mix(text, base, 88%)` ≈ #444444 — desaturated
  the user's Text slot. Reverted to direct `var(--customify-text)`
  chain so e.g. white-on-black actually paints body white.
- Field default aligned with slot.text `#2b2b2b`.

### Phase 2.4 — link/link-hover + Surface + Legacy collapsible + reset icon
- `$link_css` → `var(--customify-link, {{value}})`. :root cascade:
  `--customify-link: var(--customify-primary, hex)`.
- `$link_hover_css` → `var(--customify-link-hover, {{value}})`. Cascade
  via @supports color-mix: `color-mix(in oklab, var(--customify-link) 85%,
  white)` (LIGHTER not darker — project owner's design call).
- Slot.surface default `#FFFFFF` → `#ECECEC` (theme.json palette too).
- Legacy fine-tuning section renamed; the heading is collapsed by
  default. Toggle handler binds to `.customize-control-title` only
  (NOT the LI) so the browser's default focus outline doesn't paint a
  stuck rectangle around the row.
- Reset icon: WP's `.wp-picker-default` input is the underlying widget
  but it's hidden by wp-color-picker.css. SCSS un-hides it (`display:
  none → block !important` when `.customify-input-color.is-dirty`),
  repositions absolute LEFT of the swatch, paints the dashicons-image-
  rotate SVG as `background-image` (input element doesn't render
  `::before`).
- `is-dirty` toggling via closure-scoped `initialValues` map keyed by
  setting id (dataset attributes turned out to be rewritten by Iris).

### Phase 2.5 — Base composite cascade + border/meta/widget_title
- 3 composites (`background`, `site_content_styling`, `content_background`)
  default `bg_color` changed from `#FFFFFF` to `''`. Auto-css's
  `setup_color()` returns false for empty value → skips emit → palette-
  tokens cascade rule (`body { background-color: var(--customify-base, hex) }`)
  is the sole emitter. Saved composite emits literal hex AFTER the
  cascade (later in source order) so override wins by cascade order.
- `$border_css` (18 declarations) → `var(--customify-border, ...)`.
  Field default `#eaecee` → `#e6e6e6` (slot-derived). See Phase 2.6
  for the eventual currentcolor change.
- `$meta_css` → `var(--customify-text-muted, {{value}})`. Field default
  `#6d6d6d` → `#6b6b6b`.
- `$w_title_css` → `var(--customify-widget-title, {{value}})`. Field
  default `#444444` → `#2b2b2b` (slot.text).
- Cascade decls added: `--customify-widget-title: var(--customify-text)`,
  `--customify-body-text: var(--customify-text)`.
- Preview JS payload extended: border/meta/widget-title/body picker
  drags update :root vars live.

### Phase 2.6 — bundled SCSS `$color_*` → CSS var expressions
The bundled stylesheet referenced `$color_text`, `$color_heading`,
`$color_primary`, `$color_secondary`, `$color_link`, `$color_link_hover`,
`$color_border`, `$color_meta` as literal hex values across 65+ rules.
Refactored ALL of them to CSS-var expressions at the SCSS variable
level (one line per token in `src/frontend/scss/utils/_vars.scss`).
Every bundled rule that uses one of those SCSS variables now resolves
through `--customify-<token>` with the legacy hex as fallback. Result:
saved overrides paint the entire theme correctly, no need to keep
chasing per-selector rules.

Border specifically uses `var(--customify-border, color-mix(in srgb,
currentcolor 12%, transparent))` so unsaved sites get an adaptive
border that follows the local text color (visible on both light and
dark surfaces).

### Phase 2.7 — visual cascade swatch sync in picker UI
Override pickers (Link, Heading, Widget-title, Body-text) display
their swatch in the cascade-resolved color when no override is saved.
JS reads source slot value, sets `--customify-cascade-display` CSS
custom property + `.is-cascading` class on the LI; SCSS overrides
wpColorPicker's inline background on `.color-alpha` only when that
class is present. Setting underlying value stays untouched — user
dragging the picker writes a real value and removes the class.

### Misc this session
- **Sidebar 320px** — added then reverted (user reported it broke
  layout elsewhere).
- **Default Sidebar Layout** — global `sidebar_layout` and
  `page_sidebar_layout` defaults changed from `content-sidebar` to
  `content`. 404 already defaults to `content`.
- **Colors section position**: priority 65, sits between Styling (60)
  and Typography (70) inside the General Options group.
- **"Links" group renamed to "Colors"** and Heading override promoted
  out of Legacy fine-tuning into it. New group contains Link, Link
  hover, Heading.
- **Menu Location link** fix: added inline `.focus-section` /
  `.focus-control` delegated handlers (NOT a full `builder.js`
  enqueue, which throws on customize_register because of layout-
  builder code that expects header builder panel context).

---

## Cascade architecture cheat sheet

```
slots (direct user pick):
  base, surface, text, primary, secondary, accent

derived (pure var() chain — modern browsers cascade live):
  --customify-heading      = var(--customify-text, hex)
  --customify-widget-title = var(--customify-text, hex)
  --customify-body-text    = var(--customify-text, hex)
  --customify-link         = var(--customify-primary, hex)

derived (color-mix — @supports oklab):
  --customify-text-muted   = color-mix(in oklab, var(--text) 70%, var(--base))
  --customify-primary-hover= color-mix(in oklab, var(--primary), black 10%)
  --customify-link-hover   = color-mix(in oklab, var(--link) 85%, white)

derived (special):
  --customify-border       = NOT emitted when unsaved
                              → CSS rule fallback: color-mix(currentcolor 12%, transparent)
  --customify-on-primary/secondary/accent
                           = PHP-precomputed WCAG luminance pick
                              (does NOT live-update on slot drag — deferred)

override semantics (legacy 7 keys still respected):
  global_styling_color_text       → --customify-body-text
  global_styling_color_meta       → --customify-text-muted
  global_styling_color_border     → --customify-border
  global_styling_color_heading    → --customify-heading
  global_styling_color_w_title    → --customify-widget-title
  global_styling_color_link       → --customify-link
  global_styling_color_link_hover → --customify-link-hover

composite override semantics (3 Background fields):
  background.normal.bg_color           → body bg
  site_content_styling.normal.bg_color → .site-content .content-area bg
  content_background.normal.bg_color   → .site-content bg

  All 3 cascade to --customify-base when bg_color is empty (composite
  default). Saved composite emits its literal hex via the auto-css
  pipeline (loads AFTER palette-tokens), wins by source order.
```

---

## File responsibility map

| File | What it does |
|---|---|
| `inc/colors-palette.php` | `:root` token builder (PHP), live preview JS, cascade swatch sync JS, reset-icon dirty-state JS, Legacy collapsible JS, force `postMessage` on 4 new slot keys at `customize_register` priority 1000. **This file does a lot — read it end to end.** |
| `inc/customizer/configs/colors.php` | Config array for the Colors section: 6 slot pickers, "Colors" group (link / link-hover / heading), 3 Background composite pickers, "Legacy fine-tuning" collapsible group with 4 remaining overrides (body text, border, meta, widget title). Every `css_format` uses `var(--customify-X, {{value}})`. |
| `inc/class-customify.php` | Two relevant edits: `print_palette_tokens()` printed at `wp_head` priority 8; composite `default bg_color = ''` no longer needs special handling here because auto-css drops empty values. |
| `inc/customizer/configs/layouts.php` | `sidebar_layout` + `page_sidebar_layout` defaults changed to `content`. |
| `inc/customizer/controls/class-control-base.php` | Inline `.focus-section` + `.focus-control` delegated handlers (replacement for the broken `builder.js` enqueue). |
| `src/frontend/scss/utils/_vars.scss` | All 8 `$color_*` SCSS variables changed to CSS-var expressions with legacy-hex fallback. |
| `src/backend/customizer/scss/_control.scss` | Picker UI: round swatches, popover layout, Iris vertical strips, reset icon SVG, Legacy collapsible chevron, `.is-cascading` swatch override. |
| `theme.json` | Surface palette default `#FFFFFF` → `#ECECEC`. |

---

## Deferred / open items (Phase 2.8+)

In rough priority order:

1. **Iris picker UX overhaul** — user wants saturation box and the two
   hue/alpha strips all full-width (stacked vertically, like
   modern color pickers). Iris doesn't use jQuery UI slider widget —
   it has its own drag math reading inline `offsetTop`. CSS rotate
   trick breaks the drag handler. Options: patch Iris source (risky
   across WP updates), replace Iris with a custom widget (large
   effort), or accept Iris vertical strips. Owner picked: defer until
   discussed.

2. **Iris initial colorful state** — when the current value is
   grayscale (e.g. default text `#2b2b2b`), Iris's saturation square
   shows white→black gradient because hue is undefined for
   grayscale. Owner wants a colorful initial state for easier picking.
   Approach unclear; defer until UX direction.

3. **Surface wiring** — `--customify-surface` is in :root but no
   bundled rule consumes it yet. Need to identify card / widget /
   comment box / modal selectors and wire `background-color:
   var(--customify-surface)`.

4. **WCAG `--on-*` live preview** — currently PHP-precomputed only;
   dragging Primary doesn't refresh `--customify-on-primary` in the
   iframe. Mirror `customify_color_pick_on()` luminance math in JS so
   the contrast token updates live.

5. **Read-only derived preview chips in the Palette section** — small
   row of chips showing how text-muted / border / link-hover / on-*
   currently resolve, so the user sees the cascade output before
   picking.

6. **`.site-title`** (header logo) uses the separate "header skin"
   system (`$light_color_link_hover` / `$dark_color_link_hover`) and
   does NOT follow slot.text. Out of Colors panel scope today —
   discuss whether to make header-skin pull from slot.text too.

7. **Heading clear mid-session quirk** — clearing an override picker
   without saving leaves the bundled `_skins.scss` color winning until
   page reload (auto-css drops the empty value rule). Cosmetic, post-
   save it's fine.

8. **Customify Pro megamenu `changeId` TypeError** — fires on every
   `customize_register`. Out of theme scope per
   `feedback_kit_vs_theme_fix_split.md` — note it in `KIT_ISSUES.md`
   if there isn't an entry already.

---

## Commit strategy

Owner has been reviewing live, no commits yet. When ready, suggested
split (smallest blast radius first):

1. `chore(layouts): default Sidebar Layout = content (no sidebar)`
2. `feat(customizer-colors): primary + secondary var() refactor + live preview` (Phase 2.1)
3. `feat(customizer-colors): heading + body + widget + link cascades` (Phase 2.2–2.4)
4. `feat(customizer-colors): Base composite cascade + border/meta refactor` (Phase 2.5)
5. `feat(customizer-colors): bundled SCSS $color_* → CSS vars` (Phase 2.6)
6. `feat(customizer-colors): visual cascade swatch sync` (Phase 2.7)
7. `feat(customizer-colors): Legacy fine-tuning collapsible + reset icon UI`
8. `docs(customizer-colors): update SPEC §8 with Phase 2 progress`

OR one big `feat(customizer-colors): Phase 2 — slot-driven cascade for
heading, link, body, border, meta, widget, background composites + UI
polish` if owner prefers a single landing commit.

---

## Pre-flight checklist before any code change

1. Read this handoff end-to-end.
2. Read the SPEC sections you're touching.
3. Refresh the Customizer at
   `http://customify2.wp.local/wp-admin/customize.php` to feel the
   current state. NO commits yet — `git log` ends at `5d336555`.
4. Use `./bin/sync` after every edit (rsync + cache flush). Don't
   commit on the live theme dir.
5. Run the A/B/C compare for every change touching `:root` or any
   `css_format`. Helpers live in `/tmp/`:
   ```bash
   /tmp/capture_color_css.sh <name> <out.css>
   python3 /tmp/compare_color_css.py /tmp/baseline.css /tmp/after.css
   ```
6. Verify the test page renders all elements:
   `http://customify2.wp.local/color-test-page/` (post id 98). It
   includes h1-h6, paragraph, blockquote, list, table, button,
   Recent Posts blocks, and a swatch grid showing every token.

---

## Useful single-shot commands

```bash
# Where am I?
cd /Users/kientrong/Studio/customify2/wp-content/themes/customify/.claude/worktrees/epic-shamir-e4a2d1
git status -s
git log --oneline -5

# Sync changes to the live theme + flush cache (idempotent)
./bin/sync              # rsync only
./bin/sync -b           # build + rsync

# Reset all color theme_mods (for Scenario A baseline)
for k in global_styling_color_primary global_styling_color_secondary global_styling_color_text \
         global_styling_color_link global_styling_color_link_hover global_styling_color_border \
         global_styling_color_meta global_styling_color_heading global_styling_color_w_title \
         customify_palette_text customify_palette_base customify_palette_surface customify_palette_accent \
         background site_content_styling content_background; do
  studio wp --path /Users/kientrong/Studio/customify2 theme mod remove $k 2>/dev/null
done
studio wp --path /Users/kientrong/Studio/customify2 cache flush

# Quick PHP sanity check (eval — does the function still produce CSS?)
studio wp --path /Users/kientrong/Studio/customify2 eval '
  require_once get_template_directory()."/inc/colors-palette.php";
  $css = customify_color_palette_root_css();
  echo "Length: ".strlen($css)."\n";
  echo "Has heading cascade: ".(strpos($css,"--customify-heading: var(")!==false?"yes":"no")."\n";
  echo "Has body cascade: ".(strpos($css,"--customify-body-text: var(")!==false?"yes":"no")."\n";
  echo "Has link cascade: ".(strpos($css,"--customify-link: var(")!==false?"yes":"no")."\n";'
```

---

## Known-good test scenarios (snapshots in /tmp/)

| Scenario | wp-cli setup | Expected diff vs baseline |
|---|---|---|
| **A** — Defaults | no theme_mods | 26 HEX DIFFS + 1 ADDED `.site-content` cascade rule. All intentional Phase 2 shifts (body/border/meta/widget/link/link-hover/heading aligning to slot values). |
| **B** — All 9 legacy keys saved | full hex set | Only `--customify-surface` shifts (#FFFFFF → #ECECEC). All 9 saved values preserved on their selectors. PASS. |
| **C** — Partial (primary + heading saved) | 2 keys | Same as A for unsaved fields. PASS for saved keys. |

If a refactor produces a NEW diff, decide whether it's intentional
(document in commit message + SPEC §8) or a regression (fix and re-
run).

---

## Visual verification protocol

Per `feedback_verify_user_facing_outcome.md`:
DO NOT report "PASS" based on PHP function output or curl-grep matches
alone. Open the Customizer and the test page, drag the relevant slot,
verify with:
- `getComputedStyle(el).color` / `.backgroundColor` / `.borderLeftColor`
- The actual visible swatch in the picker
- The `--customify-X` value on `document.documentElement`

Chrome MCP commands used this session:
```js
// In customize controls pane:
wp.customize('customify_palette_text').set('#FFFFFF');

// In iframe (preview):
var doc = document.querySelector('#customize-preview iframe').contentDocument;
getComputedStyle(doc.documentElement).getPropertyValue('--customify-heading');
getComputedStyle(doc.querySelector('article h2')).color;
```

---

## "Don't do this" notes from this session

- DON'T enqueue the full `builder.js` — it has v1 layout-builder code
  that throws on customize_register. Use the inline `.focus-section`
  handlers instead (`class-control-base.php`).
- DON'T use `transform: scaleX(-1)` to flip the reset icon — it
  swaps the arrow direction but the icon shape now points the wrong
  way. Use the dashicons-image-rotate SVG path verbatim.
- DON'T rely on `input.defaultValue` for "is dirty" detection — Iris
  rewrites it on every set. Use a closure-scoped `initialValues` map
  keyed by setting id.
- DON'T put new CSS rules in `customify-style-inline-css` if they
  must survive customize-preview JS regeneration. Use the
  `customify-palette-tokens-inline-css` block (`wp_head` priority 8).
- DON'T put the click target for the collapsible heading on the LI —
  the browser paints a stuck focus outline across the entire row.
  Use the `.customize-control-title` span.
- DON'T touch the project's bundled `style-theme.min.css` directly;
  rebuild from SCSS with `npm run build` then rsync via `./bin/sync`.
