/**
 * 3-dot dropdown menu (controlled). Items: [{ label, icon?, onClick }].
 * Renders trigger + popout menu. Click-outside + ESC close handled here.
 */

import { useRef, useState, useEffect } from '@wordpress/element';

import Icon from '../Icon';
import useEscapeKey from '../Modal/useEscapeKey';

export default function Dropdown( {
	items,
	align = 'right',
	triggerIcon = 'dots-vertical',
	triggerLabel,
	className,
} ) {
	const [ open, setOpen ] = useState( false );
	const wrapRef = useRef( null );

	useEscapeKey( open, () => setOpen( false ) );

	useEffect( () => {
		if ( ! open ) {
			return undefined;
		}
		const onClick = ( e ) => {
			if ( wrapRef.current && ! wrapRef.current.contains( e.target ) ) {
				setOpen( false );
			}
		};
		document.addEventListener( 'mousedown', onClick );
		return () => document.removeEventListener( 'mousedown', onClick );
	}, [ open ] );

	const cls = [ 'pm-dropdown' ];
	if ( open ) {
		cls.push( 'pm-dropdown--open' );
	}
	if ( className ) {
		cls.push( className );
	}

	return (
		<div ref={ wrapRef } className={ cls.join( ' ' ) }>
			<button
				type="button"
				className="pm-dropdown__trigger"
				aria-haspopup="menu"
				aria-expanded={ open }
				aria-label={ triggerLabel }
				onClick={ () => setOpen( ! open ) }
			>
				<Icon name={ triggerIcon } size={ 16 } />
			</button>
			{ open && (
				<div
					className={ `pm-dropdown__menu pm-dropdown__menu--${ align }` }
					role="menu"
				>
					{ items.map( ( item, i ) => (
						<button
							key={ i }
							type="button"
							role="menuitem"
							className="pm-dropdown__item"
							onClick={ () => {
								item.onClick();
								setOpen( false );
							} }
						>
							{ item.icon && (
								<Icon name={ item.icon } size={ 14 } />
							) }
							{ item.label }
						</button>
					) ) }
				</div>
			) }
		</div>
	);
}
