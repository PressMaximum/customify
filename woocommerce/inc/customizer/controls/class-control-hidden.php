<?php
class Customify_Customizer_Control_Hidden extends Customify_Customizer_Control_Base {
	static function field_template() {
		?>
		<script type="text/html" id="tmpl-field-customify-hidden">
		<?php
		self::before_field();
		?>
		<input type="hidden" class="customify-input customify-only" data-name="{{ field.name }}" value="{{ field.value }}">
		<?php
		self::after_field();
		?>
		</script>
		<?php
	}
}
