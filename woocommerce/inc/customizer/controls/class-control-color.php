<?php
class Customify_Customizer_Control_Color extends Customify_Customizer_Control_Base {
	static function field_template() {
		?>
		<script type="text/html" id="tmpl-field-customify-color">
		<?php
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<div class="customify-input-color" data-default="{{ field.default }}">
				<input type="hidden" class="customify-input customify-input--color" data-name="{{ field.name }}" value="{{ field.value }}">
				<input type="text" class="customify--color-panel" placeholder="{{ field.placeholder }}" data-alpha="true" value="{{ field.value }}">
			</div>
		</div>
		<?php
		self::after_field();
		?>
		</script>
		<?php
	}
}
