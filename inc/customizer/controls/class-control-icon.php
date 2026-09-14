<?php
class Customify_Customizer_Control_Icon extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-icon">';
		self::before_field();
		?>
		<#
		if ( ! _.isObject( field.value ) ) {
			field.value = { };
		}
		#>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<#
			// Suggested preset keys for THIS field, published on the picker
			// element so the sidebar can read them when it opens. Optional:
			// a field with no `presets` arg renders an empty attribute and
			// the sidebar simply omits the Suggested row.
			var customifyIconPresets = _.isArray( field.presets ) ? field.presets : [];
			// Preset markup comes from the same PHP-generated map the picker
			// grid uses, so the chip previews exactly what the site renders.
			var customifyIconLib = ( 'undefined' !== typeof Customify_Control_Args && Customify_Control_Args.svg_icons )
				? Customify_Control_Args.svg_icons
				: {};
			var customifyIconPreset = ( 'svg' === field.value.type && field.value.icon && customifyIconLib[ field.value.icon ] )
				? customifyIconLib[ field.value.icon ].svg
				: '';
			#>
			<div class="customify--icon-picker" data-presets="{{ customifyIconPresets.join( ',' ) }}">
				<div class="customify--icon-preview">
					<input type="hidden" class="customify-input customify--input-icon-type" data-name="{{ field.name }}-type" value="{{ field.value.type }}">
					<input type="hidden" class="customify-input customify--input-icon-svg" data-name="{{ field.name }}-svg" value="{{ field.value.svg }}">
					<div class="customify--icon-preview-icon customify--pick-icon">
						<# if ( 'custom-svg' === field.value.type && field.value.svg ) {  #>
							{{{ field.value.svg }}}
						<# } else if ( customifyIconPreset ) {  #>
							{{{ customifyIconPreset }}}
						<# } else if ( 'svg' !== field.value.type && field.value.icon ) {  #>
							<i class="{{ field.value.icon }}"></i>
						<# }  #>
					</div>
				</div>
				<#
				// CAREFUL: this read-only input IS the storage for `icon`
				// (see getFieldValue's `case "icon"` — it reads
				// `input[data-name="<field>"]`). It must therefore hold the
				// raw stored value: the CSS class for a font icon, the
				// LIBRARY KEY for a preset SVG. Only `custom-svg`, whose
				// `icon` is a pure label, may show prose here.
				var customifyIconName = ( 'custom-svg' === field.value.type )
					? '<?php echo esc_js( __( 'Custom SVG', 'customify' ) ); ?>'
					: ( field.value.icon || '' );
				#>
				<input type="text" readonly class="customify-input customify--pick-icon customify--input-icon-name" placeholder="<?php esc_attr_e( 'Pick an icon', 'customify' ); ?>" data-name="{{ field.name }}" value="{{ customifyIconName }}">
				<span class="customify--icon-remove" title="<?php esc_attr_e( 'Remove', 'customify' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<span class="screen-reader-text">
					<?php _e( 'Remove', 'customify' ); ?></span>
				</span>
			</div>
		</div>
		<?php
		self::after_field();
		echo '</script>';
		?>
		<div id="customify--sidebar-icons">
			<div class="customify--sidebar-header">
				<a class="customize-controls-icon-close" href="#">
					<span class="screen-reader-text"><?php _e( 'Cancel', 'customify' ); ?></span>
				</a>
				<div class="customify--icon-type-inner">
					<select id="customify--sidebar-icon-type">
						<option value="all"><?php _e( 'All Icon Types', 'customify' ); ?></option>
					</select>
				</div>
			</div>
			<?php
			/*
			 * Per-field shortlist. Populated by JS from the opening field's
			 * `data-presets` (see the `presets` control arg); stays empty and
			 * hidden for every icon field that doesn't declare one, which is
			 * all of them by default.
			 */
			?>
			<div id="customify--icon-suggested" class="customify--icon-suggested" hidden>
				<div class="customify--icon-suggested-label"><?php esc_html_e( 'Suggested', 'customify' ); ?></div>
				<ul class="customify--icon-suggested-list"></ul>
			</div>
			<div class="customify--sidebar-search">
				<input type="text" id="customify--icon-search" placeholder="<?php esc_attr_e( 'Type icon name', 'customify' ); ?>">
			</div>
			<div id="customify--icon-browser"></div>
			<div id="customify--icon-custom-svg" class="customify--icon-custom-svg">
				<label for="customify--icon-custom-svg-input" class="customify--icon-custom-svg-label">
					<?php _e( 'Paste SVG code below', 'customify' ); ?>
				</label>
				<textarea id="customify--icon-custom-svg-input" class="customify--icon-custom-svg-input" rows="8" placeholder="<?php esc_attr_e( '<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 24 24&quot;>...</svg>', 'customify' ); ?>" spellcheck="false"></textarea>
				<div class="customify--icon-custom-svg-preview" aria-hidden="true"></div>
				<div class="customify--icon-custom-svg-actions">
					<button type="button" class="button button-primary customify--icon-custom-svg-apply"><?php _e( 'Apply SVG', 'customify' ); ?></button>
					<button type="button" class="button-link customify--icon-custom-svg-clear"><?php _e( 'Clear', 'customify' ); ?></button>
				</div>
				<p class="customify--icon-custom-svg-help description">
					<?php _e( 'Only &lt;svg&gt; markup is allowed. Any &lt;script&gt; or event handlers are stripped.', 'customify' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
