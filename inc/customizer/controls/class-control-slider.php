<?php
class Customify_Customizer_Control_Slider extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-slider">';
		self::before_field();
		?>
		<#
		if ( ! _.isObject( field.value ) ) {
			field.value = { unit: 'px' };
		}
		var uniqueID = field.name + ( new Date().getTime() );

		if ( ! field.device_settings ) {
			if ( ! _.isObject( field.default  ) ) {
				field.default = {
					unit: 'px',
					value: field.default
				}
			}
			if ( _.isUndefined( field.value.value ) || ! field.value.value ) {
				field.value.value = field.default.value;
			}

		} else {
			_.each( field.default, function( value, device ){
				if ( ! _.isObject( value  ) ) {
					value = {
						unit: 'px',
						value: value
					}
				}
				field.default[device] = value;
			} );

			try {
				if ( ! _.isUndefined( field.default[field._current_device] ) ) {
					if ( field._current_device ) {
						field.default = field.default[field._current_device];
					}
				}
			} catch ( e ) {

			}
		}

		// Display-only placeholder (e.g. the effective CSS default of a
		// typography sub-field). May be a per-device map for device-scoped
		// fields. The input itself stays empty, so nothing is saved and no
		// CSS is emitted until the user actually picks a value.
		var _ph = field.placeholder || '';
		if ( _.isObject( _ph ) ) {
			_ph = _ph[ field._current_device || 'desktop' ] || '';
		}

		// Multi-unit mode: `field.units` maps unit => {min, max, step}
		// (see get_typo_fields()). The STORED shape is unchanged
		// ({value, unit}); only the UI gains a unit select. The active
		// unit picks the slider range. Saved-data safety: a saved unit
		// that is NOT in the map (legacy/filtered data) still renders as
		// its own selected option so the value round-trips losslessly —
		// it is never silently rewritten to another unit.
		var _units = ( _.isObject( field.units ) && ! _.isEmpty( field.units ) ) ? field.units : null;
		var _activeUnit = field.value.unit || 'px';
		var _noValue = _.isUndefined( field.value.value ) || null === field.value.value || '' === field.value.value;

		// No saved value yet → align the initial unit with the display
		// default's unit (placeholder "2.42em" → em, bare "1.216" → the
		// unitless '-') so the first drag starts from the documented
		// starting point. A SAVED value always keeps its stored unit —
		// this branch never runs when value is non-empty.
		if ( _units && _noValue && _ph ) {
			var _phMatch = String( _ph ).match( /^-?[0-9.]+\s*([a-z%]+)?$/i );
			if ( _phMatch ) {
				var _phUnit = _phMatch[1] ? _phMatch[1].toLowerCase() : '-';
				if ( ! _.isUndefined( _units[ _phUnit ] ) ) {
					_activeUnit = _phUnit;
				}
			}
		}

		if ( _units && ! _.isUndefined( _units[ _activeUnit ] ) ) {
			field.min  = _units[ _activeUnit ].min;
			field.max  = _units[ _activeUnit ].max;
			field.step = _units[ _activeUnit ].step;
		}
		#>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<div class="customify-input-slider-wrapper<# if ( _units ) { #> customify--has-units<# } #>">
				<div class="customify--css-unit">
					<# if ( _units ) { #>
					<select class="customify--unit-select customify-input change-by-js" data-name="{{ field.name }}-unit" title="<?php esc_attr_e( 'Unit', 'customify' ); ?>">
						<# _.each( _units, function( _range, _unit ) { #>
						<option value="{{ _unit }}" <# if ( _unit === _activeUnit ){ #> selected="selected" <# } #>>{{ '-' === _unit ? '–' : _unit }}</option>
						<# }); #>
						<# if ( _.isUndefined( _units[ _activeUnit ] ) ) { #>
						<option value="{{ _activeUnit }}" selected="selected">{{ '-' === _activeUnit ? '–' : _activeUnit }}</option>
						<# } #>
					</select>
					<# } else { #>
					<label class="<# if ( field.value.unit == 'px' || ! field.value.unit ){ #> customify--label-active <# } #>">
						<# if ( field.unit ) { #>
							{{ field.unit }}
						<#  } else {  #>
						<?php _e( 'px', 'customify' ); ?>
						<#  } #>
						<input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == 'px' || ! field.value.unit ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="px">
					</label>
					<# } #>
					<a href="#" class="reset" title="<?php esc_attr_e( 'Reset', 'customify' ); ?>"></a>
				</div>
				<div data-min="{{ field.min }}" data-default="{{ JSON.stringify( field.default ) }}" data-step="{{ field.step }}" data-max="{{ field.max }}" <# if ( _units ) { #>data-units="{{ JSON.stringify( field.units ) }}"<# } #> class="customify-input-slider"></div>
				<input type="number" min="{{ field.min }}" step="{{ field.step }}" max="{{ field.max }}" class="customify--slider-input customify-input" data-name="{{ field.name }}-value" value="{{ field.value.value }}" placeholder="{{ _ph }}" size="4">
			</div>
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
