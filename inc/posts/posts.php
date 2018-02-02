<?php

class Customify_Posts_Layout {
    private $layout = 'blog_classic';
    private $pagination = array();

    function set_layout( $layout ){
        $this->layout = $layout;
    }

    function set_pagination( $args ){
        $args = wp_parse_args( $args, array(
            'show_paging' => '',
            'show_number' => '',
            'show_nav' => '',
            'preview_label' => '',
            'next_label' => '',
        ) );

        $this->pagination = $args;
    }

    function layout_blog_classic(){
        $media_fields = array(
            array(
                '_key' => 'thumbnail'
            )
        );
        $content_fields = array(
            array(
                '_key' => 'title',
            ),
            array(
                '_key' => 'excerpt',
            ),
        );

        ?>
        <div class="entry-media">
            <?php Customify_Blog_Builder()->build_fields( $media_fields ); ?>
        </div>
        <div class="entry-content-data">
            <?php Customify_Blog_Builder()->build_fields( $content_fields ); ?>
        </div>
        <?php


    }

    function blog_item( $post = null ){
        ?>
        <div <?php post_class( 'entry',  $post) ?>>
            <?php
            $this->layout_blog_classic();
            ?>
        </div><!-- /.entry post --> <?php
    }

    function render(){
        $classes =  array();
        $classes[] = 'posts-layout';
        $classes[] = 'layout--'.$this->layout;
        ?>
        <div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
            <?php
            if ( have_posts() ) {
                /* Start the Loop */
                while ( have_posts()) {
                    the_post();
                    $this->blog_item();
                }
                the_posts_navigation();
            } else {
                get_template_part('template-parts/content', 'none');
            } ?>
        </div>
        <?php
    }

}