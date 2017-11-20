<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
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
	                echo customify_get_layout();
	                while ( have_posts() ) : the_post();

		                get_template_part( 'template-parts/content', 'page' );

		                // If comments are open or we have at least one comment, load up the comment template.
		                if ( comments_open() || get_comments_number() ) :
			                comments_template();
		                endif;

	                endwhile; // End of the loop.
	                ?>
                </div><!-- #.content-inner -->
            </main><!-- #main -->

            <?php do_action( 'customify_sidebars' ); ?>

        </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->

<?php
get_footer();
