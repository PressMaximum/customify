<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

get_header(); ?>
<div class="content-inner">
    <?php
    if ( ! customify_is_e_theme_location('archive') ) {
        customify_blog_posts_heading();
        customify_blog_posts();
    }
    ?>
</div><!-- #.content-inner -->
<?php
get_footer();
