<?php
/**
 * Preview Colors — config (slots, descriptions, theme presets, settings rows).
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Config
{
	// Default 6-slot vocabulary (Style Pack). Themes / demos can hide any of
	// `secondary` / `accent` via the filter — `base`, `text`, `primary`,
	// `surface` are mandatory per the matrix and never removed.
	const SLOTS = array('base', 'text', 'primary', 'secondary', 'accent', 'surface');
	const MANDATORY_SLOTS = array('base', 'text', 'primary', 'surface');

	/**
	 * Active slots for the current request, filtered through
	 * `customify_preview_colors/active_slots`. Always preserves the four
	 * mandatory slots; only `secondary` / `accent` can be opted out.
	 *
	 * @return array
	 */
	public static function slots()
	{
		$filtered = apply_filters('customify_preview_colors/active_slots', self::SLOTS);
		if (! is_array($filtered) || empty($filtered)) {
			return self::SLOTS;
		}
		// Make sure mandatory slots stay even if a filter forgets them.
		$out = array_values(array_unique(array_merge(self::MANDATORY_SLOTS, $filtered)));
		// Preserve canonical ordering so JS deck lays out predictably.
		return array_values(array_filter(self::SLOTS, function ($s) use ($out) {
			return in_array($s, $out, true);
		}));
	}

	public static function slot_descriptions()
	{
		// Slot descriptions render as HTML inside the panel popover via
		// `dangerouslySetInnerHTML` — translators must preserve the <strong> tags.
		return apply_filters('customify_preview_colors/slot_descriptions', array(
			'base'      => __('<strong>Page background.</strong> Default canvas for body, neutral sections, form wrappers.', 'customify'),
			'text'      => __('<strong>Ink foreground.</strong> Body text, headings (H1-H6), labels, nav links at rest.', 'customify'),
			'primary'   => __('<strong>Brand action.</strong> CTA buttons, links, focus rings, active tabs, accent words.', 'customify'),
			'surface'   => __('<strong>Elevated container.</strong> Card backgrounds, modals, dropdowns, tooltips.', 'customify'),
			'secondary' => __('<strong>Dark sectional band.</strong> Callouts, footer, dark header, secondary buttons.', 'customify'),
			'accent'    => __('<strong>Decorative pop.</strong> Badges, sticky notes, stat numbers, hover glow, sale tags.', 'customify'),
		));
	}

	public static function theme_presets()
	{
		return apply_filters('customify_preview_colors/theme_presets', array(
			array(
				'id'     => 'ashwood',
				'name'   => _x('Ashwood', 'palette name', 'customify'),
				'colors' => array(
					'base' => '#F9F3E4', 'text' => '#1A3A28', 'primary' => '#B35932',
					'secondary' => '#1C2147', 'accent' => '#F5DE9A', 'surface' => '#FFFFFF',
				),
				'dark' => array(
					'base' => '#1A1410', 'text' => '#F2EAD8', 'primary' => '#E07A4F',
					'secondary' => '#E8DCC4', 'accent' => '#FFD56B', 'surface' => '#28201A',
				),
			),
			array(
				'id'     => 'midnight',
				'name'   => _x('Midnight', 'palette name', 'customify'),
				'colors' => array(
					'base' => '#0B0D10', 'text' => '#F2F0EB', 'primary' => '#FF7A45',
					'secondary' => '#FFFFFF', 'accent' => '#FFD36A', 'surface' => '#1C1F26',
				),
				// Already a dark design — `dark` mirrors `colors` so toggling
				// the trigger class produces no visual change (correct for
				// palettes that ARE dark).
				'dark' => array(
					'base' => '#0B0D10', 'text' => '#F2F0EB', 'primary' => '#FF7A45',
					'secondary' => '#FFFFFF', 'accent' => '#FFD36A', 'surface' => '#1C1F26',
				),
			),
			array(
				'id'     => 'ocean',
				'name'   => _x('Ocean', 'palette name', 'customify'),
				'colors' => array(
					'base' => '#F5F6F4', 'text' => '#0F1C33', 'primary' => '#0055FF',
					'secondary' => '#001D4A', 'accent' => '#B8E6FF', 'surface' => '#FFFFFF',
				),
				'dark' => array(
					'base' => '#0A1124', 'text' => '#E5ECF7', 'primary' => '#3D8BFF',
					'secondary' => '#FFFFFF', 'accent' => '#7AB8FF', 'surface' => '#152043',
				),
			),
			array(
				'id'     => 'moss',
				'name'   => _x('Moss', 'palette name', 'customify'),
				'colors' => array(
					'base' => '#F4FAF5', 'text' => '#0F2616', 'primary' => '#2B9348',
					'secondary' => '#2B3D28', 'accent' => '#D9F0B5', 'surface' => '#FFFFFF',
				),
				'dark' => array(
					'base' => '#0B1A11', 'text' => '#E7F3EB', 'primary' => '#52B86C',
					'secondary' => '#E0EAD9', 'accent' => '#A8D87E', 'surface' => '#162619',
				),
			),
		));
	}

	// Hardcoded default. Override via the
	// `customify_preview_colors/default_active_id` filter rather than
	// editing this constant — see `default_active_id()` below.
	const DEFAULT_ACTIVE_ID = 'ashwood';

	/**
	 * Default active palette id. Used by:
	 *   - the Customizer setting's `'default'` value
	 *   - `Customify_Preview_Colors_Ajax::get_active_id()` fallback
	 * so React panel can highlight the right card on first load, and the
	 * visitor-side `:root` block paints from the get-go.
	 *
	 * Resolution order:
	 *   1. `customify_preview_colors/default_active_id` filter return —
	 *      validated against the available presets, dropped if unknown.
	 *   2. `DEFAULT_ACTIVE_ID` constant (`'ashwood'`) — same validation.
	 *   3. First entry in the theme_presets list (last-resort safety net
	 *      so a child theme that renames `'ashwood'` doesn't break).
	 *
	 * Override example (in a child theme functions.php):
	 *   add_filter('customify_preview_colors/default_active_id',
	 *       function () { return 'midnight'; });
	 *
	 * @return string
	 */
	public static function default_active_id()
	{
		$presets = self::theme_presets();
		$ids = array();
		foreach ($presets as $p) {
			if (! empty($p['id']) && is_string($p['id'])) {
				$ids[] = $p['id'];
			}
		}
		if (empty($ids)) {
			return '';
		}

		$candidate = apply_filters('customify_preview_colors/default_active_id', self::DEFAULT_ACTIVE_ID);
		if (is_string($candidate) && in_array($candidate, $ids, true)) {
			return $candidate;
		}
		// Filter returned junk OR DEFAULT_ACTIVE_ID is missing from the
		// presets list (renamed by a child theme) → fall through to the
		// first available id so callers always get a usable value.
		return $ids[0];
	}

	public static function settings_rows()
	{
		// `sublabel` mirrors the slot id and is rendered as-is in the panel
		// (used as a small hint chip), so it stays untranslated.
		return apply_filters('customify_preview_colors/settings_rows', array(
			array('label' => __('Text & ink', 'customify'),      'sublabel' => 'text',      'slots' => array('text')),
			array('label' => __('Primary action', 'customify'),  'sublabel' => 'primary',   'slots' => array('primary')),
			array('label' => __('Page background', 'customify'), 'sublabel' => 'base',      'slots' => array('base')),
			array('label' => __('Card surface', 'customify'),    'sublabel' => 'surface',   'slots' => array('surface')),
			array('label' => __('Dark section', 'customify'),    'sublabel' => 'secondary', 'slots' => array('secondary')),
			array('label' => __('Highlight / pop', 'customify'), 'sublabel' => 'accent',    'slots' => array('accent')),
		));
	}
}
