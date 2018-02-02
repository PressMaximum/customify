<?php

class Customify_Posts_Layout {
    private $args = array();
    function set_args( $args = array() )
    {

        $args = wp_parse_args( $args, array(
            'layout' => '',
            'columns' => '',
            'pagination' => array(),
        ) );

        $args[ 'columns' ] = absint( $args['columns'] );
        if ( $args[ 'columns' ] < 1 ) {
            $args[ 'columns' ] = 1;
        }

        if ( ! $args['layout'] ) {
            $args['layout'] = 'blog_classic';
        }

        $args[ 'pagination' ] = wp_parse_args( $args['pagination'], array(
            'show_paging' => 1,
            'show_number' => 1,
            'show_nav' => 1,
            'prev_text' => '',
            'next_text' => '',
        ) );

        $this->args = $args;
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

    function layout_classic_rounded(){
        $this->layout_blog_classic();
    }

    function layout_blog_column(){
        $this->layout_blog_classic( );
    }

    function blog_item( $post = null , $class = null ){
        if ( $this->args['columns'] > 1 ) {
            $entry_class = 'customify-col entry'. ( $class ? ' '.$class : '' );
        } else {
            $entry_class = 'entry'. ( $class ? ' '.$class : '' );
        }
        ?>
        <div <?php post_class( $entry_class,  $post) ?>>
            <?php
            $this->layout_blog_classic();
            ?>
        </div><!-- /.entry post --> <?php
    }

    function get_predefined( ){
        $presets = array(
            'blog_classic' => array(
                'columns' => 1,
                'pagination' => array(),
            ),

            'blog_classic_rounded' => array(
                'columns' => 1,
                'pagination' => array(),
            ),

            'blog_column' => array(
                'columns' => 1,
                'pagination' => array(),
            ),

            'blog_2column' => array(
                'columns' => 2,
                'pagination' => array(),
            ),

            'blog_lateral' => array(
                'columns' => 1,
                'pagination' => array(),
            ),

            'blog_boxed' => array(
                'columns' => 1,
                'pagination' => array(),
            ),

            'blog_masonry' => array(
                'columns' => 3,
                'pagination' => array(),
            ),

            'blog_timeline' => array(
                'columns' => 2,
                'pagination' => array(),
            ),
        );

    }

    function render( $args = array() ){

        $this->set_args( $args );
        $classes =  array();

        if ( $this->args['columns'] > 1 ) {
            $classes[] = 'customify-grid-'.$this->args['columns'];
        }
        $classes[] = 'posts-layout';
        $classes[] = 'layout--'.$this->args['layout'];
        ?>
        <div class="posts-layout-wrapper">
            <div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
                <?php
                if ( have_posts() ) {
                    /* Start the Loop */
                    $i = 1;
                    while ( have_posts()) {
                        the_post();
                        $this->blog_item( null, $i % 2 == 0 ? 'even' : 'odd' );
                        $i++;
                    }

                } else {
                    get_template_part('template-parts/content', 'none');
                } ?>
            </div>
            <?php
            $this->render_pagination();
            ?>
        </div>
        <?php
    }

    function render_pagination(){
        if ( ! $this->args['pagination']['show_number'] ) {
            return ;
        }

        if ( $this->args['pagination']['show_nav'] ) {
            $prev_text = $this->args['pagination']['prev_text'];
            $next_text = $this->args['pagination']['next_text'];
            if ( ! $prev_text ) {
                $prev_text = _x( 'Previous', 'previous set of posts', 'customify' );
            }
            if ( ! $next_text ) {
                $next_text = _x( 'Next', 'next set of posts', 'customify' );
            }
        } else {
            $prev_text = false;
            $next_text = false;
        }

        the_posts_pagination( array(
            'mid_size' => ( $this->args['pagination']['show_number'] ) ? 3 : 1,
            'prev_text'=> $prev_text,
            'next_text' => $next_text,
        ) );
    }

}