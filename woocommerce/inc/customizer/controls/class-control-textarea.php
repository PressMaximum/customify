<?php
class Customify_Customizer_Control_Textarea extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-textarea">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<textarea rows="10" class="customify-input" data-name="{{ field.name }}">{{ field.value }}</textarea>
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
