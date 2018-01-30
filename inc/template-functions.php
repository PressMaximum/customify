<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package customify
 */

if ( ! function_exists( 'customify_get_config_sidebar_layouts' ) ) {
    function customify_get_config_sidebar_layouts (){
        return array(
            'content-sidebar' => __('Content / Sidebar', 'customify'),
            'sidebar-content' => __('Sidebar / Content', 'customify'),
            'content' => __('Content (no sidebars)', 'customify'),
            'sidebar-content-sidebar' => __('Sidebar / Content / Sidebar', 'customify'),
            'sidebar-sidebar-content' => __('Sidebar / Sidebar / Content', 'customify'),
            'content-sidebar-sidebar' => __('Content / Sidebar / Sidebar', 'customify'),
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
    function customify_get_all_image_sizes()
    {
        global $_wp_additional_image_sizes;
        $default_image_sizes = array('thumbnail', 'medium', 'large');

        foreach ($default_image_sizes as $size) {
            $image_sizes[$size]['width'] = intval(get_option("{$size}_size_w"));
            $image_sizes[$size]['height'] = intval(get_option("{$size}_size_h"));
            $image_sizes[$size]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
        }

        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
        }

        $options = array();
        foreach( $image_sizes as $k => $option ) {
            $options[ $k ] = sprintf( '%1$s - (%2$s x %3$s)', $k, $option['width'], $option['height'] );
        }

        $options[ 'full' ] = 'Full';
        return $options;
    }
}

if ( ! function_exists( 'customify_get_layout' ) ) {
	/**
	 * Get the layout for the current page from Customizer setting or individual page/post.
	 * @since 0.0.1
	 */
	function customify_get_layout() {
	    $layout = apply_filters( 'customify_get_layout', false );
	    if ( ! $layout ) {
            $default    = Customify_Customizer()->get_setting('sidebar_layout');
            $page       = Customify_Customizer()->get_setting('page_sidebar_layout');
            $blog_posts = Customify_Customizer()->get_setting('posts_sidebar_layout');
            $archive    = Customify_Customizer()->get_setting('posts_archives_sidebar_layout');
            $search     = Customify_Customizer()->get_setting('search_sidebar_layout');
            if (is_search()) {
                $layout = $search;
            } elseif (is_archive()) {
                $layout = $archive;
            } elseif (is_home() || is_category() || is_tag() || is_single()) { // blog page and single page
                $layout = $blog_posts;
            } else {
                $layout = $default;
            }

            if ( is_singular() ) {
                $page_custom = get_post_meta(get_the_ID(), '_customify_sidebar', true);
                if ($page_custom && $page_custom != 'default') {
                    $layout = $page_custom;
                } else {
                    $layout = $page;
                }
            }

            if (!$layout) {
                $layout = $default;
            }

        }
		return $layout;
	}
}

if ( ! function_exists( 'customify_get_sidebars' ) ) {
	/**
	 * Display primary or/and secondary sidebar base on layout setting.
	 * @since 0.0.1
	 */
	function customify_get_sidebars() {

		// Get the current layout
		$layout = customify_get_layout();
		if ( ! $layout || $layout == 'default' ) {
            $layout = 'content-sidebar';
        }

		// Layout with 2 column
		$layout_2_columns = array( 'sidebar-content', 'content-sidebar' );

		// Layout with 3 column
		$layout_3_columns = array( 'sidebar-sidebar-content', 'sidebar-content-sidebar', 'content-sidebar-sidebar' );

		// Only show primary sidebar for 2 column layout
		if ( in_array( $layout , $layout_2_columns) ) {
			get_sidebar();
		}

		// Show both sidebar for 3 column layout
		if ( in_array( $layout, $layout_3_columns ) ) {
			get_sidebar();
			get_sidebar('secondary');
		}

	}
}
add_action( 'customify_sidebars', 'customify_get_sidebars' );

if ( ! function_exists( 'customify_pingback_header' ) ) {
    /**
     * Add a pingback url auto-discovery header for singularly identifiable articles.
     */
    function customify_pingback_header()
    {
        if (is_singular() && pings_open()) {
            echo '<link rel="pingback" href="', esc_url(get_bloginfo('pingback_url')), '">';
        }
    }
}
add_action( 'wp_head', 'customify_pingback_header' );

if ( ! function_exists( 'customify_is_header_display' ) ) {
    /**
     * Check if show header
     *
     * @return bool
     */
    function customify_is_header_display(){
        $show = true;

        if ( is_singular() ) {
            $disable = get_post_meta(get_the_ID(), '_customify_disable_header', true);
            if ( $disable ) {
                $show = false;
            }
        }

        return $show;
    }
}

if ( ! function_exists( 'customify_is_footer_display' ) ) {
    /**
     * Check if show header
     *
     * @return bool
     */
    function customify_is_footer_display(){
        $show = true;

        if ( is_singular() ) {
            $rows =  array( 'main', 'bottom' );
            $count = 0;
            foreach ( $rows as $row_id ) {
                if ( ! customify_is_builder_row_display( 'footer', $row_id ) ) {
                    $count ++ ;
                }
            }
            if ( $count >= count( $rows ) ){
                $show = false;
            }
        }

        return  apply_filters( 'customify_is_header_display', $show );
    }
}

if ( ! function_exists( 'customify_is_builder_row_display' ) ) {
    /**
     * Check if show header
     *
     * @return bool
     */
    function customify_is_builder_row_display( $builder_id, $row_id = false ){
        $show = true;
        if ( $row_id  && $builder_id ) {
            if (is_singular()) {
                $key = $builder_id . '_' . $row_id;
                $disable = get_post_meta(get_the_ID(), '_customify_disable_' . $key, true);
                if ($disable) {
                    $show = false;
                }
            }
        }

        return apply_filters( 'customify_is_builder_row_display', $show, $builder_id, $row_id );
    }
}

if ( ! function_exists( 'customify_show_post_title' ) ) {
    /**
     * Check if display title of any post type
     */
    function customify_is_post_title_display(){
        $show = true;

        if ( is_singular() ) {
            $disable = get_post_meta(get_the_ID(), '_customify_disable_page_title', true);
            if ( $disable ) {
                $show = false;
            }
        }

        return apply_filters( 'customify_is_post_title_display', $show );
    }
}

