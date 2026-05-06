/**
 * Welcome sidebar — recommended free plugins from wordpress.org.
 *
 * Server (Customify::theme_dashboard_inject_recommend_plugins) fetches
 * plugin metadata via plugins_api with a 12h transient cache, filters out
 * already-active plugins, and ships only rows worth surfacing. Each row
 * shows the plugin's wp.org icon + name + Install/Activate button +
 * Details link to its wp.org page.
 */

import { __ } from '@wordpress/i18n';

import { Card, Button } from '../../ui';
import { RECOMMEND_PLUGINS } from '../config';

export default function RecommendPluginsCard() {
	if ( ! RECOMMEND_PLUGINS.length ) {
		return null;
	}
	return (
		<Card title={ __( 'Recommend Plugins', 'customify' ) }>
			<ul className="pm-recommend-plugins">
				{ RECOMMEND_PLUGINS.map( ( plugin ) => (
					<li
						key={ plugin.slug }
						className="pm-recommend-plugins__item"
					>
						{ plugin.iconUrl && (
							<img
								className="pm-recommend-plugins__icon"
								src={ plugin.iconUrl }
								alt=""
								loading="lazy"
							/>
						) }
						<div className="pm-recommend-plugins__body">
							<p className="pm-recommend-plugins__name">
								{ plugin.name }
							</p>
							<div className="pm-recommend-plugins__actions">
								{ plugin.actionUrl && (
									<Button
										variant={
											plugin.state === 'installed'
												? 'primary'
												: 'secondary'
										}
										size="small"
										href={ plugin.actionUrl }
									>
										{ plugin.actionLabel }
									</Button>
								) }
								{ plugin.detailsUrl && (
									<a
										className="pm-recommend-plugins__details"
										href={ plugin.detailsUrl }
										target="_blank"
										rel="noreferrer"
									>
										{ __( 'Details', 'customify' ) }
									</a>
								) }
							</div>
						</div>
					</li>
				) ) }
			</ul>
		</Card>
	);
}
