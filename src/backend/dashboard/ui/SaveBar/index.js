/**
 * Sticky save bar inside a settings Card — status on the left, action
 * buttons on the right. Status states: idle | saving | saved | error.
 */

import { __ } from '@wordpress/i18n';

import Icon from '../Icon';
import Button from '../Button';

const STATUS_LABEL = {
	idle: ( dirty ) =>
		dirty
			? __( 'Unsaved changes', 'customify' )
			: __( 'All changes saved', 'customify' ),
	saving: () => __( 'Saving…', 'customify' ),
	saved: () => __( 'All changes saved', 'customify' ),
	error: () => __( 'Save failed — try again', 'customify' ),
};

export default function SaveBar( {
	dirty,
	status = 'idle',
	onReset,
	onSave,
	saveLabel,
	resetLabel,
} ) {
	const cls = [ 'pm-save-bar' ];
	if ( status === 'error' ) {
		cls.push( 'pm-save-bar--error' );
	}
	const statusText = STATUS_LABEL[ status ]( dirty );
	return (
		<div className={ cls.join( ' ' ) }>
			<span className="pm-save-bar__status">
				<Icon name="clock" size={ 14 } />
				{ statusText }
			</span>
			<Button
				variant="secondary"
				onClick={ onReset }
				disabled={ status === 'saving' }
			>
				{ resetLabel || __( 'Reset to defaults', 'customify' ) }
			</Button>
			<Button
				variant="primary"
				onClick={ onSave }
				disabled={ ! dirty || status === 'saving' }
			>
				{ saveLabel || __( 'Save changes', 'customify' ) }
			</Button>
		</div>
	);
}
