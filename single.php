<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package customify
 */

get_header(); ?>
    <div class="content-inner">
        <?php
        while ( have_posts() ) : the_post();

            get_template_part( 'template-parts/content', get_post_type() );

            the_post_navigation( array(
                'prev_text' => __( '<span>Prev post</span> %title', 'customify' ),
                'next_text' => __( '<span>Next post</span> %title', 'customify' ),
            ) );

            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        ?>

    </div><!-- #.content-inner -->
<?php
get_footer();
