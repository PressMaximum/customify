/**
 * Changelog tab — multi-source releases timeline.
 *
 * Free ships a single source (Customify theme releases parsed PHP-side
 * from changelog.txt and shipped on `boot.changelog`). Customify Pro
 * adds itself as a second source via the kit's filter:
 *
 *   addFilter( 'customify.dashboard.changelog.sources', 'customify-pro/changelog',
 *     ( sources ) => sources.concat( [ {
 *       id: 'customify-pro',
 *       label: __( 'Customify Pro', 'customify-pro' ),
 *       fetch: () => apiFetch( { path: '/customify-pro/v1/changelog' } ),
 *     } ] )
 *   );
 *
 * When only one source is registered (Free default), the SubNav rail
 * is hidden — a 1-item rail adds chrome without information. The kit's
 * <ReleaseBlock> ships its own card chrome (background + border +
 * radius), so this file does NOT wrap them in another <Card>.
 */

import { useEffect, useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Notice, Spinner } from '@wordpress/components';
import {
	ReleaseBlock,
	SubNav,
	navigate,
	useBoot,
} from '@pressmaximum/dashboard-kit';

const RELEASE_LABELS = {
	currentBadge: __( 'Current', 'customify' ),
};

const CATEGORY_LABELS = {
	new: __( 'New', 'customify' ),
	added: __( 'New', 'customify' ),
	improved: __( 'Improved', 'customify' ),
	fixed: __( 'Fixed', 'customify' ),
	updated: __( 'Updated', 'customify' ),
	removed: __( 'Removed', 'customify' ),
	security: __( 'Security', 'customify' ),
	deprecated: __( 'Deprecated', 'customify' ),
	neutral: __( 'Note', 'customify' ),
};

function SourceReleases( { source } ) {
	const [ releases, setReleases ] = useState( null );
	const [ error, setError ] = useState( null );

	useEffect( () => {
		let cancelled = false;
		setReleases( null );
		setError( null );
		Promise.resolve( source.fetch() )
			.then( ( data ) => {
				if ( cancelled ) {
					return;
				}
				setReleases( Array.isArray( data ) ? data : [] );
			} )
			.catch( ( err ) => {
				if ( cancelled ) {
					return;
				}
				setError( err );
			} );
		return () => {
			cancelled = true;
		};
	}, [ source ] );

	if ( error ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ __( 'Could not load the changelog.', 'customify' ) }
			</Notice>
		);
	}

	if ( releases === null ) {
		return (
			<div className="customify-dashboard-changelog__loading">
				<Spinner />
				<span>{ __( 'Loading changelog…', 'customify' ) }</span>
			</div>
		);
	}

	if ( releases.length === 0 ) {
		return (
			<Notice status="info" isDismissible={ false }>
				{ __( 'No releases yet.', 'customify' ) }
			</Notice>
		);
	}

	return (
		<div className="customify-dashboard-changelog__releases">
			{ releases.map( ( release ) => (
				<ReleaseBlock
					key={ release.version }
					release={ release }
					labels={ RELEASE_LABELS }
					categoryLabels={ CATEGORY_LABELS }
				/>
			) ) }
		</div>
	);
}

export default function Changelog( { params } ) {
	const boot = useBoot();

	// Recompute on every render — Pro bridge JS registers
	// `customify.dashboard.changelog.sources` AFTER the theme bundle's
	// mountDashboard mounts the React tree. A useMemo over `[boot]`
	// (stable) captured before Pro's filter would never see the appended
	// Pro source. applyFilters is cheap.
	const sourcesBase = [
		{
			id: 'customify',
			label: __( 'Customify', 'customify' ),
			fetch: () =>
				Promise.resolve(
					Array.isArray( boot?.changelog ) ? boot.changelog : [],
				),
		},
	];
	const sourcesFiltered = applyFilters(
		'customify.dashboard.changelog.sources',
		sourcesBase,
	);
	const sources =
		Array.isArray( sourcesFiltered ) && sourcesFiltered.length > 0
			? sourcesFiltered
			: sourcesBase;

	const activeId = params?.sourceId || sources[ 0 ]?.id;
	const activeSource =
		sources.find( ( s ) => s.id === activeId ) || sources[ 0 ];

	// Redirect bare `#changelog` to the canonical multi-source path so
	// SubNav has a resolved active row. Skipped when there's only one
	// source — bare `#changelog` is fine for the single-source case.
	useEffect( () => {
		if ( ! params?.sourceId && sources.length > 1 && activeSource ) {
			navigate( `#changelog/${ activeSource.id }` );
		}
	}, [ params, sources, activeSource ] );

	if ( ! activeSource ) {
		return null;
	}

	if ( sources.length < 2 ) {
		return (
			<div className="customify-dashboard-changelog">
				<SourceReleases source={ activeSource } />
			</div>
		);
	}

	return (
		<div className="customify-dashboard-changelog customify-dashboard-changelog--multi">
			<SubNav
				items={ sources.map( ( s ) => ( {
					id: s.id,
					label: s.label,
					hash: `#changelog/${ s.id }`,
				} ) ) }
				activeId={ activeId }
				ariaLabel={ __( 'Changelog sources', 'customify' ) }
			/>
			<div className="customify-dashboard-changelog__pane">
				<SourceReleases source={ activeSource } />
			</div>
		</div>
	);
}
