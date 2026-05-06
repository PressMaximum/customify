/**
 * Multi-page guides shown in the GuidePopover. Each entry is an array of
 * pages: { iconName, title, body }. The body is JSX so we can interpolate
 * <strong> + <code> safely without dangerouslySetInnerHTML.
 *
 * Icon names map to the guide-* entries in ui/Icon/icons.js — those are
 * the 240×160 illustration SVGs ported verbatim from sample's POPOVERS
 * inline-script constants (ICON_BLOCK_GRID / ICON_DESIGN_KIT / etc.).
 */

import { __ } from '@wordpress/i18n';

export const GUIDES = {
	'browse-blocks': [
		{
			iconName: 'guide-block-grid',
			title: __( 'Getting Started with Customify', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'To start creating a page, you can add blocks by clicking the plus icon located on the left of the top toolbar.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'The Customify category will appear at the top of the block list, with each block highlighted in blue for easy identification.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-layers',
			title: __( 'Section is your full-width wrapper', 'customify' ),
			body: (
				<>
					<p>
						<strong>{ __( 'Section', 'customify' ) }</strong>{ ' ' }
						{ __(
							'always renders edge-to-edge. Drop one in to define a band of your page — hero, features, footer, anything.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Use the alignment toolbar to switch between contained, wide, and full width. The background, padding, and overflow are all set on the Section, not on its children.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-build',
			title: __( 'Container handles inner layout', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'Inside every Section sits one or more',
							'customify'
						) }{ ' ' }
						<strong>{ __( 'Container', 'customify' ) }</strong>{ ' ' }
						{ __(
							'blocks. Each Container has three modes: Stack (vertical), Grid (responsive columns), or Flex (horizontal).',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Switch modes from the sidebar — the same Container can become a hero column, a 3-up feature grid, or a navigation row without rebuilding it.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-block-grid',
			title: __(
				'Heading, Button, Image — the essentials',
				'customify'
			),
			body: (
				<>
					<p>
						{ __(
							'Three content blocks cover most needs: Heading with full typography control, Button with built-in icon and hover states, and Image with focal-point cropping and lazy-loading by default.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							"That's it. Five blocks total in Free, plus core WordPress blocks. No bloat, no overlap.",
							'customify'
						) }
					</p>
				</>
			),
		},
	],
	'design-kit': [
		{
			iconName: 'guide-design-kit',
			title: __( 'Browse the Design Kit library', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'A Design Kit is a curated set of Block Patterns styled for a specific niche — Spa, Handyman, Photography, and more on the way.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Open the kit library from the Welcome tab and preview each kit before you commit. Every pattern in a kit shares the same color palette, type scale, and spacing rhythm.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-layers',
			title: __( 'Import in one click', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'Pick a kit, click Import, and Customify drops a complete homepage into your site as a draft. Nothing goes live until you publish.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'The import is non-destructive — your existing pages, posts, and theme settings are untouched.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-build',
			title: __( 'Replace, then publish', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'Edit any imported page in the regular block editor. Replace demo headlines, swap images for your own, and tune colors via global styles.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Most users go from import to live homepage in under 10 minutes. You can keep iterating after launch — the patterns are just blocks.',
							'customify'
						) }
					</p>
				</>
			),
		},
	],
	performance: [
		{
			iconName: 'guide-gauge',
			title: __(
				'Per-block asset loading is on by default',
				'customify'
			),
			body: (
				<>
					<p>
						{ __(
							'Customify scans each rendered page and only ships the CSS and JS for blocks that actually appear there. A page with three blocks loads three blocks worth of assets — not the whole library.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'This single setting is what gets most sites to PageSpeed 90+ without further tuning.',
							'customify'
						) }
					</p>
				</>
			),
		},
		{
			iconName: 'guide-layers',
			title: __(
				'Inline critical CSS for faster paint',
				'customify'
			),
			body: (
				<p>
					{ __(
						'For an extra boost, enable Preload critical CSS. Above-the-fold styles are inlined directly into the page <head>, so the browser starts painting before any stylesheet downloads.',
						'customify'
					) }
				</p>
			),
		},
		{
			iconName: 'guide-gauge',
			title: __(
				'Local fonts and CSS output mode',
				'customify'
			),
			body: (
				<>
					<p>
						{ __(
							'Toggle Load Google Fonts locally to download fonts to your server — better performance and GDPR-friendly.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Switch the CSS output mode between per-page files (default), inline (zero requests), or a single global file (best for static sites).',
							'customify'
						) }
					</p>
				</>
			),
		},
	],
	'product-tour': [
		{
			iconName: 'guide-play',
			title: __( 'A 2-minute walkthrough', 'customify' ),
			body: (
				<p>
					{ __(
						'The fastest way to understand Customify is to watch it in action. The video walks through Container modes — Stack, Grid, and Flex — and shows how to build a complete hero from scratch.',
						'customify'
					) }
				</p>
			),
		},
		{
			iconName: 'guide-build',
			title: __( 'See the editor flow', 'customify' ),
			body: (
				<>
					<p>
						{ __(
							'Watch how Section, Container, and content blocks combine in the inserter and the List View.',
							'customify'
						) }
					</p>
					<p>
						{ __(
							'Pay attention to how Container modes are switched in the sidebar — that single dropdown is the whole layout system.',
							'customify'
						) }
					</p>
				</>
			),
		},
	],
};
