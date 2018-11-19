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
					<div class="customify--icon-preview-icon customify--pick-icon">
						<# if ( field.value.icon ) {  #>
							<i class="{{ field.value.icon }}"></i>
						<# }  #>
					</div>
				</div>
				<input type="text" readonly class="customify-input customify--pick-icon customify--input-icon-name" placeholder="<?php esc_attr_e( 'Pick an icon', 'customify' ); ?>" data-name="{{ field.name }}" value="{{ field.value.icon }}">
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
		</div>
		<?php
	}
}
