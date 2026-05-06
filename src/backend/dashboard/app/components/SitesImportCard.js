/**
 * Welcome sidebar card promoting the Customify Sites Library plugin —
 * one-click import of ready-made starter sites. Renders one of three CTA
 * states based on bootstrap data:
 *
 *   not-installed → "Download Plugin" (GitHub releases)
 *   installed     → "Activate Plugin" (nonce-signed admin URL)
 *   active        → "View Site Library" (themes.php?page=customify-sites)
 *
 * Mirrors the legacy Customify_Dashboard::box_plugins() output but lives
 * inside the React dashboard's WelcomeLayout sidebar slot.
 */

import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

import { Card, Button } from '../../ui';
import { SITES_PLUGIN } from '../config';

export default function SitesImportCard() {
	const { state, actionUrl, actionLabel, detailsUrl, thumbnailUrl } =
		SITES_PLUGIN;

	const isActive = state === 'active';

	return (
		<Card title={ __( 'Customify ready to import sites', 'customify' ) }>
			{ thumbnailUrl && (
				<div className="pm-sites-card__thumb">
					<img
						src={ thumbnailUrl }
						alt={ __(
							'Customify sites library preview',
							'customify'
						) }
					/>
				</div>
			) }
			<div className="pm-sites-card__body">
				<p>
					{ createInterpolateElement(
						__(
							'<b>Customify Sites</b> is a free add-on for the Customify theme that lets you browse and import ready-made websites with a few clicks.',
							'customify'
						),
						{ b: <strong /> }
					) }
				</p>
				<div className="pm-sites-card__actions">
					{ actionUrl && actionLabel && (
						<Button
							variant={ isActive ? 'secondary' : 'primary' }
							href={ actionUrl }
							target={
								state === 'not-installed' ? '_blank' : undefined
							}
							rel={
								state === 'not-installed'
									? 'noreferrer'
									: undefined
							}
						>
							{ actionLabel }
						</Button>
					) }
					{ detailsUrl && ! isActive && (
						<a
							className="pm-sites-card__details"
							href={ detailsUrl }
							target="_blank"
							rel="noreferrer"
						>
							{ __( 'Details', 'customify' ) }
						</a>
					) }
				</div>
			</div>
		</Card>
	);
}
