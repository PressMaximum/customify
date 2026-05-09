/**
 * Checklist row — circle check + body (h4 + description) + actions slot.
 * State (checked/unchecked) is owned by the parent so the same row renders
 * in both states without re-mounting.
 */

import Icon from '../Icon';

export default function ChecklistRow( {
	checked,
	onToggleCheck,
	title,
	description,
	actions,
} ) {
	const cls = [ 'pm-qstart' ];
	if ( checked ) {
		cls.push( 'pm-qstart--checked' );
	}
	return (
		<div className={ cls.join( ' ' ) }>
			<button
				type="button"
				className="pm-qstart__check"
				onClick={ onToggleCheck }
				aria-pressed={ checked }
				aria-label={ title }
			>
				<Icon name="check" size={ 12 } />
			</button>
			<div className="pm-qstart__body">
				<h4 className="pm-qstart__title">{ title }</h4>
				<p className="pm-qstart__desc description">
					{ description }
				</p>
			</div>
			{ actions && (
				<div className="pm-qstart__actions">{ actions }</div>
			) }
		</div>
	);
}
