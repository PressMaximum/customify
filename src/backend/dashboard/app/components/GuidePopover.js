/**
 * Multi-page guide popover — composition of <Modal> + a paged hero/body.
 * Driven by a `guideKey` prop; content lives in app/data/guides.js.
 *
 * Keyboard shortcuts (in addition to Modal's ESC):
 *   ←  previous page
 *   →  next page (or finish on last)
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Modal, Button, Icon } from '../../ui';
import { GUIDES } from '../data/guides';

export default function GuidePopover( { guideKey, isOpen, onClose } ) {
	const [ page, setPage ] = useState( 0 );

	// Reset to first page each time the popover opens or guideKey changes.
	useEffect( () => {
		if ( isOpen ) {
			setPage( 0 );
		}
	}, [ isOpen, guideKey ] );

	const pages = guideKey ? GUIDES[ guideKey ] || [] : [];
	const isLast = page === pages.length - 1;
	const current = pages[ page ];

	useEffect( () => {
		if ( ! isOpen ) {
			return undefined;
		}
		const onKey = ( e ) => {
			if ( e.key === 'ArrowRight' ) {
				if ( isLast ) {
					onClose();
				} else {
					setPage( ( p ) => p + 1 );
				}
			} else if ( e.key === 'ArrowLeft' && page > 0 ) {
				setPage( ( p ) => p - 1 );
			}
		};
		document.addEventListener( 'keydown', onKey );
		return () => document.removeEventListener( 'keydown', onKey );
	}, [ isOpen, page, isLast, onClose ] );

	if ( ! current ) {
		return null;
	}

	return (
		<Modal
			isOpen={ isOpen }
			onClose={ onClose }
			size="lg"
			ariaLabel={ current.title }
			closeOnOverlayClick={ true }
		>
			<Modal.Header onClose={ onClose }>
				<div className="pm-guide__hero">
					<Icon name={ current.iconName } size={ 240 } />
				</div>
				<div className="pm-guide__pagination">
					{ pages.map( ( _, i ) => (
						<button
							key={ i }
							type="button"
							className={
								'pm-guide__dot' +
								( i === page ? ' pm-guide__dot--active' : '' )
							}
							onClick={ () => setPage( i ) }
							aria-label={ `Go to page ${ i + 1 }` }
						/>
					) ) }
				</div>
			</Modal.Header>
			<Modal.Body>
				<h3 className="pm-guide__title">{ current.title }</h3>
				<div className="pm-guide__body">{ current.body }</div>
			</Modal.Body>
			<Modal.Footer align="between">
				<Button
					variant="ghost"
					disabled={ page === 0 }
					onClick={ () => setPage( page - 1 ) }
				>
					{ __( 'Previous', 'customify' ) }
				</Button>
				<Button
					variant="primary"
					onClick={ () => ( isLast ? onClose() : setPage( page + 1 ) ) }
				>
					{ isLast
						? __( 'Finish', 'customify' )
						: __( 'Next', 'customify' ) }
				</Button>
			</Modal.Footer>
		</Modal>
	);
}
