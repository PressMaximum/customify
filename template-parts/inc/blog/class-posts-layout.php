<?php

class Customify_Posts_Layout {
	public $args = array();
	public $customizer_args = array();
	public $entry_class = '';

	function set_args( $customizer_args = array() ) {
		$args = array(
			'layout'              => Customify()->get_setting( $customizer_args['prefix'] . '_layout' ),
			'excerpt_type'        => Customify()->get_setting( $customizer_args['prefix'] . '_excerpt_type' ),
			'excerpt_length'      => Customify()->get_setting( $customizer_args['prefix'] . '_excerpt_length' ),
			'excerpt_more'        => Customify()->get_setting( $customizer_args['prefix'] . '_excerpt_more' ),
			'more_text'           => Customify()->get_setting( $customizer_args['prefix'] . '_more_text' ),
			'more_display'        => Customify()->get_setting( $customizer_args['prefix'] . '_more_display' ),
			'thumbnail_size'      => Customify()->get_setting( $customizer_args['prefix'] . '_thumbnail_size' ),
			'hide_thumb_if_empty' => Customify()->get_setting( $customizer_args['prefix'] . '_hide_thumb_if_empty' ),
			'meta_config'         => Customify()->get_setting( $customizer_args['prefix'] . '_meta_config' ),
			'meta_sep'            => Customify()->get_setting( $customizer_args['prefix'] . '_meta_sep' ),
			'author_avatar'       => Customify()->get_setting( $customizer_args['prefix'] . '_author_avatar' ),
			'media_hide'          => Customify()->get_setting( $customizer_args['prefix'] . '_media_hide' ),
		);

		$size = Customify()->get_setting( $customizer_args['prefix'] . '_avatar_size' );
		if ( is_array( $size ) && isset( $size['value'] ) ) {
			$args['avatar_size'] = absint( $size['value'] );
		}

		$pagination = array(
			'show_paging' => Customify()->get_setting( $customizer_args['prefix'] . '_pg_show_paging' ),
			'show_nav'    => Customify()->get_setting( $customizer_args['prefix'] . '_pg_show_nav' ),
			'mid_size'    => Customify()->get_setting( $customizer_args['prefix'] . '_pg_mid_size' ),
			'prev_text'   => Customify()->get_setting( $customizer_args['prefix'] . '_pg_prev_text' ),
			'next_text'   => Customify()->get_setting( $customizer_args['prefix'] . '_pg_next_text' ),
		);

		$args['pagination']    = is_array( $pagination ) ? $pagination : array();
		$this->customizer_args = $customizer_args;

		$_args = wp_parse_args(
			$args,
			array(
				'layout'              => '',
				'columns'             => '',
				'excerpt_length'      => '',
				'excerpt_more'        => '',
				'more_text'           => '',
				'more_display'        => 1,
				'thumbnail_size'      => '',
				'hide_thumb_if_empty' => 1,
				'pagination'          => array(),
				'meta_config'         => array(),
				'meta_sep'            => null,
			)
		);

		if ( ! $_args['layout'] || is_array( $_args['layout'] ) ) {
			$_args['layout'] = 'blog_classic';
		}

		$_args['pagination'] = wp_parse_args(
			$_args['pagination'],
			array(
				'show_paging' => 1,
				'show_number' => 1,
				'show_nav'    => 1,
				'prev_text'   => '',
				'next_text'   => '',
				'mid_size'    => 3,
			)
		);

		if ( ! $_args['columns'] ) {
			$c = $this->get_predefined( $_args['layout'] );
			if ( $c ) {
				$_args['columns'] = $c['columns'];
			}
		}

		$_args['columns'] = absint( $_args['columns'] );
		if ( $_args['columns'] < 1 ) {
			$_args['columns'] = 1;
		}
		if ( ( ! isset( $args['columns'] ) || ! $args['columns'] ) && 'blog_masonry' == $_args['layout'] ) {
			$_args['columns'] = 3;
		}

		if ( in_array( $_args['layout'], array( 'blog_lateral', 'blog_classic' ) ) ) { // phpcs:ignore
			$_args['columns'] = 1;
		}

		$_args['pagination']['mid_size'] = absint( $_args['pagination']['mid_size'] );

		if ( empty( $_args['meta_config'] ) ) {
			$_args['meta_config'] = array(
				array(
					'_key' => 'author',
				),
				array(
					'_key' => 'date',
				),
				array(
					'_key' => 'categories',
				),
				array(
					'_key' => 'comment',
				),
			);
		}

		$this->args = $_args;

		$this->args['header_fields'] = array(
			array(
				'_visibility' => '',
				'_key'        => 'title',
			),
			array(
				'_key'        => 'meta',
				'_visibility' => '',
			),
		);

		$this->args['body_fields']   = array(
			array(
				'_key'        => 'excerpt',
				'_visibility' => '',
			),
		);
		$this->args['footer_fields'] = array(
			array(
				'_key'        => 'readmore',
				'_visibility' => '',
			),
		);
		$this->args['media_fields']  = array();

		if ( isset( $this->customizer_args['_overwrite'] ) ) {
			$this->args = array_merge( $this->args, $this->customizer_args['_overwrite'] );
		}

		Customify_Post_Entry()->set_config( $this->args );
	}


	function has_only_field( $fields, $field = 'category' ) {
		$check = false;
		$n     = 0;
		$c     = false;
		foreach ( (array) $fields as $item ) {
			$item = wp_parse_args(
				$item,
				array(
					'_key'        => '',
					'_visibility' => '',
				)
			);
			if ( 'hidden' !== $item['_visibility'] ) {
				$n ++;
				if ( $item['_key'] == $field ) {
					$c = true;
				}
			}
		}

		if ( $c && $n < 2 ) {
			$check = true;
		} else {
			$check = false;
		}

		return $check;
	}

	function count_item_visibility( $fields ) {
		$n = 0;
		foreach ( (array) $fields as $item ) {
			$item = wp_parse_args(
				$item,
				array(
					'_key'        => '',
					'_visibility' => '',
				)
			);
			if ( 'hidden' !== $item['_visibility'] ) {
				$n ++;
			}
		}

		return $n;
	}

	function item_part( $part = '', $post = null, $inner_class = '' ) {

		$n = $this->count_item_visibility( $this->args[ $part . '_fields' ] );

		if ( isset( $this->args[ $part . '_fields' ] ) && $n > 0 ) {
			if ( 'media' == $part && $this->has_only_field( $this->args[ $part . '_fields' ] ) ) {
				Customify_Post_Entry()->build_fields( $this->args[ $part . '_fields' ], $post );
			} else {
				$only_more = $this->has_only_field( $this->args[ $part . '_fields' ], 'readmore' );
				$classes   = array();
				$classes[] = 'entry-article-part entry-article-' . $part;
				if ( $only_more ) {
					$classes[] = 'only-more';
				}
				echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';
				if ( $inner_class ) {
					echo '<div class="' . esc_attr( $inner_class ) . '">';
				}
				Customify_Post_Entry()->build_fields( $this->args[ $part . '_fields' ], $post );
				if ( $inner_class ) {
					echo '</div>';
				}
				echo '</div>';
			}
		}
	}

	function layout( $post = null ) {
		$media_fields = array(
			array(
				'_key' => 'thumbnail',
			),
		);

		if ( $this->args['media_hide'] ) {
			$show_media = false;
		} else {
			$show_media = true;
			if ( ! has_post_thumbnail( $post ) ) {
				if ( $this->args['hide_thumb_if_empty'] ) {
					$show_media = false;
				}
			}
		}

		switch ( $this->args['layout'] ) {
			case 'blog_column':
				$this->item_part( 'header', $post );
				if ( $show_media && $this->count_item_visibility( $this->args['header_fields'] ) ) {
					?>
					<div class="entry-article-part entry-media">
						<a class="entry-media-link " href="<?php echo esc_url( get_permalink( $post ) ); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"></a>
						<?php
						Customify_Post_Entry()->build_fields( $media_fields, $post );
						$this->item_part( 'media', $post, 'media-content-inner' );
						?>
					</div>
				<?php } ?>
				<div class="entry-content-data">
					<?php
					$this->item_part( 'body', $post );
					$this->item_part( 'footer', $post );
					?>
				</div>
				<?php
				break;
			default:
				if ( $show_media && $this->count_item_visibility( $this->args['header_fields'] ) ) {
					?>
					<div class="entry-media">
						<a class="entry-media-link " href="<?php echo esc_url( get_permalink( $post ) ); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"></a>
						<?php
						Customify_Post_Entry()->build_fields( $media_fields, $post );
						$this->item_part( 'media', $post, 'media-content-inner' );
						?>
					</div>
				<?php } ?>
				<div class="entry-content-data">
					<?php
					$this->item_part( 'header', $post );
					$this->item_part( 'body', $post );
					$this->item_part( 'footer', $post );
					?>
				</div>
				<?php

		}

	}

	function blog_item( $post = null, $class = null ) {
		$entry_class = array( 'entry' );

		if ( is_numeric( $this->args['columns'] ) && $this->args['columns'] > 1 ) {
			$entry_class[] = 'customify-col';
		} elseif ( is_array( $this->args['columns'] ) ) {
			$entry_class[] = 'customify-col';
		}
		if ( $class ) {
			$entry_class[] = $class;
		}
		if ( $this->entry_class ) {
			$entry_class[] = $this->entry_class;
		}

		$key = 'loop';
		if ( is_single() ) {
			$key = 'single';
		}

		Customify_Post_Entry()->set_post( $post );
		/**
		 * Hook before each post
		 *
		 * @since 0.2.0
		 */
		do_action( "customify/before-post/{$key}" );
		?>
		<article <?php post_class( join( ' ', $entry_class ), $post ); ?>>
			<div class="entry-inner">
				<?php
				$this->layout( $post );
				?>
			</div>
		</article><!-- /.entry post -->
		<?php
		/**
		 * Hook after each post
		 *
		 * @since 0.2.0
		 */
		do_action( "customify/after-post/{$key}" );
	}

	function get_predefined( $layout ) {
		if ( ! is_string( $layout ) ) {
			return false;
		}
		$presets = array(
			'blog_classic' => array(
				'columns'    => 1,
				'pagination' => array(),
			),

			'blog_lateral' => array(
				'columns'    => 1,
				'pagination' => array(),
			),

			'blog_boxed' => array(
				'columns'    => 2,
				'pagination' => array(),
			),

		);

		if ( ! empty( $layout ) ) {
			if ( isset( $presets[ $layout ] ) && $presets[ $layout ] ) {
				return $presets[ $layout ];
			}
		}

		return false;
	}

	function render( $customizer_args = array() ) {
		$this->set_args( $customizer_args );
		$classes = array();

		$atts = array();
		if ( is_numeric( $this->args['columns'] ) && $this->args['columns'] > 1 ) {
			$classes['grid']  = 'customify-grid-' . $this->args['columns'];
			$atts['data-col'] = $this->args['columns'];
		} elseif ( is_array( $this->args['columns'] ) ) {
			$this->args['columns'] = wp_parse_args(
				$this->args['columns'],
				array(
					'desktop' => 1,
					'tablet'  => 1,
					'mobile'  => 1,
				)
			);
			foreach ( $this->args['columns'] as $d => $v ) {
				$v = absint( $v );
				if ( $v < 1 ) {
					$v = 1;
				} elseif ( $v > 12 ) {
					$v = 12;
				}
				$this->args['columns'][ $d ] = $v;
				$atts[ 'data-col-' . $d ]    = $v;
			}
			$classes['grid'] = sprintf( 'customify-grid-%1$s_sm-%2$s_xs-%3$s', $this->args['columns']['desktop'], $this->args['columns']['tablet'], $this->args['columns']['mobile'] );
		}

		$classes[] = 'posts-layout';
		$classes[] = 'layout--' . $this->args['layout'];

		$s_atts = '';
		foreach ( $atts as $k => $v ) {
			$s_atts .= " {$k}='" . esc_attr( $v ) . "' ";
		}
		do_action( 'customify/blog/before-render', $this );
		?>
		<div class="posts-layout-wrapper">
			<div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>"<?php echo $s_atts; // WPCS: XSS OK. ?>>
				<?php
				if ( 'blog_timeline' == $this->args['layout'] ) {
					echo '<div class="time-line"></div>';
				}
				?>
				<?php
				if ( have_posts() ) {
					global $post;
					/* Start the Loop */
					$i = 1;
					while ( have_posts() ) {
						the_post();
						$this->blog_item( $post, ( 0 == $i % 2 ) ? 'even' : 'odd' );
						$i ++;
					}
				} else {
					get_template_part( 'template-parts/content', 'none' );
				}
				?>
			</div>
			<?php
			$this->render_pagination();
			?>
		</div>
		<?php
		do_action( 'customify/blog/after-render', $this );
	}

	function render_pagination() {
		if ( ! $this->args['pagination']['show_paging'] ) {
			return;
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

		the_posts_pagination(
			array(
				'mid_size'  => ( $this->args['pagination']['mid_size'] ) ? 3 : 0,
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'prev_next' => $prev_next,
			)
		);
	}

}

