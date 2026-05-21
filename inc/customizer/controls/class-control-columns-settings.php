<?php
/**
 * Columns Settings control — per-column direction / align / gap / padding.
 *
 * Renders a single mount node; the React app (bundled inside
 * backend/customizer/control.js) takes over from there.
 *
 * Saved value shape:
 *   {
 *     "desktop": { "<colKey>": { direction, align, gap: { unit, value }, padding: { top, right, bottom, left, unit, link } }, ... },
 *     "mobile":  { ... }
 *   }
 *
 * `direction` is `'row'` or `'column'`.
 * `align` is one of `flex-start | flex-center | flex-end | space-between`
 * and maps to `justify-content` on the main axis defined by `direction`.
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
	 * Hide the per-column Direction button group (used when direction is
	 * fixed — e.g. the mobile off-canvas sidebar is always vertical).
	 *
	 * @var bool
	 */
	public $hide_direction = false;

	/**
	 * Hide the per-column Align button group.
	 *
	 * @var bool
	 */
	public $hide_align = false;

	/**
	 * When set, this direction (`'row'` or `'column'`) is used by the CSS
	 * generator regardless of what is stored in the saved value (e.g.
	 * `'column'` for the mobile sidebar).
	 *
	 * @var string
	 */
	public $forced_direction = '';

	/**
	 * When set, this align value is used regardless of saved input.
	 *
	 * @var string
	 */
	public $forced_align = '';

	/**
	 * Default direction used when the user hasn't picked one for a column.
	 * Overrides the built-in fallback of `'row'`. Typical values:
	 *   - Header rows  → `'row'`
	 *   - Footer rows  → `'column'`
	 * The user can still pick a different value per column. Use
	 * `forced_direction` if you need to lock the value regardless of user
	 * input.
	 *
	 * @var string
	 */
	public $default_direction = '';

	/**
	 * Default align used when the user hasn't picked one for a column.
	 * Overrides the built-in position-based default (first → flex-start,
	 * last → flex-end, middle → flex-center). The user can still pick a
	 * different value per column. Use `forced_align` if you need to lock
	 * the value regardless of user input.
	 *
	 * @var string
	 */
	public $default_align = '';

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
		$this->json['hide_direction']     = (bool) $this->hide_direction;
		$this->json['hide_align']         = (bool) $this->hide_align;
		$this->json['forced_direction']   = (string) $this->forced_direction;
		$this->json['forced_align']       = (string) $this->forced_align;
		$this->json['default_direction']  = (string) $this->default_direction;
		$this->json['default_align']      = (string) $this->default_align;
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
			data-hide-direction="{{ field.hide_direction ? '1' : '' }}"
			data-hide-align="{{ field.hide_align ? '1' : '' }}"
			data-forced-direction="{{ field.forced_direction }}"
			data-forced-align="{{ field.forced_align }}"
			data-default-direction="{{ field.default_direction }}"
			data-default-align="{{ field.default_align }}"
		></div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
