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
import FreeVsPro from './tabs/FreeVsPro.jsx';
import Settings from './tabs/Settings.jsx';
import StarterTemplates from './tabs/StarterTemplates.jsx';
import Changelog from './tabs/Changelog.jsx';

import './dashboard-v2.scss';
import brandIcon from './brand-icon.js';

function mount() {
	if ( ! document.getElementById( 'customify-dashboard' ) ) {
		return;
	}
	const boot = window.customifyDashboard || {};
	// When Customify Pro is active it filters `customify_dashboard_localize`
	// to inject `proVersion`; the header label switches to that version +
	// "Pro version" suffix. Falls back to the theme's own version label
	// for the Free path.
	const proActive    = !! boot.proActive;
	const proVersion   = ( boot.proVersion || '' ) + '';
	const themeVersion = ( boot.themeVersion || '' ) + '';
	let versionLabel;
	if ( proActive && proVersion ) {
		versionLabel = sprintf(
			// translators: %s is the Customify Pro plugin version (e.g. "0.4.16").
			__( 'v%s — Pro version', 'customify' ),
			proVersion,
		);
	} else if ( proActive ) {
		versionLabel = __( 'Pro version', 'customify' );
	} else if ( themeVersion ) {
		versionLabel = sprintf(
			// translators: %s is the Customify theme version (e.g. "0.4.14").
			__( 'v%s — Free version', 'customify' ),
			themeVersion,
		);
	} else {
		versionLabel = __( 'Free version', 'customify' );
	}
	mountDashboard( {
		rootEl: '#customify-dashboard',
		bootGlobal: 'customifyDashboard',
		filterNamespace: 'customify',
		__: ( text ) => __( text, 'customify' ),
		brand: {
			name: __( 'Customify', 'customify' ),
			icon: brandIcon,
			href: '#welcome',
			ariaLabel: __( 'Customify dashboard — go to Welcome', 'customify' ),
		},
		tabsAriaLabel: __( 'Customify dashboard tabs', 'customify' ),
		versionLabel,
		// Land on the Pro changelog source when Pro is active so the
		// `v{pro} — Pro version` anchor points at the matching releases
		// stream. Falls back to the free Customify source otherwise.
		versionHref: proActive
			? '#changelog/customify-pro'
			: '#changelog',
		versionAriaLabel: proActive
			? __( 'Customify Pro version — view changelog', 'customify' )
			: __( 'Theme version — view changelog', 'customify' ),
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
				href: 'https://pressmaximum.com/contact/',
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
		// Free vs Pro tab is Free-only: it ships the compare matrix that
		// pitches the Pro upgrade. When Pro is active the matrix becomes
		// noise, so we drop both the tab strip entry AND the
		// `#free-vs-pro` route — kit's HashRouter falls back to
		// `#welcome` for any leftover deep link. Slotted immediately to
		// the right of Settings (between Settings and Changelog) so the
		// "settings-then-upsell" reading order is preserved.
		baseTabs: [
			{ id: 'welcome', label: __( 'Welcome', 'customify' ) },
			{ id: 'settings', label: __( 'Settings', 'customify' ) },
			{ id: 'starter-templates', label: __( 'Starter Templates', 'customify' ) },
			...( proActive
				? []
				: [ { id: 'free-vs-pro', label: __( 'Free vs Pro', 'customify' ) } ] ),
			{ id: 'changelog', label: __( 'Changelog', 'customify' ) },
		],
		baseRoutes: {
			'#welcome': { component: Welcome, type: 'page' },
			'#settings': { component: Settings, type: 'page' },
			'#settings/:panelId': { component: Settings, type: 'page' },
			'#starter-templates': { component: StarterTemplates, type: 'page' },
			...( proActive
				? {}
				: { '#free-vs-pro': { component: FreeVsPro, type: 'page' } } ),
			'#changelog': { component: Changelog, type: 'page' },
			'#changelog/:sourceId': { component: Changelog, type: 'page' },
		},
		initialRoute: '#welcome',
	} );
}

// Defer to DOMContentLoaded so any Pro / child theme JS that registers
// extender filters (`customify.dashboard.settings.panels`,
// `…changelog.sources`, `…pro.modules`, `…pro.toggle`) has run before
// the React tree mounts and calls applyFilters() for the first time.
// Mirrors Blocksify's bootstrap pattern.
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount );
} else {
	mount();
}
