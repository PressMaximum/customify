<?php
/**
 * Preview Colors — option storage + sanitiser helpers.
 *
 * Persistence layer for the panel-managed colour palettes. Constants here
 * are referenced from compat / demo-import / Customizer setting registration.
 * The class kept its `_Ajax` suffix for backward-compat after the frontend
 * overlay (which originally used these as AJAX endpoints) was removed —
 * persistence now flows entirely through the Customizer Publish pipeline.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Ajax
{
	const OPTION_PALETTES = 'customify_preview_user_palettes';
	const OPTION_ACTIVE   = 'customify_preview_active_palette';
	// Reading + writing the palette options both require `manage_options`
	// (Administrator only — Editor's `edit_theme_options` is not enough).
	// Applied to the Customizer setting `capability` field.
	const CAPABILITY      = 'manage_options';

	public static function get_user_palettes()
	{
		$raw = get_option(self::OPTION_PALETTES, array());
		if (! is_array($raw)) {
			return array();
		}
		return self::sanitize_palettes($raw);
	}

	public static function get_active_id()
	{
		$id = get_option(self::OPTION_ACTIVE, '');
		$id = is_string($id) ? sanitize_key($id) : '';
		// Empty option (fresh install, never saved) → default to the first
		// theme preset (`'ashwood'`) so the React panel can highlight a
		// card and the visitor-side `:root` block always paints something.
		if ('' === $id) {
			$id = Customify_Preview_Colors_Config::default_active_id();
		}
		return $id;
	}

	/**
	 * Sanitize a palette id (used as the active-palette setting value).
	 * Public for reuse by the Customizer setting's sanitize_callback.
	 *
	 * @param mixed $id
	 * @return string
	 */
	public static function sanitize_active_id($id)
	{
		return is_string($id) ? sanitize_key($id) : '';
	}

	/**
	 * Sanitise the user palettes array. Public so it can be reused as the
	 * Customizer setting's `sanitize_callback` (which writes the same option
	 * key) and by the demo-import preloader.
	 *
	 * @param mixed $items
	 * @return array
	 */
	public static function sanitize_palettes($items)
	{
		$out = array();
		foreach ($items as $item) {
			if (! is_array($item)) {
				continue;
			}
			$id   = isset($item['id']) ? sanitize_key($item['id']) : '';
			$name = isset($item['name']) ? sanitize_text_field($item['name']) : '';
			$cols = isset($item['colors']) && is_array($item['colors']) ? $item['colors'] : array();
			if ('' === $id || '' === $name) {
				continue;
			}
			// Sanitize against the FULL six-slot vocabulary (not the filtered
			// active set) so palettes saved while a slot is hidden still
			// validate when the slot is re-enabled later. Missing slot values
			// are rejected — the panel JS always writes all six.
			$colors = array();
			foreach (Customify_Preview_Colors_Config::SLOTS as $slot) {
				$val = isset($cols[$slot]) ? trim((string) $cols[$slot]) : '';
				if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $val)) {
					continue 2;
				}
				$colors[$slot] = strtoupper($val);
			}

			// Optional `dark` companion (PHASE-7-PLAN §3). Partial entries
			// are allowed — slots without an explicit dark hex fall through
			// to auto-derivation in Customify_Preview_Colors_Dark.
			$dark = array();
			if (isset($item['dark']) && is_array($item['dark'])) {
				foreach (Customify_Preview_Colors_Config::SLOTS as $slot) {
					$val = isset($item['dark'][$slot]) ? trim((string) $item['dark'][$slot]) : '';
					if ('' === $val) {
						continue;
					}
					if (preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $val)) {
						$dark[$slot] = strtoupper($val);
					}
				}
			}

			$entry = array('id' => $id, 'name' => $name, 'colors' => $colors);
			if (! empty($dark)) {
				$entry['dark'] = $dark;
			}
			$out[] = $entry;
		}
		return $out;
	}
}
