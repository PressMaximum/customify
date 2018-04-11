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
        if ( have_posts() ){
            while ( have_posts() ) {
                the_post();
                get_template_part( 'template-parts/content', 'search' );
            }
        } else {
            get_template_part( 'template-parts/content', 'none' );
        }
        ?>
    </div><!-- #.content-inner -->
<?php
get_footer();