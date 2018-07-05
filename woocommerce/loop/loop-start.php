<?php
/**
 * Product Loop Start
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'customify_wc_loop_start' );

$columns = wc_get_loop_prop( 'columns', 3 );
$tablet = wc_get_loop_prop( 'tablet_columns' , 2 );
if ( $tablet > $columns ) {
	$tablet = $columns;
}
$mobile = wc_get_loop_prop( 'mobile_columns' , 1 );
if ( $mobile > $tablet ) {
	$tablet = $mobile;
}

$class = sprintf( "customify-grid-{$columns}_md-{$tablet}_sm-{$mobile}" );

?>
<ul class="products <?php echo esc_attr( $class ); ?>">