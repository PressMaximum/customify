<?php
/**
 * Preview Colors — dark-mode derivation + fallback chain.
 *
 * Resolves a `<slot>-dark` value through a 5-tier chain (PHASE-7-PLAN §10):
 *   L1: explicit `palette.dark[<slot>]`
 *   L2: deriveDarkSlot(palette.colors[<slot>])      (HSL transform per §5)
 *   L3: deriveDarkSlot(legacy theme_mod value)      (Phase 6 compat)
 *   L4: SCSS baseline from _skins.scss              (existing dark literals)
 *   L5: hex baseline (Midnight preset values)
 *
 * The class is pure compute + a per-request memoisation cache. Derived values
 * are NEVER persisted — a future tweak to the algorithm propagates on next
 * render without a one-shot migration.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Dark
{
	/** Per-request cache: "<slot>:<hex>" → derived hex. */
	private static $derive_cache = array();

	/** Cached baselines fixture. */
	private static $baselines = null;

	/**
	 * Map slot → legacy theme_mod key (PHASE-6-PLAN §1). Slots without a
	 * counterpart return null and skip L3 cleanly via PHP's null check.
	 */
	const LEGACY_MAP = array(
		'text'      => 'global_styling_color_text',
		'primary'   => 'global_styling_color_primary',
		'secondary' => 'global_styling_color_secondary',
		// base → no theme_mod (lives in background.php, out of scope here)
		// surface, accent → no legacy counterpart
	);

	/**
	 * Resolve the full chain for one slot. Returns the final hex; never
	 * returns null — falls through to L5 baseline as last resort.
	 *
	 * @param string $slot    one of base/text/primary/secondary/accent/surface
	 * @param array  $palette { id, name, colors{…}, dark?{…} }
	 * @return string         e.g. "#0B0D10"
	 */
	public static function resolve_slot($slot, $palette)
	{
		// L1 — explicit palette.dark
		if (isset($palette['dark'][$slot])) {
			$hex = self::normalize_hex($palette['dark'][$slot]);
			if ($hex) {
				return $hex;
			}
		}

		// L2 — derive from palette.colors
		if (isset($palette['colors'][$slot])) {
			$src = self::normalize_hex($palette['colors'][$slot]);
			if ($src) {
				return self::derive_slot($slot, $src, $palette);
			}
		}

		// L3 — derive from legacy theme_mod
		if (isset(self::LEGACY_MAP[$slot])) {
			$legacy = get_theme_mod(self::LEGACY_MAP[$slot]);
			if (is_string($legacy)) {
				$src = self::normalize_hex($legacy);
				if ($src) {
					return self::derive_slot($slot, $src, $palette);
				}
			}
		}

		// L4 — SCSS baseline (only some slots have one)
		$baselines = self::baselines();
		if (! empty($baselines['scss'][$slot])) {
			return $baselines['scss'][$slot];
		}

		// L5 — hex baseline (always present)
		return isset($baselines['hex'][$slot]) ? $baselines['hex'][$slot] : '#000000';
	}

	/**
	 * Derive all 6 dark slots for a palette in one pass. Result is keyed by
	 * slot. Used by output_root_vars() to emit the `-dark` block.
	 */
	public static function resolve_palette($palette)
	{
		$out = array();
		foreach (Customify_Preview_Colors_Config::SLOTS as $slot) {
			$out[$slot] = self::resolve_slot($slot, $palette);
		}
		return $out;
	}

	/**
	 * HSL-based derivation per PHASE-7-PLAN §5. The algorithm runs on a
	 * resolved source hex (whether from palette.colors, theme_mod, etc.).
	 */
	public static function derive_slot($slot, $src_hex, $palette = array())
	{
		$cache_key = $slot . ':' . strtolower($src_hex) . ':' . (isset($palette['id']) ? $palette['id'] : '');
		if (isset(self::$derive_cache[$cache_key])) {
			return self::$derive_cache[$cache_key];
		}

		$rgb = self::hex_to_rgb_array($src_hex);
		if (null === $rgb) {
			$baselines = self::baselines();
			return isset($baselines['hex'][$slot]) ? $baselines['hex'][$slot] : '#000000';
		}
		$hsl = self::rgb_to_hsl($rgb);
		$h = $hsl[0];
		$s = $hsl[1];
		$l = $hsl[2];

		switch ($slot) {
			case 'base':
				// Invert lightness, clamp to a near-black band; preserve hue.
				$l = self::clamp(100 - $l, 5, 12);
				break;

			case 'text':
				// Invert lightness, near-white band; cap saturation so warm
				// inks don't read as colour casts on dark.
				$l = self::clamp(100 - $l, 88, 96);
				$s = min($s, 30);
				break;

			case 'surface':
				// Lift base_dark by +6% lightness (cap at 18 so card stays
				// distinct from canvas without becoming "light surface").
				$base_src = isset($palette['colors']['base']) ? self::normalize_hex($palette['colors']['base']) : '';
				$base_dark_hex = $base_src ? self::derive_slot('base', $base_src, $palette) : self::baselines()['hex']['base'];
				$base_rgb = self::hex_to_rgb_array($base_dark_hex);
				$base_hsl = self::rgb_to_hsl($base_rgb);
				$h = $base_hsl[0];
				$s = $base_hsl[1];
				$l = min($base_hsl[2] + 6, 18);
				break;

			case 'primary':
				// Boost lightness into a band that maintains AA contrast on
				// dark surfaces. Hue + saturation preserved for brand fidelity.
				$l = self::clamp($l, 55, 70);
				break;

			case 'secondary':
				// Auto-flip based on luminance vs the (light-mode) surface:
				// if the source secondary is darker than surface (typical
				// "dark band" usage), invert to produce a light band on dark;
				// otherwise nudge slightly darker but stay above base_dark.
				$surface_hex = isset($palette['colors']['surface'])
					? self::normalize_hex($palette['colors']['surface'])
					: '#FFFFFF';
				$surface_rgb = self::hex_to_rgb_array($surface_hex);
				$lum_src = self::luminance($rgb);
				$lum_surface = $surface_rgb ? self::luminance($surface_rgb) : 1.0;
				if ($lum_src < $lum_surface) {
					$l = self::clamp(100 - $l, 80, 95);
					$s = max($s - 10, 0);
				} else {
					$l = max($l - 10, 20);
				}
				break;

			case 'accent':
				// Boost saturation, mid-range lightness. Decorative slot
				// needs to pop visibly on dark canvas without screaming.
				$s = min($s + 10, 95);
				$l = self::clamp($l, 55, 80);
				break;
		}

		$out_rgb = self::hsl_to_rgb(array($h, $s, $l));
		$out_hex = self::rgb_array_to_hex($out_rgb);

		self::$derive_cache[$cache_key] = $out_hex;
		return $out_hex;
	}

	/**
	 * Load + cache the baselines fixture. Source of truth for L4 (SCSS) and
	 * L5 (hex). Shared between PHP and JS via wp_localize_script.
	 */
	public static function baselines()
	{
		if (null !== self::$baselines) {
			return self::$baselines;
		}

		$path = __DIR__ . '/dark-baselines.json';
		$json = is_readable($path) ? file_get_contents($path) : '';
		$decoded = $json ? json_decode($json, true) : null;

		if (! is_array($decoded) || empty($decoded['hex'])) {
			// Last-resort hardcoded baseline — used only if the JSON file
			// is missing/corrupt. Mirror the values in dark-baselines.json
			// so removing this fallback would not change behaviour.
			$decoded = array(
				'scss' => array(
					'base' => '#1A1A1A', 'text' => '#FCFCFC',
					'primary' => '#FCFCFC', 'secondary' => null,
					'accent' => null, 'surface' => '#1F1F1F',
				),
				'hex' => array(
					'base' => '#0B0D10', 'text' => '#F2F0EB',
					'primary' => '#FF7A45', 'secondary' => '#FFFFFF',
					'accent' => '#FFD36A', 'surface' => '#1C1F26',
				),
			);
		}

		self::$baselines = $decoded;
		return self::$baselines;
	}

	// ──────────────────────────────────────────────────────── colour helpers

	private static function normalize_hex($hex)
	{
		$h = ltrim((string) $hex, '#');
		if (3 === strlen($h)) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		return preg_match('/^[0-9A-Fa-f]{6}$/', $h) ? '#' . strtoupper($h) : '';
	}

	private static function hex_to_rgb_array($hex)
	{
		$h = ltrim((string) $hex, '#');
		if (3 === strlen($h)) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if (! preg_match('/^[0-9A-Fa-f]{6}$/', $h)) {
			return null;
		}
		return array(hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)));
	}

	private static function rgb_array_to_hex($rgb)
	{
		return sprintf(
			'#%02X%02X%02X',
			max(0, min(255, (int) round($rgb[0]))),
			max(0, min(255, (int) round($rgb[1]))),
			max(0, min(255, (int) round($rgb[2])))
		);
	}

	/** WCAG relative luminance, 0..1. */
	private static function luminance($rgb)
	{
		$f = function ($v) {
			$v /= 255;
			return $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
		};
		return 0.2126 * $f($rgb[0]) + 0.7152 * $f($rgb[1]) + 0.0722 * $f($rgb[2]);
	}

	/** Convert RGB triplet (0..255) → HSL ([0..360, 0..100, 0..100]). */
	private static function rgb_to_hsl($rgb)
	{
		$r = $rgb[0] / 255;
		$g = $rgb[1] / 255;
		$b = $rgb[2] / 255;
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$l = ($max + $min) / 2;
		if ($max === $min) {
			return array(0.0, 0.0, $l * 100);
		}
		$d = $max - $min;
		$s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
		switch ($max) {
			case $r:
				$h = ($g - $b) / $d + ($g < $b ? 6 : 0);
				break;
			case $g:
				$h = ($b - $r) / $d + 2;
				break;
			default: // $b
				$h = ($r - $g) / $d + 4;
				break;
		}
		$h *= 60;
		return array($h, $s * 100, $l * 100);
	}

	/** HSL → RGB triplet 0..255. */
	private static function hsl_to_rgb($hsl)
	{
		$h = $hsl[0] / 360;
		$s = $hsl[1] / 100;
		$l = $hsl[2] / 100;
		if (0 === $s) {
			$v = $l * 255;
			return array($v, $v, $v);
		}
		$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
		$p = 2 * $l - $q;
		$hue2rgb = function ($p, $q, $t) {
			if ($t < 0) $t += 1;
			if ($t > 1) $t -= 1;
			if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
			if ($t < 1 / 2) return $q;
			if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
			return $p;
		};
		return array(
			$hue2rgb($p, $q, $h + 1 / 3) * 255,
			$hue2rgb($p, $q, $h) * 255,
			$hue2rgb($p, $q, $h - 1 / 3) * 255,
		);
	}

	private static function clamp($v, $min, $max)
	{
		return max($min, min($max, $v));
	}
}
