import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Returns the Welcome-tab Customizer quick-link list, derived from the
 * PHP-localised boot payload. Each item carries a short description so
 * the 2-col grid renders title + description stacked.
 *
 * Pro / child theme extends via `customify.dashboard.welcome.links`.
 *
 * @param {object} boot Boot payload (from useBoot()).
 * @return {Array<{ id: string, title: string, description: string, href: string }>}
 */
export function useCustomizerLinks( boot ) {
	const urls = boot?.urls || {};
	const base = [
		{
			id: 'logoIdentity',
			title: __( 'Logo & site identity', 'customify' ),
			description: __( 'Upload logo, site title, tagline.', 'customify' ),
			href: urls.logoIdentity,
		},
		{
			id: 'layout',
			title: __( 'Layout settings', 'customify' ),
			description: __( 'Container width, content layout.', 'customify' ),
			href: urls.layout,
		},
		{
			id: 'headerBuilder',
			title: __( 'Header builder', 'customify' ),
			description: __( 'WYSIWYG header drag-and-drop.', 'customify' ),
			href: urls.headerBuilder,
		},
		{
			id: 'footerBuilder',
			title: __( 'Footer builder', 'customify' ),
			description: __( 'WYSIWYG footer drag-and-drop.', 'customify' ),
			href: urls.footerBuilder,
		},
		{
			id: 'styling',
			title: __( 'Styling', 'customify' ),
			description: __( 'Brand colors and site palette.', 'customify' ),
			href: urls.styling,
		},
		{
			id: 'typography',
			title: __( 'Typography', 'customify' ),
			description: __( 'Font family, size, line-height.', 'customify' ),
			href: urls.typography,
		},
		{
			id: 'sidebar',
			title: __( 'Sidebar settings', 'customify' ),
			description: __( 'Sidebar layout per context.', 'customify' ),
			href: urls.sidebar,
		},
	].filter( ( l ) => Boolean( l.href ) );

	return applyFilters( 'customify.dashboard.welcome.links', base, boot );
}
