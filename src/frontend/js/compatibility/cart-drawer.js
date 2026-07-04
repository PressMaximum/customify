/**
 * WooCommerce Cart Drawer (off-canvas) — frontend behavior.
 *
 * Independent state (`is-cart-drawer` on <html>/<body>) so it never collides
 * with the theme's mobile-menu off-canvas (`is-menu-sidebar`). Opens from the
 * header cart trigger, syncs its body from WooCommerce's own AJAX fragments.
 *
 * Only acts when the drawer panel (#customify-cart-drawer) is present — the
 * header cart item prints it only when Cart Behavior = Drawer, so in Dropdown
 * mode this file binds nothing and the native hover dropdown is untouched.
 */
(function ($) {
	'use strict';

	// Auto-open reads the theme's localized Customify_JS object (same one the
	// sibling woocommerce.js reads for wc_open_cart). Guarded so a missing
	// object doesn't break the drawer.
	var CJS = ( window.Customify_JS && typeof window.Customify_JS === 'object' ) ? window.Customify_JS : {};
	var autoOpen = !! CJS.wc_cart_drawer_auto_open;

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	var drawer, overlay, lastFocused, autoOpenArmed = false;

	function getScrollbarWidth() {
		return window.innerWidth - document.documentElement.clientWidth;
	}

	function lockScroll() {
		var sw = getScrollbarWidth();
		if ( sw > 0 ) {
			document.documentElement.style.setProperty( '--customify-scrollbar-width', sw + 'px' );
		}
		document.documentElement.classList.add( 'is-cart-drawer' );
		document.body.classList.add( 'is-cart-drawer' );
	}

	function unlockScroll() {
		document.documentElement.classList.remove( 'is-cart-drawer' );
		document.body.classList.remove( 'is-cart-drawer' );
		document.documentElement.style.removeProperty( '--customify-scrollbar-width' );
	}

	function isOpen() {
		return drawer && drawer.classList.contains( 'is-open' );
	}

	/**
	 * @param {boolean} focusDisabled When true (auto-open), don't steal focus
	 *                                from the page the shopper is browsing.
	 */
	function open( focusDisabled ) {
		if ( ! drawer || isOpen() ) {
			return;
		}

		lastFocused = document.activeElement;

		drawer.hidden = false;
		overlay.hidden = false;
		drawer.removeAttribute( 'inert' );

		// Forced synchronous reflow so the transition runs even in a background
		// tab — requestAnimationFrame doesn't fire while the tab is hidden
		// (same convention as the theme's typography-control.js).
		void drawer.offsetWidth;

		drawer.classList.add( 'is-open' );
		overlay.classList.add( 'is-open' );
		lockScroll();

		if ( ! focusDisabled ) {
			var closeBtn = drawer.querySelector( '.customify-cart-drawer__close' );
			if ( closeBtn ) {
				closeBtn.focus();
			}
		}
	}

	function close() {
		if ( ! drawer || ! isOpen() ) {
			return;
		}

		drawer.classList.remove( 'is-open' );
		overlay.classList.remove( 'is-open' );
		unlockScroll();
		drawer.setAttribute( 'inert', '' );

		// Remove from the a11y tree after the slide-out. A timeout (rather than
		// transitionend) also covers prefers-reduced-motion, where no
		// transition fires.
		window.setTimeout( function () {
			if ( ! isOpen() ) {
				drawer.hidden = true;
				overlay.hidden = true;
			}
		}, 300 );

		if ( lastFocused && typeof lastFocused.focus === 'function' ) {
			lastFocused.focus();
		}
	}

	function trapFocus( e ) {
		if ( e.key !== 'Tab' || ! isOpen() ) {
			return;
		}

		var f = drawer.querySelectorAll( FOCUSABLE );
		if ( ! f.length ) {
			return;
		}

		var first = f[ 0 ];
		var last = f[ f.length - 1 ];

		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	function onKeydown( e ) {
		if ( ( e.key === 'Escape' || e.key === 'Esc' ) && isOpen() ) {
			e.preventDefault();
			close();
		} else {
			trapFocus( e );
		}
	}

	// Flag the drawer as empty (drives the "Continue Shopping" button) whenever
	// WC's mini-cart shows its empty message.
	function updateEmptyState() {
		if ( ! drawer ) {
			return;
		}
		drawer.classList.toggle(
			'is-cart-empty',
			!! drawer.querySelector( '.woocommerce-mini-cart__empty-message' )
		);
	}

	function bind() {
		// Belt-and-suspenders against a double enqueue: bind the delegated
		// handlers / auto-open listeners only once.
		if ( window.__customifyCartDrawerBound ) {
			return;
		}

		drawer = document.getElementById( 'customify-cart-drawer' );
		overlay = document.querySelector( '.customify-cart-drawer-overlay' );

		// Drawer only renders when Cart Behavior = Drawer. In Dropdown mode
		// there's nothing to bind — leave the native dropdown alone.
		if ( ! drawer || ! overlay ) {
			return;
		}

		window.__customifyCartDrawerBound = true;

		drawer.setAttribute( 'inert', '' );

		// Open from the header cart item; keep the href as a no-JS fallback.
		$( document ).on( 'click', '.builder-header-wc_cart-item .cart-item-link', function ( e ) {
			e.preventDefault();
			open( false );
		} );

		overlay.addEventListener( 'click', close );

		var closeBtn = drawer.querySelector( '.customify-cart-drawer__close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault(); // it's an <a href="#"> (matches Quick View's close)
				close();
			} );
		}

		// Show "Continue Shopping" only while the cart is empty — re-check on
		// load and on every WC cart change.
		updateEmptyState();
		$( document.body ).on( 'wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', updateEmptyState );

		// Capture phase so ESC wins over other handlers.
		document.addEventListener( 'keydown', onKeydown, true );

		// Auto-open after add-to-cart — wait for fresh fragments so the drawer
		// shows updated contents, and don't yank focus off the page. Two-step:
		// arm on added_to_cart, open on the fragment refresh. The setTimeout is
		// a script-order safety net: WooCommerce can fire wc_fragments_loaded
		// synchronously inside the SAME added_to_cart dispatch, before this arm
		// handler runs — the fragment listener would then miss it. WC replaces
		// the fragments BEFORE firing added_to_cart, so opening on the next tick
		// still shows fresh contents; the fragment listener handles the normal
		// case and clears the flag so this never double-opens.
		if ( autoOpen ) {
			$( document.body ).on( 'added_to_cart', function () {
				autoOpenArmed = true;
				window.setTimeout( function () {
					if ( autoOpenArmed ) {
						autoOpenArmed = false;
						open( true );
					}
				}, 0 );
			} );

			$( document.body ).on( 'wc_fragments_refreshed wc_fragments_loaded', function () {
				if ( autoOpenArmed ) {
					autoOpenArmed = false;
					open( true );
				}
			} );
		}

		// Tear down a stuck-open drawer restored from the bfcache (back button).
		window.addEventListener( 'pageshow', function ( e ) {
			if ( e.persisted && isOpen() ) {
				drawer.classList.remove( 'is-open' );
				overlay.classList.remove( 'is-open' );
				drawer.hidden = true;
				overlay.hidden = true;
				drawer.setAttribute( 'inert', '' );
				unlockScroll();
			}
		} );
	}

	if ( document.readyState !== 'loading' ) {
		bind();
	} else {
		document.addEventListener( 'DOMContentLoaded', bind );
	}

})( jQuery );
