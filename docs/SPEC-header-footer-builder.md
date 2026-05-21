# SPEC — Header & Footer Builder

Canonical reference for the Customify Header / Footer WYSIWYG builder: class hierarchy, per-item registration, storage shape, the frontend render pipeline, mobile sidebar behaviour, and the full extension surface.

Builder primer in the main Customizer spec: see [`SPEC-customizer.md` §11](./SPEC-customizer.md). This file is the depth reference — the main spec stays short and points here.

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The builder is a Customizer-only UI that lets users compose the site header and footer by dragging items (logo, menu, search, social icons, …) into rows and columns. A React canvas inside the Customizer writes a JSON tree into a single theme_mod per builder, and a PHP renderer turns that tree into HTML on every page load.

| Surface | Role |
|---|---|
| Customizer canvas (React) | User-facing — picks items, drags into rows/columns |
| `theme_mod` storage | Single setting per builder holds the entire layout tree |
| Frontend renderer (PHP) | Reads the theme_mod, dispatches to each registered item's `render()` |
| Item class | One class per logical item (Logo, Primary Menu, Search Icon, …) — owns metadata + settings + render |

The builder integrates with the rest of Customify through the same `customify/customizer/config` filter — each item's settings are normal config items, not a parallel registry. The builder is "just" a layout layer on top of standard Customizer settings.

---

## 2. Class hierarchy

All classes live in `inc/panel-builder/` except per-item subclasses, which live in `inc/customizer/configs/header/` and `inc/customizer/configs/footer/`.

```
Customify_Panel_Builder
  └── boots the system; wires customify/site-start + customify/site-end actions

Customify_Customize_Builder_Panel              (abstract)
  ├── Customify_Builder_Header                 (defines header rows + items)
  └── Customify_Builder_Footer                 (defines footer rows + items)

Customify_Customize_Layout_Builder             (central registry)
  ├── register_builder( $id, $instance )       — Header / Footer
  ├── register_item( $builder_id, $instance )  — Logo, Menu, Search, …
  ├── generates Customizer panels/sections from registered items
  └── handles AJAX save/export of layout templates

Customify_Abstract_Layout_Frontend             (abstract render base)
  └── Customify_Layout_Builder_Frontend_V2     — active renderer (class name kept for historical reasons)

Customify_Builder_Item_*                       (one class per item)
  ├── Customify_Builder_Item_Logo
  ├── Customify_Builder_Item_Primary_Menu
  ├── Customify_Builder_Item_Search_Icon
  └── … many more under configs/header/, configs/footer/
```

### 2.1 Abstract contract for items

Every `Customify_Builder_Item_*` class implements three methods:

| Method | Returns / does | Required |
|---|---|---|
| `item()` | Item metadata array: `id`, `name`, `icon`, `desc`, `builder` | ✓ |
| `customize()` | Registers Customizer settings (typically via `Customify_Customizer::get_instance()->add_field()` or by hooking `customify/customizer/config`) | ✓ |
| `render()` | Echoes the item's frontend HTML | ✓ |

Items register themselves at the bottom of their own file:

```php
Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Logo() );
```

### 2.2 Abstract contract for builder panels

`Customify_Customize_Builder_Panel` subclasses implement:

| Method | Returns | Notes |
|---|---|---|
| `get_config()` | Panel metadata: `id`, `title`, `panel`, `version_id`, `versions[]`, `devices`, `react_control_id` | The React canvas reads `react_control_id` to know which setting to write to. |
| `get_rows_config()` | `array( row_id => row_label )` | Header: `top`, `main`, `bottom`, `sidebar`. Footer: `main`, `bottom`. |
| `get_items()` | Array of items registered for this builder | Auto-populated from `register_item()` calls. |
| `customize( $wp_customize )` | Adds the builder UI control to the Customizer | Called during `customize_register`. |

---

## 3. Builder concepts

### 3.1 Builder

Top-level container (Header or Footer). Each builder owns:
- A set of **rows** (`top`, `main`, `bottom`, optionally `sidebar` for header)
- A registry of available **items** (only items registered to this builder ID show up in the canvas)
- A single `theme_mod` that stores the entire layout tree

### 3.2 Row

A horizontal band of the header/footer. Header has 3 visible rows + 1 mobile-only sidebar:

| Row id | Header label | Footer label | Notes |
|---|---|---|---|
| `top` | Header Top | — | Optional thin band above the main row |
| `main` | Header Main | Footer Main | The primary row |
| `bottom` | Header Bottom | Footer Bottom | Optional secondary row |
| `sidebar` | Menu Sidebar | — | Header only, mobile only — see §8 |

Override row availability per-builder by subclassing `get_rows_config()`.

### 3.3 Column slots

Each row is divided into a fixed set of column slots. Items live inside a slot; order within the slot is preserved. The slot count differs between header and footer:

| Builder | Slots per row | Slot ids |
|---|---|---|
| **Header** | 3 | `left`, `center`, `right` |
| **Footer** | 5 | `left`, `center`, `right`, `col4`, `col5` |

The header is intentionally constrained to 3 slots — header content needs to remain scannable across viewport widths, and the React canvas surfaces a tighter 3-column drop target. The footer is wider and supports up to 5 columns for richer multi-column footer layouts (e.g. About / Quick Links / Categories / Newsletter / Contact).

The mobile sidebar (header-only special row) does NOT use column slots — see §8.

### 3.4 Item

A reusable component (logo, menu, search icon, social, button, HTML block, …). Each item is a PHP class that registers its metadata, Customizer settings, and render callback.

---

## 4. Storage model

Each builder persists its layout in a single `theme_mod`:

| Builder | `theme_mod` key |
|---|---|
| Header | `header_builder_panel_v2` |
| Footer | `footer_builder_panel_v2` |

The value is a nested array indexed by **device → row → slot → items**. Items in a slot are stored as ordered arrays of `array( 'id' => '<item-id>' )` records. The sanitize callback accepts items as either URL-encoded JSON strings OR decoded PHP arrays — both forms are normalized on read.

### 4.1 Header shape (3 slots per row + mobile sidebar)

```php
array(
    'desktop' => array(
        'top'    => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
        'main'   => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
        'bottom' => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
    ),
    'mobile'  => array(
        'top'     => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
        'main'    => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
        'bottom'  => array( 'left' => [...], 'center' => [...], 'right' => [...] ),
        'sidebar' => array( 'sidebar' => [
            array( 'id' => 'logo' ),
            array( 'id' => 'primary-menu' ),
        ] ),
    ),
)
```

The `sidebar` row is mobile-only and uses a single flat slot named `sidebar` (no left/center/right) — see §8 for the rendering behaviour.

### 4.2 Footer shape (5 slots per row, no sidebar)

```php
array(
    'desktop' => array(
        'main'   => array(
            'left'   => [...],
            'center' => [...],
            'right'  => [...],
            'col4'   => [...],
            'col5'   => [...],
        ),
        'bottom' => array(
            'left' => [...], 'center' => [...], 'right' => [...], 'col4' => [...], 'col5' => [...],
        ),
    ),
    'mobile'  => array(
        'main'   => array( /* same 5-slot shape */ ),
        'bottom' => array( /* same 5-slot shape */ ),
    ),
)
```

No mobile sidebar row — the footer renders a single responsive grid (see §5.3).

---

## 5. Render pipeline

### 5.1 Entry points

```php
add_action( 'customify/site-start', 'customify_customize_render_header' );
add_action( 'customify/site-end',   'customify_customize_render_footer' );
```

`customify_customize_render_header()` (in `inc/panel-builder/builder-functions.php`) is the canonical entry:

```php
function customify_customize_render_header() {
    do_action( 'customify/before-header' );

    $builder = Customify_Layout_Builder_Frontend_V2();
    $builder->set_control_id( 'header_builder_panel_v2' );
    $builder->render();

    do_action( 'customify/after-header' );
}
```

### 5.2 Call chain

1. `render()` loops the configured rows for the builder (header: `top`, `main`, `bottom`; footer: `main`, `bottom`).
2. For each row, fetches both `desktop` and `mobile` settings via `get_row_settings( $row_id, $device )`.
3. `render_row()` iterates the builder's column slots in order — 3 for header (`left`, `center`, `right`), 5 for footer (`left`, `center`, `right`, `col4`, `col5`).
4. For each item in each slot, calls `get_render_item( $item_id )` which dispatches to the registered item instance's `render()` method.
5. Each item render is bracketed by:
   - `do_action( "customify/builder/<builder>/before-item/{$item_id}" )`
   - `do_action( "customify/builder/<builder>/after-item/{$item_id}" )`
6. For the header only, the mobile sidebar (`render_mobile_sidebar()`) is appended at the end if any sidebar items exist or in Customizer preview.

### 5.3 Device strategy

**Header renders BOTH desktop and mobile markup**, wrapped in:

```html
<div class="customify-grid hide-on-mobile"> … desktop markup … </div>
<div class="customify-grid hide-on-desktop"> … mobile markup … </div>
```

CSS toggles visibility per viewport. This double-render is intentional — it lets users put completely different items in mobile vs desktop slots without a JS-driven swap.

**Footer renders a single responsive grid** and relies on pure CSS media queries — no per-device markup. Footer is generally simpler (no sidebar, no off-canvas).

---

## 6. Adding a new item — canonical recipe

### Step 1 — Create the item file

`inc/customizer/configs/header/badge.php`:

```php
<?php
defined( 'ABSPATH' ) || exit;

class Customify_Builder_Item_Badge {

    public function item() {
        return array(
            'id'      => 'badge',
            'name'    => __( 'Badge', 'customify' ),
            'icon'    => 'dashicons-flag',
            'desc'    => __( 'Small text badge with link.', 'customify' ),
            'builder' => 'header',
        );
    }

    public function customize() {
        add_filter( 'customify/customizer/config', function ( $items ) {
            $items[] = array(
                'name'    => 'header_badge_section',
                'type'    => 'section',
                'panel'   => 'header_settings',
                'title'   => __( 'Badge', 'customify' ),
            );
            $items[] = array(
                'name'       => 'header_badge_text',
                'type'       => 'text',
                'section'    => 'header_badge_section',
                'title'      => __( 'Text', 'customify' ),
                'default'    => __( 'New', 'customify' ),
                'selector'   => '.header-badge',
                'css_format' => 'format',
            );
            $items[] = array(
                'name'       => 'header_badge_color',
                'type'       => 'color',
                'section'    => 'header_badge_section',
                'title'      => __( 'Color', 'customify' ),
                'default'    => '#ff7a00',
                'selector'   => '.header-badge',
                'css_format' => 'background-color: {{value}};',
            );
            return $items;
        } );
    }

    public function render() {
        $text = Customify()->get_setting( 'header_badge_text' );
        if ( ! $text ) {
            return;
        }
        printf( '<span class="header-badge">%s</span>', esc_html( $text ) );
    }
}

// Register
Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Badge() );
```

### Step 2 — Include the file

Make sure `inc/panel-builder/class-panel-builder.php` (or the header loader) requires every PHP file in `inc/customizer/configs/header/`. Most existing items are auto-loaded — verify before duplicating.

### Step 3 — That's it

- The item appears in the React canvas under its `name` + `icon`.
- Users drag it into any row/column slot.
- The frontend renderer dispatches to `render()` at the correct position.
- The new section `header_badge_section` appears inside the `header_settings` panel.

No JS file needed unless the item has interactive behaviour (a toggle, dropdown, popup).

---

## 7. Per-item settings convention

The main spec previously claimed every header item prefixes its settings `header_<item>_`. **This is incorrect.** Real items mix conventions:

| Item | Prefix used | Example field name |
|---|---|---|
| Logo | mixed | `logo_max_width` (bare), `header_logo_retina` (prefixed) |
| Nav Icon | bare | `nav_icon_*` |
| Search Icon | bare | `search_icon_*` |
| Primary Menu | bare | `primary_menu_style`, `primary_menu_style_border_h` |

### Recommendation for new items

- **Always prefix with `header_<item>_` or `footer_<item>_`.** Bare names are historical accidents that risk collision with other config keys (or with Customify Pro additions).
- If you need to mirror an old bare name for back-compat, register both — the prefixed one as canonical, the bare one as a deprecated alias.

### Section naming

Each item typically owns a dedicated Customizer section. Convention:

| Builder | Section name |
|---|---|
| Header item | `header_<item>` (e.g. `header_menu_primary`) — sits inside `header_settings` panel |
| Footer item | `footer_<item>` — sits inside `footer_settings` panel |

Some items reuse core sections (e.g. Logo writes to `title_tagline`, WP's built-in site identity section) — only do this when the settings overlap with WP core's existing UI.

---

## 8. Mobile sidebar (header only)

The `sidebar` row is special: it renders **only on mobile**, as an off-canvas panel triggered by the Nav Icon (hamburger).

### 8.1 When and how it renders

```php
public function render_mobile_sidebar() {
    $mobile_items = $this->get_row_settings( 'sidebar', 'mobile' );
    if ( ! empty( $mobile_items ) || is_customize_preview() ) {
        // Emit #header-menu-sidebar wrapper with #header-menu-sidebar-inner inside.
    }
}
```

- Wrapper: `#header-menu-sidebar`
- Inner scroll container: `#header-menu-sidebar-inner`
- Single-column stacked layout — does NOT use the 5-slot column model

### 8.2 Sidebar-specific settings

Standard Customify settings that govern sidebar appearance:

| Setting | Type | Purpose |
|---|---|---|
| `header_sidebar_animate` | radio | `slide-left`, `slide-right`, `overlay`, `dropdown` — how the panel enters |
| `header_sidebar_skin_mode` | radio | `dark-mode` / `light-mode` |
| `header_sidebar_styling` | styling (modal) | Color, background, padding, border |
| `header_sidebar_menu_no_duplicator` | checkbox | If true, sub-menus don't re-display their parent item |

### 8.3 Triggering open

The Nav Icon item (`Customify_Builder_Item_Nav_Icon`) renders the hamburger button. Frontend JS (`src/frontend/js/theme.js`) listens for the button click + outside-click to toggle the sidebar's `is-open` class.

Known unsafe spots in that JS (also flagged in CLAUDE.md):
- `menuSidebar.contains(e.target)` — no null guard on `menuSidebar`
- `menuSidebarInner.getBoundingClientRect()` — no null guard

Fix these before adding new sidebar-related JS nearby.

---

## 9. Column settings — per-row CSS layer

The layout grid (§4) decides **which** items go in **which** column slot. Column settings decide **how each slot's container is styled** — its flex direction, alignment, gap, and padding. The two are complementary: layout grid is item placement, column settings is per-column CSS.

One `columns_settings` field exists for every builder row.

### 9.1 Concept

A `columns_settings` field is a custom control type (`'type' => 'columns_settings'`) that exposes per-column knobs in the Customizer. It writes a single `theme_mod` that is later consumed by the auto-CSS pipeline to emit flexbox CSS scoped to that row's slot selectors.

The control is a React component that mounts via the same `observeAndMount` pattern documented in [`SPEC-customizer.md` §9.2](./SPEC-customizer.md#92-react-style-controls-modern).

### 9.2 Per-row naming convention

Every row that supports column styling has a `{section}_columns_settings` field:

| Row | Setting key | Column keys | Target selector |
|---|---|---|---|
| Header top | `header_top_columns_settings` | `left`, `center`, `right` | `.header--row.header-top .col-v2-{key}` |
| Header main | `header_main_columns_settings` | `left`, `center`, `right` | `.header--row.header-main .col-v2-{key}` |
| Header bottom | `header_bottom_columns_settings` | `left`, `center`, `right` | `.header--row.header-bottom .col-v2-{key}` |
| Header sidebar | `header_sidebar_columns_settings` | `sidebar` (single slot) | `#header-menu-sidebar-inner` (forced) |
| Footer main | `footer_main_columns_settings` | `left`, `center`, `right`, `col4`, `col5` | row-specific selector |
| Footer bottom | `footer_bottom_columns_settings` | `left`, `center`, `right`, `col4`, `col5` | row-specific selector |

### 9.3 Config item shape

```php
array(
    'name'               => 'footer_main_columns_settings',
    'type'               => 'columns_settings',
    'section'            => 'footer_main',
    'priority'           => 999,
    'title'              => __( 'Column Settings', 'customify' ),
    'description'        => __( 'Per-column layout, gap and padding.', 'customify' ),
    'col_layout_setting' => 'footer_main_col_layout',          // optional — links to a row_layout
    'column_keys'        => array( 'left', 'center', 'right', 'col4', 'col5' ),
    'default_layout'     => 'stack',                            // field-level default for all columns
    'selector'           => '#cb-row--footer-main',
    'css_format'         => 'columns_settings',
    'sanitize_callback'  => 'customify_sanitize_columns_settings',
)
```

Extra keys specific to `columns_settings`:

| Key | Type | Purpose |
|---|---|---|
| `column_keys` | array | Slot ids managed by this field. Header = 3, footer = 5, sidebar = 1. |
| `col_layout_setting` | string | Optional — name of a `row_layout` setting that controls active column count (drives position-based defaults). |
| `default_layout` | string | Field-level default layout applied to all columns when user hasn't saved a value. Overrides position-based default. |
| `forced_layout` | string | Locks every column to this layout regardless of user input (used for sidebar = `'stack'`). |
| `hide_layout` | bool | If true, the React UI hides the Layout button group entirely. Pair with `forced_layout`. |
| `col_selectors` | array | Per-column selector overrides when markup doesn't follow `.col-v2-{key}`. E.g. `array( 'sidebar' => '#header-menu-sidebar-inner' )`. |

### 9.4 Data shape (saved value)

Stored as URL-encoded JSON in the `theme_mod`. Decoded shape:

```php
array(
    'desktop' => array(
        'left' => array(
            'layout'  => 'flex-start',                                                       // enum
            'gap'     => array( 'unit' => 'em', 'value' => 1 ),
            'padding' => array( 'unit' => 'em', 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'link' => 1 ),
        ),
        'center' => array( /* same shape */ ),
        'right'  => array( /* same shape */ ),
    ),
    'mobile' => array(
        'left'   => array( /* same shape */ ),
        'center' => array( /* same shape */ ),
        'right'  => array( /* same shape */ ),
    ),
)
```

**Empty-in-DB is normal.** Columns the user never touched are absent or have empty sub-fields. The render-time resolver (§9.6) fills them in.

### 9.5 Per-column sub-fields

| Sub-field | Type | Values | Default | Device-aware |
|---|---|---|---|---|
| `layout` | enum | `flex-start`, `flex-center`, `flex-end`, `space-between`, `stack` | position-based (§9.6) | ✓ |
| `gap` | slider + unit | `value`: 0–100 (em steps 0.1, px steps 1); `unit`: `em`, `px` | `1em` | ✓ |
| `padding` | css_ruler (4-sided) | per side numeric; `unit`: `em`, `px`; `link`: 0/1 | all empty | ✓ |

Layout → CSS mapping at render time:

| Layout | Emitted CSS |
|---|---|
| `flex-start` | `display: flex; flex-direction: row; justify-content: flex-start; align-items: center;` |
| `flex-center` | `display: flex; flex-direction: row; justify-content: center; align-items: center;` |
| `flex-end` | `display: flex; flex-direction: row; justify-content: flex-end; align-items: center;` |
| `space-between` | `display: flex; flex-direction: row; justify-content: space-between; align-items: center;` |
| `stack` | `display: flex; flex-direction: column;` |

### 9.6 Layout resolution order

When generating CSS for a column, the resolver picks the layout from the first matching source:

1. `forced_layout` (field-level lock) → used regardless of anything else.
2. Saved value `value[device][col].layout` → user's explicit choice.
3. `default_layout` (field-level default) → falls back here if user hasn't chosen.
4. **Position-based default** (inferred from column position):
   - Single active column → `flex-start`
   - First column → `flex-start`
   - Last column → `flex-end`
   - Middle columns → `flex-center`

This rule is implemented identically in BOTH places — PHP `class-customizer-auto-css.php::columns_settings()` AND React `index.jsx::defaultLayoutFor()`. **If you change one side, you MUST change the other** — otherwise the highlighted button in the Customizer UI won't match the actual rendered alignment.

### 9.7 Active column count

The number of "active" columns drives the position-based default. Source priority:

1. If `col_layout_setting` points to a `row_layout` field that stores `{ count: N }`, active count = `min( count(column_keys), N )`.
2. Otherwise active count = `count( column_keys )` (all slots).

Example: footer with `column_keys: [left, center, right, col4, col5]` but `footer_main_col_layout.count = 3` → only the first 3 slots are active; the last column in the active set (`right`) gets the `flex-end` position default.

### 9.8 CSS selector resolution

For each column, the selector emitted into CSS is:

```
{row_selector} .col-v2-{col_key}
```

Override per column via `col_selectors`:

```php
'col_selectors' => array(
    'sidebar' => '#header-menu-sidebar-inner',
),
```

Used when markup doesn't follow the `.col-v2-{key}` pattern (off-canvas sidebar is the canonical case).

### 9.9 Frontend → Customizer state bridge (React)

The React component (`src/backend/customizer/js/controls/columns-settings/index.jsx`) uses `useCustomizeSetting()`:

```js
function useCustomizeSetting( controlId, defaultValue ) {
    const [ value, setLocal ] = useState( /* read wp.customize on mount */ );
    useEffect( () => {
        wp.customize( controlId ).bind( handler );
        return () => wp.customize( controlId ).unbind( handler );
    }, [ controlId ] );

    function setValue( next ) {
        // Set _customifyWriting flag to prevent .bind() echo
        wp.customize( controlId ).set( encodeURIComponent( JSON.stringify( next ) ) );
    }
    return [ value, setValue ];
}
```

The same `_customifyWriting` flag pattern documented in [`SPEC-customizer.md` §9.2](./SPEC-customizer.md#92-react-style-controls-modern). Sanitize callback `customify_sanitize_columns_settings` accepts both URL-encoded strings AND already-decoded arrays.

### 9.10 Adding column settings to a new row

If you add a new row to header or footer, you SHOULD also register a `columns_settings` field for it — otherwise the row renders with no flex container at all.

```php
add_filter( 'customify/customizer/config', function ( $items ) {
    $items[] = array(
        'name'              => 'header_mynewrow_columns_settings',
        'type'              => 'columns_settings',
        'section'           => 'header_mynewrow',
        'priority'           => 999,
        'title'             => __( 'Column Settings', 'customify' ),
        'description'       => __( 'Per-column layout, gap and padding.', 'customify' ),
        'column_keys'       => array( 'left', 'center', 'right' ),
        'selector'          => '.header--row.header-mynewrow',
        'css_format'        => 'columns_settings',
        'sanitize_callback' => 'customify_sanitize_columns_settings',
    );
    return $items;
} );
```

The setting auto-wires into the auto-CSS pipeline via `'css_format' => 'columns_settings'` — no further code needed.

---

## 10. Customizer UI integration

### 10.1 The builder control

The React canvas is exposed as a custom Customizer control with `type: 'js_raw'`. It writes its state to:

- Header: `theme_mod 'header_builder_panel_v2'`
- Footer: `theme_mod 'footer_builder_panel_v2'`

### 10.2 JS enqueue

`Customify_Customize_Layout_Builder::scripts()` enqueues:

| Handle | Built path |
|---|---|
| `customify-header-builder` | `build/js/backend/header-builder.js` |
| `customify-footer-builder` | `build/js/backend/footer-builder.js` |

Both rely on the matching `*.asset.php` sidecar for dependencies + version.

### 10.3 PHP → JS bridge

Localized to `window.Customify_Layout_Builder`:

```js
window.Customify_Layout_Builder = {
    builders: {
        header: { /* config from Customify_Builder_Header::get_config() */ },
        footer: { /* config from Customify_Builder_Footer::get_config() */ },
    },
    nonce:  '…',
    is_rtl: false,
}
```

The React components read this to render the canvas.

### 10.4 Mount point

The React builder mounts into `body .wp-full-overlay` on `wp.customize.bind('ready')`. It's NOT a normal inline control — it's a full-screen overlay activated when the user opens the Header / Footer builder section.

See [`SPEC-customizer.md` §9.3](./SPEC-customizer.md#93-react-style-builders-header--footer) for the cross-references with the JS architecture.

---

## 11. Hooks & filters catalog

### 11.1 Registration phase

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/customize-builder/init` | action | — | Extension point — register additional builders BEFORE Header/Footer load |
| `customify/builder/<builder>/rows` | filter | `array $rows` | Override the rows list for a builder |
| `customify/builder/<builder>/items` | filter | `array $items` | Override the items registry for a builder |

### 11.2 Render phase

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/before-header` | action | — | Output BEFORE `<header>` tag |
| `customify/after-header` | action | — | Output AFTER `</header>` tag |
| `customify/before-footer` | action | — | Output BEFORE `<footer>` tag |
| `customify/after-footer` | action | — | Output AFTER `</footer>` tag |
| `customify/builder/row-classes` | filter | `array $classes, string $row, string $builder` | Add/remove row wrapper classes |
| `customify/builder/row-attrs` | filter | `array $attrs, string $row, string $builder` | Add custom HTML attrs on row wrapper |
| `customify/builder/inner-row-classes` | filter | `array $classes, string $row, string $builder` | Modify inner grid classes |
| `customify/builder/<builder>/before-item/<item>` | action | — | Inject content immediately before an item's render |
| `customify/builder/<builder>/after-item/<item>` | action | — | Inject content immediately after an item's render |

### 11.3 Item-specific hooks

Items often expose their own filter for their settings array (e.g. `customify/builder/header/logo-settings`) so child themes / Customify Pro can extend them without forking. Look at the item file for the exact hook name.

---

## 12. Troubleshooting

### 12.1 Items & rendering

| Symptom | Likely cause |
|---|---|
| Item doesn't appear in canvas | `register_item()` not called, or `builder` field in `item()` metadata doesn't match (`'header'` vs `'footer'`) |
| Item appears but renders nothing on frontend | `render()` returns early because a required setting is empty — set a sensible default |
| Item renders desktop but not mobile (header) | Header double-renders — item exists in `desktop.<row>.<slot>` but not `mobile.<row>.<slot>`. Add it to the mobile slot in the canvas. |
| Mobile sidebar doesn't open | Nav Icon item not added to a mobile row, OR `theme.js` JS error (check `menuSidebar` null guard) |
| Sidebar styling broken | `header_sidebar_styling` field's `selector` doesn't match `#header-menu-sidebar` or `#header-menu-sidebar-inner` |
| Footer items in `col4` / `col5` not visible | Theme CSS expecting only 3 columns. Ensure the row's flex/grid rule sizes to 5 children. |
| New item's section not visible in Customizer | Missing `panel` key in the section config, or panel name typo (`header_settings`) |
| Filter `customify/builder/header/items` doesn't filter anything | Hooked too late — must run before `customify/customize-builder/init` |
| React canvas blank | `window.Customify_Layout_Builder` undefined → enqueue didn't fire. Check `Customify_Customize_Layout_Builder::scripts()` hooks. |

### 12.2 Column settings

| Symptom | Likely cause |
|---|---|
| Column has no flex container at all | Row missing a `columns_settings` field. Register one (§9.10). |
| Highlighted layout button in Customizer differs from actual frontend alignment | Position-based default rule out of sync between PHP and React (§9.6). Verify `defaultLayoutFor()` and `columns_settings()` use the same logic. |
| Column gap or padding doesn't apply | Slot selector mismatch — auto-CSS emits `{row_selector} .col-v2-{key}` but markup uses different class. Provide `col_selectors` override. |
| Sidebar column shows layout buttons in UI | Missing `hide_layout: true` on the sidebar `columns_settings` field. Pair with `forced_layout: 'stack'`. |
| Footer columns ignore `col_layout_setting.count` | `col_layout_setting` value not a valid `row_layout` — must be a setting name that stores `{ count: N, ... }`. |
| Saved column settings lost on next page load | Sanitize callback missing → raw value rejected by Customizer save flow. Always set `'sanitize_callback' => 'customify_sanitize_columns_settings'`. |

---

## 13. Where to look next

**Boot + registry**
- [`inc/panel-builder/class-panel-builder.php`](../inc/panel-builder/class-panel-builder.php) — `Customify_Panel_Builder`, top-level bootstrap.
- [`inc/panel-builder/class-customize-layout-builder.php`](../inc/panel-builder/class-customize-layout-builder.php) — central registry, AJAX template handlers.
- [`inc/panel-builder/builder-functions.php`](../inc/panel-builder/builder-functions.php) — `customify_customize_render_header()`, `customify_customize_render_footer()`.

**Render**
- [`inc/panel-builder/class-customize-layout-builder-frontend-v2.php`](../inc/panel-builder/class-customize-layout-builder-frontend-v2.php) — active frontend renderer.

**Column settings**
- [`inc/customizer/controls/class-control-columns-settings.php`](../inc/customizer/controls/class-control-columns-settings.php) — PHP control class + mount template.
- [`src/backend/customizer/js/controls/columns-settings/index.jsx`](../src/backend/customizer/js/controls/columns-settings/index.jsx) — React component + `useCustomizeSetting` bridge.
- [`inc/customizer/class-customizer-auto-css.php`](../inc/customizer/class-customizer-auto-css.php) — `columns_settings()` method, layout resolution + CSS emission.

**Builder panels**
- [`inc/customizer/configs/header/panel.php`](../inc/customizer/configs/header/panel.php) — `Customify_Builder_Header`, header rows.
- [`inc/customizer/configs/footer/panel.php`](../inc/customizer/configs/footer/panel.php) — `Customify_Builder_Footer`, footer rows.

**Reference items (models for new ones)**
- [`inc/customizer/configs/header/logo.php`](../inc/customizer/configs/header/logo.php) — simple item with section reuse (`title_tagline`).
- [`inc/customizer/configs/header/menus.php`](../inc/customizer/configs/header/menus.php) — complex item with many settings.
- [`inc/customizer/configs/header/search-icon.php`](../inc/customizer/configs/header/search-icon.php) — interactive item (toggle + form).
- [`inc/customizer/configs/header/nav-icon.php`](../inc/customizer/configs/header/nav-icon.php) — the hamburger that opens the sidebar.

**Customizer integration**
- [`SPEC-customizer.md`](./SPEC-customizer.md) — main Customizer spec; §11 has the short reference back to this file.
- [`src/backend/header-builder/index.js`](../src/backend/header-builder/index.js) — React header builder entry.
- [`src/backend/footer-builder/index.js`](../src/backend/footer-builder/index.js) — React footer builder entry.

**Conventions**
- [`CLAUDE.md`](../CLAUDE.md) — project-wide rules (English-only, no function deletions, CSS handle naming).
