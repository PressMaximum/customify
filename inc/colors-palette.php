<?php
/**
 * Customify Colors palette — :root CSS variable emitter.
 *
 * Emits a `:root` block declaring the 6 slot vars and ~10 derived vars used by
 * the new Colors panel. Derived vars use a static (PHP-precomputed) fallback
 * plus an `@supports (color-mix)` refinement so modern browsers can live-mix
 * while older browsers still see a sensible static color.
 *
 * Override mechanism: a derived var is only emitted as "computed" when its
 * corresponding legacy theme_mod key has NO saved value. If the legacy key is
 * saved, the derived var is locked to the saved value so 30K+ legacy sites
 * render byte-identical to before.
 *
 * @package Customify
 */

defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────────────────────────────
// Color math helpers (sRGB-space; good enough for fallback hex).
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_normalize_hex' ) ) {
	/**
	 * Validate + normalize a color value. Accepts:
	 *   • 3- or 6-char hex (with or without leading #) → returned as #rrggbb
	 *   • rgb(r,g,b) and rgba(r,g,b,a) → returned as-is (alpha preserved)
	 *
	 * Returns $fallback for anything else (empty string, invalid chars,
	 * named colors, var(), hsl, etc.).
	 *
	 * Name kept as `normalize_hex` for backcompat — it's been the slot
	 * reader on every install since Phase 2 launched. The rgba support
	 * added later (Phase 2.10) lets the WP color picker's alpha slider
	 * round-trip correctly: when user picks a transparent brand color,
	 * the rgba string survives the slot read so :root --customify-primary
	 * gets the actual rgba value (not a hex fallback) — and downstream
	 * helpers (hex_to_rgb, relative_luminance, pick_on) handle rgba by
	 * stripping the alpha for luminance math.
	 *
	 * Use this on every read of a user-saved color value before feeding it into
	 * CSS output or math helpers — wp-cli and external code can bypass the
	 * Customizer sanitize_callback and write raw values.
	 */
	function customify_color_normalize_hex( $value, $fallback = '#000000' ) {
		if ( ! is_string( $value ) ) {
			return $fallback;
		}
		$value = trim( $value );
		// Accept rgb()/rgba() syntax — returned verbatim (preserves alpha).
		// Anchor the trailing `)` so partial inputs like `rgba(255,255,255`
		// (cut off) don't pass the validator. composite_over already does
		// this; keeping the regexes consistent across helpers.
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*[\d.]+)?\s*\)$/i', $value ) ) {
			return $value;
		}
		$hex = ltrim( $value, '#' );
		if ( strlen( $hex ) === 3 && ctype_xdigit( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return $fallback;
		}
		return '#' . strtolower( $hex );
	}
}

if ( ! function_exists( 'customify_color_hex_to_rgb' ) ) {
	/**
	 * Parse a color string to [r, g, b] integer triplet (0-255 each).
	 * Accepts both hex (#rrggbb / #rgb) and rgb()/rgba() forms — alpha
	 * is ignored. The function name keeps the legacy `hex_to_rgb` for
	 * backcompat with downstream callers; new rgba support means the
	 * on-* / container / border-strong derivations don't silently drop
	 * to [0,0,0] when the user picks a transparent brand color.
	 *
	 * Returns [0, 0, 0] for invalid input — math helpers downstream
	 * handle that as black (luminance 0).
	 */
	function customify_color_hex_to_rgb( $value ) {
		$value = (string) $value;
		// rgb()/rgba() form — capture first 3 channel ints, ignore alpha.
		// Anchor trailing `)` so cut-off inputs don't pass — keeps the
		// regex consistent with normalize_hex and composite_over.
		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*[\d.]+)?\s*\)$/i', $value, $m ) ) {
			return array(
				max( 0, min( 255, (int) $m[1] ) ),
				max( 0, min( 255, (int) $m[2] ) ),
				max( 0, min( 255, (int) $m[3] ) ),
			);
		}
		// Hex form.
		$hex = ltrim( $value, '#' );
		if ( strlen( $hex ) === 3 && ctype_xdigit( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return array( 0, 0, 0 );
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}

if ( ! function_exists( 'customify_color_rgb_to_hex' ) ) {
	function customify_color_rgb_to_hex( $rgb ) {
		return sprintf( '#%02x%02x%02x',
			max( 0, min( 255, (int) round( $rgb[0] ) ) ),
			max( 0, min( 255, (int) round( $rgb[1] ) ) ),
			max( 0, min( 255, (int) round( $rgb[2] ) ) )
		);
	}
}

if ( ! function_exists( 'customify_color_mix_hex' ) ) {
	/**
	 * Mix two hex colors in sRGB space.
	 *
	 * @param string $a       First color hex.
	 * @param string $b       Second color hex.
	 * @param float  $weight_a Weight of color A in [0,1]. 1.0 = pure A, 0.0 = pure B.
	 * @return string Mixed hex.
	 */
	function customify_color_mix_hex( $a, $b, $weight_a ) {
		$weight_a = max( 0.0, min( 1.0, (float) $weight_a ) );
		$wb       = 1.0 - $weight_a;
		$ra       = customify_color_hex_to_rgb( $a );
		$rb       = customify_color_hex_to_rgb( $b );
		return customify_color_rgb_to_hex( array(
			$ra[0] * $weight_a + $rb[0] * $wb,
			$ra[1] * $weight_a + $rb[1] * $wb,
			$ra[2] * $weight_a + $rb[2] * $wb,
		) );
	}
}

if ( ! function_exists( 'customify_color_relative_luminance' ) ) {
	function customify_color_relative_luminance( $hex ) {
		list( $r, $g, $b ) = customify_color_hex_to_rgb( $hex );
		$f = function ( $v ) {
			$v = $v / 255;
			return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
		};
		return 0.2126 * $f( $r ) + 0.7152 * $f( $g ) + 0.0722 * $f( $b );
	}
}

if ( ! function_exists( 'customify_color_composite_over' ) ) {
	/**
	 * Composite a (possibly-transparent) color over an opaque base. Returns
	 * the [r, g, b] of what would actually be RENDERED if `$value` were
	 * painted on top of `$base_hex`.
	 *
	 * Why: when a user picks rgba(brand, alpha=0.14) for the Primary slot
	 * in the WP color picker, the BUTTON background is rgba composited
	 * over the page bg (the user's saved Base, usually white). The on-*
	 * WCAG safety pick needs to contrast against THAT composite, not
	 * against the opaque rgb component of the rgba (which would still be
	 * the dark brand color and incorrectly pick white text).
	 *
	 * For opaque values (hex / rgb / rgba alpha=1) the function returns
	 * the rgb triplet unchanged — equivalent to the bare hex_to_rgb call.
	 *
	 * @param string $value     Any color string accepted by hex_to_rgb plus rgba().
	 * @param string $base_hex  The opaque background to composite over (usually slot.base).
	 * @return array [r, g, b] integer triplet 0-255.
	 */
	function customify_color_composite_over( $value, $base_hex ) {
		$value = (string) $value;
		// rgba() with explicit alpha — composite.
		if ( preg_match( '/^rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([\d.]+)\s*\)/i', $value, $m ) ) {
			$r = max( 0, min( 255, (int) $m[1] ) );
			$g = max( 0, min( 255, (int) $m[2] ) );
			$b = max( 0, min( 255, (int) $m[3] ) );
			$a = max( 0.0, min( 1.0, (float) $m[4] ) );
			if ( $a >= 1.0 ) {
				return array( $r, $g, $b );
			}
			// When base_hex is invalid (returns [0,0,0] from hex_to_rgb),
			// substitute white instead — base is overwhelmingly the page
			// background, so an invalid/empty base reading composites the
			// rgba over the visual "page color" that most users have. Same
			// fallback as the JS mirror (_compositeOver) for parity.
			$base_rgb = customify_color_hex_to_rgb( $base_hex );
			if ( 0 === $base_rgb[0] && 0 === $base_rgb[1] && 0 === $base_rgb[2] && '#000000' !== strtolower( (string) $base_hex ) ) {
				$base_rgb = array( 255, 255, 255 );
			}
			return array(
				(int) round( $r * $a + $base_rgb[0] * ( 1 - $a ) ),
				(int) round( $g * $a + $base_rgb[1] * ( 1 - $a ) ),
				(int) round( $b * $a + $base_rgb[2] * ( 1 - $a ) ),
			);
		}
		// Opaque (hex or rgb without alpha) — passthrough.
		return customify_color_hex_to_rgb( $value );
	}
}

if ( ! function_exists( 'customify_color_wcag_contrast' ) ) {
	/**
	 * WCAG 2.x contrast ratio between two hex colors. Returns a value in
	 * [1.0, 21.0] where higher = more contrast. Used as the foundation
	 * for max-contrast picks (§3) and L-reduction loops (§5) per the
	 * color-token-derivation spec.
	 */
	function customify_color_wcag_contrast( $a_hex, $b_hex ) {
		$la = customify_color_relative_luminance( $a_hex );
		$lb = customify_color_relative_luminance( $b_hex );
		$hi = max( $la, $lb );
		$lo = min( $la, $lb );
		return ( $hi + 0.05 ) / ( $lo + 0.05 );
	}
}

if ( ! function_exists( 'customify_color_pick_on' ) ) {
	/**
	 * Pick max-contrast text color (#FFFFFF or #1A1A1A) for a given background.
	 *
	 * Spec §3: `on-X = contrast(LIGHT, X') >= contrast(DARK, X') ? LIGHT : DARK`
	 * where X' is the EFFECTIVE rendered color — for rgba inputs X' is the
	 * composite over the page base (since that's what the user sees behind
	 * the text), NOT the opaque rgb component of the rgba.
	 *
	 * Max-contrast pick — pick whichever of #FFFFFF / #1A1A1A has higher
	 * WCAG contrast against the effective bg. More robust than a fixed
	 * luminance threshold (e.g. `> 0.45`), which silently picks white on
	 * medium-tone colors where black would be more readable.
	 *
	 * Example 1 — teal #3CAA9D
	 *   • Threshold (luminance > 0.45): luminance ≈ 0.34 → returns white →
	 *     contrast 2.87 FAILS WCAG.
	 *   • Max-contrast: contrast(white, teal)=2.87 vs contrast(black, teal)=6.16
	 *     → returns black → PASSES.
	 *
	 * Example 2 — rgba(17,52,109,0.14) on white base
	 *   • Opaque rgb component: (17,52,109) — dark navy → max-contrast picks
	 *     WHITE (correct against opaque navy, WRONG against rendered output).
	 *   • Effective composite over white: ~(220,225,233) — very light blue
	 *     → max-contrast picks DARK ✓ matches what the user actually sees.
	 *
	 * @param string $bg_value Color string (hex, rgb, or rgba).
	 * @param string $base_hex Opaque base to composite over for rgba inputs.
	 *                        Defaults to #FFFFFF (white) — pass the saved
	 *                        Palette Base for accurate dark-mode pick.
	 * @return string '#FFFFFF' or '#1A1A1A'.
	 */
	function customify_color_pick_on( $bg_value, $base_hex = '#FFFFFF' ) {
		$rgb        = customify_color_composite_over( $bg_value, $base_hex );
		$effective  = customify_color_rgb_to_hex( $rgb );
		$c_light    = customify_color_wcag_contrast( '#FFFFFF', $effective );
		$c_dark     = customify_color_wcag_contrast( '#1A1A1A', $effective );
		return $c_light >= $c_dark ? '#FFFFFF' : '#1A1A1A';
	}
}

if ( ! function_exists( 'customify_color_srgb_to_oklab' ) ) {
	/**
	 * Convert sRGB hex → OKLab (L, a, b). Standard Ottosson transform.
	 * L is in roughly [0, 1] (0 = black, 1 = white). a, b are unbounded
	 * chromaticity channels.
	 *
	 * Used by §4 (container P solve) — measures L of source + base to
	 * solve the percentage that lands the container at TARGET_CONTAINER_L.
	 * Used by §5 (on-container L-reduction) — keeps a, b (hue) constant
	 * while stepping L down until contrast meets AA.
	 */
	function customify_color_srgb_to_oklab( $hex ) {
		list( $r, $g, $b ) = customify_color_hex_to_rgb( $hex );
		// sRGB → linear.
		$f = function ( $v ) {
			$v = $v / 255;
			return $v <= 0.04045 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
		};
		$rl = $f( $r );
		$gl = $f( $g );
		$bl = $f( $b );
		// Linear sRGB → LMS (Ottosson).
		$l = 0.4122214708 * $rl + 0.5363325363 * $gl + 0.0514459929 * $bl;
		$m = 0.2119034982 * $rl + 0.6806995451 * $gl + 0.1073969566 * $bl;
		$s = 0.0883024619 * $rl + 0.2817188376 * $gl + 0.6299787005 * $bl;
		// Cube-root.
		$l_ = $l < 0 ? -pow( -$l, 1.0 / 3.0 ) : pow( $l, 1.0 / 3.0 );
		$m_ = $m < 0 ? -pow( -$m, 1.0 / 3.0 ) : pow( $m, 1.0 / 3.0 );
		$s_ = $s < 0 ? -pow( -$s, 1.0 / 3.0 ) : pow( $s, 1.0 / 3.0 );
		// LMS' → OKLab.
		return array(
			0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_, // L
			1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_, // a
			0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_, // b
		);
	}
}

if ( ! function_exists( 'customify_color_oklab_to_srgb' ) ) {
	/**
	 * Convert OKLab (L, a, b) → sRGB hex. Inverse of srgb_to_oklab.
	 * Clamps the output to valid sRGB [0, 255] range so out-of-gamut
	 * results don't crash — they get pinned to the nearest representable
	 * color, which is acceptable for our derivation use case.
	 */
	function customify_color_oklab_to_srgb( $oklab ) {
		list( $L, $a, $b ) = $oklab;
		// OKLab → LMS'.
		$l_ = $L + 0.3963377774 * $a + 0.2158037573 * $b;
		$m_ = $L - 0.1055613458 * $a - 0.0638541728 * $b;
		$s_ = $L - 0.0894841775 * $a - 1.2914855480 * $b;
		// Cube.
		$l = $l_ * $l_ * $l_;
		$m = $m_ * $m_ * $m_;
		$s = $s_ * $s_ * $s_;
		// LMS → linear sRGB.
		$rl =  4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
		$gl = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
		$bl = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;
		// Linear → sRGB.
		$g = function ( $v ) {
			$v = max( 0.0, min( 1.0, $v ) );
			return $v <= 0.0031308 ? $v * 12.92 : 1.055 * pow( $v, 1.0 / 2.4 ) - 0.055;
		};
		return customify_color_rgb_to_hex( array(
			$g( $rl ) * 255,
			$g( $gl ) * 255,
			$g( $bl ) * 255,
		) );
	}
}

if ( ! function_exists( 'customify_color_oklab_l' ) ) {
	/**
	 * Extract just the OKLab L channel (lightness) from a hex color.
	 * Thin wrapper used by §4 P-solve readability.
	 */
	function customify_color_oklab_l( $hex ) {
		$oklab = customify_color_srgb_to_oklab( $hex );
		return $oklab[0];
	}
}

if ( ! function_exists( 'customify_color_solve_border_strong' ) ) {
	/**
	 * Spec §2 — solve P (text-vs-base mix percentage) such that the
	 * resulting color has WCAG contrast ≥ 3.0 against base.
	 *
	 * UI_CONTRAST = 3.0 per WCAG 1.4.11 for non-text content (form input
	 * borders, button outlines — anything where the boundary is the only
	 * cue that there's a control).
	 *
	 * Iterates P from 6% upward in 1% steps (closed-form solve is messy
	 * across the sRGB gamma curve; iterative is simpler and fast). For
	 * the default (text=#2b2b2b, base=#FFFFFF) palette lands ~47% → ~#949494.
	 */
	function customify_color_solve_border_strong( $text_hex, $base_hex ) {
		for ( $p = 6; $p <= 100; $p++ ) {
			$mix      = customify_color_mix_hex( $text_hex, $base_hex, $p / 100 );
			$contrast = customify_color_wcag_contrast( $mix, $base_hex );
			if ( $contrast >= 3.0 ) {
				return $mix;
			}
		}
		// Fallback if base ≈ text (shouldn't happen with sane palettes).
		return $text_hex;
	}
}

if ( ! function_exists( 'customify_color_chroma_cap_oklab' ) ) {
	/**
	 * Cap a color's OKLab chroma (= sqrt(a² + b²)) to a maximum value
	 * while preserving its L and hue direction.
	 *
	 * Used to tame high-chroma brand colors (notably yellow / lime /
	 * neon) when computing container tints. The spec §4 formula lands
	 * containers at OKLab L = 0.93 but doesn't adjust chroma — for a
	 * dark navy brand the OKLab-mix with white naturally desaturates
	 * to a soft blue-grey (chroma ~0.01), but for a yellow brand the
	 * mix preserves most of yellow's chroma (~0.10) because yellow is
	 * already perceptually light. Result: yellow's container reads
	 * "still very yellow" instead of "soft cream", breaking the badge
	 * aesthetic that the container pattern targets.
	 *
	 * Capping chroma to ~0.04 produces a cream/peach feel for high-
	 * chroma brands while leaving low-chroma brands unaffected (their
	 * container chroma is already well under the cap).
	 *
	 * @param string $hex
	 * @param float  $max_chroma Maximum allowed sqrt(a² + b²) in OKLab.
	 * @return string Capped hex (same color if already under cap).
	 */
	function customify_color_chroma_cap_oklab( $hex, $max_chroma ) {
		$oklab  = customify_color_srgb_to_oklab( $hex );
		$L      = $oklab[0];
		$a      = $oklab[1];
		$b      = $oklab[2];
		$chroma = sqrt( $a * $a + $b * $b );
		if ( $chroma <= $max_chroma ) {
			return customify_color_normalize_hex( $hex, $hex );
		}
		$scale = $max_chroma / $chroma;
		return customify_color_oklab_to_srgb( array( $L, $a * $scale, $b * $scale ) );
	}
}

if ( ! function_exists( 'customify_color_solve_container_p' ) ) {
	/**
	 * Spec §4 — closed-form solve for the percentage P that lands a tint
	 * of `source` mixed with `base` at OKLab lightness TARGET_CONTAINER_L
	 * (= 0.93, the perceptual lightness for soft-tint badges).
	 *
	 *   P = clamp( (TARGET_L - L_oklab(base)) / (L_oklab(source) - L_oklab(base)),
	 *              0.02, 0.98 )
	 *
	 * Returns a float in [0.02, 0.98] (the percentage as a 0..1 fraction).
	 * Multiply by 100 when emitting into a `color-mix(in oklab, A {P}%, B)`
	 * expression. Clamped so we never hit pathological 0%/100% mixes.
	 */
	function customify_color_solve_container_p( $source_hex, $base_hex, $target_l = 0.93 ) {
		$l_source = customify_color_oklab_l( $source_hex );
		$l_base   = customify_color_oklab_l( $base_hex );
		$denom    = $l_source - $l_base;
		if ( abs( $denom ) < 1e-6 ) {
			// source ≈ base lightness → can't make a meaningful tint. Pin to 50%.
			return 0.5;
		}
		$p = ( $target_l - $l_base ) / $denom;
		return max( 0.02, min( 0.98, $p ) );
	}
}

if ( ! function_exists( 'customify_color_l_reduce_until_contrast' ) ) {
	/**
	 * Spec §5 — keep source hue (a, b) in OKLab, step L downward until the
	 * resulting color has contrast ≥ $target_contrast against $bg_hex.
	 *
	 * Used to produce `on-X-container` from a brand color X. For a dark
	 * brand (primary navy), the loop barely steps L because contrast is
	 * already adequate → result ≈ source. For a light brand (accent
	 * yellow), L drops significantly → dark gold result.
	 *
	 * If no L in [0, source_L] passes the target, returns #1A1A1A as the
	 * unconditional fallback.
	 */
	function customify_color_l_reduce_until_contrast( $source_hex, $bg_hex, $target_contrast = 4.5 ) {
		$oklab = customify_color_srgb_to_oklab( $source_hex );
		$L     = $oklab[0];
		$a     = $oklab[1];
		$b     = $oklab[2];
		// Step L downward in 0.02 increments (50 steps from L=1 to L=0 worst case).
		while ( $L > 0 ) {
			$candidate = customify_color_oklab_to_srgb( array( $L, $a, $b ) );
			$contrast  = customify_color_wcag_contrast( $candidate, $bg_hex );
			if ( $contrast >= $target_contrast ) {
				return $candidate;
			}
			$L -= 0.02;
		}
		return '#1A1A1A';
	}
}

// ──────────────────────────────────────────────────────────────────
// Slot resolver — reads 6 slot keys with defaults.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_get_slots' ) ) {
	function customify_color_get_slots() {
		// Slot primary / secondary reuse existing legacy theme_mod keys to keep
		// storage compatibility with 30K+ sites. Every read is normalized to
		// guard against wp-cli / external writes that bypass sanitize_callback.
		// Surface default `#f9f9f9` aligns with the canvas-tinted SCSS hardcode
		// historically used on `.page-titlebar` (and similar elevated chrome).
		// Wiring the titlebar SCSS to `var(--customify-surface, #f9f9f9)` means
		// 30K legacy sites keep the byte-identical render (fallback fires when
		// the surface var isn't emitted to :root), while palette-opt-in sites
		// pick up a coherent surface tone — e.g. dark-base saved → surface
		// auto-tones with it instead of leaving the titlebar stuck at light gray.
		$defaults = array(
			'base'      => '#FFFFFF',
			'surface'   => '#f9f9f9',
			'text'      => '#2b2b2b',
			'primary'   => '#235787',
			'secondary' => '#c3512f',
			'accent'    => '#FFD042',
		);
		$keys     = array(
			'base'      => 'customify_palette_base',
			'surface'   => 'customify_palette_surface',
			'text'      => 'customify_palette_text',
			'primary'   => 'global_styling_color_primary',
			'secondary' => 'global_styling_color_secondary',
			'accent'    => 'customify_palette_accent',
		);
		$slots    = array();
		foreach ( $defaults as $slot => $default ) {
			$raw          = get_theme_mod( $keys[ $slot ], $default );
			$slots[ $slot ] = customify_color_normalize_hex( $raw, $default );
		}
		return $slots;
	}
}

// ──────────────────────────────────────────────────────────────────
// :root CSS block builder.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_root_css' ) ) {
	function customify_color_palette_root_css() {
		$slots = customify_color_get_slots();

		// Static hex fallbacks for derived vars (PHP-precomputed).
		// `border` mix at 9% — lighter than the spec-§2 14% the project briefly
		// shipped (which renders #e1e1e1 on default install vs the legacy
		// #eaecee, a perceptible darkening). 9% lands render at #ECECEC, the
		// grayscale equivalent of legacy avg (ΔE ≈ 1.1 vs #eaecee). Decorative-
		// only (WCAG-exempt) per spec — functional-control borders should use
		// `border-strong` instead. The fallback expressions in inc/customizer/
		// configs/colors.php are kept in sync at the same 9% percentage.
		$text_muted_default    = customify_color_mix_hex( $slots['text'], $slots['base'], 0.70 );
		$border_default        = customify_color_mix_hex( $slots['text'], $slots['base'], 0.09 );
		// `border-strong` solved per spec §2 — smallest P where WCAG contrast
		// vs base ≥ 3.0. Used by form input borders / button outlines / any
		// boundary that's the ONLY cue identifying a functional control.
		$border_strong_default = customify_color_solve_border_strong( $slots['text'], $slots['base'] );
		$primary_hover_default = customify_color_mix_hex( $slots['primary'], '#000000', 0.90 ); // primary at 90%, black at 10%
		// Link hover defaults to the SAME as link (which itself defaults to the
		// Primary slot) — hovering keeps the link colour unless the user saves a
		// Link-hover override. Project-owner decision (was: link lighter 15%).
		$link_hover_default    = $slots['primary'];
		// Body text default = slot.text directly (same pattern as heading).
		// Earlier Phase 2.3 used mix(text, base, 88%) for a softer ink,
		// but that desaturates the user's Text slot — e.g. setting Text to
		// pure white on a dark base yields ~#e0e0e0 grey body copy
		// instead of the explicit white. Use slot.text verbatim so body
		// copy fully respects whatever the user picked.
		$body_text_default     = $slots['text'];

		// Override resolution — legacy explicit values win over computed defaults.
		// Each override is normalized; an invalid stored value (e.g. from a
		// rogue wp-cli write) is treated as "no override" and the derived
		// fallback kicks in instead of polluting the :root with garbage.
		//
		// Critical: `get_theme_mod()` inside the Customizer preview iframe falls
		// back to the customize control's registered default, NOT to our `null`
		// argument — so passing `null` here would still resolve to the field
		// default and look like an explicit user override, suppressing the
		// cascade. Read straight from the raw saved-mods array so the override
		// only counts when the user actually saved something. Outside the
		// customizer, get_theme_mods() returns the same data as get_theme_mod().
		$_saved_mods = get_theme_mods();
		$_get_saved  = function ( $key ) use ( $_saved_mods ) {
			return ( is_array( $_saved_mods ) && array_key_exists( $key, $_saved_mods ) )
				? $_saved_mods[ $key ]
				: null;
		};
		$ov_text_muted   = customify_color_normalize_hex( $_get_saved( 'global_styling_color_meta' ), '' );
		$ov_border       = customify_color_normalize_hex( $_get_saved( 'global_styling_color_border' ), '' );
		$ov_link         = customify_color_normalize_hex( $_get_saved( 'global_styling_color_link' ), '' );
		$ov_link_hover   = customify_color_normalize_hex( $_get_saved( 'global_styling_color_link_hover' ), '' );
		$ov_heading      = customify_color_normalize_hex( $_get_saved( 'global_styling_color_heading' ), '' );
		$ov_widget_title = customify_color_normalize_hex( $_get_saved( 'global_styling_color_w_title' ), '' );
		$ov_body_text    = customify_color_normalize_hex( $_get_saved( 'global_styling_color_text' ), '' );

		$text_muted   = $ov_text_muted   ?: $text_muted_default;
		// Border emitted UNCONDITIONALLY (Phase 2.13 follow-up — was
		// previously gated on saved override per Phase 2.6, which left
		// dozens of `var(--customify-border, color-mix(currentcolor X%,
		// transparent))` consumers in inc/customizer/configs/colors.php
		// falling back to the currentcolor mix. On dark headers / dark
		// page-titlebars / dark hero sections, that fallback resolves
		// to ~14% of white on dark bg ≈ invisible — leading to the
		// reported "page-titlebar lost its border" issue.
		//
		// Using the slot-derived default (mix(text, base, 9%) — see border
		// §2) gives a concrete hex value that's visible regardless of
		// the consuming element's text color. Saved override still wins.
		// 30K safety: same logic as Phase 2.13 on-* gate drop — the
		// CSS rules that consume this var have always been there with a
		// fallback; emitting a concrete value just makes them render
		// reliably instead of relying on context-dependent currentcolor.
		$border       = $ov_border       ?: $border_default;
		$link         = $ov_link         ?: $slots['primary'];
		$link_hover   = $ov_link_hover   ?: $link_hover_default;
		$heading      = $ov_heading      ?: $slots['text'];
		$widget_title = $ov_widget_title ?: $slots['text'];
		// Body text default uses its own mix (88% ink) — stronger than
		// text-muted (70%, used for meta / secondary copy) so body copy
		// stays legible while headings keep contrast above it.
		$body_text    = $ov_body_text    ?: $body_text_default;

		// Contrast picks for on-* (PHP-precomputed).
		//
		// 30K-site safety gate: only emit auto-contrast on-* tokens when the
		// user has explicitly engaged with the new Palette panel (saved any
		// of the 4 truly-new slot keys: base, surface, text, accent — none
		// of which existed pre-Phase-2). For legacy sites that have only
		// touched the long-standing primary/secondary keys (or nothing),
		// leaving the on-* tokens UNSET means bundled rules of the form
		// `color: var(--customify-on-primary, #fff)` fall back to the
		// literal #fff hex — byte-equivalent to the pre-refactor hard-coded
		// `color: #fff;` everywhere buttons consume these tokens.
		//
		// The 4 slot keys are checked via array_key_exists() on the raw
		// saved-mods array (NOT get_theme_mod() — that returns field defaults
		// inside the customize preview and would falsely look "saved"; see
		// the same lesson in the override-resolution block above).
		$has_palette_opt_in = (
			is_array( $_saved_mods ) && (
				array_key_exists( 'customify_palette_base',    $_saved_mods ) ||
				array_key_exists( 'customify_palette_surface', $_saved_mods ) ||
				array_key_exists( 'customify_palette_text',    $_saved_mods ) ||
				array_key_exists( 'customify_palette_accent',  $_saved_mods )
			)
		);
		// §3 on-* tokens — UNCONDITIONALLY emitted, NOT gated on the
		// 4-new-slot-keys palette opt-in. Rationale: the SCSS auto-wire
		// (`.has-primary-background-color { color: var(--customify-on-primary,
		// inherit) }`) needs on-* present at render time even when the
		// user only changed legacy Primary/Secondary/Accent slots (not the
		// new Phase 2 slots). With the old gate, changing Primary in the
		// Customizer didn't make text auto-flip because on-primary stayed
		// unset → fallback `inherit` → body text color (dark on dark = bad).
		//
		// 30K safety preserved: the on-* tokens are CONSUMED only by the
		// new auto-wire SCSS rule on the new picker slugs. Legacy sites
		// without `.has-primary-background-color` blocks see no behavioral
		// change — the var() is set in :root but no rule references it.
		// Sites that DO have `.has-primary-background-color` blocks gain
		// the auto-readability safety net; this is a strict UX improvement
		// over the pre-PR behavior (text was inheriting body color, often
		// failing contrast against a brand bg).
		//
		// §3 — on-X = max-contrast against the EFFECTIVE rendered color.
		// Pass slot.base so rgba brand values composite correctly before
		// the contrast pick (the helper passes opaque colors through
		// unchanged, so this is a no-op for hex inputs).
		$on_primary   = customify_color_pick_on( $slots['primary'],   $slots['base'] );
		$on_secondary = customify_color_pick_on( $slots['secondary'], $slots['base'] );
		$on_accent    = customify_color_pick_on( $slots['accent'],    $slots['base'] );

		// On-surface contrast — picks against the saved Surface, or the
		// SCSS var() fallback (#FFFFFF in `.is-style-card`) if Surface
		// isn't saved. Same unconditional emit rationale as the on-X
		// solid picks above.
		$surface_effective = ( is_array( $_saved_mods ) && array_key_exists( 'customify_palette_surface', $_saved_mods ) )
			? $slots['surface']
			: '#FFFFFF';
		$on_surface = customify_color_pick_on( $surface_effective, $slots['base'] );

		if ( $has_palette_opt_in ) {
			// §4 — *-container = soft tint of brand at OKLab L ≈ 0.93.
			// Each `customify_color_solve_container_p()` returns the
			// percentage that lands the source color's mix-with-base at
			// the target lightness. We emit the result as a `color-mix`
			// expression with the percentage frozen at compute time, so
			// the container var() chain looks like:
			//   --customify-primary-container: color-mix(in oklab,
			//       var(--customify-primary) {P}%, var(--customify-base));
			// Browsers re-resolve the var() at render time → when the
			// user drags Primary in the Customizer, the container updates
			// without a full PHP re-emit (the live-preview JS still has
			// to call the same solver to recompute {P} when the source
			// brand changes — handled in palette_preview_js).
			$primary_container_p   = customify_color_solve_container_p( $slots['primary'],   $slots['base'] );
			$secondary_container_p = customify_color_solve_container_p( $slots['secondary'], $slots['base'] );
			$accent_container_p    = customify_color_solve_container_p( $slots['accent'],    $slots['base'] );

			// Precompute the resulting hex for each container, then apply the
			// chroma cap so on-X-container is solved against the ACTUAL
			// container color that gets rendered (not the un-capped raw mix).
			// Otherwise the L-reduction loop would optimize for a saturated
			// container that no longer exists once the cap is applied.
			$container_max_chroma_compute = 0.04;
			$primary_container_hex   = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['primary'],   $slots['base'], $primary_container_p ),   $container_max_chroma_compute );
			$secondary_container_hex = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['secondary'], $slots['base'], $secondary_container_p ), $container_max_chroma_compute );
			$accent_container_hex    = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['accent'],    $slots['base'], $accent_container_p ),    $container_max_chroma_compute );

			// §5 — on-X-container = darken brand hue (keep OKLab a, b) until
			// the result has WCAG contrast ≥ 4.5 against the container.
			// For dark brands (primary navy) the loop barely moves L → result
			// ≈ source. For light brands (accent yellow) L drops significantly
			// → dark gold result. Either way, AA against the container is
			// guaranteed. NOT exposed in the Blocksify picker (theme internals
			// only) per the user's design decision — block authors typically
			// pick the source brand color directly as text on its container.
			$on_primary_container   = customify_color_l_reduce_until_contrast( $slots['primary'],   $primary_container_hex );
			$on_secondary_container = customify_color_l_reduce_until_contrast( $slots['secondary'], $secondary_container_hex );
			$on_accent_container    = customify_color_l_reduce_until_contrast( $slots['accent'],    $accent_container_hex );
		} else {
			$primary_container_p = $secondary_container_p = $accent_container_p = null;
			$on_primary_container = $on_secondary_container = $on_accent_container = null;
		}

		// Surface slot is the elevated-container background (cards / table
		// cells / code blocks / form inputs / calendar headers / page
		// titlebar). Only emit to :root when the user EXPLICITLY saved
		// palette_surface — for unsaved sites the bundled SCSS fallback
		// resolves to `color-mix(in srgb, currentcolor 6-12%, transparent)`
		// for the card/widget/code surface tints, while `.page-titlebar`
		// uses the slot default `#f9f9f9` as its fallback (matches the
		// historical hardcoded SCSS). Emitting the slot default here would
		// bake a fixed light gray into :root and break the adaptive
		// fallback for users who saved Base = dark but didn't touch Surface.
		// (Note: --customify-border used to follow this same opt-in gate
		// per Phase 2.6, but was switched to unconditional emit in Phase
		// 2.13 to fix invisible-border-on-dark-surface regressions —
		// surface stays gated because cards/widgets/code blocks consume the
		// adaptive 6-12% fallback that the rigid hex would suppress.)
		$ov_surface = ( is_array( $_saved_mods ) && array_key_exists( 'customify_palette_surface', $_saved_mods ) )
			? $slots['surface']
			: null;

		$lines = array(
			"--customify-base: {$slots['base']}",
			"--customify-text: {$slots['text']}",
			"--customify-primary: {$slots['primary']}",
			"--customify-secondary: {$slots['secondary']}",
			"--customify-accent: {$slots['accent']}",
			"--customify-text-muted: {$text_muted}",
			"--customify-body-text: {$body_text}",
			"--customify-primary-hover: {$primary_hover_default}",
			"--customify-link: {$link}",
			"--customify-link-hover: {$link_hover}",
			"--customify-heading: {$heading}",
			"--customify-widget-title: {$widget_title}",
		);
		// On-* contrast tokens — emitted UNCONDITIONALLY (see rationale
		// above the $on_primary computation). Consumed by the SCSS
		// auto-wire `.has-{brand}-background-color { color: var(--customify-on-{brand}, inherit) }`
		// so brand-bg blocks get WCAG-readable text out of the box, with
		// real-time recompute as user drags slot pickers in the Customizer.
		$lines[] = "--customify-on-primary: {$on_primary}";
		$lines[] = "--customify-on-secondary: {$on_secondary}";
		$lines[] = "--customify-on-accent: {$on_accent}";
		$lines[] = "--customify-on-surface: {$on_surface}";
		// --customify-border emitted UNCONDITIONALLY since the Phase 2.13
		// follow-up — see the $border resolution block above for the full
		// rationale. The slot-derived default (`mix(text, base, 9%)`) gives
		// a concrete hex that's visible on any surface, instead of the
		// pre-fix currentcolor-12% fallback that turned invisible on dark
		// containers. Saved override is honored 1:1 by $border = $ov_border.
		$lines[] = "--customify-border: {$border}";
		// --customify-surface only emitted when user explicitly saved
		// palette_surface (see $ov_surface above). Absence lets the
		// bundled SCSS `$surface_subtle/medium/strong` fallback expressions
		// (color-mix in srgb, currentcolor X%, transparent) fire so surface
		// tints (table cells, code blocks, calendar headers, form inputs)
		// adapt to the page background automatically.
		if ( null !== $ov_surface ) {
			$lines[] = "--customify-surface: {$ov_surface}";
		}

		// --customify-border-strong — emitted on palette opt-in, gated the
		// same as other derived tokens. Theme.json palette fallback inside
		// `var(--customify-border-strong, {hex})` covers the no-opt-in case
		// so block authors still get a usable color in the picker.
		// Future SCSS work will wire this to form input borders (per spec
		// §2: form inputs need WCAG 1.4.11's ≥3:1 contrast, not the
		// decorative ~1.35:1 of `--customify-border`).
		if ( $has_palette_opt_in ) {
			$lines[] = "--customify-border-strong: {$border_strong_default}";
		}

		// --customify-*-container — soft tints of brand colors at OKLab
		// L ≈ 0.93 with chroma capped at 0.04 to keep high-chroma brands
		// (yellow, lime, hot pink) from producing oversaturated tints.
		// Gated on palette opt-in for the same 30K-safety reasons as
		// other derived tokens — fresh sites get the theme.json palette
		// fallback (precomputed capped hex) inside `var()`.
		//
		// Emit as STATIC hex (not `color-mix(...)` expression) because
		// the chroma-cap step can't be expressed in CSS — color-mix gives
		// us perceptual L blending but doesn't let us project the result
		// back onto a max-chroma boundary. Static hex is fine: containers
		// recompute on Customizer save (PHP re-renders :root) and on every
		// live-preview slot drag (JS computes the same capped hex inline).
		// The 30K-safe fallback in theme.json palette also uses the capped
		// hex so picker swatches show the soft tint, not raw mix.
		$container_max_chroma = 0.04;
		if ( null !== $primary_container_p ) {
			$raw     = customify_color_mix_hex( $slots['primary'], $slots['base'], $primary_container_p );
			$capped  = customify_color_chroma_cap_oklab( $raw, $container_max_chroma );
			$lines[] = "--customify-primary-container: {$capped}";
		}
		if ( null !== $secondary_container_p ) {
			$raw     = customify_color_mix_hex( $slots['secondary'], $slots['base'], $secondary_container_p );
			$capped  = customify_color_chroma_cap_oklab( $raw, $container_max_chroma );
			$lines[] = "--customify-secondary-container: {$capped}";
		}
		if ( null !== $accent_container_p ) {
			$raw     = customify_color_mix_hex( $slots['accent'], $slots['base'], $accent_container_p );
			$capped  = customify_color_chroma_cap_oklab( $raw, $container_max_chroma );
			$lines[] = "--customify-accent-container: {$capped}";
		}

		// --customify-on-*-container — darkened brand-hue text for AA
		// contrast on the corresponding container. Emitted to :root but
		// deliberately omitted from the Blocksify picker (theme internals).
		// Block authors typically use the source brand color (`primary`,
		// `secondary`, `accent`) directly as the text-on-container choice;
		// these on-* tokens are the safety net for edge cases (e.g. user
		// saves a pastel-light brand where the source-as-text pattern
		// would fail AA).
		if ( null !== $on_primary_container ) {
			$lines[] = "--customify-on-primary-container: {$on_primary_container}";
		}
		if ( null !== $on_secondary_container ) {
			$lines[] = "--customify-on-secondary-container: {$on_secondary_container}";
		}
		if ( null !== $on_accent_container ) {
			$lines[] = "--customify-on-accent-container: {$on_accent_container}";
		}

		// Derived-token cascade lines — added AFTER the static lines so
		// modern browsers re-resolve them when the underlying slot changes
		// (e.g. drag the Text slot → headings update without save). Legacy
		// browsers without var() support ignore the duplicate decl as invalid
		// and the static line above still wins, keeping the precomputed hex.
		//
		// Only emit when there is no explicit override saved — an override
		// must remain a frozen static value (cf. 30K-site safety doctrine).
		if ( ! $ov_heading ) {
			$lines[] = "--customify-heading: var(--customify-text, {$slots['text']})";
		}
		// Widget title cascade — same pattern as heading. Sites without a
		// saved w_title override see widget titles follow slot.text. Saved
		// override locks the static value.
		if ( ! $ov_widget_title ) {
			$lines[] = "--customify-widget-title: var(--customify-text, {$slots['text']})";
		}
		// Body text cascade — body follows slot.text directly (no mix).
		// Same pattern as heading/widget-title: pure var() chain so user's
		// Text slot value flows through unchanged. Saved body override
		// suppresses this line and locks --customify-body-text to override.
		if ( ! $ov_body_text ) {
			$lines[] = "--customify-body-text: var(--customify-text, {$slots['text']})";
		}
		// Link cascade — link defaults to slot.primary directly (same
		// pattern as heading→text). The CSS rule `a { color: var(--customify-link, ...) }`
		// then resolves to whatever primary is set to, unless a saved
		// link override beats it. Modern browsers see the cascade live;
		// legacy browsers keep the static `--customify-link: <slot.primary>`
		// emitted earlier in this block.
		if ( ! $ov_link ) {
			$lines[] = "--customify-link: var(--customify-primary, {$slots['primary']})";
		}
		// Link-hover cascade — follows Link (which follows Primary) so hovering
		// keeps the link colour unless a Link-hover override is saved. Pure var()
		// chain (no color-mix) now that hover == link.
		if ( ! $ov_link_hover ) {
			$lines[] = "--customify-link-hover: var(--customify-link, {$link_hover_default})";
		}

		$static_root = ':root{' . implode( ';', $lines ) . ';}';

		// Modern-browser refinement via color-mix(in oklab, ...). Only emit for
		// derived vars that DON'T have an explicit override saved — preserves
		// legacy 30K-site behavior bit-for-bit.
		$mix_lines = array();
		if ( ! $ov_text_muted ) {
			$mix_lines[] = '--customify-text-muted: color-mix(in oklab, var(--customify-text) 70%, var(--customify-base))';
		}
		// Body text cascade removed from @supports block — body now uses a
		// pure var() chain (see static :root section above) instead of a
		// color-mix so the Text slot value flows through unchanged.
		//
		// Border live-resolve: when no override saved, the static line
		// emits a baked hex mix(text, base, 9%). That works at page-load
		// but does NOT update when the user drags Text or Base in the
		// Customizer live preview (the static value was computed once at
		// PHP render time). The @supports block here re-emits the same
		// value as a color-mix expression with var() refs — modern
		// browsers re-resolve when the underlying slot vars change, so
		// the live preview updates without a re-render.
		if ( ! $ov_border ) {
			$mix_lines[] = '--customify-border: color-mix(in oklab, var(--customify-text) 9%, var(--customify-base))';
		}
		$mix_lines[] = '--customify-primary-hover: color-mix(in oklab, var(--customify-primary), black 10%)';
		// Link-hover is now a pure var() chain (= Link) emitted in the static
		// $lines block above — no @supports color-mix line needed.

		$css = $static_root;
		if ( $mix_lines ) {
			$css .= '@supports (color: color-mix(in oklab, red, blue)){:root{' . implode( ';', $mix_lines ) . ';}}';
		}

		// Background composite cascade — Page bg / Content Area bg / Site
		// Content bg all fall back to `--customify-base` when the user has
		// NOT explicitly saved a bg_color subfield in the corresponding
		// composite styling control. When saved, the composite's own
		// auto-CSS rule emits the literal hex and that wins via cascade
		// order (palette-tokens loads AFTER customify-style-inline-css,
		// but we suppress emission here for saved composites so the
		// literal stays the only emitter for that selector).
		$bg_composites = array(
			array( 'key' => 'background',           'selector' => 'body' ),
			array( 'key' => 'site_content_styling', 'selector' => '.site-content .content-area' ),
			array( 'key' => 'content_background',   'selector' => '.site-content' ),
		);
		$bg_cascade_lines = array();
		foreach ( $bg_composites as $comp ) {
			$saved = isset( $_saved_mods[ $comp['key'] ]['normal']['bg_color'] )
				? trim( (string) $_saved_mods[ $comp['key'] ]['normal']['bg_color'] )
				: '';
			if ( '' === $saved ) {
				$bg_cascade_lines[] = $comp['selector'] . '{background-color:var(--customify-base, ' . $slots['base'] . ')}';
			}
		}
		if ( $bg_cascade_lines ) {
			$css .= implode( '', $bg_cascade_lines );
		}

		return $css;
	}
}

// ──────────────────────────────────────────────────────────────────
// theme.json palette sync — block editor color picker reads from here.
// Slug contract is API surface for Blocksify starter templates: never rename.
// ──────────────────────────────────────────────────────────────────

// Note: the 3 NEW slot slugs (base, surface, accent) are declared statically
// in theme.json alongside the long-standing 8 entries (primary, secondary,
// text, link, heading, background, light-gray, dark-gray). They use the same
// default values as the slot defaults here; the live Customizer values drive
// the `:root` block above (consumed by frontend / Blocksify), while
// theme.json values feed the block editor color picker.
//
// Both pickers are in sync at theme defaults; if the user changes a slot in
// the Customizer, the :root var updates but theme.json palette default does
// not — this matches existing Customify behavior (Customizer color changes
// never propagated to the block editor palette before).

// ──────────────────────────────────────────────────────────────────
// Customizer-controls JS: inject a "From palette" quick-pick row at the
// bottom of every wp-color-picker popup inside the Colors section.
// Lets the user override any component color from the 6 brand slots in
// one click, instead of typing a hex.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_quickpick_js' ) ) {
	function customify_color_palette_quickpick_js() {
		$slots   = customify_color_get_slots();
		// Brand-first display order to match the Palette section UI.
		// `control` is the LI id (without the `customize-control-` prefix)
		// used to detect whether an open picker is a palette slot — in that
		// case the popup shows a hex input instead of the From-Palette row.
		$payload = wp_json_encode( array(
			'slots' => array(
				array( 'key' => 'primary',   'label' => 'Primary',   'color' => $slots['primary'],   'control' => 'global_styling_color_primary' ),
				array( 'key' => 'secondary', 'label' => 'Secondary', 'color' => $slots['secondary'], 'control' => 'global_styling_color_secondary' ),
				array( 'key' => 'accent',    'label' => 'Accent',    'color' => $slots['accent'],    'control' => 'customify_palette_accent' ),
				array( 'key' => 'text',      'label' => 'Text',      'color' => $slots['text'],      'control' => 'customify_palette_text' ),
				array( 'key' => 'surface',   'label' => 'Surface',   'color' => $slots['surface'],   'control' => 'customify_palette_surface' ),
				array( 'key' => 'base',      'label' => 'Base',      'color' => $slots['base'],      'control' => 'customify_palette_base' ),
			),
		) );

		$script = "(function(\$){
	var CFY_COLORS = {$payload};
	var PALETTE_CONTROLS = CFY_COLORS.slots.map(function(s){ return s.control; });
	// Map of override settings → cascade source slot (read from wp.customize
	// on demand). Used to visually sync each override picker's swatch with
	// the resolved cascade value when no user override has been saved.
	// Source value drives the swatch DOM only — the override setting stays
	// empty so the cascade keeps applying. The user dragging the override
	// picker writes a real value and breaks out of cascade mode.
	var CASCADE_MAP = {
		'global_styling_color_link':         'global_styling_color_primary',
		'global_styling_color_link_hover':   'global_styling_color_link',
		'global_styling_color_heading':      'customify_palette_text',
		'global_styling_color_w_title':      'customify_palette_text',
		'global_styling_color_text':         'customify_palette_text'
	};
	// Field defaults — when an override's value equals its field default,
	// we treat it as 'no override' and apply the cascade-sync display.
	// Mirrors the 'default' keys in inc/customizer/configs/colors.php.
	var FIELD_DEFAULTS = {
		'global_styling_color_link':         '#235787',
		'global_styling_color_link_hover':   '#235787',
		'global_styling_color_heading':      '#2b2b2b',
		'global_styling_color_w_title':      '#2b2b2b',
		'global_styling_color_text':         '#2b2b2b'
	};

	// Decode wp.customize value if Customify wrapped it as URL-encoded JSON.
	function decodeValue(v) {
		if (typeof v !== 'string') return v;
		try { return JSON.parse(decodeURI(v)); } catch (e) { return v; }
	}

	// Resolve a setting's EFFECTIVE cascade value by walking the chain when the
	// setting is itself unset (= empty or its field default). e.g. link-hover
	// cascades from link, which cascades from primary — so the link-hover swatch
	// tracks Primary even though its direct source is Link.
	function resolveCascadeValue(id) {
		var val = decodeValue(wp.customize(id).get() || '');
		var src = CASCADE_MAP[id];
		if (src && (val === '' || val === FIELD_DEFAULTS[id])) {
			return resolveCascadeValue(src);
		}
		return val;
	}

	// Sync the override picker's swatch to its cascade source when the
	// override is unset (= still equals the registered field default).
	function syncCascadeSwatch(targetId) {
		var sourceId = CASCADE_MAP[targetId];
		if (!sourceId) return;
		var li = document.getElementById('customize-control-' + targetId);
		if (!li) return;
		var saved = decodeValue(wp.customize(targetId).get() || '');
		var unset = (saved === '' || saved === FIELD_DEFAULTS[targetId]);
		if (!unset) {
			// User override saved — let wp-color-picker paint as usual.
			li.classList.remove('is-cascading');
			li.style.removeProperty('--customify-cascade-display');
			return;
		}
		var cascadeValue = resolveCascadeValue(sourceId);
		if (!cascadeValue) return;
		li.classList.add('is-cascading');
		li.style.setProperty('--customify-cascade-display', cascadeValue);
	}

	function syncAllCascadeSwatches() {
		Object.keys(CASCADE_MAP).forEach(syncCascadeSwatch);
	}

	// Bind to each cascade SOURCE so swatches refresh when the user drags
	// Primary or Text. Also bind each target's own setting so saving an
	// override flips it out of cascade mode immediately.
	function wireCascadeListeners() {
		if (typeof wp === 'undefined' || !wp.customize) return;
		// Bind targets — react to override save / clear.
		Object.keys(CASCADE_MAP).forEach(function(targetId){
			wp.customize(targetId, function(value){
				value.bind(function(){ setTimeout(function(){ syncCascadeSwatch(targetId); }, 16); });
			});
		});
		// Bind sources — react to Primary / Text slot drags.
		var sources = {};
		Object.keys(CASCADE_MAP).forEach(function(t){ sources[CASCADE_MAP[t]] = true; });
		Object.keys(sources).forEach(function(sourceId){
			wp.customize(sourceId, function(value){
				value.bind(function(){
					setTimeout(syncAllCascadeSwatches, 16);
				});
			});
		});
		// Initial paint once controls mount.
		setTimeout(syncAllCascadeSwatches, 200);
	}
	\$(wireCascadeListeners);

	function getControlId(container) {
		var li = container.closest ? container.closest('.customize-control') : null;
		if ( ! li || ! li.id ) return '';
		return li.id.replace(/^customize-control-/, '');
	}

	function removeAddons(container) {
		var qp = container.querySelector ? container.querySelector('.customify-color-quickpick') : null;
		if (qp) qp.parentNode.removeChild(qp);
		var hex = container.querySelector ? container.querySelector('.customify-color-hexrow') : null;
		if (hex) hex.parentNode.removeChild(hex);
	}

	// Build the From-Palette row used by component overrides (Link, Border,
	// Heading, etc.) so the user can override a single element from the
	// brand palette in one click.
	function buildQuickPick(\$panel, currentVal) {
		var \$row = \$('<div class=\"customify-color-quickpick\"></div>');
		\$row.append('<span class=\"customify-color-quickpick__label\">From palette</span>');
		CFY_COLORS.slots.forEach(function(s){
			var color = (s.color || '').toLowerCase();
			var \$sw = \$('<button type=\"button\" class=\"customify-color-quickpick__swatch\"></button>')
				.css('background-color', color)
				.attr('title', s.label)
				.attr('data-label', s.label)
				.attr('data-color', color);
			if (color === currentVal) \$sw.addClass('is-active');
			\$sw.on('click', function(e){
				e.preventDefault();
				e.stopPropagation();
				\$panel.wpColorPicker('color', color);
				\$row.find('.customify-color-quickpick__swatch').removeClass('is-active');
				\$sw.addClass('is-active');
			});
			\$row.append(\$sw);
		});
		return \$row;
	}

	// Build the hex input + read-only token var rows for a palette slot.
	// Two-way sync the hex with wp-color-picker via Iris events; the token
	// var is purely informational (one-tap select-all for copy/paste).
	function buildHexInput(\$panel, currentVal, slotKey) {
		var \$row = \$('<div class=\"customify-color-hexrow\"></div>');
		var \$input = \$('<input type=\"text\" class=\"customify-color-hex\" spellcheck=\"false\" autocomplete=\"off\" />').val(currentVal);
		\$input.on('input', function(){
			var v = (\$input.val() || '').trim();
			if ( v && v.charAt(0) !== '#' ) v = '#' + v;
			if ( /^#[0-9a-fA-F]{6}\$/.test(v) || /^#[0-9a-fA-F]{8}\$/.test(v) ) {
				\$panel.wpColorPicker('color', v);
			}
		});
		\$input.on('click', function(e){ e.stopPropagation(); });
		\$panel.on('iris-customify-hex-sync', function(){
			var v = \$panel.val();
			if ( v && document.activeElement !== \$input[0] ) \$input.val(v);
		});
		\$row.append(\$input);

		if ( slotKey ) {
			var \$tokenRow = \$('<div class=\"customify-color-tokenrow\"></div>');
			var \$token = \$('<input type=\"text\" class=\"customify-color-token\" readonly />')
				.val('var(--customify-' + slotKey + ')');
			\$token.on('focus', function(){ this.select(); });
			\$token.on('click', function(e){ e.stopPropagation(); this.select(); });
			\$tokenRow.append(\$token);
			// Wrap both rows in a fragment-like jQuery set so the caller can
			// append in one go without changing the public API.
			return \$row.add(\$tokenRow);
		}
		return \$row;
	}

	function injectPopupAddon(container) {
		var \$container = \$(container);
		var \$panel     = \$container.find('.customify--color-panel');
		if ( ! \$panel.length ) return;
		var currentVal = (\$panel.val() || '').toLowerCase();
		var controlId  = getControlId(container);
		var slot       = CFY_COLORS.slots.filter(function(s){ return s.control === controlId; })[0];
		var isPalette  = !!slot;

		// If an addon is already present (pre-built on init), refresh its
		// current-value state instead of rebuilding the DOM from scratch.
		// Overrides now carry BOTH a hex row and a quick-pick row, so refresh
		// either / both if present.
		var existing = container.querySelector ? container.querySelector('.customify-color-quickpick, .customify-color-hexrow') : null;
		if (existing) {
			var hexInput = container.querySelector('.customify-color-hex');
			if (hexInput && document.activeElement !== hexInput) {
				hexInput.value = \$panel.val() || '';
			}
			var quickpick = container.querySelector('.customify-color-quickpick');
			if (quickpick) {
				quickpick.querySelectorAll('.customify-color-quickpick__swatch').forEach(function(sw){
					sw.classList.toggle('is-active', (sw.getAttribute('data-color') || '').toLowerCase() === currentVal);
				});
			}
			return;
		}

		var \$addon;
		if (isPalette) {
			// Palette slots: hex input + readonly token var (var(--customify-<slug>)).
			\$addon = buildHexInput(\$panel, currentVal, slot.key);
		} else {
			// Component overrides (Link, Heading, Border, etc.): hex input
			// (no token row — overrides don't have a stable slot slug to
			// reference) PLUS the From-Palette quick-pick swatches so the
			// user can either type a custom hex or one-tap a slot color.
			\$addon = buildHexInput(\$panel, currentVal, null).add(buildQuickPick(\$panel, currentVal));
		}
		\$container.find('.wp-picker-holder').append(\$addon);
	}

	// Pre-build addons for every color picker in the Colors section right
	// after Customizer init — that way opening a picker just refreshes the
	// already-mounted addon (single class toggle / value update) instead of
	// building the DOM + 6-7 buttons + handlers from scratch each time.
	function prebuildAll(section) {
		section.querySelectorAll('.wp-picker-container').forEach(function(c){
			if ( c.querySelector('.customify-color-quickpick, .customify-color-hexrow') ) return;
			injectPopupAddon(c);
		});
		section.querySelectorAll('.customify-input-color').forEach(refreshDirtyState);
	}

	// Toggle is-dirty on the picker row when the saved value diverges
	// from the field's REGISTERED DEFAULT (data-default attribute, set
	// from field.default in the color control template). SCSS uses
	// is-dirty to reveal the small reset glyph next to the swatch.
	//
	// Pre-fix bug: this comparison used the value LOADED at page-render
	// time (initialValues snapshot). When a user had already saved a
	// non-default value (e.g. Base = #000000) before opening Customizer,
	// the snapshot captured that as initial. Then current === initial
	// meant the picker was never marked dirty, so the reset glyph
	// never appeared and the user could not revert to the default.
	//
	// Fix: compare against data-default. Any saved override that differs
	// from the registered default toggles is-dirty regardless of when
	// the value was saved.
	function refreshDirtyState(div) {
		if (!div) return;
		var input = div.querySelector('input.wp-color-picker');
		if (!input) return;
		var cur = (input.value || '').trim().toLowerCase();
		var def = (div.getAttribute('data-default') || '').trim().toLowerCase();
		var dirty = cur !== '' && cur !== def;
		div.classList.toggle('is-dirty', dirty);
	}

	// Wire change listeners once. Two sources cover everything:
	//   • DOM events on the underlying wp-color-picker input — fires for
	//     Iris drag, hex paste, quickpick swatch click.
	//   • wp.customize setting bind — fires for programmatic .set() and
	//     for clicks on wp-picker-default (which resets value via the
	//     customize API, not via a DOM event on the input).
	// Either way, refreshDirtyState re-evaluates and toggles is-dirty.
	\$(document).on('input change keyup', '#sub-accordion-section-customify_colors input.wp-color-picker, #sub-accordion-section-customify_colors .customify-color-hex', function(){
		var li = \$(this).closest('.customify-input-color')[0];
		refreshDirtyState(li);
	});
	// Bind to each picker's underlying wp.customize Setting once it's
	// available so reset-button clicks (which go through the customize
	// API) also refresh the dirty state. `.customify-input-color` is a
	// DIV inside the control LI — settingId comes from the LI's id.
	function bindSettingListeners() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if (!section) return;
		section.querySelectorAll('.customify-input-color').forEach(function(div){
			if (div.dataset.dirtyBound) return;
			var li = div.closest('.customize-control');
			if (!li) return;
			var settingId = (li.id || '').replace('customize-control-', '');
			if (!settingId || !wp.customize(settingId)) return;
			div.dataset.dirtyBound = '1';
			wp.customize(settingId).bind(function(){
				// Defer one tick so wp-color-picker has time to write the
				// new value into the input before we read it.
				setTimeout(function(){ refreshDirtyState(div); }, 16);
			});
		});
	}
	\$(bindSettingListeners);
	setTimeout(bindSettingListeners, 600);


	// Legacy fine-tuning section is collapsed by default. The .customize-control-title
	// span inside the heading control gets a click handler; the LI itself stays
	// inert so it doesn't pick up the browser's default focus outline (which
	// otherwise paints a stuck rectangle around the entire heading row).
	// State persists per-session via sessionStorage.
	function setupLegacyCollapse() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if (!section) return;
		var heading = document.getElementById('customize-control-customify_colors_h_overrides');
		if (!heading) return;
		if (heading.classList.contains('customify-collapsible-heading')) return;
		heading.classList.add('customify-collapsible-heading');

		// Title span is the clickable target (chevron sits next to text).
		var titleEl = heading.querySelector('.customize-control-title');
		if (!titleEl) return;
		titleEl.classList.add('customify-collapsible-toggle');

		var targets = [];
		var next = heading.nextElementSibling;
		while (next) {
			if (next.classList && next.classList.contains('customize-control')) targets.push(next);
			next = next.nextElementSibling;
		}
		if (!targets.length) return;

		var STORAGE_KEY = 'customify-legacy-collapsed';
		var collapsed = sessionStorage.getItem(STORAGE_KEY) !== 'open';

		function apply(state) {
			targets.forEach(function(el){ el.style.display = state ? 'none' : ''; });
			heading.classList.toggle('is-collapsed', state);
			heading.classList.toggle('is-open',     !state);
		}
		apply(collapsed);

		titleEl.setAttribute('role', 'button');
		titleEl.setAttribute('tabindex', '0');
		titleEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

		function toggle(e) {
			e.preventDefault();
			e.stopPropagation();
			collapsed = !collapsed;
			sessionStorage.setItem(STORAGE_KEY, collapsed ? 'closed' : 'open');
			apply(collapsed);
			titleEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
		}
		titleEl.addEventListener('click', toggle);
		titleEl.addEventListener('keydown', function(e){
			if (e.key === 'Enter' || e.key === ' ') toggle(e);
		});
	}
	\$(setupLegacyCollapse);
	setTimeout(setupLegacyCollapse, 400);

	// Re-emit a custom event from any change on the picker so the hex input
	// stays in sync while the user drags Iris's saturation / hue / alpha.
	\$(document).on('input change', '#sub-accordion-section-customify_colors .customify--color-panel', function(){
		\$(this).trigger('iris-customify-hex-sync');
	});

	// Monkey-patch jQuery's slide methods so Iris's wpColorPicker can't
	// queue its slow slideToggle('fast') on our color picker holders. The
	// override is scoped to elements WITH .wp-picker-holder — never matches
	// the styling composite control's modal panel (which also lives in the
	// Colors section and uses slideUp/slideDown for its own toggle, and
	// relies on the animation-end callback to remove its .modal--opening
	// class — making it instant would strand the modal open).
	(function patchJQuerySlide(){
		var origDown   = \$.fn.slideDown;
		var origUp     = \$.fn.slideUp;
		var origToggle = \$.fn.slideToggle;
		function isPickerHolder(el){
			return el && el.classList && el.classList.contains('wp-picker-holder')
				&& el.closest && el.closest('#sub-accordion-section-customify_colors');
		}
		\$.fn.slideDown = function(){
			if (this.length && isPickerHolder(this[0])) return this.show();
			return origDown.apply(this, arguments);
		};
		\$.fn.slideUp = function(){
			if (this.length && isPickerHolder(this[0])) return this.hide();
			return origUp.apply(this, arguments);
		};
		\$.fn.slideToggle = function(){
			if (this.length && isPickerHolder(this[0])) {
				return this.is(':visible') ? this.hide() : this.show();
			}
			return origToggle.apply(this, arguments);
		};
	})();

	// Iris stops propagation on its click handler, so jQuery delegation on
	// document never sees the click. Instead we observe the .wp-picker-active
	// class on each wp-picker-container inside the Colors section and add/
	// remove our addon row in lock-step with the picker open/close.
	function startObserver() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if ( ! section ) {
			setTimeout( startObserver, 500 );
			return;
		}
		// Pre-build addons so opens feel instant — give Customify's initColor
		// a tick to call wpColorPicker() and produce .wp-picker-container.
		setTimeout(function(){ prebuildAll(section); }, 100);
		setTimeout(function(){ prebuildAll(section); }, 800);

		var observer = new MutationObserver(function(mutations){
			mutations.forEach(function(m){
				if ( m.type !== 'attributes' || m.attributeName !== 'class' ) return;
				var target = m.target;
				if ( ! target.classList || ! target.classList.contains('wp-picker-container') ) return;
				if ( target.classList.contains('wp-picker-active') ) {
					// Picker just opened — refresh existing addon (or create
					// the first time if pre-build hadn't run for this picker yet).
					injectPopupAddon(target);
				}
				// Note: no removeAddons on close — we keep the addon mounted
				// so the next open is instant. wp-picker-holder is display:none
				// via CSS when not .wp-picker-active so the addon is hidden too.
			});
		});
		observer.observe(section, { attributes: true, subtree: true, attributeFilter: ['class'] });
	}

	\$(startObserver);
})(jQuery);";

		wp_add_inline_script( 'customize-controls', $script );
	}
	add_action( 'customize_controls_enqueue_scripts', 'customify_color_palette_quickpick_js' );
}

// ──────────────────────────────────────────────────────────────────
// Force transport=postMessage on the 4 new slot settings.
// The 2 legacy slot keys (primary, secondary) already get postMessage
// via Customify_Customizer's css_format detection (class-customizer.php
// L1085-1097). The 4 new slot fields have empty css_format (they only
// feed :root vars, not auto-CSS rules) so they default to 'refresh' —
// override here so the preview JS below can live-update :root.
// Priority 1000 ensures Customify_Customizer::register (priority 666)
// has already added all settings.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_force_postmessage' ) ) {
	function customify_color_palette_force_postmessage( $wp_customize ) {
		$slot_settings = array(
			'customify_palette_base',
			'customify_palette_surface',
			'customify_palette_text',
			'customify_palette_accent',
		);
		foreach ( $slot_settings as $setting_id ) {
			$setting = $wp_customize->get_setting( $setting_id );
			if ( $setting ) {
				$setting->transport = 'postMessage';
			}
		}
	}
	add_action( 'customize_register', 'customify_color_palette_force_postmessage', 1000 );
}

// ──────────────────────────────────────────────────────────────────
// Preview JS: live-update --customify-<slot> on document.documentElement
// when any of the 6 slot settings changes. Modern browsers re-resolve
// derived tokens (text-muted, border, primary-hover, link-hover) via the
// color-mix() lines in :root automatically. Static fallbacks and on-*
// contrast picks don't live-update — they refresh on Customizer save.
//
// For primary/secondary the auto-CSS pipeline already regenerates the
// rule strings in the preview iframe via the existing Customify auto-css
// JS; this handler additionally keeps the :root var in sync so the
// var(--customify-primary, ...) refactor renders the new color in
// modern browsers.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_preview_js' ) ) {
	function customify_color_palette_preview_js() {
		$payload = wp_json_encode( array(
			'global_styling_color_primary'   => '--customify-primary',
			'global_styling_color_secondary' => '--customify-secondary',
			'customify_palette_accent'       => '--customify-accent',
			'customify_palette_text'         => '--customify-text',
			'customify_palette_surface'      => '--customify-surface',
			'customify_palette_base'         => '--customify-base',
			// Override-style settings whose CSS rule now consumes the var()
			// token. Live-updating the token on drag means the user's direct
			// picker change wins over the slot cascade (the :root cascade
			// chain `--customify-heading: var(--customify-text, ...)` is
			// overridden by the inline style.setProperty we do here). When
			// the user clears the picker, our `normalize()` returns '' and
			// removeProperty() restores the cascade.
			'global_styling_color_heading'   => '--customify-heading',
			'global_styling_color_text'      => '--customify-body-text',
			'global_styling_color_link'      => '--customify-link',
			'global_styling_color_link_hover' => '--customify-link-hover',
			'global_styling_color_border'    => '--customify-border',
			'global_styling_color_meta'      => '--customify-text-muted',
			'global_styling_color_w_title'   => '--customify-widget-title',
		) );

		$script = "(function(){
	if (typeof wp === 'undefined' || ! wp.customize) return;
	var SLOT_VARS = {$payload};
	// Cascade expressions for derived override tokens. When the user CLEARS
	// an override picker mid-session via .set(''), removing the inline style
	// would fall through to the PHP-baked :root rule — which still holds the
	// SAVED override hex (palette-tokens block is rendered once at page load,
	// not regenerated on setting change). Result: cleared overrides keep
	// showing the old value until next save+reload.
	//
	// Fix: when normalize() returns empty for one of these tokens, set the
	// inline value to the cascade expression instead of removeProperty. CSS
	// custom-property values support nested var()/color-mix(), so the cascade
	// chain re-engages immediately and the rendered value tracks the source
	// slot in real time. After save+reload, PHP re-renders :root cleanly so
	// the inline override is no longer needed.
	//
	// Tokens NOT in this map fall back to the old removeProperty behavior:
	//   slot tokens (primary/secondary/accent/text/surface/base) — their
	//   :root values come from PHP defaults/saved slots, so removing the
	//   inline override correctly reverts to those.
	//
	// `--customify-border` IS in this map (since the Phase 2.13 unconditional-
	// emit shift): it's always present in :root, so removeProperty would
	// fall back to the PHP-baked static line — which still holds the SAVED
	// override hex on mid-session clears. The cascade expression here mirrors
	// the @supports color-mix line so cleared borders track Text+Base live.
	var CASCADE_FALLBACK = {
		'--customify-heading':      'var(--customify-text)',
		'--customify-body-text':    'var(--customify-text)',
		'--customify-widget-title': 'var(--customify-text)',
		'--customify-link':         'var(--customify-primary)',
		'--customify-link-hover':   'var(--customify-link)',
		'--customify-text-muted':   'color-mix(in oklab, var(--customify-text) 70%, var(--customify-base))',
		'--customify-border':       'color-mix(in oklab, var(--customify-text) 9%, var(--customify-base))'
	};
	// Customify wraps stored setting values as urlencode(json_encode(value))
	// so a saved hex arrives as '%22#ff00aa%22'. Mirror Customify's decode
	// (control.js / customizer.js use the same JSON.parse(decodeURI(v))
	// pattern) so we get the raw '#ff00aa' before validating.
	function decode(v) {
		if (typeof v !== 'string') return v;
		try { return JSON.parse(decodeURI(v)); } catch(e) { return v; }
	}
	function normalize(v) {
		var d = decode(v);
		if (typeof d !== 'string') return '';
		d = d.trim();
		if (!d) return '';
		if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})\$/.test(d)) return d;
		if (/^rgba?\\(/.test(d)) return d;
		return '';
	}
	Object.keys(SLOT_VARS).forEach(function(setting){
		wp.customize(setting, function(value){
			value.bind(function(newval){
				var clean = normalize(newval);
				var token = SLOT_VARS[setting];
				if (clean) {
					document.documentElement.style.setProperty(token, clean);
				} else if (CASCADE_FALLBACK[token]) {
					document.documentElement.style.setProperty(token, CASCADE_FALLBACK[token]);
				} else {
					document.documentElement.style.removeProperty(token);
				}
			});
		});
	});

	// Live preview math — mirrors the PHP helpers in colors-palette.php
	// per the color-token-derivation spec. Recomputes derived tokens
	// (on-*, *-container, on-*-container, border-strong) live as the
	// user drags any source-slot picker in the Customizer.
	function _hexToRgb(value) {
		// Accept both hex (#rrggbb / #rgb) and rgb()/rgba() input forms.
		// Mirrors PHP customify_color_hex_to_rgb() — the rgba support is
		// what makes on-* live-preview keep working when user drags the
		// alpha slider in the WP color picker.
		value = (value || '').toString().trim();
		// Anchored trailing `)` to reject cut-off inputs (parity with PHP).
		var m = value.match(/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*[\d.]+)?\s*\)\$/i);
		if (m) {
			return [
				Math.max(0, Math.min(255, parseInt(m[1], 10))),
				Math.max(0, Math.min(255, parseInt(m[2], 10))),
				Math.max(0, Math.min(255, parseInt(m[3], 10)))
			];
		}
		var hex = value.replace(/^#/, '');
		if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
		if (!/^[0-9a-fA-F]{6}\$/.test(hex)) return null;
		return [
			parseInt(hex.slice(0,2), 16),
			parseInt(hex.slice(2,4), 16),
			parseInt(hex.slice(4,6), 16)
		];
	}
	function _rgbToHex(rgb) {
		var c = function(v) {
			v = Math.max(0, Math.min(255, Math.round(v)));
			var h = v.toString(16);
			return h.length === 1 ? '0' + h : h;
		};
		return '#' + c(rgb[0]) + c(rgb[1]) + c(rgb[2]);
	}
	function _mixHex(a, b, weightA) {
		weightA = Math.max(0, Math.min(1, weightA));
		var wb = 1 - weightA;
		var ra = _hexToRgb(a), rb = _hexToRgb(b);
		if (!ra || !rb) return a;
		return _rgbToHex([
			ra[0]*weightA + rb[0]*wb,
			ra[1]*weightA + rb[1]*wb,
			ra[2]*weightA + rb[2]*wb
		]);
	}
	function _relativeLuminance(hex) {
		var rgb = _hexToRgb(hex);
		if (!rgb) return 0;
		var f = function(v) {
			v = v / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		};
		return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
	}
	function _wcagContrast(a, b) {
		var la = _relativeLuminance(a), lb = _relativeLuminance(b);
		var hi = Math.max(la, lb), lo = Math.min(la, lb);
		return (hi + 0.05) / (lo + 0.05);
	}
	// Composite a possibly-transparent color over an opaque base. Mirrors
	// PHP customify_color_composite_over() — returns the rendered color
	// so the max-contrast pick measures against what the user actually
	// sees, not the opaque rgb component of an rgba.
	function _compositeOver(value, baseHex) {
		var v = (value || '').toString().trim();
		var m = v.match(/^rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([\d.]+)\s*\)/i);
		if (m) {
			var r = Math.max(0, Math.min(255, parseInt(m[1], 10)));
			var g = Math.max(0, Math.min(255, parseInt(m[2], 10)));
			var b = Math.max(0, Math.min(255, parseInt(m[3], 10)));
			var a = Math.max(0, Math.min(1, parseFloat(m[4])));
			if (a >= 1) return [r, g, b];
			var baseRgb = _hexToRgb(baseHex) || [255, 255, 255];
			return [
				Math.round(r * a + baseRgb[0] * (1 - a)),
				Math.round(g * a + baseRgb[1] * (1 - a)),
				Math.round(b * a + baseRgb[2] * (1 - a))
			];
		}
		// Opaque (hex / rgb passthrough).
		return _hexToRgb(v) || [0, 0, 0];
	}
	// Spec §3: max-contrast pick against the EFFECTIVE (composited) bg.
	function _pickOn(value, baseHex) {
		baseHex = baseHex || '#FFFFFF';
		var rgb = _compositeOver(value, baseHex);
		var eff = _rgbToHex(rgb);
		return _wcagContrast('#FFFFFF', eff) >= _wcagContrast('#1A1A1A', eff)
			? '#FFFFFF' : '#1A1A1A';
	}
	// OKLab transforms (Ottosson) — same math as PHP customify_color_srgb_to_oklab
	// / customify_color_oklab_to_srgb. Needed for §4 P-solve and §5 L-reduction.
	function _srgbToOklab(hex) {
		var rgb = _hexToRgb(hex);
		if (!rgb) return [0, 0, 0];
		var f = function(v) {
			v = v / 255;
			return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		};
		var rl = f(rgb[0]), gl = f(rgb[1]), bl = f(rgb[2]);
		var l = 0.4122214708*rl + 0.5363325363*gl + 0.0514459929*bl;
		var m = 0.2119034982*rl + 0.6806995451*gl + 0.1073969566*bl;
		var s = 0.0883024619*rl + 0.2817188376*gl + 0.6299787005*bl;
		var cbrt = function(x){ return x < 0 ? -Math.pow(-x, 1/3) : Math.pow(x, 1/3); };
		var l_ = cbrt(l), m_ = cbrt(m), s_ = cbrt(s);
		return [
			0.2104542553*l_ + 0.7936177850*m_ - 0.0040720468*s_,
			1.9779984951*l_ - 2.4285922050*m_ + 0.4505937099*s_,
			0.0259040371*l_ + 0.7827717662*m_ - 0.8086757660*s_
		];
	}
	function _oklabToSrgb(L, a, b) {
		var l_ = L + 0.3963377774*a + 0.2158037573*b;
		var m_ = L - 0.1055613458*a - 0.0638541728*b;
		var s_ = L - 0.0894841775*a - 1.2914855480*b;
		var l = l_*l_*l_, m = m_*m_*m_, s = s_*s_*s_;
		var rl =  4.0767416621*l - 3.3077115913*m + 0.2309699292*s;
		var gl = -1.2684380046*l + 2.6097574011*m - 0.3413193965*s;
		var bl = -0.0041960863*l - 0.7034186147*m + 1.7076147010*s;
		var g = function(v) {
			v = Math.max(0, Math.min(1, v));
			return v <= 0.0031308 ? v * 12.92 : 1.055 * Math.pow(v, 1/2.4) - 0.055;
		};
		return _rgbToHex([g(rl)*255, g(gl)*255, g(bl)*255]);
	}
	function _oklabL(hex) { return _srgbToOklab(hex)[0]; }
	// Spec §4: closed-form P solve for container tints at OKLab L = 0.93.
	function _solveContainerP(source, base) {
		var ls = _oklabL(source), lb = _oklabL(base);
		var denom = ls - lb;
		if (Math.abs(denom) < 1e-6) return 0.5;
		var p = (0.93 - lb) / denom;
		return Math.max(0.02, Math.min(0.98, p));
	}
	// Chroma cap (Customify extension) — keeps high-chroma brand
	// containers from staying oversaturated when mixed with white.
	// Mirrors PHP customify_color_chroma_cap_oklab().
	function _chromaCap(hex, maxChroma) {
		var lab = _srgbToOklab(hex);
		var L = lab[0], a = lab[1], b = lab[2];
		var c = Math.sqrt(a*a + b*b);
		if (c <= maxChroma) return hex;
		var s = maxChroma / c;
		return _oklabToSrgb(L, a * s, b * s);
	}
	// Spec §5: step OKLab L downward until contrast against bg ≥ 4.5.
	function _lReduceUntilContrast(source, bg, target) {
		target = target || 4.5;
		var lab = _srgbToOklab(source);
		var L = lab[0], a = lab[1], b = lab[2];
		while (L > 0) {
			var candidate = _oklabToSrgb(L, a, b);
			if (_wcagContrast(candidate, bg) >= target) return candidate;
			L -= 0.02;
		}
		return '#1A1A1A';
	}
	// Spec §2: iterate P from 6% upward until contrast vs base ≥ 3.0.
	function _solveBorderStrong(text, base) {
		for (var p = 6; p <= 100; p++) {
			var mix = _mixHex(text, base, p/100);
			if (_wcagContrast(mix, base) >= 3.0) return mix;
		}
		return text;
	}

	// Helper: read a slot's current effective value from wp.customize.
	// Falls back to the spec default when the user hasn't saved the slot
	// (mirrors PHP slot resolver). Used by recomputeDerived() so a
	// container update fires correctly even mid-drag of a non-source slot.
	var SLOT_DEFAULTS = {
		'global_styling_color_primary':   '#235787',
		'global_styling_color_secondary': '#c3512f',
		'customify_palette_accent':       '#FFD042',
		'customify_palette_text':         '#2b2b2b',
		'customify_palette_surface':      '#f9f9f9',
		'customify_palette_base':         '#FFFFFF'
	};
	function _readSlot(setting) {
		try {
			var val = wp.customize(setting).get();
			var clean = normalize(val);
			return clean || SLOT_DEFAULTS[setting];
		} catch(e) {
			return SLOT_DEFAULTS[setting];
		}
	}

	// Recompute every derived token. Called from every source-slot
	// listener so a drag on Primary updates container/on-container/etc.
	function recomputeDerived() {
		var primary   = _readSlot('global_styling_color_primary');
		var secondary = _readSlot('global_styling_color_secondary');
		var accent    = _readSlot('customify_palette_accent');
		var text      = _readSlot('customify_palette_text');
		var surface   = _readSlot('customify_palette_surface');
		var base      = _readSlot('customify_palette_base');
		var de = document.documentElement.style;

		// §3 on-* — max-contrast against rgba-composited bg.
		de.setProperty('--customify-on-primary',   _pickOn(primary,   base));
		de.setProperty('--customify-on-secondary', _pickOn(secondary, base));
		de.setProperty('--customify-on-accent',    _pickOn(accent,    base));
		// On-surface uses #FFFFFF when --customify-surface is unset
		// (matches the SCSS var() fallback in `.is-style-card`).
		var hasSurface = false;
		try { hasSurface = !!normalize(wp.customize('customify_palette_surface').get()); } catch(e) {}
		de.setProperty('--customify-on-surface', _pickOn(hasSurface ? surface : '#FFFFFF', base));

		// §4 *-container — solve P, mix, apply chroma cap (0.04). Emit
		// as static hex (NOT a color-mix expression) because the chroma
		// cap can't be expressed in CSS color-mix. Mirrors PHP container
		// emit logic.
		var CONTAINER_MAX_CHROMA = 0.04;
		var pPrim = _solveContainerP(primary,   base);
		var pSec  = _solveContainerP(secondary, base);
		var pAcc  = _solveContainerP(accent,    base);
		var primContainerHex = _chromaCap(_mixHex(primary,   base, pPrim), CONTAINER_MAX_CHROMA);
		var secContainerHex  = _chromaCap(_mixHex(secondary, base, pSec),  CONTAINER_MAX_CHROMA);
		var accContainerHex  = _chromaCap(_mixHex(accent,    base, pAcc),  CONTAINER_MAX_CHROMA);
		de.setProperty('--customify-primary-container',   primContainerHex);
		de.setProperty('--customify-secondary-container', secContainerHex);
		de.setProperty('--customify-accent-container',    accContainerHex);

		// §5 on-*-container — L-reduced brand hue against the resolved
		// CAPPED container hex (so the safety net matches what's rendered).
		de.setProperty('--customify-on-primary-container',   _lReduceUntilContrast(primary,   primContainerHex));
		de.setProperty('--customify-on-secondary-container', _lReduceUntilContrast(secondary, secContainerHex));
		de.setProperty('--customify-on-accent-container',    _lReduceUntilContrast(accent,    accContainerHex));

		// §2 border-strong — solved P.
		de.setProperty('--customify-border-strong', _solveBorderStrong(text, base));
	}

	// Hook recomputeDerived to every source-slot change. Each derived
	// token depends on at least one source, so any drag fires a full
	// recompute (cheap — all formulas are closed-form or short loops).
	var SOURCE_SLOTS = [
		'global_styling_color_primary',
		'global_styling_color_secondary',
		'customify_palette_accent',
		'customify_palette_text',
		'customify_palette_surface',
		'customify_palette_base'
	];
	// Debounce: a palette switch changes all six slots at once; binding the raw
	// recomputeDerived would run the OKLab derivation six times back-to-back and
	// block the preview paint (~100ms+). Coalesce into ONE recompute ~24ms after
	// the last slot settles — imperceptible for a drag, snappy for a switch.
	var _cfyRderiveT;
	function recomputeDerivedDebounced(){ clearTimeout(_cfyRderiveT); _cfyRderiveT = setTimeout(recomputeDerived, 24); }
	SOURCE_SLOTS.forEach(function(setting){
		wp.customize(setting, function(value){
			value.bind(recomputeDerivedDebounced);
		});
	});
	// Prime the initial state on load so the preview iframe shows
	// derived values immediately (without waiting for the first drag).
	try { recomputeDerived(); } catch(e) {}
})();";

		wp_add_inline_script( 'customize-preview', $script );
	}
	add_action( 'customize_preview_init', 'customify_color_palette_preview_js' );
}

// ──────────────────────────────────────────────────────────────────
// theme.json palette injection — make Customify Palette tokens
// available to the block editor color picker (WP core, Blocksify,
// Gutenberg blocks, child themes, etc.) via `--wp--preset--color--X`.
// ──────────────────────────────────────────────────────────────────
//
// Why: block editor color pickers (and Blocksify's ColorSections
// composite) read the palette via `useSetting('color.palette.theme')`,
// which returns whatever theme.json declares under
// settings.color.palette. Without this filter the static palette in
// theme.json contains hard-coded hex values — block previews don't
// reflect the saved Customizer Palette, and authors picking "Primary"
// in the editor get the literal #235787 baked in at theme.json author
// time instead of the user's currently-saved primary slot value.
//
// What: replace the palette at runtime with the same slugs but each
// `color` field reads a `var(--customify-X, {literal_hex_fallback})`
// reference. WP 6.1+ accepts var() in palette color → propagates the
// var into the generated `--wp--preset--color--X` declaration → user
// blocks that picked "Primary" automatically follow the saved
// Customizer Primary, no rebuild required.
//
// 30K safety: the literal hex fallback inside each var() expression
// is the SAME value that the static theme.json palette previously
// declared. Sites with no saved Palette resolve the chain to the
// literal — byte-identical block render to before the filter.
//
// WP version gate: var() in palette color is WP 6.1+. Older WP
// renders the swatch as a raw "var(--customify-...)" text string in
// the picker (broken UX). Early-return on <6.1 keeps the static
// theme.json palette intact for those sites.
if ( ! function_exists( 'customify_color_palette_for_theme_json' ) ) {
	/**
	 * Build the palette array consumed by `wp_theme_json_data_theme`.
	 *
	 * Each entry uses `var(--customify-{token}, {hex_fallback})` so the
	 * block editor picker swatch + every block that picks this slug
	 * track the live Customizer value when set, and fall back to the
	 * legacy hex (byte-identical to the pre-filter render) when not.
	 *
	 * Token sources:
	 *   • Static slots (always emitted to :root): base / surface / text
	 *     / primary / secondary / accent / link / heading / text-muted
	 *     / link-hover / primary-hover.
	 *   • Gated slots (emitted only on palette opt-in): on-primary /
	 *     on-secondary / on-accent / on-surface / border / surface.
	 *
	 * Existing slugs (already in static theme.json) preserve their
	 * legacy hex fallback so currently-rendered block colors don't
	 * shift. New slugs (link-hover, primary-hover, text-muted, border,
	 * on-*) use the PHP-computed default from `customify_color_*`
	 * helpers as the var() fallback.
	 *
	 * @return array
	 */
	function customify_color_palette_for_theme_json() {
		// Slot values resolve to: saved theme_mod, else slot default.
		// We use these to seed the var() fallback for derived slugs so
		// the literal in the fallback matches what the bundled SCSS
		// would compute for an unsaved site.
		$slots = customify_color_get_slots();

		// Precompute fallback hexes per the color-token-derivation spec
		// formulas. Fresh sites (no Palette opt-in) get the literal hex
		// inside `var(--customify-X, {hex})` — block authors still see
		// concrete swatches in the picker. Opt-in sites resolve through
		// to the live Customizer value via the cascade.
		$text_muted_hex    = customify_color_mix_hex( $slots['text'], $slots['base'], 0.70 );
		$border_hex        = customify_color_mix_hex( $slots['text'], $slots['base'], 0.09 ); // 9% — see border_default in customify_color_palette_root_css(); matches grayscale equivalent of legacy #eaecee
		$border_strong_hex = customify_color_solve_border_strong( $slots['text'], $slots['base'] );

		// Container fallbacks — solve P per spec §4, mix, then apply the
		// chroma cap (0.04) so the picker swatch matches what the :root
		// emits. High-chroma brands (yellow / lime / hot pink) would
		// otherwise yield oversaturated container tints (e.g. accent yellow
		// → bright #ffe595 instead of soft cream #f0e6c9).
		$container_max_chroma = 0.04;
		$p_primary       = customify_color_solve_container_p( $slots['primary'],   $slots['base'] );
		$p_secondary     = customify_color_solve_container_p( $slots['secondary'], $slots['base'] );
		$p_accent        = customify_color_solve_container_p( $slots['accent'],    $slots['base'] );
		$container_p_hex = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['primary'],   $slots['base'], $p_primary ),   $container_max_chroma );
		$container_s_hex = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['secondary'], $slots['base'], $p_secondary ), $container_max_chroma );
		$container_a_hex = customify_color_chroma_cap_oklab( customify_color_mix_hex( $slots['accent'],    $slots['base'], $p_accent ),    $container_max_chroma );

		// Lean 12-entry palette — every slug a real design choice a
		// block author would reach for. Pair-order layout so each brand
		// color sits next to its container partner in the picker:
		//
		//   Primary · Primary Container · Secondary · Secondary Container
		//   · Accent · Accent Container · Text · Surface · Base
		//   · Text Muted · Border · Border Strong
		//
		// Hidden from picker (still emitted to :root as theme internals):
		//   on-primary, on-secondary, on-accent, on-surface,
		//   on-primary-container, on-secondary-container, on-accent-container.
		//
		// The on-* family is for theme CSS rules (button text, .is-style-card
		// foreground safety net, etc.) — block authors typically don't pick
		// "text on primary" from a palette swatch; they use the source
		// brand color directly as text on the container, and theme button
		// rules auto-apply on-* via `.has-X-background-color` matchers.
		//
		// Deliberately omitted from the picker entirely:
		//   • link / link-hover / heading — handled by global styles.
		//   • primary-hover — hover STATE; CSS pseudo-class territory.
		//   • background / light-gray / dark-gray — legacy noise.
		return array(
			// ─── Brand + container pairs (per Material Design 3).
			array(
				'slug'  => 'primary',
				'name'  => __( 'Primary', 'customify' ),
				'color' => 'var(--customify-primary, ' . $slots['primary'] . ')',
			),
			array(
				'slug'  => 'primary-container',
				'name'  => __( 'Primary Container', 'customify' ),
				// Soft tint of primary (spec §4). Default palette → ~light
				// blue-grey; user-saved primary → tinted accordingly.
				'color' => 'var(--customify-primary-container, ' . $container_p_hex . ')',
			),
			array(
				'slug'  => 'secondary',
				'name'  => __( 'Secondary', 'customify' ),
				'color' => 'var(--customify-secondary, ' . $slots['secondary'] . ')',
			),
			array(
				'slug'  => 'secondary-container',
				'name'  => __( 'Secondary Container', 'customify' ),
				'color' => 'var(--customify-secondary-container, ' . $container_s_hex . ')',
			),
			array(
				'slug'  => 'accent',
				'name'  => __( 'Accent', 'customify' ),
				'color' => 'var(--customify-accent, ' . $slots['accent'] . ')',
			),
			array(
				'slug'  => 'accent-container',
				'name'  => __( 'Accent Container', 'customify' ),
				'color' => 'var(--customify-accent-container, ' . $container_a_hex . ')',
			),

			// ─── Text + canvas axis.
			// Slug intentionally named `body-text` NOT `text` to avoid the
			// `.has-text-color` collision: WP auto-generates
			// `.has-{slug}-color { color: var(--wp--preset--color--{slug}) !important; }`
			// for every palette slug. When the slug is literally `text`,
			// the generated rule (`.has-text-color { color: var(--text) !important }`)
			// matches the WP marker class that's added to ANY block whose
			// text color was set manually (inline style or a different
			// slug pick) — overriding the user's actual color choice with
			// the text slot value. Renaming the slug to `body-text` keeps
			// the picker swatch labelled "Body Text" while sidestepping
			// the marker-class collision. The CSS var() target stays
			// `--customify-text` (the underlying slot key is unchanged).
			array(
				'slug'  => 'body-text',
				'name'  => __( 'Body Text', 'customify' ),
				'color' => 'var(--customify-text, ' . $slots['text'] . ')',
			),
			array(
				'slug'  => 'surface',
				'name'  => __( 'Surface', 'customify' ),
				'color' => 'var(--customify-surface, ' . $slots['surface'] . ')',
			),
			array(
				'slug'  => 'base',
				'name'  => __( 'Base', 'customify' ),
				'color' => 'var(--customify-base, ' . $slots['base'] . ')',
			),

			// ─── Derived / chrome.
			array(
				'slug'  => 'text-muted',
				'name'  => __( 'Text Muted', 'customify' ),
				// Derived from text + base (spec §2). Auto-updates when
				// Text or Base changes.
				'color' => 'var(--customify-text-muted, ' . $text_muted_hex . ')',
			),
			// Slugs `divider` / `divider-strong` are renamed from spec's
			// `border` / `border-strong` to dodge the same WP marker-class
			// collision that hit the `text` slug (see body-text note above):
			// WP adds `has-border-color` as a marker class to ANY block whose
			// border color was set via the Border panel — so a generated
			// `.has-border-color { color: var(--border) !important }` rule
			// would force the text color to the border value on every
			// border-styled block. The :root token names (--customify-border,
			// --customify-border-strong) stay unchanged because they're
			// theme-internal — only the picker slug is renamed.
			array(
				'slug'  => 'divider',
				'name'  => __( 'Divider', 'customify' ),
				// Decorative-only (~1.35:1 vs base, WCAG-exempt). For
				// functional control boundaries (form input borders,
				// button outlines) use `divider-strong`. Spec §2.
				'color' => 'var(--customify-border, ' . $border_hex . ')',
			),
			array(
				'slug'  => 'divider-strong',
				'name'  => __( 'Divider Strong', 'customify' ),
				// WCAG 1.4.11 ≥3:1 vs base — for form input borders and
				// any boundary that's the ONLY cue identifying a control.
				// Spec §2 (solved P).
				'color' => 'var(--customify-border-strong, ' . $border_strong_hex . ')',
			),
		);

		// ───────────────────────────────────────────────────────────
		// Legacy slugs (text / link / heading / background / light-gray /
		// dark-gray) — DELIBERATELY OMITTED per project owner decision.
		//
		// The original static theme.json palette listed these slugs and
		// 30K sites with block markup `class="has-{slug}-color"` rely
		// on the WP-generated `--wp--preset--color--{slug}` declarations
		// + `.has-{slug}-color { color: var(...) !important }` rules.
		// Removing the slugs means those rules are no longer emitted,
		// so legacy block markup falls back to the body text cascade
		// (loses the explicitly-picked color).
		//
		// Trade-off explicitly accepted by the project owner: clean
		// picker UX wins over backward compat. Old block markup that
		// loses color can be manually re-picked using one of the 12
		// design-purposeful slugs above (e.g. `text` → `body-text`,
		// `heading` → `body-text` or whatever the designer intended).
		//
		// This is a CONSCIOUS regression. Documented as such in
		// SPEC §3.7 + §8.11. Sites that need the legacy slugs back
		// can override the filter via wp_theme_json_data_theme
		// (priority > default) and re-add them.
	}
}

if ( ! function_exists( 'customify_palette_inject_into_theme_json' ) ) {
	/**
	 * Replace the theme.json color palette at runtime with the
	 * Customify dynamic palette. Hooks `wp_theme_json_data_theme`
	 * which fires BEFORE WP renders `--wp--preset--color--*` so the
	 * generated declarations carry the var() reference (not a frozen
	 * hex snapshot).
	 *
	 * The replacement is conditional:
	 *   • WP <6.1: skip (var() in palette color unsupported; would
	 *     render raw "var(--customify-X)" text in the picker).
	 *   • WP >=6.1: replace the entire `settings.color.palette` array.
	 *
	 * @param WP_Theme_JSON_Data $theme_json
	 * @return WP_Theme_JSON_Data
	 */
	function customify_palette_inject_into_theme_json( $theme_json ) {
		if ( version_compare( get_bloginfo( 'version' ), '6.1', '<' ) ) {
			return $theme_json;
		}

		$palette = customify_color_palette_for_theme_json();

		// update_with() merges; passing the palette array under the
		// same path replaces it wholesale (arrays in theme.json don't
		// merge per-element, they replace).
		$theme_json->update_with( array(
			'version'  => 3,
			'settings' => array(
				'color' => array(
					'palette' => $palette,
				),
			),
		) );

		return $theme_json;
	}
	add_filter( 'wp_theme_json_data_theme', 'customify_palette_inject_into_theme_json' );
}
