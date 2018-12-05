<?php
/**
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}
?>
<nav class="woocommerce-pagination">
	<?php

	the_posts_pagination(
		array(
			'format'       => $format,
			'base'         => $base,
			'add_args'     => false,
			'total'        => $total,
			'current'      => max( 1, $current ),
			'end_size'     => 3,
			'mid_size'     => 3,
			'prev_text' => _x( 'Previous', 'previous set of posts', 'customify' ),
			'next_text' => _x( 'Next', 'next set of posts', 'customify' ),
		)
	);
	?>
</nav>
