import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Base onboarding checklist items. Each item's `check()` runs against
 * the boot payload (read at mount time) — no network round-trips.
 *
 * Pro / child theme extends via `customify.dashboard.welcome.checklist`.
 *
 * @param {object} boot Boot payload.
 * @return {Array} Checklist item array (kit's ChecklistItem shape).
 */
export function useChecklist( boot ) {
	const urls = boot?.urls || {};
	const base = [
		{
			id: 'site-identity',
			label: __( 'Set your site title and logo', 'customify' ),
			description: __(
				'Fill in your brand name, tagline, and upload a logo from the Customizer.',
				'customify',
			),
			ctaLabel: __( 'Open identity settings', 'customify' ),
			ctaHref: urls.logoIdentity || urls.customize,
			check: () => false,
		},
		{
			id: 'header-builder',
			label: __( 'Build your header', 'customify' ),
			description: __(
				'Drag-and-drop rows in the Customizer to compose the header that fits your site.',
				'customify',
			),
			ctaLabel: __( 'Open header builder', 'customify' ),
			ctaHref: urls.headerBuilder || urls.customize,
			check: () => false,
		},
		{
			id: 'footer-builder',
			label: __( 'Build your footer', 'customify' ),
			description: __(
				'Add widget columns, copyright, and any custom rows to your footer.',
				'customify',
			),
			ctaLabel: __( 'Open footer builder', 'customify' ),
			ctaHref: urls.footerBuilder || urls.customize,
			check: () => false,
		},
		{
			id: 'styling',
			label: __( 'Pick your colors and typography', 'customify' ),
			description: __(
				'Set primary, secondary, text, and heading colors plus type scale.',
				'customify',
			),
			ctaLabel: __( 'Open styling', 'customify' ),
			ctaHref: urls.styling || urls.customize,
			check: () => false,
		},
		{
			id: 'homepage',
			label: __( 'Choose a homepage', 'customify' ),
			description: __(
				'Static page or your latest posts — pick what fits your site.',
				'customify',
			),
			ctaLabel: __( 'Configure homepage', 'customify' ),
			ctaHref: urls.homepage || urls.customize,
			check: () => false,
		},
	];

	return applyFilters( 'customify.dashboard.welcome.checklist', base, boot );
}
