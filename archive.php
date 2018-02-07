<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

get_header(); ?>

<div <?php customify_site_content_container_class(); ?>>
    <div <?php customify_site_content_grid_class(); ?>>

        <main id="main" <?php customify_main_content_class(); ?>>
            <div class="content-inner">
                <?php customify_archive_posts(); ?>
            </div><!-- #.content-inner -->
        </main><!-- #main -->

	    <?php do_action( 'customify_sidebars' ); ?>

    </div><!-- #.customify-grid -->
</div><!-- #.customify-container -->

<?php
get_footer();
