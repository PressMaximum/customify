<?php
/**
 * The secondary sidebar for 3 columns layout.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

$sidebar_id = apply_filters( 'customify/sidebar-id', 'sidebar-2', 'secondary' );
if ( ! is_active_sidebar( $sidebar_id ) ) {
	return;
}
?>
<aside id="sidebar-secondary" <?php customify_sidebar_secondary_class(); ?>>
	<div class="sidebar-secondary-inner sidebar-inner widget-area">
		<?php
		do_action( 'customify/sidebar-secondary/before' );
		dynamic_sidebar( $sidebar_id );
		do_action( 'customify/sidebar-secondary/after' );
		?>
	</div>
</aside><!-- #sidebar-secondary -->
