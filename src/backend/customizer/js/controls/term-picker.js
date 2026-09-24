/**
 * Term picker control - Select2 multi-select fed by admin-ajax.
 *
 * Markup: inc/customizer/controls/class-control-term-picker.php
 * Endpoint: customify_term_picker_ajax_search() in inc/customizer/term-picker.php
 *
 * The select carries `.customify-input` + `data-name`, so the generic
 * customifyField.getFieldValue() reads its value (an array of term IDs as
 * strings) and the control saves it like any other field. This module only
 * adds the search UI and keeps the picker in step with its taxonomy setting.
 */

const $ = window.jQuery;

// Taxonomy setting ids already bound, so a control repaint does not stack
// a second listener on the same setting.
const boundSettings = {};

/**
 * Read a select setting value written by the Customify controls, which
 * store `encodeURI( JSON.stringify( value ) )`, or a raw PHP value.
 *
 * @param {*} raw Setting value.
 * @return {string} Decoded string.
 */
function decodeSettingValue( raw ) {
	if ( typeof raw !== 'string' ) {
		return raw ? String( raw ) : '';
	}

	try {
		const decoded = JSON.parse( decodeURI( raw ) );
		return typeof decoded === 'string' ? decoded : raw;
	} catch {
		return raw;
	}
}

/**
 * Taxonomy the picker should query right now.
 *
 * @param {Object} $select Picker select (jQuery).
 * @return {string} Taxonomy slug.
 */
function currentTaxonomy( $select ) {
	const settingId = $select.attr( 'data-taxonomy-setting' );
	const api = window.wp && window.wp.customize;

	if ( settingId && api && api.has( settingId ) ) {
		const value = decodeSettingValue( api( settingId ).get() );

		if ( value ) {
			return value;
		}
	}

	return $select.attr( 'data-taxonomy' ) || '';
}

/**
 * Clear every picker bound to a taxonomy setting when that setting changes.
 * The picker is looked up at change time: a repaint replaces the DOM node.
 *
 * @param {string} settingId Taxonomy setting id.
 * @param {string} fieldName Picker field name (`data-name`).
 */
function bindTaxonomySetting( settingId, fieldName ) {
	const api = window.wp && window.wp.customize;
	const key = settingId + '|' + fieldName;

	if ( ! settingId || ! api || boundSettings[ key ] ) {
		return;
	}

	boundSettings[ key ] = true;

	api( settingId, function ( setting ) {
		let last = decodeSettingValue( setting.get() );

		setting.bind( function ( value ) {
			const next = decodeSettingValue( value );

			if ( next === last ) {
				return;
			}

			last = next;

			$( 'select.customify-term-picker' )
				.filter( function () {
					return $( this ).attr( 'data-name' ) === fieldName;
				} )
				.each( function () {
					// The selected IDs belong to the previous taxonomy.
					$( this ).find( 'option' ).remove();
					$( this ).trigger( 'change' );
				} );
		} );
	} );
}

/**
 * Enhance the picker rendered into a field area.
 *
 * @param {Object} $fieldsArea Field container (jQuery).
 */
export function initTermPicker( $fieldsArea ) {
	if ( ! $fieldsArea || ! $fieldsArea.length ) {
		return;
	}

	const $select = $fieldsArea.find( 'select.customify-term-picker' ).first();

	if ( ! $select.length || typeof $.fn.select2 !== 'function' ) {
		return;
	}

	if ( $select.hasClass( 'select2-hidden-accessible' ) ) {
		$select.select2( 'destroy' );
	}

	const $wrap = $select.closest( '.customify-term-picker-wrap' );

	$select.select2( {
		width: '100%',
		multiple: true,
		placeholder: $select.attr( 'data-placeholder' ) || '',
		// Body-level popup (Select2's default): the Customizer pane clips
		// and scrolls, and a popup inside it lands on top of the chips.
		dropdownCssClass: 'customify-term-picker-dropdown',
		minimumInputLength: 0,
		language: {
			noResults: () => $select.attr( 'data-no-results' ) || '',
			searching: () => $select.attr( 'data-searching' ) || '',
			errorLoading: () => $select.attr( 'data-error' ) || '',
		},
		ajax: {
			url: $select.attr( 'data-ajax-url' ),
			type: 'POST',
			dataType: 'json',
			delay: 250,
			data( params ) {
				return {
					action: 'customify_term_picker_search',
					nonce: $select.attr( 'data-nonce' ),
					taxonomy: currentTaxonomy( $select ),
					q: params.term || '',
					page: params.page || 1,
				};
			},
			processResults( response ) {
				if ( response && response.success && response.data ) {
					return response.data;
				}

				return { results: [] };
			},
		},
	} );

	// Typing in Select2's inline search box must not reach the control's
	// delegated `keyup` handler: it would re-save the unchanged value on
	// every keystroke. The select's own `change` still bubbles.
	$wrap.on( 'keyup input', '.select2-search__field', function ( event ) {
		event.stopPropagation();
	} );

	// Removing a chip should not pop the result list open.
	// Select2 opens synchronously right after the unselect, so the guard only
	// lives for this tick and never blocks a later, deliberate open.
	$select.on( 'select2:unselect', function () {
		const block = ( event ) => event.preventDefault();

		$select.one( 'select2:opening', block );
		window.setTimeout( () => $select.off( 'select2:opening', block ), 0 );
	} );

	// Keep the picked order: Select2 re-sorts to <option> order, so move a
	// newly picked option to the end.
	$select.on( 'select2:select', function ( event ) {
		const id =
			event.params && event.params.data ? event.params.data.id : '';

		if ( ! id ) {
			return;
		}

		const $option = $select.find( 'option' ).filter( function () {
			return $( this ).val() === String( id );
		} );

		if ( $option.length ) {
			$option.detach().appendTo( $select );
			$select.trigger( 'change' );
		}
	} );

	bindTaxonomySetting(
		$select.attr( 'data-taxonomy-setting' ),
		$select.attr( 'data-name' )
	);
}
