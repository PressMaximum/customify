<?php
/**
 * The secondary sidebar for 3 columns layout.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>

<aside id="sidebar-secondary" <?php customify_sidebar_secondary_class(); ?>>
    <div class="sidebar-secondary-inner sidebar-inner widget-area">
        <?php dynamic_sidebar( 'sidebar-2' ); ?>
    </div>
</aside><!-- #sidebar-secondary -->
