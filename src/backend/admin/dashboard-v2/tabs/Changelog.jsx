/**
 * Changelog tab — P2 stub. Real rendering lands in the next phase.
 */

import { __ } from '@wordpress/i18n';
import { Card, CardBody } from '@wordpress/components';

export default function Changelog() {
	return (
		<Card>
			<CardBody>
				<p>{ __( 'Changelog lands in the next phase.', 'customify' ) }</p>
			</CardBody>
		</Card>
	);
}
