<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside id="sidebar-primary" <?php customify_sidebar_primary_class(); ?>>
    <div class="sidebar-primary-inner sidebar-inner widget-area">
        <?php dynamic_sidebar( 'sidebar-1' ); ?>
    </div>
</aside><!-- #sidebar-primary -->
