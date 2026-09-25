<?php
/**
 * Wishlist provider layer for the "Wishlist" header item (`wc_wishlist_counter`).
 *
 * The theme does not ship a wishlist of its own. It talks to whichever
 * wishlist plugin is active through a small provider registry:
 *
 *   • TI WooCommerce Wishlist  (`ti`)   — the plugin's own public.js fills the
 *     badge; the theme only hands it the classes it already targets.
 *   • YITH WooCommerce Wishlist (`yith`) — the free plugin has no header
 *     counter API, so the theme exposes a tiny read-only wc-ajax endpoint
 *     (`customify_wishlist_count`) and refreshes the badge on YITH's events.
 *
 * Other plugins (or Customify Pro) register more providers through the
 * `customify/wishlist/providers` filter — see docs/api-reference.md §4.4.
 *
 * Cache safety: the header item never prints a count. The badge is rendered
 * empty and filled client-side, so a full-page cache can serve one HTML copy
 * to every visitor without leaking one visitor's count to the next.
 *
 * @package customify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Customify_WC_Wishlist
 */
class Customify_WC_Wishlist {

	/**
	 * The wc-ajax action that returns the current visitor's wishlist count.
	 */
	const AJAX_ACTION = 'customify_wishlist_count';

	/**
	 * Singleton instance.
	 *
	 * @var Customify_WC_Wishlist|null
	 */
	protected static $instance = null;

	/**
	 * Resolved active provider, cached per request. `false` = not resolved yet.
	 *
	 * @var array|null|false
	 */
	protected $active = false;

	/**
	 * Get the singleton.
	 *
	 * @return Customify_WC_Wishlist
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hook the endpoint and the front-end settings.
	 */
	protected function __construct() {
		add_action( 'wc_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_count' ) );
		add_filter( 'Customify_JS', array( $this, 'js_settings' ) );
	}

	/**
	 * Built-in provider definitions.
	 *
	 * Shape of one provider (every key but `id` is optional):
	 *
	 *   id           string   Unique slug, printed as `data-wishlist-provider`.
	 *   label        string   Human-readable name (Customizer notice).
	 *   active       bool|callable  Whether the plugin is available.
	 *   url          string|callable  Wishlist page URL.
	 *   count        callable|null  Returns the CURRENT visitor's item count.
	 *                When set, the theme's wc-ajax endpoint serves it and the
	 *                front-end fetches it. Leave null when the plugin's own JS
	 *                writes the number into the badge.
	 *   badge_class  string   Extra classes on the count `<span>` (e.g. the
	 *                classes a plugin's JS already writes counts into).
	 *   scripts      string[] Script handles to enqueue when the item renders.
	 *   events       array    `{ body: string[], document: string[] }` jQuery
	 *                event names that mean "the list changed" — the front-end
	 *                re-fetches the count on each (only used with `count`).
	 *
	 * @return array
	 */
	protected function default_providers() {
		return array(
			'ti'   => array(
				'id'          => 'ti',
				'label'       => 'TI WooCommerce Wishlist',
				'active'      => class_exists( 'TInvWL_Public_WishlistCounter' ) && function_exists( 'tinv_url_wishlist_default' ),
				'url'         => 'tinv_url_wishlist_default',
				// TI's public.js writes counts into
				// `.wishlist_products_counter_number, .theme-item-count.wishlist-item-count`
				// (assets/js/public.js update_product_counter()), and only
				// fetches the list when a `.wishlist_products_counter_number`
				// is on the page. Borrowing both lets TI keep the badge in
				// sync on add/remove, across tabs and from its localStorage
				// cache, with no request of our own. Its server counter
				// (TInvWL_Public_WishlistCounter::counter()) is not used: it
				// creates the default wishlist for a logged-in user as a side
				// effect, and a count in the HTML would not be cache-safe.
				'count'       => null,
				'badge_class' => 'wishlist_products_counter_number theme-item-count wishlist-item-count',
				'scripts'     => array( 'tinvwl' ),
				'events'      => array(),
			),
			'yith' => array(
				'id'          => 'yith',
				'label'       => 'YITH WooCommerce Wishlist',
				'active'      => function_exists( 'yith_wcwl_count_all_products' ) && function_exists( 'YITH_WCWL' ),
				'url'         => array( $this, 'yith_url' ),
				'count'       => 'yith_wcwl_count_all_products',
				'badge_class' => '',
				'scripts'     => array(),
				// Verified against YITH WooCommerce Wishlist 4.18.1:
				// - body `added_to_wishlist` / `removed_from_wishlist`
				//   (legacy jQuery button + wishlist table, jquery.yith-wcwl.js);
				// - document `yith_wcwl_reload_fragments` (React add-to-wishlist
				//   button, src/utils/button-data-context.js, after add/remove);
				// - document `yith_wcwl_fragments_loaded` (fragments refreshed).
				'events'      => array(
					'body'     => array( 'added_to_wishlist', 'removed_from_wishlist' ),
					'document' => array( 'yith_wcwl_reload_fragments', 'yith_wcwl_fragments_loaded' ),
				),
			),
		);
	}

	/**
	 * All registered providers, normalised.
	 *
	 * @return array Provider arrays keyed by id, in priority order.
	 */
	public function get_providers() {
		/**
		 * Filter the wishlist providers the header Wishlist item can use.
		 *
		 * Order matters: the first ACTIVE provider wins (override that with
		 * `customify/wishlist/provider`). Add a provider for another plugin:
		 *
		 *     add_filter( 'customify/wishlist/providers', function ( $providers ) {
		 *         $providers['my_plugin'] = array(
		 *             'id'     => 'my_plugin',
		 *             'label'  => 'My Wishlist',
		 *             'active' => function_exists( 'my_wishlist_count' ),
		 *             'url'    => 'my_wishlist_url',
		 *             'count'  => 'my_wishlist_count',
		 *             'events' => array( 'body' => array( 'my_wishlist_changed' ) ),
		 *         );
		 *         return $providers;
		 *     } );
		 *
		 * @since 0.4.27
		 *
		 * @param array $providers Provider arrays keyed by id.
		 */
		$providers = apply_filters( 'customify/wishlist/providers', $this->default_providers() );

		$normalised = array();
		foreach ( (array) $providers as $key => $provider ) {
			if ( ! is_array( $provider ) ) {
				continue;
			}
			$provider = wp_parse_args(
				$provider,
				array(
					'id'          => is_string( $key ) ? $key : '',
					'label'       => '',
					'active'      => false,
					'url'         => '',
					'count'       => null,
					'badge_class' => '',
					'scripts'     => array(),
					'events'      => array(),
				)
			);

			$provider['id'] = sanitize_key( $provider['id'] );
			if ( '' === $provider['id'] ) {
				continue;
			}

			$provider['events'] = wp_parse_args(
				(array) $provider['events'],
				array(
					'body'     => array(),
					'document' => array(),
				)
			);

			$normalised[ $provider['id'] ] = $provider;
		}

		return $normalised;
	}

	/**
	 * Whether a provider's plugin is available.
	 *
	 * @param array $provider Normalised provider.
	 *
	 * @return bool
	 */
	protected function is_provider_active( $provider ) {
		$active = $provider['active'];
		if ( is_callable( $active ) ) {
			$active = call_user_func( $active );
		}

		return (bool) $active;
	}

	/**
	 * The provider the header item uses on this request, or null.
	 *
	 * @return array|null
	 */
	public function get_active_provider() {
		if ( false !== $this->active ) {
			return $this->active;
		}

		$providers = $this->get_providers();
		$chosen    = null;
		foreach ( $providers as $id => $provider ) {
			if ( $this->is_provider_active( $provider ) ) {
				$chosen = $id;
				break;
			}
		}

		/**
		 * Filter which wishlist provider the header Wishlist item uses.
		 *
		 * Useful when more than one wishlist plugin is active. Return an id
		 * from `customify/wishlist/providers`, or '' for none. An id whose
		 * provider is inactive or unknown is ignored (renders nothing).
		 *
		 * @since 0.4.27
		 *
		 * @param string|null $chosen    Id of the first active provider, or null.
		 * @param array       $providers All providers, keyed by id.
		 */
		$chosen = apply_filters( 'customify/wishlist/provider', $chosen, $providers );

		$active = null;
		if ( is_string( $chosen ) && isset( $providers[ $chosen ] ) && $this->is_provider_active( $providers[ $chosen ] ) ) {
			$active = $providers[ $chosen ];
		}

		// Only memoise once `init` has run, so filters registered on `init`
		// by late-loading code are never missed by an early caller.
		if ( did_action( 'init' ) ) {
			$this->active = $active;
		}

		return $active;
	}

	/**
	 * Wishlist page URL for a provider.
	 *
	 * @param array|null $provider Normalised provider; defaults to the active one.
	 *
	 * @return string Raw URL ('' when unknown) — escape on output.
	 */
	public function get_url( $provider = null ) {
		if ( null === $provider ) {
			$provider = $this->get_active_provider();
		}
		if ( ! $provider ) {
			return '';
		}

		$url = $provider['url'];
		if ( is_callable( $url ) ) {
			$url = call_user_func( $url );
		}

		return is_string( $url ) ? $url : '';
	}

	/**
	 * YITH wishlist page URL.
	 *
	 * @return string
	 */
	public function yith_url() {
		if ( ! function_exists( 'YITH_WCWL' ) ) {
			return '';
		}
		$yith = YITH_WCWL();

		return ( $yith && is_callable( array( $yith, 'get_wishlist_url' ) ) ) ? (string) $yith->get_wishlist_url() : '';
	}

	/**
	 * Enqueue the active provider's own scripts (called from the item render).
	 */
	public function enqueue_provider_scripts() {
		$provider = $this->get_active_provider();
		if ( ! $provider ) {
			return;
		}
		foreach ( (array) $provider['scripts'] as $handle ) {
			if ( is_string( $handle ) && wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}
		}
	}

	/**
	 * Front-end settings, merged into the localized `Customify_JS` object.
	 *
	 * @param array $args Customify_JS settings.
	 *
	 * @return array
	 */
	public function js_settings( $args ) {
		$provider = $this->get_active_provider();
		$remote   = $provider && is_callable( $provider['count'] ) && class_exists( 'WC_AJAX' );
		$events   = array(
			'body'     => array(),
			'document' => array(),
		);
		if ( $provider ) {
			foreach ( array_keys( $events ) as $target ) {
				$events[ $target ] = array_values( array_filter( array_map( 'strval', (array) $provider['events'][ $target ] ) ) );
			}
		}

		$args['wishlist'] = array(
			'provider' => $provider ? $provider['id'] : '',
			'endpoint' => $remote ? WC_AJAX::get_endpoint( self::AJAX_ACTION ) : '',
			'events'   => $events,
			// Appended to the item's own label for the link's aria-label,
			// e.g. "Wishlist, 3 items" (the label is the accessible-name base
			// so a custom visible label stays part of the name).
			'i18n'     => array(
				/* translators: %d: number of items in the wishlist. */
				'one'  => _n( '%d item', '%d items', 1, 'customify' ),
				/* translators: %d: number of items in the wishlist. */
				'many' => _n( '%d item', '%d items', 2, 'customify' ),
			),
		);

		return $args;
	}

	/**
	 * `?wc-ajax=customify_wishlist_count` — the current visitor's count.
	 *
	 * Deliberately public and nonce-free. AGENTS.md §4.8 governs privileged
	 * `wp_ajax_*` handlers; this one is neither privileged nor state-changing:
	 * - read-only: it only calls the provider's count callback, no writes;
	 * - it reads no request input at all, so there is nothing to sanitise;
	 * - it only answers about the requester's OWN list (resolved from their
	 *   auth / wishlist-session cookies), and the JSON carries no CORS
	 *   headers, so another origin cannot read it;
	 * - a nonce would be baked into full-page-cached HTML and go stale after
	 *   12-24h, and cached pages are exactly what this endpoint exists for.
	 * `nocache_headers()` keeps proxies/CDNs from caching one visitor's count.
	 */
	public function ajax_count() {
		nocache_headers();

		$provider = $this->get_active_provider();
		if ( ! $provider || ! is_callable( $provider['count'] ) ) {
			wp_send_json_error( array( 'count' => null ), 404 );
		}

		$count = absint( call_user_func( $provider['count'] ) );

		wp_send_json_success(
			array(
				'provider' => $provider['id'],
				'count'    => $count,
			)
		);
	}
}

/**
 * Accessor for the wishlist provider layer.
 *
 * @since 0.4.27
 *
 * @return Customify_WC_Wishlist
 */
function customify_wc_wishlist() {
	return Customify_WC_Wishlist::get_instance();
}

customify_wc_wishlist();
