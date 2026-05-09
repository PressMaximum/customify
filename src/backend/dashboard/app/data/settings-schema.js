/**
 * Settings field schema — single source of truth for Settings.js to render
 * each card. Matches the option shape sanitized in PHP
 * (Customify::theme_dashboard_sanitize).
 *
 * Field types: toggle | select | action | static. Each field declares its
 * group + key so the controlled state can read/write the nested option.
 *
 * The Performance card is conditional on the Customify Pro plugin being
 * active — the underlying option (customify_pro_assets_compress) lives in
 * the Pro plugin and Customify mirrors it on save.
 */

import { __, sprintf } from '@wordpress/i18n';
import { brush, chartBar } from '@wordpress/icons';

import {
	PRO_ACTIVE,
	PRO_ASSETS_WRITABLE,
	PRO_ASSETS_SAVE_PATH,
} from '../config';

export const SETTINGS_NAV = [
	{
		id: 'icons',
		label: __( 'Icons', 'customify' ),
		// `brush` reads as "visual styling" — fits a Font Awesome version
		// picker that decides which icon glyphs ship with the theme.
		icon: brush,
	},
	...( PRO_ACTIVE
		? [
				{
					id: 'performance',
					label: __( 'Performance', 'customify' ),
					// `chartBar` is the standard performance/metrics glyph
					// in @wordpress/icons.
					icon: chartBar,
				},
		  ]
		: [] ),
];

export const DEFAULT_SUB_TAB = 'icons';

const performanceCard = {
	title: __( 'Performance', 'customify' ),
	group: 'pro',
	fields: [
		{
			type: 'toggle',
			key: 'assets_compress',
			title: __(
				'Combine module asset files into 1, reducing HTTP requests',
				'customify'
			),
			description: __(
				'Customify Pro modules each ship their own CSS and JS files, so each one costs an HTTP request. Enable this to bundle them into a single file (recommended).',
				'customify'
			),
			disabled: ! PRO_ASSETS_WRITABLE,
			disabledHint: PRO_ASSETS_SAVE_PATH
				? sprintf(
						/* translators: %s: filesystem path of the save directory */
						__(
							"Save path %s isn't writable, so this option can't be enabled. Make the directory writable and reload to retry.",
							'customify'
						),
						PRO_ASSETS_SAVE_PATH
				  )
				: __(
						"The Pro asset save directory isn't writable, so this option can't be enabled.",
						'customify'
				  ),
		},
		{
			type: 'static',
			title: __( 'Save path', 'customify' ),
			description: __(
				'Where the combined asset file is written. Must be writable by PHP for combining to work.',
				'customify'
			),
			static: PRO_ASSETS_SAVE_PATH || __( '— not detected —', 'customify' ),
		},
	],
};

export const SETTINGS_CARDS = {
	icons: {
		title: __( 'Font Icons', 'customify' ),
		group: 'icons',
		fields: [
			{
				type: 'select',
				key: 'fa_version',
				title: __( 'Font Awesome version', 'customify' ),
				description: __(
					'Choose which Font Awesome version is loaded across the theme. Pick v6 for new sites; the v4 + v5 fallback exists only for legacy content.',
					'customify'
				),
				options: [
					{
						value: 'v4',
						label: __( 'Font Awesome 4', 'customify' ),
					},
					{
						value: 'v6',
						label: __( 'Font Awesome 6', 'customify' ),
					},
					{
						value: 'v456',
						label: __(
							'Font Awesome 6 with v4 / v5 fallback',
							'customify'
						),
					},
				],
			},
		],
	},
	...( PRO_ACTIVE ? { performance: performanceCard } : {} ),
};
