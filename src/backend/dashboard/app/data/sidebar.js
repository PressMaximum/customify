/**
 * Sidebar content shared between Welcome + Changelog tabs:
 *   - License (Pro upgrade) card
 *   - Resources list (Documentation, Changelog, Support)
 *   - Cross-promo product rows
 *   - Review card
 */

import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

export const LICENSE_DATA = {
	title: __('Unlock the Pro features', 'customify'),
	tagline: __('Everything in Free, plus:', 'customify'),
	features: [
		createInterpolateElement(
			__(
				'<b>Header & Footer Builder Booster</b> with extra items',
				'customify'
			),
			{ b: <strong /> }
		),
		createInterpolateElement(
			__('<b>Header Transparent</b> & Sticky modules', 'customify'),
			{ b: <strong /> }
		),
		createInterpolateElement(
			__('<b>WooCommerce Booster</b> with shop layouts', 'customify'),
			{ b: <strong /> }
		),
		createInterpolateElement(
			__('<b>Mega Menu</b> & Multiple Headers', 'customify'),
			{ b: <strong /> }
		),

		createInterpolateElement(
			__('<b>1 year</b> updates & support', 'customify'),
			{ b: <strong /> }
		),
	],
	price: '$59',
	priceUnit: __('/year · 1 site', 'customify'),
	priceFootnote: __(
		'15-day money-back',
		'customify'
	),
	ctaLabel: __('Upgrade to Pro →', 'customify'),
	ctaHref:
		'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=sidebar&utm_campaign=license_card',
};

export const RESOURCES = [
	{
		icon: 'doc',
		label: __('Documentation', 'customify'),
		href: 'https://pressmaximum.com/docs/customify/',
	},
	{
		icon: 'clock-large',
		label: __('Changelog', 'customify'),
		href: '#changelog',
	},
	{
		icon: 'star-large',
		label: __('Join Facebook community', 'customify'),
		href: 'https://www.facebook.com/groups/133106770857743',
	},
	{
		icon: 'mail',
		label: __('Contact support', 'customify'),
		href: 'https://pressmaximum.com/support/',
	},
];

export const PRODUCTS = [
	{
		initials: 'CS',
		gradient: 'blue',
		name: __('Customify Sites Library', 'customify'),
		meta: __('Ready-made starter sites you can import', 'customify'),
		ctaLabel: __('Get Plugin', 'customify'),
		ctaHref: 'https://github.com/PressMaximum/customify-sites-library',
	},
	{
		initials: 'OP',
		gradient: 'purple',
		name: __('OnePress Theme', 'customify'),
		meta: __('Free one-page WordPress theme', 'customify'),
		ctaLabel: __('View Theme', 'customify'),
		ctaHref: 'https://wordpress.org/themes/onepress/',
	},
];

export const REVIEW = {
	rating: 5,
	message: __(
		'A 5-star review on WordPress.org keeps the project alive and helps others find it.',
		'customify'
	),
	ctaLabel: __('Leave a review →', 'customify'),
	ctaHref:
		'https://wordpress.org/support/theme/customify/reviews/?filter=5#new-post',
};
