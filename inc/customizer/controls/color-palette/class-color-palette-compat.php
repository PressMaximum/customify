<?php
/**
 * Color Palette — backward-compatibility helpers.
 *
 * On the first request after this code ships, if the site already has
 * customizer-saved values in the legacy `styling.php` color settings AND
 * has not yet created any custom palette, automatically materialise a
 * "Theme custom" palette so the existing customizer colours continue to
 * apply through the new 6-slot pipeline.
 *
 * Idempotent: skipped on every subsequent request because the palette
 * option is then defined.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Color_Palette_Compat
{
	const SEEDED_PALETTE_ID = 'theme-custom';
	const SEEDED_PALETTE_NAME = 'Theme custom';

	/**
	 * The 9 legacy `styling.php` color settings, keyed by their default hex.
	 * Used to detect "user has customised" — a theme_mod whose saved value
	 * matches the registered default doesn't count as a customisation.
	 */
	const LEGACY_DEFAULTS = array(
		'global_styling_color_primary'    => '#235787',
		'global_styling_color_secondary'  => '#c3512f',
		'global_styling_color_text'       => '#686868',
		'global_styling_color_link'       => '#1e4b75',
		'global_styling_color_link_hover' => '#111111',
		'global_styling_color_border'     => '#eaecee',
		'global_styling_color_meta'       => '#6d6d6d',
		'global_styling_color_heading'    => '#2b2b2b',
		'global_styling_color_w_title'    => '#444444',
	);

	public static function init()
	{
		// Priority 5 — runs before any output but after WP loads core options
		// + theme_mods. The guard short-circuits on the second request so
		// the cost is one `get_option()` lookup per request thereafter.
		add_action('init', array(__CLASS__, 'maybe_seed_legacy_palette'), 5);
	}

	/**
	 * One-time bootstrap. Runs at most once per site:
	 *   - skip if the user palettes option already exists (in any state,
	 *     including empty array — user has interacted with the panel).
	 *   - skip if no legacy theme_mod was ever saved to a non-default value.
	 *   - otherwise compose a palette from the legacy values, save it as
	 *     the only entry in `customify_preview_user_palettes` and mark it
	 *     active.
	 */
	public static function maybe_seed_legacy_palette()
	{
		// Guard 1: panel options already exist? bail.
		$existing = get_option(Customify_Color_Palette_Ajax::OPTION_PALETTES, null);
		if (null !== $existing) {
			return;
		}

		// Guard 2: pull saved theme_mods, accept only values that differ
		// from the registered default. A value matching the default means
		// the user accepted the default (or never touched the control).
		$saved = array();
		foreach (self::LEGACY_DEFAULTS as $mod_key => $default_hex) {
			$value = get_theme_mod($mod_key);
			if (! is_string($value) || '' === $value) {
				continue;
			}
			if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $value)) {
				continue;
			}
			if (strcasecmp($value, $default_hex) === 0) {
				continue; // unchanged from default — not a real customisation
			}
			$saved[$mod_key] = strtoupper($value);
		}
		if (empty($saved)) {
			return;
		}

		// Compose 6 slot colors. Slots without a legacy counterpart fall
		// through to the first theme preset's colour (Ashwood by default)
		// so the palette is always complete + valid.
		$themes   = Customify_Color_Palette_Config::theme_presets();
		$fallback = ! empty($themes[0]['colors']) && is_array($themes[0]['colors'])
			? $themes[0]['colors']
			: array();

		$colors = array();
		foreach (Customify_Color_Palette_Config::SLOTS as $slot) {
			$hex = '';
			switch ($slot) {
				case 'primary':
					// Prefer explicit primary; fall back to link if user only set link.
					$hex = isset($saved['global_styling_color_primary']) ? $saved['global_styling_color_primary'] : '';
					if ('' === $hex && isset($saved['global_styling_color_link'])) {
						$hex = $saved['global_styling_color_link'];
					}
					break;
				case 'secondary':
					$hex = isset($saved['global_styling_color_secondary']) ? $saved['global_styling_color_secondary'] : '';
					break;
				case 'text':
					// Prefer body text; fall back to heading if user only customised heading.
					$hex = isset($saved['global_styling_color_text']) ? $saved['global_styling_color_text'] : '';
					if ('' === $hex && isset($saved['global_styling_color_heading'])) {
						$hex = $saved['global_styling_color_heading'];
					}
					break;
				// base / accent / surface — no legacy counterpart in styling.php.
				// Leave empty to trigger the fallback below.
			}
			if ('' === $hex && isset($fallback[$slot])) {
				$hex = $fallback[$slot];
			}
			if ('' === $hex) {
				$hex = '#FFFFFF';
			}
			$colors[$slot] = strtoupper($hex);
		}

		$palette = array(
			'id'     => self::SEEDED_PALETTE_ID,
			'name'   => self::SEEDED_PALETTE_NAME,
			'colors' => $colors,
		);

		update_option(Customify_Color_Palette_Ajax::OPTION_PALETTES, array($palette), false);

		// Only auto-activate if no active palette is set yet — respect any
		// active id the site might already have.
		$active = get_option(Customify_Color_Palette_Ajax::OPTION_ACTIVE, '');
		if ('' === $active) {
			update_option(Customify_Color_Palette_Ajax::OPTION_ACTIVE, self::SEEDED_PALETTE_ID, false);
		}
	}
}
