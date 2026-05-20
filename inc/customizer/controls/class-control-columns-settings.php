<?php
/**
 * Columns Settings control — per-column layout / gap / padding.
 *
 * Renders a single mount node; the React app (bundled inside
 * backend/customizer/control.js) takes over from there.
 *
 * Saved value shape (unchanged from the jQuery version):
 *   {
 *     "desktop": { "<colKey>": { layout, gap: { unit, value }, padding: { top, right, bottom, left, unit, link } }, ... },
 *     "mobile":  { ... }
 *   }
 *
 * @since 0.5.0
 */
class Customify_Customizer_Control_Columns_Settings extends Customify_Customizer_Control_Base {

	/**
	 * Setting key whose `count` field determines how many column panels
	 * the React app should render (e.g. 'header_main_col_layout').
	 *
	 * @var string
	 */
	public $col_layout_setting = '';

	/**
	 * Ordered list of column slot keys (e.g. left/center/right/col4/col5).
	 *
	 * @var array
	 */
	public $column_keys = array( 'left', 'center', 'right', 'col4', 'col5' );

	/**
	 * Hide the per-column Layout button group (used when layout is fixed —
	 * e.g. the mobile off-canvas sidebar is always vertical).
	 *
	 * @var bool
	 */
	public $hide_layout = false;

	/**
	 * When set, this layout value is used by the CSS generator regardless
	 * of what is stored in the saved value (e.g. "stack" for sidebar).
	 *
	 * @var string
	 */
	public $forced_layout = '';

	/**
	 * Map of `colKey => CSS selector` to override the default
	 * `{selector} .col-v2-{key}` chain. Useful for rows whose markup
	 * doesn't follow the col-v2 pattern (e.g. mobile off-canvas sidebar).
	 *
	 * @var array
	 */
	public $col_selectors = array();

	public function to_json() {
		parent::to_json();
		$this->json['col_layout_setting'] = $this->col_layout_setting;
		$this->json['column_keys']        = $this->column_keys;
		$this->json['hide_layout']        = (bool) $this->hide_layout;
		$this->json['forced_layout']      = (string) $this->forced_layout;
		$this->json['col_selectors']      = is_array( $this->col_selectors ) ? $this->col_selectors : array();
	}

	static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-columns_settings">';
		self::before_field();
		?>
		<?php echo self::field_header(); ?>
		<div
			class="customify-columns-settings-mount"
			data-control="{{ field.name }}"
			data-col-layout="{{ field.col_layout_setting }}"
			data-column-keys='{{{ JSON.stringify( field.column_keys ) }}}'
			data-default='{{{ JSON.stringify( field.default ) }}}'
			data-value='{{{ JSON.stringify( field.value ) }}}'
			data-hide-layout="{{ field.hide_layout ? '1' : '' }}"
		></div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
