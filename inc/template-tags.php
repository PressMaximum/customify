<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package customify
 */

if ( ! function_exists( 'customify_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function customify_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'customify' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'customify' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

	}
endif;

if ( ! function_exists( 'customify_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function customify_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'customify' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'customify' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'customify' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'customify' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'customify' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'customify' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;


if ( ! function_exists( 'customify_comment' ) ) :
	/**
	 * Template for comments and pingbacks.
	 *
	 * To override this walker in a child theme without modifying the comments template
	 * simply create your own customify_comment(), and that function will be used instead.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @return void
	 */
	function customify_comment( $comment, $args, $depth ) {
		//$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'customify' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'customify' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default :
				// Proceed with normal comments.
				global $post;
				?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment clearfix">
					<div class="comment-image">
						<?php echo get_avatar( $comment, 60 ); ?>
					</div>
					<div class="comment-reply">
						<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'customify' ), 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div>
					<div class="comment-wrap">
						<header class="comment-meta">
							<?php
							printf( '<cite class="comment-author fn vcard">%1$s %2$s</cite>',
								get_comment_author_link(),
								// If current post author is also comment author, make it known visually.
								( $comment->user_id === $post->post_author ) ? '<span class="comment-post-author">' . __( 'Post author', 'customify' ) . '</span>' : ''
							);
							?>
							<div class="comment-time-wrap">
								<?php
								printf( '<a class="comment-time" href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* translators: 1: date, 2: time */
                                    get_comment_date()
								);
								?>
							</div>
							<?php edit_comment_link( __( 'Edit', 'customify' ), '<span class="edit-link">', '</span>' ); ?>
						</header><!-- .comment-meta -->

						<?php if ( '0' == $comment->comment_approved ) : ?>
							<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'customify' ); ?></p>
						<?php endif; ?>

						<div class="comment-content entry-content">
							<?php comment_text(); ?>
							<?php  ?>
						</div><!-- .comment-content -->

					</div><!--/comment-wrapper-->

				</article><!-- #comment-## -->
				<?php
				break;
		endswitch; // end comment_type check
	}
endif;

if ( ! function_exists( 'customify_comment_field_to_bottom' ) ) :
	/**
	 * Move the comment content field to bottom of the respond form.
	 */
	function customify_comment_field_to_bottom( $fields ) {
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		$fields['comment'] = $comment_field;
		return $fields;
	}
	add_filter( 'comment_form_fields', 'customify_comment_field_to_bottom' );
endif;



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
            'excerpt_length' => Customify_Customizer()->get_setting('blog_post_excerpt_length' ),
            'thumbnail_size' => Customify_Customizer()->get_setting('blog_post_thumb_size' ),
            'meta_config' => Customify_Customizer()->get_setting('blog_post_meta' ),
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
        $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
        $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
        return $text;
    }

    function meta_date(){
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
        }
        $time_string = sprintf( $time_string,
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() ),
            esc_attr( get_the_modified_date( 'c' ) ),
            esc_html( get_the_modified_date() )
        );

        $posted_on = sprintf(
        /* translators: %s: post date. */
            esc_html_x( 'Posted on %s', 'post date', 'customify' ),
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>';
    }

    function meta_categories(){
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( esc_html__( ', ', 'customify' ) );
            if ( $categories_list ) {
                /* translators: 1: list of categories. */
                printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'customify' ) . '</span>', $categories_list ); // WPCS: XSS OK.
            }
        }
    }

    function meta_tags(){
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'customify' ) );
            if ( $tags_list ) {
                /* translators: 1: list of tags. */
                printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'customify' ) . '</span>', $tags_list ); // WPCS: XSS OK.
            }
        }
    }

    function meta_comment(){
        if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            echo '<span class="comments-link">';
            comments_popup_link(
                sprintf(
                    wp_kses(
                    /* translators: %s: post title */
                        __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'customify' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    get_the_title()
                )
            );
            echo '</span>';
        }
    }

    function meta_author(){
        $byline = sprintf(
            /* translators: %s: post author. */
            esc_html_x( 'by %s', 'post author', 'customify' ),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
        );

        echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
    }

    function post_meta( $post = null ){
        ?>
        <div class="entry-meta">
            <?php
            foreach( ( array ) $this->config['meta_config'] as $item ) {
                $item = wp_parse_args( $item, array(
                    '_key' => '',
                    '_visibility' => ''
                ) );

                if ( $item['_visibility'] !== 'hidden' ) {
                    if ( method_exists( $this, 'meta_'.$item['_key'] ) ) {
                        call_user_func_array( array( $this, 'meta_'.$item['_key'] ), array( $this->post ) );
                    }
                }
            }
            ?>
        </div><!-- .entry-meta -->
        <?php
    }

    function post_title( $post = null ){
        if ( is_singular() ) :
            the_title( '<h1 class="entry-title">', '</h1>' );
        else :
            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
        endif;
    }

    function post_thumbnail( $post = null ){
        if ( has_post_thumbnail() ) {
            ?>
            <div class="entry-thumbnail">
                <?php the_post_thumbnail($this->config['thumbnail_size'] ); ?>
            </div><!-- .entry-meta -->
            <?php
        }
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
        ?>
        <div class="entry-readmore">
            <a href="<?php the_permalink() ?>" title="<?php esc_attr( sprintf( __( 'Continue reading %s', 'customify' ), get_the_title() )  ); ?>"><?php _e( "Readmore", 'customify' ); ?></a>
        </div><!-- .entry-content -->
        <?php
    }

    function build( $field ){
       if ( method_exists( $this, 'post_'.$field ) ) {
            call_user_func_array( array( $this, 'post_'.$field ), array( $this->post ) );
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
        $items_config = Customify_Customizer()->get_setting('blog_post_item' );
        foreach ( ( array ) $items_config as $item ) {
            $item = wp_parse_args( $item, array(
                '_key' => '',
                '_visibility' => ''
            ) );
            if ( $item['_visibility'] !== 'hidden' ) {
                Customify_Blog_Builder()->build( $item['_key'] );
            }
        }
        ?>
        </div><!-- /.entry post --> <?php
    }
}