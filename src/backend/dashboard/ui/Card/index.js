/**
 * Card — bordered, rounded white surface. Optional header with title slot
 * (h3) + trailing slot (link / dropdown / pill). Body wraps children.
 *
 * Both header slots are optional. Pass nothing to get a bare card; pass a
 * string for a plain title or a node for full custom header content.
 */

export default function Card( {
	title,
	headerRight,
	className,
	children,
	bodyPadding = false,
} ) {
	const cls = [ 'pm-card' ];
	if ( className ) {
		cls.push( className );
	}
	const bodyCls = [ 'pm-card__body' ];
	if ( bodyPadding ) {
		bodyCls.push( 'pm-card__body--padded' );
	}
	const hasHeader = title || headerRight;
	return (
		<div className={ cls.join( ' ' ) }>
			{ hasHeader && (
				<div className="pm-card__header">
					{ title && (
						<h3 className="pm-card__title">{ title }</h3>
					) }
					{ headerRight && (
						<div className="pm-card__header-right">
							{ headerRight }
						</div>
					) }
				</div>
			) }
			<div className={ bodyCls.join( ' ' ) }>{ children }</div>
		</div>
	);
}
