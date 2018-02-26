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
            'more_text' => '',
            'more_display' => 1,
            'thumbnail_size' => '',
            'hide_thumb_if_empty' => 1,
            'pagination' => array(),
            'meta_config' => array(),
            'meta_sep' => null
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
            'mid_size' => 3,
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
        $_args[ 'pagination' ]['mid_size'] = absint( $_args[ 'pagination' ]['mid_size'] );

        if ( empty( $_args['meta_config'] ) ) {
            $_args['meta_config'] = array(
                array(
                    '_key' => 'categories',
                ),
                array(
                    '_key' => 'author',
                ),
                array(
                    '_key' => 'date',
                ),
                array(
                    '_key' => 'comment',
                )
            );
        }

        $this->args = $_args;
    }

    function layout_blog_classic( $post = null ){
        $media_fields = array(
            array(
                '_key' => 'thumbnail'
            ),
        );
        $content_fields = array(
            array(
                '_key' => 'title',
            ),
            array(
                '_key' => 'meta',
                'fields' => $this->args['meta_config'],
            ),
            array(
                '_key' => 'excerpt',
            ),
            array(
                '_key' => 'readmore',
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
            'more_text' => $this->args['more_text'],
            'more_display' => $this->args['more_display'],
            'meta_config' => $this->args['meta_config'],
            'meta_sep' => $this->args['meta_sep'],
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
        if ( ! $this->args['pagination']['show_paging'] ) {
            return ;
        }
        $prev_next = true;
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
            $prev_next = false;
        }

        the_posts_pagination( array(
            'mid_size' => ( $this->args['pagination']['mid_size'] ) ? 3 : 0,
            'prev_text'=> $prev_text,
            'next_text' => $next_text,
            'prev_next' => $prev_next,
        ) );
    }

}


function customify_blog_posts( $args = array() ){

    $args = wp_parse_args( $args, array(
        'el_id' => 'blog-posts',
        'prefix' => 'blog_post',
    ) );

    echo '<div id="'.esc_attr( $args['el_id'] ).'">';

    if ( is_archive() ){
        ?>
        <header class="page-header">
            <?php
            the_archive_title( '<h1 class="page-title">', '</h1>' );
            the_archive_description( '<div class="archive-description">', '</div>' );
            ?>
        </header><!-- .page-header -->
        <?php
    } else if ( customify_is_post_title_display() ) {
        ?>
        <header>
            <h1 class="page-title"><?php echo get_the_title( customify_get_support_meta_id() ); ?></h1>
        </header>
        <?php
    }

    if ( have_posts() ) :
        $_args = array(
            'layout'                => Customify_Customizer()->get_setting($args['prefix'].'_layout' ),
            'excerpt_length'        => Customify_Customizer()->get_setting($args['prefix'].'_excerpt_length' ),
            'excerpt_more'          => Customify_Customizer()->get_setting($args['prefix'].'_excerpt_more' ),
            'more_text'             => Customify_Customizer()->get_setting($args['prefix'].'_more_text'),
            'more_display'          => Customify_Customizer()->get_setting($args['prefix'].'_more_display'),
            'thumbnail_size'        => Customify_Customizer()->get_setting($args['prefix'].'_thumbnail_size' ),
            'hide_thumb_if_empty'   => Customify_Customizer()->get_setting($args['prefix'].'_hide_thumb_if_empty' ),
            'meta_config'           => Customify_Customizer()->get_setting($args['prefix'].'_meta_config' ),
            'meta_sep'              => Customify_Customizer()->get_setting($args['prefix'].'_meta_sep' ),
        );
        if ( ! is_array( $_args ) ) {
            $_args = $args ;
        }
        $pagination = array(
            'show_paging'   => Customify_Customizer()->get_setting( $args['prefix'].'_pg_show_paging' ),
            'show_nav'      => Customify_Customizer()->get_setting( $args['prefix'].'_pg_show_nav' ),
            'mid_size'      => Customify_Customizer()->get_setting( $args['prefix'].'_pg_mid_size' ),
            'prev_text'     => Customify_Customizer()->get_setting( $args['prefix'].'_pg_prev_text' ),
            'next_text'     => Customify_Customizer()->get_setting( $args['prefix'].'_pg_next_text' ),
        ) ;

        $l = new Customify_Posts_Layout();
        $_args[ 'pagination' ] = is_array( $pagination ) ? $pagination : array();
        $l->render( $_args );

    else :
        get_template_part( 'template-parts/content', 'none' );
    endif;
    echo '</div>';
}

function customify_archive_posts(){
    customify_blog_posts( array(
        'el_id' => 'archive-posts',
        'prefix' => 'archive_post',
    ));
}
