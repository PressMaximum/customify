<?php
class Customify_Customizer_Control_Heading extends Customify_Customizer_Control_Base {
	static function field_template() {
		?>
		<script type="text/html" id="tmpl-field-customify-heading">
		<?php
		self::before_field();
		?>
		<h3 class="customify-field--heading">{{ field.label }}</h3>
		<?php
		self::after_field();
		?>
		</script>
		<?php
	}
}
