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

                <?php
                if ( have_posts() ) :

                    if ( is_home() && ! is_front_page() ) : ?>
                        <header>
                            <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                        </header>

                    <?php
                    endif;

                    /* Start the Loop */
                    while ( have_posts() ) : the_post();

                        customify_the_blog_item();

                    endwhile;

                    the_posts_navigation();

                else :

                    get_template_part( 'template-parts/content', 'none' );

                endif; ?>

            </div><!-- #.content-inner -->
        </main><!-- #main -->


	    <?php do_action( 'customify_sidebars' ); ?>

    </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->

<?php
get_footer();