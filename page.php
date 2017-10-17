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
 * @package _beacon
 */

get_header(); ?>

    <div class="_beacon-container">
        <div class="_beacon-grid__equalHeight">

            <main id="main" <?php _beacon_main_content_class(); ?>>
                <div class="content-inner">
	                <?php
	                echo _beacon_get_layout();
	                while ( have_posts() ) : the_post();

		                get_template_part( 'template-parts/content', 'page' );

		                // If comments are open or we have at least one comment, load up the comment template.
		                if ( comments_open() || get_comments_number() ) :
			                comments_template();
		                endif;

	                endwhile; // End of the loop.
	                ?>
                </div>
            </main>


            <?php do_action( '_beacon_sidebars' ); ?>

        </div><!-- #._beacon-grid -->
    </div><!-- #._beacon-container -->

<?php
get_footer();
