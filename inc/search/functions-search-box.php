<?php
/**
 * Header Search Box item: opt-in category dropdown.
 *
 * The feature is OFF by default and every function below returns early (or
 * hands its input back untouched) while it is, so a site that never enables
 * it renders the Search Box byte for byte as before.
 *
 * Category dropdown (`search_box_cat_filter`)
 * -------------------------------------------
 * Prints a native `<select>` of taxonomy terms before the search input. The
 * select is named after the taxonomy's public query var and its values are
 * term slugs, so the form submits a plain core query -
 * `?s=term&post_type=product&product_cat=jackets` - that WordPress,
 * WooCommerce and every template resolver already understand. The first
 * option has an empty value and means "no filter".
 *
 * - The taxonomy is picked by the site owner (`search_box_cat_taxonomy`),
 *   never derived from the search scope. When the item has a scope and the
 *   taxonomy is not registered for that post type the dropdown is skipped,
 *   because every choice would return nothing.
 * - Terms (`search_box_cat_source`): "selected" lists the terms picked in the
 *   term picker, in the picked order, and falls back to the top level terms
 *   while nothing is picked; "top" lists the top level terms; "all" lists
 *   every term, indented by depth. Top / all are capped (100 by default, see
 *   the `customify/builder_item/search-box/cat_max_terms` filter) so a
 *   taxonomy with thousands of terms cannot blow up the header.
 * - Term IDs are stored (stable across slug renames); slugs are printed.
 * - Item HTML is rendered once and reused in the desktop header, mobile
 *   header and off-canvas panel, so nothing here prints an `id`: the select
 *   is labelled by wrapping it in its `<label>`.
 *
 * Rotating placeholder (`search_box_placeholder` containing `|`)
 * -------------------------------------------------------------
 * No setting of its own: a placeholder such as
 * `Search sofas|Search rugs|Search chairs` is split into phrases. The input's
 * real `placeholder` attribute keeps the first phrase (no-JS fallback, screen
 * readers) and an aria-hidden overlay, positioned by a small inline script,
 * rotates the phrases. Leading words every phrase shares ("Search") stay in
 * place and only the rest slides. A placeholder without `|` takes none of
 * these paths, so its markup is unchanged.
 *
 * Customify Pro's WooCommerce Booster module ships an older product category
 * dropdown (`search_box_show_cats`). Pro checks
 * customify_search_box_cat_filter_enabled() and stays out of the form while
 * this dropdown is on, so the two never stack.
 *
 * Look: the dropdown sits inside `.search-form-fields` and inherits the box
 * drawn by the item's existing Customizer fields (height, Input Styling,
 * Input Focus Styling). Its own stylesheet
 * (src/frontend/scss/header/builder_items/_search-box.scss) is structural
 * only; colours and type come from `inherit` / `currentColor` or from the
 * optional Dropdown Text Color / Typography / Divider Color fields.
 *
 * @package customify
 * @since   0.4.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'customify_search_box_cat_filter_enabled' ) ) {
	/**
	 * Is the Search Box category dropdown switched on?
	 *
	 * Also the feature flag Customify Pro reads to keep its legacy dropdown
	 * out of the form.
	 *
	 * @since 0.4.26
	 *
	 * @return bool
	 */
	function customify_search_box_cat_filter_enabled() {
		return (bool) Customify()->get_setting( 'search_box_cat_filter' );
	}
}

if ( ! function_exists( 'customify_search_box_get_cat_taxonomies' ) ) {
	/**
	 * Taxonomies the category dropdown can filter by.
	 *
	 * Public taxonomies with a query var (the select submits through it) that
	 * are attached to at least one searchable post type. Post formats and
	 * WooCommerce shipping classes are left out - they are not a way anybody
	 * browses a catalogue.
	 *
	 * @since 0.4.26
	 *
	 * @return WP_Taxonomy[] Keyed by taxonomy slug.
	 */
	function customify_search_box_get_cat_taxonomies() {
		$searchable = function_exists( 'customify_search_get_result_types' ) ? array_keys( customify_search_get_result_types() ) : array();
		$list       = array();

		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $slug => $taxonomy ) {
			if ( in_array( $slug, array( 'post_format', 'product_shipping_class' ), true ) || empty( $taxonomy->query_var ) || ! is_string( $taxonomy->query_var ) ) {
				continue;
			}

			if ( ! array_intersect( (array) $taxonomy->object_type, $searchable ) ) {
				continue;
			}

			$list[ $slug ] = $taxonomy;
		}

		/**
		 * Filter the taxonomies offered by the Search Box category dropdown.
		 *
		 * @since 0.4.26
		 *
		 * @param WP_Taxonomy[] $list Taxonomy objects keyed by slug.
		 */
		$list = apply_filters( 'customify/builder_item/search-box/cat_taxonomies', $list );

		return is_array( $list ) ? $list : array();
	}
}

if ( ! function_exists( 'customify_search_box_get_cat_taxonomy_choices' ) ) {
	/**
	 * Select choices for the "Taxonomy" setting.
	 *
	 * @since 0.4.26
	 *
	 * @return array Slug => "Product categories (Products)" style label.
	 */
	function customify_search_box_get_cat_taxonomy_choices() {
		$choices = array();

		foreach ( customify_search_box_get_cat_taxonomies() as $slug => $taxonomy ) {
			$types = array();

			foreach ( (array) $taxonomy->object_type as $post_type ) {
				$object = get_post_type_object( $post_type );

				if ( $object ) {
					$types[] = $object->labels->name;
				}
			}

			$choices[ $slug ] = $types
				/* translators: 1: taxonomy name, 2: comma separated post type names. */
				? sprintf( __( '%1$s (%2$s)', 'customify' ), $taxonomy->labels->name, implode( ', ', $types ) )
				: $taxonomy->labels->name;
		}

		return $choices;
	}
}

if ( ! function_exists( 'customify_search_box_get_default_cat_taxonomy' ) ) {
	/**
	 * Default taxonomy of the category dropdown.
	 *
	 * @since 0.4.26
	 *
	 * @return string `product_cat` on WooCommerce sites, `category` otherwise.
	 */
	function customify_search_box_get_default_cat_taxonomy() {
		return Customify()->is_woocommerce_active() ? 'product_cat' : 'category';
	}
}

if ( ! function_exists( 'customify_search_box_get_cat_taxonomy' ) ) {
	/**
	 * Resolve the configured, still valid taxonomy of the dropdown.
	 *
	 * @since 0.4.26
	 *
	 * @return string Taxonomy slug, empty string when none is usable.
	 */
	function customify_search_box_get_cat_taxonomy() {
		$taxonomies = customify_search_box_get_cat_taxonomies();
		$taxonomy   = Customify()->get_setting( 'search_box_cat_taxonomy' );
		$taxonomy   = is_string( $taxonomy ) ? sanitize_key( $taxonomy ) : '';

		if ( isset( $taxonomies[ $taxonomy ] ) ) {
			return $taxonomy;
		}

		// The saved taxonomy may belong to a deactivated plugin.
		$default = customify_search_box_get_default_cat_taxonomy();

		return isset( $taxonomies[ $default ] ) ? $default : '';
	}
}

if ( ! function_exists( 'customify_search_box_get_cat_terms' ) ) {
	/**
	 * Terms the dropdown lists, each with its display depth.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @since 0.4.26
	 *
	 * @return array[] List of `array( 'term' => WP_Term, 'depth' => int )`.
	 */
	function customify_search_box_get_cat_terms( $taxonomy ) {
		$source     = Customify()->get_setting( 'search_box_cat_source' );
		$source     = in_array( $source, array( 'selected', 'top', 'all' ), true ) ? $source : 'selected';
		$hide_empty = (bool) Customify()->get_setting( 'search_box_cat_hide_empty' );

		/**
		 * Filter the maximum number of terms the "top level" and "all" modes
		 * list, including hand-picked terms.
		 *
		 * @since 0.4.26
		 *
		 * @param int    $max      Maximum number of options. Default 100.
		 * @param string $taxonomy Taxonomy slug.
		 */
		$max = (int) apply_filters( 'customify/builder_item/search-box/cat_max_terms', 100, $taxonomy );
		$max = $max > 0 ? $max : 100;

		$base = array(
			'taxonomy'               => $taxonomy,
			'hide_empty'             => $hide_empty,
			'update_term_meta_cache' => false,
		);

		if ( 'selected' === $source ) {
			$ids = customify_term_picker_sanitize_ids( Customify()->get_setting( 'search_box_cat_terms' ) );
			$ids = array_slice( $ids, 0, $max );

			if ( $ids ) {
				$terms = get_terms(
					array_merge(
						$base,
						array(
							'include' => $ids,
							'orderby' => 'include',
						)
					)
				);

				$list = array();

				foreach ( is_array( $terms ) ? $terms : array() as $term ) {
					$list[] = array(
						'term'  => $term,
						'depth' => 0,
					);
				}

				if ( $list ) {
					return $list;
				}
			}

			// Nothing picked yet (or every pick is gone / empty): top level.
			$source = 'top';
		}

		if ( 'top' === $source ) {
			// No `orderby`: WooCommerce then applies the shop's own category
			// order to product taxonomies; everything else sorts by name.
			$terms = get_terms(
				array_merge(
					$base,
					array(
						'parent' => 0,
						'number' => $max,
					)
				)
			);

			$list = array();

			foreach ( is_array( $terms ) ? $terms : array() as $term ) {
				$list[] = array(
					'term'  => $term,
					'depth' => 0,
				);
			}

			return $list;
		}

		// "all": fetch no more than the output cap, then walk the available
		// portion of the tree depth first so children follow their parent.
		$terms = get_terms(
			array_merge(
				$base,
				array(
					'hierarchical' => false,
					'number'       => $max,
				)
			)
		);

		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return array();
		}

		$by_id    = array();
		$children = array();

		foreach ( $terms as $term ) {
			$by_id[ $term->term_id ] = $term;
		}

		foreach ( $terms as $term ) {
			// A term whose parent is hidden (empty) is promoted to the root
			// instead of disappearing with it.
			$parent                = isset( $by_id[ $term->parent ] ) ? (int) $term->parent : 0;
			$children[ $parent ][] = $term;
		}

		$list  = array();
		$stack = array();

		foreach ( array_reverse( isset( $children[0] ) ? $children[0] : array() ) as $term ) {
			$stack[] = array( $term, 0 );
		}

		while ( $stack && count( $list ) < $max ) {
			list( $term, $depth ) = array_pop( $stack );

			$list[] = array(
				'term'  => $term,
				'depth' => $depth,
			);

			if ( ! empty( $children[ $term->term_id ] ) ) {
				foreach ( array_reverse( $children[ $term->term_id ] ) as $child ) {
					$stack[] = array( $child, $depth + 1 );
				}
			}
		}

		return $list;
	}
}

if ( ! function_exists( 'customify_search_box_get_cat_dropdown' ) ) {
	/**
	 * Build the category dropdown markup of a search item.
	 *
	 * @param string $item_id Builder item id.
	 *
	 * @since 0.4.26
	 *
	 * @return string HTML, empty string when the dropdown should not render.
	 */
	function customify_search_box_get_cat_dropdown( $item_id = 'search_box' ) {
		static $cache = array();

		if ( 'search_box' !== $item_id || ! customify_search_box_cat_filter_enabled() ) {
			return '';
		}

		// Built twice per render (form classes + markup): query terms once.
		if ( isset( $cache[ $item_id ] ) && ! is_customize_preview() ) {
			return $cache[ $item_id ];
		}

		$cache[ $item_id ] = customify_search_box_build_cat_dropdown( $item_id );

		return $cache[ $item_id ];
	}
}

if ( ! function_exists( 'customify_search_box_build_cat_dropdown' ) ) {
	/**
	 * Uncached worker of customify_search_box_get_cat_dropdown().
	 *
	 * @param string $item_id Builder item id.
	 *
	 * @since 0.4.26
	 *
	 * @return string HTML, empty string when the dropdown should not render.
	 */
	function customify_search_box_build_cat_dropdown( $item_id ) {

		$taxonomy = customify_search_box_get_cat_taxonomy();
		$object   = $taxonomy ? get_taxonomy( $taxonomy ) : false;

		if ( ! $object ) {
			return '';
		}

		// A scoped form only ever searches its own post type - a taxonomy
		// that type does not use would turn every option into "no results".
		$scope = function_exists( 'customify_search_get_item_scope' ) ? customify_search_get_item_scope( $item_id ) : '';

		if ( '' !== $scope && ! in_array( $scope, (array) $object->object_type, true ) ) {
			return '';
		}

		$items = customify_search_box_get_cat_terms( $taxonomy );

		if ( empty( $items ) ) {
			return '';
		}

		$query_var = $object->query_var;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only query var on a public search request.
		$current = isset( $_GET[ $query_var ] ) && is_string( $_GET[ $query_var ] ) ? sanitize_text_field( wp_unslash( $_GET[ $query_var ] ) ) : '';

		$all_text = Customify()->get_setting( 'search_box_cat_all_text' );
		$all_text = is_string( $all_text ) && '' !== trim( $all_text ) ? $all_text : __( 'All categories', 'customify' );

		$options = sprintf( '<option value="">%s</option>', esc_html( $all_text ) );

		foreach ( $items as $item ) {
			$term = $item['term'];

			$options .= sprintf(
				'<option class="level-%1$d" value="%2$s"%3$s>%4$s%5$s</option>',
				(int) $item['depth'],
				esc_attr( $term->slug ),
				selected( $current, $term->slug, false ),
				str_repeat( '&nbsp;&nbsp;&nbsp;', (int) $item['depth'] ),
				esc_html( $term->name )
			);
		}

		$chevron = '<svg class="cfy-search-box__cat-icon" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

		$html = sprintf(
			'<label class="cfy-search-box__cat"><span class="screen-reader-text">%1$s</span><select class="cfy-search-box__cat-select" name="%2$s">%3$s</select>%4$s</label>',
			esc_html__( 'Search in', 'customify' ),
			esc_attr( $query_var ),
			$options,
			$chevron
		);

		/**
		 * Filter the Search Box category dropdown markup.
		 *
		 * @since 0.4.26
		 *
		 * @param string $html     Dropdown HTML.
		 * @param string $taxonomy Taxonomy slug.
		 * @param array  $items    Listed terms with their depth.
		 */
		return (string) apply_filters( 'customify/builder_item/search-box/cat_dropdown', $html, $taxonomy, $items );
	}
}

if ( ! function_exists( 'customify_search_box_cat_dropdown' ) ) {
	/**
	 * Print the category dropdown of a search item (see the getter above).
	 *
	 * @param string $item_id Builder item id.
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_cat_dropdown( $item_id = 'search_box' ) {
		$html = customify_search_box_get_cat_dropdown( $item_id );

		if ( '' === $html ) {
			return;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every part is escaped while it is built.

		customify_search_box_enqueue_submit_script();
	}
}

if ( ! function_exists( 'customify_search_box_form_classes' ) ) {
	/**
	 * Add the opt-in modifier classes to a Search Box form.
	 *
	 * @param string[] $classes Classes from the `form_extra_class` filter.
	 * @param string   $item_id Builder item id.
	 *
	 * @since 0.4.26
	 *
	 * @return string[] Untouched while the category dropdown is off.
	 */
	function customify_search_box_form_classes( $classes, $item_id = 'search_box' ) {
		if ( 'search_box' !== $item_id ) {
			return $classes;
		}

		$classes = is_array( $classes ) ? $classes : array();
		$extra   = array();

		$placeholder = customify_search_box_parse_placeholder( Customify()->get_setting( 'search_box_placeholder' ) );

		if ( count( $placeholder['phrases'] ) > 1 ) {
			$extra[] = 'cfy-search-box--ph-rotate';
		}

		if ( '' !== customify_search_box_get_cat_dropdown( $item_id ) ) {
			$extra[] = 'cfy-search-box--has-cat';

			if ( Customify()->get_setting( 'search_box_cat_hide_mobile' ) ) {
				$extra[] = 'cfy-search-box--cat-hide-mobile';
			}

			if ( ! Customify()->get_setting( 'search_box_cat_divider' ) ) {
				$extra[] = 'cfy-search-box--cat-no-divider';
			}
		}

		if ( empty( $extra ) ) {
			return $classes;
		}

		array_unshift( $extra, 'cfy-search-box' );

		return array_merge( $classes, $extra );
	}
}

if ( ! function_exists( 'customify_search_box_enqueue_submit_script' ) ) {
	/**
	 * Tidy the submitted query of a form with a category dropdown.
	 *
	 * Disables the select right before submit when it is hidden (the "hide on
	 * mobile" option would otherwise still send its preselected term) or left
	 * on "All categories" (keeps an empty `product_cat=` out of the URL).
	 * Re-enabled on `pageshow` so a back navigation finds it usable.
	 *
	 * Attached to the footer theme script, and only once a dropdown was
	 * printed. The Customizer preview attaches it up front while the opt-in
	 * switch is enabled, so a later partial refresh can safely replace the form.
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_enqueue_submit_script() {
		static $done = false;

		if ( $done || ! wp_script_is( 'customify-themejs', 'enqueued' ) ) {
			return;
		}

		$done = true;

		wp_add_inline_script(
			'customify-themejs',
			'(function(){var s=".cfy-search-box__cat-select";document.addEventListener("submit",function(e){var f=e.target;if(!f||!f.querySelectorAll){return;}var l=f.querySelectorAll(s);for(var i=0;i<l.length;i++){if(!l[i].value||!l[i].getClientRects().length){l[i].disabled=true;(function(c){window.setTimeout(function(){c.disabled=false;},0);})(l[i]);}}},true);window.addEventListener("pageshow",function(){var l=document.querySelectorAll(s);for(var i=0;i<l.length;i++){l[i].disabled=false;}});})();'
		);
	}
}

if ( ! function_exists( 'customify_search_box_preview_submit_script' ) ) {
	/**
	 * Load the submit helper up front in the Customizer preview, where later
	 * dropdown setting changes use partial refreshes that never reach the
	 * footer scripts.
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_preview_submit_script() {
		if ( is_customize_preview() && customify_search_box_cat_filter_enabled() ) {
			customify_search_box_enqueue_submit_script();
		}
	}
}
add_action( 'wp_enqueue_scripts', 'customify_search_box_preview_submit_script', 100 );

if ( ! function_exists( 'customify_search_box_parse_placeholder' ) ) {
	/**
	 * Split a Search Box placeholder into rotating phrases.
	 *
	 * Only a placeholder that contains `|` is touched: it is cut into trimmed,
	 * non-empty phrases, the first of which becomes the input's real
	 * placeholder. The leading words shared by every phrase (whitespace
	 * separated, compared case-sensitively) become a static prefix so only
	 * the rest rotates; when removing them would leave a phrase empty, or
	 * nothing is shared, there is no prefix and whole phrases rotate.
	 * Full phrases are stored on purpose - word order differs by language.
	 *
	 * @param string $placeholder Placeholder setting value.
	 *
	 * @since 0.4.26
	 *
	 * @return array {
	 *     @type string   $placeholder Value for the `placeholder` attribute.
	 *     @type string[] $phrases     Full phrases, empty unless the value has `|`.
	 *     @type string   $prefix      Static leading words, may be empty.
	 *     @type string[] $words       Rotating part of each phrase.
	 * }
	 */
	function customify_search_box_parse_placeholder( $placeholder ) {
		$placeholder = sanitize_text_field( is_string( $placeholder ) ? $placeholder : '' );
		$result      = array(
			'placeholder' => $placeholder,
			'phrases'     => array(),
			'prefix'      => '',
			'words'       => array(),
		);

		if ( false === strpos( $placeholder, '|' ) ) {
			return $result;
		}

		$phrases = array();

		foreach ( explode( '|', $placeholder ) as $segment ) {
			$segment = trim( $segment );

			if ( '' !== $segment ) {
				$phrases[] = $segment;
			}
		}

		$result['placeholder'] = $phrases ? $phrases[0] : '';
		$result['phrases']     = $phrases;
		$result['words']       = $phrases;

		if ( count( $phrases ) < 2 ) {
			return $result;
		}

		// Longest shared run of leading words.
		$split = array();

		foreach ( $phrases as $phrase ) {
			$split[] = preg_split( '/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY );
		}

		$shared = 0;

		while ( true ) {
			foreach ( $split as $words ) {
				if ( ! isset( $words[ $shared ], $split[0][ $shared ] ) || $words[ $shared ] !== $split[0][ $shared ] ) {
					break 2;
				}
			}

			++$shared;
		}

		foreach ( $split as $words ) {
			// A phrase made of the shared words only would rotate to nothing.
			if ( count( $words ) <= $shared ) {
				$shared = 0;
				break;
			}
		}

		if ( $shared > 0 ) {
			$result['prefix'] = implode( ' ', array_slice( $split[0], 0, $shared ) );
			$result['words']  = array();

			foreach ( $split as $words ) {
				$result['words'][] = implode( ' ', array_slice( $words, $shared ) );
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'customify_search_box_rotating_placeholder' ) ) {
	/**
	 * Print the rotating placeholder overlay of a search item.
	 *
	 * Prints nothing unless the placeholder holds at least two phrases. The
	 * overlay is decorative (aria-hidden) and stays hidden until the inline
	 * script positions it, so without JavaScript the native placeholder (the
	 * first phrase) shows. No ids: the item HTML is reused in the desktop
	 * header, mobile header and off-canvas panel.
	 *
	 * @param array $parsed Result of customify_search_box_parse_placeholder().
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_rotating_placeholder( $parsed ) {
		if ( ! is_array( $parsed ) || empty( $parsed['words'] ) || count( $parsed['words'] ) < 2 ) {
			return;
		}

		$words = '';

		foreach ( $parsed['words'] as $word ) {
			$words .= '<span class="cfy-search-box__ph-word">' . esc_html( $word ) . '</span>';
		}

		$static = '' !== $parsed['prefix'] ? '<span class="cfy-search-box__ph-static">' . esc_html( $parsed['prefix'] ) . '</span> ' : '';

		echo '<span class="cfy-search-box__ph" aria-hidden="true"><span class="cfy-search-box__ph-line">' . $static . '<span class="cfy-search-box__ph-words">' . $words . '</span></span></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every phrase is escaped above.

		customify_search_box_enqueue_placeholder_script();
	}
}

if ( ! function_exists( 'customify_search_box_enqueue_placeholder_script' ) ) {
	/**
	 * Drive the rotating placeholder overlays.
	 *
	 * Per overlay: copies the input's placeholder look (colour, opacity, font)
	 * and places the overlay over the input's content box, hides the native
	 * placeholder, hides the overlay while the input has focus or a value,
	 * and slides to the next phrase every 2.6s. One shared timer drives every
	 * overlay and stops while the tab is hidden; under
	 * `prefers-reduced-motion: reduce` the first phrase stays put. Overlays
	 * added later (Customizer partial refresh, cloned panels) are picked up
	 * on `selective-refresh-content-rendered`, DOMContentLoaded and load; a
	 * JS property guards against a second init.
	 *
	 * Attached to the footer theme script once an overlay was printed; the
	 * Customizer preview attaches it up front (see below).
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_enqueue_placeholder_script() {
		static $done = false;

		if ( $done || ! wp_script_is( 'customify-themejs', 'enqueued' ) ) {
			return;
		}

		$done = true;

		$script = <<<'JS'
(function () {
	var READY = 'cfy-search-box--ph-ready', INTERVAL = 2600, list = [], timer = 0;
	var reduce = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
	var px = function (v) { return parseFloat(v) || 0; };
	var up = function (el, tag) { while (el && el.nodeName !== tag) { el = el.parentNode; } return el; };
	var alive = function (p) { return document.documentElement.contains(p.ph); };
	var shown = function (p) { return p.input.getClientRects().length > 0; };

	function layout(p) {
		if (!shown(p)) { return; }
		var f = p.fields.getBoundingClientRect(), r = p.input.getBoundingClientRect();
		var fs = window.getComputedStyle(p.fields), is = window.getComputedStyle(p.input);
		var ps = window.getComputedStyle(p.input, '::placeholder'), s = p.ph.style;
		s.top = (r.top - f.top - px(fs.borderTopWidth) + px(is.borderTopWidth) + px(is.paddingTop)) + 'px';
		s.height = Math.max(0, p.input.clientHeight - px(is.paddingTop) - px(is.paddingBottom)) + 'px';
		s.width = Math.max(0, p.input.clientWidth - px(is.paddingLeft) - px(is.paddingRight)) + 'px';
		if ('rtl' === is.direction) {
			s.left = 'auto';
			s.right = (f.right - r.right - px(fs.borderRightWidth) + px(is.borderRightWidth) + px(is.paddingRight)) + 'px';
		} else {
			s.right = 'auto';
			s.left = (r.left - f.left - px(fs.borderLeftWidth) + px(is.borderLeftWidth) + px(is.paddingLeft)) + 'px';
		}
		s.direction = is.direction;
		s.color = ps.color || is.color;
		s.fontFamily = ps.fontFamily;
		s.fontSize = ps.fontSize;
		s.fontWeight = ps.fontWeight;
		s.fontStyle = ps.fontStyle;
		s.letterSpacing = ps.letterSpacing;
		s.wordSpacing = ps.wordSpacing;
		s.textTransform = ps.textTransform;
	}

	function sync(p) {
		p.ph.classList.toggle('is-hidden', '' !== p.input.value || document.activeElement === p.input);
	}

	function next(p) {
		var n = p.words.length, cur = p.words[p.i], nxt;
		p.i = (p.i + 1) % n;
		nxt = p.words[p.i];
		// The incoming phrase may still sit above (it left earlier): drop it
		// below without a transition, then slide it in.
		nxt.style.transition = 'none';
		nxt.classList.remove('is-leaving');
		void nxt.offsetWidth;
		nxt.style.transition = '';
		cur.classList.remove('is-active');
		cur.classList.add('is-leaving');
		nxt.classList.add('is-active');
	}

	function tick() {
		list = list.filter(alive);
		if (!list.length) { stop(); return; }
		for (var k = 0; k < list.length; k++) {
			var p = list[k];
			if (!shown(p)) { continue; }
			layout(p);
			if (!(reduce && reduce.matches) && !p.ph.classList.contains('is-hidden')) { next(p); }
		}
	}

	function stop() { if (timer) { window.clearInterval(timer); timer = 0; } }
	function start() { if (!timer && list.length && !document.hidden) { timer = window.setInterval(tick, INTERVAL); } }

	function setup(ph) {
		var fields = ph.parentNode, form = up(ph, 'FORM');
		var input = fields && fields.querySelector ? fields.querySelector('.search-field') : null;
		var words = ph.querySelectorAll('.cfy-search-box__ph-word');
		if (!input || !form || words.length < 2) { return; }
		ph.cfyPh = true;
		var p = { ph: ph, fields: fields, input: input, words: words, i: 0 };
		// Read the native placeholder's own opacity before hiding it (a
		// cloned form may already carry the ready class).
		form.classList.remove(READY);
		ph.style.setProperty('--cfy-ph-opacity', window.getComputedStyle(input, '::placeholder').opacity || 1);
		for (var k = 0; k < words.length; k++) { words[k].classList.remove('is-active', 'is-leaving'); }
		words[0].classList.add('is-active');
		form.classList.add(READY);
		var refresh = function () { sync(p); };
		input.addEventListener('focus', refresh);
		input.addEventListener('blur', refresh);
		input.addEventListener('input', refresh);
		input.addEventListener('change', refresh);
		if (window.ResizeObserver) { new window.ResizeObserver(function () { layout(p); }).observe(input); }
		layout(p);
		sync(p);
		list.push(p);
	}

	function init() {
		var all = document.querySelectorAll('.cfy-search-box__ph');
		for (var k = 0; k < all.length; k++) { if (!all[k].cfyPh) { setup(all[k]); } }
		start();
	}

	init();
	document.addEventListener('DOMContentLoaded', init);
	window.addEventListener('load', init);
	document.addEventListener('selective-refresh-content-rendered', init);
	document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });
	window.addEventListener('resize', function () { for (var k = 0; k < list.length; k++) { layout(list[k]); } });
	window.addEventListener('pageshow', function () { for (var k = 0; k < list.length; k++) { sync(list[k]); } });
})();
JS;

		wp_add_inline_script( 'customify-themejs', $script );
	}
}

if ( ! function_exists( 'customify_search_box_preview_placeholder_script' ) ) {
	/**
	 * Load the rotating placeholder script up front in the Customizer
	 * preview: a placeholder edited to hold `|` arrives through a partial
	 * refresh, which never reaches the footer scripts. The front end only
	 * gets the script once an overlay was actually printed.
	 *
	 * @since 0.4.26
	 */
	function customify_search_box_preview_placeholder_script() {
		if ( is_customize_preview() ) {
			customify_search_box_enqueue_placeholder_script();
		}
	}
}
add_action( 'wp_enqueue_scripts', 'customify_search_box_preview_placeholder_script', 100 );
