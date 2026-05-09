/**
 * Dashboard header — single row: Customify brand (logo + name) on the left,
 * tab nav centered, version pill + tier pill on the right. Layout ported
 * from .bsify-app-header in samples/Dashboard.html.
 */

import { __ } from '@wordpress/i18n';

import { Tabs, Pill } from '../ui';
import { PLUGIN_VERSION, PRO_ACTIVE, assetUrl } from './config';

const LOGO_URL = assetUrl( 'build/images/admin/customify_logo@2x.png' );

export default function AppHeader( { tabs, activeTab, onTabChange } ) {
	return (
		<div className="pm-app-header">
			<div className="pm-app-header__brand">
				<div className="pm-app-header__logo">
					<img
						src={ LOGO_URL }
						alt={ __( 'Customify', 'customify' ) }
					/>
				</div>
			</div>

			<Tabs
				items={ tabs }
				active={ activeTab }
				onChange={ onTabChange }
				ariaLabel={ __( 'Dashboard sections', 'customify' ) }
			/>

			<div className="pm-app-header__version">
				<Pill>v{ PLUGIN_VERSION }</Pill>
				{ PRO_ACTIVE ? (
					<Pill variant="pro">{ __( 'Pro', 'customify' ) }</Pill>
				) : (
					<Pill variant="free">{ __( 'Free', 'customify' ) }</Pill>
				) }
			</div>
		</div>
	);
}
