<?php
/**
 * Term picker - runtime half of the `term_picker` Customizer control.
 *
 * The control itself (inc/customizer/controls/class-control-term-picker.php)
 * only loads inside the Customizer. Everything that must also exist outside
 * it lives here: the AJAX endpoint the picker searches through (admin-ajax
 * requests never run `customize_register`), the stored value sanitizer and
 * the label helper shared by both.
 *
 * Why AJAX instead of `choices`
 * -----------------------------
 * A shop can have hundreds or thousands of product categories. Printing all
 * of them into the Customizer's JSON would bloat every Customizer load, so
 * the picker only ships the labels of the terms that are already selected
 * and pages through the rest on demand, 20 at a time, with a search box.
 *
 * Stored value: a list of term IDs (IDs survive slug renames).
 *
 * @package customify
 * @since   0.4.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'customify_term_picker_sanitize_ids' ) ) {
	/**
	 * Normalise a stored / posted term picker value to a list of term IDs.
	 *
	 * Accepts every shape the value can arrive in: the Customizer posts the
	 * framework's URL-encoded JSON string, theme_mods hold a PHP array, and a
	 * comma separated string is accepted for hand-written filters.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return int[] Unique, positive term IDs in their original order.
	 */
	function customify_term_picker_sanitize_ids( $value ) {
		if ( is_string( $value ) ) {
			$decoded = json_decode( urldecode( $value ), true );

			if ( is_array( $decoded ) ) {
				$value = $decoded;
			} elseif ( is_numeric( $decoded ) ) {
				$value = array( $decoded );
			} else {
				$value = explode( ',', $value );
			}
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$ids = array();

		foreach ( $value as $id ) {
			if ( ! is_scalar( $id ) ) {
				continue;
			}

			$id = absint( $id );

			if ( $id > 0 && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}

		// A header dropdown with more entries than this is unusable anyway;
		// the cap only keeps a tampered request from storing a huge list.
		return array_slice( $ids, 0, 200 );
	}
}

if ( ! function_exists( 'customify_term_picker_sanitize_setting' ) ) {
	/**
	 * Customizer `sanitize_callback` for term picker settings.
	 *
	 * @param mixed $value Posted value.
	 *
	 * @return int[]
	 */
	function customify_term_picker_sanitize_setting( $value ) {
		return customify_term_picker_sanitize_ids( wp_unslash( $value ) );
	}
}

if ( ! function_exists( 'customify_term_picker_term_label' ) ) {
	/**
	 * Plain text label of a term for the picker: ancestor path + count.
	 *
	 * @param WP_Term $term Term object.
	 *
	 * @return string E.g. "Clothing › Jackets (12)". Entities decoded - the
	 *                picker escapes on output.
	 */
	function customify_term_picker_term_label( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return '';
		}

		$names = array();

		foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $term->taxonomy );

			if ( $ancestor instanceof WP_Term ) {
				$names[] = $ancestor->name;
			}
		}

		$names[] = $term->name;

		$label = sprintf(
			/* translators: 1: term name, including its parent terms, 2: number of items in the term. */
			__( '%1$s (%2$s)', 'customify' ),
			implode( ' › ', $names ),
			number_format_i18n( (int) $term->count )
		);

		return html_entity_decode( $label, ENT_QUOTES, get_bloginfo( 'charset' ) );
	}
}

if ( ! function_exists( 'customify_term_picker_get_choices' ) ) {
	/**
	 * Labels for already selected term IDs, in the stored order.
	 *
	 * @param int[]  $ids      Term IDs.
	 * @param string $taxonomy Taxonomy the IDs must belong to.
	 *
	 * @return array[] List of `array( 'id' => int, 'text' => string )`.
	 */
	function customify_term_picker_get_choices( $ids, $taxonomy ) {
		$ids = customify_term_picker_sanitize_ids( $ids );

		if ( empty( $ids ) || ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'               => $taxonomy,
				'include'                => $ids,
				'orderby'                => 'include',
				'hide_empty'             => false,
				'update_term_meta_cache' => false,
			)
		);

		if ( ! is_array( $terms ) ) {
			return array();
		}

		$choices = array();

		foreach ( $terms as $term ) {
			$choices[] = array(
				'id'   => (int) $term->term_id,
				'text' => customify_term_picker_term_label( $term ),
			);
		}

		return $choices;
	}
}

if ( ! function_exists( 'customify_term_picker_ajax_search' ) ) {
	/**
	 * AJAX: one page of terms for the term picker control.
	 *
	 * Request: `nonce`, `taxonomy`, optional `q` (search) and `page` (1 based).
	 * Response: Select2's `{ results: [ { id, text } ], pagination: { more } }`.
	 */
	function customify_term_picker_ajax_search() {
		check_ajax_referer( 'customify_term_picker', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'Forbidden', 403 );
		}

		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_key( wp_unslash( $_REQUEST['taxonomy'] ) ) : '';
		$search   = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
		$page     = isset( $_REQUEST['page'] ) ? max( 1, absint( $_REQUEST['page'] ) ) : 1;
		$object   = get_taxonomy( $taxonomy );

		// Only public taxonomies: these are the ones whose terms already show
		// up on the front end, so listing them discloses nothing new.
		if ( ! $object || empty( $object->public ) ) {
			wp_send_json_error( 'Invalid taxonomy', 400 );
		}

		$per_page = 20;
		$args     = array(
			'taxonomy'               => $taxonomy,
			'hide_empty'             => false,
			'number'                 => $per_page + 1, // One extra row tells whether another page exists.
			'offset'                 => ( $page - 1 ) * $per_page,
			'orderby'                => 'name',
			'order'                  => 'ASC',
			'update_term_meta_cache' => false,
		);

		if ( '' !== $search ) {
			$args['search'] = $search;
		}

		$terms = get_terms( $args );

		if ( ! is_array( $terms ) ) {
			wp_send_json_error( 'Query failed', 500 );
		}

		$more    = count( $terms ) > $per_page;
		$results = array();

		foreach ( array_slice( $terms, 0, $per_page ) as $term ) {
			$results[] = array(
				'id'   => (int) $term->term_id,
				'text' => customify_term_picker_term_label( $term ),
			);
		}

		wp_send_json_success(
			array(
				'results'    => $results,
				'pagination' => array( 'more' => $more ),
			)
		);
	}
}
add_action( 'wp_ajax_customify_term_picker_search', 'customify_term_picker_ajax_search' );
