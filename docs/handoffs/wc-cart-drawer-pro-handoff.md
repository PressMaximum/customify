# Handoff — WooCommerce Cart Drawer (Off-canvas) — Customify **Pro** module

**Audience:** the Customify **Pro** session (repo `PressMaximum/customify-pro`, branch `Dev`).
**Author:** theme (free) session — research + free-side contract done here.
**Status:** free-side seam landed; Pro module unstarted.

This is a transient session note (per SPEC-customizer §1.5 / docs convention). The permanent contract lives in [`../SPEC-pro-integration.md`](../SPEC-pro-integration.md) §8 and [`../api-reference.md`](../api-reference.md) §4.4.

---

## 1. Goal & decisions (locked with the product owner)

Add an **off-canvas cart drawer** as an alternative to the header cart item's existing hover **dropdown**. Locked decisions:

1. **Lives in Pro**, folded into the **WooCommerce Booster** module (`modules/woocommerce-booster/cart-drawer.php`, next to `off-canvas-filter.php`) — on/off rides on the WC Booster module toggle. NOT a standalone module.
2. **Opt-in per site.** Master switch `wc_cart_behavior` = `dropdown` (default) | `drawer`. Default `dropdown` → zero behavior change for the 30k existing sites.
3. Drawer **reuses `the_widget('WC_Widget_Cart')`** (same content as the dropdown) — NOT the WC Mini Cart block, NOT custom markup.
4. Drawer width is **`%` + responsive** (device_settings), single global behavior value (not per-device).
5. Free stays clean: the theme exposes **one filter seam** (already added); everything else is Pro-side.

---

## 2. Free ↔ Pro contract (what the theme already gives you)

### 2.1 The seam (NEW — already committed in the free theme)

`inc/compatibility/woocommerce/config/header/cart.php` → `Customify_Builder_Item_WC_Cart::render()` now wraps the inline dropdown in a filter:

```php
$render_dropdown = apply_filters( 'customify/wc_cart/render_dropdown', true, $this );
if ( $render_dropdown ) {
    // ... echoes <div class="cart-dropdown-box"> the_widget('WC_Widget_Cart') </div>
}
```

- Default `true` → dropdown renders as always (30k sites unchanged).
- **Pro returns `false`** when `wc_cart_behavior === 'drawer'` so the inline mini-cart is not printed twice.
- Signature is public API (documented in [`../api-reference.md`](../api-reference.md) §4.4) — never change it.

### 2.2 Extension points already present in free (no theme change needed)

| Need | Free mechanism |
|---|---|
| Add the behavior select + drawer settings to the cart section | `customify/customizer/config` filter (SPEC-pro-integration §8.1). The section id is **`wc_cart`**. |
| Gate the free `wc_cart_d_align` / `wc_cart_d_width` fields so they hide in drawer mode | Pro **mutates those items in the `customify/customizer/config` filter** — add `'required' => array('wc_cart_behavior','==','dropdown')`. Do it Pro-side so free is untouched when Pro is off. |
| Suppress the inline dropdown | `customify/wc_cart/render_dropdown` → false (§2.1) |
| Change the trigger to open the drawer | Pro frontend JS intercepts clicks on `.builder-header-wc_cart-item .cart-item-link`, `preventDefault()`, open drawer. No theme change. |
| Render the drawer panel | Pro hooks `wp_footer`, prints panel + overlay, calls `the_widget('WC_Widget_Cart', ['hide_if_empty'=>0])`. |
| Live cart sync | **Already works.** WC core seeds fragment `div.widget_shopping_cart_content`; the theme also registers `.customify-wc-sub-total` + `.customify-wc-total-qty` (`inc/compatibility/woocommerce/woocommerce.php` ~L361-370). The drawer's `WC_Widget_Cart` emits `.widget_shopping_cart_content`, so add/remove auto-refreshes the drawer body. No Store API. |

### 2.3 Cart item markup you're attaching to (free-rendered)

```html
<div class="d-align-right builder-header-wc_cart-item item--wc_cart">
  <a href="…cart…" class="cart-item-link text-uppercase text-small link-meta"> …icon/label/subtotal/qty… </a>
  <!-- .cart-dropdown-box printed ONLY when customify/wc_cart/render_dropdown === true -->
</div>
```

Trigger to bind: `.builder-header-wc_cart-item .cart-item-link`. Badge/subtotal spans: `.customify-wc-total-qty`, `.customify-wc-sub-total`.

---

## 3. Pro module design

Model the file on the sibling `modules/woocommerce-booster/off-canvas-filter.php` (same module, closest analog — an off-canvas UI). Verify the WC Booster loader (`woocommerce-booster.php`) `require`s the new `cart-drawer.php`, and the module base API (`inc/class-module-base.php`) for asset enqueue / `is_enabled` conventions before wiring.

### 3.1 Settings (append via `customify/customizer/config`, section `wc_cart`)

All theme_mods, `wc_cart_` namespace (consistent with the free cart fields):

```
wc_cart_behavior        select { 'dropdown'(default), 'drawer' }   render_callback = free cart render
                        // place just before the free 'wc_cart_d_h' heading via priority

// Pro mutates existing free items in the same filter:
wc_cart_d_h / wc_cart_d_align / wc_cart_d_width  += required: [wc_cart_behavior == dropdown]

// New drawer group — required: [wc_cart_behavior == drawer]
wc_cart_drawer_h        heading "Drawer Settings"
wc_cart_drawer_position select { 'right'(default), 'left' }
wc_cart_drawer_width    slider unit '%', device_settings:true, min 20 / max 100,
                        default { desktop:30, tablet:65, mobile:90 },
                        selector '.customify-cart-drawer', css_format 'width:{{value}};'
                        // if the slider '%' unit renders oddly, fall back to a plain
                        // slider + css_format 'width:{{value_no_unit}}%;'
wc_cart_drawer_auto_open checkbox default 0

// Optional styling (phase 2, defer): backdrop color, panel bg, heading color,
// close-button group. Keep out of the first cut.
```

Width defaults mirror Astra/Blocksy (narrow desktop, wide mobile): Astra tablet 80% / mobile 100%; Blocksy desktop 500px / tablet 65vw / mobile 90vw.

### 3.2 Render (Pro, on `wp_footer`)

Guard: WC active, not `is_cart()` / `is_checkout()`, `wc_cart_behavior === 'drawer'`. Print **once**, sibling overlay + panel, direction via `data-position`:

```php
add_filter( 'customify/wc_cart/render_dropdown', fn( $r ) => 'drawer' === get_behavior() ? false : $r, 10, 1 );

add_action( 'wp_footer', function () {
    if ( ! function_exists('WC') || is_cart() || is_checkout() || 'drawer' !== get_behavior() ) return;
    $pos = get_setting('wc_cart_drawer_position') ?: 'right';
    ?>
    <div class="customify-cart-drawer-overlay" hidden></div>
    <aside id="customify-cart-drawer" class="customify-cart-drawer" data-position="<?php echo esc_attr($pos); ?>"
           role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Shopping cart','customify-pro'); ?>" hidden>
      <div class="customify-cart-drawer__head">
        <span class="customify-cart-drawer__title"><?php esc_html_e('Cart','customify-pro'); ?></span>
        <button class="customify-cart-drawer__close" aria-label="<?php esc_attr_e('Close','customify-pro'); ?>">&times;</button>
      </div>
      <div class="customify-cart-drawer__body widget-area">
        <?php add_filter('woocommerce_widget_cart_is_hidden','__return_false',999);
              the_widget('WC_Widget_Cart', ['hide_if_empty'=>0]);
              remove_filter('woocommerce_widget_cart_is_hidden','__return_false',999); ?>
      </div>
    </aside>
    <?php
} );
```

Enqueue Pro drawer JS/CSS only when behavior is drawer + WC present (lazy — don't tax non-shop pages).

### 3.3 Frontend JS (Pro)

Mirror the theme's off-canvas pattern (`src/frontend/js/theme.js` `toggleMenuSidebar()`), but keep **independent state** (own body class, e.g. `is-cart-drawer`) — do NOT reuse the mobile-menu `is-menu-sidebar` (Astra lesson: separate systems avoid z-index/state collisions).

- **Open:** click `.builder-header-wc_cart-item .cart-item-link` → `e.preventDefault()` (keep the `href` as no-JS fallback) → add `.is-open` to drawer + overlay, `is-cart-drawer` to `<html>`/`<body>`.
- **Close:** overlay click, `.customify-cart-drawer__close` click, ESC (capture-phase keydown).
- **Scroll-lock:** toggle a body/html class → CSS `overflow:hidden` + `padding-right: var(--scrollbar-width)` to avoid layout shift.
- **a11y:** focus into close button on open, focus-trap Tab/Shift-Tab, return focus to trigger on close, `aria-modal`, flip `hidden`/`inert`.
- **Auto-open** (when `wc_cart_drawer_auto_open`): listen to jQuery `added_to_cart`, but **open on the next `wc_fragments_refreshed`** (one-shot) so the drawer shows fresh content, and set a `focusDisabled`-style flag so it doesn't yank focus off the product page. (Both Astra Pro and Blocksy do exactly this.)
- **bfcache:** a `pageshow` handler to tear down a stuck-open drawer after browser back (Blocksy lesson).

The open-transition kick: use a **forced synchronous reflow** (`void el.offsetWidth`), never `requestAnimationFrame` (rAF doesn't fire in hidden tabs — matches the theme's own convention in `typography-control.js`).

### 3.4 SCSS (Pro)

- `.customify-cart-drawer-overlay`: `position:fixed; inset:0; background:rgba(0,0,0,.4); opacity:0; transition:opacity .25s;` → `.is-open{opacity:1}`.
- `.customify-cart-drawer`: `position:fixed; top:0; bottom:0; height:100%; z-index:100000; width: <from wc_cart_drawer_width auto-CSS>; transform: translateX(...)` parked off-screen; `.is-open{transform:translateX(0)}`. Use `translate3d` + `will-change:transform` (GPU).
- Side via `[data-position="right"]{right:0; transform:translateX(100%)}` / `[data-position="left"]{left:0; transform:translateX(-100%)}`.
- Body scroll area = the product `<ul>`; admin-bar top offset.
- **RTL:** prefer logical properties / a single `[dir=rtl]` transform-sign flip over Astra's fully-duplicated RTL branch.

---

## 4. Research findings baked in (Astra + Blocksy)

Both themes gate the drawer behind Pro and converge on the SAME design — strong signal to copy:

**Copy:**
- Reuse `the_widget('WC_Widget_Cart')` / `woocommerce_mini_cart()` as the panel body; sync via **existing WC AJAX fragments** (`div.widget_shopping_cart_content`) — one handler updates dropdown + drawer.
- `preventDefault()` on the trigger anchor but keep a real `href` (cart URL) as fallback; add a touch guard (tap opens the panel).
- Overlay fade (opacity) + inner sheet slid via `transform:translateX` off-screen→0; width via CSS var, `%`/`vw` responsive per breakpoint; left/right via data-attr.
- Overlay-click + ESC + close-button; scroll-lock via html/body class + scrollbar-width padding; focus trap + `role=dialog`/`aria-modal`/`inert`.
- Auto-open **after** `wc_fragments_refreshed` with a focus-disabled flag; page-context gating if you later split archive vs product.
- Print the panel once near `</body>` (`wp_footer`) — never inline in the header (fixed/z-index gets trapped by header stacking context).

**Avoid:**
- Don't maintain two cart templates (dropdown vs drawer) — share the one `WC_Widget_Cart` render.
- Don't monkey-patch `jQuery.fn.replaceWith` (Blocksy does; overkill for v1) — let WC replace the fragment; the drawer body updates in place because it contains `.widget_shopping_cart_content`.
- Don't duplicate the whole RTL CSS branch (Astra does) — logical props are less code.
- Remember iOS scroll-lock needs more than `overflow:hidden` (no-bounce) if mobile matters.

Reference sources (read-only, in the user's Studio sites):
- Astra: `t2027/wp-content/themes/astra` (`inc/builder/controllers/class-astra-builder-ui-controller.php` drawer markup; `assets/js/unminified/mobile-cart.js` open/close/a11y; `inc/class-astra-dynamic-css.php` layout) + `plugins/astra-addon` (Pro auto-open).
- Blocksy: `spectra/wp-content/themes/blocksy` (`inc/panel-builder/header/cart/{view,options,dynamic-styles}.php`; `static/js/frontend/woocommerce/mini-cart.js`; `static/sass/frontend/5-modules/off-canvas/*`).

---

## 5. 30k-site safety checklist

- [ ] `wc_cart_behavior` default is `dropdown` — existing sites render identically.
- [ ] `customify/wc_cart/render_dropdown` default `true` — free theme with Pro off/absent is unchanged.
- [ ] All new setting keys are **new** (`wc_cart_behavior`, `wc_cart_drawer_*`) — no rename/removal of existing `wc_cart_*` keys.
- [ ] With Pro deactivated, the drawer theme_mods become inert; free ignores them and renders the dropdown. Graceful.
- [ ] Drawer settings hidden unless behavior=drawer (via `required`); dropdown fields hidden unless behavior=dropdown (Pro-mutated `required`).
- [ ] No Store API / no WC Mini Cart block dependency — works on this classic (non-FSE) theme.

## 6. Verification (Pro session)

1. WC Booster on, behavior=dropdown → identical to today (dropdown hover; no footer panel; `render_dropdown` true).
2. behavior=drawer → inline dropdown gone; footer panel present; click cart icon opens drawer; overlay/ESC/close work; add-to-cart updates drawer body live.
3. Width `%` responsive across desktop/tablet/mobile; left/right position; RTL.
4. Auto-open on: adding a product opens the drawer with fresh contents, no focus theft.
5. Pro deactivated → free theme back to dropdown, no errors, theme_mods retained.
6. a11y: keyboard open/close, focus trap, screen-reader `role=dialog`.
