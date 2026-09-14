<?php
/**
 * Preset inline-SVG icon library.
 *
 * The icon controls (`'type' => 'icon'`) store `{ type, icon, svg }`. Three
 * `type` values are understood by `customify_render_icon()`:
 *
 *   • a font library id (`font-awesome`, `font-awesome-v6`, …) — `icon` is the
 *     CSS class, rendered as `<i class="…">`. Unchanged since day one.
 *   • `custom-svg` — `svg` holds the user-pasted markup (sanitised on save AND
 *     on render by `customify_sanitize_svg()`).
 *   • `svg` — `icon` is a KEY into the preset library below. Nothing
 *     user-supplied is stored, so the markup never needs sanitising: it is
 *     shipped in this file.
 *
 * Why a preset library at all: a font-icon needs the whole Font Awesome
 * stylesheet (~70KB) downloaded before the first glyph paints, and the glyphs
 * themselves are a decade old. A preset inline SVG costs ~200 bytes, paints
 * with the first HTML byte, and inherits `currentColor` so every existing
 * colour control keeps working.
 *
 * ---------------------------------------------------------------------------
 * ATTRIBUTION — the path data below is COPIED VERBATIM from these upstream
 * sets. Do not hand-edit the geometry: re-extract from a newer upstream
 * release instead, so the set stays internally consistent.
 *
 *   • Lucide — ISC License. https://lucide.dev
 *     Package `lucide-static`. PRIMARY outline set.
 *
 *   • Tabler Icons — MIT License, Copyright (c) Paweł Kuna.
 *     https://tabler.io/icons — package `@tabler/icons`.
 *     `icons/outline` for the softer `-outline` variants (Tabler shares
 *     Lucide's grid exactly — 24×24, stroke 2, round caps — so the two mix
 *     without a seam) and `icons/filled` for the `-filled` solids.
 *
 *   • Heroicons — MIT License, Copyright (c) Tailwind Labs.
 *     https://heroicons.com — package `heroicons`, `24/solid`.
 *     Used for `bag-filled` only: neither Lucide nor Tabler ships a solid
 *     shopping bag. Heroicons' OUTLINE set is deliberately NOT used — it is
 *     drawn for stroke-width 1.5 and reads visibly lighter beside Lucide at 2.
 *
 *   • Phosphor Icons — MIT License. https://phosphoricons.com
 *     Package `@phosphor-icons/core`, `regular` weight. The rounded, generous
 *     silhouettes. Phosphor draws every weight as FILLED paths on a 256×256
 *     grid, which is why these carry `'style' => 'filled'` and their own
 *     viewBox below even though they read as outlines. Its `thin` weight was
 *     tried and dropped — hairlines disappear at header size.
 *
 *   • react-payment-logos — MIT License, Copyright (c) iamgutz.
 *     https://github.com/iamgutz/react-payment-logos — package
 *     `react-payment-logos`, the `logo` (full-colour) set. Source of the
 *     CARD SCHEME logos in the Payment family: Visa, American Express,
 *     Discover, Diners Club, JCB, UnionPay, PayPal. Upstream ships them as
 *     React components on a 780x500 grid; the `<path>` data below is those
 *     components' data verbatim, wrapped in one `<g transform>` that fits
 *     them into the shared 38x24 card (no geometry is edited).
 *
 *   • Simple Icons — CC0 1.0 Universal (public domain dedication).
 *     https://simpleicons.org — package `simple-icons`. Source of the
 *     WALLET / GATEWAY marks: Apple Pay, Google Pay, Stripe, Klarna
 *     (v16.31.0) and Amazon Pay (v13.21.0 — upstream retired the icon
 *     after that release). These are single-colour glyphs, so each is
 *     painted in its brand colour here.
 *
 * HAND-DRAWN — `pay-mastercard` and `pay-maestro` are the only geometry in
 * this file the theme drew itself. Their upstream full-colour logos are the
 * legacy wordmark lockups (9KB and 7KB of outlined type, unreadable at card
 * size); the modern scheme marks are two overlapping discs plus the lens
 * where they meet, which is ~270 bytes of plain `<circle>`/`<path>` and
 * reads correctly at 24px. Brand colours: Mastercard #EB001B / #F79E1B with
 * #FF5F00 overlap, Maestro #ED0006 / #0099DF with #6C6BBD overlap.
 *
 * TRADEMARKS — payment marks are trademarks of their respective owners and
 * are reproduced here solely to identify the payment methods a shop accepts,
 * which is what the scheme brand guidelines call an acceptance mark. The
 * LICENCES above cover the artwork files only; they grant no trademark
 * rights, and nothing here implies endorsement.
 *
 * Only the upstream `<svg>` wrapper is discarded during extraction, plus
 * Tabler's transparent `M0 0h24v24H0z` bounding-box path (sprite-build
 * padding) and per-set `class` / `data-slot` hooks. Every `<path>`, `<circle>`
 * and `<rect>` below is upstream's, byte for byte.
 *
 * NOT USED — Shopify Dawn. Dawn's icons are the reference look, but its
 * LICENSE.md is not plain MIT: the grant is limited to "themes that integrate
 * or interoperate with Shopify software or services", with all other uses
 * "strictly prohibited". Customify is a WordPress theme distributed under GPL
 * on WordPress.org, so shipping Dawn's assets would fall outside that grant
 * AND break GPL compatibility. Phosphor's `regular` weight gives the same
 * rounded, minimal silhouettes under a licence we can actually use.
 * ---------------------------------------------------------------------------
 *
 * ICON STYLE CONTRACT — every entry must honour it, including icons added
 * through the `customify/svg_icons` filter, or the set stops looking like one
 * set. `customify_get_svg_icon()` supplies the root element, so an entry only
 * has to provide contract-compliant geometry plus its paint style and viewBox:
 *
 *   • a SQUARE `viewBox` (`0 0 24 24`, `0 0 256 256`, …) declared per entry —
 *     upstream sets disagree on grid size and rescaling by hand would mean
 *     editing geometry, which this file does not do
 *   • NO `width` / `height` attributes — CSS sizes the icon (see
 *     `src/frontend/scss/base/_icons.scss`)
 *   • `'style' => 'outline'` → rendered `fill="none" stroke="currentColor"`,
 *     stroke-width 2, round caps and joins (Lucide's and Tabler's native
 *     drawing weight)
 *   • `'style' => 'filled'` → rendered `fill="currentColor" stroke="none"`
 *     plus the extra class `customify-svg-icon--filled`, which the frontend
 *     CSS needs in order to beat the outline default
 *   • `'style' => 'brand'` → rendered with NO root paint at all, plus the
 *     extra class `customify-svg-icon--brand`. Colour lives on the body's
 *     own elements. See BRAND STYLE below — brand entries are the one
 *     documented exception to the square-viewBox rule.
 *   • `aria-hidden="true"` + `focusable="false"` — icons here are decorative;
 *     the accessible name comes from the surrounding link/label
 *
 * `style` is the PAINT MODE, not the visual weight: Phosphor's hairline glyphs
 * are `filled` because their geometry is a filled outline, and they still look
 * like the thinnest icons in the set.
 *
 * BRAND STYLE — `'style' => 'brand'` is the third paint mode and the only one
 * that carries colour. It exists for payment-method logos, which are not
 * glyphs: a merchant needs Visa blue and Mastercard's red/orange discs, not a
 * currentColor silhouette. The rules differ from the two monochrome modes:
 *
 *   • a LANDSCAPE `viewBox` — the whole Payment family shares `0 0 38 24`,
 *     the ISO card ratio rounded to whole units. `bin/test-svg-sanitize.php`
 *     exempts `brand` from the square-viewBox check for exactly this reason.
 *   • NO `fill` / `stroke` on the root at all. Every element in the body
 *     declares its own `fill`, so nothing is left to inherit.
 *   • the extra class `customify-svg-icon--brand`, which both the frontend
 *     (`src/frontend/scss/base/_icons.scss`) and the Customizer picker
 *     (`src/backend/customizer/scss/_control.scss`) use to exempt the entry
 *     from the outline default and from the `fill: currentColor` wrapper
 *     rule, and to size it `height: 1em; width: auto` so the card keeps its
 *     aspect ratio instead of being squashed into a 1em square.
 *   • a rounded card rect FIRST in the body, classed `customify-brand-bg`,
 *     so a logo drawn for a white background still reads on a dark footer.
 *
 * MONO MODE — a consumer (the Pro "Payment Methods" footer item, a child
 * theme) puts `customify-icons--mono` on an ANCESTOR — the `<ul>`, or the
 * wrapper `<span>` via `customify_render_icon( $value, array( 'mono' => true ) )`.
 * Under it the frontend CSS repaints every brand entry with `currentColor`
 * and turns the card rect into an outline. No `!important` is involved and
 * none is needed: the colours above are PRESENTATION ATTRIBUTES, which lose
 * to any stylesheet declaration. Never move a brand colour into a `style=""`
 * attribute — an inline style would beat the mono rule and the icon would
 * stay coloured.
 *
 * NAMING — `-outline` marks an alternate, softer silhouette of the same
 * subject (`bag` is Lucide's squared-shoulder bag, `bag-outline` Tabler's
 * rounded one); `-alt` a second distinct one; `-soft` a rounder one; and
 * `-filled` the solid counterpart. The per-field "Suggested" rows offer these
 * as a quick visual choice.
 *
 * @package Customify
 * @since   0.4.25
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'customify_get_svg_icons' ) ) {
	/**
	 * The preset SVG icon library.
	 *
	 * Shape: `array( '<key>' => array( 'label', 'source', 'style', 'viewbox',
	 * 'body' ) )` where `body` is the INNER markup of the `<svg>` element (no
	 * `<svg>` wrapper — `customify_get_svg_icon()` adds it so every icon gets
	 * a consistent, contract-compliant root element).
	 *
	 * `source` is informational only — nothing reads it at runtime. It exists
	 * so a maintainer can re-extract an icon from a newer upstream release
	 * without guessing which set it came from.
	 *
	 * `style` and `viewbox` are optional for filtered-in entries: they default
	 * to `outline` and `0 0 24 24`, which is what a hand-written Lucide-style
	 * addition wants anyway.
	 *
	 * Keys are stored in `theme_mod`s. Treat them as public API: never rename
	 * or remove a key once released, only add. A removed key renders nothing
	 * (no notice), but the site silently loses its icon.
	 *
	 * @since 0.4.25
	 *
	 * @return array<string, array{label: string, source: string, style: string, viewbox: string, body: string}>
	 */
	function customify_get_svg_icons() {
		static $icons = null;

		if ( null === $icons ) {
			$library = array(
				// -------------------------------------------------- Commerce
				// Bags lead: modern storefronts reach for a bag long before a
				// trolley, and the header cart item's Suggested row is ordered to
				// match. Outline and solid silhouettes of the same subject sit
				// next to each other so a shop can match its own line weight.
				'bag'                => array(
					'label'   => __( 'Shopping Bag', 'customify' ),
					'source'  => 'lucide/shopping-bag',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/>',
				),
				'bag-outline'        => array(
					'label'   => __( 'Shopping Bag (Soft)', 'customify' ),
					'source'  => 'tabler/shopping-bag',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/>',
				),
				'bag-handle'         => array(
					'label'   => __( 'Handbag', 'customify' ),
					'source'  => 'lucide/handbag',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M2.048 18.566A2 2 0 0 0 4 21h16a2 2 0 0 0 1.952-2.434l-2-9A2 2 0 0 0 18 8H6a2 2 0 0 0-1.952 1.566z"/><path d="M8 11V6a4 4 0 0 1 8 0v5"/>',
				),
				'bag-tote'           => array(
					'label'   => __( 'Tote Bag', 'customify' ),
					'source'  => 'phosphor/tote',
					'style'   => 'filled',
					'viewbox' => '0 0 256 256',
					'body'    => '<path d="M236,69.4A16.13,16.13,0,0,0,223.92,64H176a48,48,0,0,0-96,0H32.08a16.13,16.13,0,0,0-12,5.4,16,16,0,0,0-3.92,12.48l14.26,120a16,16,0,0,0,16,14.12H209.67a16,16,0,0,0,16-14.12l14.26-120A16,16,0,0,0,236,69.4ZM128,32a32,32,0,0,1,32,32H96A32,32,0,0,1,128,32Zm81.76,168a.13.13,0,0,1-.09,0H46.25L32.08,80H80v24a8,8,0,0,0,16,0V80h64v24a8,8,0,0,0,16,0V80h48Z"/>',
				),
				'bag-filled'         => array(
					'label'   => __( 'Shopping Bag (Solid)', 'customify' ),
					'source'  => 'heroicons-solid/shopping-bag',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 0 0 4.25 22.5h15.5a1.875 1.875 0 0 0 1.865-2.071l-1.263-12a1.875 1.875 0 0 0-1.865-1.679H16.5V6a4.5 4.5 0 1 0-9 0ZM12 3a3 3 0 0 0-3 3v.75h6V6a3 3 0 0 0-3-3Zm-3 8.25a3 3 0 1 0 6 0v-.75a.75.75 0 0 1 1.5 0v.75a4.5 4.5 0 1 1-9 0v-.75a.75.75 0 0 1 1.5 0v.75Z" clip-rule="evenodd"/>',
				),
				'cart'               => array(
					'label'   => __( 'Shopping Cart', 'customify' ),
					'source'  => 'lucide/shopping-cart',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/>',
				),
				'cart-outline'       => array(
					'label'   => __( 'Cart (Simple)', 'customify' ),
					'source'  => 'tabler/shopping-cart',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>',
				),
				'cart-filled'        => array(
					'label'   => __( 'Cart (Solid)', 'customify' ),
					'source'  => 'tabler-filled/shopping-cart',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M6 2a1 1 0 0 1 .993 .883l.007 .117v1.068l13.071 .935a1 1 0 0 1 .929 1.024l-.01 .114l-1 7a1 1 0 0 1 -.877 .853l-.113 .006h-12v2h10a3 3 0 1 1 -2.995 3.176l-.005 -.176l.005 -.176c.017 -.288 .074 -.564 .166 -.824h-5.342a3 3 0 1 1 -5.824 1.176l-.005 -.176l.005 -.176a3.002 3.002 0 0 1 1.995 -2.654v-12.17h-1a1 1 0 0 1 -.993 -.883l-.007 -.117a1 1 0 0 1 .883 -.993l.117 -.007h2zm0 16a1 1 0 1 0 0 2a1 1 0 0 0 0 -2m11 0a1 1 0 1 0 0 2a1 1 0 0 0 0 -2"/>',
				),
				'basket'             => array(
					'label'   => __( 'Basket', 'customify' ),
					'source'  => 'lucide/shopping-basket',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/>',
				),
				'basket-alt'         => array(
					'label'   => __( 'Basket (Soft)', 'customify' ),
					'source'  => 'tabler/basket',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M5.001 8h13.999a2 2 0 0 1 1.977 2.304l-1.255 7.152a3 3 0 0 1 -2.966 2.544h-9.512a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304"/><path d="M17 10l-2 -6"/><path d="M7 10l2 -6"/>',
				),
				'basket-filled'      => array(
					'label'   => __( 'Basket (Solid)', 'customify' ),
					'source'  => 'tabler-filled/basket',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M15.949 3.684l1.104 3.316h1.947a3 3 0 0 1 2.962 3.477l-1.252 7.131a4 4 0 0 1 -3.954 3.392h-9.512a3.994 3.994 0 0 1 -3.95 -3.371l-1.258 -7.173a3 3 0 0 1 2.964 -3.456h1.945l1.105 -3.316a1 1 0 0 1 1.898 .632l-.895 2.684h5.893l-.895 -2.684a1 1 0 1 1 1.898 -.632m-3.949 7.316a3 3 0 0 0 -2.995 2.824l-.005 .176a3 3 0 1 0 3 -3"/>',
				),
				// --------------------------------------------------- Account
				// Pro's User Icon item reuses `contact` and `id-card`; keep them.
				'user'               => array(
					'label'   => __( 'User', 'customify' ),
					'source'  => 'lucide/user-round',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
				),
				'user-alt'           => array(
					'label'   => __( 'User (Square)', 'customify' ),
					'source'  => 'lucide/user',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
				),
				'user-outline'       => array(
					'label'   => __( 'User (Soft)', 'customify' ),
					'source'  => 'tabler/user',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>',
				),
				'user-filled'        => array(
					'label'   => __( 'User (Solid)', 'customify' ),
					'source'  => 'tabler-filled/user',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z"/><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z"/>',
				),
				'user-circle'        => array(
					'label'   => __( 'User Circle', 'customify' ),
					'source'  => 'lucide/circle-user-round',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M17.925 20.056a6 6 0 0 0-11.851.001"/><circle cx="12" cy="11" r="4"/><circle cx="12" cy="12" r="10"/>',
				),
				'user-circle-alt'    => array(
					'label'   => __( 'User Circle (Soft)', 'customify' ),
					'source'  => 'tabler/user-circle',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 10a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"/>',
				),
				'user-circle-soft'   => array(
					'label'   => __( 'User Circle (Round)', 'customify' ),
					'source'  => 'phosphor/user-circle',
					'style'   => 'filled',
					'viewbox' => '0 0 256 256',
					'body'    => '<path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24ZM74.08,197.5a64,64,0,0,1,107.84,0,87.83,87.83,0,0,1-107.84,0ZM96,120a32,32,0,1,1,32,32A32,32,0,0,1,96,120Zm97.76,66.41a79.66,79.66,0,0,0-36.06-28.75,48,48,0,1,0-59.4,0,79.66,79.66,0,0,0-36.06,28.75,88,88,0,1,1,131.52,0Z"/>',
				),
				'user-circle-filled' => array(
					'label'   => __( 'User Circle (Solid)', 'customify' ),
					'source'  => 'heroicons-solid/user-circle',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd"/>',
				),
				'user-square'        => array(
					'label'   => __( 'User Square', 'customify' ),
					'source'  => 'tabler/user-square-rounded',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6"/><path d="M12 3c7.2 0 9 1.8 9 9c0 7.2 -1.8 9 -9 9c-7.2 0 -9 -1.8 -9 -9c0 -7.2 1.8 -9 9 -9"/><path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05"/>',
				),
				'contact'            => array(
					'label'   => __( 'Contact', 'customify' ),
					'source'  => 'lucide/contact-round',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M16 2v2"/><path d="M17.915 21a6 6 0 10-12 0"/><path d="M8 2v2"/><circle cx="12" cy="11" r="4"/><rect x="3" y="3" width="18" height="18" rx="2"/>',
				),
				'id-card'            => array(
					'label'   => __( 'ID Card', 'customify' ),
					'source'  => 'lucide/id-card',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M13 19a4 4 0 00-8 0"/><path d="M16 10h2"/><path d="M16 14h2"/><circle cx="9" cy="12" r="3"/><rect x="2" y="5" width="20" height="14" rx="2"/>',
				),
				// ------------------------------------------ Wishlist / saved
				// Pro's Wishlist item reuses the heart pair.
				'heart'              => array(
					'label'   => __( 'Heart', 'customify' ),
					'source'  => 'lucide/heart',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>',
				),
				'heart-outline'      => array(
					'label'   => __( 'Heart (Soft)', 'customify' ),
					'source'  => 'tabler/heart',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"/>',
				),
				'heart-plus'         => array(
					'label'   => __( 'Heart with Plus', 'customify' ),
					'source'  => 'lucide/heart-plus',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m14.479 19.374-.971.939a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.219 1.49"/><path d="M15 15h6"/><path d="M18 12v6"/>',
				),
				'heart-filled'       => array(
					'label'   => __( 'Heart (Solid)', 'customify' ),
					'source'  => 'tabler-filled/heart',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M6.979 3.074a6 6 0 0 1 4.988 1.425l.037 .033l.034 -.03a6 6 0 0 1 4.733 -1.44l.246 .036a6 6 0 0 1 3.364 10.008l-.18 .185l-.048 .041l-7.45 7.379a1 1 0 0 1 -1.313 .082l-.094 -.082l-7.493 -7.422a6 6 0 0 1 3.176 -10.215z"/>',
				),
				'bookmark'           => array(
					'label'   => __( 'Bookmark', 'customify' ),
					'source'  => 'lucide/bookmark',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M17 3a2 2 0 0 1 2 2v15a1 1 0 0 1-1.496.868l-4.512-2.578a2 2 0 0 0-1.984 0l-4.512 2.578A1 1 0 0 1 5 20V5a2 2 0 0 1 2-2z"/>',
				),
				'bookmark-filled'    => array(
					'label'   => __( 'Bookmark (Solid)', 'customify' ),
					'source'  => 'tabler-filled/bookmark',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M14 2a5 5 0 0 1 5 5v14a1 1 0 0 1 -1.555 .832l-5.445 -3.63l-5.444 3.63a1 1 0 0 1 -1.55 -.72l-.006 -.112v-14a5 5 0 0 1 5 -5h4z"/>',
				),
				'star'               => array(
					'label'   => __( 'Star', 'customify' ),
					'source'  => 'lucide/star',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
				),
				'star-filled'        => array(
					'label'   => __( 'Star (Solid)', 'customify' ),
					'source'  => 'tabler-filled/star',
					'style'   => 'filled',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"/>',
				),
				// ------------------------------------------------ General UI
				// Deliberately lean and header-oriented — this is not a general
				// icon font, and every extra key is one more string a shop has to
				// scroll past to reach the icon it actually wants.
				'search'             => array(
					'label'   => __( 'Search', 'customify' ),
					'source'  => 'lucide/search',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/>',
				),
				'menu'               => array(
					'label'   => __( 'Menu', 'customify' ),
					'source'  => 'lucide/menu',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/>',
				),
				'menu-lines'         => array(
					'label'   => __( 'Menu (Wide)', 'customify' ),
					'source'  => 'tabler/baseline-density-medium',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 20h16"/><path d="M4 12h16"/><path d="M4 4h16"/>',
				),
				'menu-narrow'        => array(
					'label'   => __( 'Menu (Narrow)', 'customify' ),
					'source'  => 'tabler/menu-2',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 6l16 0"/><path d="M4 12l16 0"/><path d="M4 18l16 0"/>',
				),
				'menu-minimal'       => array(
					'label'   => __( 'Menu (Two Lines)', 'customify' ),
					'source'  => 'tabler/menu',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 8l16 0"/><path d="M4 16l16 0"/>',
				),
				'menu-deep'          => array(
					'label'   => __( 'Menu (Staggered)', 'customify' ),
					'source'  => 'tabler/menu-deep',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M4 6h16"/><path d="M7 12h13"/><path d="M10 18h10"/>',
				),
				'menu-left'          => array(
					'label'   => __( 'Menu (Left)', 'customify' ),
					'source'  => 'lucide/align-left',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M21 5H3"/><path d="M15 12H3"/><path d="M17 19H3"/>',
				),
				'menu-right'         => array(
					'label'   => __( 'Menu (Right)', 'customify' ),
					'source'  => 'lucide/align-right',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M21 5H3"/><path d="M21 12H9"/><path d="M21 19H7"/>',
				),
				'close'              => array(
					'label'   => __( 'Close', 'customify' ),
					'source'  => 'lucide/x',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
				),
				'chevron-down'       => array(
					'label'   => __( 'Chevron Down', 'customify' ),
					'source'  => 'lucide/chevron-down',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m6 9 6 6 6-6"/>',
				),
				'arrow-right'        => array(
					'label'   => __( 'Arrow Right', 'customify' ),
					'source'  => 'lucide/arrow-right',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
				),
				'external-link'      => array(
					'label'   => __( 'External Link', 'customify' ),
					'source'  => 'lucide/external-link',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
				),
				'home'               => array(
					'label'   => __( 'Home', 'customify' ),
					'source'  => 'lucide/house',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
				),
				'phone'              => array(
					'label'   => __( 'Phone', 'customify' ),
					'source'  => 'lucide/phone',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
				),
				'mail'               => array(
					'label'   => __( 'Mail', 'customify' ),
					'source'  => 'lucide/mail',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/>',
				),
				'map-pin'            => array(
					'label'   => __( 'Map Pin', 'customify' ),
					'source'  => 'lucide/map-pin',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
				),
				'globe'              => array(
					'label'   => __( 'Globe', 'customify' ),
					'source'  => 'lucide/globe',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
				),
				'clock'              => array(
					'label'   => __( 'Clock', 'customify' ),
					'source'  => 'lucide/clock',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
				),
				'calendar'           => array(
					'label'   => __( 'Calendar', 'customify' ),
					'source'  => 'lucide/calendar',
					'style'   => 'outline',
					'viewbox' => '0 0 24 24',
					'body'    => '<path d="M8 2v3"/><path d="M16 2v3"/><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/>',
				),
				// -------------------------------------------------- Payment
				// BRAND-COLOURED logos, the only `brand` entries in the library.
				// They break the square-viewBox rule on purpose: a payment mark is
				// a landscape card, so the whole family shares one 38x24 card
				// ratio with a rounded `.customify-brand-bg` rect behind the logo
				// (white unless the brand's own badge is coloured - Klarna pink,
				// Stripe purple). See the BRAND STYLE section in the file header.
				'pay-visa'           => array(
					'label'   => __( 'Visa', 'customify' ),
					'source'  => 'react-payment-logos/Visa (MIT)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(0.08 -0.129) scale(0.04851)"><path fill="#0E4595" d="m293.2 348.73 33.359-195.76h53.358l-33.384 195.76H293.2zm246.11-191.54c-10.569-3.966-27.135-8.222-47.821-8.222-52.726 0-89.863 26.551-90.181 64.604-.297 28.129 26.515 43.822 46.754 53.185 20.771 9.598 27.752 15.716 27.652 24.283-.133 13.123-16.586 19.115-31.924 19.115-21.355 0-32.701-2.967-50.225-10.273l-6.878-3.111-7.487 43.822c12.463 5.467 35.508 10.199 59.438 10.445 56.09 0 92.502-26.248 92.916-66.885.199-22.27-14.016-39.215-44.801-53.188-18.65-9.056-30.072-15.099-29.951-24.269 0-8.137 9.668-16.838 30.56-16.838 17.446-.271 30.088 3.534 39.936 7.5l4.781 2.259 7.231-42.427m137.31-4.223h-41.23c-12.772 0-22.332 3.486-27.94 16.234l-79.245 179.4h56.031s9.159-24.121 11.231-29.418c6.123 0 60.555.084 68.336.084 1.596 6.854 6.492 29.334 6.492 29.334h49.512l-43.187-195.64zm-65.417 126.41c4.414-11.279 21.26-54.724 21.26-54.724-.314.521 4.381-11.334 7.074-18.684l3.606 16.878s10.217 46.729 12.353 56.527h-44.293v.003zm-363.3-126.41-52.239 133.5-5.565-27.129c-9.726-31.274-40.025-65.157-73.898-82.12l47.767 171.2 56.455-.063 84.004-195.39-56.524-.001"></path><path fill="#F2AE14" d="M146.92 152.96H60.879l-.682 4.073c66.939 16.204 111.23 55.363 129.62 102.42l-18.709-89.96c-3.229-12.396-12.597-16.096-24.186-16.528"></path></g>',
				),
				'pay-mastercard'     => array(
					'label'   => __( 'Mastercard', 'customify' ),
					'source'  => 'hand-drawn (Customify) — two-disc scheme mark',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><circle cx="15.2" cy="12" r="6" fill="#EB001B"/><circle cx="22.8" cy="12" r="6" fill="#F79E1B"/><path fill="#FF5F00" d="M19 7.36a6 6 0 0 1 0 9.28 6 6 0 0 1 0-9.28z"/>',
				),
				'pay-maestro'        => array(
					'label'   => __( 'Maestro', 'customify' ),
					'source'  => 'hand-drawn (Customify) — two-disc scheme mark',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><circle cx="15.2" cy="12" r="6" fill="#ED0006"/><circle cx="22.8" cy="12" r="6" fill="#0099DF"/><path fill="#6C6BBD" d="M19 7.36a6 6 0 0 1 0 9.28 6 6 0 0 1 0-9.28z"/>',
				),
				'pay-amex'           => array(
					'label'   => __( 'American Express', 'customify' ),
					'source'  => 'react-payment-logos/Amex (MIT)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(3 1.743) scale(0.04103)"><path fill="#2557D6" d="m575.61 145.11-15.092 35.039h30.266L575.61 145.11zm-174.15 21.713c2.845-1.422 4.52-4.515 4.52-8.356 0-3.764-1.76-6.49-4.604-7.771-2.591-1.42-6.577-1.584-10.399-1.584h-27v19.523h26.638c4.266.001 7.831-.059 10.845-1.812zM55.49 145.11l-14.921 35.039h29.932L55.49 145.11zm694.7 224.47h-42.344v-18.852h42.173c4.181 0 7.109-.525 8.872-2.178 1.667-1.473 2.609-3.555 2.592-5.732 0-2.562-1.062-4.596-2.68-5.813-1.588-1.342-3.907-1.953-7.726-1.953-20.588-.67-46.273.609-46.273-27.211 0-12.75 8.451-26.172 31.461-26.172h43.677v-17.492h-40.58c-12.246 0-21.144 2.81-27.443 7.181v-7.181h-60.022c-9.597 0-20.863 2.279-26.191 7.181v-7.181h-107.19v7.181c-8.529-5.897-22.925-7.181-29.565-7.181h-70.702v7.181c-6.747-6.262-21.758-7.181-30.902-7.181H308.22l-18.104 18.775-16.959-18.775h-118.2v122.68h115.97l18.655-19.076 17.575 19.076 71.484.06v-28.859h7.03c9.484.146 20.67-.223 30.542-4.311v33.106h58.962v-31.976h2.844c3.628 0 3.988.146 3.988 3.621v28.348h179.12c11.372 0 23.26-2.786 29.841-7.853v7.853h56.817c11.822 0 23.369-1.588 32.154-5.653V358.34c-5.324 7.462-15.707 11.245-29.751 11.245zm-363.58-28.967h-27.36v29.488h-42.618l-27-29.102-28.058 29.102H174.72v-87.914h88.19l26.976 28.818 27.89-28.818h70.064c17.401 0 36.952 4.617 36.952 28.963 0 24.422-19.016 29.463-38.182 29.463zm131.56-3.986c3.097 4.291 3.544 8.297 3.634 16.047v17.428h-22.016v-10.998c0-5.289.533-13.121-3.544-17.209-3.2-3.148-8.086-3.9-16.088-3.9h-23.432v32.107h-22.031v-87.914h50.62c11.105 0 19.188.473 26.384 4.148 6.92 4.006 11.275 9.494 11.275 19.523-.002 14.031-9.769 21.189-15.541 23.389 4.878 1.725 8.866 4.818 10.739 7.379zm90.575-36.258h-51.346v15.982h50.091v17.938h-50.091v17.492l51.346.078v18.242h-73.182v-87.914h73.182v18.182zm56.344 69.731h-42.705v-18.852h42.535c4.16 0 7.109-.527 8.957-2.178 1.507-1.359 2.591-3.336 2.591-5.73 0-2.564-1.174-4.598-2.676-5.818-1.678-1.34-3.993-1.947-7.809-1.947-20.506-.674-46.186.605-46.186-27.213 0-12.752 8.363-26.174 31.35-26.174h43.96v18.709h-40.225c-3.987 0-6.579.146-8.783 1.592-2.405 1.424-3.295 3.535-3.295 6.322 0 3.316 2.04 5.574 4.797 6.549 2.314.771 4.797.996 8.533.996l11.805.309c11.899.273 20.073 2.25 25.04 7.068 4.266 4.232 6.559 9.578 6.559 18.625-.002 18.913-12.335 27.742-34.448 27.742zm-170.06-68.313c-2.649-1.508-6.559-1.588-10.461-1.588h-27.001v19.744h26.64c4.265 0 7.892-.145 10.822-1.812 2.842-1.646 4.543-4.678 4.543-8.438s-1.701-6.482-4.543-7.906zm244.99-1.59c-3.988 0-6.641.145-8.873 1.588-2.314 1.426-3.202 3.537-3.202 6.326 0 3.314 1.953 5.572 4.794 6.549 2.315.771 4.796.996 8.448.996l11.887.303c11.99.285 19.998 2.262 24.879 7.08.889.668 1.423 1.42 2.034 2.174v-25.014h-39.965l-.002-.002zm-352.65 0h-28.59v22.391h28.336c8.424 0 13.663-4.006 13.667-11.611-.004-7.688-5.497-10.78-13.413-10.78zm-190.81 0v15.984h48.136v17.938h-48.136v17.49h53.909l25.047-25.791-23.983-25.621h-54.973zm140.77 61.479v-70.482l-33.664 34.674 33.664 35.808zm-138.93-141.15v15.148h183.19l-.085-32.046h3.545c2.483.083 3.205.302 3.205 4.229v27.818h94.748v-7.461c7.642 3.924 19.527 7.461 35.168 7.461h39.86l8.531-19.522h18.913l8.342 19.522h76.811v-18.544l11.629 18.543h61.555v-122.58h-60.915v14.477l-8.53-14.477h-62.507v14.477l-7.833-14.477h-84.434c-14.135 0-26.555 1.89-36.591 7.158v-7.158h-58.268v7.158c-6.387-5.43-15.089-7.158-24.762-7.158h-212.87l-14.282 31.662-14.668-31.662H91.104v14.477l-7.367-14.477h-57.18L.004 171.378v46.621l39.264-87.894h32.579l37.29 83.217v-83.217h35.789l28.695 59.625 26.362-59.625h36.507v87.894h-22.475l-.082-68.837-31.796 68.837h-19.252l-31.877-68.898v68.898h-44.6l-8.425-19.605H32.329l-8.512 19.605H.003v17.682h37.466l8.447-19.523H64.83l8.425 19.523h73.713v-14.927l6.579 14.989h38.266l6.58-15.214zm288.67-80.176c7.085-7.015 18.188-10.25 33.298-10.25h21.227v18.833h-20.782c-7.998 0-12.521 1.14-16.871 5.208-3.74 3.7-6.304 10.696-6.304 19.908 0 9.417 1.955 16.206 6.028 20.641 3.376 3.478 9.513 4.533 15.283 4.533h9.851l30.902-69.12h32.853l37.124 83.134v-83.133h33.386l38.543 61.213v-61.213h22.46v87.891h-31.072l-41.562-65.968v65.968h-44.656l-8.532-19.605h-45.55l-8.278 19.605h-25.66c-10.657 0-24.151-2.258-31.793-9.722-7.707-7.462-11.713-17.571-11.713-33.553-.004-13.037 2.389-24.953 11.818-34.37zm-45.101-10.249h22.372v87.894h-22.372v-87.894zm-100.87 0h50.432c11.203 0 19.464.285 26.553 4.21 6.936 3.926 11.095 9.658 11.095 19.46 0 14.015-9.763 21.254-15.448 23.429 4.796 1.75 8.896 4.841 10.849 7.401 3.096 4.372 3.629 8.277 3.629 16.126v17.267h-22.115l-.083-11.084c0-5.29.528-12.896-3.461-17.122-3.203-3.09-8.088-3.763-15.983-3.763h-23.538v31.97h-21.927l-.003-87.894zm-88.393 0h73.249v18.303h-51.32v15.843h50.088v18.017h-50.088v17.553h51.32v18.177h-73.249v-87.893z"></path></g>',
				),
				'pay-discover'       => array(
					'label'   => __( 'Discover', 'customify' ),
					'source'  => 'react-payment-logos/Discover (MIT)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(-0.006 -0.184) scale(0.04874)"><path fill="#F47216" d="M409.41 197.26c30.938 0 56.02 23.58 56.02 52.709v.033c0 29.129-25.082 52.742-56.02 52.742-30.941 0-56.022-23.613-56.022-52.742v-.033c0-29.129 25.081-52.709 56.022-52.709z"></path><path fill="#000000" d="M321.43 197.94c8.836 0 16.247 1.785 25.27 6.09v22.753c-8.544-7.863-15.955-11.154-25.757-11.154-19.265 0-34.413 15.015-34.413 34.051 0 20.074 14.681 34.195 35.368 34.195 9.312 0 16.586-3.12 24.802-10.856v22.764c-9.343 4.142-16.912 5.775-25.757 5.775-31.277 0-55.581-22.597-55.581-51.736-.002-28.83 24.949-51.882 56.068-51.882zm-97.113.626c11.546 0 22.109 3.721 30.942 10.994l-10.748 13.248c-5.351-5.646-10.411-8.027-16.563-8.027-8.854 0-15.301 4.744-15.301 10.988 0 5.354 3.618 8.188 15.944 12.481 23.364 8.043 30.289 15.176 30.289 30.926 0 19.193-14.976 32.554-36.319 32.554-15.631 0-26.993-5.795-36.457-18.871l13.268-12.03c4.73 8.608 12.622 13.223 22.42 13.223 9.163 0 15.947-5.95 15.947-13.983 0-4.164-2.056-7.733-6.158-10.258-2.066-1.195-6.158-2.978-14.199-5.646-19.292-6.538-25.91-13.527-25.91-27.186-.001-16.227 14.213-28.413 32.845-28.413zm234.72 1.729h22.436l28.084 66.592 28.447-66.592h22.267l-45.493 101.69h-11.054l-44.687-101.69zm-301.21.152h20.541v99.143h-20.541v-99.143zm411.73 0h58.253v16.799h-37.726v22.006h36.336v16.791h-36.336v26.762h37.726v16.785h-58.253v-99.143zm115.59 57.377c15.471-2.965 23.983-12.926 23.983-28.105 0-18.562-13.575-29.271-37.266-29.271H641.41v99.144h20.516v-39.83h2.681l28.43 39.828h25.26l-33.15-41.766zm-17.218-11.736h-6.002v-30.025h6.326c12.791 0 19.744 5.049 19.744 14.697.002 9.967-6.951 15.328-20.068 15.328zm-576.09-45.641H61.69v99.143h29.992c15.946 0 27.465-3.543 37.573-11.445 12.014-9.359 19.117-23.467 19.117-38.057.001-29.259-23.221-49.641-56.533-49.641zm23.997 74.479c-6.454 5.484-14.837 7.879-28.108 7.879h-5.514v-65.559h5.513c13.271 0 21.323 2.238 28.108 8.018 7.104 5.956 11.377 15.184 11.377 24.682.001 9.513-4.273 19.024-11.376 24.98z"></path></g>',
				),
				'pay-diners'         => array(
					'label'   => __( 'Diners Club', 'customify' ),
					'source'  => 'react-payment-logos/Diners (MIT)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(0.279 -0.001) scale(0.048)"><path fill="#0079BE" d="M599.93 251.45c0-99.416-82.979-168.13-173.9-168.1h-78.241c-92.003-.033-167.73 68.705-167.73 168.1 0 90.931 75.729 165.64 167.73 165.2h78.241c90.913.437 173.9-74.293 173.9-165.2z"></path><path fill="#fff" d="M348.28 97.432c-84.069.026-152.19 68.308-152.22 152.58.021 84.258 68.145 152.53 152.22 152.56 84.088-.025 152.23-68.301 152.24-152.56-.011-84.274-68.15-152.55-152.24-152.58z"></path><path fill="#0079BE" d="M252.07 249.6c.08-41.18 25.747-76.296 61.94-90.25v180.48c-36.193-13.946-61.861-49.044-61.94-90.229zm131 90.275v-180.52c36.208 13.921 61.915 49.057 61.98 90.256-.066 41.212-25.772 76.322-61.98 90.269z"></path></g>',
				),
				'pay-jcb'            => array(
					'label'   => __( 'JCB', 'customify' ),
					'source'  => 'react-payment-logos/Jcb (MIT), gradients flattened',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(2.282 1.283) scale(0.04287)"><path class="customify-brand-plate" fill="#fff" d="M632.24 361.27c0 41.615-33.729 75.36-75.357 75.36h-409.13V138.75c0-41.626 33.73-75.371 75.364-75.371h409.12l-.001 297.89z"></path><path fill="#3BA235" d="M498.86 256.54c11.686.254 23.438-.516 35.077.4 11.787 2.199 14.628 20.043 4.156 25.887-7.145 3.85-15.633 1.434-23.379 2.113H498.86v-28.4zm41.834-32.145c2.596 9.164-6.238 17.392-15.064 16.13h-26.77c.188-8.642-.367-18.022.272-26.209 10.724.302 21.547-.616 32.209.48 4.581 1.151 8.415 4.917 9.353 9.599zm64.425-135.9c.498 17.501.072 35.927.215 53.783-.033 72.596.07 145.19-.057 217.79-.47 27.207-24.582 50.848-51.601 51.391-27.045.11-54.094.017-81.143.047v-109.75c29.471-.152 58.957.309 88.416-.23 13.666-.858 28.635-9.875 29.271-24.914 1.609-15.104-12.631-25.551-26.151-27.201-5.197-.135-5.045-1.515 0-2.117 12.895-2.787 23.021-16.133 19.227-29.499-3.233-14.058-18.771-19.499-31.695-19.472-26.352-.179-52.709-.025-79.062-.077.17-20.489-.355-41 .283-61.474 2.088-26.716 26.807-48.748 53.446-48.27 26.287-.004 52.57-.004 78.851-.005z"></path><path fill="#095AA3" d="M174.74 139.54c.673-27.164 24.888-50.611 51.872-51.008 26.945-.083 53.894-.012 80.839-.036-.074 90.885.146 181.78-.111 272.66-1.038 26.834-24.989 49.834-51.679 50.309-26.996.098-53.995.014-80.992.041v-113.45c26.223 6.195 53.722 8.832 80.474 4.723 15.991-2.573 33.487-10.426 38.901-27.016 3.984-14.191 1.741-29.126 2.334-43.691v-33.825h-46.297c-.208 22.371.426 44.781-.335 67.125-1.248 13.734-14.849 22.46-27.802 21.994-16.064.17-47.897-11.642-47.897-11.642-.08-41.914.466-94.405.693-136.18z"></path><path fill="#C10E35" d="M324.72 211.89c-2.437.517-.49-8.301-1.113-11.646.166-21.15-.347-42.323.283-63.458 2.082-26.829 26.991-48.916 53.738-48.288h78.768c-.074 90.885.145 181.78-.111 272.66-1.039 26.834-24.992 49.833-51.683 50.309-26.997.102-53.997.016-80.996.042v-124.3c18.439 15.129 43.5 17.484 66.472 17.525 17.318-.006 34.535-2.676 51.353-6.67v-22.772c-18.953 9.446-41.233 15.446-62.243 10.019-14.656-3.648-25.295-17.812-25.058-32.937-1.698-15.729 7.522-32.335 22.979-37.011 19.191-6.008 40.107-1.413 58.096 6.398 3.854 2.018 7.766 4.521 6.225-1.921v-17.899c-30.086-7.158-62.104-9.792-92.33-2.005-8.749 2.468-17.273 6.211-24.38 11.956z"></path></g>',
				),
				'pay-unionpay'       => array(
					'label'   => __( 'UnionPay', 'customify' ),
					'source'  => 'react-payment-logos/Unionpay (MIT), Latin wordmark dropped',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(1.687 0.902) scale(0.04439)"><path class="customify-brand-plate" fill="#D10429" d="M216.4 69.791h142.39c19.87 0 32.287 16.406 27.63 36.47l-66.333 287.48c-4.656 20.063-24.629 36.47-44.498 36.47h-142.39c-19.87 0-32.287-16.406-27.63-36.47l66.331-287.48c4.657-20.168 24.526-36.47 44.395-36.47h.104z"></path><path class="customify-brand-plate" fill="#022E64" d="M346.34 69.791h163.82c19.867 0 10.865 16.406 6.209 36.47l-66.334 287.48c-4.658 20.063-3.209 36.47-23.078 36.47h-163.81c-19.972 0-32.287-16.406-27.527-36.47l66.334-287.48c4.656-20.168 24.524-36.47 44.498-36.47h-.104z"></path><path class="customify-brand-plate" fill="#076F74" d="M504.41 69.791H646.8c19.869 0 32.287 16.406 27.631 36.47l-66.334 287.48c-4.656 20.063-24.629 36.47-44.498 36.47h-142.39c-19.973 0-32.288-16.406-27.631-36.47l66.334-287.48c4.656-20.168 24.525-36.47 44.393-36.47h.105z"></path><path fill="#FEFEFE" d="M480.5 340.81h13.453l3.828-13.063h-13.35L480.5 340.81zm10.762-35.95-4.658 15.467s5.072-2.613 7.865-3.449c2.795-.627 6.934-1.15 6.934-1.15l3.207-10.763h-13.451l.103-.105zm6.726-22.153-4.449 14.839s4.967-2.3 7.76-3.029c2.795-.732 6.934-.941 6.934-.941l3.207-10.764h-13.348l-.104-.105zm29.7 0-17.385 57.997h4.656l-3.621 12.018h-4.658l-1.137 3.657h-16.559l1.139-3.657h-33.529l3.311-11.076h3.416l17.594-58.938 3.518-11.913H501.3l-1.76 5.956s4.449-3.239 8.797-4.39c4.244-1.148 28.666-1.566 28.666-1.566l-3.623 11.809h-5.795l.103.103z"></path><path fill="#FEFEFE" d="M534.59 270.79h18.006l.207 6.792c-.102 1.149.828 1.672 3.002 1.672h3.621l-3.311 11.183h-9.729c-8.381.627-11.59-3.03-11.383-7.106l-.311-12.437-.102-.104zm2.217 53.2h-17.178l2.896-9.927h19.662l2.793-9.092h-19.35l3.311-11.182h53.812l-3.312 11.182h-18.109l-2.793 9.092h18.109l-3.002 9.927h-19.559l-3.518 4.18h7.969l1.965 12.54c.207 1.254.207 2.09.621 2.613.414.418 2.795.627 4.139.627h2.381l-3.725 12.227h-6.107c-.93 0-2.379-.104-4.346-.104-1.863-.21-3.104-1.255-4.346-1.882-1.139-.522-2.793-1.881-3.207-4.284l-1.863-12.54-8.9 12.331c-2.795 3.866-6.621 6.897-13.143 6.897H509.59l3.311-10.869h4.762c1.346 0 2.588-.521 3.52-1.045.93-.418 1.758-.836 2.586-2.193l13.038-18.498zm-187.9-27.2h45.429l-3.312 10.973h-18.11l-2.793 9.299h18.627l-3.415 11.287h-18.524l-4.553 15.152c-.517 1.672 4.45 1.881 6.209 1.881l9.313-1.254-3.726 12.54h-20.904c-1.654 0-2.896-.209-4.76-.627-1.76-.418-2.587-1.254-3.311-2.403-.726-1.254-1.968-2.195-1.14-4.912l6.002-20.063h-10.347l3.415-11.495h10.348l2.794-9.3h-10.347l3.312-10.974-.207-.104zm31.387-19.835h18.626l-3.414 11.39h-25.458l-2.794 2.404c-1.242 1.15-1.552.732-3.105 1.568-1.447.73-4.449 2.193-8.382 2.193h-8.175l3.311-10.972h2.484c2.07 0 3.52-.21 4.243-.627.828-.522 1.76-1.672 2.69-3.554l4.656-8.568h18.526l-3.208 6.27v-.104zm35.106 18.81s5.07-4.701 13.764-6.164c1.967-.418 14.385-.211 14.385-.211l1.863-6.27H419.23l-3.83 12.75v-.105zm24.629 4.807h-25.975l-1.551 5.329h22.559c2.691-.313 3.209.104 3.416-.104l1.654-5.225h-.103zm-33.734-29.678h15.832l-2.275 8.047s4.967-4.075 8.484-5.539c3.52-1.254 11.383-2.508 11.383-2.508l25.664-.104-8.795 29.469c-1.449 5.016-3.209 8.256-4.244 9.823-.93 1.463-2.07 2.821-4.346 4.075-2.172 1.15-4.141 1.881-6.002 1.986-1.656.104-4.346.209-7.865.209h-24.732l-6.934 23.303c-.619 2.299-.93 3.447-.516 4.074.309.523 1.24 1.15 2.379 1.15l10.865-1.045-3.725 12.749h-12.211c-3.932 0-6.727-.104-8.693-.21-1.862-.208-3.83 0-5.174-1.044-1.138-1.045-2.896-2.403-2.794-3.763.104-1.254.621-3.344 1.45-6.27l22.249-74.402z"></path><path fill="#FEFEFE" d="m590.33 270.9-6.936 12.019c-2.172 4.075-6.311 7.21-12.727 7.21l-11.074-.209 3.209-10.868h2.172c1.139 0 1.967-.104 2.588-.418.621-.209.932-.627 1.449-1.254l4.139-6.583h17.281l-.101.103z"></path></g>',
				),
				'pay-paypal'         => array(
					'label'   => __( 'PayPal', 'customify' ),
					'source'  => 'react-payment-logos/Paypal (MIT)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(0.376 0.061) scale(0.04775)"><path fill="#003087" d="M168.38 169.35c-8.399-5.774-19.359-8.668-32.88-8.668H83.154c-4.145 0-6.435 2.073-6.87 6.215L55.02 300.377c-.221 1.311.107 2.51.981 3.6.869 1.092 1.962 1.635 3.271 1.635h24.864c4.361 0 6.758-2.068 7.198-6.215l5.888-35.986c.215-1.744.982-3.162 2.291-4.254 1.308-1.09 2.944-1.803 4.907-2.129 1.963-.324 3.814-.488 5.562-.488 1.743 0 3.814.111 6.217.328 2.397.217 3.925.324 4.58.324 18.756 0 33.478-5.285 44.167-15.867 10.684-10.576 16.032-25.242 16.032-44.004 0-12.868-4.203-22.191-12.598-27.974zm-26.989 40.08c-1.094 7.635-3.926 12.649-8.506 15.049-4.581 2.403-11.124 3.599-19.629 3.599l-10.797.326 5.563-35.007c.434-2.397 1.851-3.597 4.252-3.597h6.218c8.72 0 15.049 1.257 18.975 3.761 3.924 2.51 5.233 7.801 3.924 15.869z"></path><path fill="#009CDE" d="M720.79 160.68h-24.207c-2.406 0-3.822 1.2-4.254 3.601l-21.266 136.1-.328.654c0 1.096.436 2.127 1.311 3.109.867.98 1.963 1.471 3.27 1.471h21.596c4.137 0 6.428-2.068 6.871-6.215l21.264-133.81v-.325c-.001-3.055-1.423-4.581-4.257-4.581z"></path><path fill="#003087" d="M428.31 213.36c0-1.088-.438-2.126-1.305-3.105-.875-.981-1.857-1.475-2.945-1.475h-25.191c-2.404 0-4.367 1.096-5.891 3.271L358.3 263.09l-14.395-49.074c-1.096-3.487-3.492-5.236-7.197-5.236h-24.541c-1.093 0-2.074.492-2.941 1.475-.875.979-1.309 2.019-1.309 3.105 0 .439 2.127 6.871 6.379 19.303 4.252 12.436 8.832 25.85 13.74 40.246 4.908 14.393 7.469 22.031 7.688 22.896-17.886 24.432-26.825 37.518-26.825 39.26 0 2.838 1.415 4.254 4.253 4.254h25.191c2.398 0 4.36-1.088 5.89-3.27l83.427-120.4c.433-.432.65-1.192.65-2.29z"></path><path fill="#009CDE" d="M662.89 208.78h-24.865c-3.057 0-4.904 3.6-5.559 10.799-5.678-8.722-16.031-13.089-31.084-13.089-15.703 0-29.064 5.89-40.076 17.668-11.016 11.778-16.521 25.632-16.521 41.552 0 12.871 3.762 23.121 11.285 30.752 7.525 7.639 17.611 11.451 30.266 11.451 6.324 0 12.758-1.311 19.301-3.926 6.543-2.617 11.664-6.105 15.379-10.469 0 .219-.223 1.197-.654 2.941-.441 1.748-.656 3.061-.656 3.926 0 3.494 1.414 5.234 4.254 5.234h22.576c4.139 0 6.541-2.068 7.193-6.215l13.416-85.39c.215-1.31-.111-2.507-.982-3.599-.877-1.088-1.965-1.635-3.273-1.635zm-42.694 64.454c-5.562 5.453-12.27 8.178-20.121 8.178-6.328 0-11.449-1.742-15.377-5.234-3.928-3.482-5.891-8.281-5.891-14.395 0-8.064 2.727-14.886 8.182-20.447 5.445-5.562 12.213-8.342 20.283-8.342 6.102 0 11.174 1.799 15.213 5.396 4.031 3.6 6.055 8.562 6.055 14.889-.002 7.851-2.783 14.505-8.344 19.955z"></path><path fill="#003087" d="M291.23 208.78h-24.865c-3.058 0-4.908 3.6-5.563 10.799-5.889-8.722-16.25-13.089-31.081-13.089-15.704 0-29.065 5.89-40.078 17.668-11.016 11.778-16.521 25.632-16.521 41.552 0 12.871 3.763 23.121 11.288 30.752 7.525 7.639 17.61 11.451 30.262 11.451 6.104 0 12.433-1.311 18.975-3.926 6.543-2.617 11.778-6.105 15.704-10.469-.875 2.615-1.309 4.906-1.309 6.867 0 3.494 1.417 5.234 4.253 5.234h22.574c4.141 0 6.543-2.068 7.198-6.215l13.413-85.39c.215-1.31-.111-2.507-.981-3.599-.873-1.088-1.962-1.635-3.269-1.635zm-42.695 64.616c-5.563 5.35-12.382 8.016-20.447 8.016-6.329 0-11.4-1.742-15.214-5.234-3.819-3.482-5.726-8.281-5.726-14.395 0-8.064 2.725-14.886 8.18-20.447 5.449-5.562 12.211-8.343 20.284-8.343 6.104 0 11.175 1.8 15.214 5.397 4.032 3.6 6.052 8.562 6.052 14.889-.001 8.07-2.781 14.779-8.343 20.117z"></path><path fill="#009CDE" d="M540.04 169.35c-8.398-5.774-19.355-8.668-32.879-8.668h-52.02c-4.363 0-6.764 2.073-7.197 6.215l-21.266 133.48c-.221 1.311.107 2.51.982 3.6.865 1.092 1.961 1.635 3.27 1.635h26.826c2.617 0 4.361-1.416 5.236-4.252l5.889-37.949c.217-1.744.98-3.162 2.291-4.254 1.309-1.09 2.943-1.803 4.908-2.129 1.961-.324 3.812-.488 5.561-.488 1.744 0 3.814.111 6.215.328 2.398.217 3.93.324 4.58.324 18.76 0 33.479-5.285 44.168-15.867 10.688-10.576 16.031-25.242 16.031-44.004.001-12.868-4.2-22.192-12.595-27.974zm-33.533 53.819c-4.799 3.271-11.998 4.906-21.592 4.906l-10.471.328 5.562-35.008c.432-2.396 1.85-3.598 4.252-3.598h5.887c4.799 0 8.615.219 11.455.654 2.83.438 5.561 1.799 8.178 4.088 2.619 2.291 3.926 5.619 3.926 9.979 0 9.164-2.402 15.377-7.197 18.651z"></path></g>',
				),
				'pay-apple-pay'      => array(
					'label'   => __( 'Apple Pay', 'customify' ),
					'source'  => 'simple-icons/applepay (CC0-1.0)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(6.504 -0.495) scale(1.04132)"><path fill="#000000" d="M2.15 4.318a42.16 42.16 0 0 0-.454.003c-.15.005-.303.013-.452.04a1.44 1.44 0 0 0-1.06.772c-.07.138-.114.278-.14.43-.028.148-.037.3-.04.45A10.2 10.2 0 0 0 0 6.222v11.557c0 .07.002.138.003.207.004.15.013.303.04.452.027.15.072.291.142.429a1.436 1.436 0 0 0 .63.63c.138.07.278.115.43.142.148.027.3.036.45.04l.208.003h20.194l.207-.003c.15-.004.303-.013.452-.04.15-.027.291-.071.428-.141a1.432 1.432 0 0 0 .631-.631c.07-.138.115-.278.141-.43.027-.148.036-.3.04-.45.002-.07.003-.138.003-.208l.001-.246V6.221c0-.07-.002-.138-.004-.207a2.995 2.995 0 0 0-.04-.452 1.446 1.446 0 0 0-1.2-1.201 3.022 3.022 0 0 0-.452-.04 10.448 10.448 0 0 0-.453-.003zm0 .512h19.942c.066 0 .131.002.197.003.115.004.25.01.375.032.109.02.2.05.287.094a.927.927 0 0 1 .407.407.997.997 0 0 1 .094.288c.022.123.028.258.031.374.002.065.003.13.003.197v11.552c0 .065 0 .13-.003.196-.003.115-.009.25-.032.375a.927.927 0 0 1-.5.693 1.002 1.002 0 0 1-.286.094 2.598 2.598 0 0 1-.373.032l-.2.003H1.906c-.066 0-.133-.002-.196-.003a2.61 2.61 0 0 1-.375-.032c-.109-.02-.2-.05-.288-.094a.918.918 0 0 1-.406-.407 1.006 1.006 0 0 1-.094-.288 2.531 2.531 0 0 1-.032-.373 9.588 9.588 0 0 1-.002-.197V6.224c0-.065 0-.131.002-.197.004-.114.01-.248.032-.375.02-.108.05-.199.094-.287a.925.925 0 0 1 .407-.406 1.03 1.03 0 0 1 .287-.094c.125-.022.26-.029.375-.032.065-.002.131-.002.196-.003zm4.71 3.7c-.3.016-.668.199-.88.456-.191.22-.36.58-.316.918.338.03.675-.169.888-.418.205-.258.345-.603.308-.955zm2.207.42v5.493h.852v-1.877h1.18c1.078 0 1.835-.739 1.835-1.812 0-1.07-.742-1.805-1.808-1.805zm.852.719h.982c.739 0 1.161.396 1.161 1.089 0 .692-.422 1.092-1.164 1.092h-.979zm-3.154.3c-.45.01-.83.28-1.05.28-.235 0-.593-.264-.981-.257a1.446 1.446 0 0 0-1.23.747c-.527.908-.139 2.255.374 2.995.249.366.549.769.944.754.373-.014.52-.242.973-.242.454 0 .586.242.98.235.41-.007.667-.366.915-.733.286-.417.403-.82.41-.841-.007-.008-.79-.308-.797-1.209-.008-.754.615-1.113.644-1.135-.352-.52-.9-.578-1.09-.593a1.123 1.123 0 0 0-.092-.002zm8.204.397c-.99 0-1.606.533-1.652 1.256h.777c.072-.358.369-.586.845-.586.502 0 .803.266.803.711v.309l-1.097.064c-.951.054-1.488.484-1.488 1.184 0 .72.548 1.207 1.332 1.207.526 0 1.032-.281 1.264-.727h.019v.659h.788v-2.76c0-.803-.62-1.317-1.591-1.317zm1.94.072l1.446 4.009c0 .003-.073.24-.073.247-.125.41-.33.571-.711.571-.069 0-.206 0-.267-.015v.666c.06.011.267.019.335.019.83 0 1.226-.312 1.568-1.283l1.5-4.214h-.868l-1.012 3.259h-.015l-1.013-3.26zm-1.167 2.189v.316c0 .521-.45.917-1.024.917-.442 0-.731-.228-.731-.579 0-.342.278-.56.769-.593z"/></g>',
				),
				'pay-google-pay'     => array(
					'label'   => __( 'Google Pay', 'customify' ),
					'source'  => 'simple-icons/googlepay (CC0-1.0)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(3 -4.001) scale(1.33333)"><path fill="#5F6368" d="M3.963 7.235A3.963 3.963 0 00.422 9.419a3.963 3.963 0 000 3.559 3.963 3.963 0 003.541 2.184c1.07 0 1.97-.352 2.627-.957.748-.69 1.18-1.71 1.18-2.916a4.722 4.722 0 00-.07-.806H3.964v1.526h2.14a1.835 1.835 0 01-.79 1.205c-.356.241-.814.379-1.35.379-1.034 0-1.911-.697-2.225-1.636a2.375 2.375 0 010-1.517c.314-.94 1.191-1.636 2.225-1.636a2.152 2.152 0 011.52.594l1.132-1.13a3.808 3.808 0 00-2.652-1.033zm6.501.55v6.9h.886V11.89h1.465c.603 0 1.11-.196 1.522-.588a1.911 1.911 0 00.635-1.464 1.92 1.92 0 00-.635-1.456 2.125 2.125 0 00-1.522-.598zm2.427.85a1.156 1.156 0 01.823.365 1.176 1.176 0 010 1.686 1.171 1.171 0 01-.877.357H11.35V8.635h1.487a1.156 1.156 0 01.054 0zm4.124 1.175c-.842 0-1.477.308-1.907.925l.781.491c.288-.417.68-.626 1.175-.626a1.255 1.255 0 01.856.323 1.009 1.009 0 01.366.785v.202c-.34-.193-.774-.289-1.3-.289-.617 0-1.11.145-1.479.434-.37.288-.554.677-.554 1.165a1.476 1.476 0 00.525 1.156c.35.308.785.463 1.305.463.61 0 1.098-.27 1.465-.81h.038v.655h.848v-2.909c0-.61-.19-1.09-.568-1.44-.38-.35-.896-.525-1.551-.525zm2.263.154l1.946 4.422-1.098 2.38h.915L24 9.963h-.965l-1.368 3.391h-.02l-1.406-3.39zm-2.146 2.368c.494 0 .88.11 1.156.33 0 .372-.147.696-.44.973a1.413 1.413 0 01-.997.414 1.081 1.081 0 01-.69-.232.708.708 0 01-.293-.578c0-.257.12-.47.363-.647.24-.173.54-.26.9-.26Z"/></g>',
				),
				'pay-stripe'         => array(
					'label'   => __( 'Stripe', 'customify' ),
					'source'  => 'simple-icons/stripe (CC0-1.0)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#635BFF" stroke="#E3E5E8"/><g transform="translate(11 4) scale(0.66667)"><path fill="#FFFFFF" d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/></g>',
				),
				'pay-klarna'         => array(
					'label'   => __( 'Klarna', 'customify' ),
					'source'  => 'simple-icons/klarna (CC0-1.0)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFB3C7" stroke="#E3E5E8"/><g transform="translate(9.4 2.4) scale(0.8)"><path fill="#0B051D" d="M4.592 2v20H0V2h4.592zm11.46 0c0 4.194-1.583 8.105-4.415 11.068l-.278.283L17.702 22h-5.668l-6.893-9.4 1.779-1.332c2.858-2.14 4.535-5.378 4.637-8.924L11.562 2h4.49zM21.5 17a2.5 2.5 0 110 5 2.5 2.5 0 010-5z"/></g>',
				),
				'pay-amazon-pay'     => array(
					'label'   => __( 'Amazon Pay', 'customify' ),
					'source'  => 'simple-icons 13.21.0/amazonpay (CC0-1.0)',
					'style'   => 'brand',
					'viewbox' => '0 0 38 24',
					'body'    => '<rect class="customify-brand-bg" x=".5" y=".5" width="37" height="23" rx="3" fill="#FFFFFF" stroke="#E3E5E8"/><g transform="translate(6.189 -0.811) scale(1.06758)"><path fill="#FF9900" d="M14.3781 4.9945c-.3732-.3227-.953-.4843-1.7401-.4843-.3895 0-.779.0355-1.1684.1054-.3901.0706-.7172.1636-.9824.2797-.0993.0418-.166.0849-.1991.1304-.0331.0456-.05.1267-.05.2422v.3352c0 .1491.0537.224.1617.224a.337.337 0 0 0 .1061-.0187c.0374-.0125.0687-.0225.093-.0312.6385-.1904 1.247-.2859 1.8275-.2859.4968 0 .8451.0912 1.0442.274.1991.1823.2984.4969.2984.9444v.8201c-.5799-.141-1.1023-.211-1.5667-.211-.729 0-1.3088.1804-1.74.5406-.4308.3601-.6467.8432-.6467 1.448 0 .5642.1741 1.013.5224 1.3488.3477.3358.8201.503 1.4168.503.3564 0 .7147-.0705 1.0754-.2109.3608-.1404.6897-.3402.988-.5967l.0625.41c.025.1574.116.236.274.236h.5343c.1654 0 .249-.083.249-.2484V6.4987c-.0006-.6797-.1872-1.1809-.5599-1.5042zm-.6091 4.6c-.2734.2072-.5593.3645-.8576.4725-.2984.108-.5842.1617-.8576.1617-.3233 0-.5717-.085-.7459-.2547-.1741-.1698-.2609-.412-.2609-.7271 0-.721.4682-1.0817 1.4044-1.0817.2153 0 .4369.015.6647.0437.2278.0293.4456.0687.6529.118zM8.7726 6.402c-.1204-.402-.292-.744-.5161-1.0255-.2235-.2815-.4969-.4975-.8202-.6466-.3227-.1492-.6834-.2235-1.0816-.2235-.3727 0-.7378.07-1.0936.211-.3563.141-.6921.3483-1.0073.6216l-.0618-.3982c-.025-.1654-.1205-.2484-.2865-.2484h-.5468c-.1654 0-.2484.083-.2484.2484v8.3662c0 .166.083.2484.2484.2484h.7334c.166 0 .2484-.083.2484-.2484v-2.9086c.5387.4887 1.181.7334 1.9268.7334.4057 0 .7746-.0811 1.106-.2422.3314-.1616.6129-.3876.845-.6778.2323-.2896.4126-.6416.5406-1.0567.1286-.4144.1929-.8788.1929-1.3925.0012-.505-.0593-.9587-.1792-1.3606zM5.982 10.1369c-.5642 0-1.111-.1985-1.6409-.5967V6.0724c.5218-.3813 1.0773-.5717 1.666-.5717 1.1271 0 1.6907.7752 1.6907 2.3243-.0006 1.5417-.5723 2.3119-1.7158 2.3119zm13.0005 1.963l2.735-6.9612c.0575-.141.0868-.2403.0868-.2984 0-.0992-.058-.1491-.1741-.1491h-.696c-.1329 0-.2234.0212-.274.0624-.0499.0418-.0992.133-.1491.274l-1.6784 4.8228-1.7401-4.8228c-.05-.141-.0993-.2322-.1492-.274-.05-.0412-.141-.0624-.274-.0624h-.7459c-.116 0-.1741.0499-.1741.1491 0 .058.0287.1573.0868.2984l2.3992 5.917-.236.6341c-.141.3982-.2983.6716-.4724.8208-.1741.1491-.4188.2234-.7334.2234-.141 0-.2528-.0087-.3352-.025-.083-.0162-.1454-.025-.1866-.025-.1242 0-.1866.0787-.1866.236v.3233c0 .1161.0206.201.0624.2547.0412.0536.1074.0936.1991.118.2066.0574.4432.0873.7084.0873.4725 0 .8557-.1242 1.1497-.3732.2952-.2478.5543-.6585.7777-1.2302m2.7113 4.4233c-2.6276 1.9393-6.4369 2.9704-9.7174 2.9704-4.5975 0-8.7375-1.6996-11.8701-4.5283-.246-.2221-.0269-.5255.269-.3532 3.3798 1.9667 7.5597 3.1513 11.877 3.1513 2.9123 0 6.1136-.6042 9.0596-1.8537.4437-.1891.8163.2921.382.6135m1.0928-1.2483c.3364.4307-.3738 2.204-.691 2.996-.096.2396.11.3364.3271.1548 1.4094-1.179 1.7739-3.65 1.4855-4.0071-.2865-.3539-2.7506-.6585-4.2548.3976-.2316.1623-.1916.387.0649.3557.847-.101 2.7325-.3276 3.0683.103Z"/></g>',
				),
			);

			/**
			 * Filter the preset inline-SVG icon library.
			 *
			 * Entries must follow the icon style contract documented at the top
			 * of `inc/icons-svg.php`: a square-viewBox glyph supplied as the
			 * INNER markup of the `<svg>` element, no `width` / `height`, no
			 * hardcoded colours. `customify_get_svg_icon()` supplies the root
			 * element, so anything here inherits the contract automatically;
			 * `style` and `viewbox` may be omitted and default to an outline
			 * glyph on a 24×24 grid.
			 *
			 * Customify Pro and child themes use this to extend the picker —
			 * the Customizer control reads the same list, so a filtered-in icon
			 * shows up in the picker with no extra registration.
			 *
			 * @since 0.4.25
			 *
			 * @param array<string, array{label: string, style: string, viewbox: string, body: string}> $library Icon key => entry.
			 */
			$library = apply_filters( 'customify/svg_icons', $library );

			$icons = is_array( $library ) ? $library : array();
		}

		return $icons;
	}
}

if ( ! function_exists( 'customify_get_svg_icon' ) ) {
	/**
	 * Build the `<svg>` markup for one preset icon.
	 *
	 * Returns an empty string for an unknown or empty key — an icon that was
	 * removed from the library (or supplied by a plugin that has since been
	 * deactivated) renders as nothing rather than as a PHP notice.
	 *
	 * The output is shipped markup, NOT user input, so it is safe to echo
	 * directly. It still passes the `customify_sanitize_svg()` allowlist
	 * (checked by `bin/test-svg-sanitize.php`) — anything filtered in through
	 * `customify/svg_icons` that does not would be a bug in the filter.
	 *
	 * @since 0.4.25
	 *
	 * @param string $key  Library key, e.g. `cart`.
	 * @param array  $args Optional. `class` — extra classes on the `<svg>`.
	 *
	 * @return string SVG markup, or '' when the key is unknown.
	 */
	function customify_get_svg_icon( $key, $args = array() ) {
		$key = sanitize_key( (string) $key );
		if ( '' === $key ) {
			return '';
		}

		$icons = customify_get_svg_icons();
		if ( ! isset( $icons[ $key ]['body'] ) ) {
			return '';
		}

		$icon = wp_parse_args(
			$icons[ $key ],
			array(
				// Defaults for filtered-in entries that only supply a body:
				// a Lucide-style outline glyph on the 24x24 grid.
				'style'   => 'outline',
				'viewbox' => '0 0 24 24',
			)
		);

		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
			)
		);

		$brand  = ( 'brand' === $icon['style'] );
		$filled = ( 'filled' === $icon['style'] );

		$classes = array( 'customify-svg-icon', 'customify-svg-icon--' . $key );
		if ( $brand ) {
			// Brand logos carry their own per-element colours. The class is
			// what the frontend + picker exemptions key off so the outline
			// default (and the filled override) leave their paint alone.
			$classes[] = 'customify-svg-icon--brand';
		} elseif ( $filled ) {
			// The frontend default paints `fill: none; stroke: currentColor`
			// on every preset icon; a solid glyph needs this class for the
			// override rule to win. See src/frontend/scss/base/_icons.scss.
			$classes[] = 'customify-svg-icon--filled';
		}
		if ( '' !== $args['class'] ) {
			$classes[] = $args['class'];
		}

		// Paint mode lives on the root as presentation attributes so the icon
		// is still correct anywhere the theme stylesheet is absent — the block
		// editor canvas, an email preview, a copied snippet.
		//
		// A `brand` entry gets NO root paint at all: every element in its body
		// declares its own `fill`, and a root `fill`/`stroke` would either be
		// inherited by anything that somehow lacks one or fight the mono-mode
		// CSS. An empty string here keeps the root attribute list clean.
		if ( $brand ) {
			$paint = '';
		} elseif ( $filled ) {
			$paint = 'fill="currentColor" stroke="none"';
		} else {
			$paint = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
		}

		return '<svg class="' . esc_attr( implode( ' ', $classes ) ) . '"'
			. ' viewBox="' . esc_attr( $icon['viewbox'] ) . '"'
			. ( '' !== $paint ? ' ' . $paint : '' )
			. ' aria-hidden="true" focusable="false">'
			. $icon['body']
			. '</svg>';
	}
}

if ( ! function_exists( 'customify_get_svg_icons_for_js' ) ) {
	/**
	 * The preset library flattened for the Customizer control script.
	 *
	 * Shape: `array( '<key>' => array( 'label' => string, 'svg' => string ) )`.
	 * Rendered markup rather than raw bodies so the JS never has to know the
	 * root-element contract — the picker grid, the Suggested row and the
	 * control's preview chip all inject exactly what the front end prints,
	 * solid glyphs and 256-grid glyphs included.
	 *
	 * @since 0.4.25
	 *
	 * @return array<string, array{label: string, svg: string}>
	 */
	function customify_get_svg_icons_for_js() {
		$out = array();

		foreach ( customify_get_svg_icons() as $key => $icon ) {
			$markup = customify_get_svg_icon( $key );
			if ( '' === $markup ) {
				continue;
			}
			$out[ $key ] = array(
				'label' => isset( $icon['label'] ) ? (string) $icon['label'] : $key,
				'svg'   => $markup,
			);
		}

		return $out;
	}
}
