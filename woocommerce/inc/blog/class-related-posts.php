<?php

/**
 * Class Customify_Related_Posts
 *
 * @since 0.2.4
 */
class Customify_Related_Posts {
	static private $_instance = null;

	/**
	 * Get instance
	 *
	 * @return Customify_Related_Posts
	 */
	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get related posts
	 *
	 * @param WP_Post|null|int $post
	 * @param string           $by
	 * @param int              $number
	 * @param string           $orderby
	 * @param string           $order
	 *
	 * @return WP_Query|bool
	 */
	function get_related_post( $post = null, $by = 'cat', $number = 3, $orderby = 'date', $order = 'desc' ) {

		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		$current_post_type = get_post_type( $post );
		$query_args        = array(
			'post_type'      => $current_post_type,
			'post__not_in'   => array( $post->ID ),
			'posts_per_page' => $number,
			'orderby'        => $orderby,
			'order'          => $order,
		);

		if ( 'tag' == $by ) {
			$terms    = get_the_tags( $post->ID );
			$term_ids = array();

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
			}
			$query_args['tag__in'] = $term_ids;

		} else {
			$terms    = get_the_category( $post->ID );
			$term_ids = array();

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
			}

			$query_args['category__in'] = $term_ids;
		}

		// Try get related by hand pick.
		$post__in = get_post_meta( $post->ID, '_customify_related_posts' );
		if ( ! empty( $post__in ) && is_array( $post__in ) ) {
			unset( $query_args['category__in'] );
			unset( $query_args['tag__in'] );

			$query_args['post__in'] = $post__in;
		}

		$query_args = apply_filters( 'customify/single_post/related_args', $query_args );

		$related_cats_post = new WP_Query( $query_args );

		return $related_cats_post;
	}

	/**
	 * Display related post
	 *
	 * @return bool
	 */
	function display() {
		if ( ! is_single() ) {
			return false;
		}

		$number = Customify()->get_setting( 'single_blog_post_related_number' );
		$number = absint( $number );
		if ( $number <= 0 ) {
			return false;
		}

		$layout  = 'grid';
		$title   = Customify()->get_setting( 'single_blog_post_related_title' );
		$cols    = Customify()->get_setting( 'single_blog_post_related_col', 'all' );
		$by      = Customify()->get_setting( 'single_blog_post_related_by' );
		$orderby = Customify()->get_setting( 'single_blog_post_related_orderby' );
		$order   = Customify()->get_setting( 'single_blog_post_related_order' );

		// Get related posts.
		$query_posts = $this->get_related_post( null, $by, $number, $orderby, $order );

		if ( $query_posts ) {
			$image_position = Customify()->get_setting( 'single_blog_post_related_img_pos' );
			$thumbnail_size = Customify()->get_setting( 'single_blog_post_related_thumbnail_size' );
			$excerpt_length = Customify()->get_setting( 'single_blog_post_related_excerpt_length' );
			$meta_config    = Customify()->get_setting( 'single_blog_post_related_meta' );
			$meta_args      = array();

			$wrapper_classes = array(
				'entry--item entry-related',
				'related-' . esc_attr( $layout ),
				'img-pos-' . esc_attr( $image_position ),
			);

			if ( ! is_array( $cols ) ) {
				$cols = array();
			}

			$cols = wp_parse_args(
				$cols,
				array(
					'desktop' => 2,
					'tablet'  => 2,
					'mobile'  => 2,
				)
			);

			$cols = array_map( 'absint', $cols );

			if ( ! $cols['desktop'] ) {
				$cols['desktop'] = 2;
			}

			if ( ! $cols['tablet'] ) {
				$cols['tablet'] = 1;
			}

			if ( ! $cols['mobile'] ) {
				$cols['mobile'] = 1;
			}

			$layout_class = "customify-grid-{$cols['desktop']}_sm-{$cols['tablet']}}_xs-{$cols['mobile']}}";

			if ( ! $query_posts->have_posts() ) {
				return '';
			}

			global $post;

			echo '<div class="' . esc_attr( join( ' ', $wrapper_classes ) ) . ' ">';

			echo '<h4 class="related-post-title">' . wp_kses_post( $title ) . '</h4>';
			echo '<div class="related-posts ' . esc_attr( $layout_class ) . '">';
			while ( $query_posts->have_posts() ) {
				$query_posts->the_post();
				$link = '<a href="' . esc_url( get_permalink( $post ) ) . '" title="' . the_title_attribute( array( 'echo' => false ) ) . '" rel="bookmark" class="plain_color">';
				?>
				<article <?php post_class( 'related-post customify-col' ); ?>>
				<div class="related-thumbnail <?php echo ( has_post_thumbnail() ) ? 'has-thumb' : 'no-thumb'; ?>">
					<?php echo $link; ?>
					<?php the_post_thumbnail( $thumbnail_size ); ?>
					</a>
				</div>
				<div class="related-body">
					<?php
					the_title( '<h2 class="entry-title entry--item">' . $link, '</a></h2>' );
					Customify_Post_Entry::get_instance()->post_meta( $post, $meta_config, $meta_args );
					if ( $excerpt_length > 0 ) {
						Customify_Post_Entry::get_instance()->post_excerpt( $post, 'custom', $excerpt_length );
					}
					?>
				</div>
				<?php
				echo '</article>';
			}
			wp_reset_postdata();
			echo '</div>';
			echo '</div>';
		}

	}

}
