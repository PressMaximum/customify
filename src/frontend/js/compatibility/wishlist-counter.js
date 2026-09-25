/**
 * Header "Wishlist" item (`wc_wishlist_counter`) — live count badge.
 *
 * The PHP render prints the badge EMPTY so full-page caches never serve one
 * visitor's count to another (inc/compatibility/woocommerce/inc/wishlist.php).
 * This file fills it in and keeps it in sync:
 *
 * - Plugin-driven providers (TI WooCommerce Wishlist): the plugin's own JS
 *   writes the number into the badge (it carries TI's counter classes). This
 *   file only watches the badge text and mirrors it into the `has-items`
 *   class and the link's aria-label.
 * - Endpoint providers (YITH WooCommerce Wishlist, or any provider registered
 *   with a `count` callback): the count is fetched from the theme's read-only
 *   `?wc-ajax=customify_wishlist_count` endpoint on load when the visitor can
 *   have a list, and again whenever one of the provider's events fires.
 *
 * Settings come from `Customify_JS.wishlist` (see Customify_WC_Wishlist::js_settings()).
 */
/* global jQuery */
( function ( $ ) {
	'use strict';

	const CJS =
		window.Customify_JS && 'object' === typeof window.Customify_JS
			? window.Customify_JS
			: {};
	const settings =
		CJS.wishlist && 'object' === typeof CJS.wishlist ? CJS.wishlist : null;

	if ( ! settings ) {
		return;
	}

	const ITEM = '.item--wc_wishlist_counter';
	const BADGE = '.wishlist-counter-count';
	const LINK = '.wishlist-counter-link';
	const provider = settings.provider || '';
	const endpoint = settings.endpoint || '';
	const i18n = settings.i18n || {};
	const storageKey = 'customify_wishlist_count_' + provider;
	let lastCount = null;
	let refreshTimer = null;

	/**
	 * Parse a badge's text into a count ('' / 'false' / junk → 0).
	 *
	 * @param {string} text Badge text.
	 * @return {number} Count.
	 */
	function parseCount( text ) {
		const n = parseInt( String( text || '' ).replace( /[^\d]/g, '' ), 10 );
		return isNaN( n ) ? 0 : n;
	}

	function readStorage() {
		try {
			const raw = window.localStorage.getItem( storageKey );
			return null === raw ? null : parseCount( raw );
		} catch {
			return null;
		}
	}

	function writeStorage( n ) {
		try {
			window.localStorage.setItem( storageKey, String( n ) );
		} catch {
			// Storage unavailable (private mode, blocked) — the badge still works.
		}
	}

	/**
	 * Reflect a badge's current text onto its item: `has-items` + aria-label.
	 * Never writes to the badge itself, so it cannot loop with the observer.
	 *
	 * @param {Element} item `.item--wc_wishlist_counter` element.
	 */
	function syncItem( item ) {
		const badge = item.querySelector( BADGE );
		const link = item.querySelector( LINK );
		const n = badge ? parseCount( badge.textContent ) : 0;

		if ( badge ) {
			lastCount = n;
		}

		item.classList.toggle( 'has-items', n > 0 );

		if ( link ) {
			const label = link.getAttribute( 'data-label' ) || '';
			const tpl = 1 === n ? i18n.one : i18n.many;
			link.setAttribute(
				'aria-label',
				n > 0 && tpl ? label + ', ' + tpl.replace( '%d', n ) : label
			);
		}
	}

	/**
	 * Write a count into every badge (endpoint providers only).
	 *
	 * @param {number} n Count.
	 */
	function setCount( n ) {
		n = Math.max( 0, parseInt( n, 10 ) || 0 );
		lastCount = n;
		writeStorage( n );
		document
			.querySelectorAll( ITEM + ' ' + BADGE )
			.forEach( function ( badge ) {
				if ( badge.textContent !== String( n ) ) {
					badge.textContent = String( n );
				}
			} );
		// Observers resync asynchronously; sync now so the state is exact
		// even where MutationObserver is unavailable.
		document.querySelectorAll( ITEM ).forEach( syncItem );
	}

	function fetchCount() {
		if ( ! endpoint ) {
			return;
		}
		$.ajax( {
			url: endpoint,
			type: 'GET',
			dataType: 'json',
			cache: false,
		} ).done( function ( res ) {
			if (
				res &&
				res.success &&
				res.data &&
				'number' === typeof res.data.count
			) {
				setCount( res.data.count );
			}
		} );
	}

	function scheduleFetch() {
		clearTimeout( refreshTimer );
		refreshTimer = setTimeout( fetchCount, 150 );
	}

	/**
	 * Wire up every item not yet initialised (first load + Customizer
	 * selective-refresh re-renders).
	 */
	function initItems() {
		document.querySelectorAll( ITEM ).forEach( function ( item ) {
			if ( item.getAttribute( 'data-wishlist-ready' ) ) {
				return;
			}
			item.setAttribute( 'data-wishlist-ready', '1' );

			const badge = item.querySelector( BADGE );

			// A re-rendered item (Customizer partial refresh) comes back
			// empty; carry the last known count over instead of blanking it.
			if (
				badge &&
				'' === badge.textContent &&
				null !== lastCount &&
				lastCount > 0
			) {
				badge.textContent = String( lastCount );
			}

			if ( badge && 'function' === typeof window.MutationObserver ) {
				new window.MutationObserver( function () {
					syncItem( item );
				} ).observe( badge, {
					childList: true,
					characterData: true,
					subtree: true,
				} );
			}

			syncItem( item );
		} );
	}

	$( function () {
		if ( ! document.querySelector( ITEM ) ) {
			return;
		}

		initItems();

		if ( endpoint && document.querySelector( ITEM + ' ' + BADGE ) ) {
			const events = settings.events || {};
			if ( events.body && events.body.length ) {
				$( document.body ).on( events.body.join( ' ' ), scheduleFetch );
			}
			if ( events.document && events.document.length ) {
				$( document ).on(
					events.document.join( ' ' ),
					function ( e, a, b, firstLoad ) {
						// `yith_wcwl_fragments_loaded` also fires for the initial
						// fragment load (4th arg true) — the load check below
						// already covers that.
						if ( true === firstLoad ) {
							return;
						}
						scheduleFetch();
					}
				);
			}

			// On load, only ask when the visitor can have a list: logged in,
			// or a guest this browser has already seen add something (the
			// plugin's own session cookie is HttpOnly, so it cannot be read
			// here). Guests who never used the wishlist cost zero requests.
			const stored = readStorage();
			if ( document.body.classList.contains( 'logged-in' ) ) {
				fetchCount();
			} else if ( null !== stored && stored > 0 ) {
				setCount( stored );
				fetchCount();
			}
		}

		if (
			window.wp &&
			window.wp.customize &&
			window.wp.customize.selectiveRefresh
		) {
			window.wp.customize.selectiveRefresh.bind(
				'partial-content-rendered',
				initItems
			);
		}
	} );
} )( jQuery );
