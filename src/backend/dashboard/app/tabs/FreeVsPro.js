/**
 * Free vs Pro tab — single Card containing the CompareTable with sections
 * + rows + bottom CTA. Data from app/data/compare-matrix.js.
 */

import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Card, CompareTable, Button } from '../../ui';
import { COMPARE_SECTIONS, COMPARE_CTA } from '../data/compare-matrix';

export default function FreeVsPro() {
	return (
		<Card title={ __( 'Compare Free vs Pro', 'customify' ) }>
			<CompareTable>
				{ COMPARE_SECTIONS.map( ( section, si ) => (
					<Fragment key={ si }>
						<CompareTable.Section
							title={ section.title }
							colLabels={ [
								__( 'Free', 'customify' ),
								__( 'Pro', 'customify' ),
							] }
						/>
						{ section.rows.map( ( row, ri ) => (
							<CompareTable.Row
								key={ ri }
								name={ row.name }
								detail={ row.detail }
								cells={ row.cells }
							/>
						) ) }
					</Fragment>
				) ) }
				<CompareTable.CTA
					title={ COMPARE_CTA.title }
					description={ COMPARE_CTA.description }
					action={
						<Button
							variant="primary"
							href={ COMPARE_CTA.ctaHref }
							className={'pm-button--lg'}
						>
							{ COMPARE_CTA.ctaLabel }
						</Button>
					}
				/>
			</CompareTable>
		</Card>
	);
}
