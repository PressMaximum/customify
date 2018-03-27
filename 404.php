<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package customify
 */

get_header(); ?>

    <div <?php customify_site_content_container_class(); ?>>
        <div <?php customify_site_content_grid_class(); ?>>

            <main id="main" <?php customify_main_content_class(); ?>>
                <div class="content-inner">

                    <section class="error-404 not-found">
                        <header class="page-header">
                            <h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'customify' ); ?></h1>
                        </header><!-- .page-header -->

                        <div class="page-content widget-area">
                            <p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'customify' ); ?></p>

                            <?php get_search_form(); ?>

                        </div><!-- .page-content -->
                    </section><!-- .error-404 -->

                </div><!-- #.content-inner -->
            </main><!-- #main -->

            <?php do_action( 'customify_sidebars' ); ?>

        </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->

<?php
get_footer();
