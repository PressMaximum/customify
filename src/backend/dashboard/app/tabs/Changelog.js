/**
 * Changelog tab — lazy-loaded. Fetches the raw changelog.txt content via
 * admin-ajax on first mount (which happens when the user activates this
 * tab — App conditionally renders the active pane), then parses it
 * client-side into release records. A module-level memo caches the parsed
 * data so re-mounts (tab switching) don't refetch or re-parse.
 *
 * Format spec for changelog.txt:
 *   = VERSION - DATE [Current] =     header; "[Current]" optional
 *   * TAG: change text                   one entry per line
 *   * TAG: text with `inline code`       backticks render as <code>
 *
 * Recognized tags (case-insensitive, matched to .pm-change-tag-- variants):
 *   New | Added | Updated | Improved | Fixed | Changed | Breaking
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import {
	Card,
	ReleaseBlock,
	LicenseCard,
	ResourceList,
	WelcomeLayout,
	Button,
} from '../../ui';
import { fetchChangelog } from '../api/changelog';
import { LICENSE_DATA, RESOURCES } from '../data/sidebar';

const INITIAL_VISIBLE = 5;

// Module-level memo — survives unmount/remount when switching tabs.
let CACHED_RELEASES = null;

function parseInlineCode( text ) {
	if ( typeof text !== 'string' || text.indexOf( '`' ) === -1 ) {
		return text;
	}
	const parts = text.split( /`([^`]+)`/g );
	return parts.map( ( part, i ) =>
		i % 2 === 1 ? <code key={ i }>{ part }</code> : part
	);
}

/**
 * Tag alias map — normalizes raw changelog tags to a smaller set the
 * ReleaseBlock CSS knows how to colorize. Keys are lowercased raw tags;
 * values are the canonical tag (matches `.pm-change-tag--{value}`).
 */
const TAG_ALIASES = {
	change: 'changed',
	changes: 'changed',
	add: 'added',
	adds: 'added',
	addition: 'added',
	additions: 'added',
	update: 'updated',
	updates: 'updated',
	improve: 'improved',
	improvement: 'improved',
	improvements: 'improved',
	enhanced: 'improved',
	enhancement: 'improved',
	enhancements: 'improved',
	fix: 'fixed',
	fixes: 'fixed',
	bugfix: 'fixed',
	bug: 'fixed',
	remove: 'removed',
	removal: 'removed',
	deprecate: 'removed',
	deprecated: 'removed',
	note: 'notes',
	break: 'breaking',
};

function normalizeTag( raw ) {
	const key = String( raw || '' ).toLowerCase();
	return TAG_ALIASES[ key ] || key;
}

/**
 * Parse a single header string into { version, date, isCurrent }.
 *
 * Accepts either of these formats (date is optional, [Current] is optional
 * and may sit anywhere):
 *
 *   0.4.14
 *   0.4.14 - May 6, 2026
 *   0.4.14 [Current]
 *   0.4.14 - May 6, 2026 [Current]
 *
 * The version is taken from the first whitespace-separated token so it
 * tolerates extra junk after it.
 */
function parseHeader( raw ) {
	let header = raw.trim();
	const isCurrent = /\[Current\]/i.test( header );
	if ( isCurrent ) {
		header = header.replace( /\[Current\]/i, '' ).trim();
	}

	let version = '';
	let date = '';
	if ( header.includes( ' - ' ) ) {
		const [ v, ...rest ] = header.split( ' - ' );
		version = v.trim();
		date = rest.join( ' - ' ).trim();
	} else {
		// No explicit date separator — treat first token as version, the
		// remainder (if any) as a free-form date string.
		const match = header.match( /^(\S+)\s*(.*)$/ );
		if ( match ) {
			version = match[ 1 ].trim();
			date = ( match[ 2 ] || '' ).trim();
		} else {
			version = header;
		}
	}
	return { version, date, isCurrent };
}

/**
 * Parse the raw changelog.txt content into release records.
 *
 * Header lines start with single `= ` so a `== Title ==` intro line is
 * skipped. Both `= VERSION =` and `= VERSION - DATE =` are accepted; the
 * `[Current]` flag is optional and falls through to "newest release" if
 * never specified explicitly.
 */
function parseChangelog( raw ) {
	if ( typeof raw !== 'string' || ! raw ) {
		return [];
	}
	const content = raw.replace( /\r\n/g, '\n' );

	// Split on whole-line single-`=` headers, keeping the captured group.
	// Result: [pre-content, header1, body1, header2, body2, ...]
	const parts = content.split( /^=\s*(.+?)\s*=[ \t]*$/m );
	const releases = [];
	let sawExplicitCurrent = false;

	for ( let i = 1; i < parts.length - 1; i += 2 ) {
		const { version, date, isCurrent } = parseHeader( parts[ i ] );
		if ( ! version ) {
			continue;
		}

		const body = ( parts[ i + 1 ] || '' ).trim();
		const changes = [];

		for ( const line of body.split( '\n' ) ) {
			const trimmed = line.trim();
			if ( ! trimmed.startsWith( '*' ) ) {
				continue;
			}
			const stripped = trimmed.replace( /^\*\s*/, '' );
			const tagged = stripped.match( /^([A-Za-z]+)\s*:\s*(.+)$/ );
			if ( tagged ) {
				changes.push( {
					tag: normalizeTag( tagged[ 1 ] ),
					text: parseInlineCode( tagged[ 2 ].trim() ),
				} );
			} else if ( stripped ) {
				// Untagged bullet — surface it as a plain note instead of
				// swallowing the line entirely.
				changes.push( {
					tag: 'notes',
					text: parseInlineCode( stripped ),
				} );
			}
		}

		const release = { version, date, changes };
		if ( isCurrent ) {
			release.current = true;
			sawExplicitCurrent = true;
		}
		releases.push( release );
	}

	// Auto-mark the first release as current when the file doesn't carry an
	// explicit [Current] flag — matches reader expectation that the topmost
	// version is the one you're running.
	if ( ! sawExplicitCurrent && releases.length > 0 ) {
		releases[ 0 ].current = true;
	}

	return releases;
}

export default function Changelog() {
	const [ releases, setReleases ] = useState( CACHED_RELEASES || [] );
	const [ status, setStatus ] = useState(
		CACHED_RELEASES ? 'ready' : 'loading'
	);
	const [ visible, setVisible ] = useState( INITIAL_VISIBLE );

	useEffect( () => {
		if ( CACHED_RELEASES ) {
			return undefined;
		}
		let alive = true;
		fetchChangelog()
			.then( ( raw ) => {
				if ( ! alive ) {
					return;
				}
				CACHED_RELEASES = parseChangelog( raw );
				setReleases( CACHED_RELEASES );
				setStatus( 'ready' );
			} )
			.catch( () => {
				if ( alive ) {
					setStatus( 'error' );
				}
			} );
		return () => {
			alive = false;
		};
	}, [] );

	const shown = releases.slice( 0, visible );
	const hasMore = visible < releases.length;

	const main = (
		<Card title={ __( 'Latest Releases', 'customify' ) }>
			{ status === 'loading' && (
				<div className="pm-changelog-more">
					{ __( 'Loading…', 'customify' ) }
				</div>
			) }
			{ status === 'error' && (
				<div className="pm-changelog-more">
					{ __( 'Failed to load changelog.', 'customify' ) }
				</div>
			) }
			{ status === 'ready' &&
				shown.map( ( r ) => (
					<ReleaseBlock key={ r.version } { ...r } />
				) ) }
			{ status === 'ready' && hasMore && (
				<div className="pm-changelog-more">
					<Button
						variant="secondary"
						onClick={ () =>
							setVisible( ( v ) => v + INITIAL_VISIBLE )
						}
					>
						{ __( 'Load older releases', 'customify' ) }
					</Button>
				</div>
			) }
		</Card>
	);

	const sidebar = (
		<>
			<LicenseCard { ...LICENSE_DATA } />
			<Card title={ __( 'Resources', 'customify' ) }>
				<ResourceList items={ RESOURCES } />
			</Card>
		</>
	);

	return <WelcomeLayout main={ main } sidebar={ sidebar } />;
}
