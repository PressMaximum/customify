/**
 * Customizer preview — color palette live-update + cascade rescue.
 *
 * Loaded in the customize-preview iframe. Keeps `--customify-*` tokens on
 * document.documentElement in sync with the Customizer settings so the user
 * sees paint updates without a save/reload, and rescues the palette-tokens
 * <style> block from a WordPress selective-refresh bug that silently detaches
 * it from the cascade engine at boot.
 *
 * Config (from PHP via wp_localize_script → window.CustomifyColorsPreview):
 *   - slotVars: { settingName → cssVarName } for every color setting we drive.
 *
 * All derivation math (on-*, *-container, border-strong, OKLab transforms)
 * mirrors the PHP helpers in inc/colors-palette.php per the color-token spec.
 */
( function () {
	if ( typeof wp === 'undefined' || ! wp.customize ) return;

	var CFG = window.CustomifyColorsPreview || {};
	var SLOT_VARS = CFG.slotVars || {};

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
	function decode( v ) {
		if ( typeof v !== 'string' ) return v;
		try { return JSON.parse( decodeURI( v ) ); } catch ( e ) { return v; }
	}

	function normalize( v ) {
		var d = decode( v );
		if ( typeof d !== 'string' ) return '';
		d = d.trim();
		if ( ! d ) return '';
		if ( /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test( d ) ) return d;
		if ( /^rgba?\(/.test( d ) ) return d;
		return '';
	}

	Object.keys( SLOT_VARS ).forEach( function ( setting ) {
		wp.customize( setting, function ( value ) {
			value.bind( function ( newval ) {
				var clean = normalize( newval );
				var token = SLOT_VARS[ setting ];
				if ( clean ) {
					document.documentElement.style.setProperty( token, clean );
				} else if ( CASCADE_FALLBACK[ token ] ) {
					document.documentElement.style.setProperty( token, CASCADE_FALLBACK[ token ] );
				} else {
					document.documentElement.style.removeProperty( token );
				}
			} );
		} );
	} );

	// ──────────────────────────────────────────────────────────────────
	// Live preview math — mirrors the PHP helpers in colors-palette.php
	// per the color-token-derivation spec. Recomputes derived tokens
	// (on-*, *-container, on-*-container, border-strong) live as the
	// user drags any source-slot picker in the Customizer.
	// ──────────────────────────────────────────────────────────────────

	function _hexToRgb( value ) {
		// Accept both hex (#rrggbb / #rgb) and rgb()/rgba() input forms.
		// Mirrors PHP customify_color_hex_to_rgb() — the rgba support is
		// what makes on-* live-preview keep working when user drags the
		// alpha slider in the WP color picker.
		value = ( value || '' ).toString().trim();
		// Anchored trailing `)` to reject cut-off inputs (parity with PHP).
		var m = value.match( /^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*[\d.]+)?\s*\)$/i );
		if ( m ) {
			return [
				Math.max( 0, Math.min( 255, parseInt( m[1], 10 ) ) ),
				Math.max( 0, Math.min( 255, parseInt( m[2], 10 ) ) ),
				Math.max( 0, Math.min( 255, parseInt( m[3], 10 ) ) )
			];
		}
		var hex = value.replace( /^#/, '' );
		if ( hex.length === 3 ) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		if ( ! /^[0-9a-fA-F]{6}$/.test( hex ) ) return null;
		return [
			parseInt( hex.slice( 0, 2 ), 16 ),
			parseInt( hex.slice( 2, 4 ), 16 ),
			parseInt( hex.slice( 4, 6 ), 16 )
		];
	}

	function _rgbToHex( rgb ) {
		var c = function ( v ) {
			v = Math.max( 0, Math.min( 255, Math.round( v ) ) );
			var h = v.toString( 16 );
			return h.length === 1 ? '0' + h : h;
		};
		return '#' + c( rgb[0] ) + c( rgb[1] ) + c( rgb[2] );
	}

	function _mixHex( a, b, weightA ) {
		weightA = Math.max( 0, Math.min( 1, weightA ) );
		var wb = 1 - weightA;
		var ra = _hexToRgb( a ), rb = _hexToRgb( b );
		if ( ! ra || ! rb ) return a;
		return _rgbToHex( [
			ra[0] * weightA + rb[0] * wb,
			ra[1] * weightA + rb[1] * wb,
			ra[2] * weightA + rb[2] * wb
		] );
	}

	function _relativeLuminance( hex ) {
		var rgb = _hexToRgb( hex );
		if ( ! rgb ) return 0;
		var f = function ( v ) {
			v = v / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
		};
		return 0.2126 * f( rgb[0] ) + 0.7152 * f( rgb[1] ) + 0.0722 * f( rgb[2] );
	}

	function _wcagContrast( a, b ) {
		var la = _relativeLuminance( a ), lb = _relativeLuminance( b );
		var hi = Math.max( la, lb ), lo = Math.min( la, lb );
		return ( hi + 0.05 ) / ( lo + 0.05 );
	}

	// Composite a possibly-transparent color over an opaque base. Mirrors
	// PHP customify_color_composite_over() — returns the rendered color
	// so the max-contrast pick measures against what the user actually
	// sees, not the opaque rgb component of an rgba.
	function _compositeOver( value, baseHex ) {
		var v = ( value || '' ).toString().trim();
		var m = v.match( /^rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([\d.]+)\s*\)/i );
		if ( m ) {
			var r = Math.max( 0, Math.min( 255, parseInt( m[1], 10 ) ) );
			var g = Math.max( 0, Math.min( 255, parseInt( m[2], 10 ) ) );
			var b = Math.max( 0, Math.min( 255, parseInt( m[3], 10 ) ) );
			var a = Math.max( 0, Math.min( 1, parseFloat( m[4] ) ) );
			if ( a >= 1 ) return [ r, g, b ];
			var baseRgb = _hexToRgb( baseHex ) || [ 255, 255, 255 ];
			return [
				Math.round( r * a + baseRgb[0] * ( 1 - a ) ),
				Math.round( g * a + baseRgb[1] * ( 1 - a ) ),
				Math.round( b * a + baseRgb[2] * ( 1 - a ) )
			];
		}
		// Opaque (hex / rgb passthrough).
		return _hexToRgb( v ) || [ 0, 0, 0 ];
	}

	// Spec §3: max-contrast pick against the EFFECTIVE (composited) bg.
	function _pickOn( value, baseHex ) {
		baseHex = baseHex || '#FFFFFF';
		var rgb = _compositeOver( value, baseHex );
		var eff = _rgbToHex( rgb );
		return _wcagContrast( '#FFFFFF', eff ) >= _wcagContrast( '#1A1A1A', eff )
			? '#FFFFFF' : '#1A1A1A';
	}

	// OKLab transforms (Ottosson) — same math as PHP customify_color_srgb_to_oklab
	// / customify_color_oklab_to_srgb. Needed for §4 P-solve and §5 L-reduction.
	function _srgbToOklab( hex ) {
		var rgb = _hexToRgb( hex );
		if ( ! rgb ) return [ 0, 0, 0 ];
		var f = function ( v ) {
			v = v / 255;
			return v <= 0.04045 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
		};
		var rl = f( rgb[0] ), gl = f( rgb[1] ), bl = f( rgb[2] );
		var l = 0.4122214708 * rl + 0.5363325363 * gl + 0.0514459929 * bl;
		var m = 0.2119034982 * rl + 0.6806995451 * gl + 0.1073969566 * bl;
		var s = 0.0883024619 * rl + 0.2817188376 * gl + 0.6299787005 * bl;
		var cbrt = function ( x ) { return x < 0 ? -Math.pow( -x, 1 / 3 ) : Math.pow( x, 1 / 3 ); };
		var l_ = cbrt( l ), m_ = cbrt( m ), s_ = cbrt( s );
		return [
			0.2104542553 * l_ + 0.7936177850 * m_ - 0.0040720468 * s_,
			1.9779984951 * l_ - 2.4285922050 * m_ + 0.4505937099 * s_,
			0.0259040371 * l_ + 0.7827717662 * m_ - 0.8086757660 * s_
		];
	}

	function _oklabToSrgb( L, a, b ) {
		var l_ = L + 0.3963377774 * a + 0.2158037573 * b;
		var m_ = L - 0.1055613458 * a - 0.0638541728 * b;
		var s_ = L - 0.0894841775 * a - 1.2914855480 * b;
		var l = l_ * l_ * l_, m = m_ * m_ * m_, s = s_ * s_ * s_;
		var rl =  4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s;
		var gl = -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s;
		var bl = -0.0041960863 * l - 0.7034186147 * m + 1.7076147010 * s;
		var g = function ( v ) {
			v = Math.max( 0, Math.min( 1, v ) );
			return v <= 0.0031308 ? v * 12.92 : 1.055 * Math.pow( v, 1 / 2.4 ) - 0.055;
		};
		return _rgbToHex( [ g( rl ) * 255, g( gl ) * 255, g( bl ) * 255 ] );
	}

	function _oklabL( hex ) { return _srgbToOklab( hex )[0]; }

	// Spec §4: closed-form P solve for container tints at OKLab L = 0.93.
	function _solveContainerP( source, base ) {
		var ls = _oklabL( source ), lb = _oklabL( base );
		var denom = ls - lb;
		if ( Math.abs( denom ) < 1e-6 ) return 0.5;
		var p = ( 0.93 - lb ) / denom;
		return Math.max( 0.02, Math.min( 0.98, p ) );
	}

	// Chroma cap (Customify extension) — keeps high-chroma brand
	// containers from staying oversaturated when mixed with white.
	// Mirrors PHP customify_color_chroma_cap_oklab().
	function _chromaCap( hex, maxChroma ) {
		var lab = _srgbToOklab( hex );
		var L = lab[0], a = lab[1], b = lab[2];
		var c = Math.sqrt( a * a + b * b );
		if ( c <= maxChroma ) return hex;
		var s = maxChroma / c;
		return _oklabToSrgb( L, a * s, b * s );
	}

	// Spec §5: step OKLab L downward until contrast against bg ≥ 4.5.
	function _lReduceUntilContrast( source, bg, target ) {
		target = target || 4.5;
		var lab = _srgbToOklab( source );
		var L = lab[0], a = lab[1], b = lab[2];
		while ( L > 0 ) {
			var candidate = _oklabToSrgb( L, a, b );
			if ( _wcagContrast( candidate, bg ) >= target ) return candidate;
			L -= 0.02;
		}
		return '#1A1A1A';
	}

	// Spec §2: iterate P from 6% upward until contrast vs base ≥ 3.0.
	function _solveBorderStrong( text, base ) {
		for ( var p = 6; p <= 100; p++ ) {
			var mix = _mixHex( text, base, p / 100 );
			if ( _wcagContrast( mix, base ) >= 3.0 ) return mix;
		}
		return text;
	}

	// Helper: read a slot's current effective value from wp.customize.
	// Falls back to the spec default when the user hasn't saved the slot
	// (mirrors PHP slot resolver). Used by recomputeDerived() so a
	// container update fires correctly even mid-drag of a non-source slot.
	var SLOT_DEFAULTS = {
		'global_styling_color_primary':   '#0e7c7b',
		'global_styling_color_secondary': '#c3512f',
		'customify_palette_accent':       '#FFD042',
		'customify_palette_text':         '#2b2b2b',
		'customify_palette_surface':      '#f9f9f9',
		'customify_palette_base':         '#FFFFFF'
	};

	function _readSlot( setting ) {
		try {
			var val = wp.customize( setting ).get();
			var clean = normalize( val );
			return clean || SLOT_DEFAULTS[ setting ];
		} catch ( e ) {
			return SLOT_DEFAULTS[ setting ];
		}
	}

	// Recompute every derived token. Called from every source-slot
	// listener so a drag on Primary updates container/on-container/etc.
	function recomputeDerived() {
		var primary   = _readSlot( 'global_styling_color_primary' );
		var secondary = _readSlot( 'global_styling_color_secondary' );
		var accent    = _readSlot( 'customify_palette_accent' );
		var text      = _readSlot( 'customify_palette_text' );
		var surface   = _readSlot( 'customify_palette_surface' );
		var base      = _readSlot( 'customify_palette_base' );
		var de = document.documentElement.style;

		// §3 on-* — max-contrast against rgba-composited bg.
		de.setProperty( '--customify-on-primary',   _pickOn( primary,   base ) );
		de.setProperty( '--customify-on-secondary', _pickOn( secondary, base ) );
		de.setProperty( '--customify-on-accent',    _pickOn( accent,    base ) );
		// On-surface uses #FFFFFF when --customify-surface is unset
		// (matches the SCSS var() fallback in `.is-style-card`).
		var hasSurface = false;
		try { hasSurface = !! normalize( wp.customize( 'customify_palette_surface' ).get() ); } catch ( e ) {}
		de.setProperty( '--customify-on-surface', _pickOn( hasSurface ? surface : '#FFFFFF', base ) );

		// §4 *-container — solve P, mix, apply chroma cap (0.04). Emit
		// as static hex (NOT a color-mix expression) because the chroma
		// cap can't be expressed in CSS color-mix. Mirrors PHP container
		// emit logic.
		var CONTAINER_MAX_CHROMA = 0.04;
		var pPrim = _solveContainerP( primary,   base );
		var pSec  = _solveContainerP( secondary, base );
		var pAcc  = _solveContainerP( accent,    base );
		var primContainerHex = _chromaCap( _mixHex( primary,   base, pPrim ), CONTAINER_MAX_CHROMA );
		var secContainerHex  = _chromaCap( _mixHex( secondary, base, pSec  ), CONTAINER_MAX_CHROMA );
		var accContainerHex  = _chromaCap( _mixHex( accent,    base, pAcc  ), CONTAINER_MAX_CHROMA );
		de.setProperty( '--customify-primary-container',   primContainerHex );
		de.setProperty( '--customify-secondary-container', secContainerHex );
		de.setProperty( '--customify-accent-container',    accContainerHex );

		// §5 on-*-container — L-reduced brand hue against the resolved
		// CAPPED container hex (so the safety net matches what's rendered).
		de.setProperty( '--customify-on-primary-container',   _lReduceUntilContrast( primary,   primContainerHex ) );
		de.setProperty( '--customify-on-secondary-container', _lReduceUntilContrast( secondary, secContainerHex ) );
		de.setProperty( '--customify-on-accent-container',    _lReduceUntilContrast( accent,    accContainerHex ) );

		// §2 border-strong — solved P.
		de.setProperty( '--customify-border-strong', _solveBorderStrong( text, base ) );
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
	function recomputeDerivedDebounced() {
		clearTimeout( _cfyRderiveT );
		_cfyRderiveT = setTimeout( recomputeDerived, 24 );
	}
	SOURCE_SLOTS.forEach( function ( setting ) {
		wp.customize( setting, function ( value ) {
			value.bind( recomputeDerivedDebounced );
		} );
	} );
	// Prime the initial state on load so the preview iframe shows
	// derived values immediately (without waiting for the first drag).
	try { recomputeDerived(); } catch ( e ) {}

	// ──────────────────────────────────────────────────────────────────
	// Cascade rescue — registering the selective-refresh partial for
	// #customify-palette-tokens-inline-css detaches that <style> from the
	// cascade engine at preview boot (CSSOM still parses the :root rule,
	// but getComputedStyle(root).getPropertyValue('--customify-primary')
	// returns ''). The in-tag text is intact and all brand colors go
	// missing — section backgrounds, buttons, borders — because every
	// consumer resolves to the var() fallback. Detach + re-insert the
	// same node to force the engine to bind it, mirroring the one-liner
	// workaround already in style-guide-template.js.
	//
	// Runs on 'preview-ready' (after wp.customize.selectiveRefresh has
	// scanned the DOM and claimed the style tag as a partial container).
	// A DOMContentLoaded binding fires too early: the detach happens AFTER
	// DCL, so a pre-DCL rescue would re-attach before the breakage.
	// ──────────────────────────────────────────────────────────────────

	function rescuePaletteCascade() {
		try {
			var el = document.getElementById( 'customify-palette-tokens-inline-css' );
			if ( ! el || ! el.parentNode ) return true; // nothing to rescue
			var cs = document.defaultView.getComputedStyle( document.documentElement );
			if ( cs.getPropertyValue( '--customify-primary' ).trim() ) return true;
			var p = el.parentNode, n = el.nextSibling;
			el.remove();
			p.insertBefore( el, n );
			var after = document.defaultView.getComputedStyle( document.documentElement ).getPropertyValue( '--customify-primary' ).trim();
			return !! after;
		} catch ( e ) { return false; }
	}
	// Selective_refresh's partial registration (which detaches the <style>
	// from the cascade engine) can land before OR after preview-ready — the
	// exact order varies by WP version and by how many partials the theme
	// registers. One-shot rescue inside preview-ready wins the race on some
	// loads and loses on others. Retry on a shrinking schedule (0 → 50 →
	// 250 → 1000ms) and stop as soon as --customify-primary resolves; each
	// call is a no-op when the cascade is already live, so the extra ticks
	// cost nothing on good loads.
	function scheduleRescues() {
		var delays = [ 0, 50, 250, 1000 ];
		delays.forEach( function ( d ) {
			setTimeout( rescuePaletteCascade, d );
		} );
	}
	wp.customize.bind( 'preview-ready', scheduleRescues );
	// preview-ready may have already fired by the time this script evaluates
	// (customize-preview is a dependency, so WP guarantees it loads first,
	// but the 'ready' event is deferred async). Fire a parallel schedule from
	// DOMContentLoaded so we cover both orderings.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scheduleRescues );
	} else {
		scheduleRescues();
	}

} )();
