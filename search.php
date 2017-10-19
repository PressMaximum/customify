<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package _beacon
 */

get_header(); ?>

    <div <?php _beacon_site_content_container_class(); ?>>
        <div <?php _beacon_site_content_grid_class(); ?>>

            <main id="main" <?php _beacon_main_content_class(); ?>>
                <div class="content-inner">

                <?php
                if ( have_posts() ) : ?>

                    <header class="page-header">
                        <h1 class="page-title"><?php
                            /* translators: %s: search query. */
                            printf( esc_html__( 'Search Results for: %s', '_beacon' ), '<span>' . get_search_query() . '</span>' );
                        ?></h1>
                    </header><!-- .page-header -->

                    <?php
                    /* Start the Loop */
                    while ( have_posts() ) : the_post();

                        /**
                         * Run the loop for the search to output the results.
                         * If you want to overload this in a child theme then include a file
                         * called content-search.php and that will be used instead.
                         */
                        get_template_part( 'template-parts/content', 'search' );

                    endwhile;

                    the_posts_navigation();

                else :

                    get_template_part( 'template-parts/content', 'none' );

                endif; ?>

                </div><!-- #.content-inner -->
            </main><!-- #main -->

            <?php do_action( '_beacon_sidebars' ); ?>

        </div><!-- #._beacon-grid -->
    </div><!-- #._beacon-container -->

<?php
get_footer();