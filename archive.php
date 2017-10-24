<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
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
                        <?php
                            the_archive_title( '<h1 class="page-title">', '</h1>' );
                            the_archive_description( '<div class="archive-description">', '</div>' );
                        ?>
                    </header><!-- .page-header -->

                    <?php
                    /* Start the Loop */
                    while ( have_posts() ) : the_post();

                        /*
                         * Include the Post-Format-specific template for the content.
                         * If you want to override this in a child theme, then include a file
                         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                         */
                        get_template_part( 'template-parts/content', get_post_format() );

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
