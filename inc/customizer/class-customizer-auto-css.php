<?php

class Customify_Customizer_Auto_CSS
{

	static $_instance;
	public $fonts = array();
	public $custom_fonts = array();
	public $variants = array();
	public $subsets = array();

	// WP Font Library buckets. Separate from $fonts/$variants because
	// they emit @font-face inline instead of building a Google Fonts URL.
	public $library_fonts = array();
	public $library_variants = array();

	// theme.json typography.fontFamilies buckets — same idea as the
	// Library buckets but the data comes from WP_Theme_JSON_Resolver
	// and the family CSS may be a stack ("Inter, sans-serif") rather
	// than a single name, so we keep both the picker name and the raw
	// fontFamily string for frontend output.
	public $theme_fonts = array();
	public $theme_variants = array();
	private $theme_fonts_resolver = null;

	static $code = null;
	static $font_url = null;
	/**
	 * CSS device query settings
	 *
	 * @var array
	 */
	public $media_queries = array(
		'all'     => '%s',
		'desktop' => '%s',
		'tablet'  => '@media screen and (max-width: 1024px) { %s }',
		'mobile'  => '@media screen and (max-width: 568px) { %s }',
	);

	/**
	 * CSS code for devices
	 *
	 * @var array
	 */
	private $css = array(
		'all'     => '',
		'desktop' => '',
		'tablet'  => '',
		'mobile'  => '',
	);

	/**
	 * Default shadow fields
	 *
	 * @var array
	 */
	private $box_shadow_fields = array(
		'color'  => null,
		'x'      => 0,
		'y'      => 0,
		'blur'   => 0,
		'spread' => 0,
		'inset'  => null,
	);

	/**
	 * Get intance.
	 *
	 * @return Customify_Customizer_Auto_CSS
	 */
	static function get_instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
			self::$code      = self::$_instance->auto_css();
		}

		return self::$_instance;
	}

	private function replace_value($value, $format, $value_no_unit = '')
	{
		if (!$value) {
			$value =  '';
		}
		if (!$value_no_unit) {
			$value_no_unit =  '';
		}
		if (!$format) {
			$format =  '';
		}

		$s = str_replace('{{value}}', $value, $format);
		return str_replace('{{value_no_unit}}', $value_no_unit, $s);
	}

	function setup_css_ruler($value, $format)
	{
		$value = wp_parse_args(
			$value,
			array(
				'unit'   => '',
				'top'    => null,
				'right'  => null,
				'bottom' => null,
				'left'   => null,
			)
		);

		if (!$value['unit']) {
			$value['unit'] = 'px';
		}

		$format = wp_parse_args(
			$format,
			array(
				'top'    => null,
				'right'  => null,
				'bottom' => null,
				'left'   => null,
			)
		);

		$code = array();
		foreach ($format as $pos => $string) {
			$v = $value[$pos];
			if ($string) {
				if (!is_null($v) && '' !== $v) {
					$v            = $v . $value['unit'];
					$code[$pos] = $this->replace_value($v, $string);
				}
			}
		}

		return join("\n\t", $code);
	}

	function setup_shadow($value, $format)
	{

		if (!is_array($value) || !$format) {
			return '';
		}

		$p        = wp_parse_args($value, $this->box_shadow_fields);
		$value    = Customify_Sanitize_Input::sanitize_color($p['color']);
		$color    = $value;
		$position = $p['inset'] ? 'inset' : '';
		if (!$color) {
			return '';
		}
		if (!$p['blur']) {
			$p['blur'] = 0;
		}
		if (!$p['spread']) {
			$p['spread'] = 0;
		}
		if (!$p['x']) {
			$p['x'] = 0;
		}
		if (!$p['y']) {
			$p['y'] = 0;
		}

		$style = $p['x'] . 'px'
			. ' ' . $p['y'] . 'px'
			. ' ' . $p['blur'] . 'px'
			. ' ' . $p['spread'] . 'px'
			. ' ' . $color
			. ' ' . $position
			. ';';

		// offset-x | offset-y | blur-radius | spread-radius | color.
		// box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.2);.
		return $this->replace_value($style, $format);
	}

	function setup_slider($value, $format)
	{
		$value = wp_parse_args(
			$value,
			array(
				'unit'  => '',
				'value' => null,
			)
		);

		if (!$value['unit']) {
			$value['unit'] = 'px';
		}

		if ($format) {
			if (!is_null($value['value']) && '' !== $value['value']) {
				$v = $value['value'] . $value['unit'];
				$c = $this->replace_value($v, $format, $value['value']);

				return $c;
			}
		}

		return false;
	}

	function setup_color($value, $format)
	{
		$value = Customify_Sanitize_Input::sanitize_color($value);
		if ($format) {
			if (!is_null($value) && '' !== $value) {
				return $this->replace_value($value, $format);
			}
		}

		return false;
	}

	function setup_checkbox($value, $format)
	{
		if ($format) {
			if ($value) {
				return $format;
			}
		}

		return false;
	}

	function setup_image($value, $format)
	{
		$image = Customify()->get_media($value);
		if ($format) {
			if ($image) {
				return $this->replace_value($image, $format) . '';
			}
		}

		return false;
	}

	function setup_text_align($value, $format)
	{
		$value = sanitize_text_field($value);
		if ($format) {
			if (!is_null($value) && '' !== $value) {
				return $this->replace_value($value, $format) . ';';
			}
		}

		return false;
	}

	function css_ruler($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_css_ruler', $values, $no_selector);

		return $code;
	}

	function slider($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_slider', $values, $no_selector);

		return $code;
	}

	function color($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_color', $values, $no_selector);

		return $code;
	}

	function shadow($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_shadow', $values, $no_selector);

		return $code;
	}

	function checkbox($field, $values = null, $no_selector = null)
	{
		return $this->maybe_devices_setup($field, 'setup_checkbox', $values, $no_selector);
	}

	function image($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_image', $values, $no_selector);

		return $code;
	}

	function text_align($field, $values = null, $no_selector = null)
	{
		$code = $this->maybe_devices_setup($field, 'setup_default', $values, $no_selector);

		return $code;
	}

	function _join($lists, $codeList, &$selectorCSSAll = array(), &$selectorCSSDevices = array())
	{ //phpcs:ignore
		if (!is_array($selectorCSSAll)) {
			$selectorCSSAll = array();
		}

		if (!is_array($selectorCSSDevices)) {
			$selectorCSSDevices = array();
		}

		foreach ((array) $lists as $name => $f) {

			if (isset($f['selector']) && $f['selector']) {
				if (!isset($selectorCSSAll[$f['selector']])) {
					$selectorCSSAll[$f['selector']] = '';
				}

				if (isset($codeList[$name])) {

					if (isset($codeList[$name]['no_devices'])) {
						if ($codeList[$name]['no_devices']) {
							$selectorCSSAll[$f['selector']] .= $codeList[$name]['no_devices'];
						}
					} else {

						if (is_array($codeList[$name])) {
							foreach ($codeList[$name] as $device => $code) {
								if (!isset($selectorCSSDevices[$device])) {
									$selectorCSSDevices[$device] = array();
								}

								if (!isset($selectorCSSDevices[$device][$f['selector']])) {
									$selectorCSSDevices[$device][$f['selector']] = '';
								}

								if ($code) {
									$selectorCSSDevices[$device][$f['selector']] .= $code;
								}
							}
						}
					}
				}
			}
		}
	}

	function setup_styling_fields($fields, $list, $selectors, $type)
	{
		$newList = array();
		if (!is_array($selectors)) {
			$selectors = array();
		}
		if (false === $fields) {
			$newList = null;
		} else {

			if (!is_array($fields)) {
				$fields = array();
			}

			$newfs = array();
			$i     = 0;
			foreach ($list as $f) {
				$key = $f['name'];
				if (!isset($fields[$key]) || $fields[$key]) {
					$newfs[$key] = $f;
					if (isset($selectors[$type . '_' . $key])) {
						$newfs[$key]['selector'] = $selectors[$type . '_' . $key];
					} else {
						$newfs[$key]['selector'] = $selectors[$type];
					}
					$i++;
				}
			}
			$newList = $newfs;

			return $newList;
		}
	}

	/**
	 * Emit CSS for a `columns_settings` field.
	 *
	 * Saved value:
	 *   { desktop: { <colKey>: { direction, align, gap, padding: { top, right, bottom, left, unit } }, ... },
	 *     mobile:  { <colKey>: { ... }, ... } }
	 *
	 * `direction` is `'row'` or `'column'`. `align` is one of
	 * `flex-start | flex-center | flex-end | space-between` and maps to
	 * `justify-content` on the main axis defined by `direction`.
	 *
	 * Selector: `{$field['selector']} .col-v2-<colKey>`
	 *
	 * @since 0.5.0
	 */
	function columns_settings($field, $values = null)
	{
		$values = Customify()->get_setting($field['name']);
		if (!is_array($values)) {
			$values = array();
		}

		$row_selector = isset($field['selector']) ? $field['selector'] : '';
		if (!$row_selector) {
			return;
		}

		$col_selectors     = isset($field['col_selectors']) && is_array($field['col_selectors']) ? $field['col_selectors'] : array();
		$forced_direction  = isset($field['forced_direction']) ? (string) $field['forced_direction'] : '';
		$forced_align      = isset($field['forced_align']) ? (string) $field['forced_align'] : '';
		$default_direction = isset($field['default_direction']) ? (string) $field['default_direction'] : '';
		$default_align     = isset($field['default_align']) ? (string) $field['default_align'] : '';
		$column_keys       = isset($field['column_keys']) && is_array($field['column_keys']) ? $field['column_keys'] : array();
		// Field-level device list — drives which buckets the CSS pipeline
		// iterates. Header rows default to `[desktop, mobile]`; footer
		// rows declare the full `[desktop, tablet, mobile]` set.
		$devices_list      = isset($field['devices']) && is_array($field['devices']) && ! empty($field['devices'])
			? $field['devices']
			: array( 'desktop', 'mobile' );
		// Optional per-device class-scoping. When a device maps to a class
		// here, its CSS is scoped through that class (between the row
		// selector and `.col-v2-{key}`) and lands in the unwrapped `all`
		// bucket — used for builders that double-render markup per
		// device, e.g. header wraps desktop items in `.cb-row--desktop`
		// and mobile items in `.cb-row--mobile`.
		$device_scope      = isset($field['device_scope']) && is_array($field['device_scope']) ? $field['device_scope'] : array();

		// Legacy fallback — early builds of this control saved column data without
		// a desktop/mobile wrapper (the generic sanitizer stripped it). When the
		// stored value has column keys at the top level instead of device keys,
		// treat the whole bag as the desktop bucket so the saved gap/padding
		// keeps applying after the sanitize fix.
		if ( ! empty( $values ) && ! isset( $values['desktop'] ) && ! isset( $values['mobile'] ) ) {
			$has_col_key = false;
			foreach ( $column_keys as $_ck ) {
				if ( isset( $values[ $_ck ] ) ) {
					$has_col_key = true;
					break;
				}
			}
			if ( $has_col_key ) {
				$values = array( 'desktop' => $values );
			}
		}

		// Active column list — count from the linked col_layout setting if any,
		// else fall back to the full column_keys list. Drives the position-based
		// default align (first=flex-start, last=flex-end, middle=flex-center)
		// applied below when the user hasn't saved an explicit align choice.
		$active_count = count($column_keys);
		if (!empty($field['col_layout_setting'])) {
			$cl_raw  = Customify()->get_setting($field['col_layout_setting']);
			$cl_data = is_array($cl_raw) ? $cl_raw : (is_string($cl_raw) ? json_decode($cl_raw, true) : array());
			if (is_array($cl_data) && isset($cl_data['count'])) {
				$cnt = intval($cl_data['count']);
				if ($cnt >= 1) {
					$active_count = min(count($column_keys), $cnt);
				}
			}
		}
		$active_columns = array_slice($column_keys, 0, $active_count);

		// Build device_map from field's `devices` config. Each device
		// becomes the saved-value bucket key. The CSS bucket defaults to
		// the same device name (wrapped by the matching media query at
		// render time) — but for devices in `$device_scope`, we instead
		// land in the unwrapped `all` bucket and inject the scope class
		// into the selector below.
		$device_map = array();
		foreach ( $devices_list as $_d ) {
			$device_map[ $_d ] = isset( $device_scope[ $_d ] ) && $device_scope[ $_d ] ? 'all' : $_d;
		}

		foreach ($device_map as $device_key => $css_bucket) {
			$device_values = (isset($values[$device_key]) && is_array($values[$device_key])) ? $values[$device_key] : array();

			// Always seed the active columns so direction/align defaults emit
			// even when the user hasn't saved a value. Padding/gap remain empty
			// until the user sets them.
			foreach ($active_columns as $ck) {
				if (!isset($device_values[$ck])) {
					$device_values[$ck] = array();
				}
			}

			if (empty($device_values)) {
				continue;
			}

			foreach ($device_values as $col_key => $col_value) {
				if (!is_array($col_value)) {
					$col_value = array();
				}

				if ( isset( $col_selectors[ $col_key ] ) && $col_selectors[ $col_key ] ) {
					$selector = $col_selectors[ $col_key ];
				} else {
					$scope    = isset( $device_scope[ $device_key ] ) && $device_scope[ $device_key ]
						? ' ' . trim( $device_scope[ $device_key ] )
						: '';
					$selector = trim( $row_selector ) . $scope . ' .col-v2-' . sanitize_html_class( $col_key );
				}
				$rules    = array();

				// Resolve direction. Order: forced > saved > field default > 'row'.
				$saved_direction = isset($col_value['direction']) ? (string) $col_value['direction'] : '';
				if ('' !== $forced_direction) {
					$direction = $forced_direction;
				} elseif ('' !== $saved_direction) {
					$direction = $saved_direction;
				} elseif ('' !== $default_direction) {
					$direction = $default_direction;
				} else {
					$direction = 'row';
				}
				if ('row' !== $direction && 'column' !== $direction) {
					$direction = 'row';
				}

				// Position-based default align. First column → flex-start,
				// last → flex-end, anything else → flex-center. A single
				// active column resolves to flex-start.
				$col_index    = array_search($col_key, $active_columns, true);
				$position_align = 'flex-center';
				if ($col_index === false) {
					$position_align = '';
				} elseif (count($active_columns) === 1 || $col_index === 0) {
					$position_align = 'flex-start';
				} elseif ($col_index === count($active_columns) - 1) {
					$position_align = 'flex-end';
				}

				// Resolve align. Order: forced > saved > field default > position-based.
				$saved_align = isset($col_value['align']) ? (string) $col_value['align'] : '';
				if ('' !== $forced_align) {
					$align = $forced_align;
				} elseif ('' !== $saved_align) {
					$align = $saved_align;
				} elseif ('' !== $default_align) {
					$align = $default_align;
				} else {
					$align = $position_align;
				}

				// Map align → justify-content keyword (shared between the
				// column-slot rule and the per-item rule below).
				$justify_value = '';
				switch ($align) {
					case 'flex-start':    $justify_value = 'flex-start';    break;
					case 'flex-center':   $justify_value = 'center';        break;
					case 'flex-end':      $justify_value = 'flex-end';      break;
					case 'space-between': $justify_value = 'space-between'; break;
				}

				// Emit flexbox rules on the column slot.
				//
				// Row direction: align maps to justify-content on the column
				// so items distribute horizontally inside the slot.
				//
				// Column direction: align does NOT go on the column (items
				// just stack vertically). Instead it's applied below to each
				// `.item--inner` so the chosen value aligns the item's own
				// internal content (icon vs label, image vs caption, …)
				// along the horizontal axis.
				$rules[] = 'display: flex;';
				$rules[] = 'flex-direction: ' . $direction . ';';
				if ('row' === $direction) {
					if ('' !== $justify_value) {
						$rules[] = 'justify-content: ' . $justify_value . ';';
					}
					$rules[] = 'align-items: center;';
				}

				// Gap — value shape: { unit, value } or scalar (legacy).
				if (isset($col_value['gap'])) {
					$gap_val  = $col_value['gap'];
					$gap_num  = null;
					$gap_unit = 'em';
					if (is_array($gap_val)) {
						if (isset($gap_val['value']) && '' !== $gap_val['value'] && null !== $gap_val['value']) {
							$gap_num  = $gap_val['value'];
							$gap_unit = isset($gap_val['unit']) && $gap_val['unit'] ? $gap_val['unit'] : 'em';
						}
					} elseif ('' !== $gap_val && null !== $gap_val) {
						$gap_num = $gap_val;
					}
					if (null !== $gap_num) {
						$rules[] = 'gap: ' . floatval($gap_num) . sanitize_text_field($gap_unit) . ';';
					}
				}

				// Padding.
				if (isset($col_value['padding']) && is_array($col_value['padding'])) {
					$padding = wp_parse_args(
						$col_value['padding'],
						array(
							'top'    => null,
							'right'  => null,
							'bottom' => null,
							'left'   => null,
							'unit'   => 'px',
						)
					);
					$unit  = $padding['unit'] ? $padding['unit'] : 'px';
					$sides = array();
					foreach (array('top', 'right', 'bottom', 'left') as $side) {
						if (null === $padding[$side] || '' === $padding[$side]) {
							continue;
						}
						$sides[$side] = 'padding-' . $side . ': ' . floatval($padding[$side]) . $unit . ';';
					}
					if ($sides) {
						$rules = array_merge($rules, array_values($sides));
					}
				}

				if (empty($rules)) {
					continue;
				}

				$this->css[$css_bucket] .= "{$selector} {\r\n\t" . join("\r\n\t", $rules) . "\r\n}\r\n";

				// Column direction extras:
				//   1. Each item takes the full column width so stacked
				//      items line up on their own row.
				//   2. Each `.item--inner` becomes its own flex container
				//      with the resolved `justify-content`, so the user's
				//      align choice controls horizontal placement of the
				//      item's internal content.
				if ('column' === $direction) {
					$this->css[$css_bucket] .= "{$selector} > * {\r\n\twidth: 100%;\r\n}\r\n";
					if ('' !== $justify_value) {
						$this->css[$css_bucket] .= "{$selector} .item--inner {\r\n\tdisplay: flex;\r\n\tjustify-content: " . $justify_value . ";\r\n}\r\n";
					}
				}
			}
		}
	}

	function styling($field, $values = null)
	{
		$values = Customify()->get_setting($field['name'], 'all');

		$values = wp_parse_args(
			$values,
			array(
				'normal' => array(),
				'hover'  => array(),
			)
		);

		$new_fields = array();
		$selectors  = array();

		if (is_string($field['selector'])) {
			$selectors['normal'] = $field['selector'];
			$selectors['hover']  = $field['selector'];
		} else {
			$selectors = wp_parse_args(
				$field['selector'],
				array(
					'normal' => array(),
					'hover'  => array(),
				)
			);
		}
		$tabs          = null;
		$normal_fields = -1;
		$hover_fields  = -1;
		if (isset($field['fields']) && is_array($field['fields'])) {
			if (isset($field['fields']['tabs'])) {
				$tabs = $field['fields']['tabs'];
			}
			if (isset($field['fields']['normal_fields'])) {
				$normal_fields = $field['fields']['normal_fields'];
			}
			if (isset($field['fields']['hover_fields'])) {
				$hover_fields = $field['fields']['hover_fields'];
			}
		}

		$styling_config     = Customify()->customizer->get_styling_config();
		$selectorCSSAll     = array();
		$selectorCSSDevices = array();
		$listNormalFields   = $this->setup_styling_fields($normal_fields, $styling_config['normal_fields'], $selectors, 'normal');
		$listHoverFields    = $this->setup_styling_fields($hover_fields, $styling_config['hover_fields'], $selectors, 'hover');

		$listTabs = $styling_config['tabs'];

		if (false === $tabs) {
			$listTabs['hover'] = false;
		} elseif (is_array($tabs)) {
			$listTabs = $tabs;
		}

		// Do no use bg settings if no bg.
		if (isset($values['normal']['bg_image'])) {
			$image = Customify()->get_media($values['normal']['bg_image']);
			if (!$image) {
				unset($values['normal']['bg_repeat']);
				unset($values['normal']['bg_cover']);
				unset($values['normal']['bg_position']);
				unset($values['normal']['bg_attachment']);
			}
		}

		$normal_style = $this->loop_fields($listNormalFields, $values['normal'], true, true);
		$hover_style  = $this->loop_fields($listHoverFields, $values['hover'], true, true);

		$this->_join($listNormalFields, $normal_style, $selectorCSSAll, $selectorCSSDevices);
		$this->_join($listHoverFields, $hover_style, $selectorCSSAll, $selectorCSSDevices);

		foreach ($selectorCSSAll as $s => $code) {
			if (trim($code)) {
				$this->css['all'] .= "\r\n{$s}  {\r\n\t{$code}\r\n} \r\n";
			}
		}

		foreach (Customify()->customizer->devices as $device) {
			$css = '';
			if (isset($selectorCSSDevices[$device])) {
				$deviceCode = $selectorCSSDevices[$device];
				foreach ($deviceCode as $s => $c) {
					if (!empty($c)) {
						if (is_string($c) && trim($c)) {
							$css .= "\r\n{$s}  {\r\n\t{$c}\r\n} \r\n";
						} else {
							if (!is_array($c)) {
								$c = array();
							}
							$c = array_map('trim', $c);
							$c = array_filter($c);
							if (!empty($c)) {
								$css .= "\r\n{$s}  {\r\n\t" . join($c, "\n") . "\r\n} \r\n";
							}
						}
					}
				}
			}
			$this->css[$device] .= $css;
		}
	}

	function modal($field, $values = null)
	{

		$values = Customify()->get_setting($field['name'], 'all');
		if (!is_array($values)) {
			$values = array();
		}
		if (isset($field['fields']['tabs'])) {
			foreach ($field['fields']['tabs'] as $key => $title) {
				if (isset($field['fields'][$key . '_fields'])) {
					$this->loop_fields($field['fields'][$key . '_fields'], isset($values[$key]) ? $values[$key] : array());
				}
			}
		}
	}

	function setup_default($value, $format)
	{
		if (is_string($value) && $value) {
			$value = sanitize_text_field($value);
			if ($format) {
				return $this->replace_value($value, $format);
			}
		}

		return false;
	}

	function maybe_devices_setup($field, $call_back, $values = null, $no_selector = false)
	{
		$code       = '';
		$code_array = array();
		$has_device = false;
		$format     = isset($field['css_format']) ? $field['css_format'] : false;
		if (isset($field['device_settings']) && $field['device_settings']) {
			$has_device = true;
			foreach (Customify()->customizer->devices as $device) {
				$value = null;
				if (is_null($values)) {
					$value = Customify()->get_setting($field['name'], $device);
				} else {
					if (isset($values[$device])) {
						$value = $values[$device];
					}
				}
				$_c = false;
				if (method_exists($this, $call_back)) {
					$_c = call_user_func_array(array($this, $call_back), array($value, $format));
				}
				if ($_c) {
					$code_array[$device] = $_c;
				}
			}
		} else {
			if (is_null($values)) {
				$values = Customify()->get_setting($field['name']);
			}
			if (method_exists($this, $call_back)) {
				$code = call_user_func_array(array($this, $call_back), array($values, $format));
			}
			$code_array['no_devices'] = $code;
		}

		$code_array = apply_filters('customify/customizer/auto_css', $code_array, $field, $this);

		if (empty($code_array)) {
			return false;
		}

		$code_array = array_map('trim', $code_array);
		$code_array = array_filter($code_array);

		$code = '';
		if ($no_selector) {
			return $code_array;
		} else {
			if ($has_device) {
				foreach (Customify()->customizer->devices as $device) {
					if (isset($code_array[$device])) {
						$_c = $code_array[$device];
						if ($_c && trim($_c)) {
							if ('format' == $field['selector']) {
								$this->css[$device] .= "\r\n{$_c}\r\n";
							} else {
								$this->css[$device] .= "\r\n{$field['selector']} {\r\n\t{$_c}\r\n}\r\n";
							}
						}
					}
				}
			} else {
				if (isset($code_array['no_devices']) && $code_array['no_devices']) {
					if ('format' == $field['selector']) {
						$this->css['all'] .= "\r\n{$code_array['no_devices']}\r\n";
					} else {
						$this->css['all'] .= "\r\n{$field['selector']} {\r\n\t{$code_array['no_devices']}\r\n}\r\n";
					}
				}
			}
		}

		return $code;
	}

	function setup_font($value)
	{

		$value = wp_parse_args(
			$value,
			array(
				'font'    => null,
				'type'    => null,
				'variant' => null,
				'subsets' => null,
			)
		);

		if (!$value['font']) {
			return '';
		}

		if ('google' == $value['type']) {
			$this->fonts[$value['font']] = $value['font'];
			if ($value['variant']) {
				if (!isset($this->variants[$value['font']])) {
					$this->variants[$value['font']] = array();
					if (!is_array($value['variant'])) {
						$this->variants[$value['font']] = array_merge($this->variants[$value['font']], array($value['variant'] => $value['variant']));
					} else {
						$this->variants[$value['font']] = array_merge($this->variants[$value['font']], $value['variant']);
					}
				}
			}

			if ($value['subsets']) {
				$this->subsets = array_merge($this->subsets, $value['subsets']);
			}
		} elseif ('library' === $value['type']) {
			// WP Font Library fonts are uploaded locally; we resolve
			// their files in get_library_fonts_css() and emit @font-face
			// inline. Collect only the variants actually used so we
			// don't ship CSS for unused weight/style combinations.
			$this->library_fonts[$value['font']] = $value['font'];
			if ($value['variant']) {
				$variants = is_array($value['variant']) ? array_values($value['variant']) : array($value['variant']);
				if (!isset($this->library_variants[$value['font']])) {
					$this->library_variants[$value['font']] = array();
				}
				$this->library_variants[$value['font']] = array_unique(
					array_merge($this->library_variants[$value['font']], $variants)
				);
			}
		} elseif ('theme' === $value['type']) {
			// theme.json-declared fonts. Same bucket pattern as Library,
			// but the font-family CSS uses the theme.json `fontFamily`
			// stack (e.g. "Inter, sans-serif") so unloaded fonts fall
			// back to the next family in the stack gracefully.
			$this->theme_fonts[$value['font']] = $value['font'];
			if ($value['variant']) {
				$variants = is_array($value['variant']) ? array_values($value['variant']) : array($value['variant']);
				if (!isset($this->theme_variants[$value['font']])) {
					$this->theme_variants[$value['font']] = array();
				}
				$this->theme_variants[$value['font']] = array_unique(
					array_merge($this->theme_variants[$value['font']], $variants)
				);
			}

			// Use the raw fontFamily declaration from theme.json when
			// it differs from the picker name. Falls back to the name
			// (quoted) so unknown fonts still emit valid CSS.
			$resolver = $this->theme_fonts_resolver();
			if ($resolver) {
				$theme_data = $resolver->get_for_frontend();
				if (!empty($theme_data[$value['font']]['font_family_css'])) {
					return "font-family: {$theme_data[$value['font']]['font_family_css']};";
				}
			}
		} else {
			$this->custom_fonts[$value['font']] = $value['font'];
		}

		return "font-family: \"{$value['font']}\";";
	}

	/**
	 * Lazy-instantiate the theme-fonts resolver so callers above don't
	 * pay for object construction unless a theme font is actually used.
	 *
	 * @return Customify_Customizer_Theme_Fonts|null
	 */
	private function theme_fonts_resolver()
	{
		if (null === $this->theme_fonts_resolver) {
			$this->theme_fonts_resolver = class_exists('Customify_Customizer_Theme_Fonts')
				? new Customify_Customizer_Theme_Fonts()
				: false;
		}
		return $this->theme_fonts_resolver ?: null;
	}


	function font($field, $values = null)
	{
		$code = '';
		if ($field['device_settings']) {
			foreach (Customify()->customizer->devices as $device) {
				$value = null;
				if (is_null($values)) {
					$value = Customify()->get_setting($field['name'], $device);
				} else {
					if (isset($values[$device])) {
						$value = $values[$device];
					}
				}

				$_c = $this->setup_font($value);
				if ($_c) {
					$this->css[$device] = "\r\n{$field['selector']} {\r\n\t{$_c}\r\n}\r\n";
					if ('desktop' == $device) {
						$code .= "\r\n{$field['selector']} {\r\n\t{$_c}\r\n}";
					} else {
						$code .= "\r\n.{$device} {$field['selector']} {\r\n\t{$_c}\r\n}\r\n";
					}
				}
			}
		} else {
			if (is_null($values)) {
				$values = Customify()->get_setting($field['name']);
			}
			$code             = $this->setup_font($values);
			$this->css['all'] .= "{$field['selector']} {\r\n\t{$code}\r\n}\r\n";
			$code             .= "{$field['selector']} {\r\n\t{$code}\r\n}\r\n";
		}

		return $code;
	}

	function setup_font_style($value)
	{
		$value = wp_parse_args(
			$value,
			array(
				'b' => null,
				'i' => null,
				'u' => null,
				's' => null,
				't' => null,
			)
		);
		$css   = array();
		if ($value['b']) {
			$css['b'] = 'font-weight: bold;';
		}
		if ($value['i']) {
			$css['i'] = 'font-style: italic;';
		}

		$decoration = array();
		if ($value['u']) {
			$decoration['underline'] = 'underline';
		}

		if ($value['s']) {
			$decoration['line-through'] = 'line-through';
		}

		if (!empty($decoration)) {
			$css['d'] = 'text-decoration: ' . join(' ', $decoration) . ';';
		}

		if ($value['t']) {
			$css['t'] = 'text-transform: uppercase;';
		}

		return join("\r\n\t", $css);
	}

	function typography($field)
	{
		$values = Customify()->get_setting($field['name']);
		$values = wp_parse_args(
			$values,
			array(
				'font'            => null,
				'style'           => null,
				'font_size'       => null,
				'line_height'     => null,
				'letter_spacing'  => null,
				'font_type'       => null,
				'languages'       => null,
				'font_weight'     => null,
				'text_decoration' => null,
				'text_transform'  => null,
				'variant'         => null,
			)
		);
		$code   = array();

		$fields      = array();
		$devices_css = array();
		foreach (Customify()->customizer->get_typo_fields() as $f) {
			$fields[$f['name']] = $f;
		}

		if (isset($fields['font'])) {
			$code['font'] = $this->setup_font(
				array(
					'font'    => $values['font'],
					'type'    => $values['font_type'],
					'subsets' => $values['languages'],
					'variant' => $values['variant'],
				)
			);
		}

		if (isset($values['style']) && $values['style']) {

			if ($values['style'] && 'default' !== $values['style']) {
				$code['style'] = 'font-style: ' . $values['style'] . ';';
			}
		}

		// Font Weight.
		if ('default' == $values['font_weight']) {
			$values['font_weight'] = '';
		}
		if (isset($fields['font_weight']) && $values['font_weight']) {
			if ('regular' == $values['font_weight']) {
				$values['font_weight'] = 'normal';
			}
			$code['font_weight'] = 'font-weight: ' . sanitize_text_field($values['font_weight']) . ';';
		}

		// Text Decoration.
		if (isset($fields['text_decoration']) && $values['text_decoration']) {
			$code['text_decoration'] = 'text-decoration: ' . sanitize_text_field($values['text_decoration']) . ';';
		}

		// Text Transform.
		if (isset($fields['text_transform']) && $values['text_transform']) {
			$code['text_transform'] = 'text-transform: ' . sanitize_text_field($values['text_transform']) . ';';
		}

		if (isset($fields['font_size'])) {
			$fields['font_size']['css_format'] = 'font-size: {{value}};';
			$font_size_css                     = $this->maybe_devices_setup($fields['font_size'], 'setup_slider', $values['font_size'], true);
			if ($font_size_css) {
				if (isset($font_size_css['no_devices'])) {
					$code['font_size'] = $font_size_css['no_devices'];
				} else {
					foreach ($font_size_css as $device => $_c) {
						if ('desktop' == $device) {
							$code['font_size'] = $_c;
						} else {
							if (!isset($devices_css[$device])) {
								$devices_css[$device] = array();
							}
							$devices_css[$device]['font_size'] = $_c;
						}
					}
				}
			}
		}

		if (isset($fields['line_height'])) {
			$fields['line_height']['css_format'] = 'line-height: {{value}};';
			$font_size_css                       = $this->maybe_devices_setup($fields['line_height'], 'setup_slider', $values['line_height'], true);
			if ($font_size_css) {
				if (isset($font_size_css['no_devices'])) {
					$code['line_height'] = $font_size_css['no_devices'];
				} else {
					foreach ($font_size_css as $device => $_c) {
						if ('desktop' == $device) {
							$code['line_height'] = $_c;
						} else {
							if (!isset($devices_css[$device])) {
								$devices_css[$device] = array();
							}
							$devices_css[$device]['line_height'] = $_c;
						}
					}
				}
			}
		}

		if (isset($fields['letter_spacing'])) {
			$fields['letter_spacing']['css_format'] = 'letter-spacing: {{value}};';
			$font_size_css                          = $this->maybe_devices_setup($fields['letter_spacing'], 'setup_slider', $values['letter_spacing'], true);
			if ($font_size_css) {
				if (isset($font_size_css['no_devices']) && !empty($font_size_css['no_devices'])) {
					$code['letter_spacing'] = $font_size_css['no_devices'];
				} else {
					foreach ((array) $font_size_css as $device => $_c) {
						if ('desktop' == $device) {
							$code['letter_spacing'] = $_c;
						} else {
							if (!isset($devices_css[$device])) {
								$devices_css[$device] = array();
							}
							$devices_css[$device]['letter_spacing'] = $_c;
						}
					}
				}
			}
		}

		$devices_css = apply_filters('customify/customizer/auto_css', $devices_css, $field, $this);
		foreach ($devices_css as $device => $els) {
			if (!empty($els)) {
				$this->css[$device] .= "{$field['selector']} {\r\n\t" . join("\r\n\t", $els) . "\r\n}";
			}
		}

		$code = array_filter($code);
		if (!empty($code)) {
			$this->css['all'] .= "{$field['selector']} {\r\n\t" . join("\r\n\t", $code) . "\r\n}";
		}
	}

	/**
	 * Build the @font-face CSS for WP Font Library families that are
	 * actually referenced by the current configuration. Auto_CSS must
	 * have run first so $library_fonts / $library_variants are
	 * populated by setup_font(). Returns an empty string when nothing
	 * is in use — keep wp_add_inline_style happy.
	 *
	 * @return string
	 */
	function get_library_fonts_css()
	{
		if (empty($this->library_fonts)) {
			return '';
		}
		if (!class_exists('Customify_Customizer_Font_Library')) {
			return '';
		}

		$library = (new Customify_Customizer_Font_Library())->get_for_frontend();
		if (empty($library)) {
			return '';
		}

		// Dedupe with WP core. Library fonts that the user has
		// "activated" in wp-admin/site-editor end up in
		// wp_get_global_settings() and WP auto-emits @font-face for
		// them. Skip those; emit only the un-activated ones (uploaded
		// but not turned on in the editor) that WP wouldn't reach.
		$handled = class_exists('Customify_Customizer_Font_Loader')
			? Customify_Customizer_Font_Loader::wp_handled_names()
			: array();

		$css = '';
		foreach ($this->library_fonts as $name) {
			if (isset($handled[$name])) {
				continue; // WP will print this
			}
			if (empty($library[$name]['font_faces'])) {
				continue;
			}
			$used = isset($this->library_variants[$name]) ? $this->library_variants[$name] : array();
			foreach ($library[$name]['font_faces'] as $face) {
				// When the user picked specific variants, restrict to
				// those. With no explicit variant in storage we ship
				// every face the family owns so font-weight CSS
				// elsewhere can still resolve.
				if (!empty($used) && !in_array($face['variant'], $used, true)) {
					continue;
				}
				$css .= sprintf(
					"@font-face{font-family:'%s';font-style:%s;font-weight:%s;font-display:swap;src:url('%s');}",
					esc_attr($name),
					esc_attr($face['style']),
					esc_attr($face['weight']),
					esc_url($face['src'])
				);
			}
		}
		return $css;
	}

	/**
	 * Build @font-face CSS for theme.json fontFamilies that are
	 * actually used. Mirrors get_library_fonts_css() but reads from
	 * the theme-fonts resolver, and skips families with no font-face
	 * declarations (system-font stacks need no @font-face).
	 *
	 * @return string
	 */
	function get_theme_fonts_css()
	{
		if (empty($this->theme_fonts)) {
			return '';
		}
		$resolver = $this->theme_fonts_resolver();
		if (!$resolver) {
			return '';
		}
		$theme = $resolver->get_for_frontend();
		if (empty($theme)) {
			return '';
		}

		// Dedupe with WP core's wp_print_font_faces() output. On WP 6.5+
		// theme.json fonts get auto-emitted; re-emitting them here only
		// ships duplicate bytes. Helper returns empty on older WP so
		// legacy installs continue to receive theme-emitted CSS.
		$handled = class_exists('Customify_Customizer_Font_Loader')
			? Customify_Customizer_Font_Loader::wp_handled_names()
			: array();

		$css = '';
		foreach ($this->theme_fonts as $name) {
			if (isset($handled[$name])) {
				continue; // WP will print this
			}
			if (empty($theme[$name]['font_faces'])) {
				continue; // system-font stack — no @font-face to emit
			}
			$used = isset($this->theme_variants[$name]) ? $this->theme_variants[$name] : array();
			foreach ($theme[$name]['font_faces'] as $face) {
				if (!empty($used) && !in_array($face['variant'], $used, true)) {
					continue;
				}
				$css .= sprintf(
					"@font-face{font-family:'%s';font-style:%s;font-weight:%s;font-display:swap;src:url('%s');}",
					esc_attr($name),
					esc_attr($face['style']),
					esc_attr($face['weight']),
					esc_url($face['src'])
				);
			}
		}
		return $css;
	}

	function get_google_fonts_url()
	{
		$url = '//fonts.googleapis.com/css?family=';
		$s   = '';
		if (empty($this->fonts)) {
			return false;
		}

		foreach ($this->fonts as $font_name) {
			if ($s) {
				$s .= '|';
			}
			$s .= str_replace(' ', '+', $font_name);
			$v = array();
			if (isset($this->variants[$font_name])) {
				foreach ($this->variants[$font_name] as $_v) {
					if ('regular' != $_v) {
						switch ($_v) {
							case 'italic':
								$v[$_v] = '400i';
								break;
							default:
								$v[$_v] = str_replace('italic', 'i', $_v);
						}
					} else {
						$v[$_v] = '400';
					}
				}
			}

			if (!empty($v)) {
				$s .= ':' . join(',', $v);
			}
		}

		$url .= $s;

		if (!empty($this->subsets)) {
			$url .= '&subset=' . join(',', $this->subsets);
		}

		return $url . '&display=swap';
	}

	function loop_fields($fields, $values = null, $skip_if_val_null = false, $no_selector = false, $key_name = 'name')
	{
		$listcss = array();

		foreach ((array) $fields as $field) {
			$field = wp_parse_args(
				$field,
				array(
					'selector'   => null,
					'css_format' => null,
					'type'       => null,
				)
			);

			if (!$key_name) {
				$key_name = 'name';
			}

			if (isset($field[$key_name])) {

				$key = $field[$key_name];

				$v = isset($values[$field['name']]) ? $values[$field['name']] : null;
				if (!(is_null($v) && $skip_if_val_null)) {
					if (($field['selector'] && $field['css_format']) || 'modal' == $field['type']) {
						switch ($field['type']) {
							case 'css_ruler':
								$listcss[$key] = $this->css_ruler($field, $v, $no_selector);
								break;
							case 'slider':
								$listcss[$key] = $this->slider($field, $v, $no_selector);
								break;
							case 'color':
								$listcss[$key] = $this->color($field, $v, $no_selector);
								break;
							case 'shadow':
								$listcss[$key] = $this->shadow($field, $v, $no_selector);
								break;
							case 'image':
								$listcss[$key] = $this->image($field, $v, $no_selector);
								break;
							case 'checkbox':
								if ('html_class' !== $field['css_format']) {
									$listcss[$key] = $this->checkbox($field, $v, $no_selector);
								}
								break;
							case 'text_align':
							case 'text_align_no_justify':
								$listcss[$key] = $this->text_align($field, $v, $no_selector);
								break;
							case 'font':
								$this->font($field, $v);
								break;
							case 'styling':
								$this->styling($field, $v);
								break;
							case 'columns_settings':
								$this->columns_settings($field, $v);
								break;
							case 'modal':
								$this->modal($field, $v);
								break;
							default:
								switch ($field['css_format']) {
									case 'typography':
										$this->typography($field);
										break;
									case 'html_class':
										break;
									default:
										$listcss[$key] = $this->maybe_devices_setup($field, 'setup_default', $v, $no_selector);
								}
						}
					}
				}
			} // End check field exists.
		} // end for each fields

		return $listcss;
	}

	function get_font_url()
	{
		return self::$font_url;
	}

	/**
	 * Remove space of css code.
	 *
	 * @since 1.0.0
	 * @since 0.2.6
	 *
	 * @param string $css
	 * @return tring
	 */
	function min_css($css)
	{

		if (trim($css) == '') {
			return;
		}

		$css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
		return $css;
	}

	/**
	 * Render CSS content from array customize configs.
	 *
	 * @since 0.2.5
	 *
	 * @param array $fields
	 * @return string
	 */
	public function render_css($fields = array())
	{
		$this->loop_fields($fields);
		$css_code = '';
		$i        = 0;
		foreach ($this->css as $device => $code) {
			$new_line = '';
			if ($i > 0) {
				$new_line = "\r\n/* CSS for {$device} */\r\n";
			}
			$css_code .= $new_line . sprintf($this->media_queries[$device], $code) . "\r\n";
			$i++;
		}

		$css_code = apply_filters('customify/auto-css', $css_code, $this);
		return $css_code;
	}

	/**
	 * Auto render CSS code.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * @param boolean $partial
	 * @return string
	 */
	function auto_css($partial = false)
	{
		if (!is_null(self::$code)) {
			return self::$code;
		}
		$config_fields = Customify()->customizer->get_config();
		/**
		 * Render CSS from customize configs
		 *
		 * @since  0.2.6
		 */
		$css_code = $this->render_css($config_fields);

		$url            = $this->get_google_fonts_url();
		self::$font_url = $url;
		if (defined('WP_DEBUG') && WP_DEBUG) {
			return $css_code;
		}

		return $this->min_css($css_code);
	}
}
