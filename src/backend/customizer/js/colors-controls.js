/**
 * Customizer controls pane — Colors section UI augmentations.
 *
 * Loaded in the customize.php controls iframe (NOT the preview iframe). Adds
 * three capabilities to the Colors section pickers:
 *
 *  1. Palette-slot pickers get a hex input and a readonly `var(--customify-*)`
 *     token row underneath the Iris swatch.
 *  2. Component-override pickers (Link, Heading, Border, …) get a hex input
 *     plus a "From palette" quick-pick swatch row.
 *  3. Cascade-sync — when an override is still at its field default, the
 *     picker swatch mirrors the resolved cascade source (Primary / Text)
 *     without writing to the override setting.
 *
 * Also handles the legacy Overrides collapsible, dirty-state glyph, jQuery
 * slide monkey-patch so Iris open/close is instant, and the MutationObserver
 * that re-mounts the addon when Iris opens a picker.
 *
 * Config (from PHP via wp_localize_script → window.CustomifyColorsControls):
 *   - slots: [{ key, label, color, control }] — 6 palette slot entries, in
 *     brand-first display order.
 */
( function ( $ ) {
	var CFG = window.CustomifyColorsControls || {};
	var CFY_COLORS = { slots: CFG.slots || [] };

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
		'global_styling_color_link':         '#0e7c7b',
		'global_styling_color_link_hover':   '#0e7c7b',
		'global_styling_color_heading':      '#2b2b2b',
		'global_styling_color_w_title':      '#2b2b2b',
		'global_styling_color_text':         '#2b2b2b'
	};

	// Decode wp.customize value if Customify wrapped it as URL-encoded JSON.
	function decodeValue( v ) {
		if ( typeof v !== 'string' ) return v;
		try { return JSON.parse( decodeURI( v ) ); } catch ( e ) { return v; }
	}

	// Resolve a setting's EFFECTIVE cascade value by walking the chain when the
	// setting is itself unset (= empty or its field default). e.g. link-hover
	// cascades from link, which cascades from primary — so the link-hover swatch
	// tracks Primary even though its direct source is Link.
	function resolveCascadeValue( id ) {
		var val = decodeValue( wp.customize( id ).get() || '' );
		var src = CASCADE_MAP[ id ];
		if ( src && ( val === '' || val === FIELD_DEFAULTS[ id ] ) ) {
			return resolveCascadeValue( src );
		}
		return val;
	}

	// Sync the override picker's swatch to its cascade source when the
	// override is unset (= still equals the registered field default).
	function syncCascadeSwatch( targetId ) {
		var sourceId = CASCADE_MAP[ targetId ];
		if ( ! sourceId ) return;
		var li = document.getElementById( 'customize-control-' + targetId );
		if ( ! li ) return;
		var saved = decodeValue( wp.customize( targetId ).get() || '' );
		var unset = ( saved === '' || saved === FIELD_DEFAULTS[ targetId ] );
		if ( ! unset ) {
			// User override saved — let wp-color-picker paint as usual.
			li.classList.remove( 'is-cascading' );
			li.style.removeProperty( '--customify-cascade-display' );
			return;
		}
		var cascadeValue = resolveCascadeValue( sourceId );
		if ( ! cascadeValue ) return;
		li.classList.add( 'is-cascading' );
		li.style.setProperty( '--customify-cascade-display', cascadeValue );
	}

	function syncAllCascadeSwatches() {
		Object.keys( CASCADE_MAP ).forEach( syncCascadeSwatch );
	}

	// Bind to each cascade SOURCE so swatches refresh when the user drags
	// Primary or Text. Also bind each target's own setting so saving an
	// override flips it out of cascade mode immediately.
	function wireCascadeListeners() {
		if ( typeof wp === 'undefined' || ! wp.customize ) return;
		// Bind targets — react to override save / clear.
		Object.keys( CASCADE_MAP ).forEach( function ( targetId ) {
			wp.customize( targetId, function ( value ) {
				value.bind( function () { setTimeout( function () { syncCascadeSwatch( targetId ); }, 16 ); } );
			} );
		} );
		// Bind sources — react to Primary / Text slot drags.
		var sources = {};
		Object.keys( CASCADE_MAP ).forEach( function ( t ) { sources[ CASCADE_MAP[ t ] ] = true; } );
		Object.keys( sources ).forEach( function ( sourceId ) {
			wp.customize( sourceId, function ( value ) {
				value.bind( function () {
					setTimeout( syncAllCascadeSwatches, 16 );
				} );
			} );
		} );
		// Initial paint once controls mount.
		setTimeout( syncAllCascadeSwatches, 200 );
	}
	$( wireCascadeListeners );

	function getControlId( container ) {
		var li = container.closest ? container.closest( '.customize-control' ) : null;
		if ( ! li || ! li.id ) return '';
		return li.id.replace( /^customize-control-/, '' );
	}

	// Build the From-Palette row used by component overrides (Link, Border,
	// Heading, etc.) so the user can override a single element from the
	// brand palette in one click.
	function buildQuickPick( $panel, currentVal ) {
		var $row = $( '<div class="customify-color-quickpick"></div>' );
		$row.append( '<span class="customify-color-quickpick__label">From palette</span>' );
		CFY_COLORS.slots.forEach( function ( s ) {
			var color = ( s.color || '' ).toLowerCase();
			var $sw = $( '<button type="button" class="customify-color-quickpick__swatch"></button>' )
				.css( 'background-color', color )
				.attr( 'title', s.label )
				.attr( 'data-label', s.label )
				.attr( 'data-color', color );
			if ( color === currentVal ) $sw.addClass( 'is-active' );
			$sw.on( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				$panel.wpColorPicker( 'color', color );
				$row.find( '.customify-color-quickpick__swatch' ).removeClass( 'is-active' );
				$sw.addClass( 'is-active' );
			} );
			$row.append( $sw );
		} );
		return $row;
	}

	// Build the hex input + read-only token var rows for a palette slot.
	// Two-way sync the hex with wp-color-picker via Iris events; the token
	// var is purely informational (one-tap select-all for copy/paste).
	function buildHexInput( $panel, currentVal, slotKey ) {
		var $row = $( '<div class="customify-color-hexrow"></div>' );
		var $input = $( '<input type="text" class="customify-color-hex" spellcheck="false" autocomplete="off" />' ).val( currentVal );
		$input.on( 'input', function () {
			var v = ( $input.val() || '' ).trim();
			if ( v && v.charAt( 0 ) !== '#' ) v = '#' + v;
			if ( /^#[0-9a-fA-F]{6}$/.test( v ) || /^#[0-9a-fA-F]{8}$/.test( v ) ) {
				$panel.wpColorPicker( 'color', v );
			}
		} );
		$input.on( 'click', function ( e ) { e.stopPropagation(); } );
		$panel.on( 'iris-customify-hex-sync', function () {
			var v = $panel.val();
			if ( v && document.activeElement !== $input[0] ) $input.val( v );
		} );
		$row.append( $input );

		if ( slotKey ) {
			var $tokenRow = $( '<div class="customify-color-tokenrow"></div>' );
			var $token = $( '<input type="text" class="customify-color-token" readonly />' )
				.val( 'var(--customify-' + slotKey + ')' );
			$token.on( 'focus', function () { this.select(); } );
			$token.on( 'click', function ( e ) { e.stopPropagation(); this.select(); } );
			$tokenRow.append( $token );
			// Wrap both rows in a fragment-like jQuery set so the caller can
			// append in one go without changing the public API.
			return $row.add( $tokenRow );
		}
		return $row;
	}

	function injectPopupAddon( container ) {
		var $container = $( container );
		var $panel     = $container.find( '.customify--color-panel' );
		if ( ! $panel.length ) return;
		var currentVal = ( $panel.val() || '' ).toLowerCase();
		var controlId  = getControlId( container );
		var slot       = CFY_COLORS.slots.filter( function ( s ) { return s.control === controlId; } )[0];
		var isPalette  = !! slot;

		// If an addon is already present (pre-built on init), refresh its
		// current-value state instead of rebuilding the DOM from scratch.
		// Overrides now carry BOTH a hex row and a quick-pick row, so refresh
		// either / both if present.
		var existing = container.querySelector ? container.querySelector( '.customify-color-quickpick, .customify-color-hexrow' ) : null;
		if ( existing ) {
			var hexInput = container.querySelector( '.customify-color-hex' );
			if ( hexInput && document.activeElement !== hexInput ) {
				hexInput.value = $panel.val() || '';
			}
			var quickpick = container.querySelector( '.customify-color-quickpick' );
			if ( quickpick ) {
				quickpick.querySelectorAll( '.customify-color-quickpick__swatch' ).forEach( function ( sw ) {
					sw.classList.toggle( 'is-active', ( sw.getAttribute( 'data-color' ) || '' ).toLowerCase() === currentVal );
				} );
			}
			return;
		}

		var $addon;
		if ( isPalette ) {
			// Palette slots: hex input + readonly token var (var(--customify-<slug>)).
			$addon = buildHexInput( $panel, currentVal, slot.key );
		} else {
			// Component overrides (Link, Heading, Border, etc.): hex input
			// (no token row — overrides don't have a stable slot slug to
			// reference) PLUS the From-Palette quick-pick swatches so the
			// user can either type a custom hex or one-tap a slot color.
			$addon = buildHexInput( $panel, currentVal, null ).add( buildQuickPick( $panel, currentVal ) );
		}
		$container.find( '.wp-picker-holder' ).append( $addon );
	}

	// Pre-build addons for every color picker in the Colors section right
	// after Customizer init — that way opening a picker just refreshes the
	// already-mounted addon (single class toggle / value update) instead of
	// building the DOM + 6-7 buttons + handlers from scratch each time.
	function prebuildAll( section ) {
		section.querySelectorAll( '.wp-picker-container' ).forEach( function ( c ) {
			if ( c.querySelector( '.customify-color-quickpick, .customify-color-hexrow' ) ) return;
			injectPopupAddon( c );
		} );
		section.querySelectorAll( '.customify-input-color' ).forEach( refreshDirtyState );
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
	function refreshDirtyState( div ) {
		if ( ! div ) return;
		var input = div.querySelector( 'input.wp-color-picker' );
		if ( ! input ) return;
		var cur = ( input.value || '' ).trim().toLowerCase();
		var def = ( div.getAttribute( 'data-default' ) || '' ).trim().toLowerCase();
		var dirty = cur !== '' && cur !== def;
		div.classList.toggle( 'is-dirty', dirty );
	}

	// Wire change listeners once. Two sources cover everything:
	//   • DOM events on the underlying wp-color-picker input — fires for
	//     Iris drag, hex paste, quickpick swatch click.
	//   • wp.customize setting bind — fires for programmatic .set() and
	//     for clicks on wp-picker-default (which resets value via the
	//     customize API, not via a DOM event on the input).
	// Either way, refreshDirtyState re-evaluates and toggles is-dirty.
	$( document ).on( 'input change keyup', '#sub-accordion-section-customify_colors input.wp-color-picker, #sub-accordion-section-customify_colors .customify-color-hex', function () {
		var li = $( this ).closest( '.customify-input-color' )[0];
		refreshDirtyState( li );
	} );
	// Bind to each picker's underlying wp.customize Setting once it's
	// available so reset-button clicks (which go through the customize
	// API) also refresh the dirty state. `.customify-input-color` is a
	// DIV inside the control LI — settingId comes from the LI's id.
	function bindSettingListeners() {
		var section = document.getElementById( 'sub-accordion-section-customify_colors' );
		if ( ! section ) return;
		section.querySelectorAll( '.customify-input-color' ).forEach( function ( div ) {
			if ( div.dataset.dirtyBound ) return;
			var li = div.closest( '.customize-control' );
			if ( ! li ) return;
			var settingId = ( li.id || '' ).replace( 'customize-control-', '' );
			if ( ! settingId || ! wp.customize( settingId ) ) return;
			div.dataset.dirtyBound = '1';
			wp.customize( settingId ).bind( function () {
				// Defer one tick so wp-color-picker has time to write the
				// new value into the input before we read it.
				setTimeout( function () { refreshDirtyState( div ); }, 16 );
			} );
		} );
	}
	$( bindSettingListeners );
	setTimeout( bindSettingListeners, 600 );


	// Legacy fine-tuning section is collapsed by default. The .customize-control-title
	// span inside the heading control gets a click handler; the LI itself stays
	// inert so it doesn't pick up the browser's default focus outline (which
	// otherwise paints a stuck rectangle around the entire heading row).
	// State persists per-session via sessionStorage.
	function setupLegacyCollapse() {
		var section = document.getElementById( 'sub-accordion-section-customify_colors' );
		if ( ! section ) return;
		var heading = document.getElementById( 'customize-control-customify_colors_h_overrides' );
		if ( ! heading ) return;
		if ( heading.classList.contains( 'customify-collapsible-heading' ) ) return;
		heading.classList.add( 'customify-collapsible-heading' );

		// Title span is the clickable target (chevron sits next to text).
		var titleEl = heading.querySelector( '.customize-control-title' );
		if ( ! titleEl ) return;
		titleEl.classList.add( 'customify-collapsible-toggle' );

		var targets = [];
		var next = heading.nextElementSibling;
		while ( next ) {
			if ( next.classList && next.classList.contains( 'customize-control' ) ) targets.push( next );
			next = next.nextElementSibling;
		}
		if ( ! targets.length ) return;

		var STORAGE_KEY = 'customify-legacy-collapsed';
		var collapsed = sessionStorage.getItem( STORAGE_KEY ) !== 'open';

		function apply( state ) {
			targets.forEach( function ( el ) { el.style.display = state ? 'none' : ''; } );
			heading.classList.toggle( 'is-collapsed', state );
			heading.classList.toggle( 'is-open',     ! state );
		}
		apply( collapsed );

		titleEl.setAttribute( 'role', 'button' );
		titleEl.setAttribute( 'tabindex', '0' );
		titleEl.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );

		function toggle( e ) {
			e.preventDefault();
			e.stopPropagation();
			collapsed = ! collapsed;
			sessionStorage.setItem( STORAGE_KEY, collapsed ? 'closed' : 'open' );
			apply( collapsed );
			titleEl.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
		}
		titleEl.addEventListener( 'click', toggle );
		titleEl.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) toggle( e );
		} );
	}
	$( setupLegacyCollapse );
	setTimeout( setupLegacyCollapse, 400 );

	// Re-emit a custom event from any change on the picker so the hex input
	// stays in sync while the user drags Iris's saturation / hue / alpha.
	$( document ).on( 'input change', '#sub-accordion-section-customify_colors .customify--color-panel', function () {
		$( this ).trigger( 'iris-customify-hex-sync' );
	} );

	// Monkey-patch jQuery's slide methods so Iris's wpColorPicker can't
	// queue its slow slideToggle('fast') on our color picker holders. The
	// override is scoped to elements WITH .wp-picker-holder — never matches
	// the styling composite control's modal panel (which also lives in the
	// Colors section and uses slideUp/slideDown for its own toggle, and
	// relies on the animation-end callback to remove its .modal--opening
	// class — making it instant would strand the modal open).
	( function patchJQuerySlide() {
		var origDown   = $.fn.slideDown;
		var origUp     = $.fn.slideUp;
		var origToggle = $.fn.slideToggle;
		function isPickerHolder( el ) {
			return el && el.classList && el.classList.contains( 'wp-picker-holder' )
				&& el.closest && el.closest( '#sub-accordion-section-customify_colors' );
		}
		$.fn.slideDown = function () {
			if ( this.length && isPickerHolder( this[0] ) ) return this.show();
			return origDown.apply( this, arguments );
		};
		$.fn.slideUp = function () {
			if ( this.length && isPickerHolder( this[0] ) ) return this.hide();
			return origUp.apply( this, arguments );
		};
		$.fn.slideToggle = function () {
			if ( this.length && isPickerHolder( this[0] ) ) {
				return this.is( ':visible' ) ? this.hide() : this.show();
			}
			return origToggle.apply( this, arguments );
		};
	} )();

	// Iris stops propagation on its click handler, so jQuery delegation on
	// document never sees the click. Instead we observe the .wp-picker-active
	// class on each wp-picker-container inside the Colors section and add/
	// remove our addon row in lock-step with the picker open/close.
	function startObserver() {
		var section = document.getElementById( 'sub-accordion-section-customify_colors' );
		if ( ! section ) {
			setTimeout( startObserver, 500 );
			return;
		}
		// Pre-build addons so opens feel instant — give Customify's initColor
		// a tick to call wpColorPicker() and produce .wp-picker-container.
		setTimeout( function () { prebuildAll( section ); }, 100 );
		setTimeout( function () { prebuildAll( section ); }, 800 );

		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( m ) {
				if ( m.type !== 'attributes' || m.attributeName !== 'class' ) return;
				var target = m.target;
				if ( ! target.classList || ! target.classList.contains( 'wp-picker-container' ) ) return;
				if ( target.classList.contains( 'wp-picker-active' ) ) {
					// Picker just opened — refresh existing addon (or create
					// the first time if pre-build hadn't run for this picker yet).
					injectPopupAddon( target );
				}
				// Note: no removeAddons on close — we keep the addon mounted
				// so the next open is instant. wp-picker-holder is display:none
				// via CSS when not .wp-picker-active so the addon is hidden too.
			} );
		} );
		observer.observe( section, { attributes: true, subtree: true, attributeFilter: [ 'class' ] } );
	}

	$( startObserver );
} )( jQuery );
