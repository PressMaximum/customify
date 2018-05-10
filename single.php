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
        while ( have_posts() ) :

            customify_single_post();

        endwhile; // End of the loop.
        ?>

    </div><!-- #.content-inner -->
<?php
get_footer();
