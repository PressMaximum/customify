<?php
/**
 * Row Layout control — renders a placeholder div that the footer-row-layout
 * React script mounts into.
 */
class Customify_Customizer_Control_Row_Layout extends WP_Customize_Control {
	public $type = 'row_layout';

	public function render_content() {
		$setting    = isset( $this->settings['default'] ) ? $this->settings['default'] : null;
		$setting_id = $setting ? $setting->id : $this->id;
		?>
		<div
			class="customify-row-layout-control"
			id="cb-row-layout-<?php echo esc_attr( $setting_id ); ?>"
			data-setting="<?php echo esc_attr( $setting_id ); ?>"
		></div>
		<?php
	}
}
