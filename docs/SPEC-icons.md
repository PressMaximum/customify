# SPEC — Icon system

Every icon a builder item renders — the WooCommerce cart glyph, the header button, social icons, the search magnifier — comes from a Customizer field of `'type' => 'icon'`. This SPEC is the contract for that field: the stored value shapes, the render helpers, the preset inline-SVG library, and the filters Pro and child themes extend it through.

Related references:
- [`SPEC-customizer.md`](SPEC-customizer.md) — control registration + auto-CSS pipeline
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) — the builder items that consume icons
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) — Pro boundary rules for the filters below
- [`api-reference.md`](api-reference.md) §2.3 — one-line filter index

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

Three kinds of icon share one field type and one storage shape, so a site can mix them freely and a call site never branches on which kind it got.

| Kind | `type` | What `icon` / `svg` hold | Cost on the front end |
|---|---|---|---|
| Font icon | `font-awesome`, `font-awesome-v6`, `font-awesome-v456` | `icon` = CSS class (`fa fa-shopping-basket`) | the Font Awesome stylesheet (~70KB) must load first |
| Preset SVG | `svg` | `icon` = key into the theme's library (`cart`, `bag`, …) | ~200 bytes inline, paints with the HTML |
| Custom SVG | `custom-svg` | `svg` = user-pasted markup, sanitised | size of the paste, inline |

| Surface | Role |
|---|---|
| `icon` Customizer control | Picker UI — Suggested row, type dropdown, grid, search, paste panel |
| `theme_mod` (`{type, icon, svg}`) | Persistent data — **public API, never reshape** |
| `customify_render_icon()` | Storage → HTML |
| `customify_get_svg_icons()` | The preset library, filterable |
| `customify_sanitize_svg()` | Security boundary for pasted markup |

---

## 2. File map

| File | Responsibility |
|---|---|
| [`inc/icons-svg.php`](../inc/icons-svg.php) | Preset SVG library + `customify/svg_icons` filter + markup builder |
| [`inc/install-marker.php`](../inc/install-marker.php) | `customify_is_fresh_install_since()` — gates modern defaults |
| [`src/frontend/scss/header/builder_items/_icon_label_items.scss`](../src/frontend/scss/header/builder_items/_icon_label_items.scss) | Shared header icon+label style (§9.2) |
| [`inc/template-functions.php`](../inc/template-functions.php) | `customify_sanitize_svg()`, `customify_render_icon()` and its get/echo aliases |
| [`inc/customizer/class-customizer-icons.php`](../inc/customizer/class-customizer-icons.php) | Font Awesome catalogs + stylesheet enqueue |
| [`inc/customizer/class-customizer-sanitize.php`](../inc/customizer/class-customizer-sanitize.php) | `sanitize_icon()` — the save-time pass |
| [`inc/customizer/controls/class-control-icon.php`](../inc/customizer/controls/class-control-icon.php) | Control + sidebar templates |
| [`inc/customizer/controls/class-control-base.php`](../inc/customizer/controls/class-control-base.php) | `presets` control arg, `Customify_Control_Args.svg_icons` payload |
| [`src/backend/customizer/js/control.js`](../src/backend/customizer/js/control.js) | `IconPicker` — the sidebar behaviour |
| [`src/backend/customizer/scss/_control.scss`](../src/backend/customizer/scss/_control.scss) | Sidebar layout (`#customify--sidebar-icons`) |
| [`src/frontend/scss/base/_icons.scss`](../src/frontend/scss/base/_icons.scss) | Front-end sizing + `currentColor` rules |
| [`bin/test-svg-sanitize.php`](../bin/test-svg-sanitize.php) | Runnable sanitiser + library contract checks |

---

## 3. Storage shape

```php
array(
    'type' => 'svg',   // '' | 'font-awesome' | 'font-awesome-v6' | 'font-awesome-v456' | 'svg' | 'custom-svg'
    'icon' => 'cart',  // CSS class (font) | library key (svg) | display label (custom-svg)
    'svg'  => '',      // sanitised markup, ONLY when type === 'custom-svg'
)
```

Rules that must not be broken ([`../AGENTS.md`](../AGENTS.md) §4.1):

1. **The three keys are permanent.** Values saved before `svg` existed parse through `wp_parse_args()` defaults — no migration ran, and none is needed.
2. **`svg` is cleared for every non-`custom-svg` type** at save time, so switching a field from a pasted SVG back to a font icon round-trips to the same bytes it had before the SVG feature shipped.
3. **`icon` is `sanitize_key()`'d when `type === 'svg'`** (it is a library key) and `sanitize_text_field()`'d otherwise (a font class carries spaces).
4. **Unknown type, unknown preset key, or empty value renders nothing.** No notices, no fallback glyph.

---

## 4. Render helpers

```php
customify_render_icon( $value, $args = array() ) : string   // canonical
customify_get_icon_html( $value, $args = array() ) : string // alias of the above
customify_icon_html( $value, $args = array() ) : void       // echo variant
```

`$args`: `wrapper_class` (extra classes on the `<i>` / wrapper `<span>`) and `title`.

Output by type:

| Type | Markup |
|---|---|
| font library | `<i class="fa fa-shopping-basket">` |
| `svg` | `<span class="customify-icon customify-icon--svg customify-icon--preset"><svg class="customify-svg-icon customify-svg-icon--cart" …>` |
| `custom-svg` | `<span class="customify-icon customify-icon--svg">` + sanitised markup |

The return value is **already safe to echo** — escaping it would turn an inline `<svg>` into visible angle brackets. Call sites echo it directly with a `phpcs:ignore`.

`customify_render_icon()` is the original name and stays the canonical implementation; the other two are thin wrappers so the API reads as the usual WordPress get/echo pair ([`../AGENTS.md`](../AGENTS.md) §4.2 — never rename, add alongside).

### 4.1 Why preset markup is not stored

Only the **key** is persisted. The markup is looked up at render time, so a later redraw of an icon reaches every site that picked it, and a 49-icon library costs nothing in `theme_mod` size.

---

## 5. Preset SVG library

```php
customify_get_svg_icons()        // key => array( 'label', 'source', 'style', 'viewbox', 'body' )
customify_get_svg_icon( $key )   // full <svg> markup, or '' for an unknown key
customify_get_svg_icons_for_js() // key => array( 'label', 'svg' ) — the Customizer payload
```

`body` is the **inner** markup of the `<svg>`; `customify_get_svg_icon()` supplies the root element so every icon gets an identical, contract-compliant one.

### 5.1 Where the geometry comes from

The path data is **copied verbatim from upstream sets** — nothing in the library is hand-drawn. Re-extract from a newer upstream release rather than editing geometry by hand, or the set drifts out of visual sync with itself.

| Set | Licence | Grid | Paint | Role |
|---|---|---|---|---|
| [Lucide](https://lucide.dev) (`lucide-static`) | ISC | 24 | stroke | **Primary** outline set |
| [Tabler](https://tabler.io/icons) (`@tabler/icons`, `icons/outline`) | MIT | 24 | stroke | The softer `-outline` variants |
| [Tabler](https://tabler.io/icons) (`icons/filled`) | MIT | 24 | fill | The `-filled` solids |
| [Heroicons](https://heroicons.com) (`heroicons`, `24/solid`) | MIT | 24 | fill | `bag-filled` only |
| [Phosphor](https://phosphoricons.com) (`@phosphor-icons/core`, `regular`) | MIT | 256 | fill | The rounded, generous silhouettes |
| [react-payment-logos](https://github.com/iamgutz/react-payment-logos) (`logo` set) | MIT | 780×500 | brand colour | Payment card schemes: Visa, Amex, Discover, Diners, JCB, UnionPay, PayPal |
| [Simple Icons](https://simpleicons.org) (`simple-icons`) | CC0-1.0 | 24 | brand colour | Payment wallets / gateways: Apple Pay, Google Pay, Stripe, Klarna, Amazon Pay |

Tabler shares Lucide's grid exactly (24×24, stroke 2, round caps), which is why the two mix without a seam. Heroicons is used for one icon because neither Lucide nor Tabler ships a solid shopping bag; Heroicons' **outline** set is deliberately unused — it is drawn for stroke-width 1.5 and reads visibly lighter beside Lucide at 2. Phosphor draws every weight as filled paths on a 256 grid, which is why its glyphs are `filled` entries with their own viewBox even though they read as outlines. Its `thin` weight was tried and dropped — hairlines disappear at header size.

Only the upstream `<svg>` wrapper is discarded during extraction, plus Tabler's transparent `M0 0h24v24H0z` bounding-box path (sprite-build padding) and per-set `class` / `data-slot` hooks.

The two payment sets are handled the same way — path data verbatim, nothing redrawn — with two mechanical adjustments the monochrome sets do not need:

- **Fitting.** The scheme logos are drawn on 780×500 and the wallet marks on 24×24, but the Payment family shares one 38×24 card. Each body is wrapped in a single `<g transform="translate(…) scale(…)">` computed from the artwork's real bounding box. The transform is the only thing added; no coordinate inside `d=""` is touched.
- **Flattening.** JCB's three bars ship as `<linearGradient>` fills. They are replaced with the solid colour 70 % along each gradient (`#3BA235`, `#095AA3`, `#C10E35`). Flatten gradients rather than widening `customify_sanitize_svg()`'s allowlist — the sanitiser's surface is a security boundary, and a payment logo is not worth growing it.

Two entries are the exception to "nothing is hand-drawn": **`pay-mastercard` and `pay-maestro`**. Their upstream full-colour logos are the legacy wordmark lockups (9 KB and 7 KB of outlined type, illegible at card size), while the modern scheme marks are two overlapping discs plus the lens where they meet — ~270 bytes of `<circle>` + one `<path>`, and correct at 24 px. Colours: Mastercard `#EB001B` / `#F79E1B` with an `#FF5F00` overlap; Maestro `#ED0006` / `#0099DF` with `#6C6BBD`.

> **Trademarks.** Payment marks are trademarks of their owners and appear here purely as *acceptance marks* — the standard "we take these cards" row. The licences above cover the artwork files only; they convey no trademark rights and imply no endorsement.

> **Shopify Dawn is NOT used, despite being the reference look.** Dawn's `LICENSE.md` is not plain MIT: the grant is limited to "themes that integrate or interoperate with Shopify software or services", with all other uses "strictly prohibited". Customify is a GPL WordPress theme on WordPress.org, so shipping Dawn's assets would fall outside that grant *and* break GPL compatibility. Phosphor's `regular` weight gives the same rounded, minimal silhouettes under a licence the project can actually use. Do not re-add Dawn.

### 5.2 Icon style contract

Every entry — including anything filtered in — must honour it, or the set stops reading as one set:

- a **square** `viewBox`, declared per entry (`0 0 24 24`, `0 0 256 256`, …). Upstream sets disagree on grid size and rescaling by hand would mean editing geometry. **`brand` is the one documented exception** — see §5.6.
- **no `width` / `height` on the root** — CSS sizes the icon
- `'style' => 'outline'` → rendered `fill="none" stroke="currentColor"`, stroke-width 2, round caps + joins (Lucide's and Tabler's native weight)
- `'style' => 'filled'` → rendered `fill="currentColor" stroke="none"` **plus the class `customify-svg-icon--filled`**, which the frontend override rule keys off; without it the outline default blanks the glyph
- `'style' => 'brand'` → rendered with **no root `fill` / `stroke` at all**, plus the class `customify-svg-icon--brand`. Colour lives on the body's own elements. §5.6.
- `aria-hidden="true"` + `focusable="false"` — icons are decorative; the accessible name comes from the surrounding link or label

`style` is the **paint mode, not the visual weight**: Phosphor's hairline glyphs are `filled` because their geometry is a filled outline, and they still look like the thinnest icons in the set.

`style` and `viewbox` are optional on a filtered-in entry and default to `outline` / `0 0 24 24` — what a hand-written Lucide-style addition wants anyway.

`bin/test-svg-sanitize.php` asserts all of this against every library entry, per paint style.

### 5.3 Families and naming

| Family | Keys |
|---|---|
| Commerce (11) | `bag` `bag-outline` `bag-handle` `bag-tote` `bag-filled` `cart` `cart-outline` `cart-filled` `basket` `basket-alt` `basket-filled` |
| Account (11) | `user` `user-alt` `user-outline` `user-filled` `user-circle` `user-circle-alt` `user-circle-soft` `user-circle-filled` `user-square` `contact` `id-card` |
| Wishlist (8) | `heart` `heart-outline` `heart-plus` `heart-filled` `bookmark` `bookmark-filled` `star` `star-filled` |
| General UI (19) | `search` `menu` `menu-lines` `menu-narrow` `menu-minimal` `menu-deep` `menu-left` `menu-right` `close` `chevron-down` `arrow-right` `external-link` `home` `phone` `mail` `map-pin` `globe` `clock` `calendar` |
| Payment (14) | `pay-visa` `pay-mastercard` `pay-maestro` `pay-amex` `pay-discover` `pay-diners` `pay-jcb` `pay-unionpay` `pay-paypal` `pay-apple-pay` `pay-google-pay` `pay-stripe` `pay-klarna` `pay-amazon-pay` |

The General UI family is deliberately lean and header-oriented. This is not a replacement icon font — every extra key is one more cell a shop scrolls past to reach the icon it actually wants. Pro's User Icon item reuses `contact` and `id-card`; its Wishlist item reuses the heart pair.

`-outline` marks an alternate, softer silhouette of the same subject (`bag` is Lucide's squared-shoulder bag, `bag-outline` Tabler's rounded one); `-alt` a second distinct one, `-soft` a rounder one, and `-filled` the solid counterpart.

A key may be **re-pointed** to better geometry while it is unreleased — `user` now draws Lucide's `user-round` rather than its squarer `user`, and the old glyph lives on as `user-alt`. Once shipped, a key's meaning is frozen even if its artwork is refreshed. Keys are stored in `theme_mod`s: **treat them as public API — add, never rename or remove.** A removed key renders nothing, and the site silently loses its icon.

Each entry also carries a `source` field (`lucide/shopping-bag`, `tabler-filled/basket`). Nothing reads it at runtime; it exists so a maintainer can re-extract an icon without guessing which set it came from.

### 5.4 Extending it

```php
add_filter( 'customify/svg_icons', function ( $icons ) {
    $icons['my-glyph'] = array(
        'label' => __( 'My Glyph', 'my-plugin' ),
        'body'  => '<path d="M4 12h16"/>',
    );
    return $icons;
} );
```

One registration is enough: the front-end renderer, the picker grid and the per-field Suggested row all read the same list.

---

### 5.5 Gating a modern default — the install marker

A default that can never change is a default frozen in 2019; a default that changes freely redraws 30,000 live headers overnight. [`inc/install-marker.php`](../inc/install-marker.php) splits the two populations.

```php
customify_get_installed_version()            // '0.4.25' | '0.0.0' | …
customify_is_fresh_install_since( '0.4.25' ) // bool
```

The `customify_installed_version` option is stamped **once**, on first read (and on `after_switch_theme`, which catches a genuinely fresh activation at the earliest moment):

| Site state when first asked | Stamped | Effect |
|---|---|---|
| Has any Customify `theme_mod` | `0.0.0` | Never passes a gate — keeps every legacy default |
| No Customify `theme_mod` at all | running version | Passes gates at or below that version |

WordPress seeds `theme_mods_<stylesheet>` itself on theme switch (`0`, `nav_menu_locations`, `custom_css_post_id`, `sidebars_widgets`), so those keys are skipped — the option merely existing proves nothing.

Two consumers today, both gated on `0.4.25`:

1. **The cart's icon default** — [`config/header/cart.php`](../inc/compatibility/woocommerce/config/header/cart.php) registers `{type:'svg', icon:'bag'}` on a fresh install and the Font Awesome basket otherwise. Config arrays are built at runtime, so the branch is evaluated per request.
2. **The `customify-header-items-v2` body class** — [`inc/element-classes.php`](../inc/element-classes.php), which scopes §9.2's shared header-item style.

A site that **saved** a value is unaffected either way: the saved value always beats the default. The gate only decides what a site that never touched the field sees.

> **Known ambiguity, deliberately fail-safe.** A site that installed the theme years ago and never customised anything is indistinguishable from a fresh install, and will be stamped as fresh. By construction such a site has only ever rendered defaults, so the exposure is one cosmetic icon on a header nobody configured. Every site that has *any* saved setting is correctly stamped legacy.

`customify_is_fresh_install_since()` is generic on purpose — later items (User Icon, Search) gate the same way instead of inventing a second mechanism. **The option is public API and Pro reads it**, so its builder-item defaults move in step with the theme's.

---

### 5.6 Brand-coloured entries and mono mode

`'style' => 'brand'` is the third paint mode and the only one that carries colour. It exists for the Payment family, whose members are **not glyphs**: a shop needs Visa blue and Mastercard's red/orange discs, not a `currentColor` silhouette. Shopify stores set the expectation, and a monochrome payment row reads as a placeholder next to one.

#### The card

Every brand entry is a **38 × 24 rounded card** — the ISO card ratio rounded to whole units, shared by the whole family so a row of them lines up on one baseline no matter how wide each logo is. The first element in the body is always the card:

```html
<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/>
```

White unless the brand's own badge is coloured (Klarna `#FFB3C7`, Stripe `#635BFF`). The card is what lets a logo drawn for white paper — Amex's blue box, Discover's black wordmark — stay legible in a dark footer, and it is the hook mono mode turns into an outline.

#### Rendered markup

```html
<span class="customify-icon customify-icon--svg customify-icon--preset">
  <svg class="customify-svg-icon customify-svg-icon--visa customify-svg-icon--brand"
       viewBox="0 0 38 24" aria-hidden="true" focusable="false">
    <rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/>
    <g transform="translate(3.446 5.65) scale(0.04851)">
      <path fill="#0E4595" d="…"/><path fill="#F2AE14" d="…"/>
    </g>
  </svg>
</span>
```

Note what is **absent**: no `fill` or `stroke` on the root. That is the whole rule. An outline entry needs root paint, a filled entry needs root paint, a brand entry must have none — every element inside declares its own `fill`, and a value on the element always beats one inherited from its parent.

#### Why the CSS has to fight back

"Leave the colours alone" cannot be written literally in CSS: a declaration always beats a presentation attribute, and no keyword reverts to one. Two rules already shipped would eat a brand entry — `.customify-icon--svg > svg { fill: currentColor }` from the wrapper, and `svg.customify-svg-icon { fill: none; stroke: currentColor }` from the preset default. The exemption works by making the **root** stop imposing anything, which leaves each path's own `fill` untouched:

```scss
// src/frontend/scss/base/_icons.scss
svg.customify-svg-icon.customify-svg-icon--brand {
	width: auto;          // a card, not a 1em square
	height: 1em;
	fill: none;
	stroke: none;
}
svg.customify-svg-icon.customify-svg-icon--brand .customify-brand-bg {
	stroke: #e3e5e8;      // beats the `[stroke] { stroke: currentColor }` wrapper rule
}
```

Three levels deep (`svg` + two classes), so it wins regardless of source order. `src/backend/customizer/scss/_control.scss` mirrors both rules twice — once for `.customify--icon-suggested-item`, once for `.customify--list-svg-icons` — so the Suggested row and the picker grid preview exactly what the front end paints, card ratio included.

#### Mono mode — the class contract with Pro

A consumer that wants the row to read as one quiet tonal strip puts **`customify-icons--mono` on an ANCESTOR** of the icons — normally the `<ul>`:

```scss
.customify-icons--mono svg.customify-svg-icon.customify-svg-icon--brand,
.customify-icons--mono svg.customify-svg-icon.customify-svg-icon--brand * {
	fill: currentColor;
	stroke: none;
}
.customify-icons--mono svg.customify-svg-icon.customify-svg-icon--brand .customify-brand-bg {
	fill: none;
	stroke: currentColor;
	stroke-width: 1;
}
.customify-icons--mono svg.customify-svg-icon.customify-svg-icon--brand .customify-brand-plate {
	fill: none;
}
```

**No `!important` anywhere, and none is needed** — the brand colours are presentation attributes, which sit at the bottom of the author cascade. The corollary is a hard rule for entry authors: **never put a brand colour in a `style=""` attribute.** An inline style would beat these rules and the icon would stay coloured in mono. `bin/test-svg-sanitize.php` fails any brand entry containing `style=`.

`customify-brand-plate` handles the marks that are **letterforms knocked out of a coloured plate** — JCB's white "JCB" in three bars, UnionPay's 银联 in three panels. Flattened to one colour those plates become a solid slab and the logo disappears; dropping them in mono leaves exactly the letterforms. Marks that are inherently two-tone discs (Diners Club, and the Mastercard / Maestro pair) flatten to their silhouette — that is the correct monochrome reading of those marks, not a bug.

`customify-icons--mono`, `customify-svg-icon--brand`, `customify-brand-bg` and `customify-brand-plate` are **public selectors** ([`../AGENTS.md`](../AGENTS.md) §4.4) agreed with Customify Pro. Do not rename them.

#### Pro-facing usage

A Pro item rendering a whole row sets the class once on the list:

```php
$icons = array( 'pay-visa', 'pay-mastercard', 'pay-amex', 'pay-paypal' );
$mono  = (bool) $this->get_setting( 'payment_methods_mono' );

echo '<ul class="customify-payment-methods' . ( $mono ? ' customify-icons--mono' : '' ) . '">';
foreach ( $icons as $key ) {
    echo '<li>' . customify_render_icon( array( 'type' => 'svg', 'icon' => $key ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-authored markup.
}
echo '</ul>';
```

For a single icon there is a shortcut — `mono` puts the class on the wrapper `<span>`, which is an ancestor of the `<svg>` just the same:

```php
customify_render_icon( array( 'type' => 'svg', 'icon' => 'pay-visa' ), array( 'mono' => true ) );
```

Both spellings are equivalent; prefer the `<ul>` for a row (one class instead of N wrappers). The flag is harmless on font icons and monochrome presets — the mono rules only match `--brand` entries.

Sizing is inherited: the card is `height: 1em`, so the row's `font-size` sets the logo height and `width: auto` keeps each card's ratio. Do not set `width` on the `<svg>`.

---

## 6. The `presets` control arg

An `icon` field may declare a shortlist of library keys. The picker then shows them as a one-click **Suggested** row above the search — the common case becomes one click instead of a scroll through ~2,000 FontAwesome glyphs. Order matters: the cart item leads with bags because that is what modern storefronts reach for.

```php
array(
    'name'    => 'wc_cart_icon',
    'type'    => 'icon',
    'section' => 'wc_cart',
    'presets' => array( 'bag', 'bag-filled', 'bag-outline', 'bag-handle', 'cart', 'cart-filled', 'basket', 'basket-filled' ),
    'default' => array( 'icon' => 'fa fa-shopping-basket', 'type' => 'font-awesome' ),
)
```

- **Optional everywhere.** A field without it renders the picker exactly as before.
- Keys the library doesn't know (a Pro icon whose plugin was deactivated) are skipped silently.
- The value travels as `$this->json['presets']` → `data-presets` on `.customify--icon-picker` → `IconPicker.renderSuggested()`.
- Selecting one stores `{ type: 'svg', icon: '<key>' }`, identical to picking it out of the grid.

---

## 7. Picker sidebar

`#customify--sidebar-icons` is a flex column: header (type select) → Suggested row → search → **exactly one** of the library browser or the Custom SVG paste panel. `IconPicker.applyType()` is the single owner of which panes are visible.

> The panes were previously stacked as absolutely positioned boxes, which let the Custom SVG textarea paint on top of a still-visible FontAwesome grid. They are normal-flow siblings now; hiding one cannot leave the other showing through.

Type dropdown order: `All Icon Types` → each font library → `SVG Icons` → `Custom SVG` (last, framed as an escape hatch).

The search box filters on `data-icon` (the CSS class or library key) **and** `data-label` (the lowercased human name), so "bag" and "shopping" both find Shopping Bag.

---

## 8. Sanitising pasted SVG

`customify_sanitize_svg()` runs **on save and again on render** — an SVG that somehow bypassed one pass is caught by the other. The pipeline, in order:

1. Strip HTML comments (Iconify / Tabler exports prefix a metadata comment; stripping it first means a valid paste isn't rejected for an invisible reason).
2. Reject anything that doesn't then open with `<svg`.
3. Reject anything over `customify/icon/svg_max_bytes` (default 20480) — an icon is never that big, and the cap runs *before* the parser.
4. Rewrite `javascript:` URLs on `href` / `xlink:href` to `#`, before kses, so a mangled allowlist can't re-approve them.
5. `wp_kses()` with an SVG-focused allowlist (`customify/icon/svg_allowed_html`), which drops `<script>`, `<foreignObject>`, `<image>`, `<a>`, `<animate>`, every `on*` handler and every non-SVG tag.
6. `width` / `height` are **absent from the allowlist for `svg` itself**, so kses strips them from the root element. A paste-in from Figma or an icon site routinely opens `<svg width="64" height="64">`, and those attributes beat the theme's CSS box — the icon would render 64×64 no matter what the section's Icon Size slider says. `viewBox` is kept (without it the stripped SVG has no coordinate system left and cannot scale). Children keep their own `width`/`height`: on a `<rect>` or `<use>` those are geometry, not rendered size.

The client-side `IconPicker.sanitizeSvg()` mirrors this so the picker's live preview can't execute anything, but **the server pass is the security boundary** — never rely on the JS one.

Run the checks:

```bash
wp eval-file bin/test-svg-sanitize.php
```

---

## 9. Front-end sizing + colour

Sizing lives on the markup, not on per-call-site CSS:

- `.customify-icon--svg > svg { width: 1em; height: 1em; fill: currentColor }` — an SVG drops into a font-icon slot and inherits whatever `font-size` that slot already had, so a section's existing Icon Size slider scales it with no new control.
- `svg.customify-svg-icon { fill: none; stroke: currentColor }` — higher specificity, so preset stroke glyphs are not flooded solid by the `fill` default above.
- `svg.customify-svg-icon.customify-svg-icon--filled { fill: currentColor; stroke: none }` — two classes deep, so the solid entries beat that outline default regardless of source order. The Customizer picker mirrors both rules (grid cells and Suggested cells) so a preview matches what the site renders.
- `[stroke] { stroke: currentColor }` on descendants — a pasted Tabler/Iconify icon with a hardcoded `stroke="#607d8b"` tints with the theme palette instead.
- `svg.customify-svg-icon.customify-svg-icon--brand { width: auto; height: 1em; fill: none; stroke: none }` — the brand-coloured payment cards escape every rule above and keep their 38×24 ratio. Full contract, including `customify-icons--mono`, in §5.6.

### 9.1 Cross-item size parity

Header items must agree on a default icon size or the row looks accidental. **One token owns it:**

```scss
.customify-header-items-v2 {
    --customify-header-icon-size: 20px;   // tune the whole header row here
}
```

Every icon+label item reads it with the pre-token value as the fallback, so a legacy site (no body class, §5.5) never sees the token and renders exactly what it always did:

| Item | Rule | Legacy | v2 |
|---|---|---|---|
| Search | `.search-icon svg { width: var(--customify-header-icon-size, 18px) }` | 18px | 20px |
| Cart | `.cart-icon { --customify-cart-icon-size: var(--customify-header-icon-size, 18px) }` | 18px | 20px |

**The sliders must keep outranking the token, so both use sites stay at their ORIGINAL selector specificity.** This is the trap to avoid: scoping a copy of the size under `.customify-header-items-v2 .item--search_icon .search-icon > svg` would be `(0,3,1)` and would silently beat the Search Icon Size slider's generated `body .search-icon svg` `(0,1,2)`. The token sets the default; it must never become the override.

| Consumer | Selector | Specificity | Outranked by |
|---|---|---|---|
| Search default | `.search-icon svg` | (0,1,1) | slider `body .search-icon svg` (0,1,2) |
| Cart default | `.cart-icon` | (0,1,0) | slider `.builder-header-wc_cart-item .cart-icon` (0,2,0) |

The cart cannot express its size as an `em` multiple at all: `1em` resolves against `.cart-icon`'s `font-size: 1.3em` inside `.cart-item-link { font-size: 0.85em }` → 17.68px, and a flat `px` width would make its slider inert (the slider writes `font-size`, which cannot override a px width). Hence the intermediate `--customify-cart-icon-size` property, which the slider writes alongside `font-size`:

```php
'css_format' => 'font-size: {{value}}; --customify-cart-icon-size: {{value}};',
```

The extra declaration is inert on a font-icon site — nothing reads the property unless an `<svg>` is present. The font-icon `<i>` is untouched and still rides `font-size: 1.3em` (22.98px): Font Awesome glyphs carry their own internal padding and are the saved value on existing sites, so their rendered size must not move.

Measured in a rendered harness against the built CSS — **v2: cart svg 20.00 × 20.00, search svg 20.00 × 20.00. Legacy: both 18.00 × 18.00.**

### 9.2 Shared header icon+label style

Cart, Search and Pro's User Icon are the same shape of thing — a glyph with an optional word beside it. [`src/frontend/scss/header/builder_items/_icon_label_items.scss`](../src/frontend/scss/header/builder_items/_icon_label_items.scss) is their single definition. **Pro must mirror this table exactly.**

| Property | Value | Note |
|---|---|---|
| `display` | `inline-flex` | shrink-to-fit inside the builder column |
| `align-items` | `center` | glyph and label share one optical centre line |
| `gap` | `8px` | owns icon↔label spacing; replaces the old `> span { margin: 0 2px }` |
| `font-size` | `0.875em` | the theme's `.text-small` token |
| `font-weight` | `500` | |
| `text-transform` | `none` | sentence case — beats the `text-uppercase` class still printed in the cart markup |
| `letter-spacing` | `0` | |
| `line-height` | `1.2` | restores a real line box (`.search-icon` sets `line-height: 0` for a bare glyph) |
| icon box | `display: inline-flex; align-items: center; justify-content: center; line-height: 0` | an `inline-block` leaves descender space under the SVG — that gap is what dropped the cart glyph below its label |
| icon size | `20px` | §9.1 — from `--customify-header-icon-size`, one token for the whole row |
| icon colour | `inherit` | same colour as the label, base and hover — see below |

`.cart-qty` keeps `position: absolute` against `.cart-icon`, which keeps `position: relative` — verified present and visible after the flex change. The font-icon's `top: -1px` optical nudge is zeroed under this scope: it existed to fake centring inside the old inline-block, and would now push the glyph *off* centre.

Measured: icon centre and label centre differ by **0.00px**, with the icon both before and after the label.

#### Colour

The glyph takes the **same colour as its own label**, like Search and the Pro User item.

`base/_skins.scss` deliberately paints it in the *hover* colour instead — `.light-mode .cart-item-link .cart-icon { color: black(0.8) }` while the link is `black(0.55)` (dark mode: `0.99` vs `0.79`), plus the same pair under `.header-menu-sidebar`. That was reasonable when the cart was the only icon+label item in the row; beside Search and User it just reads as a darker cart.

The v2 scope resets all four to `color: inherit`:

| Skin rule | Specificity | v2 override | Specificity |
|---|---|---|---|
| `.light-mode .cart-item-link .cart-icon` | (0,3,0) | `.customify-header-items-v2 .light-mode .cart-item-link .cart-icon` | (0,4,0) |
| `.dark-mode .cart-item-link .cart-icon` | (0,3,0) | same + `.dark-mode` | (0,4,0) |
| `.header-menu-sidebar.light-mode .cart-icon` | (0,3,0) | same + prefix | (0,4,0) |
| `.header-menu-sidebar.dark-mode .cart-icon` | (0,3,0) | same + prefix | (0,4,0) |

`inherit` rather than a restated colour, so every state keeps working for free: base and `:hover` both cascade from `.cart-item-link`, which the skins already colour, and any palette or Customizer colour a site sets on the link flows straight through.

Cart → **Icon Styling** (Advanced Styling) is unaffected — it generates `… .cart-icon i, … .cart-icon svg { color: … }`, targeting the glyph element itself, and an explicit colour on the child always beats `inherit` on its parent. Verified: a styled cart still renders the exact colour the control emits, in both modes.

Measured:

| | label | cart glyph | search glyph |
|---|---|---|---|
| v2 light | `rgba(0,0,0,.55)` | **`rgba(0,0,0,.55)`** | `rgba(0,0,0,.55)` |
| v2 dark | `rgba(255,255,255,.79)` | **`rgba(255,255,255,.79)`** | `rgba(255,255,255,.79)` |
| legacy light | `rgba(0,0,0,.55)` | `rgba(0,0,0,.8)` | `rgba(0,0,0,.55)` |
| legacy dark | `rgba(255,255,255,.79)` | `rgba(255,255,255,.99)` | `rgba(255,255,255,.79)` |

Hover verified by driving the link's own colour and reading the glyph: link, label and glyph resolve to the same value together.

**Gated.** Every rule is scoped under `.customify-header-items-v2` (§5.5), so only sites that first installed at 0.4.25+ get it. Verified unchanged on a legacy site: link `flex` / `uppercase` / `13.6px` / `600`, `<i>` `22.98px` with `top: -1px`, span margins `2px`. To promote it to the global default later, delete the wrapper selector — every declaration inside stands on its own.

Because both `fill` and `stroke` resolve to `currentColor`, any existing **colour** control that sets `color` on an ancestor already colours the SVG. When widening a styling selector for this, list both elements — e.g. the cart's `… .cart-icon i, … .cart-icon svg`. Adding the `svg` half cannot change an existing site: a font-icon cart has no `svg` element to match.

---

## 9.3 Menu Icon: empty means "the hamburger"

The Menu Icon item (`inc/customizer/configs/header/nav-icon.php`) is the one place where an **empty icon value is a real choice, not a missing one**. It draws a CSS hamburger — three `<span>`s, the squeeze-to-X animation, bar widths driven by the Small/Medium/Large control — and that is what every existing site renders.

So the field registers **no default**. `customify_render_icon()` returns `''` for the empty value, and `render()` falls through to the hamburger branch, whose markup is emitted as an explicit string so the output is **byte-identical** to what the item printed before the field existed (verified: 386 bytes, `cmp`-clean against the previous implementation). Clearing the icon in the picker — the `×` button, which writes `{type:'', icon:'', svg:''}` — puts the hamburger back. The field carries a description saying so, since the picker has no way to render "no icon" as a grid cell.

Suggested presets: `menu` `menu-lines` `menu-narrow` `menu-minimal` `menu-deep` `menu-left` `menu-right` — seven distinct hamburger silhouettes.

### Size

Same custom-property pattern as the cart, keyed off the shared token:

```scss
.menu-mobile-toggle {
    --customify-nav-icon-size: var(--customify-header-icon-size, 18px);
    &.is-size-small { --customify-nav-icon-size: calc(var(--customify-header-icon-size, 18px) - 4px); }
    &.is-size-large { --customify-nav-icon-size: calc(var(--customify-header-icon-size, 18px) + 8px); }
}
```

emitted for both the bare `.is-size-<step>` class and the per-device `.is-size-<device>-<step>` classes `render()` produces, the latter inside the matching media query.

| Step | v2 | legacy | hamburger bars today |
|---|---|---|---|
| Small | 16px | 14px | 19px wide |
| **Medium** | **20px** | **18px** | 22px wide |
| Large | 28px | 26px | 31px wide |

Medium is the shared header icon size by definition, so a Menu Icon sits at exactly the scale of the Search magnifier and the cart bag. The ±4 / +8 steps round the hamburger's own -3 / +9 spread to numbers that stay legible against an 18 or 20px token. Font Awesome values are sized with `font-size` on the `<i>` — a glyph has no box to set `width`/`height` on.

This section is **not** install-gated: no existing site can have an icon saved, so there is no prior rendering to preserve.

### Open state

`theme.js` toggles `is-active` on `.menu-mobile-toggle` (and on `.hamburger`) and never touches markup. So `render()` prints **both** glyphs — the chosen icon and the library `close` preset — and CSS shows one:

```scss
.nav-icon--icon-close { display: none; }
.menu-mobile-toggle.is-active {
    .nav-icon--icon-open  { display: none; }
    .nav-icon--icon-close { display: inline-flex; }
}
```

No JS change, no selector change, nothing to fetch mid-flip. The close glyph is always the theme's `close` preset rather than a counterpart from the chosen icon's own set: Font Awesome has no version-stable "times" class (`fa-times` vs `fa-xmark`) and a pasted custom SVG has no close counterpart at all, so one theme-authored glyph on the same 24-grid is the only answer that covers all three value types.

---

## 10. Font Awesome loading

`Customify_Font_Icons::is_used()` decides whether the Font Awesome stylesheet is enqueued, walking the builder layouts against the `customify/icon_used` allowlist when `customify/load-icons` returns `null`.

**Preset and custom SVG icons must never register into `customify/icon_used`** — they need no webfont, and claiming otherwise would pull 70KB onto a page that doesn't use a single glyph. The WooCommerce cart item does not register at all, which is why it is already correct.

> Known wart: `customify/load-icons` defaults to `true`, so `is_used()` short-circuits and the builder walk is effectively dead code on a default install. Items that *do* register (`button`, `social-icons`) register unconditionally, regardless of whether their saved icon is a font icon or an SVG. Tightening that is a separate change with its own FA-regression risk — don't fold it into an icon feature PR.

---

## 11. Known issues / edge cases

### Issue #1 — the read-only name input IS the storage

`.customify--input-icon-name` carries `data-name="<field>"`, and `getFieldValue()`'s `case "icon"` reads `icon` straight out of it. It must hold the raw stored value — a CSS class, or a library key. Only `custom-svg`, whose `icon` is a pure display label, may show prose there.

### Issue #2 — `.val()` does not fire `change`

Setting the type `<select>` programmatically does not emit a `change` event, so the panes must be driven by calling `applyType()` directly. An earlier `.trigger("change")` was the only thing keeping them in sync, and a missed trigger left the picker in a mixed state.

### Issue #3 — `build/` is gitignored

`build/` is **not** committed (`.gitignore`), despite what [`DEVELOPMENT.md`](DEVELOPMENT.md) §4 says. A site running a stale `build/` gets new PHP templates against old CSS/JS — which is exactly how the overlay bug in §7 became visible. Always `npm run build` after touching `src/`.

### Issue #4 — preset markup is rendered, not sanitised

`customify_get_svg_icon()` output is theme-authored, so it does not pass through `customify_sanitize_svg()` on the hot path. Anything added via `customify/svg_icons` is therefore trusted — a filter must never build its `body` from user input. `bin/test-svg-sanitize.php` does push every library entry through the sanitiser, so a non-conforming addition is caught in testing.
