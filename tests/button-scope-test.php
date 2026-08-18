<?php
/**
 * Regression tests for the global button scope.
 *
 * Guards the fix for the 0.4.21–0.4.22 leak where a saved
 * `customify_button_styling` value repainted component chrome — Blocksify's
 * product-gallery thumbnails/arrows and lightGallery's lightbox controls in
 * particular — because the scope matched the bare `button` element and tried
 * to subtract non-buttons with a denylist of class names.
 *
 * Two layers are covered:
 *   1. The Customizer scope    — customify_get_button_styling_selectors()
 *      in inc/customizer/configs/buttons-forms.php.
 *   2. The default SCSS skin   — read back from the COMPILED
 *      build/css/frontend/style-theme.css so the SCSS is tested as shipped.
 *      Skipped with a notice when build/ has not been generated.
 *
 * Run with: php tests/button-scope-test.php
 */

// ─── Minimal WordPress stubs ────────────────────────────────────────────────
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return $text;
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

require_once __DIR__ . '/../inc/customizer/configs/buttons-forms.php';

// ─── Assertion helpers ──────────────────────────────────────────────────────
$GLOBALS['customify_test_failures'] = 0;

function customify_test_assert( $condition, $message ) {
	if ( $condition ) {
		return;
	}
	$GLOBALS['customify_test_failures']++;
	echo "FAIL: {$message}\n";
}

function customify_test_assert_same( $expected, $actual, $message ) {
	customify_test_assert(
		$expected === $actual,
		$message . ' (expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . ')'
	);
}

// ─── A tiny CSS matcher ─────────────────────────────────────────────────────
// Deliberately supports only the grammar the two scopes emit: descendant
// combinators, type/class/attribute simple selectors and the functional
// pseudo-classes :not() / :is() / :where(). That is enough to answer the one
// question these tests ask — "does this element fall inside the scope?" —
// without pulling in a CSS parser dependency.

/**
 * Split on whitespace that sits outside brackets and parentheses.
 *
 * @param string $selector Complex selector.
 * @return array<int, string> Compound selectors, outermost ancestor first.
 */
function customify_test_split_compounds( $selector ) {
	$parts  = array();
	$buffer = '';
	$depth  = 0;
	$length = strlen( $selector );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $selector[ $i ];

		if ( '(' === $char || '[' === $char ) {
			$depth++;
		} elseif ( ')' === $char || ']' === $char ) {
			$depth--;
		} elseif ( ' ' === $char && 0 === $depth ) {
			if ( '' !== trim( $buffer ) ) {
				$parts[] = trim( $buffer );
			}
			$buffer = '';
			continue;
		}

		$buffer .= $char;
	}

	if ( '' !== trim( $buffer ) ) {
		$parts[] = trim( $buffer );
	}

	return $parts;
}

/**
 * Tokenize one compound selector into its simple selectors.
 *
 * @param string $compound Compound selector, e.g. `button:is(a, b):not(c)`.
 * @return array<int, string>
 */
function customify_test_tokenize( $compound ) {
	$tokens = array();
	$buffer = '';
	$depth  = 0;
	$length = strlen( $compound );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $compound[ $i ];

		if ( 0 === $depth && ( '.' === $char || '[' === $char || ':' === $char || '#' === $char ) && '' !== $buffer ) {
			$tokens[] = $buffer;
			$buffer   = '';
		}

		if ( '(' === $char || '[' === $char ) {
			$depth++;
		} elseif ( ')' === $char || ']' === $char ) {
			$depth--;
		}

		$buffer .= $char;
	}

	if ( '' !== $buffer ) {
		$tokens[] = $buffer;
	}

	return $tokens;
}

/**
 * Does one simple selector match the element fixture?
 *
 * @param string $token Simple selector.
 * @param array  $el    Element fixture (tag / classes / attrs).
 * @return bool
 */
function customify_test_token_matches( $token, $el ) {
	// Functional pseudo-classes.
	if ( preg_match( '/^:(not|is|where|matches)\((.*)\)$/s', $token, $m ) ) {
		$inner = customify_split_selector_list( $m[2] );
		$any   = false;
		foreach ( $inner as $candidate ) {
			if ( customify_test_compound_matches( $candidate, $el ) ) {
				$any = true;
				break;
			}
		}
		return 'not' === $m[1] ? ! $any : $any;
	}

	// State pseudo-classes are irrelevant to scope membership.
	if ( ':' === $token[0] ) {
		return true;
	}

	// Class.
	if ( '.' === $token[0] ) {
		return in_array( substr( $token, 1 ), $el['classes'], true );
	}

	// Attribute: [name], [name="v"], [name*="v"] (quotes optional — the
	// compiled CSS is minified and drops them).
	if ( '[' === $token[0] ) {
		$body = substr( $token, 1, -1 );

		if ( preg_match( '/^([\w-]+)(\*?)=["\']?(.*?)["\']?$/', $body, $m ) ) {
			if ( ! isset( $el['attrs'][ $m[1] ] ) ) {
				return false;
			}
			return '*' === $m[2]
				? false !== strpos( $el['attrs'][ $m[1] ], $m[3] )
				: $el['attrs'][ $m[1] ] === $m[3];
		}

		return isset( $el['attrs'][ $body ] );
	}

	// Type selector.
	return $token === $el['tag'];
}

/**
 * Does a compound selector match the element fixture?
 *
 * @param string $compound Compound selector.
 * @param array  $el       Element fixture.
 * @return bool
 */
function customify_test_compound_matches( $compound, $el ) {
	foreach ( customify_test_tokenize( trim( $compound ) ) as $token ) {
		if ( ! customify_test_token_matches( $token, $el ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Does any selector in the list match the element fixture?
 *
 * @param string $list Selector list.
 * @param array  $el   Element fixture (may carry an `ancestors` array).
 * @return bool
 */
function customify_test_scope_matches( $list, $el ) {
	foreach ( customify_split_selector_list( $list ) as $selector ) {
		$compounds = customify_test_split_compounds( $selector );
		$subject   = array_pop( $compounds );

		if ( ! customify_test_compound_matches( $subject, $el ) ) {
			continue;
		}

		$ancestors_ok = true;
		foreach ( $compounds as $ancestor_selector ) {
			$found = false;
			foreach ( $el['ancestors'] as $ancestor ) {
				if ( customify_test_compound_matches( $ancestor_selector, $ancestor ) ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$ancestors_ok = false;
				break;
			}
		}

		if ( $ancestors_ok ) {
			return true;
		}
	}

	return false;
}

/**
 * Specificity of a complex selector as [ids, classes, types].
 *
 * @param string $selector Complex selector.
 * @return array{0:int,1:int,2:int}
 */
function customify_test_specificity( $selector ) {
	$score = array( 0, 0, 0 );

	foreach ( customify_test_split_compounds( $selector ) as $compound ) {
		foreach ( customify_test_tokenize( $compound ) as $token ) {
			if ( preg_match( '/^:(not|is|matches|has)\((.*)\)$/s', $token, $m ) ) {
				$best = array( 0, 0, 0 );
				foreach ( customify_split_selector_list( $m[2] ) as $inner ) {
					$candidate = customify_test_specificity( $inner );
					if ( $candidate > $best ) {
						$best = $candidate;
					}
				}
				$score[0] += $best[0];
				$score[1] += $best[1];
				$score[2] += $best[2];
				continue;
			}

			// :where() contributes nothing — that is the point of using it.
			if ( preg_match( '/^:where\(/', $token ) ) {
				continue;
			}

			if ( '#' === $token[0] ) {
				$score[0]++;
			} elseif ( '.' === $token[0] || '[' === $token[0] || ( ':' === $token[0] && '::' !== substr( $token, 0, 2 ) ) ) {
				$score[1]++;
			} else {
				$score[2]++;
			}
		}
	}

	return $score;
}

// ─── Element fixtures ───────────────────────────────────────────────────────
$form_ancestor = array(
	array(
		'tag'     => 'form',
		'classes' => array( 'cart' ),
		'attrs'   => array( 'class' => 'cart' ),
	),
);

/**
 * Build an element fixture.
 *
 * @param string $tag       Tag name.
 * @param string $classes   Space-separated class attribute (empty = unclassed).
 * @param array  $attrs     Extra attributes.
 * @param array  $ancestors Ancestor fixtures.
 * @return array
 */
function customify_test_el( $tag, $classes = '', $attrs = array(), $ancestors = array() ) {
	$class_list = '' === $classes ? array() : preg_split( '/\s+/', trim( $classes ) );

	if ( '' !== $classes ) {
		$attrs['class'] = $classes;
	}

	return array(
		'tag'       => $tag,
		'classes'   => $class_list,
		'attrs'     => $attrs,
		'ancestors' => $ancestors,
	);
}

$submit = array( 'type' => 'submit' );
$plain  = array( 'type' => 'button' );

// label => [ element, in Customizer scope?, in default SCSS skin? ]
$cases = array(
	// ── Must stay styled (backward compatibility) ───────────────────────
	'WooCommerce single add to cart'   => array( customify_test_el( 'button', 'single_add_to_cart_button button alt', $submit, $form_ancestor ), true, true ),
	'WooCommerce apply coupon'         => array( customify_test_el( 'button', 'button', $submit, $form_ancestor ), true, true ),
	'WooCommerce login submit'         => array( customify_test_el( 'button', 'woocommerce-button button woocommerce-form-login__submit', $submit, $form_ancestor ), true, true ),
	'WooCommerce register submit'      => array( customify_test_el( 'button', 'woocommerce-Button button', $submit, $form_ancestor ), true, true ),
	'WooCommerce product search'       => array( customify_test_el( 'button', '', $submit, $form_ancestor ), true, true ),
	'Comment form submit input'        => array( customify_test_el( 'input', 'submit', $submit, $form_ancestor ), true, true ),
	'Reset input'                      => array( customify_test_el( 'input', '', array( 'type' => 'reset' ), $form_ancestor ), true, true ),
	'Unclassed content button'         => array( customify_test_el( 'button', '', array() ), true, true ),
	'Classed form button, no type'     => array( customify_test_el( 'button', 'wpforms-submit', array(), $form_ancestor ), true, true ),
	'Third-party form submit button'   => array( customify_test_el( 'button', 'mc-newsletter-submit', $submit, $form_ancestor ), true, true ),
	'Theme .button link'               => array( customify_test_el( 'a', 'button' ), true, true ),
	'Core Button block link'           => array( customify_test_el( 'a', 'wp-block-button__link wp-element-button' ), true, true ),
	'theme.json element button'        => array( customify_test_el( 'button', 'wp-element-button', $submit, $form_ancestor ), true, true ),

	// ── Must NOT be touched: Blocksify product gallery ──────────────────
	'Blocksify gallery thumbnail'      => array( customify_test_el( 'button', 'bsy-gallery__thumb', $plain ), false, false ),
	'Blocksify gallery thumb arrow'    => array( customify_test_el( 'button', 'bsy-gallery__thumb-nav is-next', $plain ), false, false ),
	'Blocksify gallery arrow'          => array( customify_test_el( 'button', 'bsy-gallery__arrow', $plain ), false, false ),

	// ── Must NOT be touched: lightGallery lightbox controls ─────────────
	'lightGallery prev'                => array( customify_test_el( 'button', 'lg-prev lg-icon', $plain ), false, false ),
	'lightGallery next'                => array( customify_test_el( 'button', 'lg-next lg-icon', $plain ), false, false ),
	'lightGallery close'               => array( customify_test_el( 'button', 'lg-close lg-icon', $plain ), false, false ),
	'lightGallery zoom in'             => array( customify_test_el( 'button', 'lg-zoom-in lg-icon', $plain ), false, false ),
	'lightGallery fullscreen'          => array( customify_test_el( 'button', 'lg-fullscreen lg-icon', $plain ), false, false ),
	'lightGallery autoplay'            => array( customify_test_el( 'button', 'lg-autoplay-button lg-icon', $plain ), false, false ),
	'lightGallery share'               => array( customify_test_el( 'button', 'lg-share lg-icon', $plain ), false, false ),

	// ── Must NOT be touched: other component chrome ─────────────────────
	'PhotoSwipe close (no type attr)'  => array( customify_test_el( 'button', 'pswp__button pswp__button--close' ), false, false ),
	'Mobile menu toggle'               => array( customify_test_el( 'button', 'menu-mobile-toggle item-button', $plain ), false, false ),
	'Quantity stepper (classic)'       => array( customify_test_el( 'button', 'input-pm-act input-pm-plus', $plain, $form_ancestor ), false, false ),
	'Quantity stepper (Cart block)'    => array( customify_test_el( 'button', 'wc-block-components-quantity-selector__button', $plain ), false, false ),
	'Show/hide password toggle'        => array( customify_test_el( 'button', 'show-password-input', $plain, $form_ancestor ), false, false ),
	'Core Search block submit'         => array( customify_test_el( 'button', 'wp-block-search__button', $submit, $form_ancestor ), false, false ),
	'Gutenberg components button'      => array( customify_test_el( 'button', 'components-button', $plain ), false, false ),
	'Customizer edit shortcut'         => array( customify_test_el( 'button', 'customize-partial-edit-shortcut-button', $plain ), false, false ),
	'Style guide edit pencil'          => array( customify_test_el( 'button', 'csg-edit', $plain ), false, false ),
	'Quicktags editor button'          => array( customify_test_el( 'input', 'ed_button button button-small', $plain ), false, false ),

	// ── Theme-owned icon chrome: out of the Customizer scope, but still
	//    carrying the bundled default skin (documented divergence). ──────
	'Search submit icon'               => array( customify_test_el( 'button', 'search-submit', $submit, $form_ancestor ), false, true ),
);

// ─── 1. Customizer scope ────────────────────────────────────────────────────
$selectors = customify_get_button_styling_selectors();

foreach ( $cases as $label => $case ) {
	list( $el, $expected ) = $case;
	customify_test_assert_same(
		$expected,
		customify_test_scope_matches( $selectors['normal'], $el ),
		'Customizer button scope — ' . $label
	);
}

// ─── 2. Hover variant is derived 1:1 ────────────────────────────────────────
$normal_parts = customify_split_selector_list( $selectors['normal'] );
$hover_parts  = customify_split_selector_list( $selectors['hover'] );

customify_test_assert_same( count( $normal_parts ), count( $hover_parts ), 'Hover scope has one selector per normal selector.' );

foreach ( $hover_parts as $i => $hover_selector ) {
	customify_test_assert_same(
		$normal_parts[ $i ] . ':hover',
		$hover_selector,
		'Hover selector #' . $i . ' is the normal selector plus :hover.'
	);
}

// A functional pseudo-class with comma-separated arguments must survive the
// split — this is what the old explode(',') based helper corrupted.
customify_test_assert(
	false !== strpos( $selectors['hover'], 'button:is([type="submit"], [type="reset"], :not([class])):' ),
	'Comma-carrying :is() arguments survive the hover derivation intact.'
);
customify_test_assert(
	false === strpos( $selectors['hover'], '[type="reset"]:hover, :not([class])' ),
	'The hover derivation does not split inside functional pseudo-class arguments.'
);

// ─── 3. Specificity contract ────────────────────────────────────────────────
$by_prefix = array();
foreach ( $normal_parts as $selector ) {
	$by_prefix[ $selector ] = customify_test_specificity( $selector );
}

$find = function ( $needle ) use ( $by_prefix ) {
	foreach ( $by_prefix as $selector => $score ) {
		if ( 0 === strpos( $selector, $needle ) ) {
			return $score;
		}
	}
	return null;
};

// (0,3,0) keeps Button Styling above `.button.add_to_cart_button` (0,2,0) in
// compatibility/wc/_wc-elements.scss — WooCommerce buttons must keep obeying it.
customify_test_assert_same( array( 0, 3, 0 ), $find( '.button:not(' ), 'The `.button` branch stays at (0,3,0).' );

// (0,3,1), down from the (0,6,1) that chained :not() produced in 0.4.22.
customify_test_assert_same( array( 0, 3, 1 ), $find( 'button:is(' ), 'The bare `button` branch is (0,3,1), not the old (0,6,1).' );

// Unchanged from 0.4.22 — a single :not() list, so it cannot creep.
customify_test_assert_same( array( 0, 2, 1 ), $find( 'input[type="submit"]' ), 'The submit-input branch stays at (0,2,1).' );

// No branch may ever match the bare element without an intent gate again.
foreach ( $normal_parts as $selector ) {
	customify_test_assert(
		'button' !== $selector && 0 !== strpos( $selector, 'button:not(' ),
		'No branch matches the bare `button` element unconditionally: ' . $selector
	);
}

// ─── 4. Default SCSS skin, read back from the compiled stylesheet ───────────
$compiled = dirname( __DIR__ ) . '/build/css/frontend/style-theme.css';

if ( ! file_exists( $compiled ) ) {
	echo "NOTE: build/css/frontend/style-theme.css missing — run `npm run build` to cover the SCSS layer.\n";
} else {
	$css   = file_get_contents( $compiled );
	$scope = '';

	if ( preg_match_all( '/([^{}]+)\{([^{}]*)\}/s', $css, $rules, PREG_SET_ORDER ) ) {
		foreach ( $rules as $rule ) {
			$body = str_replace( ' ', '', $rule[2] );
			if ( false !== strpos( $rule[1], 'button:is(' ) && false !== strpos( $body, 'min-height:2.6em' ) ) {
				$scope = trim( $rule[1] );
				break;
			}
		}
	}

	customify_test_assert( '' !== $scope, 'The compiled stylesheet still carries the intent-gated button rule.' );

	if ( '' !== $scope ) {
		foreach ( $cases as $label => $case ) {
			list( $el, , $expected ) = $case;
			customify_test_assert_same(
				$expected,
				customify_test_scope_matches( $scope, $el ),
				'Default SCSS button skin — ' . $label
			);
		}
	}
}

// ─── Result ─────────────────────────────────────────────────────────────────
if ( $GLOBALS['customify_test_failures'] > 0 ) {
	echo "\n{$GLOBALS['customify_test_failures']} button scope test(s) failed.\n";
	exit( 1 );
}

echo "Button scope tests passed.\n";
