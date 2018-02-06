<?php

class Customify_Posts_Layout {
    private $args = array();
    function set_args( $args = array() )
    {

        $_args = wp_parse_args( $args, array(
            'layout' => '',
            'columns' => '',
            'excerpt_length' => '',
            'excerpt_more' => '',
            'thumbnail_size' => '',
            'hide_thumb_if_empty' => '',
            'pagination' => array(),
        ) );

        if ( ! $_args['layout'] ) {
            $_args['layout'] = 'blog_classic';
        }

        $_args[ 'pagination' ] = wp_parse_args( $_args['pagination'], array(
            'show_paging' => 1,
            'show_number' => 1,
            'show_nav' => 1,
            'prev_text' => '',
            'next_text' => '',
        ) );

        if ( ! $_args['columns'] ) {
            $c = $this->get_predefined( $_args['layout'] );
            if ( $c ) {
                $_args['columns'] = $c['columns'];
            }
        }

        $_args[ 'columns'] = absint( $_args['columns'] );
        if ( $_args[ 'columns' ] < 1 ) {
            $_args[ 'columns' ] = 1;
        }
        if ( ( ! isset( $args['columns'] ) ||  ! $args['columns'] ) && $_args['layout'] == 'blog_masonry' ) {
            $_args['columns' ] = 3;
        }

        if( in_array( $_args['layout'] , array( 'blog_lateral', 'blog_classic' ) ) ) {
            $_args['columns' ] = 1;
        }


        $this->args = $_args;
    }

    function layout_blog_classic( $post = null ){
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
            array(
                '_key' => 'meta',
            ),
        );

        $show_media = true;
        if ( ! has_post_thumbnail( $post ) ) {
            if ( $this->args['hide_thumb_if_empty'] ) {
                $show_media = false;
            }
        }

        if ( $show_media ) {
        ?>
        <div class="entry-media">
            <?php Customify_Blog_Builder()->build_fields( $media_fields, $post ); ?>
        </div>
        <?php } ?>
        <div class="entry-content-data">
            <?php Customify_Blog_Builder()->build_fields( $content_fields, $post ); ?>
        </div>
        <?php
    }

    function blog_item( $post = null , $class = null ){
        if ( $this->args['columns'] > 1 ) {
            $entry_class = 'customify-col entry'. ( $class ? ' '.$class : '' );
        } else {
            $entry_class = 'entry'. ( $class ? ' '.$class : '' );
        }

        Customify_Blog_Builder()->set_post( $post );

        ?>
        <div <?php post_class( $entry_class,  $post ) ?>>
            <?php
            $this->layout_blog_classic( $post );
            ?>
        </div><!-- /.entry post --> <?php
    }

    function get_predefined( $layout ){
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
                'columns' => 2,
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

        if ( isset( $presets[ $layout ] ) ) {
            return $presets[ $layout ];
        }

        return false;
    }

    function render( $args = array() ){

        $this->set_args( $args );
        $classes =  array();

        if ( $this->args['layout'] !=='blog_masonry' && $this->args['layout'] != 'blog_timeline' ) {
            if ($this->args['columns'] > 1) {
                $classes[] = 'customify-grid-' . $this->args['columns'];
            }
        }

        Customify_Blog_Builder()->set_config( array(
            'thumbnail_size' => $this->args['thumbnail_size'],
            'excerpt_length' => $this->args['excerpt_length'],
            'excerpt_more' => $this->args['excerpt_more'],
            'meta_config' => array(
                array(
                    '_key' => 'author',
                ),
                array(
                    '_key' => 'date',
                ),
                /*
                array(
                    '_key' => 'categories',
                ),
                */
            )
        ) );

        $classes[] = 'posts-layout';
        $classes[] = 'layout--'.$this->args['layout'];
        $style = '';
        if ( $this->args['layout'] == 'blog_masonry' ) {
            // WPCS: XSS OK.
            $style = '-webkit-column-count: '.$this->args['columns'].';  column-count: '.$this->args['columns'].';';
        }
        ?>
        <div class="posts-layout-wrapper">

            <div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>"<?php echo ( $style != '' ) ? ' style="'.esc_attr( $style).'"' : ''; ?>>
                <?php
                if ( $this->args['layout'] == 'blog_timeline' ) {
                    echo '<div class="time-line"></div>';
                }
                ?>
                <?php
                if ( have_posts() ) {
                    global $post;
                    /* Start the Loop */
                    $i = 1;
                    while ( have_posts()) {
                        the_post();
                        $this->blog_item( $post, $i % 2 == 0 ? 'even' : 'odd' );
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


function customify_blog_posts(){
    echo '<div id="blog-posts">';
    if ( have_posts() ) :
        if ( is_home() && ! is_front_page() ) : ?>
            <header>
                <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
            </header>
            <?php
        endif;

        $args = Customify_Customizer()->get_setting_tab( 'blog_post_layout', 'default' );
        if ( ! is_array( $args ) ) {
            $args = array() ;
        }
        $pagination = Customify_Customizer()->get_setting_tab( 'blog_post_pagination', 'default' );
        $l = new Customify_Posts_Layout();
        $args[ 'pagination' ] = is_array(  $pagination ) ? $pagination : array();
        $l->render( $args );

    else :
        get_template_part( 'template-parts/content', 'none' );
    endif;
    echo '</div>';
}