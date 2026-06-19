<?php
class Customify_Customizer_Control_Modal extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-modal">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-actions">
			<a href="#" title="<?php esc_attr_e( 'Reset to default', 'customify' ); ?>" class="action--reset" data-control="{{ field.name }}"><span class="dashicons dashicons-image-rotate"></span></a>
			<# if ( ! field.popover_chrome ) { #>
			<a href="#" title="<?php esc_attr_e( 'Toggle edit panel', 'customify' ); ?>" class="action--edit" data-control="{{ field.name }}"><span class="dashicons dashicons-edit"></span></a>
			<# } #>
		</div>
		<?php
		// Modal controls holding style values may opt into the styling
		// trigger + popover chrome via `'popover_chrome' => true` — they
		// render per-tab trigger rows instead of the pencil. Rows are
		// built and painted by ensureStylingRows()/paintStylingRows() in
		// control.js. Data-only modals keep the pencil + accordion above.
		?>
		<# if ( field.popover_chrome ) { #>
		<div class="customify-styling-triggers" data-control="{{ field.name }}"></div>
		<# } #>
		<div class="customify-field-settings-inner">
			<input type="hidden" class="customify-hidden-modal-input customify-only" data-name="{{ field.name }}" value="{{ JSON.stringify( field.value ) }}" data-default="{{ JSON.stringify( field.default ) }}">
		</div>
		<?php
		self::after_field();
		echo '</script>';
		?>
		<script type="text/html" id="tmpl-customify-modal-settings">
			<div class="customify-modal-settings">
				<div class="customify-modal-settings--inner">
					<div class="customify-modal-settings--fields"></div>
				</div>
			</div>
		</script>
		<?php
	}
}
