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
 *   • `aria-hidden="true"` + `focusable="false"` — icons here are decorative;
 *     the accessible name comes from the surrounding link/label
 *
 * `style` is the PAINT MODE, not the visual weight: Phosphor's hairline glyphs
 * are `filled` because their geometry is a filled outline, and they still look
 * like the thinnest icons in the set.
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

		$filled = ( 'filled' === $icon['style'] );

		$classes = array( 'customify-svg-icon', 'customify-svg-icon--' . $key );
		if ( $filled ) {
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
		$paint = $filled
			? 'fill="currentColor" stroke="none"'
			: 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

		return '<svg class="' . esc_attr( implode( ' ', $classes ) ) . '"'
			. ' viewBox="' . esc_attr( $icon['viewbox'] ) . '" ' . $paint
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
