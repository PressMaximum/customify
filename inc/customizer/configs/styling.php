<?php
if ( ! function_exists( 'customify_customizer_styling_config' ) ) {
	function customify_customizer_styling_config( $configs ) {

		$section = 'global_styling';

		$config = array(

			// Styling panel
			array(
				'name'     => 'styling_panel',
				'type'     => 'panel',
				'priority' => 22,
				'title'    => __( 'Styling', 'customify' ),
			),

			// Styling Global Section
			array(
				'name'  => "{$section}",
				'type'  => 'section',
				'panel' => 'styling_panel',
				'title' => __( 'Global Colors', 'customify' ),
                'priority' => 10,
			),
			array(
				'name'       => "{$section}_color_primary",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Primary Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/primary-color', '
.header-top,
.button,
button,
input[type="button"],
input[type="reset"],
input[type="submit"],
.pagination .nav-links > *:hover,
.pagination .nav-links span,
.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-item > a, 
.nav-menu-desktop.style-full-height .primary-menu-ul > li.current-menu-ancestor > a,
.nav-menu-desktop.style-full-height .primary-menu-ul > li > a:hover,
.posts-layout .readmore-button:hover
{
    background-color: {{value}};
}
.posts-layout .readmore-button {
	color: {{value}};
}
.pagination .nav-links > *:hover,
.pagination .nav-links span,
.entry-single .tags-links a:hover, 
.entry-single .cat-links a:hover,
.posts-layout .readmore-button
{
    border-color: {{value}};
}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_secondary",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Secondary Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/secondary-color', '
.customify-builder-btn
{
    background-color: {{value}};
}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_text",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Text Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/text-color', 'body,
input,
select,
optgroup,
textarea,
input[type="text"],
input[type="email"],
input[type="url"],
input[type="password"],
input[type="search"],
input[type="number"],
input[type="tel"],
input[type="range"],
input[type="date"],
input[type="month"],
input[type="week"],
input[type="time"],
input[type="datetime"],
input[type="datetime-local"],
input[type="color"],
textarea,
.header-search-form .search-submit,
.header-search-form .search-submit:hover,
.posts-layout .entry-title,
.header-bottom,
.site-branding .site-description
{
    color: {{value}};
}
abbr, acronym {
    border-bottom-color: {{value}};
}
.posts-layout .entry-media .entry-meta {
    background: {{value}};
}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_link",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Link Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/link-color', '
                a   
                {
                    color: {{value}};
				}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_link_hover",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Link Hover Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/link-color-hover', '
a:hover, 
a:focus,
.link-meta:hover, .link-meta a:hover
{
    color: {{value}};
}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_border",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Border Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/color-border', '
h2 + h3, 
.comments-area h2 + .comments-title, 
.h2 + h3, 
.comments-area .h2 + .comments-title, 
.page-breadcrumb {
    border-top-color: {{value}};
}
blockquote,
.site-content .widget-area .menu li.current-menu-item > a:before
{
    border-left-color: {{value}};
}

@media screen and (min-width: 64em) {
    .comment-list .children li.comment {
        border-left-color: {{value}};
    }
    .comment-list .children li.comment:after {
        background-color: {{value}};
    }
}

.page-titlebar, .page-breadcrumb {
    border-bottom-color: {{value}};
}

.header-search-form .search-field,
.entry-content .page-links a,
.header-search-modal,
.pagination .nav-links > *
.entry-footer .tags-links a, .entry-footer .cat-links a,
.search .content-area article,
.site-content .widget-area .menu li.current-menu-item > a,
.posts-layout .entry-inner,
.post-navigation .nav-links,
article.comment .comment-meta
{
    border-color: {{value}};
}

.header-search-modal::before {
    border-top-color: {{value}};
    border-left-color: {{value}};
}

@media screen and (min-width: 48em) {
    .content-sidebar.sidebar_vertical_border .content-area {
        border-right-color: {{value}};
    }
    .sidebar-content.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-primary {
        border-right-color: {{value}};
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-secondary {
        border-right-color: {{value}};
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-primary {
        border-left-color: {{value}};
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-secondary {
        border-left-color: {{value}};
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
        border-right-color: {{value}};
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
        border-right-color: {{value}};
    }
}
' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_meta",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Meta Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/color-meta',
'article.comment .comment-post-author {
	background: {{value}};				
}
.pagination .nav-links > *,
.link-meta, 
.link-meta a,
.color-meta,
.entry-single .tags-links:before, 
.entry-single .cats-links:before
{
    color: {{value}};
}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_heading",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Heading Color', 'customify' ),
				'css_format' => apply_filters( 'customify/styling/color-header', 'h1, h2, h3, h4, h5, h6 { color: {{value}};}' ),
				'selector'   => 'format',
			),

			array(
				'name'       => "{$section}_color_w_title",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Widget Title Color', 'customify' ),
				'css_format' => '.widget-title { color: {{value}};}',
				'selector'   => 'format',
			),

			// Styling Sidebar Widgets
			array(
				'name'  => "{$section}_widgets",
				'type'  => 'section',
				'panel' => 'styling_panel',
                'priority' => 90,
                'title' => __( 'Sidebar Widgets', 'customify' ),
			),

			array(
				'name'       => $section . '_styling_p_w_title',
				'type'       => 'styling',
				'section'    => "{$section}_widgets",
				'title'      => __( 'Primary Widgets Title', 'customify' ),
				'selector'   => array(
					'normal' => ".sidebar-primary .widget-title",
				),
				'css_format' => 'styling', // styling
				'fields'     => array(
					'normal_fields' => array(
						'link_color' => false, // disable for special field.
						'bg_image'   => false,
						'bg_cover'   => false,
						'bg_repeat'  => false,
						//'margin' => false,
						//'box_shadow' => false,
					),
					'hover_fields'  => false
				)
			),

			array(
				'name'       => $section . '_styling_s_w_title',
				'type'       => 'styling',
				'section'    => "{$section}_widgets",
				'title'      => __( 'Secondary Widgets Title', 'customify' ),
				'selector'   => array(
					'normal' => ".sidebar-secondary .widget-title",
				),
				'css_format' => 'styling', // styling
				'fields'     => array(
					'normal_fields' => array(
						'link_color' => false, // disable for special field.
						'bg_image'   => false,
						'bg_cover'   => false,
						'bg_repeat'  => false,
						//'margin' => false,
						//'box_shadow' => false,
					),
					'hover_fields'  => false
				)
			),

			// Styling Sidebar Widgets
			array(
				'name'  => "{$section}_footer_widgets",
				'type'  => 'section',
				'panel' => 'styling_panel',
				'priority' => 100,
                'title' => __( 'Footer Widgets', 'customify' ),
			),

			array(
				'name'       => $section . '_styling_f_w_title',
				'type'       => 'styling',
				'section'    => "{$section}_footer_widgets",
				'title'      => __( 'Footer Widgets Title', 'customify' ),
				'selector'   => array(
					'normal' => ".site-footer .widget-title",
				),
				'css_format' => 'styling', // styling
				'fields'     => array(
					'normal_fields' => array(
						'link_color' => false, // disable for special field.
						'bg_image'   => false,
						'bg_cover'   => false,
						'bg_repeat'  => false,
						//'margin' => false,
						//'box_shadow' => false,
					),
					'hover_fields'  => false
				)
			),

            array(
                'name'       => 'site_content_styling',
                'type'       => 'section',
                'panel'       => 'styling_panel',
                'priority' => 20,
                'title'      => __( 'Site Content', 'customify' ),
            ),

            array(
                'name'       => 'site_content_styling',
                'type'       => 'styling',
                'section'    => 'site_content_styling',
                'title'      => __( 'Content Area Styling', 'customify' ),
                'selector'   => array(
                    'normal'            => ".site-content .content-area",
                ),
                'css_format' => 'styling', // styling
                'fields'     => array(
                    'normal_fields' => array(
                        'text_color' => false,
                        'link_color' => false,
                    ),
                    'hover_fields'  => false,
                )
            ),

        );

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_styling_config' );