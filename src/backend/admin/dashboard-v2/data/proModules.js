import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const DOCS_BASE = 'https://pressmaximum.com/docs/customify/customify-pro-modules/';

/**
 * Pro module catalogue rendered on the Welcome tab. Two shapes:
 *
 *   FREE      — marketing list. `docHref` only; no toggle.
 *   PRO       — same rows but with `classKey`, `enabled`, `hasSettings`,
 *               `canToggle`, optional `subModules` (array of `classKey`s).
 *
 * Customify Pro replaces this list via `customify.dashboard.pro.modules`
 * (returning the PRO shape sourced from window.customifyDashboard.proModules
 * / a REST endpoint).
 *
 * @return {Array<object>}
 */
export function useProModules() {
	const base = [
		{
			id: 'header-transparent',
			name: __( 'Header Transparent', 'customify' ),
			description: __( 'Make your website stand out with transparent header modules.', 'customify' ),
			docHref: DOCS_BASE + 'header-transparent/',
		},
		{
			id: 'header-sticky',
			name: __( 'Header Sticky', 'customify' ),
			description: __( 'Let your header stay accessible as users scroll.', 'customify' ),
			docHref: DOCS_BASE + 'header-sticky/',
		},
		{
			id: 'header-footer-booster',
			name: __( 'Header & Footer Builder Booster', 'customify' ),
			description: __( 'More header/footer builder items + advanced styling.', 'customify' ),
			docHref: DOCS_BASE + 'advanced-header-footer-builder/',
		},
		{
			id: 'scroll-to-top',
			name: __( 'Scroll to Top', 'customify' ),
			description: __( 'Animated scroll-to-top button for a better UX.', 'customify' ),
			docHref: DOCS_BASE + 'scroll-to-top/',
		},
		{
			id: 'blog-pro',
			name: __( 'Blog Pro', 'customify' ),
			description: __( 'Multiple post layouts for richer blog presentations.', 'customify' ),
			docHref: DOCS_BASE + 'blog-pro/',
		},
		{
			id: 'advanced-styling',
			name: __( 'Advanced Styling', 'customify' ),
			description: __( 'Layout + typography control for page header title and cover.', 'customify' ),
			docHref: DOCS_BASE + 'advanced-styling/',
		},
		{
			id: 'portfolio',
			name: __( 'Portfolio', 'customify' ),
			description: __( 'Showcase your best projects in beautiful layouts.', 'customify' ),
			docHref: DOCS_BASE + 'portfolio/',
		},
		{
			id: 'multiple-headers',
			name: __( 'Multiple Headers', 'customify' ),
			description: __( 'Unique headers per page, post, archive, or WooCommerce page.', 'customify' ),
			docHref: DOCS_BASE + 'multiple-headers/',
		},
		{
			id: 'mega-menu',
			name: __( 'Mega Menu', 'customify' ),
			description: __( 'Mega-menu navigation with more space and visual hierarchy.', 'customify' ),
			docHref: DOCS_BASE + 'mega-menu/',
		},
		{
			id: 'multilingual',
			name: __( 'Multilingual Integration', 'customify' ),
			description: __( 'WPML support plus a built-in language-switcher header item.', 'customify' ),
			docHref: DOCS_BASE,
		},
		{
			id: 'custom-fonts',
			name: __( 'Custom Fonts', 'customify' ),
			description: __( 'Upload and use self-hosted fonts across your site.', 'customify' ),
			docHref: DOCS_BASE + 'custom-fonts/',
		},
		{
			id: 'typekit',
			name: __( 'Typekit', 'customify' ),
			description: __( 'Use Adobe Typekit fonts on your Customify site.', 'customify' ),
			docHref: DOCS_BASE + 'typekit-fonts/',
		},
		{
			id: 'hooks',
			name: __( 'Customify Hooks', 'customify' ),
			description: __( 'Add custom hook scripts without touching theme files.', 'customify' ),
			docHref: DOCS_BASE + 'customify-hooks/',
		},
		{
			id: 'woocommerce-booster',
			name: __( 'WooCommerce Booster', 'customify' ),
			description: __( 'Creative control of style + layout options for your shop.', 'customify' ),
			docHref: DOCS_BASE + 'woocommerce-booster/',
			subModules: [
				'single-product-layouts',
				'off-canvas-filter',
				'gallery-slider',
				'quick-view',
			],
		},
		{
			id: 'single-product-layouts',
			name: __( 'Single Product Layouts', 'customify' ),
			description: __( 'Multiple beautiful single-product layouts.', 'customify' ),
			docHref: DOCS_BASE + 'woocommerce-single-product-layouts/',
			parent: 'woocommerce-booster',
		},
		{
			id: 'off-canvas-filter',
			name: __( 'Off Canvas Filter', 'customify' ),
			description: __( 'Off-canvas product filter for shop and archive pages.', 'customify' ),
			docHref: DOCS_BASE + 'woocommerce-off-canvas-filter/',
			parent: 'woocommerce-booster',
		},
		{
			id: 'gallery-slider',
			name: __( 'Product Gallery Slider', 'customify' ),
			description: __( 'Slider for the WooCommerce product gallery.', 'customify' ),
			docHref: DOCS_BASE + 'woocommerce-product-gallery-slider/',
			parent: 'woocommerce-booster',
		},
		{
			id: 'quick-view',
			name: __( 'Quick View', 'customify' ),
			description: __( 'Modal quick-view for product listings.', 'customify' ),
			docHref: DOCS_BASE + 'woocommerce-quick-view/',
			parent: 'woocommerce-booster',
		},
		{
			id: 'infinity-scroll',
			name: __( 'Infinity Scroll', 'customify' ),
			description: __( 'Auto-load the next posts/products as the reader nears the bottom.', 'customify' ),
			docHref: DOCS_BASE + 'infinity-scroll/',
		},
	];

	return applyFilters( 'customify.dashboard.pro.modules', base );
}
