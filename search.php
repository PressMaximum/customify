<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package customify
 */

get_header(); ?>
    <div class="content-inner">
        <?php
        do_action('customify/content/before');
        customify_blog_posts_heading();
        customify_blog_posts( array(
            '_overwrite' => array(
                'media_hide' => 1
            )
        ));
        do_action('customify/content/after');
        ?>
    </div><!-- #.content-inner -->
<?php
get_footer();