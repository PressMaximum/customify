/**
 * Pro modules listing rendered on the Welcome tab and (in compact form) on
 * FreeVsPro. Each row has title + description. `docHref` is optional (not
 * all Pro modules have a public doc page). Optional `statusPill` adds a
 * small status tag next to the title. Optional `subs` nests sub-modules.
 *
 * Source: https://pressmaximum.com/docs/customify/customify-pro-modules/
 * Order matches the documentation index page.
 */

import { __ } from '@wordpress/i18n';

const DOCS_BASE = 'https://pressmaximum.com/docs/customify/customify-pro-modules/';

export const PRO_MODULES = [
	{
		title: __( 'Header Sticky', 'customify' ),
		description: __(
			'Keep your header accessible as users scroll, with a unique animated style.',
			'customify'
		),
		docHref: DOCS_BASE + 'header-sticky/',
	},
	{
		title: __( 'Header Transparent', 'customify' ),
		description: __(
			'Make your website stand out with a transparent header overlaid on hero sections.',
			'customify'
		),
		docHref: DOCS_BASE + 'header-transparent/',
	},
	{
		title: __( 'Header and Footer Builder Booster', 'customify' ),
		description: __(
			'Get more header and footer builder items plus advanced styling options.',
			'customify'
		),
		docHref: DOCS_BASE + 'advanced-header-footer-builder/',
	},
	{
		title: __( 'Scroll To Top', 'customify' ),
		description: __(
			'A scroll-to-top button with smooth animation for a better reader experience.',
			'customify'
		),
		docHref: DOCS_BASE + 'scroll-to-top/',
	},
	{
		title: __( 'Blog Pro', 'customify' ),
		description: __(
			'Show off your posts in any layout — grid, list, masonry — with the Blog Pro module.',
			'customify'
		),
		docHref: DOCS_BASE + 'blog-pro/',
	},
	{
		title: __( 'Portfolio', 'customify' ),
		description: __(
			'Show off your best projects in a beautiful, customizable layout.',
			'customify'
		),
		docHref: DOCS_BASE + 'portfolio/',
	},
	{
		title: __( 'Multiple Headers', 'customify' ),
		description: __(
			'Create unique headers for each page, post, archive, or WooCommerce surface.',
			'customify'
		),
		docHref: DOCS_BASE + 'multiple-headers/',
	},
	{
		title: __( 'Mega Menu', 'customify' ),
		description: __(
			'Create mega menus for sites that need more space for navigation.',
			'customify'
		),
		docHref: DOCS_BASE + 'mega-menu/',
	},
	{
		title: __( 'Advanced Styling', 'customify' ),
		description: __(
			'Control layout and typography for page header title, cover, and titlebar.',
			'customify'
		),
		docHref: DOCS_BASE + 'advanced-styling/',
	},
	{
		title: __( 'Typekit Fonts', 'customify' ),
		description: __(
			'Add Typekit fonts and use them on Customify-powered websites.',
			'customify'
		),
		docHref: DOCS_BASE + 'typekit-fonts/',
	},
	{
		title: __( 'Custom Fonts', 'customify' ),
		description: __(
			'Upload self-hosted fonts and use them on Customify-powered websites.',
			'customify'
		),
		docHref: DOCS_BASE + 'custom-fonts/',
	},
	{
		title: __( 'Customify Hooks', 'customify' ),
		description: __(
			'Add custom hook scripts directly through the dashboard.',
			'customify'
		),
		docHref: DOCS_BASE + 'customify-hooks/',
	},
	{
		title: __( 'WooCommerce Booster', 'customify' ),
		description: __(
			'Creative control of style and layout options for your shop.',
			'customify'
		),
		docHref: DOCS_BASE + 'woocommerce-booster/',
		subs: [
			{
				title: __( 'WC Single Product Layouts', 'customify' ),
				description: __(
					'More beautiful layouts for your single product page.',
					'customify'
				),
				docHref:
					DOCS_BASE + 'woocommerce-single-product-layouts/',
			},
			{
				title: __( 'WC Off Canvas Filter', 'customify' ),
				description: __(
					'Add off-canvas product filtering for shop and product archive pages.',
					'customify'
				),
				docHref:
					DOCS_BASE + 'woocommerce-off-canvas-filter/',
			},
			{
				title: __( 'WC Gallery Slider', 'customify' ),
				description: __(
					'Slider gallery for product images.',
					'customify'
				),
				docHref:
					DOCS_BASE + 'woocommerce-gallery-slider/',
			},
			{
				title: __( 'WC Quick View', 'customify' ),
				description: __(
					'Product quick-view modal for product listings.',
					'customify'
				),
				docHref: DOCS_BASE + 'woocommerce-quick-view/',
			},
		],
	},
	{
		title: __( 'Infinity Scroll', 'customify' ),
		description: __(
			'Auto-loads the next posts and products as the reader approaches the bottom of the page.',
			'customify'
		),
		docHref: DOCS_BASE + 'infinity-scroll/',
	},
	{
		title: __( 'Cookie Notice', 'customify' ),
		description: __(
			'GDPR-friendly cookie consent banner with customizable text and styling.',
			'customify'
		),
		docHref: DOCS_BASE + 'cookie-notice/',
	},
];
