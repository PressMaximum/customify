/**
 * Pro Modules card on the Welcome tab.
 *
 * Two render paths:
 *   1. Pro plugin not active → show the static marketing list (data/pro-modules.js)
 *      with a "Docs" link per row and an "Upgrade Now" header CTA.
 *   2. Pro plugin active → show the server-supplied registry (window.customifyDashboard.proModules)
 *      with a ToggleSwitch per row that flips Customify_Pro::enable_module /
 *      disable_module via the `set_module_state` AJAX task.
 *
 * Toggle is optimistic: state flips immediately, AJAX in background. On
 * failure we revert and surface a snackbar notice.
 */

import { Fragment, useState, useCallback, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { Card, Icon, ModuleList, ModuleRow, Pill, ToggleSwitch } from '../../ui';
import { PRO_ACTIVE, PRO_MODULES_BOOT } from '../config';
import { PRO_MODULES } from '../data/pro-modules';
import { setModuleState } from '../api/pro-modules';
import ModuleSettingsModal from './ModuleSettingsModal';

const UPGRADE_URL =
	'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=welcome&utm_campaign=pro_modules';

function DocsLink( { href } ) {
	if ( ! href ) {
		return null;
	}
	return (
		<a
			className="pm-module-link"
			href={ href }
			target="_blank"
			rel="noreferrer"
		>
			{ __( 'Docs', 'customify' ) }
			<Icon name="chevron-right" size={ 12 } />
		</a>
	);
}

/* ---------------------------------------------------------------- */
/* Free path — static marketing list                                */
/* ---------------------------------------------------------------- */

function MarketingList() {
	return (
		<ModuleList>
			{ PRO_MODULES.map( ( m, i ) => (
				<Fragment key={ i }>
					<ModuleRow
						title={ m.title }
						description={ m.description }
						statusPill={ m.statusPill }
						className={
							m.subs ? 'pm-module-row--has-subs' : undefined
						}
						trailing={ <DocsLink href={ m.docHref } /> }
					/>
					{ m.subs && (
						<div className="pm-module-submodules">
							{ m.subs.map( ( sub, j ) => (
								<ModuleRow
									key={ j }
									title={ sub.title }
									description={ sub.description }
									trailing={ <DocsLink href={ sub.docHref } /> }
								/>
							) ) }
						</div>
					) }
				</Fragment>
			) ) }
		</ModuleList>
	);
}

/* ---------------------------------------------------------------- */
/* Pro path — server registry with ToggleSwitch                     */
/* ---------------------------------------------------------------- */

function ProList() {
	const { createNotice, removeNotice } = useDispatch( noticesStore );

	// Seed local enabled state from the bootstrap snapshot, keyed by classKey.
	const [ enabledMap, setEnabledMap ] = useState( () => {
		const m = {};
		PRO_MODULES_BOOT.forEach( ( mod ) => {
			m[ mod.classKey ] = !! mod.enabled;
		} );
		return m;
	} );
	const [ pendingMap, setPendingMap ] = useState( {} );
	const [ settingsModule, setSettingsModule ] = useState( null );

	const moduleByKey = useMemo( () => {
		const map = {};
		PRO_MODULES_BOOT.forEach( ( mod ) => {
			map[ mod.classKey ] = mod;
		} );
		return map;
	}, [] );

	const topLevel = useMemo(
		() => PRO_MODULES_BOOT.filter( ( m ) => ! m.parent ),
		[]
	);

	const toggle = useCallback(
		( classKey ) => {
			const current = !! enabledMap[ classKey ];
			const next = ! current;
			const moduleName =
				( moduleByKey[ classKey ] && moduleByKey[ classKey ].name ) ||
				classKey;

			// Optimistic flip + mark pending so the switch shows disabled.
			setEnabledMap( ( prev ) => ( { ...prev, [ classKey ]: next } ) );
			setPendingMap( ( prev ) => ( { ...prev, [ classKey ]: true } ) );

			setModuleState( classKey, next )
				.then( ( res ) => {
					// Server is authoritative — sync to its echoed flag.
					setEnabledMap( ( prev ) => ( {
						...prev,
						[ classKey ]: !! ( res && res.enabled ),
					} ) );
					const noticeId = `pm-toast-${ Date.now() }-${ classKey }`;
					createNotice(
						'success',
						sprintf(
							next
								? /* translators: %s: module name. */
								  __( '"%s" activated.', 'customify' )
								: /* translators: %s: module name. */
								  __( '"%s" deactivated.', 'customify' ),
							moduleName
						),
						{ type: 'snackbar', id: noticeId }
					);
					setTimeout( () => removeNotice( noticeId ), 3000 );
				} )
				.catch( () => {
					// Revert on failure.
					setEnabledMap( ( prev ) => ( {
						...prev,
						[ classKey ]: current,
					} ) );
					createNotice(
						'error',
						sprintf(
							/* translators: %s: module name. */
							__(
								'Could not update "%s". Please try again.',
								'customify'
							),
							moduleName
						),
						{ type: 'snackbar' }
					);
				} )
				.finally( () => {
					setPendingMap( ( prev ) => {
						const copy = { ...prev };
						delete copy[ classKey ];
						return copy;
					} );
				} );
		},
		[ enabledMap, moduleByKey, createNotice, removeNotice ]
	);

	const renderRow = ( mod, isSub = false ) => {
		const checked = !! enabledMap[ mod.classKey ];
		const pending = !! pendingMap[ mod.classKey ];
		// Show "Settings" trigger beside Docs when the Pro module exposes a
		// settings() page AND it's currently enabled.
		const showSettings = mod.hasSettings && checked && ! pending;
		return (
			<ModuleRow
				key={ mod.classKey }
				title={ mod.name }
				description={ mod.description }
				className={
					! isSub && mod.subModules && mod.subModules.length
						? 'pm-module-row--has-subs'
						: undefined
				}
				leading={
					mod.canToggle === false ? null : (
						<ToggleSwitch
							checked={ checked }
							onChange={ () => toggle( mod.classKey ) }
							ariaLabel={ mod.name }
							disabled={ pending }
						/>
					)
				}
				trailing={
					<>
						{ showSettings && (
							<button
								type="button"
								className="pm-module-link pm-module-link--settings"
								onClick={ () => setSettingsModule( mod ) }
							>
								{ __( 'Settings', 'customify' ) }
								<Icon name="chevron-right" size={ 12 } />
							</button>
						) }
						<DocsLink href={ mod.docHref } />
					</>
				}
			/>
		);
	};

	return (
		<>
			<ModuleList>
				{ topLevel.map( ( mod ) => (
					<Fragment key={ mod.classKey }>
						{ renderRow( mod, false ) }
						{ mod.subModules && mod.subModules.length > 0 && (
							<div className="pm-module-submodules">
								{ mod.subModules.map( ( subKey ) => {
									const sub = moduleByKey[ subKey ];
									if ( ! sub ) return null;
									return renderRow( sub, true );
								} ) }
							</div>
						) }
					</Fragment>
				) ) }
			</ModuleList>
			<ModuleSettingsModal
				isOpen={ !! settingsModule }
				onClose={ () => setSettingsModule( null ) }
				moduleKey={ settingsModule ? settingsModule.classKey : null }
				moduleName={ settingsModule ? settingsModule.name : '' }
			/>
		</>
	);
}

/* ---------------------------------------------------------------- */
/* Card shell                                                       */
/* ---------------------------------------------------------------- */

export default function ProModulesCard() {
	const isPro = PRO_ACTIVE && PRO_MODULES_BOOT.length > 0;

	const headerRight = isPro ? (
		// <Pill variant="pro">{ __( 'Pro Active', 'customify' ) }</Pill>
		<></>
	) : (
		<a
			className="pm-header-link"
			href={ UPGRADE_URL }
			target="_blank"
			rel="noreferrer"
		>
			{ __( 'Upgrade Now', 'customify' ) }
			<Icon name="chevron-right" size={ 12 } />
		</a>
	);

	return (
		<Card
			title={ __( 'Customify Pro Modules', 'customify' ) }
			headerRight={ headerRight }
		>
			{ isPro ? <ProList /> : <MarketingList /> }
		</Card>
	);
}
