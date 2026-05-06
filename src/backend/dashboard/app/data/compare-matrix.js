/**
 * Free vs Pro comparison matrix. Sections rendered top-down, each with its
 * own rows. Cell value rules (CompareTable.Row):
 *   true     → green check
 *   false    → gray dash
 *   string   → text value
 *   {value, muted: true} → muted text value
 *
 * Source of truth for the Pro module set:
 *   https://pressmaximum.com/docs/customify/customify-pro-modules/
 * Source of truth for pricing & money-back window:
 *   https://pressmaximum.com/customify/pro-upgrade/
 */

import { __ } from '@wordpress/i18n';

export const COMPARE_SECTIONS = [
	{
		title: __( 'Header & Footer Builder', 'customify' ),
		rows: [
			{
				name: __( 'Header drag-and-drop builder', 'customify' ),
				detail: __(
					'Logo, menu, search, social, cart, button, and more',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'Footer drag-and-drop builder', 'customify' ),
				detail: __(
					'Up to 6 column footer rows with widgets',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'Header and Footer Builder Booster', 'customify' ),
				detail: __(
					'Extra items + advanced styling for both builders',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Header Sticky', 'customify' ),
				detail: __(
					'Animated sticky header on scroll',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Header Transparent', 'customify' ),
				detail: __(
					'Overlay header on hero sections',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Mega Menu', 'customify' ),
				detail: __(
					'Multi-column drop-down with widget areas',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Multiple Headers', 'customify' ),
				detail: __(
					'Different header per page, post, archive, or shop',
					'customify'
				),
				cells: [ false, true ],
			},
		],
	},
	{
		title: __( 'Layout & Styling', 'customify' ),
		rows: [
			{
				name: __( 'Sidebar layouts', 'customify' ),
				detail: __(
					'Content / Content-Sidebar / Sidebar-Content / 3-column',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'Container width control', 'customify' ),
				detail: __(
					'Customizer-driven container & content widths',
					'customify'
				),
				cells: [ true, true ],
			},
			// {
			// 	name: __( 'Block patterns library', 'customify' ),
			// 	detail: __(
			// 		'Pre-designed sections to drop into pages',
			// 		'customify'
			// 	),
			// 	cells: [ true, true ],
			// },
			{
				name: __( 'Advanced Styling', 'customify' ),
				detail: __(
					'Page header title, cover, and titlebar typography',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Blog Pro layouts', 'customify' ),
				detail: __(
					'Grid, list, and masonry blog listings',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Portfolio post type', 'customify' ),
				detail: __(
					'Built-in portfolio listing & single layout',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Scroll To Top', 'customify' ),
				detail: __(
					'Animated scroll-to-top button',
					'customify'
				),
				cells: [ false, true ],
			},
		],
	},
	{
		title: __( 'Typography & Customization', 'customify' ),
		rows: [
			{
				name: __( 'Google Fonts', 'customify' ),
				detail: __(
					'Full Google Fonts library available in Customizer',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'Custom Fonts upload', 'customify' ),
				detail: __(
					'Upload self-hosted .woff / .woff2 fonts',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Typekit Fonts', 'customify' ),
				detail: __(
					'Connect a Typekit / Adobe Fonts project',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Customify Hooks', 'customify' ),
				detail: __(
					'Add custom hook scripts from the dashboard',
					'customify'
				),
				cells: [ false, true ],
			},
			// {
			// 	name: __( 'Cookie Notice', 'customify' ),
			// 	detail: __(
			// 		'GDPR cookie consent banner',
			// 		'customify'
			// 	),
			// 	cells: [ false, true ],
			// },
			{
				name: __( 'Infinity Scroll', 'customify' ),
				detail: __(
					'Auto-loads next posts and products on scroll',
					'customify'
				),
				cells: [ false, true ],
			},
		],
	},
	{
		title: __( 'WooCommerce', 'customify' ),
		rows: [
			{
				name: __( 'WooCommerce compatible', 'customify' ),
				detail: __(
					'Shop, cart, checkout, and account templates',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'WooCommerce Booster', 'customify' ),
				detail: __(
					'Extra style and layout controls for the shop',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'WC Single Product Layouts', 'customify' ),
				detail: __(
					'Multiple ready-to-use single-product layouts',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'WC Off Canvas Filter', 'customify' ),
				detail: __(
					'Off-canvas product filter for shop archives',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'WC Gallery Slider', 'customify' ),
				detail: __(
					'Slider gallery for product images',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'WC Quick View', 'customify' ),
				detail: __(
					'Product quick-view modal in product listings',
					'customify'
				),
				cells: [ false, true ],
			},
		],
	},
	{
		title: __( 'Support & Updates', 'customify' ),
		rows: [
			{
				name: __( 'Community support', 'customify' ),
				detail: __(
					'WordPress.org forums & documentation',
					'customify'
				),
				cells: [ true, true ],
			},
			{
				name: __( 'Updates & email support', 'customify' ),
				detail: __(
					'1 year of updates and direct dev-team support per license',
					'customify'
				),
				cells: [ false, true ],
			},
			{
				name: __( 'Number of sites', 'customify' ),
				detail: __(
					'Where you can activate the license',
					'customify'
				),
				cells: [
					{ value: __( 'Unlimited', 'customify' ), muted: true },
					__( '1 / 3 / Unlimited', 'customify' ),
				],
			},
			{
				name: __( 'Renewal discount', 'customify' ),
				detail: __(
					'Applied automatically on yearly renewal',
					'customify'
				),
				cells: [
					false,
					__( '20% off', 'customify' ),
				],
			},
		],
	},
];

export const COMPARE_CTA = {
	title: __( 'Ready to unlock everything?', 'customify' ),
	description: __(
		'Personal $59/yr (1 site) · Business $89/yr (3 sites) · Agency $129/yr (unlimited). 15-day money-back guarantee.',
		'customify'
	),
	ctaLabel: __( 'Upgrade to Pro →', 'customify' ),
	ctaHref:
		'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=compare&utm_campaign=upgrade_cta',
};
