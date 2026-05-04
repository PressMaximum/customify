<?php
/**
 * Color Palette — custom WP Customizer control.
 *
 * Lazy-loaded by Customify_Color_Palette_Customizer::register() inside the
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

class Customify_Color_Palette_Customizer_Control extends WP_Customize_Control
{
	public $type = 'customify_color_palette';

	/**
	 * Setting IDs that must survive a "Reset section" action.
	 * The JS reset handler reads c.params.reset_exclude and skips these.
	 *
	 * @var string[]
	 */
	public $reset_exclude = array();

	/**
	 * Expose reset_exclude to the JS control object via c.params.
	 */
	public function to_json()
	{
		parent::to_json();
		$this->json['reset_exclude'] = $this->reset_exclude;
	}

	public function render_content()
	{
		?>
		<?php if (! empty($this->label)) : ?>
			<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
		<?php endif; ?>
		<?php if (! empty($this->description)) : ?>
			<span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
		<?php endif; ?>
		<div id="<?php echo esc_attr(Customify_Color_Palette_Customizer::HOST_ID); ?>" class="customify-color-palette-host"></div>
		<?php
	}
}
