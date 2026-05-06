/**
 * Horizontal pill tab strip — header navigation.
 *
 * Items: [{ id, label }]. The component is uncontrolled in styling but
 * controlled in state: parent owns `active` + `onChange`. Renders <a> with
 * href so middle-click + open-in-new-tab work for hash routes.
 */

export default function Tabs( { items, active, onChange, ariaLabel } ) {
	return (
		<nav className="pm-tabs" aria-label={ ariaLabel }>
			{ items.map( ( item ) => {
				const isActive = item.id === active;
				const cls = [ 'pm-tabs__item' ];
				if ( isActive ) {
					cls.push( 'pm-tabs__item--active' );
				}
				return (
					<a
						key={ item.id }
						href={ `#${ item.id }` }
						className={ cls.join( ' ' ) }
						aria-current={ isActive ? 'page' : undefined }
						onClick={ ( e ) => {
							e.preventDefault();
							onChange( item.id );
						} }
					>
						{ item.label }
					</a>
				);
			} ) }
		</nav>
	);
}
