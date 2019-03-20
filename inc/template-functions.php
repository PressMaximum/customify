<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package customify
 */

if ( ! function_exists( 'customify_get_config_sidebar_layouts' ) ) {
	function customify_get_config_sidebar_layouts() {
		return array(
			'content-sidebar'         => __( 'Content / Sidebar', 'customify' ),
			'sidebar-content'         => __( 'Sidebar / Content', 'customify' ),
			'content'                 => __( 'Content (no sidebars)', 'customify' ),
			'sidebar-content-sidebar' => __( 'Sidebar / Content / Sidebar', 'customify' ),
			'sidebar-sidebar-content' => __( 'Sidebar / Sidebar / Content', 'customify' ),
			'content-sidebar-sidebar' => __( 'Content / Sidebar / Sidebar', 'customify' ),
		);
	}
}
if ( ! function_exists( 'customify_get_all_image_sizes' ) ) {
	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 * @return array $image_sizes The image sizes
	 */
	function customify_get_all_image_sizes() {
		global $_wp_additional_image_sizes;
		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		$options = array();
		foreach ( $image_sizes as $k => $option ) {
			$options[ $k ] = sprintf( '%1$s - (%2$s x %3$s)', $k, $option['width'], $option['height'] );
		}

		$options['full'] = 'Full';

		return $options;
	}
}

if ( ! function_exists( 'customify_get_layout' ) ) {
	/**
	 * Get the layout for the current page from Customizer setting or individual page/post.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 */
	function customify_get_layout() {
		$default = Customify()->get_setting( 'sidebar_layout' );
		$layout  = apply_filters( 'customify_get_layout', null );
		if ( ! $layout ) {
			$page = Customify()->get_setting( 'page_sidebar_layout' );

			if ( is_home() && is_front_page() || ( is_home() && ! is_front_page() ) ) { // Blog page.
				$blog_posts = Customify()->get_setting( 'posts_sidebar_layout' );
				$layout     = $blog_posts;
			} elseif ( is_page() ) { // Page.
				$layout = Customify()->get_setting( 'page_sidebar_layout' );
			} elseif ( is_search() ) { // Search.
				$search = Customify()->get_setting( 'search_sidebar_layout' );
				$layout = $search;
			} elseif ( is_archive() ) { // Archive.
				$archive = Customify()->get_setting( 'posts_archives_sidebar_layout' );
				$layout  = $archive;
			} elseif ( is_category() || is_tag() || is_singular( 'post' ) ) { // blog page and single page.
				$blog_posts = Customify()->get_setting( 'posts_sidebar_layout' );
				$layout     = $blog_posts;
			} elseif ( is_404() ) { // 404 Page.
				$layout = Customify()->get_setting( '404_sidebar_layout' );
			} elseif ( is_singular() ) {
				$layout = Customify()->get_setting( get_post_type() . '_sidebar_layout' );
			}

			// Support for all posts that using meta settings.
			if ( Customify()->is_using_post() && customify_is_support_meta() ) {

				$post_type   = get_post_type();
				$page_custom = get_post_meta( customify_get_support_meta_id(), '_customify_sidebar', true );

				if ( ! $page_custom ) {
					if ( Customify()->is_woocommerce_active() ) {
						if ( is_cart() || is_checkout() || is_account_page() || is_product() ) {
							$page_custom = 'content';
						}
					}
				}

				if ( $page_custom ) {
					if ( $page_custom && 'default' != $page_custom ) {
						$layout = $page_custom;
					}
				} elseif ( 'page' == $post_type ) {
					$layout = $page;
				}
			}
		}

		if ( ! $layout ) {
			$layout = $default;
		}

		return $layout;
	}
}

if ( ! function_exists( 'customify_get_sidebars' ) ) {
	/**
	 * Display primary or/and secondary sidebar base on layout setting.
	 *
	 * @since 0.0.1
	 */
	function customify_get_sidebars() {

		// Get the current layout.
		$layout = customify_get_layout();
		if ( ! $layout || 'default' == $layout ) {
			$layout = 'content-sidebar';
		}

		// Layout with 2 column.
		$layout_2_columns = array( 'sidebar-content', 'content-sidebar' );

		// Layout with 3 column.
		$layout_3_columns = array( 'sidebar-sidebar-content', 'sidebar-content-sidebar', 'content-sidebar-sidebar' );

		// Only show primary sidebar for 2 column layout.
		if ( in_array( $layout, $layout_2_columns ) ) { // phpcs:ignore
			get_sidebar();
		}

		// Show both sidebar for 3 column layout.
		if ( in_array( $layout, $layout_3_columns ) ) { // phpcs:ignore
			get_sidebar();
			get_sidebar( 'secondary' );
		}

	}
}
add_action( 'customify/sidebars', 'customify_get_sidebars' );

if ( ! function_exists( 'customify_pingback_header' ) ) {
	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 */
	function customify_pingback_header() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
		}
	}
}
add_action( 'wp_head', 'customify_pingback_header' );

if ( ! function_exists( 'customify_is_support_meta' ) ) {
	function customify_is_support_meta() {
		$support = is_singular();
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			$support = true;
		}

		return $support;
	}
}

if ( ! function_exists( 'customify_get_support_meta_id' ) ) {
	function customify_get_support_meta_id() {
		$id = is_singular() ? get_the_ID() : false;
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			$id = get_option( 'page_for_posts' );
		}

		return $id;
	}
}

if ( ! function_exists( 'customify_is_header_display' ) ) {
	/**
	 * Check if show header
	 *
	 * @return bool
	 */
	function customify_is_header_display() {
		$show = true;
		// page_for_posts.
		if ( customify_is_support_meta() ) {
			$disable = get_post_meta( customify_get_support_meta_id(), '_customify_disable_header', true );
			if ( $disable ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_header_display', $show );
	}
}

if ( ! function_exists( 'customify_is_footer_display' ) ) {
	/**
	 * Check if show header
	 *
	 * @return bool
	 */
	function customify_is_footer_display() {
		$show = true;
		if ( customify_is_support_meta() ) {
			$rows  = array( 'main', 'bottom' );
			if ( class_exists( 'Customify_Pro' ) ) {
				$rows[] = 'top';
			}
			$count = 0;
			foreach ( $rows as $row_id ) {
				if ( ! customify_is_builder_row_display( 'footer', $row_id ) ) {
					$count ++;
				}
			}
			if ( $count >= count( $rows ) ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_footer_display', $show );
	}
}

if ( ! function_exists( 'customify_is_builder_row_display' ) ) {

	/**
	 * Check if show header
	 *
	 * @param string $builder_id
	 * @param bool   $row_id
	 * @param bool   $post_id
	 *
	 * @return mixed
	 */
	function customify_is_builder_row_display( $builder_id, $row_id = false, $post_id = false ) {
		$show = true;
		if ( $row_id && $builder_id ) {
			if ( ! $post_id ) {
				$post_id = apply_filters( 'customify_builder_row_display_get_post_id', customify_get_support_meta_id() );
			}
			$key     = $builder_id . '_' . $row_id;
			$disable = get_post_meta( $post_id, '_customify_disable_' . $key, true );
			if ( $disable ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_builder_row_display', $show, $builder_id, $row_id, $post_id );
	}
}

if ( ! function_exists( 'customify_show_post_title' ) ) {
	/**
	 * Check if display title of any post type
	 */
	function customify_is_post_title_display() {
		$show = true;
		if ( Customify()->is_using_post() ) {
			$disable = get_post_meta( Customify()->get_current_post_id(), '_customify_disable_page_title', true );
			if ( $disable ) {
				$show = false;
			}
		}

		$r = apply_filters( 'customify_is_post_title_display', $show );

		return $r;
	}
}


/**
 * Retrieve the archive title based on the queried object.
 *
 * @param string $title
 *
 * @return string Archive title.
 */
function customify_get_the_archive_title( $title ) {
	$disable = Customify()->get_setting( 'page_header_show_archive_prefix' );
	if ( ! $disable ) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'customify' ) );
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'customify' ) );
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'customify' ) );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		}
	}

	return $title;
}

add_filter( 'get_the_archive_title', 'customify_get_the_archive_title', 15 );

function customify_search_form( $form ) {
	$form = '
		<form role="search" class="sidebar-search-form" action="' . esc_url( home_url( '/' ) ) . '">
            <label>
                <span class="screen-reader-text">' . _x( 'Search for:', 'label', 'customify' ) . '</span>
                <input type="search" class="search-field" placeholder="' . esc_attr__( 'Search &hellip;', 'customify' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'customify' ) . '" />
            </label>
            <button type="submit" class="search-submit" >
                <svg aria-hidden="true" focusable="false" role="presentation" xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21">
                    <path id="svg-search" fill="currentColor" fill-rule="evenodd" d="M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z"></path>
                </svg>
            </button>
        </form>';

	return $form;
}

add_filter( 'get_search_form', 'customify_search_form' );
