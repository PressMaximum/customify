/**
 * Changelog tab — wraps a list of ReleaseBlocks in a Card so it
 * visually matches the other dashboard pages (which use Card + header
 * chrome). Releases are parsed PHP-side and shipped on
 * `boot.changelog`.
 */

import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { ReleaseBlock, useBoot } from '@pressmaximum/dashboard-kit';

export default function Changelog() {
	const boot = useBoot();
	const releases = Array.isArray( boot?.changelog ) ? boot.changelog : [];

	return (
		<div className="customify-dashboard-changelog">
			<Card className="customify-dashboard-changelog__card">
				<CardHeader>
					<h2 className="customify-dashboard-welcome__checklist-title">
						{ __( 'Changelog', 'customify' ) }
					</h2>
				</CardHeader>
				<CardBody>
					{ releases.length === 0 ? (
						<p>{ __( 'No changelog entries available.', 'customify' ) }</p>
					) : (
						releases.map( ( release ) => (
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
						) )
					) }
				</CardBody>
			</Card>
		</div>
	);
}
