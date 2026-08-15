<?php
/**
 * Search results framework.
 *
 * Renders the search results page as a mixed listing: a generic content card
 * for posts / pages / CPTs and the native WooCommerce product card for
 * products, plus a content type tab bar and per type result counts.
 *
 * Swap window safety
 * ------------------
 * The listing is emitted as ONE pass over the MAIN query. Block based page
 * builders (Blocksify and friends) buffer the `loop_start` -> `loop_end`
 * window of the main query and swap the content inside it, so every piece of
 * per result markup must be printed from inside the loop iterations. Never
 * buffer the listing and print it after the loop, and never run a secondary
 * WP_Query inside the listing region. Tabs and heading render before the
 * loop, pagination after it - both are outside the swap window.
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

		return is_string( $current ) ? $current : '';
	}
}

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

		$tabs = array(
			array(
				'key'    => 'all',
				'label'  => __( 'All', 'customify' ),
				'count'  => isset( $counts['all'] ) ? (int) $counts['all'] : 0,
				'url'    => add_query_arg( 's', urlencode( $term ), home_url( '/' ) ),
				'active' => ( '' === $current ),
			),
		);

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

if ( ! function_exists( 'customify_search_results_layout' ) ) {
	/**
	 * Render the search results listing.
	 *
	 * One pass over the main query - see the swap window note at the top of
	 * this file before restructuring anything in here.
	 */
	function customify_search_results_layout() {
		if ( ! have_posts() ) {
			get_template_part( 'template-parts/content', 'none' );

			return;
		}

		$columns = customify_search_get_columns();
		$wc      = Customify()->is_woocommerce_active();
		$classes = array();
		$atts    = '';

		if ( $wc ) {
			// `products` + the view mode class let the WooCommerce and Catalog
			// Designer styles apply to the embedded product cards. A single
			// column listing uses the native list view so product cards lay out
			// media-left like the generic cards next to them.
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
		global $wp_query;

		// The <ul> opens inside the FIRST loop iteration and closes inside the
		// LAST one, so the whole list element lives inside the main query's
		// `loop_start` -> `loop_end` window. A page builder swapping that window
		// (Blocksify Dynamic Templates) then replaces the entire listing; if the
		// <ul> wrapped the loop from outside, the builder's markup would be
		// injected INSIDE a grid-styled list element and break its layout.
		$ul_open = '<ul class="' . esc_attr( join( ' ', $classes ) ) . '"' . $atts . '>';
		$opened  = false;

		while ( have_posts() ) {
			the_post();

			if ( ! $opened ) {
				echo $ul_open; // WPCS: XSS ok.
				$opened = true;
			}

			customify_search_render_card( get_post() );

			if ( (int) $wp_query->current_post + 1 >= (int) $wp_query->post_count ) {
				echo '</ul>';
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
