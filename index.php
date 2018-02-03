<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
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

                <?php customify_blog_posts(); ?>

            </div><!-- #.content-inner -->
        </main><!-- #main -->

	    <?php do_action( 'customify_sidebars' ); ?>

    </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->

<?php
get_footer();