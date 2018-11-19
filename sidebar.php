<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

$sidebar_id = apply_filters( 'customify/sidebar-id', 'sidebar-1', 'primary' );
if ( ! is_active_sidebar( $sidebar_id ) ) {
	return;
}
?>
<aside id="sidebar-primary" <?php customify_sidebar_primary_class(); ?>>
	<div class="sidebar-primary-inner sidebar-inner widget-area">
		<?php
		do_action( 'customify/sidebar-primary/before' );
		dynamic_sidebar( $sidebar_id );
		do_action( 'customify/sidebar-primary/after' );
		?>
	</div>
</aside><!-- #sidebar-primary -->
