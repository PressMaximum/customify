<?php
/**
 * Single Product title
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author     WooThemes
 * @package    WooCommerce/Templates
 * @version    4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo '<div class="product_title-wrapper">';
the_title( '<h1 class="product_title entry-title">', '</h1>' );
do_action( 'wc_after_single_product_title' );
echo '</div>';
