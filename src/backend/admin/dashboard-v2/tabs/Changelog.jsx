/**
 * Changelog tab — renders releases parsed from changelog.txt
 * (PHP-side, shipped in boot data) via the kit's ReleaseBlock.
 *
 * Pro / child theme adds more streams via
 * `customify.dashboard.changelog.sources`. For now Customify Free has
 * a single source so the array indirection isn't necessary on screen.
 */

import { __ } from '@wordpress/i18n';
import { Card, CardBody } from '@wordpress/components';
import { ReleaseBlock, useBoot } from '@pressmaximum/dashboard-kit';

export default function Changelog() {
	const boot = useBoot();
	const releases = Array.isArray( boot?.changelog ) ? boot.changelog : [];

	if ( ! releases.length ) {
		return (
			<Card>
				<CardBody>
					<p>{ __( 'No changelog entries available.', 'customify' ) }</p>
				</CardBody>
			</Card>
		);
	}

	return (
		<div className="customify-dashboard-changelog">
			{ releases.map( ( release ) => (
				<ReleaseBlock
					key={ release.version }
					release={ release }
					labels={ {
						currentBadge: __( 'Current', 'customify' ),
					} }
					categoryLabels={ {
						new: __( 'New', 'customify' ),
						improved: __( 'Improved', 'customify' ),
						fixed: __( 'Fixed', 'customify' ),
						updated: __( 'Updated', 'customify' ),
						removed: __( 'Removed', 'customify' ),
						security: __( 'Security', 'customify' ),
						deprecated: __( 'Deprecated', 'customify' ),
						neutral: __( 'Note', 'customify' ),
					} }
				/>
			) ) }
		</div>
	);
}
