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
import { __, sprintf } from '@wordpress/i18n';

import Welcome from './tabs/Welcome.jsx';
import Settings from './tabs/Settings.jsx';
import Changelog from './tabs/Changelog.jsx';

import './dashboard-v2.scss';
import brandIcon from './brand-icon.js';
import { wireBrandClick } from './brand-click.js';

if ( document.getElementById( 'customify-dashboard' ) ) {
	const boot = window.customifyDashboard || {};
	const versionLabel = boot.themeVersion
		? sprintf(
			// translators: %s is the theme version (e.g. "0.4.14").
			__( 'v%s — Free version', 'customify' ),
			boot.themeVersion,
		)
		: __( 'Free version', 'customify' );
	mountDashboard( {
		rootEl: '#customify-dashboard',
		bootGlobal: 'customifyDashboard',
		filterNamespace: 'customify',
		__: ( text ) => __( text, 'customify' ),
		brand: {
			name: __( 'Customify', 'customify' ),
			icon: brandIcon,
		},
		tabsAriaLabel: __( 'Customify dashboard tabs', 'customify' ),
		versionLabel,
		versionHref: '#changelog',
		versionAriaLabel: __( 'Theme version — view changelog', 'customify' ),
		helpItems: [
			{
				id: 'documentation',
				label: __( 'Documentation', 'customify' ),
				href: 'https://pressmaximum.com/docs/customify/',
			},
			{
				id: 'changelog',
				label: __( 'Changelog', 'customify' ),
				href: '#changelog',
			},
			{
				id: 'support',
				label: __( 'Contact support', 'customify' ),
				href: 'https://wordpress.org/support/theme/customify/',
			},
			{
				id: 'pro',
				label: __( 'Upgrade to Pro', 'customify' ),
				href: 'https://pressmaximum.com/customify/pro-upgrade/',
			},
		],
		helpLabels: {
			triggerLabel: __( 'Open help panel', 'customify' ),
			heading: __( 'Help', 'customify' ),
		},
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

	// WORKAROUND for kit issue K-009 (see dashboard-kit/KIT_ISSUES.md)
	// — REMOVE when kit lands brand.href support in 0.1.0.
	wireBrandClick( '#welcome' );
}
