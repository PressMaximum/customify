<?php
/**
 * WC_Product_Cat_List_Walker class
 *
 * @package WooCommerce/Classes/Walkers
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * Product cat list walker class.
 *
 * @see WC_Product_Cat_List_Walker
 */
class Customify_WC_Product_Cat_List_Walker extends WC_Product_Cat_List_Walker {

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string  $output            Passed by reference. Used to append additional content.
	 * @param object  $cat               Category.
	 * @param int     $depth             Depth of category in reference to parents.
	 * @param array   $args              Arguments.
	 * @param integer $current_object_id Current object ID.
	 */
	public function start_el( &$output, $cat, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$cat_id = intval( $cat->term_id );

		$output .= '<li class="cat-item cat-item-' . $cat_id;

		if ( $args['current_category'] === $cat_id ) {
			$output .= ' current-cat';
		}

		if ( $args['has_children'] && $args['hierarchical'] && ( empty( $args['max_depth'] ) || $args['max_depth'] > $depth + 1 ) ) {
			$output .= ' cat-parent';
		}

		if ( $args['current_category_ancestors'] && $args['current_category'] && in_array( $cat_id, $args['current_category_ancestors'], true ) ) {
			$output .= ' current-cat-parent';
		}

		$output .= '"><a href="' . get_term_link( $cat_id, $this->tree_type ) . '">' . apply_filters( 'list_product_cats', $cat->name, $cat ) . '</a>';

		if ( $args['show_count'] ) {
			$output .= ' <span class="count">' . $cat->count . '</span>';
		}
	}

}


