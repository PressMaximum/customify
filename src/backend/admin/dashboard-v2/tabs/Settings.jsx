/**
 * Settings tab — P2 stub. Real form lands in the next phase.
 */

import { __ } from '@wordpress/i18n';
import { Card, CardBody } from '@wordpress/components';

export default function Settings() {
	return (
		<Card>
			<CardBody>
				<p>{ __( 'Settings UI lands in the next phase.', 'customify' ) }</p>
			</CardBody>
		</Card>
	);
}
