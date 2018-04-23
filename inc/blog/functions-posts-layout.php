<?php

/**
 * Alias of class Customify_Post_Entry
 *
 * @return Customify_Post_Entry
 */
function Customify_Post_Entry(){
    return Customify_Post_Entry::get_instance();
}

if ( ! function_exists( 'customify_blog_posts_heading' ) ) {
    function customify_blog_posts_heading (){
        if (customify_is_post_title_display()) {
            if (is_search()) {
                ?>
                <header class="blog-posts-heading">
                    <h1 class="page-title"><?php printf( // WPCS: XSS ok.
                            __('Search Results for: %s', 'customify'),
                            '<span>' . get_search_query() . '</span>'
                        ); ?></h1>
                </header>
                <?php
            } elseif (is_archive()) {
                ?>
                <header class="page-header blog-posts-heading">
                    <?php
                    the_archive_title('<h1 class="page-title h3">', '</h1>');
                    the_archive_description('<div class="archive-description">', '</div>');
                    ?>
                </header><!-- .page-header -->
                <?php
            } else if (customify_is_post_title_display() && !(is_front_page() && is_home())) {
                ?>
                <header class="blog-posts-heading">
                    <h1 class="page-title"><?php echo get_the_title(customify_get_support_meta_id()); ?></h1>
                </header>
                <?php
            }
        }
    }
}


if( ! function_exists( 'customify_blog_posts' ) ) {
    /**
     * Display blog posts layout
     *
     * @param array $args
     */
    function customify_blog_posts($args = array())
    {

        $args = wp_parse_args($args, array(
            'el_id'  => 'blog-posts',
            'prefix' => 'blog_post',
        ));

        $render_class = apply_filters( 'customify/blog/render_callback', 'Customify_Posts_Layout' );

        echo '<div id="' . esc_attr($args['el_id']) . '">';
        if (have_posts()) {
            if ( class_exists( $render_class ) ) {
                $l = new $render_class();
                if (method_exists($l, 'render')) {
                    call_user_func_array(array($l, 'render'), array($args));
                }
            }
        }else {
            get_template_part('template-parts/content', 'none');
        };
        echo '</div>';
    }
}

if( ! function_exists( 'customify_archive_posts' ) ) {
    /*
     * Display posts as archive layout
     */
    function customify_archive_posts(){
        customify_blog_posts( array(
            'el_id' => 'archive-posts',
            'prefix' => 'archive_post',
        ));
    }

}
