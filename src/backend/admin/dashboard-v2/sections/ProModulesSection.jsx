/**
 * Pro Modules card section on the Welcome tab.
 *
 * Two render paths:
 *   1. Pro plugin NOT active (`boot.proActive === false`) → marketing list.
 *      Each row shows title + description + optional "Docs" trailing link.
 *      Header CTA is "Upgrade Now".
 *   2. Pro plugin active → same list (overridden via the
 *      `customify.dashboard.pro.modules` filter from Pro's bundle) plus a
 *      `@wordpress/components` FormToggle leading slot per row + Settings
 *      trailing link when the module has settings + is enabled. Toggling
 *      calls a handler Pro supplies via the
 *      `customify.dashboard.pro.toggle` filter; the kit ships a no-op +
 *      reject so the marketing path can't accidentally flip server state.
 *      When a module has `canToggle: false` (e.g. WooCommerce Booster
 *      without WooCommerce active) the toggle renders disabled + the row
 *      shows `toggleDisableNotice` inline beside the name.
 *
 * Pro extension surface:
 *   - `customify.dashboard.pro.modules`  — replaces the module catalogue.
 *   - `customify.dashboard.pro.toggle`   — toggle handler `(classKey,
 *                                            nextEnabled) => Promise<{ enabled }>`.
 */

import { Fragment, useCallback, useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import {
	Card,
	CardHeader,
	Button,
	FormToggle,
	Icon,
} from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';
import { navigate } from '@pressmaximum/dashboard-kit';

import { useProModules } from '../data/proModules.js';
import { ModuleList, ModuleRow, ModuleSubmodules } from '../ui/ModuleList.jsx';

// Same green-circle check glyph used by the Settings tab snackbar so
// module activate / deactivate toasts visually match the Save Settings
// success cue. SnackbarList drops this inside .components-snackbar__icon.
const SUCCESS_GLYPH = (
	<span className="customify-dashboard-snackbar__check">
		<Icon icon={ checkIcon } size={ 14 } />
	</span>
);

const NOTICES_STORE = 'core/notices';

function DocsLink( { href } ) {
	if ( ! href ) {
		return null;
	}
	return (
		<a
			className="customify-dashboard-module-link"
			href={ href }
			target="_blank"
			rel="noopener noreferrer"
		>
			{ __( 'Docs', 'customify' ) }
		</a>
	);
}

export default function ProModulesSection( { boot } ) {
	const modules = useProModules();
	const proActive = Boolean( boot?.proActive );

	// Recompute every render — `modules` comes from applyFilters which a
	// later-loading bundle (Pro) may mutate after our first paint.
	const byId = {};
	modules.forEach( ( m ) => {
		byId[ m.id ] = m;
	} );
	const topLevel = modules.filter( ( m ) => ! m.parent );

	// Local optimistic state; on mount seed from `enabled` flags Pro injects.
	const [ enabledMap, setEnabledMap ] = useState( () => {
		const map = {};
		modules.forEach( ( m ) => {
			map[ m.id ] = !! m.enabled;
		} );
		return map;
	} );
	const [ pendingMap, setPendingMap ] = useState( {} );

	// Recompute every render — Pro bridge's toggle handler registers
	// AFTER the theme's mountDashboard runs. A useMemo over [] would
	// cache the pre-registration `null` value and silently disable
	// the toggle flow.
	const proToggle = applyFilters( 'customify.dashboard.pro.toggle', null );

	const handleToggle = useCallback(
		( id ) => {
			if ( typeof proToggle !== 'function' ) {
				return;
			}
			// Safety net: the disabled FormToggle suppresses onChange in the
			// browser, but a programmatic dispatch here would otherwise let a
			// gated module (e.g. WooCommerce Booster without WooCommerce
			// active) flip its state on the server.
			if ( byId[ id ] && byId[ id ].canToggle === false ) {
				return;
			}
			const current = !! enabledMap[ id ];
			const next = ! current;
			const moduleName = byId[ id ]?.name || id;

			setEnabledMap( ( prev ) => ( { ...prev, [ id ]: next } ) );
			setPendingMap( ( prev ) => ( { ...prev, [ id ]: true } ) );

			Promise.resolve( proToggle( id, next ) )
				.then( ( res ) => {
					const flag = !! ( res && res.enabled );
					setEnabledMap( ( prev ) => ( { ...prev, [ id ]: flag } ) );
					dispatch( NOTICES_STORE ).createSuccessNotice(
						sprintf(
							next
								? // translators: %s module name.
								  __( '"%s" activated.', 'customify' )
								: // translators: %s module name.
								  __( '"%s" deactivated.', 'customify' ),
							moduleName,
						),
						{
							type: 'snackbar',
							isDismissible: true,
							icon: SUCCESS_GLYPH,
						},
					);
				} )
				.catch( () => {
					setEnabledMap( ( prev ) => ( { ...prev, [ id ]: current } ) );
					dispatch( NOTICES_STORE ).createErrorNotice(
						sprintf(
							// translators: %s module name.
							__( 'Could not update "%s". Please try again.', 'customify' ),
							moduleName,
						),
						{ type: 'snackbar', isDismissible: true },
					);
				} )
				.finally( () => {
					setPendingMap( ( prev ) => {
						const copy = { ...prev };
						delete copy[ id ];
						return copy;
					} );
				} );
		},
		[ proToggle, enabledMap, byId ],
	);

	const renderRow = ( mod, isSub = false ) => {
		const checked = !! enabledMap[ mod.id ];
		const pending = !! pendingMap[ mod.id ];
		// The toggle is part of the Pro path only; Free renders the row
		// without a toggle at all.
		const showToggle = proActive && typeof proToggle === 'function';
		// `canToggle: false` arrives from Pro when a runtime dependency is
		// missing (WooCommerce Booster + its sub-modules when WooCommerce
		// isn't active). Render the toggle disabled instead of hiding it
		// so the user sees the row state + its notice.
		const allowed = mod.canToggle !== false;
		const toggleDisabled = pending || ! allowed;
		const showSettings =
			showToggle && allowed && checked && mod.hasSettings && ! pending;
		const showsSubs = ! isSub && mod.subModules && mod.subModules.length > 0;
		const notice =
			showToggle && ! allowed && mod.toggleDisableNotice
				? mod.toggleDisableNotice
				: null;

		return (
			<ModuleRow
				key={ mod.id }
				title={ mod.name }
				description={ mod.description }
				notice={ notice }
				hasSubs={ showsSubs }
				leading={
					showToggle ? (
						<FormToggle
							checked={ checked }
							onChange={ () => handleToggle( mod.id ) }
							disabled={ toggleDisabled }
							aria-label={ mod.name }
						/>
					) : null
				}
				trailing={
					<>
						{ showSettings && (
							<button
								type="button"
								className="customify-dashboard-module-link customify-dashboard-module-link--settings"
								onClick={ () => navigate( `#settings/${ mod.id }` ) }
							>
								{ __( 'Settings', 'customify' ) }
							</button>
						) }
						<DocsLink href={ mod.docHref } />
					</>
				}
			/>
		);
	};

	return (
		<Card className="customify-dashboard-welcome__pro">
			<CardHeader>
				<h2 className="customify-dashboard-welcome__checklist-title">
					{ __( 'Customify Pro modules', 'customify' ) }
				</h2>
				{ ! proActive && (
					<Button
						variant="primary"
						href={ boot?.urls?.proUpgrade || '#' }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Upgrade now', 'customify' ) }
					</Button>
				) }
			</CardHeader>
			<ModuleList>
				{ topLevel.map( ( mod ) => (
					<Fragment key={ mod.id }>
						{ renderRow( mod, false ) }
						{ mod.subModules && mod.subModules.length > 0 && (
							<ModuleSubmodules>
								{ mod.subModules.map( ( subId ) => {
									const sub = byId[ subId ];
									return sub ? renderRow( sub, true ) : null;
								} ) }
							</ModuleSubmodules>
						) }
					</Fragment>
				) ) }
			</ModuleList>
		</Card>
	);
}
