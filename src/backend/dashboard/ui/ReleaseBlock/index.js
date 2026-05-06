/**
 * Release block — version row + change list. Used inside the Changelog
 * card (multiple releases stacked vertically).
 *
 *   <ReleaseBlock version="0.4.13" date="May 2, 2026" current changes={[
 *     { tag: 'updated', text: 'WooCommerce template files.' },
 *     { tag: 'fixed',   text: <>Fix <code>foo()</code> bug.</> },
 *   ]} />
 *
 * Recognized tags (port of sample): new | added | updated | improved | fixed
 * | changed | breaking. Add new tag colors in style.css.
 */

import { __ } from '@wordpress/i18n';

export default function ReleaseBlock( { version, date, current, changes } ) {
	return (
		<div className="pm-release">
			<div className="pm-release__version">
				v{ version }
				{ current && (
					<span className="pm-release__current">
						{ __( 'Current', 'customify' ) }
					</span>
				) }
				<span className="pm-release__date">{ date }</span>
			</div>
			<ul className="pm-change-list">
				{ changes.map( ( change, i ) => (
					<li key={ i }>
						<span
							className={ `pm-change-tag pm-change-tag--${ change.tag }` }
						>
							{ change.tag }
						</span>
						<span className="pm-change-list__text">
							{ change.text }
						</span>
					</li>
				) ) }
			</ul>
		</div>
	);
}
