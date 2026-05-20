/**
 * Customify Dashboard (v2) — SPA entry.
 *
 * Mounts the @pressmaximum/dashboard-kit shell into #customify-dashboard
 * (rendered by inc/admin/dashboard-v2.php). Pro extends via these
 * filters (hooked BEFORE this script runs):
 *
 *   customify.dashboard.tabs    — append/reorder tabs
 *   customify.dashboard.routes  — register tab routes
 *   customify.dashboard.welcome.checklist — onboarding tasks
 *   customify.dashboard.welcome.sections  — sections below the checklist
 */

import { mountDashboard } from '@pressmaximum/dashboard-kit';
import '@pressmaximum/dashboard-kit/style.css';
import { __ } from '@wordpress/i18n';

import Welcome from './tabs/Welcome.jsx';
import Settings from './tabs/Settings.jsx';
import Changelog from './tabs/Changelog.jsx';

import './dashboard-v2.scss';

// Inline brand mark — drawn with currentColor so the kit's tokens
// (header foreground) cascade through it. ~600 bytes; cheaper than an
// HTTP round-trip for a brand icon this small.
const BRAND_ICON_SVG = `<svg width="22" height="22" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><rect x="2" y="2" width="18" height="18" rx="4" fill="currentColor" opacity="0.12"/><path d="M6 7.5h10M6 11h10M6 14.5h7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>`;

if ( document.getElementById( 'customify-dashboard' ) ) {
	const boot = window.customifyDashboard || {};
	const themeVersion = boot.themeVersion ? `v${ boot.themeVersion }` : '';
	mountDashboard( {
		rootEl: '#customify-dashboard',
		bootGlobal: 'customifyDashboard',
		filterNamespace: 'customify',
		__: ( text ) => __( text, 'customify' ),
		brand: {
			name: __( 'Customify', 'customify' ),
			icon: BRAND_ICON_SVG,
			href: 'https://pressmaximum.com',
		},
		tabsAriaLabel: __( 'Customify dashboard tabs', 'customify' ),
		versionLabel: themeVersion,
		versionHref: '#changelog',
		versionAriaLabel: __( 'View changelog', 'customify' ),
		baseTabs: [
			{ id: 'welcome', label: __( 'Welcome', 'customify' ) },
			{ id: 'settings', label: __( 'Settings', 'customify' ) },
			{ id: 'changelog', label: __( 'Changelog', 'customify' ) },
		],
		baseRoutes: {
			'#welcome': { component: Welcome, type: 'page' },
			'#settings': { component: Settings, type: 'page' },
			'#changelog': { component: Changelog, type: 'page' },
		},
		initialRoute: '#welcome',
	} );
}
