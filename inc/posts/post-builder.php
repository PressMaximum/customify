<?php

class Customify_Blog_Builder {
    public $post;
    static $_instance;
    public $config = array();
    function __construct( $_post = null )
    {
        $this->set_post( $_post );
        $this->set_config();
    }

    function set_config( $config = null ) {
        if ( ! is_array( $config ) ) {
            $config = array();
        }
        $config = wp_parse_args( $config, array(
            'excerpt_length' => Customify()->get_setting('blog_post_excerpt_length' ),
            'excerpt_more' => null,
            'thumbnail_size' => Customify()->get_setting('blog_post_thumb_size' ),
            'meta_config' => Customify()->get_setting('blog_post_meta' ),
            'meta_sep' => _x( '-', 'post meta separator', 'customify' ),
            'more_text' => null,
            'more_display' => 1,
        ) );

        $this->config = $config;
    }

    function set_post( $_post = null ) {
        if ( ! $_post ) {
            global $post;
            $_post = get_post();
        }
        if ( is_array( $_post ) ) {
            $_post = ( object ) $_post;
        }
        $this->post = $_post;
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    /**
     * Trim the excerpt with custom length
     * @see wp_trim_excerpt
     * @param $text
     * @param null $excerpt_length
     * @return mixed|string|void
     */
    function trim_excerpt( $text, $excerpt_length = null ){
        $text = strip_shortcodes( $text );
        /** This filter is documented in wp-includes/post-template.php */
        $text = apply_filters( 'the_content', $text );
        $text = str_replace(']]>', ']]&gt;', $text);

        if ( ! $excerpt_length ) {
            /**
             * Filters the number of words in an excerpt.
             *
             * @since 2.7.0
             *
             * @param int $number The number of words. Default 55.
             */
            $excerpt_length = apply_filters('excerpt_length', 55 );
        }


        /**
         * Filters the string in the "more" link displayed after a trimmed excerpt.
         *
         * @since 2.9.0
         *
         * @param string $more_string The string shown within the more link.
         */
        if ( ! $this->config['excerpt_more'] ) {
            $excerpt_more = apply_filters( 'excerpt_more', ' ' . '&hellip;' );
        } else {
            $excerpt_more = $this->config['excerpt_more'];
        }

        $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
        return $text;
    }

    function meta_date(){

        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s';
            // </time><time class="updated" datetime="%3$s">%4$s</time>
        }
        $time_string = sprintf( $time_string,
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() ),
            esc_attr( get_the_modified_date( 'c' ) ),
            esc_html( get_the_modified_date() )
        );

        $posted_on = sprintf(
            //esc_html_x( 'Posted on %s', 'post date', 'customify' ),
            '%s',
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        return '<span class="posted-on">' . $posted_on . '</span>';
    }

    function meta_categories(){
        $html = '';
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( '__cate_sep__' );
            if ( $categories_list ) {
                $categories_list = explode( '__cate_sep__', $categories_list );
                $categories_list = $categories_list[0];
                //  esc_html__( 'Posted in %1$s', 'customify' )
                $string = '%1$s';
                /* translators: 1: list of categories. */
                $html.= sprintf( '<span class="cat-links">' . $string. '</span>', $categories_list ); // WPCS: XSS OK.
            }
        }
        return $html;
    }

    function meta_tags(){
        $html =  '';
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'customify' ) );
            if ( $tags_list ) {
                /* translators: 1: list of tags. */
                // esc_html__( 'Tagged %1$s', 'customify' )
                $html .= sprintf( '<span class="tags-links">%1$s</span>', $tags_list ); // WPCS: XSS OK.
            }
        }
        return $html;
    }

    function meta_comment(){
        $html =  '';
        if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            $comment_count = get_comments_number();
            $html .= '<span class="comments-link">';
            $html .= '<a href="'.esc_url( get_comments_link() ).'">';
            if ( 1 === $comment_count ) {
                $html .= sprintf(
                /* translators: 1: title. */
                    esc_html__( '1 Comment', 'customify' ),
                    $comment_count
                );
            } else {
                $html .= sprintf( // WPCS: XSS OK.
                /* translators: 1: comment count number, 2: title. */
                    esc_html( _nx( '%1$s Comment', '%1$s Comments', $comment_count, 'comments number', 'customify' ) ),
                    number_format_i18n( $comment_count )
                );
            }
            $html .= '</a>';
            $html .= '</span>';
        }

        return $html;
    }

    function meta_author(){
        // esc_html_x( 'by %s', 'post author', 'customify' ),
        $byline = sprintf(
        /* translators: %s: post author. */
            '%s',
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
        );
        return '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
    }

    function post_meta( $post = null, $meta_fields = array(), $args = array() ){

        if ( empty( $meta_fields ) ) {
            $meta_fields =  $this->config['meta_config'];
        }

        $metas = array();
        foreach( ( array ) $meta_fields as $item ) {
            $item = wp_parse_args( $item, array(
                '_key' => '',
                '_visibility' => ''
            ) );

            if ( $item['_visibility'] !== 'hidden' ) {
                if ( method_exists( $this, 'meta_'.$item['_key'] ) ) {
                    $s = call_user_func_array( array( $this, 'meta_'.$item['_key'] ), array( $this->post, $args ) );
                    if ( $s ) {
                        $metas[ $item['_key'] ] = $s;
                    }
                }
            }
        }

        if ( ! empty( $metas ) ) {
            ?>
            <div class="entry-meta">
                <?php
                // WPCS: XSS OK.
                echo join( ( $this->config['meta_sep'] ) ?'<span class="sep">'.$this->config['meta_sep'].'</span>' : '', $metas);
                ?>
            </div><!-- .entry-meta -->
            <?php
        }
    }

    function post_title( $post = null ){
        if ( is_singular() ) :
            the_title( '<h1 class="entry-title">', '</h1>' );
        else :
            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;
    }

    function post_thumbnail( $post = null ){
        //if ( has_post_thumbnail() ) {
            ?>
            <div class="entry-thumbnail <?php echo ( has_post_thumbnail() ) ? 'has-thumb': 'no-thumb'; ?>">
                <?php the_post_thumbnail($this->config['thumbnail_size'] ); ?>
            </div><!-- .entry-meta -->
            <?php
        //}
    }
    function post_excerpt(){
        $text= '';
        if ( $this->post ) {
            if ( $this->post->post_excerpt ) {
                $text = $this->post->post_excerpt;
            } else {
                $text = $this->post->post_content;
            }
        }
        $excerpt = $this->trim_excerpt( $text, $this->config['excerpt_length'] );
        ?>
        <div class="entry-excerpt">
            <?php
            if ( $excerpt ) {
                // WPCS: XSS OK.
                echo $excerpt;
            } else {
                the_excerpt();
            }
            ?>
        </div><!-- .entry-content -->
        <?php
    }
    function post_content(){
        ?>
        <div class="entry-content">
            <?php
            the_content();
            ?>
        </div><!-- .entry-content -->
        <?php
    }
    function post_readmore()
    {
        if ( ! $this->config['more_display'] ) {
            return ;
        }
        $more = $this->config['more_text'];
        if ( ! $more ) {
            $more = __( "Read more &rarr;", 'customify' );
        }
        ?>
        <div class="entry-readmore">
            <a class="readmore-button" href="<?php the_permalink() ?>" title="<?php esc_attr( sprintf( __( 'Continue reading %s', 'customify' ), get_the_title() )  ); ?>"><?php echo wp_kses_post( $more );  ?></a>
        </div><!-- .entry-content -->
        <?php
    }

    function build( $field , $post = null, $fields = null, $args = array() ){
        if ( method_exists( $this, 'post_'.$field ) ) {
            call_user_func_array( array( $this, 'post_'.$field ), array( $post, $fields, $args ) );
        }
    }

    function build_fields( $fields , $post = null, $args = array() ){
        foreach ( ( array ) $fields as $item ) {
            $item = wp_parse_args( $item, array(
                '_key' => '',
                '_visibility' => '',
                'fields' => null,
            ) );
            if ( $item['_visibility'] !== 'hidden' ) {
                $this->build( $item['_key'] , $post, $item['fields'], $args );
            }
        }
    }
}

function Customify_Blog_Builder(){
    return Customify_Blog_Builder::get_instance();
}


if ( ! function_exists( 'customify_the_blog_item' ) ) {
    function customify_the_blog_item( $post = null ){
        ?>
        <div <?php post_class( 'entry',  $post) ?>>
            <?php
            Customify_Blog_Builder()->set_post( $post );
            $items_config = Customify()->get_setting('blog_post_item' );
            Customify_Blog_Builder()->build_fields( $items_config );
            ?>
        </div><!-- /.entry post --> <?php
    }
}