# SPEC — Customify Customizer

Canonical reference for building on top of Customify's Customizer system: how to add settings, controls, panels and sections, how the auto-CSS pipeline works, how the Header & Footer Builder is structured, and how Customizer values feed the block editor.

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Architecture at a glance

The Customizer system has two halves: **server-side PHP** (registers panels/sections/settings, renders the frontend CSS) and **client-side JavaScript** (lives inside three WordPress Customizer contexts — controls, preview, register). The single source of truth on both sides is the config array.

### 1.1 Server-side (PHP)

```
inc/customizer/
├── class-customizer.php          Customify_Customizer  (singleton, bootstraps Customizer)
├── class-customizer-auto-css.php Customify_Customizer_Auto_CSS  (config → CSS pipeline)
├── class-customizer-fonts.php    Customify_Customizer_Fonts     (Google Fonts loader)
├── configs/                      Each .php file returns a config array via the
│                                 `customify/customizer/config` filter.
│   ├── styling.php               Colors (primary/secondary/text/link/heading)
│   ├── typography.php            Font family, size, line-height
│   ├── layouts.php               Container width, sidebar layout
│   ├── blogs.php                 Blog listing
│   ├── single-blog-post.php      Single post layout + items repeater
│   ├── header/                   Header builder + per-item config (logo, menu, …)
│   └── footer/                   Footer builder + per-item config
└── controls/                     One PHP file per custom control class.

inc/panel-builder/
└── class-panel-builder.php       Customify_Panel_Builder  (Header/Footer WYSIWYG)

inc/admin/
└── editor.php                    Customify_Editor  (pipes Customizer CSS into block editor)
```

### 1.2 Client-side (JavaScript)

```
src/backend/customizer/
├── customizer.js                 Entry: imports SCSS + delegates to js/customizer.js
└── js/
    ├── customizer.js             Preview-side: selective refresh, header panel handling
    ├── auto-css.js               Preview-side: JS mirror of PHP auto-CSS for live preview
    ├── control.js                Controls-panel-side: wp.customize.Control extensions
    ├── color-picker-alpha.js     Controls-panel-side: alpha-channel color picker
    ├── builder.js                Controls-panel-side: builder version router
    ├── builder-v1.js             Controls-panel-side: legacy grid layout builder
    ├── builder-v2.js             Controls-panel-side: layout builder v2 (jQuery)
    └── controls/
        └── columns-settings/     React component for columns_settings control

src/backend/header-builder/index.js   React V2 header builder (61 KB built)
src/backend/footer-builder/index.js   React V2 footer builder (70 KB built)
```

Detailed JS architecture, contexts, and live-preview wiring: see §7–§9.

### 1.3 Bootstrap order (PHP)

1. `functions.php` instantiates `Customify()` singleton.
2. `Customify_Customizer::init()` hooks `customize_register` at priority 666.
3. On `customize_register`, the class fires `apply_filters( 'customify/customizer/config', [] )`.
4. Every config file hooks that filter and appends its items.
5. The class walks the merged array and registers panels → sections → settings + controls.
6. `Customify_Customizer_Auto_CSS::render_css()` generates frontend CSS on each page load from the same config array.

### 1.4 Source of truth

Adding a feature means appending a config item; auto-CSS, control rendering, Customizer registration, AND the preview-side JS pipeline (auto-css.js consumes the same config via `wp_localize_script`) all follow automatically.

---

## 2. Core concepts

### 2.1 Config item — the atomic unit

Every panel, section, and setting is a flat associative array keyed by `type`. The `type` field discriminates:

| `type` value | Role |
|---|---|
| `'panel'` | Customizer panel (top-level group) |
| `'section'` | Section inside a panel |
| any control name (`color`, `slider`, `typography`, `repeater`, …) | Setting + control pair |

The class indexes items into a static cache keyed `panel|<name>`, `section|<name>`, `setting|<name>` so lookups are O(1).

### 2.2 Singleton access

```php
Customify()->customizer                                  // Customify_Customizer instance
Customify()->customizer->get_field_setting( 'primary_color' )  // Config definition by name
Customify()->get_setting( 'container_width' )            // Saved theme_mod / option value
Customify()->get_setting( 'logo_width', 'tablet' )       // Device-aware variant
```

`get_setting()` is device-aware: pass `'desktop'`, `'tablet'`, or `'mobile'` as the second arg if the field has `device_settings: true`.

### 2.3 `theme_mod` vs `option`

A config item can opt into `'mod' => 'option'` to store as a WordPress option (visible across themes) instead of the default `theme_mod` (per-theme). Use `option` only when the value semantically belongs to the site, not the theme.

---

## 3. Adding a setting — minimal recipe

### Step 1 — Create or extend a config file

`inc/customizer/configs/my-feature.php`:

```php
<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'customify/customizer/config', function ( $items ) {
    // Section (skip if reusing an existing one)
    $items[] = array(
        'name'     => 'my_feature_section',
        'type'     => 'section',
        'panel'    => 'typography_panel',
        'title'    => __( 'My Feature', 'customify' ),
        'priority' => 50,
    );

    // Setting
    $items[] = array(
        'name'        => 'my_feature_accent',
        'type'        => 'color',
        'section'     => 'my_feature_section',
        'title'       => __( 'Accent color', 'customify' ),
        'default'     => '#ff7a00',
        'selector'    => '.my-feature-block',
        'css_format'  => 'background-color: {{value}};',
    );

    return $items;
} );
```

### Step 2 — Include the file

Add to `inc/customizer/class-customizer.php` config loader (most repos `require` every PHP file in `configs/` automatically — verify before duplicating).

### Step 3 — That's it

- The Customizer shows the new section + control automatically.
- Auto-CSS injects `.my-feature-block { background-color: <chosen>; }` site-wide (PHP, via §6).
- Live preview works via postMessage by default (JS, via §8) — the same config is bridged to `auto-css.js` through `wp_localize_script`.

No code in `functions.php`, no manual `add_setting()` calls, no separate CSS files, no separate JS file. If you find yourself writing JS for a setting that's just a CSS property, you're probably missing a per-type handler in `auto-css.js` (§8.2).

---

## 4. Config item reference

### 4.1 Panel

```php
array(
    'name'        => 'example_panel',
    'type'        => 'panel',
    'title'       => __( 'Example', 'customify' ),
    'priority'    => 22,
    'description' => __( 'My custom panel', 'customify' ),  // optional
)
```

### 4.2 Section

```php
array(
    'name'     => 'global_styling',
    'type'     => 'section',
    'panel'    => 'example_panel',         // required to nest inside a panel
    'title'    => __( 'Global Colors', 'customify' ),
    'priority' => 10,
)
```

Omit `'panel'` for a top-level section.

### 4.3 Setting

Universal keys (most types accept all of these):

| Key | Required | Type | Notes |
|---|---|---|---|
| `name` | ✓ | string | Unique theme-wide. Used as `theme_mod` key. |
| `type` | ✓ | string | Control type — see catalog in §5. |
| `section` | ✓ | string | Parent section name. |
| `title` | ✓ | string | Label shown in Customizer. |
| `default` | – | mixed | Initial value. |
| `priority` | – | int | Display order inside section. |
| `description` | – | string | Help text under the control. |
| `selector` | – | string\|array | CSS selector(s) the value applies to. Use `'format'` for non-CSS settings. |
| `css_format` | – | string\|array | Template producing the CSS. Supports `{{value}}` and `{{value_no_unit}}`. |
| `device_settings` | – | bool | Enable per-device values (desktop/tablet/mobile). |
| `render_callback` | – | callable | Selective-refresh callback (returns rendered HTML). |
| `mod` | – | string | `'theme_mod'` (default) or `'option'`. |
| `transport` | – | string | `'postMessage'` (default) or `'refresh'`. Auto-CSS already uses postMessage. |
| `required` | – | array | Conditional visibility: `array( 'other_field_name', '==', 'value' )`. |
| `choices` | – | array | For `select` / `radio`: `array( 'value' => 'Label' )`. |
| `placeholder` | – | string | For text/color inputs. |
| `min` / `max` / `step` | – | int\|float | For `slider`. |
| `units` | – | array | For `slider`, opt-in: `array( 'px' => array( 'min' => 1, 'max' => 120, 'step' => 1 ), 'em' => …, '-' => … )` renders a mini unit `<select>` with per-unit ranges (`-` = unitless sentinel, emits a bare number). Absent ⇒ legacy single-px markup, byte-identical. See [`SPEC-typography.md §3.1`](SPEC-typography.md). |
| `display_defaults` | – | array | For `typography`, display-only: `array( sub_field => string \| array( 'desktop' => …, 'tablet' => …, 'mobile' => … ) )`. Feeds trigger previews, placeholders and slider handle seeding; never stored, never reaches the CSS generator. See [`SPEC-typography.md §3.3`](SPEC-typography.md). |
| `popover_chrome` | – | bool | For `modal`, opt-in: render the styling-style per-tab trigger rows + floating popover instead of the pencil + accordion. Only for modals whose fields are style values (colors/border/background) — data-only modals (Display, Title & Tagline) must stay on the accordion. `styling` controls get the chrome unconditionally. |

---

## 5. Control types catalog

All control classes live in `inc/customizer/controls/`. The `type` field picks the matching `Customify_Customizer_Control_*` automatically.

### 5.1 Primitive controls

| `type` | Use case |
|---|---|
| `text` | Single-line text |
| `textarea` | Multi-line text |
| `checkbox` | Boolean toggle |
| `radio` | Single-choice radio group |
| `select` | Dropdown with `choices` |
| `hidden` | Hidden input (programmatic only) |
| `heading` | Non-editable label/separator |
| `hr` | Visual divider |

### 5.2 Visual controls

| `type` | Use case |
|---|---|
| `color` | Color picker |
| `slider` | Numeric range with unit; supports `device_settings`. Opt-in multi-unit via the `units` config key (per-unit ranges, ×16 px↔em conversion on switch, `-` = unitless) — see §4.3; without `units` the legacy single-px markup is unchanged |
| `image` | Media library picker filtered to images |
| `video` | Media library picker filtered to video |
| `media` | Media library picker (any type) |
| `upload` | Simple file upload |
| `icon` | Icon picker (AJAX-loaded library) |
| `text_align` | Alignment toolbar (left / center / right / justify) |
| `text_align_no_justify` | Alignment toolbar without justify |

### 5.3 Typography & spacing

| `type` | Use case |
|---|---|
| `font` | Google Fonts dropdown |
| `font_style` | Font-weight + variant |
| `typography` | Composite: font + weight + size + line-height + letter-spacing. Renders as a single control with a dedicated CSS pipeline. **As of theme `0.4.19`**, only the foundation typography settings (`global_typography_base_p`, `global_typography_base_heading`, `global_typography_heading_h1`–`h6` — see `Customify_Customizer_Auto_CSS::TYPO_VAR_MAP`) emit `:root { --customify-typo-*: value }` CSS variables; leaf-global typography (site title, tagline, widget title) and per-component typography (header builder items, footer copyright, blog read-more, breadcrumb, WC cart) keep selector-scoped literal CSS. See [`SPEC-typography.md`](SPEC-typography.md) for var naming rules, the `customify/typography/field_uses_vars` route filter, and the SCSS consumer pattern. |
| `css_ruler` | Margin / padding quad editor (`top right bottom left`). Stored as object. |
| `shadow` | Box-shadow builder (x / y / blur / spread / color). |
| `typography_presets` | Font-pair quick picks (grid of SVG cards). Chrome-only: clicking patches the family bits (font / font_type / variant) of the Body + Heading typography settings via their bound controls — its own setting is never written. Pairs declared in `customify_typography_presets()` ([typography.php](../inc/customizer/configs/typography.php)); preview families load in the controls frame via a glyph-subset css2 stylesheet. |

**Typography control chrome (trigger + popover).** The `typography` control renders a select-like trigger row — `a.action--edit.customify-typo-trigger` with `.customify-trigger--family/--meta/--arrow` spans; the legacy `action--edit` class is preserved so the delegated click handler keeps working. The summary is painted only by JS (`renderTypoTrigger` in [`typography-control.js`](../src/backend/customizer/js/typography-control.js)): saved family (else `Inherit`/`Default` per the field's `fields` gating) plus `size / weight` (the weight slot renders only when the field offers a weight control), each part falling back to `display_defaults`. Clicking opens a floating popover (`.customify-modal-settings.is-open`, anchored under the trigger, flipping above via `is-above`; dismissed by capture-phase outside `mousedown`, capture-phase ESC, and window `blur` — clicks inside the preview iframe never reach the controls document; one popover at a time). The open-transition kick uses a forced synchronous reflow (`void el.offsetWidth`), **never `requestAnimationFrame`** — rAF does not fire in hidden tabs. The Select2 font picker attaches inside the popover row via `dropdownParent`. All of this is chrome-level: setting names, value shapes, sanitize and emitted CSS are unchanged. Styles live in [`_control.scss`](../src/backend/customizer/scss/_control.scss), scoped to `.customize-control-customify-typography` so the `styling`/`modal` accordions are untouched.

### 5.4 Compound controls

| `type` | Use case |
|---|---|
| `styling` | Normal/Hover tabs combining color, background, border. Powers the global color groups. Renders per-tab trigger rows (saved-color swatches + one-word tail) opening a floating popover — same chrome as `typography`, applied unconditionally. |
| `repeater` | Multi-item list with sub-`fields`. See §7. |
| `modal` | Multi-tab modal hosting nested controls. See §7. Style-data modals may opt into the trigger + popover chrome via `popover_chrome` (§4.3); data-only modals keep the pencil + accordion. |
| `columns_settings` | Grid column ratio/gap editor. |
| `row_layout` | Grid row layout — used by the Header/Footer Builder V1. |
| `pro` | Upsell placeholder for paid-only features. |

### 5.5 Adding a new custom control

1. Create `inc/customizer/controls/class-customizer-control-<name>.php`.
2. Extend `Customify_Customizer_Control_Base` (preferred) or `WP_Customize_Control`.
3. Implement `render_content()` or override `field_template()`.
4. Register the file in `Customify_Customizer::register_controls()` so the loader picks it up.
5. Use `'type' => 'my-name'` in config items.

---

## 6. Auto-CSS pipeline

`Customify_Customizer_Auto_CSS::render_css( $fields )` walks the config array and emits a single stylesheet, attached via `wp_add_inline_style( 'customify-style', $css )`.

### 6.1 The two key fields

```php
'selector'   => '.button, .pagination .current',
'css_format' => 'background-color: {{value}}; border-color: {{value}};',
```

The renderer substitutes `{{value}}` for each device and wraps non-desktop values in media queries:

```css
/* Desktop (unwrapped) */
.button, .pagination .current { background-color: #235787; border-color: #235787; }

/* Tablet */
@media screen and (max-width: 1024px) {
  .button, .pagination .current { background-color: #1e4b75; border-color: #1e4b75; }
}

/* Mobile */
@media screen and (max-width: 568px) {
  .button, .pagination .current { background-color: #163759; border-color: #163759; }
}
```

### 6.2 Placeholders

| Placeholder | Resolves to |
|---|---|
| `{{value}}` | Full value, with unit if applicable (`20px`, `#235787`) |
| `{{value_no_unit}}` | Numeric portion only (`20` extracted from `20px`) |

### 6.3 `css_format` as array

For multi-axis values (margin, padding, css_ruler) the format is an array keyed by side:

```php
'css_format' => array(
    'top'    => 'padding-top: {{value}};',
    'right'  => 'padding-right: {{value}};',
    'bottom' => 'padding-bottom: {{value}};',
    'left'   => 'padding-left: {{value}};',
),
```

Same pattern applies to `border-width`, `border-radius`, etc.

### 6.4 Special selector values

| Value | Effect |
|---|---|
| `'format'` | No selector — the field carries side-effects but contributes no CSS (e.g. layout switches). |
| `'html_class'` | Toggle a class on the selector via JS rather than emitting a property. |

### 6.5 Per-field filter override

Append CSS targets without forking the config file:

```php
add_filter( 'customify/styling/primary-color', function ( $css ) {
    return $css . '.my-block { color: {{value}}; }';
} );
```

The filter name pattern is `customify/styling/<field-key-with-dashes>`.

### 6.6 Container width sync

When `container_width` changes, its `css_format` must update **both** `--wp--style--global--wide-size` AND the layout rules — otherwise the block editor's wide alignment drifts from the frontend. See [`inc/customizer/configs/layouts.php`](../inc/customizer/configs/layouts.php).

---

## 7. JavaScript architecture

The Customizer runs three independent JS contexts. Putting code in the wrong context silently fails — the file loads but nothing on the page is reachable.

### 7.1 The three contexts

| Context | When loaded | Sees | Used for |
|---|---|---|---|
| **customize-controls** | `customize_controls_enqueue_scripts` action | The Customizer panel (left side) DOM, the `wp.customize` settings API | Custom control UIs, builder canvases, validation |
| **customize-preview** | `customize_preview_init` action | The preview iframe DOM (the actual site), `wp.customize` bridge | Live preview, selective refresh, dynamic CSS injection |
| **customize-register** | `customize_register` action (PHP-only) | — | Settings/controls registration. No Customify JS runs here. |

**Rule:** every JS file enqueued for the Customizer must be classified into one of the first two contexts. Mixing them up is the #1 source of "my live preview doesn't work" bugs.

### 7.2 File → context mapping

| File | Context | Webpack entry | Built path |
|---|---|---|---|
| `customizer.js` (root entry) | customize-preview | `backend/customizer/customizer` | `build/js/backend/customizer/customizer.js` |
| `js/customizer.js` | customize-preview (via entry) | — | bundled with above |
| `js/auto-css.js` | customize-preview | `backend/customizer/auto-css` | `build/js/backend/customizer/auto-css.js` (~40 KB) |
| `js/control.js` | customize-controls | `backend/customizer/control` | `build/js/backend/customizer/control.js` (~129 KB) |
| `js/color-picker-alpha.js` | customize-controls | `backend/customizer/color-picker-alpha` | `build/js/backend/customizer/color-picker-alpha.js` |
| `js/builder.js` | customize-controls | `backend/customizer/builder` | `build/js/backend/customizer/builder.js` |
| `js/builder-v1.js` | customize-controls | `backend/customizer/builder-v1` | `build/js/backend/customizer/builder-v1.js` |
| `js/builder-v2.js` | customize-controls | `backend/customizer/builder-v2` | `build/js/backend/customizer/builder-v2.js` |
| `src/backend/header-builder/index.js` | customize-controls | `backend/header-builder` | `build/js/backend/header-builder.js` (~61 KB) |
| `src/backend/footer-builder/index.js` | customize-controls | `backend/footer-builder` | `build/js/backend/footer-builder.js` (~70 KB) |

Adding a new Customizer JS entry → register it in `webpack.config.js` (`entries` object) AND enqueue from PHP with the matching context action.

### 7.3 PHP → JS config bridge

PHP injects the config array into JS via `wp_localize_script`:

```php
wp_localize_script( 'customify-customizer-auto-css', 'Customify_Preview_Config', array(
    'fields'         => $fields_array,
    'styling_config' => $styling_config,
) );
```

The JS reads from `window.Customify_Preview_Config`. Similar bridges exist for the Header Builder (`window.Customify_Layout_Builder.builders.header`) and Footer Builder.

**Rule:** never duplicate config in JS. The PHP config is canonical; JS reads the bridge.

### 7.4 Asset loading via `.asset.php`

Each webpack entry produces an `index.asset.php` sidecar with dependencies + version hash. Always enqueue using it:

```php
$asset = require get_template_directory() . '/build/js/backend/customizer/auto-css.asset.php';
wp_enqueue_script(
    'customify-customizer-auto-css',
    get_template_directory_uri() . '/build/js/backend/customizer/auto-css.js',
    $asset['dependencies'],
    $asset['version'],
    true
);
```

Never hardcode `array( 'jquery', 'wp-customize-preview' )` — webpack already knows.

---

## 8. Live preview pipeline (preview-side)

`auto-css.js` is the JS twin of `Customify_Customizer_Auto_CSS::render_css()`. Both consume the same config array and produce the same CSS — PHP at page-load for the frontend, JS in-iframe for live preview.

### 8.1 Initialization (inside preview iframe)

1. PHP enqueues `auto-css.js` on `customize_preview_init`, localizes config to `window.Customify_Preview_Config`.
2. The script instantiates `CustomifyAutoCSS()` on DOM ready.
3. For each field in the config, it binds:

```js
wp.customize( field.name, function ( setting ) {
    setting.bind( function ( newValue ) {
        // 1. Look up field handler by type (slider, color, css_ruler, …)
        // 2. Substitute newValue into field.css_format
        // 3. Inject <style id="customify-live-<field.name>"> into iframe <head>
    } );
} );
```

### 8.2 Per-type handlers in auto-css.js

Each control type has a matching JS method that mirrors its PHP counterpart:

| Type | JS method | What it does |
|---|---|---|
| `color` | `this.color()` | Single replace `{{value}}` → hex/rgba |
| `slider` | `this.slider()` | Numeric + unit; reads device variant |
| `css_ruler` | `this.css_ruler()` | Loops 4 sides, applies array-form `css_format` |
| `shadow` | `this.shadow()` | Builds `Xpx Ypx Bpx Spx color` string |
| `typography` | `this.typography()` | Loops sub-keys (font/weight/size/line-height) |
| `styling` | `this.styling()` | Normal + Hover tabs; combines color/bg/border |
| `image` | `this.image()` | Outputs `url('...')` for background-image |
| `html_class` | `this.html_class()` | Toggles class on selector via direct DOM (no `<style>` tag) |
| `html_replace` | `this.html_replace()` | Replaces a text node — used for repeater item labels |

### 8.3 Device-aware media queries (JS side)

Both PHP and JS use the SAME breakpoints to stay consistent:

```js
desktop : no wrapper
tablet  : @media screen and (max-width: 1024px) { ... }
mobile  : @media screen and (max-width: 568px)  { ... }
```

When changing a breakpoint, update **both** `class-customizer-auto-css.php` AND `auto-css.js` — out-of-sync values cause editor-vs-preview-vs-frontend drift.

### 8.4 Selective refresh — outside auto-CSS

For fields whose value can't be expressed as CSS (e.g. Header Builder layout, repeater item order), use WP's selective refresh:

- PHP: `'render_callback' => 'my_render_fn'` on the field.
- PHP: register a `selective_refresh` partial via `$wp_customize->selective_refresh->add_partial()`.
- JS (preview): `customizer.js` listens for the partial and replaces the DOM chunk.

Selective refresh re-renders an HTML fragment; auto-CSS rewrites only CSS. Use auto-CSS when possible — it's faster and doesn't flash.

### 8.5 Common live-preview bugs

| Symptom | Cause |
|---|---|
| Setting changes only show after publish | `transport` is `'refresh'`; switch to `'postMessage'` |
| Live preview works for color but not slider | `auto-css.js` field handler missing or `css_format` shape wrong |
| Preview drifts from frontend on tablet | Breakpoint mismatch between PHP and JS (see §8.3) |
| Repeater reorder doesn't preview | `render_callback` not registered, OR partial not added to `selective_refresh` |
| Live preview throws after upgrade | `wp.customize( name, fn )` callback receives `undefined` for new settings — guard with `if ( setting )` |

---

## 9. Control JS — Backbone and React

Customify controls split into two implementation styles:

### 9.1 Backbone-style controls (legacy, most controls)

All custom PHP control classes extend `Customify_Customizer_Control_Base` and render via Underscore templates. The JS side (`control.js`) extends `wp.customize.Control` and `wp.customize.Panel` prototypes to add nested-panel UI, accordion behavior, device tabs, and per-control initialization (e.g. jQuery-UI slider, jQuery-UI sortable for repeaters).

For most controls, the Backbone template + a small init function inside `control.js` is enough — no extra JS file needed.

### 9.2 React-style controls (modern)

Used for controls with complex state (`columns_settings`, the V2 builders).

**`columns_settings` — the canonical pattern:**

- Source: `src/backend/customizer/js/controls/columns-settings/index.jsx`
- Bundled INTO `control.js` (not a separate webpack entry)
- Mounted via `observeAndMount()` — a `MutationObserver` that watches `#customize-theme-controls` for new column-settings DOM nodes and mounts React into them on demand
- Bridge to Customizer state: `useCustomizeSetting( controlId )` hook
  - Reads initial value via `wp.customize( controlId ).get()`
  - Subscribes to changes via `setting.bind( callback )`
  - Writes via `setting.set( encodeURIComponent( JSON.stringify( data ) ) )`
  - Sets `control._customifyWriting = true` flag during the write to prevent the `.bind()` echo causing double-renders

### 9.3 React-style builders (Header & Footer)

The Header / Footer Builders are full-screen React canvases for arranging items in rows and column slots.

- Entry: `src/backend/header-builder/index.js`, `src/backend/footer-builder/index.js`
- Root component: `Builder.jsx` shared between both
- Mounted into `body .wp-full-overlay` on `wp.customize.bind('ready')`
- Config injection: `window.Customify_Layout_Builder.builders.header` / `.footer`
- State: a single setting per builder (`header_builder_panel_v2`, `footer_builder_panel_v2` — names kept for historical reasons), value = JSON-encoded layout tree
- Pro variant switching: listens to `customify/builder/external-update` window event so Customify Pro can swap variant data without remounting the React tree

For the full builder spec, see [`SPEC-header-footer-builder.md`](./SPEC-header-footer-builder.md).

### 9.4 Adding a new control's JS

**Backbone (most cases):**

1. Add init code inside `src/backend/customizer/js/control.js` keyed to your control type, e.g.:

   ```js
   wp.customize.controlConstructor['my-type'] = wp.customize.Control.extend( {
       ready: function () {
           // Wire up the UI
       },
   } );
   ```

2. Inside `ready()`, listen to input changes, call `this.setting.set( newValue )`.
3. If the control needs live preview, the `auto-css.js` side handles output (no extra JS needed if the field's `type` already has a handler).

**React:**

1. Create `src/backend/customizer/js/controls/<name>/index.jsx` (component + `observeAndMount` glue).
2. Import the mount function in `control.js` so it's bundled into the controls entry.
3. Use the `useCustomizeSetting()` hook for state — don't re-implement Backbone bridging.

### 9.5 Don't enqueue per-control JS files

Pre-2023, each custom control enqueued its own JS (`slider.min.js`, `css-ruler.min.js`, etc.). The new pattern bundles all control init into `control.js` (one HTTP request, one bundle). New controls follow the bundled pattern unless they're React-heavy enough to warrant their own entry.

---

## 10. Repeater & Modal — complex controls

### 7.1 Repeater

For variable-length lists. Use `addable: false` for a fixed list whose order/visibility can change but whose items cannot be added/removed (e.g. the single-post item order).

```php
array(
    'name'             => 'single_blog_post_items',
    'type'             => 'repeater',
    'section'          => 'section_single_blog_post',
    'title'            => __( 'Items Display', 'customify' ),
    'live_title_field' => 'title',            // Which sub-field shows in collapsed header
    'addable'          => false,              // Fixed-list mode
    'selector'         => '.entry-single',
    'render_callback'  => 'customify_single_post',
    'default'          => array(
        array( '_key' => 'title', 'title' => 'Title', '_visibility' => '' ),
        array( '_key' => 'meta',  'title' => 'Meta',  '_visibility' => '' ),
    ),
    'fields'           => array(
        array( 'name' => '_key',        'type' => 'hidden' ),
        array( 'name' => 'title',       'type' => 'text', 'title' => __( 'Label', 'customify' ) ),
        array( 'name' => '_visibility', 'type' => 'select', 'choices' => array(
            ''           => __( 'Show', 'customify' ),
            'hidden'     => __( 'Hide', 'customify' ),
        ) ),
    ),
)
```

The `'_visibility' => 'hidden'` value is treated specially — auto-CSS emits `display: none` for the matching sub-selector.

### 7.2 Modal

Use when one logical setting needs many sub-controls (e.g. styling = colors + borders + shadows in one button). Sub-fields are grouped into tabs; the saved value is an object keyed by sub-field name.

Modals whose fields are pure style values can set `'popover_chrome' => true` to swap the pencil + accordion for the per-tab trigger rows + floating popover (theme adopters: `header_cover_bg`, the header/footer social-icons Custom Color and Border modals). The storage shape, `get()` serialization and CSS emit are identical either way — the flag is chrome-only.

```php
array(
    'name'    => 'button_styling',
    'type'    => 'modal',
    'section' => 'global_styling',
    'title'   => __( 'Button styling', 'customify' ),
    'tabs'    => array( 'normal' => __( 'Normal' ), 'hover' => __( 'Hover' ) ),
    'fields'  => array(
        'normal' => array(
            array( 'name' => 'bg',     'type' => 'color', 'title' => __( 'Background', 'customify' ) ),
            array( 'name' => 'border', 'type' => 'css_ruler', 'title' => __( 'Border width', 'customify' ) ),
        ),
        'hover'  => array(
            array( 'name' => 'bg', 'type' => 'color', 'title' => __( 'Background', 'customify' ) ),
        ),
    ),
)
```

---

## 11. Header & Footer Builder

The Header / Footer WYSIWYG builder has its own dedicated spec — it's substantial enough to warrant a separate document covering class hierarchy, V1 vs V2 storage, the frontend render pipeline, mobile sidebar behaviour, and the full extension surface.

**→ See [`SPEC-header-footer-builder.md`](./SPEC-header-footer-builder.md).**

### 11.1 Quick reference

| Aspect | Value |
|---|---|
| Builders | `Customify_Builder_Header`, `Customify_Builder_Footer` (subclasses of `Customify_Customize_Builder_Panel`) |
| Header rows | `top`, `main`, `bottom`, `sidebar` (mobile-only) |
| Footer rows | `main`, `bottom` |
| Header column slots | 3 per row: `left`, `center`, `right` |
| Footer column slots | 5 per row: `left`, `center`, `right`, `col4`, `col5` |
| Per-row CSS layer | `{section}_columns_settings` field on every row (layout / gap / padding per column) |
| Frontend renderer | `Customify_Layout_Builder_Frontend_V2` |
| Storage | `theme_mod 'header_builder_panel_v2'` / `'footer_builder_panel_v2'` |
| Render entry | `add_action( 'customify/site-start', 'customify_customize_render_header' )` |
| React canvas mount | `body .wp-full-overlay` on `wp.customize.bind('ready')` |
| PHP → JS bridge | `window.Customify_Layout_Builder` |
| Per-item registration | `Customify_Customize_Layout_Builder()->register_item( $builder_id, $instance )` |

### 11.2 When to consult the dedicated spec

Read [`SPEC-header-footer-builder.md`](./SPEC-header-footer-builder.md) before:

- Adding a new header or footer item (logo / menu / search / custom widget / …)
- Adding a new row to header or footer
- Customizing the mobile sidebar
- Adjusting per-column layout / gap / padding (the `columns_settings` field)
- Extending render output via the builder hooks catalog
- Reading/writing the builder's `theme_mod` value programmatically

---

## 12. Block editor integration

`Customify_Editor::css()` ([`inc/admin/editor.php`](../inc/admin/editor.php)) pipes a curated subset of Customizer values into the block editor iframe so the editing surface matches the frontend.

### 9.1 The `$keys` array

```php
$keys = array(
    'container_width',
    'site_content_styling',
    'content_background',
    'single_blog_post_content_width',
    'global_typography_heading_h1',
    'global_typography_base_heading',
    'global_styling_color_heading',
);
```

For each key the method:
1. Reads the field definition via `Customify()->customizer->get_field_setting( $key )`.
2. Rewrites the `selector` so it targets editor markup (`.editor-styles-wrapper .wp-block-post-title`, etc.).
3. Runs the field through `Customify_Customizer_Auto_CSS::render_css()` to produce editor-scoped CSS.
4. Attaches the CSS to the `wp-edit-blocks` style handle.
5. Mirrors numeric values into CSS custom properties on `:root` (e.g. `--wp--style--global--content-size`) so block layout matches.

### 9.2 Adding a new key

1. Append the field name to the `$keys` array.
2. If the editor needs a different selector or format than the frontend, add a `case` branch right after the field lookup (the file already has examples like `single_blog_post_content_width` which only applies on post-type `post`).
3. Test in WP 6.0+ — selectors changed in modern Gutenberg; do **not** assume `.editor-post-title__input` (use `.wp-block-post-title` instead). See the CLAUDE.md deprecated selectors table.

### 9.3 Selector pitfalls

WP core blocks have rendered HTML that often differs from documentation. Verify with:

```bash
grep -r 'class=' /path/to/wordpress/wp-includes/blocks/<block-name>/
```

Known mistakes documented in [`SPEC-block-editor.md`](SPEC-block-editor.md) §9 + [`../AGENTS.md`](../AGENTS.md) §4.10.

---

## 13. Filters & hooks reference

| Hook | Type | Purpose |
|---|---|---|
| `customify/customizer/config` | filter | Append config items (the primary extension point). |
| `customify/auto-css` | filter | Modify the final assembled CSS string. |
| `customify/styling/<field>` | filter | Append CSS templates to a specific field (e.g. `customify/styling/primary-color`). |
| `customify/customize/register_completed` | action | Fires after all panels/sections/settings registered. |
| `customify/header-builder/items` | filter | Register Header Builder items. |
| `customify/footer-builder/items` | filter | Register Footer Builder items. |
| `customify/header/items/render` | action | Render a header item's HTML. |
| `customify/footer/items/render` | action | Render a footer item's HTML. |
| `customify_get_layout` | filter | Override page layout (`content`, `content-sidebar`, `sidebar-content`, `sidebar-content-sidebar`). |

---

## 14. Recipes

### 11.1 Add a color that recolors both buttons and links

```php
add_filter( 'customify/customizer/config', function ( $items ) {
    $items[] = array(
        'name'       => 'accent_color',
        'type'       => 'color',
        'section'    => 'global_styling',
        'title'      => __( 'Accent', 'customify' ),
        'default'    => '#ff7a00',
        'selector'   => '.btn-primary, a',
        'css_format' => 'color: {{value}}; border-color: {{value}};',
    );
    return $items;
} );
```

### 11.2 Per-device max-width

```php
array(
    'name'            => 'card_max_width',
    'type'            => 'slider',
    'section'         => 'cards_section',
    'title'           => __( 'Card max width', 'customify' ),
    'min'             => 200,
    'max'             => 1000,
    'step'            => 10,
    'default'         => 600,
    'device_settings' => true,
    'selector'        => '.card',
    'css_format'      => 'max-width: {{value}};',
)
```

The Customizer renders device tabs automatically; auto-CSS wraps tablet/mobile values in media queries.

### 11.3 Fixed-list reorderable items

See §7.1 — set `'addable' => false` and provide a `default` array. Users can toggle visibility and drag to reorder but cannot add/remove.

### 11.4 Conditional control visibility

```php
array(
    'name'     => 'show_meta',
    'type'     => 'checkbox',
    'default'  => true,
    // …
),
array(
    'name'     => 'meta_color',
    'type'     => 'color',
    'required' => array( 'show_meta', '==', '1' ),
    // …
)
```

The control hides when `show_meta` is unchecked.

### 11.5 Override CSS targets from a plugin

```php
add_filter( 'customify/styling/primary-color', function ( $css ) {
    return $css . '.woocommerce .button { background: {{value}}; }';
} );
```

Plugins should **not** edit theme files; this filter is the supported extension point.

---

## 15. Conventions & rules

These are enforced by [`../AGENTS.md`](../AGENTS.md) — restated here for completeness:

- **Never delete or rename existing public functions.** Mark `@deprecated` and keep the body. Customizer field names are also public API — child themes/plugins may reference them. See [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) for the canonical key registry.
- **Container width must sync to CSS custom property.** When `container_width` changes, update `--wp--style--global--wide-size` so theme.json, Customizer, and the block editor stay aligned.
- **English-only in source.** All docblocks, inline notes, and `.md` files in the codebase are English.
- **Edit `src/`, not `build/`.** SCSS/JS sources live in `src/`; build outputs in `build/` are artifacts. See [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md).
- **CSS handle must match.** `wp_add_inline_style( 'customify-style', ... )` — never `'customify'` or any other typo.

---

## 16. Troubleshooting

### 16.1 PHP / configuration

| Symptom | Likely cause |
|---|---|
| Setting saves but no CSS appears on frontend | Missing `selector` or `css_format`, or `wp_add_inline_style()` handle doesn't match the enqueued style. |
| `{{value}}` literal shows in CSS | Field has no value AND no `default`. Set a default. |
| Tablet/mobile values ignored | Field missing `device_settings: true`. |
| Editor diverges from frontend | Field not in `Customify_Editor::css()` `$keys` array, or editor `selector` rewrite wrong. |
| Header item renders nothing | Render callback not hooked to `customify/header/items/render`, or item ID typo. |
| Repeater item lost on save | Sub-field missing `name`, or `_key` sub-field missing. |
| New custom control not rendering | Control class not registered in `Customify_Customizer::register_controls()`. |

### 16.2 JavaScript / live preview

| Symptom | Likely cause |
|---|---|
| Change only shows after publish | `transport` is `'refresh'` instead of `'postMessage'`. |
| Live preview works for color but not your new type | `auto-css.js` has no per-type handler for it. Add a method in §8.2. |
| Preview drifts from frontend on tablet | Breakpoint mismatch between `class-customizer-auto-css.php` and `auto-css.js`. Sync both. |
| Repeater reorder doesn't preview | Missing `render_callback`, OR partial not added to `selective_refresh`. |
| React control doesn't render | Mount glue (`observeAndMount`) not imported into `control.js`, or root DOM node not present yet. |
| Header Builder V2 doesn't show | `header_builder_version` setting still on `v1`; or `header-builder.js` not enqueued on `customize_controls_enqueue_scripts`. |
| Double-render / infinite loop on React control | Missing `control._customifyWriting` flag during `setting.set()` — the `.bind()` echo triggers re-render. |
| New JS entry 404s after build | Added to `webpack.config.js` but didn't run `npm run build`. `assets/build/` is gitignored — must rebuild. |

---

## 17. Where to look next

**Related specs**
- [`SPEC-header-footer-builder.md`](./SPEC-header-footer-builder.md) — Header & Footer Builder dedicated spec.
- [`SPEC-customizer-colors.md`](./SPEC-customizer-colors.md) — 6-slot palette, `:root` tokens, picker UI.
- [`SPEC-bootstrap.md`](./SPEC-bootstrap.md) — singleton + layout system the Customizer plugs into.
- [`SPEC-asset-pipeline.md`](./SPEC-asset-pipeline.md) — webpack entries + `customify-style` handle.
- [`SPEC-block-editor.md`](./SPEC-block-editor.md) — Customizer → block editor CSS bridge (`Customify_Editor`).
- [`SPEC-data-migration-policy.md`](./SPEC-data-migration-policy.md) — canonical key registry + migration patterns.

**PHP**
- [`inc/customizer/class-customizer.php`](../inc/customizer/class-customizer.php) — registration internals.
- [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) — the CSS pipeline.
- [`inc/customizer/configs/styling.php`](../inc/customizer/configs/styling.php) — model for color group config.
- [`inc/customizer/configs/typography.php`](../inc/customizer/configs/typography.php) — model for `typography` control.
- [`inc/customizer/configs/single-blog-post.php`](../inc/customizer/configs/single-blog-post.php) — model for fixed-list repeater.
- [`inc/customizer/configs/header/`](../inc/customizer/configs/header/) — header builder items (see SPEC-header-footer-builder for depth).
- [`inc/panel-builder/class-panel-builder.php`](../inc/panel-builder/class-panel-builder.php) — builder bootstrap.
- [`inc/admin/editor.php`](../inc/admin/editor.php) — Customizer → block editor bridge.

**JavaScript**
- [`src/backend/customizer/js/auto-css.js`](../src/backend/customizer/js/auto-css.js) — preview-side CSS generator (JS twin of `class-customizer-auto-css.php`).
- [`src/backend/customizer/js/customizer.js`](../src/backend/customizer/js/customizer.js) — preview-side selective refresh + header panel.
- [`src/backend/customizer/js/control.js`](../src/backend/customizer/js/control.js) — controls-panel-side custom controls + Backbone overrides.
- [`src/backend/customizer/js/controls/columns-settings/`](../src/backend/customizer/js/controls/columns-settings/) — canonical pattern for React-based custom controls.
- [`src/backend/header-builder/index.js`](../src/backend/header-builder/index.js) — React V2 Header Builder entry.
- [`src/backend/footer-builder/index.js`](../src/backend/footer-builder/index.js) — React V2 Footer Builder entry.
- [`webpack.config.js`](../webpack.config.js) — `entries` object lists every Customizer JS bundle.
