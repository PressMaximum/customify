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
        if ( ! customify_is_e_theme_location( 'single' ) ) {
            while (have_posts()) :
                $post_type = get_post_type();
                if (has_action("customify_single_{$post_type}_content")) {
                    do_action("customify_single_{$post_type}_content");
                } else {
                    customify_single_post();
                }
            endwhile; // End of the loop.
        }
        ?>
    </div><!-- #.content-inner -->
<?php
get_footer();
