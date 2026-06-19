<?php
/**
 * Typography presets control — a grid of font-pair quick picks.
 *
 * Pure chrome with NO stored value of its own: clicking a preset (JS,
 * typography-presets.js) patches ONLY the family bits (font / font_type
 * / variant) of the Body + Heading typography settings; the reset
 * button clears those same bits. The active card is derived live from
 * the two settings, so manual font picks stay in sync.
 *
 * Each card previews the pair with an inline SVG — "Aa" in the heading
 * family, "Font Family" in the body family. The families render from
 * the Google Fonts css2 stylesheet enqueued for the controls frame in
 * configs/typography.php (glyph-subset via text=, so the payload is a
 * few KB total).
 */
class Customify_Customizer_Control_Typography_Presets extends Customify_Customizer_Control_Base {
	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-typography_presets">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div class="customify-actions">
			<a href="#" title="<?php esc_attr_e( 'Remove preset', 'customify' ); ?>" class="customify-presets-reset" data-control="{{ field.name }}"><span class="dashicons dashicons-image-rotate"></span></a>
		</div>
		<div class="customify-typo-presets" data-control="{{ field.name }}">
			<# _.each( field.fields, function( preset, index ) { #>
			<?php
			// Pair name via a CSS tooltip styled like the block editor's
			// (dark, no arrow) — the native title tooltip is too slow and
			// subtle to read as feedback here. data-tooltip feeds the
			// ::after bubble in _control.scss; aria-label keeps the
			// accessible name.
			?>
			<button type="button" class="customify-typo-preset" data-index="{{ index }}" data-tooltip="{{ preset.name }}" aria-label="{{ preset.name }}">
				<svg viewBox="0 0 84 56" aria-hidden="true" focusable="false">
					<text class="customify-preset--aa" x="42" y="30" text-anchor="middle" style="font-family: '{{ preset.heading.family }}', {{ preset.heading.fallback }};">Aa</text>
					<text class="customify-preset--name" x="42" y="47" text-anchor="middle" style="font-family: '{{ preset.body.family }}', {{ preset.body.fallback }};">Font Family</text>
				</svg>
			</button>
			<# }); #>
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
