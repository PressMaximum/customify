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

                <?php

                $l = new Customify_Posts_Layout();
                $l->render();

                /*
                if ( have_posts() ) : ?>

                    <header class="page-header">
                        <?php
                            the_archive_title( '<h1 class="page-title">', '</h1>' );
                            the_archive_description( '<div class="archive-description">', '</div>' );
                        ?>
                    </header><!-- .page-header -->

                    <?php

                    while ( have_posts() ) : the_post();

                        customify_the_blog_item();

                    endwhile;

                    the_posts_navigation();

                else :

                    get_template_part( 'template-parts/content', 'none' );

                endif;

                */
                ?>

            </div><!-- #.content-inner -->
        </main><!-- #main -->

	    <?php do_action( 'customify_sidebars' ); ?>

    </div><!-- #.customify-grid -->
</div><!-- #.customify-container -->

<?php
get_footer();
