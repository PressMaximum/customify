/**
 * Welcome "Things to do" checklist items. The `guide` field links to a
 * GuidePopover key (data/guides.js).
 */

import { __ } from '@wordpress/i18n';

import { customizerLink } from '../config';

export const THINGS_TO_DO = [
	{
		id: 'logo',
		title: __( 'Upload your logo', 'customify' ),
		description: __(
			'Set the brand logo and site identity in the WordPress Customizer.',
			'customify'
		),
		guide: 'logo',
		ctaLabel: __( 'Set Up', 'customify' ),
		ctaHref: customizerLink( { section: 'title_tagline' } ),
	},
	{
		id: 'header-builder',
		title: __( 'Build the header', 'customify' ),
		description: __(
			'Use the WYSIWYG header builder inside the Customizer to drop in logo, menu, search, cart, and more.',
			'customify'
		),
		guide: 'header-builder',
		ctaLabel: __( 'Set Up', 'customify' ),
		ctaHref: customizerLink( { panel: 'header_settings' } ),
	},
	{
		id: 'styling',
		title: __( 'Pick brand colors and typography', 'customify' ),
		description: __(
			'Set primary, secondary, link, and heading colors plus typography presets so every block reads on-brand.',
			'customify'
		),
		guide: 'styling',
		ctaLabel: __( 'Set Up', 'customify' ),
		ctaHref: customizerLink( { panel: 'styling_panel' } ),
	},
	{
		id: 'icons',
		title: __( 'Pick a Font Awesome version', 'customify' ),
		description: __(
			'Choose between v4, v6, or the v6 + v4/v5 fallback to keep legacy icons working.',
			'customify'
		),
		guide: 'icons',
		ctaLabel: __( 'Set Up', 'customify' ),
		ctaHref: '#settings',
	},
];
