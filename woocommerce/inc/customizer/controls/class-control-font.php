<?php
class Customify_Customizer_Control_Font extends Customify_Customizer_Control_Base {
	static function field_template() {
		?>
		<script type="text/html" id="tmpl-field-customify-css-ruler">
		<?php
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<input type="hidden" class="customify--font-type" data-name="{{ field.name }}-type" >
			<div class="customify--font-families-wrapper">
				<select class="customify--font-families" data-value="{{ JSON.stringify( field.value ) }}" data-name="{{ field.name }}-font"></select>
			</div>
			<div class="customify--font-variants-wrapper">
				<label><?php _e( 'Variants', 'customify' ); ?></label>
				<select class="customify--font-variants" data-name="{{ field.name }}-variant"></select>
			</div>
			<div class="customify--font-subsets-wrapper">
				<label><?php _e( 'Languages', 'customify' ); ?></label>
				<div data-name="{{ field.name }}-subsets" class="list-subsets">
				</div>
			</div>
		</div>
		<?php
		self::after_field();
		?>
		</script>
		<?php
	}
}
