<?php
class Customify_Customizer_Control_Media extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-media">';
		self::before_field();
		?>
		<#
		if ( ! _.isObject(field.value) ) {
			field.value = {};
		}
		var url = field.value.url;
		#>
		<?php echo self::field_header(); ?>
		<div class="customify-field-settings-inner customify-media-type-{{ field.type }}">
			<div class="customify--media">
				<input type="hidden" class="attachment-id" value="{{ field.value.id }}" data-name="{{ field.name }}">
				<input type="hidden" class="attachment-url"  value="{{ field.value.url }}" data-name="{{ field.name }}-url">
				<input type="hidden" class="attachment-mime"  value="{{ field.value.mime }}" data-name="{{ field.name }}-mime">
				<div class="customify-image-preview <# if ( url ) { #> customify--has-file <# } #>" data-no-file-text="<?php esc_attr_e( 'No file selected', 'customify' ); ?>">
					<#

					if ( url ) {
						if ( url.indexOf('http://') > -1 || url.indexOf('https://') ){

						} else {
							url = Customify_Control_Args.home_url + url;
						}

						if ( ! field.value.mime || field.value.mime.indexOf('image/') > -1 ) {
							#>
							<img src="{{ url }}" alt="">
						<# } else if ( field.value.mime.indexOf('video/' ) > -1 ) { #>
							<video width="100%" height="" controls><source src="{{ url }}" type="{{ field.value.mime }}">Your browser does not support the video tag.</video>
						<# } else {
						var basename = url.replace(/^.*[\\\/]/, '');
						#>
							<a href="{{ url }}" class="attachment-file" target="_blank">{{ basename }}</a>
						<# }
					}
					#>
				</div>
				<button type="button" class="button customify--add <# if ( url ) { #> customify--hide <# } #>"><?php _e( 'Add', 'customify' ); ?></button>
				<button type="button" class="button customify--change <# if ( ! url ) { #> customify--hide <# } #>"><?php _e( 'Change', 'customify' ); ?></button>
				<button type="button" class="button customify--remove <# if ( ! url ) { #> customify--hide <# } #>"><?php _e( 'Remove', 'customify' ); ?></button>
			</div>
		</div>

		<?php
		self::after_field();
		echo '</script>';
	}
}
