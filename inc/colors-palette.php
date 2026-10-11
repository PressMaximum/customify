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
			'primary'   => '#0e7c7b',
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
		// Button label tokens — GATED on Palette opt-in. Theme buttons read
		// `var(--customify-btn-on-{primary,secondary}, #fff)`, so the 30K
		// legacy install base that never engaged the Palette panel falls back
		// to the historical hard-coded white — a saved light brand color no
		// longer silently flips button labels to black on update. Opt-in sites
		// ALIAS the unconditional on-* tokens, so labels keep the WCAG-contrast
		// auto-flip and the alias re-resolves live when the preview JS drives
		// --customify-on-* during slot drags (no extra preview JS needed). The
		// block brand-bg auto-wire (.has-X-background-color) and the Blocksify
		// fill-button rule keep reading the raw unconditional on-* tokens — they
		// are intentionally NOT gated (see colors-palette $on_primary rationale).
		if ( $has_palette_opt_in ) {
			$lines[] = '--customify-btn-on-primary: var(--customify-on-primary)';
			$lines[] = '--customify-btn-on-secondary: var(--customify-on-secondary)';
		}
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
		$slots = customify_color_get_slots();
		// Brand-first display order to match the Palette section UI.
		// `control` is the LI id (without the `customize-control-` prefix)
		// used by the controls JS to detect whether an open picker is a palette
		// slot — in that case the popup shows a hex input instead of the
		// From-Palette row.
		$slot_payload = array(
			array( 'key' => 'primary',   'label' => 'Primary',   'color' => $slots['primary'],   'control' => 'global_styling_color_primary' ),
			array( 'key' => 'secondary', 'label' => 'Secondary', 'color' => $slots['secondary'], 'control' => 'global_styling_color_secondary' ),
			array( 'key' => 'accent',    'label' => 'Accent',    'color' => $slots['accent'],    'control' => 'customify_palette_accent' ),
			array( 'key' => 'text',      'label' => 'Text',      'color' => $slots['text'],      'control' => 'customify_palette_text' ),
			array( 'key' => 'surface',   'label' => 'Surface',   'color' => $slots['surface'],   'control' => 'customify_palette_surface' ),
			array( 'key' => 'base',      'label' => 'Base',      'color' => $slots['base'],      'control' => 'customify_palette_base' ),
		);

		$asset_file = get_template_directory() . '/build/js/backend/customizer/colors-controls.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset  = require $asset_file;
		$suffix = method_exists( Customify(), 'get_asset_suffix' ) ? Customify()->get_asset_suffix() : '';

		wp_enqueue_script(
			'customify-colors-controls',
			esc_url( get_template_directory_uri() ) . '/build/js/backend/customizer/colors-controls' . $suffix . '.js',
			array_merge( array( 'jquery', 'wp-color-picker', 'customize-controls' ), $asset['dependencies'] ),
			$asset['version'],
			true
		);
		wp_localize_script(
			'customify-colors-controls',
			'CustomifyColorsControls',
			array(
				'slots' => $slot_payload,
			)
		);
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
		// Map of color setting → `--customify-*` token the preview JS should
		// drive. Override-style settings (heading/text/link/…) go through the
		// cascade-fallback branch in the JS so clearing a picker re-engages
		// the slot cascade instead of leaving the stale PHP-baked value.
		$slot_vars = array(
			'global_styling_color_primary'   => '--customify-primary',
			'global_styling_color_secondary' => '--customify-secondary',
			'customify_palette_accent'       => '--customify-accent',
			'customify_palette_text'         => '--customify-text',
			'customify_palette_surface'      => '--customify-surface',
			'customify_palette_base'         => '--customify-base',
			'global_styling_color_heading'   => '--customify-heading',
			'global_styling_color_text'      => '--customify-body-text',
			'global_styling_color_link'      => '--customify-link',
			'global_styling_color_link_hover' => '--customify-link-hover',
			'global_styling_color_border'    => '--customify-border',
			'global_styling_color_meta'      => '--customify-text-muted',
			'global_styling_color_w_title'   => '--customify-widget-title',
		);

		// Resolve the webpack-emitted asset so cache-busting ?ver= flips on
		// every rebuild and WP dependencies (customize-preview, wp-util, …) are
		// enqueued. Mirrors the pattern used by Customify_Customizer::preview_js().
		$asset_file = get_template_directory() . '/build/js/backend/customizer/colors-preview.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset  = require $asset_file;
		$suffix = method_exists( Customify(), 'get_asset_suffix' ) ? Customify()->get_asset_suffix() : '';

		wp_enqueue_script(
			'customify-colors-preview',
			esc_url( get_template_directory_uri() ) . '/build/js/backend/customizer/colors-preview' . $suffix . '.js',
			array_merge( array( 'customize-preview' ), $asset['dependencies'] ),
			$asset['version'],
			true
		);
		wp_localize_script(
			'customify-colors-preview',
			'CustomifyColorsPreview',
			array(
				'slotVars' => $slot_vars,
			)
		);
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
