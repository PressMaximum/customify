import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Returns the Welcome-tab Customizer quick-link list, derived from the
 * PHP-localised boot payload.
 *
 * Pro / child theme extends via `customify.dashboard.welcome.links`.
 *
 * @param {object} boot Boot payload (from useBoot()).
 * @return {Array<{ id: string, label: string, href: string }>}
 */
export function useCustomizerLinks( boot ) {
	const urls = boot?.urls || {};
	const base = [
		{ id: 'logoIdentity', label: __( 'Logo & site identity', 'customify' ), href: urls.logoIdentity },
		{ id: 'layout', label: __( 'Layout', 'customify' ), href: urls.layout },
		{ id: 'headerBuilder', label: __( 'Header builder', 'customify' ), href: urls.headerBuilder },
		{ id: 'footerBuilder', label: __( 'Footer builder', 'customify' ), href: urls.footerBuilder },
		{ id: 'styling', label: __( 'Styling', 'customify' ), href: urls.styling },
		{ id: 'typography', label: __( 'Typography', 'customify' ), href: urls.typography },
		{ id: 'sidebar', label: __( 'Sidebar', 'customify' ), href: urls.sidebar },
		{ id: 'blog', label: __( 'Blog posts', 'customify' ), href: urls.blog },
		{ id: 'homepage', label: __( 'Homepage', 'customify' ), href: urls.homepage },
	].filter( ( l ) => Boolean( l.href ) );

	return applyFilters( 'customify.dashboard.welcome.links', base, boot );
}
