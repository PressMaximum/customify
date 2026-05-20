import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Pro module promo list — ported from the legacy dashboard's
 * `pro_modules_box()`. Pro/child theme extends via
 * `customify.dashboard.pro.modules`.
 *
 * @return {Array<{id: string, name: string, desc?: string, sub?: boolean}>}
 */
export function useProModules() {
	const base = [
		{ id: 'header-transparent', name: __( 'Header Transparent', 'customify' ), desc: __( 'Make your website stand out with transparent header modules.', 'customify' ) },
		{ id: 'header-sticky', name: __( 'Header Sticky', 'customify' ), desc: __( 'Let your header stay accessible as users scroll.', 'customify' ) },
		{ id: 'header-footer-booster', name: __( 'Header & Footer Builder Booster', 'customify' ), desc: __( 'More header/footer builder items + advanced styling.', 'customify' ) },
		{ id: 'scroll-to-top', name: __( 'Scroll to Top', 'customify' ), desc: __( 'Animated scroll-to-top button for a better UX.', 'customify' ) },
		{ id: 'blog-pro', name: __( 'Blog Pro', 'customify' ), desc: __( 'Multiple post layouts for richer blog presentations.', 'customify' ) },
		{ id: 'advanced-styling', name: __( 'Advanced Styling', 'customify' ), desc: __( 'Layout + typography control for page header title and cover.', 'customify' ) },
		{ id: 'portfolio', name: __( 'Portfolio', 'customify' ), desc: __( 'Showcase your best projects in beautiful layouts.', 'customify' ) },
		{ id: 'multiple-headers', name: __( 'Multiple Headers', 'customify' ), desc: __( 'Unique headers per page, post, archive, or WooCommerce page.', 'customify' ) },
		{ id: 'mega-menu', name: __( 'Mega Menu', 'customify' ), desc: __( 'Mega-menu navigation with more space and visual hierarchy.', 'customify' ) },
		{ id: 'multilingual', name: __( 'Multilingual Integration', 'customify' ), desc: __( 'WPML support plus a built-in language-switcher header item.', 'customify' ) },
		{ id: 'custom-fonts', name: __( 'Custom Fonts', 'customify' ), desc: __( 'Upload and use self-hosted fonts across your site.', 'customify' ) },
		{ id: 'typekit', name: __( 'Typekit', 'customify' ), desc: __( 'Use Adobe Typekit fonts on your Customify site.', 'customify' ) },
		{ id: 'hooks', name: __( 'Customify Hooks', 'customify' ), desc: __( 'Add custom hook scripts without touching theme files.', 'customify' ) },
		{ id: 'woocommerce-booster', name: __( 'WooCommerce Booster', 'customify' ), desc: __( 'Creative control of style + layout options for your shop.', 'customify' ) },
		{ id: 'single-product-layouts', name: __( 'Single Product Layouts', 'customify' ), desc: __( 'Multiple beautiful single-product layouts.', 'customify' ), sub: true },
		{ id: 'off-canvas-filter', name: __( 'Off Canvas Filter', 'customify' ), desc: __( 'Off-canvas product filter for shop and archive pages.', 'customify' ), sub: true },
		{ id: 'gallery-slider', name: __( 'Product Gallery Slider', 'customify' ), desc: __( 'Slider for the WooCommerce product gallery.', 'customify' ), sub: true },
		{ id: 'quick-view', name: __( 'Quick View', 'customify' ), desc: __( 'Modal quick-view for product listings.', 'customify' ), sub: true },
		{ id: 'infinity-scroll', name: __( 'Infinity Scroll', 'customify' ), desc: __( 'Auto-load the next posts/products as the reader nears the bottom.', 'customify' ) },
	];

	return applyFilters( 'customify.dashboard.pro.modules', base );
}
