/**
 * Free vs Pro compare matrix — hand-curated; ships in Customify Free.
 *
 * No REST endpoint, no boot dependency: the matrix is static copy bundled
 * into the dashboard JS. Module names + descriptions mirror the legacy
 * `Customify_Dashboard::pro_modules_box()` upsell grid and the new
 * `data/proModules.js` catalogue so the three surfaces stay consistent.
 *
 * Cell semantics (per kit `<CompareTable>` cell dispatch):
 *
 *   true            → green-circle check badge
 *   false           → gray-circle em-dash badge
 *   string          → literal text
 *   { value, muted } → muted-variant text
 *
 * Returns a function so the `__()` calls run after WordPress i18n hydrates
 * the `customify` text domain (mirrors Blocksify's `buildCompareMatrix()`
 * pattern).
 */

import { __ } from '@wordpress/i18n';

export function buildFreeVsProMatrix() {
	return [
		{
			id: 'site-composition',
			label: __( 'Site composition', 'customify' ),
			rows: [
				{
					id: 'header-builder',
					label: __( 'Drag-and-drop header builder', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'footer-builder',
					label: __( 'Drag-and-drop footer builder', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'layouts',
					label: __( 'Container widths + sidebar layouts', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'block-editor',
					label: __( 'Block editor patterns + alignwide / alignfull', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'multiple-headers',
					label: __( 'Multiple headers per page / post / archive', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'mega-menu',
					label: __( 'Mega menu', 'customify' ),
					free: false,
					pro: true,
				},
			],
		},
		{
			id: 'header-footer',
			label: __( 'Header & footer', 'customify' ),
			rows: [
				{
					id: 'standard-items',
					label: __( 'Standard items: logo, menu, search, social, HTML', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'header-sticky',
					label: __( 'Sticky header', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'header-footer-booster',
					label: __( 'Header & Footer Builder Booster (extra items + styling)', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'multilingual',
					label: __( 'WPML multilingual switcher header item', 'customify' ),
					free: false,
					pro: true,
				},
			],
		},
		{
			id: 'typography-styling',
			label: __( 'Typography & styling', 'customify' ),
			rows: [
				{
					id: 'google-fonts',
					label: __( 'Google Fonts', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'typography-tokens',
					label: __( 'Typography tokens (size, weight, line-height)', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'global-colors',
					label: __( 'Global colors (primary, secondary, text, link, heading)', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'custom-fonts',
					label: __( 'Self-hosted custom fonts', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'typekit',
					label: __( 'Adobe Typekit fonts', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'advanced-styling',
					label: __( 'Advanced page-header title + cover layouts', 'customify' ),
					free: false,
					pro: true,
				},
			],
		},
		{
			id: 'blog-portfolio',
			label: __( 'Blog & portfolio', 'customify' ),
			rows: [
				{
					id: 'blog-standard',
					label: __( 'Blog listing + single-post layouts', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'blog-pro',
					label: __( 'Blog Pro layouts (grid / masonry / mixed)', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'portfolio',
					label: __( 'Portfolio post type + layouts', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'infinity-scroll',
					label: __( 'Infinity scroll for posts and products', 'customify' ),
					free: false,
					pro: true,
				},
			],
		},
		{
			id: 'woocommerce',
			label: __( 'WooCommerce', 'customify' ),
			rows: [
				{
					id: 'wc-compat',
					label: __( 'WooCommerce compatibility', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'wc-booster',
					label: __( 'WooCommerce Booster (shop styling + layout)', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'single-product-layouts',
					label: __( 'Single product layouts', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'off-canvas-filter',
					label: __( 'Off-canvas product filter', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'gallery-slider',
					label: __( 'Product gallery slider', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'quick-view',
					label: __( 'Product quick-view modal', 'customify' ),
					free: false,
					pro: true,
				},
			],
		},
		{
			id: 'workflow-support',
			label: __( 'Workflow & support', 'customify' ),
			rows: [
				{
					id: 'page-builders',
					label: __( 'Page builder compatibility (Elementor, Beaver, Divi)', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'updates',
					label: __( 'Auto-updates', 'customify' ),
					free: true,
					pro: true,
				},
				{
					id: 'scroll-to-top',
					label: __( 'Scroll-to-top button', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'hooks',
					label: __( 'Customify Hooks (custom code snippets)', 'customify' ),
					free: false,
					pro: true,
				},
				{
					id: 'support',
					label: __( 'Support', 'customify' ),
					free: { value: __( 'Community', 'customify' ), muted: true },
					pro: __( 'Priority', 'customify' ),
				},
			],
		},
	];
}
