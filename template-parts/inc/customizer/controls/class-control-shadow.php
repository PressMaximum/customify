<?php
class Customify_Customizer_Control_Shadow extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-shadow">';
		self::before_field();
		?>
		<#
			if ( ! _.isObject( field.value ) ) {
			field.value = { };
			}

			var uniqueID = field.name + ( new Date().getTime() );
		#>
			<?php echo self::field_header(); ?>
			<div class="customify-field-settings-inner">

				<div class="customify-input-color" data-default="{{ field.default }}">
					<input type="hidden" class="customify-input customify-input--color" data-name="{{ field.name }}-color" value="{{ field.value.color }}">
					<input type="text" class="customify--color-panel" data-alpha="true" value="{{ field.value.color }}">
				</div>

				<div class="customify--gr-inputs">
					<span>
						<input type="number" class="customify-input customify-input-css change-by-js"  data-name="{{ field.name }}-x" value="{{ field.value.x }}">
						<span class="customify--small-label"><?php _e( 'X', 'customify' ); ?></span>
					</span>
					<span>
						<input type="number" class="customify-input customify-input-css change-by-js"  data-name="{{ field.name }}-y" value="{{ field.value.y }}">
						<span class="customify--small-label"><?php _e( 'Y', 'customify' ); ?></span>
					</span>
					<span>
						<input type="number" class="customify-input customify-input-css change-by-js" data-name="{{ field.name }}-blur" value="{{ field.value.blur }}">
						<span class="customify--small-label"><?php _e( 'Blur', 'customify' ); ?></span>
					</span>
					<span>
						<input type="number" class="customify-input customify-input-css change-by-js" data-name="{{ field.name }}-spread" value="{{ field.value.spread }}">
						<span class="customify--small-label"><?php _e( 'Spread', 'customify' ); ?></span>
					</span>
					<span>
						<span class="input">
							<input type="checkbox" class="customify-input customify-input-css change-by-js" <# if ( field.value.inset == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-inset" value="{{ field.value.inset }}">
						</span>
						<span class="customify--small-label"><?php _e( 'inset', 'customify' ); ?></span>
					</span>
				</div>
			</div>
			<?php
			self::after_field();
			echo '</script>';
	}
}
