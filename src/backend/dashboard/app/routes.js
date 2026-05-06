/**
 * Tab definitions for the dashboard. Single source of truth — AppHeader's
 * Tabs strip and the App's pane switcher both consume this list.
 *
 * Tab IDs map to URL hash fragments (#welcome, #settings, …) for deep links.
 *
 * "Free vs Pro" is dropped when the Customify Pro plugin is active — there's
 * no upsell to make and the user already has every feature on the chart.
 */

import { __ } from '@wordpress/i18n';

import { PRO_ACTIVE } from './config';

const ALL_TABS = [
	{ id: 'welcome', label: __( 'Dashboard', 'customify' ) },
	{ id: 'settings', label: __( 'Settings', 'customify' ) },
	{ id: 'free-vs-pro', label: __( 'Free vs Pro', 'customify' ) },
	{ id: 'changelog', label: __( 'Changelog', 'customify' ) },
];

export const TABS = PRO_ACTIVE
	? ALL_TABS.filter( ( t ) => t.id !== 'free-vs-pro' )
	: ALL_TABS;

export const DEFAULT_TAB = 'welcome';

export function isValidTab( id ) {
	return TABS.some( ( t ) => t.id === id );
}
