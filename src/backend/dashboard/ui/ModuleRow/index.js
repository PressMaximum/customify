/**
 * Module row — used in 2-column grids inside cards (Pro modules listing).
 *
 *   <ModuleRow title description statusPill? trailing />
 *
 * The grid + nth-child borders are owned by the parent <ModuleList>; the
 * row itself just lays out toggle? + body + trailing slot.
 */

export default function ModuleRow( {
	title,
	description,
	statusPill,
	trailing,
	leading,
	className,
} ) {
	const cls = [ 'pm-module-row' ];
	if ( className ) {
		cls.push( className );
	}
	return (
		<div className={ cls.join( ' ' ) }>
			{ leading && (
				<div className="pm-module-row__leading">{ leading }</div>
			) }
			<div className="pm-module-row__body">
				<div className="pm-module-row__title-row">
					<h4>{ title }</h4>
					{ statusPill && (
						<span className="pm-module-row__status">
							{ statusPill }
						</span>
					) }
				</div>
				<p className="description">{ description }</p>
			</div>
			{ trailing && (
				<div className="pm-module-row__trailing">{ trailing }</div>
			) }
		</div>
	);
}

export function ModuleList( { children, className } ) {
	const cls = [ 'pm-module-list' ];
	if ( className ) {
		cls.push( className );
	}
	return <div className={ cls.join( ' ' ) }>{ children }</div>;
}
