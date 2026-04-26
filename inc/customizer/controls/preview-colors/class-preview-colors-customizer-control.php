<?php
/**
 * Preview Colors — custom WP Customizer control.
 *
 * Lazy-loaded by Customify_Preview_Colors_Customizer::register() inside the
 * `customize_register` action, after which `WP_Customize_Control` is defined.
 *
 * The control renders only the shadow-host <div>; no built-in label or
 * description — the panel UI inside the shadow root provides its own header.
 * State persists via the bound Customizer settings (option type), and the
 * panel JS clones changes onto wp.customize so Publish saves through the
 * standard pipeline.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Customizer_Control extends WP_Customize_Control
{
	public $type = 'customify_color_palette';

	public function render_content()
	{
		?>
		<?php if (! empty($this->label)) : ?>
			<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
		<?php endif; ?>
		<?php if (! empty($this->description)) : ?>
			<span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
		<?php endif; ?>
		<div id="<?php echo esc_attr(Customify_Preview_Colors_Customizer::HOST_ID); ?>" class="customify-color-palette-host"></div>
		<?php
	}
}
