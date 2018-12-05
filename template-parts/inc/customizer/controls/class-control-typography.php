<?php
class Customify_Customizer_Control_Typography extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-typography">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-actions">
			<a href="#" class="action--reset" data-control="{{ field.name }}" title="<?php esc_attr_e( 'Reset to default', 'customify' ); ?>"><span class="dashicons dashicons-image-rotate"></span></a>
			<a href="#" class="action--edit" data-control="{{ field.name }}" title="<?php esc_attr_e( 'Toggle edit panel', 'customify' ); ?>"><span class="dashicons dashicons-edit"></span></a>
		</div>
		<div class="customify-field-settings-inner">
			<input type="hidden" class="customify-typography-input customify-only" data-name="{{ field.name }}" value="{{ JSON.stringify( field.value ) }}" data-default="{{ JSON.stringify( field.default ) }}">
		</div>
		<?php
		self::after_field();
		echo '</script>';
		?>
		<div id="customify-typography-panel" class="customify-typography-panel">
			<div class="customify-typography-panel--inner">
				<input type="hidden" id="customify--font-type">
				<div id="customify-typography-panel--fields"></div>
			</div>
		</div>
		<?php
	}
}
