# Handoff — Customizer settings for Form Fields & Buttons

**Status:** the default form/field/button styling was modernized in commit
`4752a190` ("style: modernize default form fields, buttons & UI controls"), but
the values are **hardcoded in SCSS** — there are **no Customizer controls** for
form fields or buttons yet. This doc is the plan to add them.

Audience: a fresh session with no memory of the styling work. Everything you
need (mechanism, files, current values, 30k rules) is below.

---

## 1. Goal

Expose the form-field and button look as **Customizer settings** so end users can
control it live (parity with Astra / Kadence / GeneratePress, which all ship a
"Form Fields" + "Buttons" styling block). Today these are static SCSS values.

Scope = the **control family** only (text inputs, select, textarea, the global
button, and the controls that already share their tokens: WC quantity stepper,
WC sorting select, pagination, header search). Do NOT try to make one setting
drive every radius/border in the theme.

---

## 2. The mechanism (already used elsewhere — copy it)

Customify has a **field → `:root` CSS var → SCSS token** pipeline. Use it.

**Precedent (verbatim pattern to copy):** `container_width` in
[`inc/customizer/configs/layouts.php`](../../inc/customizer/configs/layouts.php) ~line 114:
```php
'selector'   => 'format',
'css_format' => ':root { --wp--style--global--wide-size: {{value}}; } ...',
```
- `selector => 'format'` + a `css_format` that contains a full rule lets a field
  emit arbitrary CSS (here, a `:root` custom property) through the auto-CSS
  pipeline `Customify_Customizer_Auto_CSS::render_css()`.
- **CRITICAL 30k behavior:** the auto-CSS pipeline **skips emission for default
  values**. So if a field's `default` equals the current hardcoded value and the
  user never changes it, **nothing is emitted** → the SCSS `var(--x, <fallback>)`
  fallback renders → **byte-identical** for all 30k existing sites. This is the
  whole reason this approach is safe. Keep defaults == current values.

**Color tokens** are printed by `customify_color_palette_root_css()`
([`inc/colors-palette.php`](../../inc/colors-palette.php) ~line 509) — that's how
`--customify-primary`, `--customify-border`, etc. land on the page. New
field/button vars can ride the same `:root` block OR be emitted per-field via
`css_format` (per-field is simpler; pick one and be consistent).

---

## 3. Recommended implementation order

1. **Add SCSS tokens** in [`src/frontend/scss/utils/_vars.scss`](../../src/frontend/scss/utils/_vars.scss),
   each `var(--customify-<x>, <current hardcoded value>)`. Example:
   ```scss
   $field_bg:        var(--customify-field-bg, transparent);
   $field_border:    var(--customify-field-border, #{$color_border_medium});
   $field_radius:    var(--customify-field-radius, 3px);
   $field_focus:     var(--customify-field-focus, #{$color_primary});
   $button_radius:   var(--customify-button-radius, 3px);
   ```
2. **Swap the hardcoded values** in the consuming partials (see §4) for the new
   tokens. Build and confirm the frontend CSS is **byte-identical** to before
   (the fallbacks equal the old literals) — same proof technique used in the
   styling commit (diff the built `style-theme.css` against a baseline).
3. **Add Customizer fields** that emit `:root { --customify-field-radius: {{value}} }`
   etc., with `default` == the token fallback. Section placement: §5.
4. (Optional) **Editor parity** — see §6 gotcha.

---

## 4. Where the values live now (files to touch)

The form/button styles were extracted into **`src/frontend/scss/base/_forms.scss`**
(a `$editor_context`-gated partial — frontend only). Current hardcoded values:

| Element | File | Current value (→ becomes the field DEFAULT) |
|---|---|---|
| Field bg | `base/_forms.scss` (input/select/textarea rule) | `transparent` |
| Field border | `base/_forms.scss` | `1px solid $color_border_medium` (≈22%, `--customify-border-medium`, defined in `utils/_vars.scss`) |
| Field radius | `base/_forms.scss` | `3px` |
| Field focus | `base/_forms.scss` `&:focus` | border `$color_primary` + ring `0 0 0 3px color-mix($color_primary 18%)` |
| Field height | `base/_forms.scss` | `2.6em` |
| Button radius | `base/_forms.scss` (the big `.button`/`button`/`.wp-block-button__link` rule) | `3px` |
| Button weight | `base/_forms.scss` | `500` |
| Button text-transform | `base/_forms.scss` | removed (was `uppercase`) |
| Button bg/color | `base/_forms.scss` | `$color_secondary` / `--customify-on-secondary` (block button: `$color_primary`) — **likely already user-controllable via the Colors section; verify before adding a duplicate** |

**Controls that SHARE the field tokens** (update these too if a setting changes
the token, or they drift):
- WC quantity stepper — `src/frontend/scss/compatibility/wc/_woocommerce-main.scss` (`.input-qty-pm`, border `$color_border_medium`, radius **4px** — note the 4px vs 3px difference, decide if it should follow `$field_radius`).
- WC sorting select — `src/frontend/scss/compatibility/wc/_woocommerce-layout.scss` (`.woocommerce-ordering select`).
- Pagination — `src/frontend/scss/layouts/_blogs.scss` (`.pagination .nav-links > *`).
- Header search (inline + popover) — `src/frontend/scss/header/builder_items/_search.scss` (`.search-form-fields` wrapper carries border for inline; `.search-field` carries it for the modal — two paths, mind both).

---

## 5. Suggested Customizer field set

The retired "Styling" panel was merged into the **Colors** section (see the note
at top of [`inc/customizer/configs/styling.php`](../../inc/customizer/configs/styling.php)).
Decide between: (a) a NEW section "Form Fields & Buttons", or (b) extend
`colors.php`. A dedicated section reads better. Model the field definitions on
existing configs (`colors.php`, `layouts.php`).

**Form Fields**
- Background color — default `transparent`
- Border color — default `--customify-border-medium`
- Border width — default `1px` (optional)
- Border radius — default `3px`
- Focus color (border + ring) — default `--customify-primary`
- Text color — default `--customify-body-text` (optional; may already exist)

**Buttons** (colors likely already exist via palette → focus on shape/type)
- Border radius — default `3px`
- Font weight — default `500`
- Uppercase toggle (text-transform) — default OFF
- Padding — default `0 1.3em` (optional)

Each field: `type` color/range/select, `selector => 'format'`,
`css_format => ':root { --customify-field-<x>: {{value}}; }'`, `default => <current>`.

---

## 6. Gotchas / decisions

- **30k-safety is the whole game.** Every new field's `default` MUST equal the
  current hardcoded value, and the SCSS fallback MUST equal it too. Verify
  byte-identical built CSS at default. See [`docs/migration-guide.md`](../migration-guide.md)
  and AGENTS.md §4.1.
- **`_forms.scss` is editor-gated** (`@if not $editor_context`). The Customizer
  CSS is emitted inline on the FRONTEND (`customify-style` handle) and applies
  there. It will NOT reach the block-editor canvas. If editor parity is wanted
  for the Button block, mirror the relevant vars into the dedicated
  `.wp-block-button__link` rule at the bottom of
  [`src/backend/admin/scss/editor.scss`](../../src/backend/admin/scss/editor.scss),
  and/or add keys to `Customify_Editor::css()` `$keys` in
  [`inc/admin/editor.php`](../../inc/admin/editor.php). Otherwise scope the
  feature to "frontend only" and say so in the field description.
- **Field selector is a big list** (`input[type="text"], … select, textarea`).
  The token approach sidesteps this — emit the `:root` var, let the existing
  selector list consume `var()`. Don't re-emit the selector list from PHP.
- **Button colors may already be controllable** via the Colors palette
  (`--customify-secondary` / `--customify-primary` + `--customify-on-*`). Check
  before adding duplicate color fields — only add shape/typography for buttons.
- **Stepper radius is 4px**, fields/buttons are 3px (intentional at the time).
  Decide whether the stepper follows `$field_radius` or keeps its own.
- **Live preview:** color/range fields re-render via the auto-CSS pipeline; the
  `:root`-var css_format updates live (same as `container_width`). Confirm in the
  Customizer preview.

---

## 7. References

- AGENTS.md §4.1 (30k-site safety), §4.7 (`customify-style` handle), §4.11 (var sync).
- [`docs/SPEC-customizer.md`](../SPEC-customizer.md) — Customizer pipeline & auto-CSS.
- [`docs/SPEC-customizer-colors.md`](../SPEC-customizer-colors.md) — color-token emission.
- [`docs/api-reference.md`](../api-reference.md) — field signatures.
- Precedent field: `container_width` in `inc/customizer/configs/layouts.php` (`:root` var via `css_format`).
- The styling commit this follows up on: `4752a190`.
