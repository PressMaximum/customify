<?php
class Customify_Customizer_Control_Select extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-select">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner">
			<select class="customify-input" data-name="{{ field.name }}">
				<# _.each( field.choices, function( label, key ){  #>
					<option <# if ( field.value == key ){ #> selected="selected" <# } #> value="{{ key }}">{{ label }}</option>
				<# } ); #>
			</select>
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
