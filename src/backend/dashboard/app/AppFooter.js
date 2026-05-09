/**
 * Dashboard footer — one row: WP credit + plugin credit on the left,
 * WP version on the right. Layout ported from .bsify-app-footer.
 */

import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

import { PLUGIN_VERSION, WP_VERSION } from './config';

export default function AppFooter() {
	const credit = createInterpolateElement(
		sprintf(
			/* translators: %s: plugin version. */
			__(
				'Thank you for creating with <wp>WordPress</wp> · Customify v%s by <pm>PressMaximum</pm>',
				'customify'
			),
			PLUGIN_VERSION
		),
		{
			wp: <a href="https://wordpress.org/" />,
			pm: <a href="https://pressmaximum.com/" />,
		}
	);

	return (
		<div className="pm-app-footer">
			<span>{ credit }</span>
			<span className="pm-app-footer__right">
				{ sprintf(
					/* translators: %s: WordPress version number. */
					__( 'Get Version %s', 'customify' ),
					WP_VERSION
				) }
			</span>
		</div>
	);
}
