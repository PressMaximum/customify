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
 * sets. Do not hand-edit the geometry: re-run the extraction against a newer
 * upstream release instead, so the set stays internally consistent.
 *
 *   • Lucide — ISC License, Copyright (c) for Lucide Icons and Contributors.
 *     https://lucide.dev — https://github.com/lucide-icons/lucide
 *     Package: `lucide-static`. PRIMARY SET — used for all but six icons.
 *
 *   • Tabler Icons — MIT License, Copyright (c) Paweł Kuna.
 *     https://tabler.io/icons — https://github.com/tabler/tabler-icons
 *     Package: `@tabler/icons` (`icons/outline`). Used ONLY where a second
 *     silhouette of the same subject is genuinely useful (the `-outline`
 *     softer variants, and the bag-with-badge pair). Tabler shares Lucide's
 *     grid exactly — 24×24, stroke 2, round caps — so the two mix cleanly.
 *
 * Both sets ship their icons as a pretty-printed `<svg>` wrapper around the
 * geometry. Only the wrapper is discarded here (plus Tabler's transparent
 * `M0 0h24v24H0z` bounding-box path, which is sprite-build padding); every
 * `<path>`, `<circle>` and `<rect>` below is upstream's, byte for byte.
 *
 * Heroicons (MIT, Tailwind Labs) and Phosphor (MIT) were evaluated and NOT
 * used: Heroicons outline is drawn for stroke-width 1.5 and reads visibly
 * lighter next to Lucide at 2, and Phosphor is a 256×256 fill-based set whose
 * solid silhouettes cannot honour the stroke contract below. Mixing either in
 * would have broken the "one set" look the picker depends on.
 * ---------------------------------------------------------------------------
 *
 * ICON STYLE CONTRACT — every entry must honour it, including icons added
 * through the `customify/svg_icons` filter, or the set stops looking like one
 * set. `customify_get_svg_icon()` supplies the root element, so an entry only
 * has to provide contract-compliant geometry:
 *
 *   • 24×24 user units (`viewBox="0 0 24 24"`), outline/stroke based
 *   • NO `width` / `height` attributes — CSS sizes the icon (see
 *     `src/frontend/scss/base/_icons.scss`)
 *   • `fill="none"`, `stroke="currentColor"`, `stroke-width="2"`, round caps
 *     and joins — Lucide's and Tabler's native drawing weight
 *   • `aria-hidden="true"` + `focusable="false"` — icons here are decorative;
 *     the accessible name comes from the surrounding link/label
 *
 * NAMING — every glyph is stroke/outline, so an `-outline` suffix does NOT
 * mean "the outline version of a filled icon". It marks an ALTERNATE, softer
 * silhouette of the same subject (`bag` is Lucide's squared-shoulder bag,
 * `bag-outline` Tabler's rounded one), which is what the per-field "Suggested"
 * rows offer as a quick visual choice.
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
	 * Shape: `array( '<key>' => array( 'label' => string, 'source' => string,
	 * 'body' => string ) )` where `body` is the INNER markup of the `<svg>`
	 * element (no `<svg>` wrapper — `customify_get_svg_icon()` adds it so every
	 * icon gets an identical, contract-compliant root element) and `source` is
	 * the upstream set + icon name the geometry came from.
	 *
	 * `source` is informational only — nothing reads it at runtime. It exists
	 * so a future maintainer can re-extract an icon from upstream without
	 * guessing which set it came from.
	 *
	 * Keys are stored in `theme_mod`s. Treat them as public API: never rename
	 * or remove a key, only add. A removed key renders nothing (no notice),
	 * but the site silently loses its icon.
	 *
	 * @since 0.4.25
	 *
	 * @return array<string, array{label: string, source: string, body: string}>
	 */
	function customify_get_svg_icons() {
		static $icons = null;

		if ( null === $icons ) {
			$library = array(
				// ------------------------------------------------- Commerce
				// Bags first: modern shops overwhelmingly pick a bag over a
				// trolley, and the header cart item's Suggested row is ordered
				// to match.
				'bag'           => array(
					'label'  => __( 'Shopping Bag', 'customify' ),
					'source' => 'lucide/shopping-bag',
					'body'   => '<path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/>',
				),
				'bag-outline'   => array(
					'label'  => __( 'Shopping Bag (Soft)', 'customify' ),
					'source' => 'tabler/shopping-bag',
					'body'   => '<path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/>',
				),
				'bag-handle'    => array(
					'label'  => __( 'Handbag', 'customify' ),
					'source' => 'lucide/handbag',
					'body'   => '<path d="M2.048 18.566A2 2 0 0 0 4 21h16a2 2 0 0 0 1.952-2.434l-2-9A2 2 0 0 0 18 8H6a2 2 0 0 0-1.952 1.566z"/><path d="M8 11V6a4 4 0 0 1 8 0v5"/>',
				),
				'bag-paper'     => array(
					'label'  => __( 'Paper Bag', 'customify' ),
					'source' => 'lucide/paper-bag',
					'body'   => '<path d="M5.364 3.848C4 6 3 9.652 3 12.652V19a2 2 0 002 2h14a2 2 0 002-2v-5c0-2.334-1.816-4.668-2.622-7.002"/><path d="M7 3h11.379a2 2 0 011.789 1.106l.723 1.447A1 1 0 0119.997 7h-8.525a2 2 0 01-1.789-1.106L8.79 4.105a2 2 0 10-3.579 1.789l2.261 4.522A5 5 0 018 12.652V21"/>',
				),
				'bag-check'     => array(
					'label'  => __( 'Bag with Check', 'customify' ),
					'source' => 'tabler/shopping-bag-check',
					'body'   => '<path d="M11.5 21h-2.926a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304h11.339a2 2 0 0 1 1.977 2.304l-.5 3.248"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/><path d="M15 19l2 2l4 -4"/>',
				),
				'bag-plus'      => array(
					'label'  => __( 'Bag with Plus', 'customify' ),
					'source' => 'tabler/shopping-bag-plus',
					'body'   => '<path d="M12.5 21h-3.926a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304h11.339a2 2 0 0 1 1.977 2.304l-.263 1.708"/><path d="M16 19h6"/><path d="M19 16v6"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/>',
				),
				'cart'          => array(
					'label'  => __( 'Shopping Cart', 'customify' ),
					'source' => 'lucide/shopping-cart',
					'body'   => '<path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/>',
				),
				'cart-outline'  => array(
					'label'  => __( 'Cart (Simple)', 'customify' ),
					'source' => 'tabler/shopping-cart',
					'body'   => '<path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>',
				),
				'cart-plus'     => array(
					'label'  => __( 'Cart with Plus', 'customify' ),
					'source' => 'lucide/shopping-cart-plus',
					'body'   => '<path d="M16 5h6"/><path d="M19 2v6"/><path d="m2.05 2.05 1.099-.028a1 1 0 011.008.815l2.69 14.347A1 1 0 007.83 18H18"/><path d="M4.564 5H12"/><path d="M6.25 14h12.712a2 2 0 001.991-1.57l.172-1.041"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/>',
				),
				'basket'        => array(
					'label'  => __( 'Basket', 'customify' ),
					'source' => 'lucide/shopping-basket',
					'body'   => '<path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/>',
				),
				'basket-alt'    => array(
					'label'  => __( 'Basket (Soft)', 'customify' ),
					'source' => 'tabler/basket',
					'body'   => '<path d="M10 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M5.001 8h13.999a2 2 0 0 1 1.977 2.304l-1.255 7.152a3 3 0 0 1 -2.966 2.544h-9.512a3 3 0 0 1 -2.965 -2.544l-1.255 -7.152a2 2 0 0 1 1.977 -2.304"/><path d="M17 10l-2 -6"/><path d="M7 10l2 -6"/>',
				),
				'package'       => array(
					'label'  => __( 'Package', 'customify' ),
					'source' => 'lucide/package',
					'body'   => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/>',
				),
				'store'         => array(
					'label'  => __( 'Store', 'customify' ),
					'source' => 'lucide/store',
					'body'   => '<path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/>',
				),
				'truck'         => array(
					'label'  => __( 'Delivery', 'customify' ),
					'source' => 'lucide/truck',
					'body'   => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
				),
				'gift'          => array(
					'label'  => __( 'Gift', 'customify' ),
					'source' => 'lucide/gift',
					'body'   => '<path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><rect x="3" y="7" width="18" height="4" rx="1"/>',
				),
				'tag'           => array(
					'label'  => __( 'Tag', 'customify' ),
					'source' => 'lucide/tag',
					'body'   => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
				),
				'percent'       => array(
					'label'  => __( 'Discount', 'customify' ),
					'source' => 'lucide/percent',
					'body'   => '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
				),
				'credit-card'   => array(
					'label'  => __( 'Credit Card', 'customify' ),
					'source' => 'lucide/credit-card',
					'body'   => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 14h2"/>',
				),
				// -------------------------------------------------- Account
				'user'          => array(
					'label'  => __( 'User', 'customify' ),
					'source' => 'lucide/user',
					'body'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
				),
				'user-outline'  => array(
					'label'  => __( 'User (Soft)', 'customify' ),
					'source' => 'tabler/user',
					'body'   => '<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>',
				),
				'user-circle'   => array(
					'label'  => __( 'User Circle', 'customify' ),
					'source' => 'lucide/circle-user-round',
					'body'   => '<path d="M17.925 20.056a6 6 0 0 0-11.851.001"/><circle cx="12" cy="11" r="4"/><circle cx="12" cy="12" r="10"/>',
				),
				'user-square'   => array(
					'label'  => __( 'User Square', 'customify' ),
					'source' => 'lucide/square-user-round',
					'body'   => '<path d="M18 21a6 6 0 0 0-12 0"/><circle cx="12" cy="11" r="4"/><rect width="18" height="18" x="3" y="3" rx="2"/>',
				),
				'users'         => array(
					'label'  => __( 'Users', 'customify' ),
					'source' => 'lucide/users',
					'body'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>',
				),
				'contact'       => array(
					'label'  => __( 'Contact', 'customify' ),
					'source' => 'lucide/contact-round',
					'body'   => '<path d="M16 2v2"/><path d="M17.915 21a6 6 0 10-12 0"/><path d="M8 2v2"/><circle cx="12" cy="11" r="4"/><rect x="3" y="3" width="18" height="18" rx="2"/>',
				),
				'id-card'       => array(
					'label'  => __( 'ID Card', 'customify' ),
					'source' => 'lucide/id-card',
					'body'   => '<path d="M13 19a4 4 0 00-8 0"/><path d="M16 10h2"/><path d="M16 14h2"/><circle cx="9" cy="12" r="3"/><rect x="2" y="5" width="20" height="14" rx="2"/>',
				),
				'lock'          => array(
					'label'  => __( 'Lock', 'customify' ),
					'source' => 'lucide/lock',
					'body'   => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
				),
				// ------------------------------------------- Wishlist / saved
				'heart'         => array(
					'label'  => __( 'Heart', 'customify' ),
					'source' => 'lucide/heart',
					'body'   => '<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>',
				),
				'heart-outline' => array(
					'label'  => __( 'Heart (Soft)', 'customify' ),
					'source' => 'tabler/heart',
					'body'   => '<path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"/>',
				),
				'heart-plus'    => array(
					'label'  => __( 'Heart with Plus', 'customify' ),
					'source' => 'lucide/heart-plus',
					'body'   => '<path d="m14.479 19.374-.971.939a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.219 1.49"/><path d="M15 15h6"/><path d="M18 12v6"/>',
				),
				'bookmark'      => array(
					'label'  => __( 'Bookmark', 'customify' ),
					'source' => 'lucide/bookmark',
					'body'   => '<path d="M17 3a2 2 0 0 1 2 2v15a1 1 0 0 1-1.496.868l-4.512-2.578a2 2 0 0 0-1.984 0l-4.512 2.578A1 1 0 0 1 5 20V5a2 2 0 0 1 2-2z"/>',
				),
				'star'          => array(
					'label'  => __( 'Star', 'customify' ),
					'source' => 'lucide/star',
					'body'   => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
				),
				// ----------------------------------------------- General UI
				'search'        => array(
					'label'  => __( 'Search', 'customify' ),
					'source' => 'lucide/search',
					'body'   => '<path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/>',
				),
				'menu'          => array(
					'label'  => __( 'Menu', 'customify' ),
					'source' => 'lucide/menu',
					'body'   => '<path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/>',
				),
				'close'         => array(
					'label'  => __( 'Close', 'customify' ),
					'source' => 'lucide/x',
					'body'   => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
				),
				'chevron-down'  => array(
					'label'  => __( 'Chevron Down', 'customify' ),
					'source' => 'lucide/chevron-down',
					'body'   => '<path d="m6 9 6 6 6-6"/>',
				),
				'chevron-up'    => array(
					'label'  => __( 'Chevron Up', 'customify' ),
					'source' => 'lucide/chevron-up',
					'body'   => '<path d="m18 15-6-6-6 6"/>',
				),
				'chevron-left'  => array(
					'label'  => __( 'Chevron Left', 'customify' ),
					'source' => 'lucide/chevron-left',
					'body'   => '<path d="m15 18-6-6 6-6"/>',
				),
				'chevron-right' => array(
					'label'  => __( 'Chevron Right', 'customify' ),
					'source' => 'lucide/chevron-right',
					'body'   => '<path d="m9 18 6-6-6-6"/>',
				),
				'arrow-left'    => array(
					'label'  => __( 'Arrow Left', 'customify' ),
					'source' => 'lucide/arrow-left',
					'body'   => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
				),
				'arrow-right'   => array(
					'label'  => __( 'Arrow Right', 'customify' ),
					'source' => 'lucide/arrow-right',
					'body'   => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
				),
				'home'          => array(
					'label'  => __( 'Home', 'customify' ),
					'source' => 'lucide/house',
					'body'   => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
				),
				'phone'         => array(
					'label'  => __( 'Phone', 'customify' ),
					'source' => 'lucide/phone',
					'body'   => '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
				),
				'mail'          => array(
					'label'  => __( 'Mail', 'customify' ),
					'source' => 'lucide/mail',
					'body'   => '<path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/>',
				),
				'map-pin'       => array(
					'label'  => __( 'Map Pin', 'customify' ),
					'source' => 'lucide/map-pin',
					'body'   => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
				),
				'globe'         => array(
					'label'  => __( 'Globe', 'customify' ),
					'source' => 'lucide/globe',
					'body'   => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
				),
				'clock'         => array(
					'label'  => __( 'Clock', 'customify' ),
					'source' => 'lucide/clock',
					'body'   => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
				),
				'calendar'      => array(
					'label'  => __( 'Calendar', 'customify' ),
					'source' => 'lucide/calendar',
					'body'   => '<path d="M8 2v3"/><path d="M16 2v3"/><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/>',
				),
				'external-link' => array(
					'label'  => __( 'External Link', 'customify' ),
					'source' => 'lucide/external-link',
					'body'   => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
				),
				'share'         => array(
					'label'  => __( 'Share', 'customify' ),
					'source' => 'lucide/share-2',
					'body'   => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>',
				),
				'download'      => array(
					'label'  => __( 'Download', 'customify' ),
					'source' => 'lucide/download',
					'body'   => '<path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/>',
				),
				'eye'           => array(
					'label'  => __( 'Eye', 'customify' ),
					'source' => 'lucide/eye',
					'body'   => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
				),
				'check'         => array(
					'label'  => __( 'Check', 'customify' ),
					'source' => 'lucide/check',
					'body'   => '<path d="M20 6 9 17l-5-5"/>',
				),
				'plus'          => array(
					'label'  => __( 'Plus', 'customify' ),
					'source' => 'lucide/plus',
					'body'   => '<path d="M5 12h14"/><path d="M12 5v14"/>',
				),
				'minus'         => array(
					'label'  => __( 'Minus', 'customify' ),
					'source' => 'lucide/minus',
					'body'   => '<path d="M5 12h14"/>',
				),
				'filter'        => array(
					'label'  => __( 'Filter', 'customify' ),
					'source' => 'lucide/funnel',
					'body'   => '<path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"/>',
				),
				'grid'          => array(
					'label'  => __( 'Grid', 'customify' ),
					'source' => 'lucide/layout-grid',
					'body'   => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
				),
				'list'          => array(
					'label'  => __( 'List', 'customify' ),
					'source' => 'lucide/list',
					'body'   => '<path d="M3 5h.01"/><path d="M3 12h.01"/><path d="M3 19h.01"/><path d="M8 5h13"/><path d="M8 12h13"/><path d="M8 19h13"/>',
				),
				'settings'      => array(
					'label'  => __( 'Settings', 'customify' ),
					'source' => 'lucide/settings',
					'body'   => '<path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/>',
				),
			);

			/**
			 * Filter the preset inline-SVG icon library.
			 *
			 * Entries must follow the icon style contract documented at the top
			 * of `inc/icons-svg.php`: a 24×24 outline glyph supplied as the
			 * INNER markup of the `<svg>` element, no `width` / `height`, no
			 * hardcoded colours. `customify_get_svg_icon()` supplies the root
			 * element, so anything here inherits the contract automatically.
			 *
			 * Customify Pro and child themes use this to extend the picker —
			 * the Customizer control reads the same list, so a filtered-in icon
			 * shows up in the picker with no extra registration.
			 *
			 * @since 0.4.25
			 *
			 * @param array<string, array{label: string, body: string}> $library Icon key => label + inner markup.
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

		$args = wp_parse_args(
			$args,
			array(
				'class' => '',
			)
		);

		$class = trim( 'customify-svg-icon customify-svg-icon--' . $key . ' ' . $args['class'] );

		return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none"'
			. ' stroke="currentColor" stroke-width="2" stroke-linecap="round"'
			. ' stroke-linejoin="round" aria-hidden="true" focusable="false">'
			. $icons[ $key ]['body']
			. '</svg>';
	}
}

if ( ! function_exists( 'customify_get_svg_icons_for_js' ) ) {
	/**
	 * The preset library flattened for the Customizer control script.
	 *
	 * Shape: `array( '<key>' => array( 'label' => string, 'svg' => string ) )`.
	 * Rendered markup rather than raw bodies so the JS never has to know the
	 * root-element contract — it just injects what PHP produced, which is
	 * byte-identical to what the front end will print.
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
