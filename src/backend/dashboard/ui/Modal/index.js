/**
 * Generic Modal — sticky header + scrollable body + sticky footer. Renders
 * via portal to document.body so it escapes WP admin layout layers.
 *
 * Usage:
 *   <Modal isOpen={open} onClose={close} size="md" ariaLabel="Settings">
 *     <Modal.Header title="Settings" onClose={close} />
 *     <Modal.Body>{...}</Modal.Body>
 *     <Modal.Footer align="end">
 *       <Button onClick={close}>Cancel</Button>
 *       <Button variant="primary" onClick={save}>Save</Button>
 *     </Modal.Footer>
 *   </Modal>
 *
 * Built-in behaviors: body scroll lock, focus trap, ESC/overlay close,
 * scroll-shadow on header/footer when body overflows.
 */

import { useRef, useEffect, useState } from '@wordpress/element';
import { createPortal } from 'react-dom';
import { __ } from '@wordpress/i18n';

import Icon from '../Icon';
import useBodyLock from './useBodyLock';
import useEscapeKey from './useEscapeKey';
import useFocusTrap from './useFocusTrap';

function Modal( {
	isOpen,
	onClose,
	size = 'md',
	closeOnOverlayClick = true,
	closeOnEsc = true,
	ariaLabel,
	ariaLabelledBy,
	className,
	children,
} ) {
	const modalRef = useRef( null );

	useBodyLock( isOpen );
	useEscapeKey( isOpen && closeOnEsc, onClose );
	useFocusTrap( modalRef, isOpen );

	if ( ! isOpen ) {
		return null;
	}

	const cls = [ 'pm-modal', `pm-modal--${ size }` ];
	if ( className ) {
		cls.push( className );
	}

	const onOverlayClick = ( e ) => {
		if ( closeOnOverlayClick && e.target === e.currentTarget ) {
			onClose();
		}
	};

	return createPortal(
		<div
			className="pm-modal-overlay"
			onClick={ onOverlayClick }
			role="presentation"
		>
			<div
				ref={ modalRef }
				className={ cls.join( ' ' ) }
				role="dialog"
				aria-modal="true"
				aria-label={ ariaLabel }
				aria-labelledby={ ariaLabelledBy }
				tabIndex={ -1 }
			>
				{ children }
			</div>
		</div>,
		document.body
	);
}

function ModalHeader( { title, subtitle, onClose, children, className } ) {
	const cls = [ 'pm-modal__header' ];
	if ( className ) {
		cls.push( className );
	}
	return (
		<div className={ cls.join( ' ' ) }>
			{ children || (
				<>
					{ title && (
						<h2 className="pm-modal__title">{ title }</h2>
					) }
					{ subtitle && (
						<p className="pm-modal__subtitle">{ subtitle }</p>
					) }
				</>
			) }
			{ onClose && (
				<button
					type="button"
					className="pm-modal__close"
					onClick={ onClose }
					aria-label={ __( 'Close', 'customify' ) }
				>
					<Icon name="close" size={ 20 } />
				</button>
			) }
		</div>
	);
}

function ModalBody( { children, className } ) {
	const cls = [ 'pm-modal__body' ];
	if ( className ) {
		cls.push( className );
	}
	const ref = useRef( null );
	const [ scrolled, setScrolled ] = useState( false );

	useEffect( () => {
		const node = ref.current;
		if ( ! node ) {
			return undefined;
		}
		const onScroll = () => setScrolled( node.scrollTop > 0 );
		node.addEventListener( 'scroll', onScroll );
		onScroll();
		return () => node.removeEventListener( 'scroll', onScroll );
	}, [] );

	if ( scrolled ) {
		cls.push( 'pm-modal__body--scrolled' );
	}

	return (
		<div ref={ ref } className={ cls.join( ' ' ) }>
			{ children }
		</div>
	);
}

function ModalFooter( { children, align = 'end', className } ) {
	const cls = [ 'pm-modal__footer', `pm-modal__footer--${ align }` ];
	if ( className ) {
		cls.push( className );
	}
	return <div className={ cls.join( ' ' ) }>{ children }</div>;
}

Modal.Header = ModalHeader;
Modal.Body = ModalBody;
Modal.Footer = ModalFooter;

export default Modal;
