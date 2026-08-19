# SPEC — Customify Improvements Tracker

**Living to-do list.** Issues found during daily work on other PressMaximum products
(or user reports) land here with evidence + a proposed fix, and get checked off when a
PR ships them. Newest entries at the top of §1. Keep each entry self-contained: a
future session should be able to pick up any unchecked item from this file alone.

Entry format:

```
## N. <short title> (found YYYY-MM-DD)
Problem — what breaks, with file:line evidence.
Fix — proposed change(s), ranked by effort/risk.
- [ ] to-do items
```

---

## 1. Global button CSS — specificity blast radius breaks plugin UIs (found 2026-07-06)

### Problem

The theme styles buttons via bare element selectors guarded by `:not()` blocklists:

- Static: `src/frontend/scss/base/_forms.scss:152-165` (shape) and `:232-246` (color) —
  `button:not(.components-button, .customize-partial-edit-shortcut-button,
  .lightbox-trigger, [class*="wp-block-"], [class*="wc-block-"])` + the same pattern for
  `input[type="submit"|"button"|"reset"]`, `.button`, `.wp-element-button`.
- Customizer-generated CSS mirrors the same selector list: `$button_selector` in
  `inc/customizer/configs/buttons-forms.php:66-72`, emitted verbatim by
  `Customify_Customizer_Auto_CSS::styling()`
  (`inc/customizer/class-customizer-auto-css.php:771-797`).

Two structural flaws:

1. **The `:not()` guard raises specificity above plugin selectors.** Per the Selectors
   spec, `:not()` contributes the specificity of its **most specific argument** — the
   `[class*="wp-block-"]` attribute selector makes the whole rule (0,1,1), which beats a
   plugin's single-class button selector (0,1,0). Combined with the theme stylesheet
   printing after plugin CSS, the theme's brand button (bg color, `min-height: 2.6em`,
   `line-height: 2.5em`, `display: inline-flex`, radius, padding) overrides plugin
   component buttons. The `display: inline-flex` even un-hides buttons a plugin hides
   with `display: none` at (0,1,0).
2. **A blocklist can't scale.** It only excludes what we knew about when writing it
   (Gutenberg chrome, WP/WC blocks, a few theme-internal classes). Every third-party
   plugin UI is unprotected by default.

Real-world case (2026-07-06): GreenLight Pulse Board docs/forum on pressmaximum.com —
the plugin's icon buttons (voting arrows, sidebar close/hamburger, AI-search submit)
rendered as large solid-brand theme buttons; a ~370-line scoped compat stylesheet in the
site's child theme was needed as a workaround (see pressmaximum site
`docs/SPEC-greenlight-docs.md` §4). Any theme user combining Customify with a
plugin that renders frontend `<button>` UI can hit the same class of bug.

### Research — how 6 popular themes contain button styling (code-verified 2026-07-06)

| Theme | Strategy | Key evidence |
|---|---|---|
| Astra | Bare `button, input[type=...]` at (0,0,1), NO guards; known-plugin selectors are string-appended into the same rule; customizer CSS reuses the identical selector | `inc/class-astra-dynamic-css.php:2793` |
| GeneratePress | Same minimal-specificity bare-element approach; one selector literal shared by static + customizer output | `assets/css/main.css:383`, `inc/customizer/fields/buttons.php:38` |
| Kadence | Bare element list at low specificity, but **customizer writes CSS variables to `:root` only** (`--global-palette-btn-bg`, …) — selectors never generated at runtime; ~11 dedicated per-plugin compat CSS files, each scoped to that plugin's own containers | `assets/css/global.min.css`, `inc/customizer/class-theme-customizer.php:2027` |
| Blocksy | **No bare `button`/`input` element selectors at all** — class allowlist (`.button`, `.ct-button`, `.wp-element-button`, `[type="submit"]`) + explicitly named third-party classes it opts in; customizer = variables only | `static/sass/frontend/4-components/buttons/base.scss:3-22` |
| OceanWP | Attribute-narrowed (`button[type=submit]`, never bare `button`) + ancestor-context scoping for plugin forms; customizer reuses the identical selector array | `assets/css/style.css:2200`, `inc/customizer/css-output/selectors.php:75` |
| Twenty Twenty-Five (block themes) | Theme ships no button CSS; WP core compiles `theme.json elements.button` to the hardcoded class pair `.wp-element-button, .wp-block-button__link`; core uses `:where()` on the `link` element precisely to keep specificity at zero | `wp-includes/class-wp-theme-json.php:654-670` |

Takeaways: the safe patterns are (a) **minimal specificity** so any class-based plugin
CSS wins, (b) **class allowlist** instead of element blocklist, (c) **variables-only
dynamic CSS**. None of the six use `@layer`; containment is pure selector composition.
Customify is currently the only one whose guard mechanism *increases* specificity.

### Fix — ranked

- [ ] **Step 1 (small, ship first): wrap every `:not()` guard in `:where()`** —
      `button:where(:not(...))` — in BOTH the static SCSS (`_forms.scss`) and the
      customizer `$button_selector` (`buttons-forms.php`). `:where()` forces specificity
      0 for the guard, returning the rules to (0,0,1) while keeping identical exclusion
      behavior. Any class-styled plugin button then wins naturally. Verify: theme
      buttons still styled (comment form, search, `.button`); a class-styled
      third-party button is no longer overridden; customizer button colors still apply.
      Mind the compatibility/safety rules in `AGENTS.md` (30k installs).
- [ ] **Step 2 (major version): migrate to a class-gated allowlist** — brand-paint only
      `.button` / `.wp-element-button` / `.wp-block-button__link` + form contexts the
      theme owns (comment form, search, own widgets); bare `<button>` keeps only a
      neutral reset (appearance/font inherit). Visual breaking change for unlisted
      plugin forms → needs release notes and a beta cycle.
- [ ] **Step 3: dynamic CSS emits variables only** — customizer button options write
      `--customify-*` tokens to `:root` (partially exists already via
      `--customify-primary`); the consuming selector list becomes fixed, compiled-once
      CSS. Eliminates the risk of runtime-generated selectors diverging from static ones.
- [ ] After Step 1 ships in a release: shrink the GreenLight compat stylesheet on
      pressmaximum.com (`pm2020/greenlight-board/compat.css`) to just the
      `display` un-hide cases.

---

<!-- New entries go above this line, numbered incrementally. -->
