# SPEC — Header Transparent

Canonical reference for the **Transparent Header** feature: when it activates, the full conditional chain, Customizer + per-page metabox overrides, CSS / class output, and the Pro-plugin handoff.

Related references:
- Builder primer in [`SPEC-header-footer-builder.md`](./SPEC-header-footer-builder.md) — the layout builder this feature plugs into.
- Customizer panel layout in [`SPEC-customizer.md`](./SPEC-customizer.md).

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

The Transparent Header feature lets the `<header>` overlay the first content block of a page (typically a hero / cover) with a fully transparent background, so background imagery shows through. It is a **read-only port** of the Pro plugin's `header-transparent` module, hosted in the Free theme so the feature exists out-of-the-box. When Customify Pro is active the Pro module takes over and the theme port stays dormant.

Two things matter most when reasoning about it:

1. It is a **filter** layered on top of the existing header builder render — it does not own the header markup, it only contributes body / row CSS classes and a transparent-logo `<img>`.
2. The decision "is the header transparent on **this** request?" is a **three-tier conditional** with a cached result. Misreading the tier order produces almost every support bug in this area.

| Surface | Role |
|---|---|
| Customizer section `header_transparent` | User-facing settings (row toggles, exclusion list, logo) |
| Per-page metabox `header_transparent_display` | Per-post override (`default` / `show` / `hide`) |
| `Customify_Header_Transparent::is_transparent()` | Single source of truth — every other call defers to this |
| Frontend filters | `body_class`, `customify/builder/row-classes`, `customify/logo-classes` |
| Action `customizer/after-logo-img` | Emits `<img class="site-img-logo-tran">` next to the standard logo |
| SCSS | `_header_transparent.scss` — position, background, logo swap |

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/customizer/configs/header/transparent.php`](../inc/customizer/configs/header/transparent.php) | 414 | `Customify_Header_Transparent` class — all PHP logic |
| [`src/frontend/scss/header/_header_transparent.scss`](../src/frontend/scss/header/_header_transparent.scss) | ~48 | Position, background, logo swap |
| [`inc/class-metabox.php`](../inc/class-metabox.php) | 125–137 | Per-page metabox field registration |
| [`inc/class-customify.php`](../inc/class-customify.php) | (compat loader) | Includes the transparent config file via `customify/customizer/config` filter |

Bootstrapping is gated by Pro detection at `after_setup_theme:30` ([`transparent.php:404-413`](../inc/customizer/configs/header/transparent.php#L404-L413)):

```php
add_action( 'after_setup_theme', function () {
    if ( class_exists( 'Customify_Pro_Module_Header_Transparent' ) ) {
        return;
    }
    Customify_Header_Transparent::get_instance();
}, 30 );
```

---

## 3. Customizer settings

All settings live under panel `header_settings`, section `header_transparent` ("Transparent Header").

### 3.1 Per-row settings (repeated for `top`, `main`, `bottom`)

| Setting | Type | Purpose |
|---|---|---|
| `header_{row}_transparent_h` | heading | Section divider label |
| `header_{row}_transparent` | checkbox | **Enable transparent on this row** |
| `header_{row}_transparent_styling` | styling | Background + border + colors; targets `.header--row.header-{row}.header--transparent .customify-container, …` |

The styling control enables `text_color` and disables `padding`, `margin`, `link_color`, `bg_heading`, `bg_cover`, `bg_image`, `bg_repeat`, `border_radius`, `box_shadow`, and hover state — it is a deliberately minimal styling form because the row layout fields are owned by the builder's per-row settings.

`text_color` uses a dedicated `#masthead:not(.sticky-active) .header--row.header-{row}.header--transparent …` selector list. It targets the row text plus each builder item's primary link/button, site title, top-level menu links, social/search/nav/cart icons, HTML/contact/icon-box links, CTA buttons, and search-box text. The ID-scoped selector deliberately outranks skin-mode and per-item normal/hover/active colors. It stops matching during `.sticky-active`, so sticky-header colors remain the only higher-priority color context. Dropdown/submenu links are intentionally not included.

The regular `Header Top/Main/Bottom → Advanced Styling` rule also targets the transparent row's layout surface. When a Transparent Styling sub-field is empty, it emits no CSS and the regular row value remains visible and live-previewable. An explicit Transparent Styling value uses the ID-scoped, non-sticky selector above and overrides the inherited row value. During `.sticky-active`, the explicit Transparent rule stops matching and the regular row styling becomes the fallback.

### 3.2 Global settings

| Setting | Type | Purpose |
|---|---|---|
| `header_transparent_display_h` | heading | "Advanced Settings" label |
| `header_transparent_display_pages` | modal (one tab: `display`) | Per-page-type exclusion checklist (see §4.2) |
| `header_logo_tran` | image | Transparent logo (light variant for dark hero) |
| `header_logo_tran_retina` | image | 2x logo |
| `logo_tran_max_width` | slider (per-device) | Max-width on `img.site-img-logo-tran` |

The two logo images use `render_callback` pointed at the Logo builder item's `render` method so the Customizer preview re-renders the logo block on change instead of reloading.

### 3.3 Modal display fields (`header_transparent_display_pages` → tab `display`)

Always present:

- `index` — Disable on index
- `category` — Disable on categories
- `search` — Disable on search
- `archive` — Disable on archive
- `page` — Disable on single page
- `post` — Disable on single post
- `singular` — Disable on singular
- `page_404` — Disable on 404 page

Added when `Customify()->is_woocommerce_active()` returns true:

- `product` — Disable on product page
- `product_cat` — Disable on product category
- `product_tag` — Disable on product tag

---

## 4. Display decision — `is_transparent()`

Defined at [`transparent.php:269-355`](../inc/customizer/configs/header/transparent.php#L269-L355). Result is cached in a class-level static (`self::$is_transparent`) and reset only when called with `$force = true`.

### 4.1 Tier 1 — At least one row toggled

```php
foreach ( array( 'top', 'main', 'bottom' ) as $row_id ) {
    if ( Customify()->get_setting( "header_{$row_id}_transparent" ) ) {
        $is_tran = true; break;
    }
}
```

If **all three** row toggles are off → `is_transparent()` returns `false` immediately. No tier 2 / 3 evaluation. Per-page metabox `show` **cannot** bypass this — see §7.

### 4.2 Tier 2 — Page-type exclusion

Reads tab `display` of modal `header_transparent_display_pages`. Each key's value is `''` (default) or truthy when checked. The conditional chain ([`transparent.php:301-331`](../inc/customizer/configs/header/transparent.php#L301-L331)):

```php
if ( is_front_page() && is_home() ) {                  // blog-as-front
    $hide = $display['index'];
} elseif ( is_front_page() || is_home() ) {            // static front OR posts page
    $hide = $display['page'];
} elseif ( is_category() ) {
    $hide = $display['category'];
} elseif ( is_page() ) {
    $hide = $display['page'];
} elseif ( is_single() ) {
    $hide = $display['post'];
} elseif ( is_singular() ) {
    $hide = $display['singular'];
} elseif ( is_404() ) {
    $hide = $display['page_404'];
} elseif ( is_search() ) {
    $hide = $display['search'];
} elseif ( is_archive() ) {
    $hide = $display['archive'];
}

if ( Customify()->is_woocommerce_active() ) {
    if ( is_product() )            $hide = $display['product'];
    elseif ( is_product_category() ) $hide = $display['product_cat'];
    elseif ( is_product_tag() )     $hide = $display['product_tag'];
    elseif ( is_shop() )            $hide = $display['page'];
}

if ( $hide ) $is_tran = false;
```

#### Page → display-key resolution table

| Current page | Resolves to key | Note |
|---|---|---|
| Latest-posts front page (`is_front_page() && is_home()`) | `index` | The only case where `index` matters |
| Static front page (`is_front_page() && !is_home()`) | **`page`** | "Disable on index" has no effect here — see §7 issue #1 |
| Posts page (`!is_front_page() && is_home()`) | **`page`** | Same as above |
| Category archive | `category` | |
| Static page | `page` | |
| Single post | `post` | |
| Attachment | `post` | `is_attachment() → is_single()` true |
| CPT single | `singular` | After `is_single()` / `is_page()` checks fail |
| 404 | `page_404` | |
| Search results | `search` | |
| Tag / author / date / generic archive | `archive` | Catch-all |
| WooCommerce single product | `product` | Overrides `post` |
| WooCommerce product category | `product_cat` | Overrides `archive` |
| WooCommerce product tag | `product_tag` | Overrides `archive` |
| WooCommerce shop page | **`page`** | No dedicated `shop` key — see §7 issue #2 |

### 4.3 Tier 3 — Per-page metabox override

Runs **inside** the tier-1 `if ( $is_tran ) { … }` block ([`transparent.php:338-348`](../inc/customizer/configs/header/transparent.php#L338-L348)):

```php
if ( Customify()->is_using_post() ) {
    $id = Customify()->get_current_post_id();
    if ( $id ) {
        $meta = get_post_meta( $id, '_customify_header_transparent_display', true );
        if ( $meta === 'hide' )       $is_tran = false;
        elseif ( $meta === 'show' )   $is_tran = true;
    }
}
```

The metabox UI field is `header_transparent_display` on tab `page_header` ([`class-metabox.php:125-137`](../inc/class-metabox.php#L125-L137)) with three choices:

| Choice | Stored value | Effect |
|---|---|---|
| Inherit from Customizer settings | `'default'` (or empty) | Use tier 1 + 2 result |
| Force transparent | `'show'` | Override tier 2 only — **does not bypass tier 1** |
| Force opaque | `'hide'` | Override tier 2 — force opaque header |

### 4.4 Final filter

```php
self::$is_transparent = apply_filters( 'customify/render_header/is-transparent', $is_tran );
```

Last word goes to the filter. **The result is cached** — any callback added after the first `is_transparent()` call of the request is ignored. See §7 issue #6.

### 4.5 Resolution flowchart

```
                ┌─────────────────────────────┐
                │ Any row toggle ON?          │
                │ header_{top|main|bottom}_   │
                │ transparent                 │
                └──────────────┬──────────────┘
                  No           │  Yes
                  ▼            ▼
              return ◀──────────────────────┐
              false                          │
                                             ▼
                      ┌──────────────────────────────┐
                      │ Resolve page-type key (§4.2) │
                      │ Check display['<key>']        │
                      └──────────────┬───────────────┘
                        Excluded     │  Allowed
                        ▼            ▼
                  $is_tran = false   │
                        │            │
                        └────►──────┐│
                                    ▼▼
                          ┌──────────────────────┐
                          │ Per-page meta?       │
                          │ '_customify_header_  │
                          │ transparent_display' │
                          └──────────┬───────────┘
                'hide' │ ''/default │ 'show'
                   ▼   ▼            ▼
              false  keep          true
                          │
                          ▼
                ┌─────────────────────────────┐
                │ apply_filters(              │
                │ 'customify/render_header/   │
                │  is-transparent', …)         │
                └──────────────┬──────────────┘
                               ▼
                          cached result
```

---

## 5. Class / CSS output

When `is_transparent() === true`:

| Selector | Added by | Always added? |
|---|---|---|
| `body.is-header-transparent` | `body_classes()` filter on `body_class` | ✓ |
| `.header--row.header-{row}.header--transparent` | `row_classes()` on `customify/builder/row-classes` | Only on rows where `header_{row}_transparent` setting is on |
| `.site-logo.has-tran-logo` / `.no-tran-logo` | `logo_classes()` on `customify/logo-classes` | Always one of the two |
| `<img class="site-img-logo-tran">` | `transparent_logo()` on `customizer/after-logo-img` action | Only when `header_logo_tran` image is set |

Key SCSS in [`_header_transparent.scss`](../src/frontend/scss/header/_header_transparent.scss):

```scss
.is-header-transparent {
    &.has-page-cover, &.home {
        #masthead {
            position: absolute; top: 0; left: 0; right: 0; z-index: 99;
        }
    }

    &.has-transparent-offset {
        #page-content {
            padding-top: var(--transparent-header-height, 0px);
        }
    }
}

.header--transparent .header--row-inner {
    background: transparent !important;
    box-shadow:  none        !important;
}

.site-img-logo-tran { display: none; }

.is-header-transparent .has-tran-logo {
    .site-img-logo      { display: none; }
    .site-img-logo-tran { display: block; }
}
```

Note the absolute positioning is gated on **`.has-page-cover` or `.home`** — the header only overlays content on pages that include a cover/hero. Other pages get the transparent background but normal flow.

The `--transparent-header-height` CSS variable and `.has-transparent-offset` body class are set by a frontend script that measures the rendered header height after layout (referenced in the SCSS as "set via JS (initTransparentHeader)"). The PHP class does not emit either — they come from frontend JS in `src/frontend/js/`.

---

## 6. Extension points

| Hook | Type | Arguments | Use case |
|---|---|---|---|
| `customify/render_header/is-transparent` | filter | `(bool $is_tran)` | Final override; runs once per request before caching |
| `customify/builder/row-classes` | filter | `(array $classes, string $row_id, $builder)` | Where this feature itself injects `.header--transparent` |
| `customify/logo-classes` | filter | `(array $classes)` | Where this feature injects `.has-tran-logo` / `.no-tran-logo` |
| `customizer/after-logo-img` | action | none | Where this feature outputs `<img class="site-img-logo-tran">` |
| `body_class` | filter | `(array $classes)` | Where this feature injects `is-header-transparent` |

Other code (Pro, child themes) can read state via:

```php
Customify_Header_Transparent::get_instance()->is_transparent();
// or with cache bypass
Customify_Header_Transparent::get_instance()->is_transparent( true );
```

---

## 7. Known issues / edge cases

These are deliberate documentation of current behaviour. Do not fix silently — any change to display semantics is a data migration concern (see project rule on 30,000 live sites). Coordinate with Pro before changing.

### Issue #1 — "Disable on index" silently no-ops on static front pages

Tier 2 chain checks `is_front_page() && is_home()` first (which is true only when the front page is the blog index). A static front page hits the next branch, which uses `display['page']`. Result: a user who unchecks **only** "Disable on index" while keeping "Disable on single page" off expects the transparent header to show on the static front page; in reality the static front page resolves to `page`, not `index`, so the "Disable on index" checkbox has **no effect** on the most common configuration.

Workaround: document this in support, or add a dedicated `front` key on the next minor cycle (data migration: read both old and new keys for at least one minor version).

### Issue #2 — WooCommerce shop page resolves to `page`

`is_shop()` is checked in the WC branch and maps to `display['page']`. There is no dedicated `shop` key. Users have to disable "Single page" to also disable shop — which then also disables all static pages. No workaround without adding a new key.

### Issue #3 — Per-page metabox `show` cannot bypass tier 1

The metabox override runs **inside** the `if ( $is_tran )` block ([`transparent.php:280`](../inc/customizer/configs/header/transparent.php#L280)). If every row toggle is off, `is_transparent()` returns `false` before reaching the metabox check, so a "Force transparent" choice on a specific page is ignored.

For "Force transparent" to be respected, **at least one** row toggle must be enabled in the Customizer.

### Issue #4 — Per-page metabox `show` does not choose which row is transparent

Body class `is-header-transparent` is gated only on `is_transparent()`, but the row class `.header--transparent` is gated **additionally** on each row's own setting ([`transparent.php:254`](../inc/customizer/configs/header/transparent.php#L254)). If the user enables only `main` globally and forces a specific page to `'show'`, the page works — only `main` is transparent. If the user disables all rows globally and forces `'show'` (which is ignored — see #3), no row gets the class.

Practical effect: the row settings are the source of truth for *which* rows turn transparent; the page-level metabox only toggles whether the chosen set applies.

### Issue #5 — Attachment pages resolve to `post`

`is_attachment()` short-circuits to `is_single()` before reaching `is_singular()`. Attachment archives therefore inherit the "Disable on single post" toggle. There is no dedicated key.

### Issue #6 — `customify/render_header/is-transparent` is cached after first call

`is_transparent()` caches its result in a class static. The final filter runs only when the cache miss happens — the **first** call within a request. Plugins registering callbacks on later hooks (e.g. after `wp` or `template_redirect`) may register too late if any prior code has already called `is_transparent()` (the body class filter and the row class filter both call it).

Workarounds:

- Register the callback as early as possible (e.g. `after_setup_theme`, `init`).
- Call `Customify_Header_Transparent::get_instance()->is_transparent( true )` to force re-evaluation when you know you have changed something downstream.

---

## 8. Pro plugin handoff

When `Customify_Pro_Module_Header_Transparent` exists, the theme's port is **not instantiated** ([`transparent.php:404-413`](../inc/customizer/configs/header/transparent.php#L404-L413)). The Pro module is expected to:

- Register the same `header_transparent` Customizer section (so saved theme_mods remain valid)
- Honour the same theme_mod keys (`header_{row}_transparent`, `header_transparent_display_pages`, `header_logo_tran*`, `logo_tran_max_width`)
- Honour the same post meta key `_customify_header_transparent_display`
- Apply the same body / row / logo CSS classes so frontend SCSS keeps working unchanged
- Expose the same `customify/render_header/is-transparent` filter signature

This contract is **public API** under the 30k-sites rule. Any change to a setting key, post meta key, or class name must follow the migration discipline in the project's main `CLAUDE.md`:

1. Read both old AND new shapes
2. Provide a version-stamped one-shot migration when truly changing storage
3. Keep the old key as a read-only fallback for at least one minor version

Pro typically extends with: sticky transparent behaviour, scroll-triggered animation, per-template overrides, and menu colour skinning. Those are out of scope here.

---

## 9. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Check programmatically whether transparent is active on the current request | `Customify_Header_Transparent::get_instance()->is_transparent()` |
| Force-disable transparent header in a specific scenario | `add_filter( 'customify/render_header/is-transparent', '__return_false' );` (register early) |
| Force-enable transparent header in a specific scenario | `add_filter( 'customify/render_header/is-transparent', '__return_true' );` (register early; also requires at least one row toggle ON) |
| Read whether a specific row is currently transparent | `Customify()->get_setting( "header_{$row}_transparent" )` + `is_transparent()` |
| Read the per-page override | `get_post_meta( $post_id, '_customify_header_transparent_display', true )` returns `''`, `'show'`, or `'hide'` |
| Find which display keys are excluded | `Customify()->get_setting_tab( 'header_transparent_display_pages', 'display' )` |

---

## 10. Storage keys (canonical list)

`theme_mod` keys (read with `get_theme_mod` or `Customify()->get_setting()`).

### Row toggles & display

| Key | Stored type | Default | Notes |
|---|---|---|---|
| `header_top_transparent` | bool | `false` | Checkbox |
| `header_main_transparent` | bool | `false` | Checkbox |
| `header_bottom_transparent` | bool | `false` | Checkbox |
| `header_top_transparent_styling` | styling array (or `""` when empty) | `""` | See "Styling control shape" below |
| `header_main_transparent_styling` | styling array | `""` | |
| `header_bottom_transparent_styling` | styling array | `""` | |
| `header_transparent_display_pages` | modal array with `display` tab | `{}` | Read via `Customify()->get_setting_tab( 'header_transparent_display_pages', 'display' )` |

### Logo + slider

| Key | Stored type | Default | Notes |
|---|---|---|---|
| `header_logo_tran` | **object** `{ id, mime, url }` | `{ id:'', mime:'', url:'' }` | **Not a plain attachment ID.** Customify's `image` control type stores the resolved attachment object. See snippet below. |
| `header_logo_tran_retina` | **object** `{ id, mime, url }` | `{ id:'', mime:'', url:'' }` | Same shape as above |
| `logo_tran_max_width` | per-device slider value | `[]` | Customify's `slider` control with `device_settings: true`. Read via `Customify()->get_setting( 'logo_tran_max_width' )`; auto-CSS handled by the framework |

#### Image control shape — `header_logo_tran` / `header_logo_tran_retina`

When set programmatically (e.g. via `wp.customize` JS or REST), the value must match the framework's expected shape exactly, otherwise the sanitize callback resets it to the empty default:

```js
wp.customize( 'header_logo_tran' ).set( {
    id:   365,                                                       // attachment ID (string or int)
    mime: 'image/png',
    url:  'https://example.com/wp-content/uploads/2026/05/logo.png',  // public URL of the attachment
} );
```

When read back from PHP, the value is the same object. To resolve a usable URL, pass the whole value (not just `id`) to `Customify()->get_media( $value, 'full' )` — the helper handles both pure-ID and object shapes:

```php
$logo_value = Customify()->get_setting( 'header_logo_tran' );      // array | ID
$logo_url   = Customify()->get_media( $logo_value, 'full' );       // string URL | false
```

`logo_classes()` and `transparent_logo()` in this feature use exactly this pattern ([`transparent.php:370-396`](../inc/customizer/configs/header/transparent.php#L370-L396)).

#### Styling control shape — `header_{row}_transparent_styling`

The styling control stores either:

- `""` (empty string) when no fields have been set, or
- an array with sub-keys for each enabled field (text color, background, border, …). The text color is stored at `normal.text_color`. Per-row config in this feature disables most fields ([§3.1](#31-per-row-settings-repeated-for-top-main-bottom)), so only the active subset is ever populated.

Auto-CSS generation reads this shape via `Customify_Customizer_Auto_CSS` — no need to parse it manually in feature code.

### Post meta

| Key | Stored type | Possible values |
|---|---|---|
| `_customify_header_transparent_display` | string | `''` (unset) \| `'default'` \| `'show'` \| `'hide'` |

### Public API guarantee

All of the above are **public API** for the Pro module and child themes. Treat as immutable under the 30k-sites rule:

1. **Never rename a key** — add a new one and read both for at least one minor version
2. **Never silently change the stored shape** — provide a version-stamped one-shot migration
3. **Never change the default value** — existing sites with the old value must continue to render correctly

See the project's main `CLAUDE.md` for the full data-migration discipline.
