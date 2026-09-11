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
			<div class="customify--icon-picker">
				<div class="customify--icon-preview">
					<input type="hidden" class="customify-input customify--input-icon-type" data-name="{{ field.name }}-type" value="{{ field.value.type }}">
					<input type="hidden" class="customify-input customify--input-icon-svg" data-name="{{ field.name }}-svg" value="{{ field.value.svg }}">
					<div class="customify--icon-preview-icon customify--pick-icon">
						<# if ( 'custom-svg' === field.value.type && field.value.svg ) {  #>
							{{{ field.value.svg }}}
						<# } else if ( field.value.icon ) {  #>
							<i class="{{ field.value.icon }}"></i>
						<# }  #>
					</div>
				</div>
				<input type="text" readonly class="customify-input customify--pick-icon customify--input-icon-name" placeholder="<?php esc_attr_e( 'Pick an icon', 'customify' ); ?>" data-name="{{ field.name }}" value="{{ 'custom-svg' === field.value.type ? '<?php echo esc_js( __( 'Custom SVG', 'customify' ) ); ?>' : ( field.value.icon || '' ) }}">
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
