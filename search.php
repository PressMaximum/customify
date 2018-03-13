<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package customify
 */

get_header(); ?>

    <div <?php customify_site_content_container_class(); ?>>
        <div <?php customify_site_content_grid_class(); ?>>

            <main id="main" <?php customify_main_content_class(); ?>>
                <div class="content-inner">
                    <?php
                    if ( have_posts() ){
                        while ( have_posts() ) {
                            the_post();
                            get_template_part( 'template-parts/content', 'search' );
                        }
                    } else {
                        get_template_part( 'template-parts/content', 'none' );
                    }
                    ?>
                </div><!-- #.content-inner -->
            </main><!-- #main -->

            <?php do_action( 'customify_sidebars' ); ?>

        </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->

<?php
get_footer();