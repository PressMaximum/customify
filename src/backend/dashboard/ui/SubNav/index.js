/**
 * Vertical sub-navigation — used in Settings left rail. Items: [{ id, label,
 * icon? }]. Controlled: parent owns `active` + `onChange`.
 *
 * `icon` is rendered through @wordpress/components Icon, so callers can
 * pass any element from @wordpress/icons (e.g. `import { brush } from
 * '@wordpress/icons'; { id, label, icon: brush }`). A plain string is also
 * accepted and forwarded to Icon — useful for dashicon names like
 * `admin-generic`.
 *
 * Renders <a href> with hash so middle-click works for sub-routes (caller
 * is responsible for any URL-state sync; the click handler owns state).
 */

import { Icon } from '@wordpress/components';

export default function SubNav( { items, active, onChange, ariaLabel } ) {
	return (
		<nav className="pm-subnav" aria-label={ ariaLabel }>
			{ items.map( ( item ) => {
				const isActive = item.id === active;
				const cls = [ 'pm-subnav__item' ];
				if ( isActive ) {
					cls.push( 'pm-subnav__item--active' );
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
						{ item.icon && (
							<span className="pm-subnav__icon">
								<Icon icon={ item.icon } size={ 18 } />
							</span>
						) }
						{ item.label }
					</a>
				);
			} ) }
		</nav>
	);
}
