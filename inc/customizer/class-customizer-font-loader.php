<?php
/**
 * Font-loading optimizations for the theme.
 *
 * Three concerns live here, all sharing the same data sources:
 *
 *  1. Dedupe with WP core. wp_print_font_faces() (WP 6.5+) auto-emits
 *     @font-face for every family in wp_get_global_settings() — that
 *     means theme.json declarations and Font-Library-activated fonts.
 *     wp_handled_names() returns the exact list so theme-side emitters
 *     can skip those families and avoid duplicate CSS in the HTML.
 *
 *  2. DNS / TLS warmup for Google Fonts (preconnect). Browser
 *     discovers fonts.gstatic.com only after parsing the Google CSS;
 *     emitting <link rel=preconnect> very early in <head> shaves
 *     ~100-300ms off the first font byte.
 *
 *  3. Critical body-font preload. The body font appears on every
 *     page above the fold, so paying for one preload to fetch it in
 *     parallel with CSS shaves 200-500ms off LCP. We deliberately
 *     preload exactly ONE font — preloading more steals bandwidth
 *     from above-the-fold critical resources.
 *
 * Helper is read-only and static; no instance state. Per-request
 * memoisation lives in a static property — same lifetime as WP's
 * request handling.
 */
class Customify_Customizer_Font_Loader {

	/**
	 * Per-request cache for wp_handled_names() — built lazily because
	 * wp_get_global_settings() does a non-trivial theme.json merge.
	 *
	 * @var array<string,true>|null
	 */
	private static $handled_cache = null;

	/**
	 * Names of font families WP will print @font-face for automatically
	 * via wp_print_font_faces() (WP 6.5+). Returns empty on WP < 6.5
	 * so legacy installs continue to receive theme-emitted CSS.
	 *
	 * Map is name → true for O(1) `isset()` lookups in tight loops
	 * inside the CSS emitters.
	 *
	 * @return array<string,true>
	 */
	public static function wp_handled_names() {
		if ( null !== self::$handled_cache ) {
			return self::$handled_cache;
		}
		if ( ! function_exists( 'wp_get_global_settings' )
		  || ! function_exists( 'wp_print_font_faces' ) ) {
			return self::$handled_cache = array();
		}

		$settings = wp_get_global_settings();
		$families = isset( $settings['typography']['fontFamilies'] )
			? $settings['typography']['fontFamilies']
			: array();

		$out = array();
		foreach ( (array) $families as $key => $entry ) {
			// Two shapes possible:
			//   (a) flat list of entries:    [{name: 'Inter', ...}, ...]
			//   (b) per-origin grouping:     ['theme' => [...], 'custom' => [...]]
			// (b) appears when WP nests by origin (theme/custom); (a)
			// is the common merged shape. Handle both defensively so
			// upstream WP changes don't break the dedupe.
			if ( isset( $entry['name'] ) ) {
				$out[ $entry['name'] ] = true;
				continue;
			}
			if ( is_array( $entry ) ) {
				foreach ( $entry as $sub ) {
					if ( is_array( $sub ) && isset( $sub['name'] ) ) {
						$out[ $sub['name'] ] = true;
					}
				}
			}
		}
		return self::$handled_cache = $out;
	}

	/**
	 * Build the Google Fonts preconnect <link> tags. Two tags: one for
	 * the CSS host (fonts.googleapis.com), one for the file host
	 * (fonts.gstatic.com) which requires crossorigin because font
	 * files are fetched in CORS mode. Returns empty string when no
	 * Google font is active so we don't waste DNS sockets.
	 *
	 * @return string
	 */
	public static function preconnect_tags() {
		if ( ! class_exists( 'Customify_Customizer_Auto_CSS' ) ) {
			return '';
		}
		$url = Customify_Customizer_Auto_CSS::get_instance()->get_font_url();
		if ( empty( $url ) ) {
			return '';
		}
		return "<link rel='preconnect' href='https://fonts.googleapis.com'>\n"
			 . "<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>\n";
	}

	/**
	 * Build a <link rel="preload"> for the body-text font so the
	 * browser can fetch the file in parallel with the rest of the
	 * page's CSS, rather than waiting for CSS parse to discover the
	 * @font-face rule.
	 *
	 * Targets ONLY the body font (Customify's `global_typography_base_p`
	 * setting, selector `body`) — preloading more than one font would
	 * steal bandwidth from above-the-fold critical resources. Returns
	 * empty when:
	 *  - the setting isn't populated;
	 *  - the body font isn't local (Google fonts have dynamic URLs
	 *    that vary per UA, so preloading is impractical);
	 *  - the resolver can't find a font-face for the chosen family
	 *    (eg. user removed the font from the Library after picking).
	 *
	 * @return string
	 */
	public static function preload_body_font_tag() {
		if ( ! function_exists( 'Customify' ) ) {
			return '';
		}
		$body_typo = Customify()->get_setting( 'global_typography_base_p' );
		if ( empty( $body_typo['font'] ) || empty( $body_typo['font_type'] ) ) {
			return '';
		}
		if ( ! in_array( $body_typo['font_type'], array( 'theme', 'library' ), true ) ) {
			return ''; // Google + system fonts: no usable URL to preload.
		}

		// Prefer the actual weight/style the user picked for body so
		// the preload matches the very font-face the page will paint
		// with — preloading a different variant would just race the
		// real one without speeding it up. `font_weight` already uses
		// the Google-variant notation (eg. '400', '400i', '700').
		$preferred = isset( $body_typo['font_weight'] ) && '' !== $body_typo['font_weight']
			? (string) $body_typo['font_weight']
			: '400';
		$face = self::resolve_face( $body_typo['font'], $body_typo['font_type'], $preferred );
		if ( empty( $face['src'] ) ) {
			return '';
		}

		$ext  = strtolower( pathinfo( $face['src'], PATHINFO_EXTENSION ) );
		$type = ( 'woff2' === $ext ) ? 'font/woff2' : 'font/' . $ext;

		return sprintf(
			'<link rel="preload" href="%s" as="font" type="%s" crossorigin>%s',
			esc_url( $face['src'] ),
			esc_attr( $type ),
			"\n"
		);
	}

	/**
	 * Look up the font-face entry for preloading, preferring (in
	 * order): the exact variant the caller asks for, then a 400-normal
	 * fallback, then the first declared face. Read from the matching
	 * resolver class so the lookup honours the same data source the
	 * @font-face emitter uses.
	 *
	 * @param string $font_name
	 * @param string $font_type 'theme' or 'library'
	 * @param string $preferred_variant Google-variant token (eg. '400', '400i', '700')
	 * @return array
	 */
	private static function resolve_face( $font_name, $font_type, $preferred_variant = '400' ) {
		$resolver = null;
		if ( 'theme' === $font_type && class_exists( 'Customify_Customizer_Theme_Fonts' ) ) {
			$resolver = new Customify_Customizer_Theme_Fonts();
		} elseif ( 'library' === $font_type && class_exists( 'Customify_Customizer_Font_Library' ) ) {
			$resolver = new Customify_Customizer_Font_Library();
		}
		if ( ! $resolver ) {
			return array();
		}
		$data = $resolver->get_for_frontend();
		if ( empty( $data[ $font_name ]['font_faces'] ) ) {
			return array();
		}

		// First pass: exact variant match (the one the page will use).
		foreach ( $data[ $font_name ]['font_faces'] as $face ) {
			if ( isset( $face['variant'] ) && $preferred_variant === $face['variant'] ) {
				return $face;
			}
		}
		// Second pass: 400-normal — the most common body face — when
		// the preferred variant doesn't exist in this family.
		if ( '400' !== $preferred_variant ) {
			foreach ( $data[ $font_name ]['font_faces'] as $face ) {
				if ( isset( $face['variant'] ) && '400' === $face['variant'] ) {
					return $face;
				}
			}
		}
		return $data[ $font_name ]['font_faces'][0];
	}
}
