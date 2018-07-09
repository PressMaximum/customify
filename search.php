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

        if ( ! customify_is_e_theme_location( 'archive' ) ) {
            if ( customify_is_post_title_display() ) {

                $args = Customify_Page_Header::get_instance()->get_settings();

                ?>
                <header class="blog-posts-heading blog-search-heading">
                    <?php
                    // WPCS: XSS ok.
                    echo '<h1 class="page-title h3">'.$args['title'].'</h1>';
                    ?>
                </header><!-- .entry-header -->
                <?php
            }
            if ( have_posts() ){
                while ( have_posts() ) {
                    the_post();
                    get_template_part( 'template-parts/content', 'search' );
                }
            } else {
                get_template_part( 'template-parts/content', 'none' );
            }
        }

        ?>
    </div><!-- #.content-inner -->
<?php
get_footer();