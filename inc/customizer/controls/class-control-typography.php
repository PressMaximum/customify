<?php
class Customify_Customizer_Control_Typography extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-typography">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-actions">
			<a href="#" class="action--reset" data-control="{{ field.name }}" title="<?php esc_attr_e( 'Reset to default', 'customify' ); ?>"><span class="dashicons dashicons-image-rotate"></span></a>
		</div>
		<?php
		// Select-like trigger: previews the saved value (family + size/weight)
		// and toggles the floating edit popover. Keeps the legacy
		// `action--edit` class so the delegated open/close handler in
		// typography-control.js keeps working unchanged. The preview spans
		// are filled by renderTypoTrigger() (JS) — initial paint included —
		// so the format logic lives in exactly one place.
		?>
		<a href="#" class="action--edit customify-typo-trigger" data-control="{{ field.name }}" title="<?php esc_attr_e( 'Toggle edit panel', 'customify' ); ?>">
			<span class="customify-trigger--family"></span>
			<span class="customify-trigger--meta"></span>
			<span class="customify-trigger--arrow dashicons dashicons-arrow-down-alt2"></span>
		</a>
		<div class="customify-field-settings-inner">
			<input type="hidden" class="customify-typography-input customify-only" data-name="{{ field.name }}" value="{{ JSON.stringify( field.value ) }}" data-default="{{ JSON.stringify( field.default ) }}">
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
