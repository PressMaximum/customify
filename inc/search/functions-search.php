<?php
/**
 * Search results framework.
 *
 * Renders the search results page as a type grouped listing: results are
 * ordered by content type and each type gets its own section - a generic
 * content card for posts / pages / CPTs and the native WooCommerce product
 * card for products - plus a content type tab bar and per type result counts.
 *
 * Sites whose product listings are driven by an external template system can
 * drop product cards from the mixed page entirely (Search Results > "Products
 * in mixed results" = teaser) and link into the product scoped surface
 * instead.
 *
 * Swap window safety
 * ------------------
 * The listing is emitted as ONE pass over the MAIN query. Block based page
 * builders (Blocksify and friends) buffer the `loop_start` -> `loop_end`
 * window of the main query and swap the content inside it, so every piece of
 * listing markup must be printed from inside the loop iterations. That covers
 * the per result cards AND the structural markup around them: every section
 * heading, every group `<ul>` opening tag and every closing tag is printed
 * from inside an iteration - a group closes at the top of the iteration that
 * starts the next group, and the last group closes inside the final
 * iteration. Never buffer the listing and print it after the loop, and never
 * run a secondary WP_Query inside the listing region. Tabs, heading and the
 * products teaser render before the loop, pagination after it - all outside
 * the swap window.
 *
 * @package customify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'customify_search_get_result_types' ) ) {
	/**
	 * Public, searchable post types that can show up in search results.
	 *
	 * @return WP_Post_Type[] Keyed by post type slug.
	 */
	function customify_search_get_result_types() {
		$types = get_post_types(
			array(
				'public'              => true,
				'exclude_from_search' => false,
			),
			'objects'
		);

		// Attachments match the args above but never surface in a default
		// search because their post_status is `inherit`, not `publish`.
		// Dropping them keeps the tab counts consistent with the main query.
		unset( $types['attachment'] );

		/**
		 * Filter the content types offered on the search results page.
		 *
		 * Controls both the tab bar and the per type result counts.
		 *
		 * @since 0.5.0
		 *
		 * @param WP_Post_Type[] $types Post type objects keyed by slug.
		 */
		$types = apply_filters( 'customify/search/content_types', $types );

		return is_array( $types ) ? $types : array();
	}
}

if ( ! function_exists( 'customify_search_get_type_order' ) ) {
	/**
	 * Order the result type sections are rendered in.
	 *
	 * Products lead on shops - they are the highest intent match on a store -
	 * followed by posts and pages, then every remaining searchable type in
	 * registration order.
	 *
	 * @return string[] Post type slugs, most important first.
	 */
	function customify_search_get_type_order() {
		$types = customify_search_get_result_types();
		$order = array();

		if ( Customify()->is_woocommerce_active() && isset( $types['product'] ) ) {
			$order[] = 'product';
		}

		foreach ( array( 'post', 'page' ) as $slug ) {
			if ( isset( $types[ $slug ] ) && ! in_array( $slug, $order, true ) ) {
				$order[] = $slug;
			}
		}

		foreach ( $types as $slug => $type ) {
			if ( ! in_array( $slug, $order, true ) ) {
				$order[] = $slug;
			}
		}

		/**
		 * Filter the order of the search result type sections.
		 *
		 * Types missing from the list still render - they sort after every
		 * listed type, in registration order.
		 *
		 * @since 0.5.0
		 *
		 * @param string[] $order Post type slugs, most important first.
		 */
		$order = apply_filters( 'customify/search/type_order', $order );

		if ( ! is_array( $order ) ) {
			return array();
		}

		return array_values( array_unique( array_filter( $order, 'is_string' ) ) );
	}
}

if ( ! function_exists( 'customify_search_get_scope_choices' ) ) {
	/**
	 * Select choices for the header search item "Search scope" setting.
	 *
	 * @return array Slug => label. The empty key means "search everything".
	 */
	function customify_search_get_scope_choices() {
		$choices = array( '' => __( 'Everything', 'customify' ) );

		foreach ( customify_search_get_result_types() as $slug => $type ) {
			$choices[ $slug ] = $type->labels->name;
		}

		return $choices;
	}
}

if ( ! function_exists( 'customify_search_get_type_counts' ) ) {
	/**
	 * Count search matches per content type.
	 *
	 * Runs one minimal count query per type (ids only, one row) and caches the
	 * result for the rest of the request.
	 *
	 * @param string $search_query Raw search term.
	 *
	 * @return array Post type slug => count, plus `all` => sum. Empty when the
	 *               search term is empty.
	 */
	function customify_search_get_type_counts( $search_query ) {
		static $cache = array();

		$search_query = is_string( $search_query ) ? $search_query : '';

		if ( '' === trim( $search_query ) ) {
			return array();
		}

		$cache_key = md5( $search_query );

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$counts = array( 'all' => 0 );

		foreach ( customify_search_get_result_types() as $slug => $type ) {
			$args = array(
				's'                      => $search_query,
				'post_type'              => $slug,
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => false,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			// WooCommerce hides `exclude-from-search` products from the main
			// search query, but only on the MAIN query - mirror it here so the
			// tab count matches what the listing actually shows.
			if ( 'product' === $slug && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
				$visibility = wc_get_product_visibility_term_ids();

				if ( ! empty( $visibility['exclude-from-search'] ) ) {
					$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'term_taxonomy_id',
							'terms'    => array( $visibility['exclude-from-search'] ),
							'operator' => 'NOT IN',
						),
					);
				}
			}

			$query = new WP_Query( $args );

			$count            = (int) $query->found_posts;
			$counts[ $slug ]  = $count;
			$counts['all']   += $count;
		}

		$cache[ $cache_key ] = $counts;

		return $counts;
	}
}

if ( ! function_exists( 'customify_search_get_current_scope' ) ) {
	/**
	 * Post type the current search is scoped to.
	 *
	 * @return string Post type slug, empty string when searching everything.
	 */
	function customify_search_get_current_scope() {
		$current = get_query_var( 'post_type' );

		if ( is_array( $current ) ) {
			$current = ( 1 === count( $current ) ) ? reset( $current ) : '';
		}

		if ( ! is_string( $current ) ) {
			return '';
		}

		// WP_Query::get_posts() writes `any` back into the main query's
		// `post_type` var while executing an unscoped search, so by render
		// time an unscoped page reads `any`, not ''. Same normalization as
		// customify_search_query_is_type_scoped().
		return ( 'any' === $current ) ? '' : $current;
	}
}

if ( ! function_exists( 'customify_search_get_default_scope' ) ) {
	/**
	 * Content type unscoped searches should land on.
	 *
	 * Shop first sites want `?s=term` to open the product results directly
	 * instead of the mixed page with its products teaser - one click less
	 * between the search box and a product listing.
	 *
	 * @return string Valid post type slug, empty string when unscoped searches
	 *                stay on the mixed "Everything" page.
	 */
	function customify_search_get_default_scope() {
		$scope = Customify()->get_setting( 'search_results_default_scope' );
		$scope = is_string( $scope ) ? sanitize_key( $scope ) : '';

		if ( '' === $scope ) {
			return '';
		}

		$types = customify_search_get_result_types();

		return isset( $types[ $scope ] ) ? $scope : '';
	}
}

if ( ! function_exists( 'customify_search_maybe_redirect_to_scope' ) ) {
	/**
	 * Send an unscoped search to the configured default scope.
	 *
	 * Why a URL level redirect and not a `pre_get_posts` mutation
	 * ----------------------------------------------------------
	 * Forcing the scope by setting `post_type` on the main search query would
	 * violate the second invariant of this file: scoped template resolvers
	 * (Blocksify `query_scoped_to_post_type()`) match with
	 * `in_array( $type, (array) $qv )`, so a silently scoped main query hands
	 * the page to whatever template is registered for `search:<type>` while the
	 * URL still says "everything" - and WP_Query writes `any` back into that
	 * same var on unscoped searches, so the state is not even stable to read
	 * back. A redirect keeps query and URL in agreement: the browser lands on a
	 * genuinely scoped request that every downstream consumer - tabs, template
	 * resolvers, WooCommerce, pagination, canonical URLs, analytics - reads the
	 * same way, and the visitor can see and undo the scope.
	 *
	 * Never redirects into an empty surface: when the term has no matches in
	 * the configured type the mixed page, with its no results block and its
	 * products teaser, is the better landing.
	 *
	 * `cfy_all` is the explicit bypass the All tab carries, so clicking "All"
	 * from a scoped view does not bounce straight back.
	 */
	function customify_search_maybe_redirect_to_scope() {
		if ( is_admin() || ! is_search() || is_feed() ) {
			return;
		}

		global $wp_query;

		// Chrome rendered by a secondary query context is none of our business.
		if ( ! is_a( $wp_query, 'WP_Query' ) || ! $wp_query->is_main_query() ) {
			return;
		}

		// Only ever act on the mixed page - a scoped request is already where
		// somebody wanted it.
		if ( '' !== customify_search_get_current_scope() ) {
			return;
		}

		// Loop guard. The scope check above reads the QUERY VAR, which can be
		// empty even though the URL asked for a type - core strips post_type
		// vars it considers non public, and the restore filter in this file
		// deliberately does not override a third party's removal. Redirecting
		// then would rebuild the very same URL, forever. A request that already
		// names a post_type is somebody's explicit choice either way.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only query var on a public search request.
		if ( ! empty( $_GET['post_type'] ) ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

		if ( 'GET' !== $method ) {
			return;
		}

		// The bypass is a presence flag: the value is never read, so there is
		// nothing to sanitize beyond the key lookup itself.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only marker on a public search request.
		if ( isset( $_GET['cfy_all'] ) ) {
			return;
		}

		$term = get_search_query( false );

		if ( ! is_string( $term ) || '' === trim( $term ) ) {
			return;
		}

		$scope = customify_search_get_default_scope();

		if ( '' === $scope ) {
			return;
		}

		$counts = customify_search_get_type_counts( $term );

		if ( empty( $counts[ $scope ] ) || (int) $counts[ $scope ] < 1 ) {
			return;
		}

		// No URL argument: both helpers then operate on the current
		// REQUEST_URI, so unrelated params (language, tracking, pagination)
		// survive the redirect.
		$url = add_query_arg( 'post_type', rawurlencode( $scope ), remove_query_arg( 'cfy_all' ) );

		wp_safe_redirect( $url, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'customify_search_maybe_redirect_to_scope', 1 );

if ( ! function_exists( 'customify_search_results_form' ) ) {
	/**
	 * Search form rendered on the results page itself.
	 *
	 * Refining a query should not mean going back to the header search. On a
	 * scoped view the current scope rides along in a hidden field so refining a
	 * product search stays on products.
	 *
	 * Chrome, not listing: every call site prints it before the loop opens, so
	 * it never enters the swap window described at the top of this file.
	 */
	function customify_search_results_form() {
		if ( ! is_search() ) {
			return;
		}

		if ( ! Customify()->get_setting( 'search_results_show_search_form' ) ) {
			return;
		}

		$scope = customify_search_get_current_scope();
		?>
		<form role="search" method="get" class="cfy-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label>
				<span class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'customify' ); ?></span>
				<input type="search" class="cfy-search-form-field" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php echo esc_attr__( 'Search …', 'customify' ); ?>" />
			</label>
			<?php if ( '' !== $scope ) : ?>
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $scope ); ?>" />
			<?php endif; ?>
			<button type="submit" class="cfy-search-form-submit">
				<svg aria-hidden="true" focusable="false" role="presentation" xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21">
					<path fill="currentColor" fill-rule="evenodd" d="M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z"></path>
				</svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'customify' ); ?></span>
			</button>
		</form>
		<?php
	}
}

if ( ! function_exists( 'customify_search_restore_scoped_post_type' ) ) {
	/**
	 * Keep a search scoped to a public but not publicly queryable post type.
	 *
	 * `WP::parse_request()` limits the public `post_type` query var to types
	 * registered with `publicly_queryable => true` - see the "Limit publicly
	 * queried post_types to those that are 'publicly_queryable'" block in
	 * wp-includes/class-wp.php. Core registers `page` as `public => true,
	 * publicly_queryable => false`, so `?s=term&post_type=page` reaches the
	 * theme as a plain `?s=term`: the Pages tab silently searched everything.
	 * `post` and `product` ARE queryable, which is why only some tabs broke.
	 *
	 * The `request` filter runs after that stripping, so a value restored here
	 * sticks - and the restored query var is what scoped template resolvers
	 * (Blocksify `search:page` conditions) read as well.
	 *
	 * Nothing new is exposed: the whitelist is customify_search_get_result_types(),
	 * i.e. public types that are not excluded from search, so every accepted
	 * type is one the unscoped search page already lists.
	 *
	 * @param array $query_vars Parsed public query vars.
	 *
	 * @return array
	 */
	function customify_search_restore_scoped_post_type( $query_vars ) {
		if ( ! is_array( $query_vars ) || ! array_key_exists( 's', $query_vars ) ) {
			return $query_vars;
		}

		// Something survived the strip (a queryable type, or another plugin's
		// value) - leave it alone.
		if ( ! empty( $query_vars['post_type'] ) ) {
			return $query_vars;
		}

		// Only the single type form the tabs and the header search forms emit.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only query var on a public search request.
		if ( empty( $_GET['post_type'] ) || ! is_string( $_GET['post_type'] ) ) {
			return $query_vars;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only query var on a public search request.
		$type = sanitize_key( wp_unslash( $_GET['post_type'] ) );

		if ( '' === $type ) {
			return $query_vars;
		}

		$types = customify_search_get_result_types();

		if ( ! isset( $types[ $type ] ) ) {
			return $query_vars;
		}

		// A queryable type was never stripped by core, so its absence here is
		// somebody else's decision - do not override it.
		if ( ! empty( $types[ $type ]->publicly_queryable ) ) {
			return $query_vars;
		}

		$query_vars['post_type'] = $type;

		return $query_vars;
	}
}
add_filter( 'request', 'customify_search_restore_scoped_post_type' );

if ( ! function_exists( 'customify_search_query_is_type_scoped' ) ) {
	/**
	 * Is a query narrowed to ONE post type?
	 *
	 * A scoped search (`?s=term&post_type=product`) is somebody else's surface:
	 * it must keep the plain relevance order and its own product handling. An
	 * empty scope, `any`, or a multi type list is the mixed page.
	 *
	 * @param WP_Query|null $query Query object.
	 *
	 * @return bool
	 */
	function customify_search_query_is_type_scoped( $query = null ) {
		if ( ! is_a( $query, 'WP_Query' ) ) {
			return false;
		}

		$post_type = $query->get( 'post_type' );

		if ( is_array( $post_type ) ) {
			$post_type = array_values( array_filter( $post_type ) );

			if ( 1 !== count( $post_type ) ) {
				return false;
			}

			$post_type = $post_type[0];
		}

		if ( ! is_string( $post_type ) ) {
			return false;
		}

		$post_type = trim( $post_type );

		return ( '' !== $post_type && 'any' !== $post_type );
	}
}

if ( ! function_exists( 'customify_search_group_main_query_posts' ) ) {
	/**
	 * Group the mixed search results by content type.
	 *
	 * Relevance ordering interleaves product cards with content cards, which
	 * reads as a messy page. Sorting the fetched posts by type turns the same
	 * result set into readable sections without a second query - the listing
	 * still makes ONE pass over the main query.
	 *
	 * Relevance order INSIDE each type is preserved: `usort()` is not stable in
	 * PHP 7.4, so each post carries its original index as the tie breaker.
	 *
	 * @param WP_Post[]     $posts Posts fetched by the query.
	 * @param WP_Query|null $query Query object.
	 *
	 * @return WP_Post[]
	 */
	function customify_search_group_main_query_posts( $posts, $query = null ) {
		if ( ! is_array( $posts ) || count( $posts ) < 2 ) {
			return $posts;
		}

		if ( is_admin() || ! is_a( $query, 'WP_Query' ) ) {
			return $posts;
		}

		if ( ! $query->is_main_query() || ! $query->is_search() ) {
			return $posts;
		}

		if ( customify_search_query_is_type_scoped( $query ) ) {
			return $posts;
		}

		$order     = array_flip( customify_search_get_type_order() );
		$unknown   = count( $order );
		$decorated = array();

		foreach ( array_values( $posts ) as $index => $post ) {
			$type = get_post_type( $post );

			$decorated[] = array(
				'rank'  => ( $type && isset( $order[ $type ] ) ) ? (int) $order[ $type ] : $unknown,
				'index' => $index,
				'post'  => $post,
			);
		}

		usort(
			$decorated,
			function ( $a, $b ) {
				if ( $a['rank'] === $b['rank'] ) {
					return $a['index'] <=> $b['index'];
				}

				return $a['rank'] <=> $b['rank'];
			}
		);

		$sorted = array();

		foreach ( $decorated as $item ) {
			$sorted[] = $item['post'];
		}

		return $sorted;
	}
}
add_filter( 'the_posts', 'customify_search_group_main_query_posts', 10, 2 );

if ( ! function_exists( 'customify_search_tabs' ) ) {
	/**
	 * Render the content type tab bar.
	 */
	function customify_search_tabs() {
		if ( ! Customify()->get_setting( 'search_results_show_tabs' ) ) {
			return;
		}

		$term        = get_search_query( false );
		$counts      = customify_search_get_type_counts( $term );
		$show_counts = Customify()->get_setting( 'search_results_show_counts' );
		$current     = customify_search_get_current_scope();

		$all_count = isset( $counts['all'] ) ? (int) $counts['all'] : 0;

		// In teaser mode the mixed page lists no product cards, so the All tab
		// counts content only - the Products tab and the teaser banner carry
		// the product number.
		if ( customify_search_is_products_teaser() && ! empty( $counts['product'] ) ) {
			$all_count = max( 0, $all_count - (int) $counts['product'] );
		}

		$all_args = array( 's' => urlencode( $term ) );

		// With a default scope armed, a bare `?s=term` would be redirected back
		// into that scope the moment the All tab is clicked - the bypass marker
		// is what makes "show me everything" reachable at all. Scoped tab URLs
		// need nothing: they are never redirected.
		if ( '' !== customify_search_get_default_scope() ) {
			$all_args['cfy_all'] = 1;
		}

		$tabs = array();

		// A zero-count All tab (teaser mode, term matching only products) leads
		// to an empty mixed page - drop it. With one tab left the whole bar is
		// suppressed below, which is right: nothing to navigate between.
		if ( $all_count > 0 ) {
			$tabs[] = array(
				'key'    => 'all',
				'label'  => __( 'All', 'customify' ),
				'count'  => $all_count,
				'url'    => add_query_arg( $all_args, home_url( '/' ) ),
				'active' => ( '' === $current ),
			);
		}

		foreach ( customify_search_get_result_types() as $slug => $type ) {
			if ( empty( $counts[ $slug ] ) ) {
				continue;
			}

			$tabs[] = array(
				'key'    => $slug,
				'label'  => $type->labels->name,
				'count'  => (int) $counts[ $slug ],
				'url'    => add_query_arg(
					array(
						's'         => urlencode( $term ),
						'post_type' => $slug,
					),
					home_url( '/' )
				),
				'active' => ( $slug === $current ),
			);
		}

		/**
		 * Filter the search results tab items before they are rendered.
		 *
		 * Each item is an array with `key`, `label`, `count`, `url` and
		 * `active` keys.
		 *
		 * @since 0.5.0
		 *
		 * @param array  $tabs Tab items.
		 * @param string $term Raw search term.
		 */
		$tabs = apply_filters( 'customify/search/tabs', $tabs, $term );

		if ( ! is_array( $tabs ) || count( $tabs ) < 2 ) {
			// A lone "All" tab carries no information, skip the whole bar.
			return;
		}
		?>
		<nav class="cfy-search-tabs" aria-label="<?php esc_attr_e( 'Filter results by content type', 'customify' ); ?>">
			<ul>
				<?php
				foreach ( $tabs as $tab ) {
					$tab = wp_parse_args(
						$tab,
						array(
							'key'    => '',
							'label'  => '',
							'count'  => 0,
							'url'    => '',
							'active' => false,
						)
					);

					if ( '' === $tab['label'] || '' === $tab['url'] ) {
						continue;
					}
					?>
					<li class="cfy-search-tab<?php echo $tab['active'] ? ' is-active' : ''; ?>">
						<a href="<?php echo esc_url( $tab['url'] ); ?>"<?php echo $tab['active'] ? ' aria-current="page"' : ''; ?>>
							<span class="cfy-search-tab-label"><?php echo esc_html( $tab['label'] ); ?></span>
							<?php if ( $show_counts ) : ?>
								<span class="cfy-search-tab-count"><?php echo esc_html( number_format_i18n( (int) $tab['count'] ) ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</nav>
		<?php
	}
}

if ( ! function_exists( 'customify_search_get_product_display' ) ) {
	/**
	 * How products behave in the mixed (unscoped) search listing.
	 *
	 * @return string `cards` - render product cards inline, or `teaser` - drop
	 *                them and link to the product scoped results instead.
	 */
	function customify_search_get_product_display() {
		$mode = Customify()->get_setting( 'search_results_product_display' );
		$mode = is_string( $mode ) ? sanitize_key( $mode ) : '';

		return ( 'teaser' === $mode ) ? 'teaser' : 'cards';
	}
}

if ( ! function_exists( 'customify_search_is_products_teaser' ) ) {
	/**
	 * Are products replaced by a teaser link on the mixed search page?
	 *
	 * Meaningless without WooCommerce, where the setting simply no-ops.
	 *
	 * @return bool
	 */
	function customify_search_is_products_teaser() {
		return ( Customify()->is_woocommerce_active() && 'teaser' === customify_search_get_product_display() );
	}
}

if ( ! function_exists( 'customify_search_exclude_products_from_main_query' ) ) {
	/**
	 * Drop products from the unscoped main search query in teaser mode.
	 *
	 * Sites whose product loops are built by an external template system get a
	 * design system clash when theme styled product cards sit in the mixed
	 * listing. Excluding products here keeps the mixed page purely editorial;
	 * the teaser banner then routes shoppers to the product scoped surface,
	 * which the owning system still renders.
	 *
	 * Scoped searches, feeds, admin and secondary queries are never touched.
	 *
	 * @param WP_Query $query Query object.
	 */
	function customify_search_exclude_products_from_main_query( $query ) {
		if ( is_admin() || ! is_a( $query, 'WP_Query' ) ) {
			return;
		}

		if ( ! $query->is_main_query() || ! $query->is_search() || $query->is_feed() ) {
			return;
		}

		if ( customify_search_query_is_type_scoped( $query ) ) {
			return;
		}

		if ( ! customify_search_is_products_teaser() ) {
			return;
		}

		// Flag only - the SQL lands in posts_where below. Setting `post_type`
		// here would make the query LOOK type scoped: template resolvers match
		// scoped search conditions with `in_array( $type, (array) $qv )`
		// (Blocksify `query_scoped_to_post_type()`), so a template scoped to
		// e.g. `search:post` would hijack the unscoped mixed page the moment
		// the query var carries an array containing `post`.
		$query->set( 'customify_search_teaser_exclude_products', true );
	}
}
add_action( 'pre_get_posts', 'customify_search_exclude_products_from_main_query' );

if ( ! function_exists( 'customify_search_teaser_posts_where' ) ) {
	/**
	 * SQL side of the teaser mode product exclusion.
	 *
	 * Runs only for queries flagged by
	 * customify_search_exclude_products_from_main_query() and filters products
	 * out at the WHERE level, leaving every public query var untouched.
	 *
	 * @param string        $where WHERE clause.
	 * @param WP_Query|null $query Query object.
	 *
	 * @return string
	 */
	function customify_search_teaser_posts_where( $where, $query = null ) {
		if ( ! is_a( $query, 'WP_Query' ) || ! $query->get( 'customify_search_teaser_exclude_products' ) ) {
			return $where;
		}

		global $wpdb;

		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_type <> %s", 'product' );

		return $where;
	}
}
add_filter( 'posts_where', 'customify_search_teaser_posts_where', 10, 2 );

if ( ! function_exists( 'customify_search_products_teaser' ) ) {
	/**
	 * Banner linking to the product scoped results, for teaser mode.
	 *
	 * Chrome, not listing: it renders before the loop opens, next to the tabs,
	 * and never from inside the swap window.
	 */
	function customify_search_products_teaser() {
		if ( ! is_search() || '' !== customify_search_get_current_scope() ) {
			return;
		}

		if ( ! customify_search_is_products_teaser() ) {
			return;
		}

		// The counts helper runs its own per type queries, so it still sees the
		// products the main query just excluded.
		$term   = get_search_query( false );
		$counts = customify_search_get_type_counts( $term );
		$count  = isset( $counts['product'] ) ? (int) $counts['product'] : 0;

		if ( $count < 1 ) {
			return;
		}

		$url = add_query_arg(
			array(
				's'         => urlencode( $term ),
				'post_type' => 'product',
			),
			home_url( '/' )
		);
		?>
		<div class="cfy-search-products-teaser">
			<span class="cfy-search-products-teaser-label">
				<?php
				printf(
					/* translators: %s: number of products matching the search term. */
					esc_html( _n( '%s product matches your search', '%s products match your search', $count, 'customify' ) ),
					esc_html( number_format_i18n( $count ) )
				);
				?>
			</span>
			<a class="cfy-search-products-teaser-link" href="<?php echo esc_url( $url ); ?>">
				<?php esc_html_e( 'View products', 'customify' ); ?> &rarr;
			</a>
		</div>
		<?php
	}
}

if ( ! function_exists( 'customify_search_get_card_renderers' ) ) {
	/**
	 * Card renderer registry.
	 *
	 * @return array Post type slug => callable, plus `_default` fallback.
	 */
	function customify_search_get_card_renderers() {
		$map = array(
			'_default' => 'customify_search_render_content_card',
		);

		if ( Customify()->is_woocommerce_active() ) {
			$map['product'] = 'customify_search_render_product_card';
		}

		/**
		 * Filter the per post type search card renderers.
		 *
		 * @since 0.5.0
		 *
		 * @param array $map Post type slug => callable. `_default` is the
		 *                   fallback used for unmapped types.
		 */
		$map = apply_filters( 'customify/search/card_renderers', $map );

		return is_array( $map ) ? $map : array();
	}
}

if ( ! function_exists( 'customify_search_render_card' ) ) {
	/**
	 * Render one search result card.
	 *
	 * @param WP_Post|null $post Post object.
	 */
	function customify_search_render_card( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		$renderers = customify_search_get_card_renderers();
		$type      = get_post_type( $post );
		$callback  = isset( $renderers[ $type ] ) ? $renderers[ $type ] : null;

		if ( ! $callback && isset( $renderers['_default'] ) ) {
			$callback = $renderers['_default'];
		}

		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $post );
		}
	}
}

if ( ! function_exists( 'customify_search_get_excerpt' ) ) {
	/**
	 * Build the card excerpt honouring the Search Results excerpt settings.
	 *
	 * The result is ALWAYS length capped: a search card must never dump a full
	 * post body (that is what leaked raw product descriptions into the old
	 * search listing).
	 *
	 * @param WP_Post|null $post Post object.
	 *
	 * @return string Excerpt HTML, empty string when there is nothing to show.
	 */
	function customify_search_get_excerpt( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return '';
		}

		$type   = Customify()->get_setting( 'search_results_excerpt_type' );
		$length = absint( Customify()->get_setting( 'search_results_excerpt_length' ) );
		$more   = Customify()->get_setting( 'search_results_excerpt_more' );

		if ( $length < 1 ) {
			/** This filter is documented in wp-includes/formatting.php */
			$length = (int) apply_filters( 'excerpt_length', 55 );
		}

		if ( ! is_string( $more ) || '' === $more ) {
			/** This filter is documented in wp-includes/formatting.php */
			$more = apply_filters( 'excerpt_more', ' ' . '&hellip;' );
		}

		if ( 'excerpt' === $type ) {
			$text = get_the_excerpt( $post );

			if ( '' !== trim( wp_strip_all_tags( $text ) ) ) {
				/** This filter is documented in wp-includes/post-template.php */
				return apply_filters( 'the_excerpt', $text );
			}
		}

		// Source text for every length capped mode. `more_tag` stops at the
		// <!--more--> marker, the other modes read the full body and let
		// wp_trim_words() do the capping.
		if ( 'more_tag' === $type ) {
			$source = explode( '<!--more', $post->post_content );
			$source = $source[0];
		} elseif ( 'content' === $type ) {
			$source = $post->post_content;
		} else {
			$source = $post->post_excerpt;

			if ( '' === trim( (string) $source ) ) {
				$source = $post->post_content;
			}
		}

		// Deliberately NOT `the_content`-filtered: rendering shortcodes/blocks to
		// full HTML just to strip it back down is wasted work, and third-party
		// content filters are not safe to run per card inside the results loop.
		$source = strip_shortcodes( (string) $source );
		$source = excerpt_remove_blocks( $source );

		$text = wp_trim_words( $source, $length, $more );

		if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			return '';
		}

		return wpautop( $text );
	}
}

if ( ! function_exists( 'customify_search_render_content_card' ) ) {
	/**
	 * Generic content card - posts, pages and custom post types.
	 *
	 * @param WP_Post|null $post Post object.
	 */
	function customify_search_render_content_card( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		$type       = get_post_type( $post );
		$type_obj   = get_post_type_object( $type );
		$columns    = customify_search_get_columns();
		$show_media = Customify()->get_setting( 'search_results_show_media' );
		$has_media  = $show_media && has_post_thumbnail( $post );

		// Additive class merge: customify_post_classes() (post_class @999)
		// replaces the whole class list on search pages, so the card modifiers
		// are appended AFTER get_post_class() has run instead of being passed
		// into it - that keeps the existing filter untouched.
		$classes = get_post_class( '', $post );
		$classes[] = 'cfy-search-card';
		$classes[] = 'cfy-search-card--' . $type;

		if ( ! $has_media ) {
			$classes[] = 'no-media';
		}

		if ( $columns > 1 ) {
			$classes[] = 'customify-col';
		}

		$classes = array_unique( array_filter( $classes ) );
		$excerpt = customify_search_get_excerpt( $post );
		?>
		<li class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
			<div class="cfy-search-card-inner">
				<?php if ( $has_media ) : ?>
					<a class="cfy-search-card-media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo get_the_post_thumbnail( $post, 'medium_large' ); // WPCS: XSS ok. ?>
					</a>
				<?php endif; ?>
				<div class="cfy-search-card-body">
					<?php
					if ( Customify()->get_setting( 'search_results_show_type_badge' ) && $type_obj && ! empty( $type_obj->labels->singular_name ) ) {
						echo '<span class="cfy-search-card-badge">' . esc_html( $type_obj->labels->singular_name ) . '</span>';
					}
					?>
					<h2 class="cfy-search-card-title">
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
					</h2>
					<?php if ( 'post' === $type ) : ?>
						<time class="cfy-search-card-date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>">
							<?php echo esc_html( get_the_date( '', $post ) ); ?>
						</time>
					<?php endif; ?>
					<?php if ( $excerpt ) : ?>
						<div class="cfy-search-card-excerpt"><?php echo $excerpt; // WPCS: XSS ok. ?></div>
					<?php endif; ?>
				</div>
			</div>
		</li>
		<?php
	}
}

if ( ! function_exists( 'customify_search_render_product_card' ) ) {
	/**
	 * WooCommerce product card - the native shop loop item.
	 *
	 * @param WP_Post|null $post Post object.
	 */
	function customify_search_render_product_card( $post = null ) {
		$post = get_post( $post );

		if ( ! $post || ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		global $product;

		// `wc_setup_product_data` (hooked to `the_post`) normally fills this in,
		// but rebuild it whenever it is missing or points at another product.
		if ( ! is_a( $product, 'WC_Product' ) || $product->get_id() !== $post->ID ) {
			$product = wc_get_product( $post );
		}

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		// wc_get_product_class() runs the `post_class` filter, and
		// customify_post_classes() (@999) replaces the entire class list with
		// `entry hentry search-article` on search pages - that would strip
		// `customify-col` and the WooCommerce loop classes off the product
		// card. Suspend it for the duration of this card only; the product
		// scoped search page (?s=x&post_type=product) already renders this way.
		$priority = has_filter( 'post_class', 'customify_post_classes' );

		if ( false !== $priority ) {
			remove_filter( 'post_class', 'customify_post_classes', $priority );
		}

		wc_get_template_part( 'content', 'product' );

		if ( false !== $priority ) {
			add_filter( 'post_class', 'customify_post_classes', $priority );
		}
	}
}

if ( ! function_exists( 'customify_search_get_columns' ) ) {
	/**
	 * Column count for the search results grid.
	 *
	 * @return int Between 1 and 4.
	 */
	function customify_search_get_columns() {
		$columns = absint( Customify()->get_setting( 'search_results_columns' ) );

		if ( $columns < 1 ) {
			$columns = 3;
		} elseif ( $columns > 4 ) {
			$columns = 4;
		}

		return $columns;
	}
}

if ( ! function_exists( 'customify_search_wc_setup_loop' ) ) {
	/**
	 * Prime the WooCommerce loop props for an embedded product card grid.
	 *
	 * Product cards read loop props (secondary image mode, column count) and
	 * Customify_WC::post_class() only adds `customify-col` while a WooCommerce
	 * loop is active, so the props have to exist before the first card.
	 *
	 * @param int $columns Grid column count.
	 */
	function customify_search_wc_setup_loop( $columns = 3 ) {
		if ( ! Customify()->is_woocommerce_active() || ! function_exists( 'wc_set_loop_prop' ) ) {
			return;
		}

		/** This action is documented in woocommerce/loop/loop-start.php */
		do_action( 'customify_wc_loop_start' );

		wc_set_loop_prop( 'columns', absint( $columns ) );
	}
}

if ( ! function_exists( 'customify_search_section_title' ) ) {
	/**
	 * Heading that opens one result type section.
	 *
	 * Printed from INSIDE a loop iteration - see the swap window note at the
	 * top of this file.
	 *
	 * @param string $type   Post type slug of the group.
	 * @param array  $counts Per type counts from customify_search_get_type_counts().
	 */
	function customify_search_section_title( $type, $counts = array() ) {
		$type_obj = get_post_type_object( $type );

		if ( ! $type_obj || empty( $type_obj->labels->name ) ) {
			return;
		}

		// The count is the TOTAL for the type, not the number on this page, so
		// it matches the tab bar. Missing counts (empty search term, filtered
		// helper) simply drop the badge.
		$count = ( is_array( $counts ) && ! empty( $counts[ $type ] ) ) ? (int) $counts[ $type ] : 0;

		echo '<h2 class="cfy-search-section-title">' . esc_html( $type_obj->labels->name );

		if ( $count > 0 ) {
			echo ' <span class="cfy-search-section-count">(' . esc_html( number_format_i18n( $count ) ) . ')</span>';
		}

		echo '</h2>';
	}
}

if ( ! function_exists( 'customify_search_group_list_open' ) ) {
	/**
	 * Opening `<ul>` tag for one result type section.
	 *
	 * The product group keeps the WooCommerce loop classes so the Catalog
	 * Designer styles apply to its cards; every other group gets the plain cfy
	 * grid classes - `products` used to leak onto content items when the whole
	 * listing shared one list element.
	 *
	 * Side effect by design: the product group primes the WooCommerce loop
	 * props here, at the exact point that group opens, because product cards
	 * read those props and they must exist before the first card.
	 *
	 * @param string $type    Post type slug of the group.
	 * @param int    $columns Grid column count.
	 *
	 * @return string
	 */
	function customify_search_group_list_open( $type, $columns = 3 ) {
		$columns = absint( $columns );

		if ( $columns < 1 ) {
			$columns = 3;
		}

		$classes = array();
		$atts    = '';

		if ( 'product' === $type && Customify()->is_woocommerce_active() ) {
			// `products` + the view mode class let the WooCommerce and Catalog
			// Designer styles apply to the embedded product cards. A single
			// column listing uses the native list view so product cards lay out
			// media-left like the generic cards above or below them.
			$classes[] = 'products';
			$classes[] = ( $columns > 1 ) ? 'wc-grid-view' : 'wc-list-view';

			customify_search_wc_setup_loop( $columns );
		}

		$classes[] = 'cfy-search-results-grid';

		if ( $columns > 1 ) {
			$classes[] = sprintf( 'customify-grid-%1$d_sm-%2$d_xs-1', $columns, min( 2, $columns ) );
			$atts      = ' data-col="' . esc_attr( $columns ) . '"';
		} else {
			$classes[] = 'cfy-search-list';
		}

		return '<ul class="' . esc_attr( join( ' ', $classes ) ) . '"' . $atts . '>';
	}
}

if ( ! function_exists( 'customify_search_results_layout' ) ) {
	/**
	 * Render the search results listing.
	 *
	 * One pass over the main query - see the swap window note at the top of
	 * this file before restructuring anything in here.
	 */
	function customify_search_results_layout() {
		// Chrome, before the early return on purpose: a teaser mode search that
		// only matched products has an empty main query, and "nothing found"
		// with no pointer to the matching products would be a dead end.
		customify_search_products_teaser();

		if ( ! have_posts() ) {
			get_template_part( 'template-parts/content', 'none' );

			return;
		}

		global $wp_query;

		$columns = customify_search_get_columns();

		// Distinct types on THIS page, in the order the (already grouped)
		// result list holds them. Reading the fetched posts prints nothing, so
		// it is safe to do before the loop opens.
		$page_types = array();

		foreach ( (array) $wp_query->posts as $result ) {
			$slug = get_post_type( $result );

			if ( $slug && ! in_array( $slug, $page_types, true ) ) {
				$page_types[] = $slug;
			}
		}

		// A single type page keeps the plain one list layout it had before -
		// section headings only earn their space when there is more than one.
		$grouped = ( count( $page_types ) > 1 );
		$counts  = $grouped ? customify_search_get_type_counts( get_search_query( false ) ) : array();

		// Section heading, `<ul>` open and `</ul>` close are all emitted from
		// inside loop iterations, so the whole listing lives inside the main
		// query's `loop_start` -> `loop_end` window. A page builder swapping
		// that window (Blocksify Dynamic Templates) then replaces the entire
		// listing; markup printed around the loop would survive the swap and
		// leave the builder output wrapped in a stray grid element.
		$open_type = null;

		while ( have_posts() ) {
			the_post();

			$post = get_post();
			$type = get_post_type( $post );

			if ( null === $open_type || ( $grouped && $type !== $open_type ) ) {
				if ( null !== $open_type ) {
					// Close the group that just ended - still inside the loop.
					echo '</ul>';
				}

				if ( $grouped ) {
					customify_search_section_title( $type, $counts );
				}

				echo customify_search_group_list_open( $type, $columns ); // WPCS: XSS ok.

				$open_type = $type;
			}

			customify_search_render_card( $post );

			if ( (int) $wp_query->current_post + 1 >= (int) $wp_query->post_count ) {
				// Last group closes inside the final iteration.
				echo '</ul>';

				$open_type = null;
			}
		}

		customify_search_pagination();
	}
}

if ( ! function_exists( 'customify_search_pagination' ) ) {
	/**
	 * Search results pagination.
	 *
	 * Reuses the blog pagination settings the search page used before this
	 * framework existed, so sites that customised the prev/next labels keep
	 * seeing them.
	 */
	function customify_search_pagination() {
		if ( ! Customify()->get_setting( 'blog_post_pg_show_paging' ) ) {
			return;
		}

		$prev_next = (bool) Customify()->get_setting( 'blog_post_pg_show_nav' );
		$prev_text = false;
		$next_text = false;

		if ( $prev_next ) {
			$prev_text = Customify()->get_setting( 'blog_post_pg_prev_text' );
			$next_text = Customify()->get_setting( 'blog_post_pg_next_text' );

			if ( ! $prev_text ) {
				$prev_text = _x( 'Previous', 'previous set of posts', 'customify' );
			}

			if ( ! $next_text ) {
				$next_text = _x( 'Next', 'next set of posts', 'customify' );
			}
		}

		the_posts_pagination(
			array(
				'mid_size'  => Customify()->get_setting( 'blog_post_pg_mid_size' ) ? 3 : 0,
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'prev_next' => $prev_next,
			)
		);
	}
}

if ( ! function_exists( 'customify_search_no_results_products' ) ) {
	/**
	 * Popular products block for the "nothing matched" page.
	 *
	 * Only ever runs on the no results branch, where there is no main query
	 * listing to protect - a secondary query is safe here.
	 */
	function customify_search_no_results_products() {
		if ( ! Customify()->is_woocommerce_active() ) {
			return;
		}

		if ( ! Customify()->get_setting( 'search_results_no_results_products' ) ) {
			return;
		}

		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 4,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'meta_key'            => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
		);

		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility = wc_get_product_visibility_term_ids();

			if ( ! empty( $visibility['exclude-from-catalog'] ) ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array( $visibility['exclude-from-catalog'] ),
						'operator' => 'NOT IN',
					),
				);
			}
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			// Shops without sales data have no total_sales meta at all.
			unset( $args['meta_key'], $args['order'] );
			$args['orderby'] = 'date';
			$query           = new WP_Query( $args );
		}

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();

			return;
		}

		customify_search_wc_setup_loop( 4 );
		?>
		<div class="cfy-search-no-results-products">
			<h2 class="cfy-search-no-results-title"><?php esc_html_e( 'Popular products', 'customify' ); ?></h2>
			<ul class="products cfy-search-results-grid wc-grid-view customify-grid-4_sm-2_xs-1" data-col="4">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					customify_search_render_product_card( get_post() );
				}
				?>
			</ul>
		</div>
		<?php
		wp_reset_postdata();
	}
}

if ( ! function_exists( 'customify_search_current_template' ) ) {
	/**
	 * Remember which template file WordPress resolved for this request.
	 *
	 * Used to tell the WooCommerce scoped search page (rendered through
	 * woocommerce.php) apart from search.php, which renders its own heading.
	 *
	 * @param string|null $template Template path when setting.
	 *
	 * @return string Resolved template path.
	 */
	function customify_search_current_template( $template = null ) {
		static $current = '';

		if ( null !== $template ) {
			$current = (string) $template;
		}

		return $current;
	}
}

if ( ! function_exists( 'customify_search_track_template' ) ) {
	/**
	 * Record the resolved template. Hooked late so plugin overrides win.
	 *
	 * @param string $template Template path.
	 *
	 * @return string Unmodified template path.
	 */
	function customify_search_track_template( $template ) {
		customify_search_current_template( $template );

		return $template;
	}
}
add_filter( 'template_include', 'customify_search_track_template', 999 );

if ( ! function_exists( 'customify_search_is_wc_scoped' ) ) {
	/**
	 * Is this a search scoped to products and rendered by the WooCommerce
	 * template rather than search.php?
	 *
	 * @return bool
	 */
	function customify_search_is_wc_scoped() {
		if ( ! Customify()->is_woocommerce_active() ) {
			return false;
		}

		if ( ! is_search() || ! is_post_type_archive( 'product' ) ) {
			return false;
		}

		// search.php renders its own heading and tabs; only the WooCommerce
		// template needs them injected.
		return 'search.php' !== basename( customify_search_current_template() );
	}
}

if ( ! function_exists( 'customify_search_wc_heading' ) ) {
	/**
	 * Inject the search heading and tabs above the WooCommerce catalog.
	 *
	 * A product scoped search renders through woocommerce.php, which has no
	 * search heading of its own. This runs before the catalog region opens, so
	 * it stays well clear of the shop loop.
	 */
	function customify_search_wc_heading() {
		if ( ! customify_search_is_wc_scoped() ) {
			return;
		}

		echo '<div class="cfy-search-results cfy-search-results--wc">';
		customify_blog_posts_heading();
		customify_search_results_form();
		customify_search_tabs();
		echo '</div>';
	}
}
add_action( 'customify/content/before', 'customify_search_wc_heading' );

if ( ! function_exists( 'customify_search_wc_hide_shop_title' ) ) {
	/**
	 * Suppress the generic shop title on a product scoped search page.
	 *
	 * Sites that enabled the shop page title would otherwise get a second h1
	 * reading "Shop" next to the injected "Search Results for: ..." heading.
	 *
	 * @param bool $show Whether to show the shop title.
	 *
	 * @return bool
	 */
	function customify_search_wc_hide_shop_title( $show ) {
		if ( customify_search_is_wc_scoped() ) {
			return false;
		}

		return $show;
	}
}
add_filter( 'customify_is_shop_title_display', 'customify_search_wc_hide_shop_title' );

if ( ! function_exists( 'customify_search_get_item_scope' ) ) {
	/**
	 * Resolve the configured search scope of a header search item.
	 *
	 * @param string $item_id Builder item id - `search_box` or `search_icon`.
	 *
	 * @return string Valid post type slug, empty string for "everything".
	 */
	function customify_search_get_item_scope( $item_id ) {
		$scope = Customify()->get_setting( $item_id . '_search_scope' );
		$scope = is_string( $scope ) ? sanitize_key( $scope ) : '';

		if ( '' === $scope && 'search_box' === $item_id ) {
			// Legacy Customify Pro WC Booster option, superseded by the search
			// scope setting. Kept as a read only fallback so existing sites
			// keep their product only header search.
			if ( Customify()->get_setting( 'search_box_search_only_product' ) ) {
				$scope = 'product';
			}
		}

		if ( '' === $scope ) {
			return '';
		}

		$types = customify_search_get_result_types();

		return isset( $types[ $scope ] ) ? $scope : '';
	}
}

if ( ! function_exists( 'customify_search_scope_hidden_input' ) ) {
	/**
	 * Print the scope hidden field inside a header search form.
	 *
	 * @param string $item_id Builder item id - `search_box` or `search_icon`.
	 */
	function customify_search_scope_hidden_input( $item_id ) {
		$scope = customify_search_get_item_scope( $item_id );

		if ( '' === $scope ) {
			return;
		}

		printf( '<input type="hidden" name="post_type" value="%s" />', esc_attr( $scope ) );
	}
}
