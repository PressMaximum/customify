<?php
/**
 * Product Loop Start
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'customify_wc_loop_start' );

$columns = intval( wc_get_loop_prop( 'columns', 3 ) );
$tablet = intval( wc_get_loop_prop( 'tablet_columns', 2 ) );
$mobile = intval( wc_get_loop_prop( 'mobile_columns', 1 ) );

if ( $tablet > $columns && $columns > 1 ) {
	$tablet = $columns;
}

if ( ! $tablet ) {
	$tablet = $columns;
}

if ( $mobile >= $tablet && $tablet > 1 ) {
	$mobile = $tablet;
}

if ( ! $mobile ) {
	$mobile = 1;
}
if ( ! $columns ) {
	$columns = 4;
	$tablet = 2;
	$mobile = 1;
}

$view = customify_get_default_catalog_view_mod();
$class = sprintf( "customify-grid-{$columns}_md-{$columns}_sm-{$tablet}_xs-{$mobile}" );
$class .= ' wc-' . $view . '-view';

?>
<ul class="products <?php echo esc_attr( $class ); ?>">
