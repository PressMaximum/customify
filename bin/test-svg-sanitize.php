<?php
/**
 * Sanity harness for customify_sanitize_svg() and the preset SVG library.
 *
 * The theme has no PHPUnit harness, and this function is a security boundary:
 * its output is echoed UNESCAPED on the front end of 30,000+ sites. So it gets
 * a runnable check that needs nothing but a WordPress install.
 *
 * Run from the theme directory:
 *
 *     wp eval-file bin/test-svg-sanitize.php          # WP-CLI (preferred)
 *     php bin/test-svg-sanitize.php /path/to/wp-load.php
 *
 * Exits non-zero when any case fails, so it can be dropped into CI as-is.
 *
 * @package Customify
 */

// Bootstrap WordPress when not already loaded (wp_kses lives there).
if ( ! function_exists( 'wp_kses' ) ) {
	$bootstrap = isset( $argv[1] ) ? $argv[1] : dirname( __DIR__, 4 ) . '/wp-load.php';
	if ( ! file_exists( $bootstrap ) ) {
		fwrite( STDERR, "Could not find wp-load.php. Pass its path as argv[1].\n" );
		exit( 2 );
	}
	require_once $bootstrap;
}

if ( ! function_exists( 'customify_sanitize_svg' ) ) {
	require_once dirname( __DIR__ ) . '/inc/template-functions.php';
}
if ( ! function_exists( 'customify_get_svg_icons' ) ) {
	require_once dirname( __DIR__ ) . '/inc/icons-svg.php';
}

$failures = 0;
$checks   = 0;

/**
 * Assert that sanitising $input produces output satisfying $expect.
 *
 * @param string          $name   Case name.
 * @param string          $input  Raw input.
 * @param callable|string $expect Callable receiving the output, or the exact
 *                                expected output string.
 */
function customify_svg_case( $name, $input, $expect ) {
	global $failures, $checks;

	$checks++;
	$out = customify_sanitize_svg( $input );
	$ok  = is_callable( $expect ) ? (bool) $expect( $out ) : ( $expect === $out );

	printf(
		"%s  %s\n",
		$ok ? 'PASS' : 'FAIL',
		$name
	);

	if ( ! $ok ) {
		$failures++;
		printf( "      in : %s\n", substr( str_replace( "\n", ' ', $input ), 0, 160 ) );
		printf( "      out: %s\n", substr( str_replace( "\n", ' ', $out ), 0, 160 ) );
	}
}

/** Helper: output must not contain $needle (case-insensitive). */
function customify_svg_lacks( $needle ) {
	return function ( $out ) use ( $needle ) {
		return false === stripos( $out, $needle );
	};
}

echo "== customify_sanitize_svg() ==\n";

// --------------------------------------------------------------- rejections
customify_svg_case( 'empty string rejected', '', '' );
customify_svg_case( 'null rejected', null, '' );
customify_svg_case( 'plain text rejected', 'hello world', '' );
customify_svg_case(
	'non-svg root rejected (bare script)',
	'<script>alert(1)</script>',
	''
);
customify_svg_case(
	'non-svg root rejected (html leading the svg)',
	'<div onclick="alert(1)"><svg viewBox="0 0 24 24"><path d="M0 0"/></svg></div>',
	''
);
customify_svg_case(
	'oversized payload rejected (> 20KB)',
	'<svg viewBox="0 0 24 24">' . str_repeat( '<path d="M0 0"/>', 2000 ) . '</svg>',
	''
);

// -------------------------------------------------------------- strippings
customify_svg_case(
	'nested <script> stripped',
	'<svg viewBox="0 0 24 24"><script>alert(document.cookie)</script><path d="M0 0"/></svg>',
	customify_svg_lacks( '<script' )
);
customify_svg_case(
	'onload handler stripped',
	'<svg viewBox="0 0 24 24" onload="alert(1)"><path d="M0 0"/></svg>',
	customify_svg_lacks( 'onload' )
);
customify_svg_case(
	'onclick/onmouseover on a child stripped',
	'<svg viewBox="0 0 24 24"><path onclick="alert(1)" onmouseover="alert(2)" d="M0 0"/></svg>',
	function ( $out ) {
		return false === stripos( $out, 'onclick' ) && false === stripos( $out, 'onmouseover' );
	}
);
customify_svg_case(
	'<foreignObject> HTML stripped',
	'<svg viewBox="0 0 24 24"><foreignObject><body xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></body></foreignObject></svg>',
	function ( $out ) {
		return false === stripos( $out, 'foreignobject' ) && false === stripos( $out, '<script' );
	}
);
customify_svg_case(
	'<image> with remote href stripped',
	'<svg viewBox="0 0 24 24"><image href="https://evil.test/x.png"/></svg>',
	customify_svg_lacks( '<image' )
);
customify_svg_case(
	'javascript: href neutralised',
	'<svg viewBox="0 0 24 24"><use href="javascript:alert(1)"/></svg>',
	customify_svg_lacks( 'javascript:' )
);
customify_svg_case(
	'javascript: xlink:href neutralised',
	'<svg viewBox="0 0 24 24"><use xlink:href="javascript:alert(1)"/></svg>',
	customify_svg_lacks( 'javascript:' )
);
customify_svg_case(
	'<a> wrapper stripped',
	'<svg viewBox="0 0 24 24"><a href="https://evil.test"><path d="M0 0"/></a></svg>',
	customify_svg_lacks( '<a ' )
);
customify_svg_case(
	'<animate> attributeName=href stripped',
	'<svg viewBox="0 0 24 24"><animate attributeName="href" values="javascript:alert(1)"/></svg>',
	function ( $out ) {
		return false === stripos( $out, '<animate' ) && false === stripos( $out, 'javascript:' );
	}
);
customify_svg_case(
	'<set> / <handler> stripped',
	'<svg viewBox="0 0 24 24"><set attributeName="onload" to="alert(1)"/></svg>',
	customify_svg_lacks( '<set' )
);

// ------------------------------------------------------------- pass-through
customify_svg_case(
	'clean stroke icon survives intact',
	'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="9" cy="20" r="1.6"/><path d="M2.5 3.5h2.6"/></svg>',
	function ( $out ) {
		return 0 === stripos( $out, '<svg' )
			&& false !== stripos( $out, '<circle' )
			&& false !== stripos( $out, 'stroke-linejoin' )
			&& false !== stripos( $out, 'viewbox' );
	}
);
customify_svg_case(
	'root width/height stripped, viewBox kept',
	'<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24"><path d="M0 0"/></svg>',
	function ( $out ) {
		if ( ! preg_match( '/^<svg\b[^>]*>/i', $out, $m ) ) {
			return false;
		}
		return false === stripos( $m[0], 'width' )
			&& false === stripos( $m[0], 'height' )
			&& false !== stripos( $m[0], 'viewbox' );
	}
);
customify_svg_case(
	'inner <rect> keeps its width/height geometry',
	'<svg width="64" height="64" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/></svg>',
	function ( $out ) {
		if ( ! preg_match( '/<rect\b[^>]*>/i', $out, $m ) ) {
			return false;
		}
		return false !== stripos( $m[0], 'width="18"' )
			&& false !== stripos( $m[0], 'height="14"' );
	}
);
customify_svg_case(
	'Iconify-style leading comment tolerated',
	'<!-- Icon from Tabler by Paweł Kuna - MIT --><svg viewBox="0 0 24 24"><path d="M0 0"/></svg>',
	function ( $out ) {
		return 0 === stripos( $out, '<svg' ) && false === stripos( $out, '<!--' );
	}
);
customify_svg_case(
	'internal fragment <use> kept',
	'<svg viewBox="0 0 24 24"><defs><path id="p" d="M0 0"/></defs><use href="#p"/></svg>',
	function ( $out ) {
		return false !== stripos( $out, '<use' ) && false !== strpos( $out, '#p' );
	}
);
customify_svg_case(
	'idempotent — sanitising twice is a no-op',
	'<svg viewBox="0 0 24 24" onload="alert(1)"><path d="M0 0"/></svg>',
	function ( $out ) {
		return $out === customify_sanitize_svg( $out );
	}
);

// ------------------------------------------------------- preset library
echo "\n== preset library (customify_get_svg_icons) ==\n";

$library = customify_get_svg_icons();
$checks++;
if ( empty( $library ) ) {
	echo "FAIL  library is not empty\n";
	$failures++;
} else {
	printf( "PASS  library is not empty (%d icons)\n", count( $library ) );
}

$style_counts = array();

foreach ( $library as $key => $entry ) {
	$markup = customify_get_svg_icon( $key );
	$style  = isset( $entry['style'] ) ? $entry['style'] : 'outline';

	$style_counts[ $style ] = ( isset( $style_counts[ $style ] ) ? $style_counts[ $style ] : 0 ) + 1;

	// width / height must be absent from the ROOT element only — <rect> and
	// friends legitimately carry them inside the glyph.
	$root = '';
	if ( preg_match( '/^<svg\b[^>]*>/i', $markup, $m ) ) {
		$root = $m[0];
	}

	// The viewBox may be any SQUARE grid: upstream sets disagree (Lucide and
	// Tabler draw on 24, Phosphor on 256) and rescaling by hand would mean
	// editing geometry, which the library deliberately never does.
	$square = false;
	if ( preg_match( '/viewBox="0 0 (\d+(?:\.\d+)?) (\d+(?:\.\d+)?)"/', $root, $vb ) ) {
		$square = ( $vb[1] === $vb[2] );
	}

	// Paint mode must match the declared style, and a filled entry must carry
	// the class the frontend override rule keys off — without it the outline
	// default blanks the glyph.
	if ( 'filled' === $style ) {
		$paint_ok = false !== strpos( $root, 'fill="currentColor"' )
			&& false !== strpos( $root, 'stroke="none"' )
			&& false !== strpos( $root, 'customify-svg-icon--filled' );
	} else {
		$paint_ok = false !== strpos( $root, 'fill="none"' )
			&& false !== strpos( $root, 'stroke="currentColor"' )
			&& false === strpos( $root, 'customify-svg-icon--filled' );
	}

	$checks++;
	$ok = '' !== $markup
		&& '' !== $root
		&& $square
		&& $paint_ok
		&& false === strpos( $root, ' width="' )
		&& false === strpos( $root, ' height="' )
		&& false !== strpos( $markup, 'aria-hidden="true"' )
		&& false !== strpos( $markup, 'focusable="false"' )
		// The theme's own markup must also satisfy the user-input allowlist:
		// anything the sanitiser would strip is a bug in the library entry.
		&& '' !== customify_sanitize_svg( $markup );

	if ( ! $ok ) {
		$failures++;
		printf( "FAIL  preset '%s' (%s) violates the icon style contract\n", $key, $style );
		printf( "      out: %s\n", substr( $markup, 0, 220 ) );
	}
}

if ( ! $failures ) {
	ksort( $style_counts );
	$summary = array();
	foreach ( $style_counts as $style => $n ) {
		$summary[] = "$n $style";
	}
	printf(
		"PASS  every preset honours the square-viewBox / no-size / paint / a11y contract (%s)\n",
		implode( ', ', $summary )
	);
}

$checks++;
if ( '' !== customify_get_svg_icon( 'definitely-not-an-icon' ) ) {
	echo "FAIL  unknown preset key must render nothing\n";
	$failures++;
} else {
	echo "PASS  unknown preset key renders nothing\n";
}

printf( "\n%d checks, %d failures\n", $checks, $failures );

exit( $failures ? 1 : 0 );
