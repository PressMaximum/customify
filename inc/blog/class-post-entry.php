<?php

class Customify_Post_Entry {
	public $post;
	static $_instance;
	public $config    = array();
	public $post_type = 'post';
	function __construct( $_post = null ) {
		$this->set_post( $_post );
		$this->set_config();
	}

	function get_config_default() {
		$args = array(
			'excerpt_type'   => 'custom',
			'excerpt_length' => Customify()->get_setting( 'blog_post_excerpt_length' ),
			'excerpt_more'   => null,
			'thumbnail_size' => Customify()->get_setting( 'blog_post_thumb_size' ),
			'meta_config'    => Customify()->get_setting( 'blog_post_meta' ),
			'meta_sep'       => _x( '-', 'post meta separator', 'customify' ),
			'more_text'      => null,
			'more_display'   => 1,
			'term_sep'       => _x( ',', 'post term separator', 'customify' ),
			'term_count'     => 1,
			'tax'            => 'category',
			'title_tag'      => 'h2',
			'title_link'     => 1,
			'author_avatar'  => Customify()->get_setting( 'blog_post_author_avatar' ),
			'avatar_size'    => 32,
		);

		$size = Customify()->get_setting( 'blog_post_avatar_size' );
		if ( is_array( $size ) && isset( $size['value'] ) ) {
			$args['avatar_size'] = absint( $size['value'] );
		}

		return $args;
	}

	/**
	 * Set config
	 *
	 * @param null $config
	 */
	function set_config( $config = null ) {
		if ( ! is_array( $config ) ) {
			$config = array();
		}
		$config = wp_parse_args( $config, $this->get_config_default() );

		$this->config = $config;
	}

	/**
	 * Reset config
	 */
	function reset_config() {
		$this->config = $this->get_config_default();
	}

	/**
	 * Set post data
	 *
	 * @param null $_post
	 */
	function set_post( $_post = null ) {
		if ( ! $_post ) {
			global $post;
			$_post = get_post();
		}
		if ( is_array( $_post ) ) {
			$_post = (object) $_post;
		}
		$this->post = $_post;
	}

	/**
	 * Main instance
	 *
	 * @return Customify_Post_Entry
	 */
	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Trim the excerpt with custom length
	 *
	 * @see wp_trim_excerpt
	 *
	 * @param string   $text
	 * @param int|bool $excerpt_length
	 * @return string
	 */
	function trim_excerpt( $text, $excerpt_length = null ) {
		$text = strip_shortcodes( $text );
		/** This filter is documented in wp-includes/post-template.php */
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		if ( ! $excerpt_length ) {
			/**
			 * Filters the number of words in an excerpt.
			 *
			 * @since 2.7.0
			 *
			 * @param int $number The number of words. Default 55.
			 */
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
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

	/**
	 * Get meta date markup
	 *
	 * @return string
	 */
	function meta_date() {

		$icon = '<i class="fa fa-clock-o" aria-hidden="true"></i> ';

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		}
		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $icon . $time_string . '</a>';
		return '<span class="meta-item posted-on">' . $posted_on . '</span>';
	}

	/**
	 * Get terms array
	 *
	 * @param string $id         Post ID.
	 * @param string $taxonomy   Name of taxonomy.
	 * @param bool   $icon_first
	 * @return array|bool|WP_Error
	 */
	function get_terms_list( $id, $taxonomy, $icon_first = false ) {
		$terms = get_the_terms( $id, $taxonomy );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( empty( $terms ) ) {
			return false;
		}

		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$prm_term_id    = $this->get_primary_term_id( $id, $taxonomy );
			$prm_term       = get_term( $prm_term_id, $taxonomy );
			$prm_term_arr[] = $prm_term;

			// Make the primary term be the first term in the terms array.
			foreach ( $terms as $index => $term ) {
				if ( $prm_term_id == $term->term_id ) {
					unset( $terms[ $index ] );
					break;
				}
			}
			$terms = array_merge( $prm_term_arr, $terms );
		}

		$links = array();

		$icon = '<i class="fa fa-folder-open-o" aria-hidden="true"></i> ';

		foreach ( $terms as $index => $term ) {
			$link = get_term_link( $term, $taxonomy );
			if ( is_wp_error( $link ) ) {
				return $link;
			}

			if ( $icon_first && 0 == $index ) { // phpcs:ignore

			} else {
				$icon = '';
			}

			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $icon . esc_html( $term->name ) . '</a>';
		}

		return $links;
	}

	/**
	 * Get primary term ID.
	 *
	 * The primary term is either the first term or the primary term set via YOAST SEO Plugin.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Name of taxonomy.
	 * @return int|false Primary or first term ID. False if no term is set.
	 */
	function get_primary_term_id( $post_id, $taxonomy ) {
		$prm_term = '';
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $post_id );
			$prm_term           = $wpseo_primary_term->get_primary_term();
		}
		if ( ! is_object( $wpseo_primary_term ) || empty( $prm_term ) ) {
			$term = wp_get_post_terms( $post_id, $taxonomy );
			if ( isset( $term ) && ! empty( $term ) ) {
				return $term[0]->term_id;
			} else {
				return '';
			}
		}
		return $wpseo_primary_term->get_primary_term();
	}

	/**
	 * Get first category markup
	 *
	 * @return string
	 */
	function meta_categories() {
		$html = '';
		if ( get_post_type() === $this->post_type ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = $this->get_terms_list( $this->get_post_id(), $this->config['tax'], true );
			if ( is_array( $categories_list ) && $this->config['term_count'] > 0 ) {
				$categories_list = array_slice( $categories_list, 0, $this->config['term_count'] );
			}
			$html .= sprintf( '<span class="meta-item meta-cat">%1$s</span>', join( $this->config['term_sep'], $categories_list ) ); // WPCS: XSS OK.
		}
		return $html;
	}

	/**
	 * Get Tags list markup
	 *
	 * @return string
	 */
	function meta_tags() {
		$html = '';
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'customify' ) );
			if ( $tags_list ) {
				$html .= sprintf( '<span class="meta-item tags-links">%1$s</span>', $tags_list ); // WPCS: XSS OK.
			}
		}
		return $html;
	}


	/**
	 * Get tags list markup
	 */
	function post_tags() {
		$html = '';
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'customify' ) );
			if ( $tags_list ) {
				$html .= sprintf( '<div class="entry--item entry-tags tags-links">' . esc_html__( 'Tagged ', 'customify' ) . '%1$s</div>', $tags_list ); // WPCS: XSS OK.
			}
		}
		echo $html;
	}
	/**
	 * Get categories list markup
	 */
	function post_categories() {
		$html = '';
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$list = get_the_category_list( esc_html_x( ', ', 'list item separator', 'customify' ) );
			if ( $list ) {
				$html .= sprintf( '<div class="entry--item entry-categories cats-links">' . esc_html__( 'Posted in ', 'customify' ) . '%1$s</div>', $list ); // WPCS: XSS OK.
			}
		}
		echo $html;
	}
	/**
	 * Get comment number markup
	 *
	 * @return string
	 */
	function meta_comment() {
		$html = '';
		if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			$icon          = '<i class="fa fa-comments-o"></i> ';
			$comment_count = get_comments_number();
			$html         .= '<span class="meta-item comments-link">';
			$html         .= '<a href="' . esc_url( get_comments_link() ) . '">' . $icon;
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

	/**
	 * Get author markup
	 *
	 * @return string
	 */
	function meta_author() {
		if ( $this->config['author_avatar'] ) {
			$avatar = get_avatar( get_the_author_meta( 'ID' ), $this->config['avatar_size'] );
		} else {
			$avatar = '<i class="fa fa-user-circle-o"></i> ';
		}

		$byline = '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . $avatar . esc_html( get_the_author() ) . '</a></span>';
		return '<span class="meta-item byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
	}

	/**
	 * Check if show post meta for this post
	 *
	 * @param object|integer $post
	 *
	 * @return boolean
	 */
	private function show_post_meta( $post ) {
		return apply_filters( 'customify/show/post_meta', get_post_type( $post ) == 'post' && ! is_search() );
	}

	/**
	 * Get post meta markup
	 *
	 * @param object|integer $post
	 * @param array          $meta_fields
	 * @param array          $args
	 */
	function post_meta( $post = null, $meta_fields = array(), $args = array() ) {

		if ( ! $this->show_post_meta( $post ) ) {
			return;
		}

		if ( empty( $meta_fields ) ) {
			$meta_fields = $this->config['meta_config'];
		}

		$metas = array();
		foreach ( (array) $meta_fields as $item ) {
			$item = wp_parse_args(
				$item,
				array(
					'_key'        => '',
					'_visibility' => '',
				)
			);

			if ( 'hidden' !== $item['_visibility'] ) {
				if ( method_exists( $this, 'meta_' . $item['_key'] ) ) {
					$s = call_user_func_array( array( $this, 'meta_' . $item['_key'] ), array( $this->post, $args ) );
					if ( $s ) {
						$metas[ $item['_key'] ] = $s;
					}
				}
			}
		}

		if ( ! empty( $metas ) ) {
			?>
			<div class="entry-meta entry--item text-uppercase text-xsmall link-meta">
				<?php
				// WPCS: XSS OK.
				echo join( '<span class="sep">' . $this->config['meta_sep'] . '</span>', $metas );
				?>
			</div><!-- .entry-meta -->
			<?php
		}
	}


	/**
	 * Post title markup
	 *
	 * @param null|WP_Post|int $post
	 * @param bool             $force_link
	 */
	function post_title( $post = null, $force_link = false ) {
		if ( is_singular() && ! $force_link ) {
			if ( customify_is_post_title_display() ) {
				the_title( '<h1 class="entry-title entry--item h2">', '</h1>' );
			}
		} else {
			if ( $this->config['title_link'] ) {
				the_title( '<' . $this->config['title_tag'] . ' class="entry-title entry--item"><a href="' . esc_url( get_permalink( $post ) ) . '" title="' . the_title_attribute( array( 'echo' => false ) ) . '" rel="bookmark" class="plain_color">', '</a></' . $this->config['title_tag'] . '>' );
			} else {
				the_title( '<' . $this->config['title_tag'] . ' class="entry-title entry--item">', '</' . $this->config['title_tag'] . '>' );
			}
		}
	}

	function get_post_id( $post = null ) {
		if ( is_object( $post ) ) {
			return $post->ID;
		} elseif ( is_array( $post ) ) {
			return $post['ID'];
		} elseif ( is_numeric( $post ) ) {
			return $post;
		} else {
			return get_the_ID();
		}
	}

	/**
	 * Get first category markup
	 *
	 * @param null|WP_Post|int $post
	 */
	function post_category( $post = null ) {
		$html = '';
		if ( get_post_type() === $this->post_type ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_term_list( $this->get_post_id( $post ), $this->config['tax'], '', '__cate_sep__' );
			if ( $categories_list && ! is_wp_error( $categories_list ) ) {
				$categories_list = explode( '__cate_sep__', $categories_list );
				if ( $this->config['term_count'] > 0 ) {
					$categories_list = array_slice( $categories_list, 0, $this->config['term_count'] );
				}
				$html .= sprintf( '<span class="entry-cat entry--item">%1$s</span>', join( $this->config['term_sep'], $categories_list ) ); // WPCS: XSS OK.
			}
		}
		echo $html;
	}

	/**
	 *  Post thumbnail markup
	 *
	 * @param null|WP_Post|int $post
	 */
	function post_thumbnail( $post = null ) {
		if ( is_single() && ! is_front_page() && ! is_home() ) {
			if ( has_post_thumbnail() ) {
				?>
			<div class="entry-thumbnail <?php echo ( has_post_thumbnail() ) ? 'has-thumb' : 'no-thumb'; ?>">
				<?php the_post_thumbnail( $this->config['thumbnail_size'] ); ?>
			</div>
				<?php
			}
		} else {
			?>
			<div class="entry-thumbnail <?php echo ( has_post_thumbnail() ) ? 'has-thumb' : 'no-thumb'; ?>">
			<?php the_post_thumbnail( $this->config['thumbnail_size'] ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Post excerpt markup
	 *
	 * @param null|WP_Post|int $post
	 * @param string           $type
	 * @param int|bool         $length
	 */
	function post_excerpt( $post = null, $type = '', $length = false ) {
		if ( ! $type ) {
			$type = $this->config['excerpt_type'];
		}

		if ( ! $length ) {
			$length = $this->config['excerpt_length'];
		}

		echo '<div class="entry-excerpt entry--item">';
		if ( 'excerpt' == $type ) {
			the_excerpt();
		} elseif ( 'more_tag' == $type ) {
			the_content( '', true );
		} elseif ( 'content' == $type ) {
			the_content( '', false );
		} else {
			$text = '';
			if ( $this->post ) {
				if ( '' != get_the_excerpt() ) {
					$text = get_the_excerpt();
				} elseif ( $this->post->post_excerpt ) {
					$text = $this->post->post_excerpt;
				} else {
					$text = $this->post->post_content;
				}
			}
			$excerpt = $this->trim_excerpt( $text, $length );
			if ( $excerpt ) {
				// WPCS: XSS OK.
				echo apply_filters( 'the_excerpt', $excerpt );
			} else {
				the_excerpt();
			}
		}

		echo '</div>';
	}

	/**
	 * Post content markup
	 */
	function post_content() {

		?>
		<div class="entry-content entry--item">
			<?php
			the_content();
			$this->post_pagination();
			?>
		</div><!-- .entry-content -->
		<?php
	}

	/**
	 * Post readmore
	 */
	function post_readmore() {
		if ( ! $this->config['more_display'] ) {
			return;
		}
		$more = $this->config['more_text'];
		if ( ! $more ) {
			if ( ! is_rtl() ) {
				$more = __( 'Read more &rarr;', 'customify' );
			} else {
				$more = __( 'Read more &larr;', 'customify' );
			}
		}
		?>
		<div class="entry-readmore entry--item">
			<a class="readmore-button" href="<?php the_permalink(); ?>" title="<?php esc_attr( sprintf( __( 'Continue reading %s', 'customify' ), get_the_title() ) ); ?>"><?php echo wp_kses_post( $more ); ?></a>
		</div><!-- .entry-content -->
		<?php
	}

	function post_comment_form() {
		if ( is_single() ) {
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				echo '<div class="entry-comment-form entry--item">';
				comments_template();
				echo '</div>';
			endif;
		}
	}


	function post_author_bio() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		if ( is_single() ) {
			global $post;
			// Detect if it is a single post with a post author.
			if ( is_single() && isset( $post->post_author ) ) {

				$user = get_user_by( 'ID', $post->post_author );
				if ( ! $user ) {
					return;
				}

				$display_name = $user->display_name ? $user->display_name : $user->user_login;
				// Get author's biographical information or description.
				$user_description = get_the_author_meta( 'user_description', $user->ID );
				// Get author's website URL.
				$user_website = get_the_author_meta( 'url', $user->ID );

				// Get link to the author archive page.
				$user_posts = get_author_posts_url( get_the_author_meta( 'ID', $user->ID ) );

				if ( ! empty( $display_name ) ) {
					$author_details = '<h4 class="author-bio-heading">' . sprintf( __( 'About the Author: <span>%s</span>', 'customify' ), $display_name ) . '</h4>';
				}

				$user_description = wptexturize( $user_description );
				$user_description = wpautop( $user_description );
				$user_description = convert_smilies( $user_description );

				$author_links = '<p class="author_links text-uppercase text-xsmall link-meta"><a href="' . $user_posts . '">' . sprintf( 'View all post by %s', $display_name ) . '</a>';

				// Check if author has a website in their profile.
				if ( ! empty( $user_website ) ) {
					// Display author website link.
					$author_links .= ' | <a href="' . $user_website . '" target="_blank" rel="nofollow">Website</a></p>';
				} else {
					// if there is no author website then just close the paragraph.
					$author_links .= '</p>';
				}

				$author_details .= '<div class="author-bio"><div class="author-bio-avatar">' . get_avatar( get_the_author_meta( 'user_email' ), 80 ) . '</div><div class="author-bio-details"><div class="author-bio-desc">' . $user_description . '</div>' . $author_links . '</div></div>';

				// Pass all this info to post content.
				$content = '<div class="entry-author-bio entry--item" >' . $author_details . '</div>';
			}

			echo $content;
		}
	}

	function post_navigation() {
		if ( ! is_single() ) {
			return '';
		}

		if ( get_post_type() != 'post' ) {
			return '';
		}

		echo '<div class="entry-post-navigation entry--item">';
		the_post_navigation(
			array(
				'next_text' => '<span class="meta-nav text-uppercase text-xsmall color-meta" aria-hidden="true">'
								. __( 'Next', 'customify' ) . '</span> '
								. '<span class="screen-reader-text">' . __( 'Next post:', 'customify' ) . '</span> '
								. '<span class="post-title text-large">%title</span>',
				'prev_text' => '<span class="meta-nav text-uppercase text-xsmall color-meta" aria-hidden="true">'
								. __( 'Previous', 'customify' ) . '</span> '
								. '<span class="screen-reader-text">' . __( 'Previous post:', 'customify' ) . '</span> '
								. '<span class="post-title text-large">%title</span>',
			)
		);
		echo '</div>';
	}

	/**
	 * Post pagination markup
	 */
	function post_pagination() {
		if ( ! is_single() ) {
			return '';
		}

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'customify' ),
				'after'  => '</div>',
			)
		);
	}

	/**
	 * Display related post
	 */
	function post_related() {
		if ( ! is_single() ) {
			return '';
		}

		if ( get_post_type() != 'post' ) {
			return '';
		}

		Customify_Related_Posts::get_instance()->display();
	}

	/**
	 * Build item markup width field config
	 *
	 * @param string           $field               Field settings.
	 * @param WP_Post|null|int $post
	 * @param array            $fields
	 * @param array            $args
	 */
	function build( $field, $post = null, $fields = null, $args = array() ) {
		// Allowed 3rd party hook to this.
		$cb = apply_filters( 'customify/single/build_field_callback', false, $field );
		if ( ! is_callable( $cb ) ) {
			if ( method_exists( $this, 'post_' . $field ) ) {
				$cb = array( $this, 'post_' . $field );
			}
		}
		$type = is_single() ? 'single' : 'loop';
		/**
		 * Hook before post item part
		 *
		 * @since 0.2.2
		 */
		do_action( "customify/{$type}/field_{$field}/before", $post, $fields, $args, $this );
		if ( is_callable( $cb ) ) {
			call_user_func_array( $cb, array( $post, $fields, $args ) );
		}
		/**
		 * Hook after post item part
		 *
		 * @since 0.2.2
		 */
		do_action( "customify/{$type}/field_{$field}/after", $post, $fields, $args, $this );
	}

	/**
	 * Build item markup width fields config
	 *
	 * @param array            $fields
	 * @param WP_Post|null|int $post
	 * @param array            $args
	 */
	function build_fields( $fields, $post = null, $args = array() ) {
		foreach ( (array) $fields as $item ) {
			$item = wp_parse_args(
				$item,
				array(
					'_key'        => '',
					'_visibility' => '',
					'fields'      => null,
				)
			);
			if ( 'hidden' !== $item['_visibility'] ) {
				$this->build( $item['_key'], $post, $item['fields'], $args );
			}
		}
	}
}
