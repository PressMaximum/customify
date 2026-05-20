/**
 * Module list grid — 2-col layout with internal hairline borders.
 * Ported from the theme-dashboard branch `pm-module-list` pattern.
 *
 *   <ModuleList>
 *     <ModuleRow leading={ <ToggleSwitch ... /> } title="..." description="..." trailing={ ... } />
 *     ...
 *   </ModuleList>
 *
 * Rows with `has-subs` class span the full width and their sub-module
 * group renders inside `.customify-dashboard-module-list__subs` below.
 */

export function ModuleList( { children, className } ) {
	const classes = [ 'customify-dashboard-module-list' ];
	if ( className ) {
		classes.push( className );
	}
	return <div className={ classes.join( ' ' ) }>{ children }</div>;
}

export function ModuleRow( {
	title,
	description,
	leading,
	trailing,
	hasSubs,
	className,
} ) {
	const classes = [ 'customify-dashboard-module-row' ];
	if ( hasSubs ) {
		classes.push( 'has-subs' );
	}
	if ( className ) {
		classes.push( className );
	}
	return (
		<div className={ classes.join( ' ' ) }>
			{ leading && (
				<div className="customify-dashboard-module-row__leading">
					{ leading }
				</div>
			) }
			<div className="customify-dashboard-module-row__body">
				<h4 className="customify-dashboard-module-row__title">{ title }</h4>
				{ description && (
					<p className="customify-dashboard-module-row__description">
						{ description }
					</p>
				) }
			</div>
			{ trailing && (
				<div className="customify-dashboard-module-row__trailing">
					{ trailing }
				</div>
			) }
		</div>
	);
}

export function ModuleSubmodules( { children } ) {
	return (
		<div className="customify-dashboard-module-list__subs">{ children }</div>
	);
}
