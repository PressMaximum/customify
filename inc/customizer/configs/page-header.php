<?php

class Customify_Page_Header {
	public $name = null;
	public $description = null;
	static $is_transparent = null;
	static $_instance = null;
	static $_settings = null;

	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ) );
		if ( ! is_admin() ) {
			add_action( 'customify_is_post_title_display', array( $this, 'display_page_title' ), 35 );
			add_action( 'customify/site-start', array( $this, 'render' ), 35 );
			add_action( 'wp', array( $this, 'wp' ), 85 );
		}
		self::$_instance = $this;
	}

	function wp() {
		$this->get_settings();
	}

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function config( $configs = array() ) {
		$section      = 'page_header';
		$name         = 'page_header';
		$choices      = array(
			'default'  => __( 'Default - inside main content', 'customify' ),
			'cover'    => __( 'Cover', 'customify' ),
			'titlebar' => __( 'Titlebar', 'customify' ),
			'none'     => __( 'Hide', 'customify' ),
		);
		$render_cb_el = array( $this, 'render' );

		$display_fields = array(
			array(
				'name'        => 'page',
				'type'        => 'select',
				'label'       => __( 'Display on single page', 'customify' ),
				'description' => __( 'Apply when viewing single page', 'customify' ),
				'default'     => 'titlebar',
				'choices'     => $choices,
			),
			array(
				'name'        => 'post',
				'type'        => 'select',
				'label'       => __( 'Display on single post', 'customify' ),
				'description' => __( 'Apply when viewing single post', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),

			array(
				'name'        => 'category',
				'type'        => 'select',
				'label'       => __( 'Display on categories', 'customify' ),
				'description' => __( 'Apply when viewing a category page', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),
			array(
				'name'        => 'index',
				'type'        => 'select',
				'label'       => __( 'Display on index', 'customify' ),
				'description' => __( 'Apply when your homepage displays as latest posts', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),
			array(
				'name'        => 'search',
				'type'        => 'select',
				'label'       => __( 'Display on search', 'customify' ),
				'description' => __( 'Apply when viewing search results page', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),
			array(
				'name'        => 'archive',
				'type'        => 'select',
				'label'       => __( 'Display on archive', 'customify' ),
				'description' => __( 'Apply when viewing archive pages, e.g. Tag, Author, Date, Custom Post Type or Custom Taxonomy', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),
			array(
				'name'        => 'page_404',
				'type'        => 'select',
				'label'       => __( 'Display on 404 page', 'customify' ),
				'description' => __( 'Apply when the page not found', 'customify' ),
				'default'     => '',
				'choices'     => $choices,
			),

		);

		$title_fields = array(
			array(
				'name'        => 'index',
				'type'        => 'text',
				'label'       => __( 'Title for index page', 'customify' ),
				'description' => __( 'Apply when your homepage displays as latest posts', 'customify' ),
				'default'     => '',
			),
			array(
				'name'        => 'post',
				'type'        => 'text',
				'label'       => __( 'Title for single post', 'customify' ),
				'description' => __( 'Apply when viewing single post', 'customify' ),
				'default'     => '',
			),
			array(
				'name'        => 'page_404',
				'type'        => 'text',
				'label'       => __( 'Title for 404 page', 'customify' ),
				'description' => __( 'Apply when the page not found', 'customify' ),
				'default'     => '',
			),
		);

		$tagline_fields = array(
			array(
				'name'        => 'index',
				'type'        => 'textarea',
				'label'       => __( 'Tagline for index page', 'customify' ),
				'description' => __( 'Apply when your homepage displays as latest posts', 'customify' ),
				'default'     => '',
			),
			array(
				'name'        => 'post',
				'type'        => 'textarea',
				'label'       => __( 'Tagline for single post', 'customify' ),
				'description' => __( 'Apply when viewing single post', 'customify' ),
				'default'     => '',
			),
			array(
				'name'        => 'page_404',
				'type'        => 'textarea',
				'label'       => __( 'Tagline for 404 page', 'customify' ),
				'description' => __( 'Apply when the page not found', 'customify' ),
				'default'     => '',
			),
		);

		$post_types = customify_get_content_post_types();
		if ( count( $post_types ) > 0 ) {
			foreach ( $post_types as $pt => $label ) {
				$display_fields[] = array(
					'name'        => $pt,
					'type'        => 'select',
					'label'       => sprintf( __( 'Display on %s page', 'customify' ), $label['singular_name'] ),
					'description' => sprintf( __( 'Apply when viewing single %s', 'customify' ), $label['singular_name'] ),
					'default'     => '',
					'choices'     => $choices,
				);

				// Per-CPT archive display. Only for types with a real archive;
				// WooCommerce owns the `product` shop archive (the is_shop branch in
				// get_settings()), so skip it. Empty value inherits the generic
				// "archive" slice in the resolver, so sites that never set it are
				// unchanged.
				$pt_object = get_post_type_object( $pt );
				if ( 'product' !== $pt && $pt_object && $pt_object->has_archive ) {
					$display_fields[] = array(
						'name'        => "{$pt}_archive",
						'type'        => 'select',
						'label'       => sprintf( __( 'Display on %s archive', 'customify' ), $label['singular_name'] ),
						'description' => sprintf( __( 'Apply when viewing the %s archive', 'customify' ), $label['singular_name'] ),
						'default'     => '',
						'choices'     => $choices,
					);
				}

				$taxonomy_filter_args = [
					'show_in_nav_menus' => true,
				];

				$taxonomy_filter_args['object_type'] = [ $pt ];
				$taxonomies                          = get_taxonomies( $taxonomy_filter_args, 'objects' );
				$options                             = array();

				foreach ( $taxonomies as $taxonomy => $object ) {
					$options[ $taxonomy ] = $object->label;
					$display_fields[]     = array(
						'name'        => $taxonomy,
						'type'        => 'select',
						'label'       => sprintf( __( 'Display on %1$s %2$s', 'customify' ), $label['singular_name'], $object->labels->singular_name ),
						'description' => sprintf( __( 'Apply when viewing %1$s %2$s', 'customify' ), $label['singular_name'], $object->labels->singular_name ),
						'default'     => '',
						'choices'     => $choices,
					);
				}

				$title_fields[] = array(
					'name'        => $pt,
					'type'        => 'text',
					'label'       => sprintf( __( 'Title for %s', 'customify' ), $label['singular_name'] ),
					'description' => sprintf( __( 'Apply when viewing single %s', 'customify' ), $label['singular_name'] ),
					'default'     => '',
				);

				$tagline_fields[] = array(
					'name'        => $pt,
					'type'        => 'textarea',
					'label'       => sprintf( __( 'Tagline for %s', 'customify' ), $label['singular_name'] ),
					'description' => sprintf( __( 'Apply when viewing single %s', 'customify' ), $label['singular_name'] ),
					'default'     => '',
				);
			}
		}

		$config = array(
			array(
				'name'  => $section,
				'type'  => 'section',
				'panel' => 'layout_panel',
				'title' => __( 'Page Header', 'customify' ),
			),

			array(
				'name'       => $section . '_layout',
				'type'       => 'select',
				'section'    => $section,
				'title'      => __( 'Layout', 'customify' ),
				'selector'   => '.page-header--item',
				'css_format' => 'html_class',
				'default'    => '',
				'choices'    => array(
					''                      => __( 'Default', 'customify' ),
					'layout-full-contained' => __( 'Full width - Contained', 'customify' ),
					'layout-fullwidth'      => __( 'Full Width', 'customify' ),
					'layout-contained'      => __( 'Contained', 'customify' ),
				),
			),

			array(
				'name'    => "{$name}_display_h",
				'type'    => 'heading',
				'section' => $section,
				'title'   => __( 'Display Settings', 'customify' ),
			),

			array(
				'name'        => "{$name}_display",
				'type'        => 'modal',
				'section'     => $section,
				'label'       => __( 'Display', 'customify' ),
				'description' => __( 'Settings display for special pages.', 'customify' ),
				'default'     => array(
					'display' => array(
						'page'     => 'titlebar',
						'archive'  => 'titlebar',
						'category' => 'titlebar',
					),
				),
				'fields'      => array(
					'tabs'            => array(
						'display'  => __( 'Display', 'customify' ),
						'advanced' => __( 'Advanced', 'customify' ),
					),
					'display_fields'  => $display_fields,
					'advanced_fields' => array(
						array(
							'name'        => 'post_bg',
							'type'        => 'select',
							'label'       => __( 'Post Header Background Cover', 'customify' ),
							'description' => __( 'Apply when viewing single post and page header setting displays as cover.', 'customify' ),
							'default'     => '',
							'choices'     => array(
								'default'   => __( 'Default', 'customify' ),
								'blog_page' => __( 'Use featured image from blog page', 'customify' ),
								'featured'  => __( 'Use featured image of current post', 'customify' ),
							),
						),
						array(
							'name'    => 'post_title_tagline',
							'type'    => 'select',
							'label'   => __( 'Single Post Title & Tagline', 'customify' ),
							'default' => '',
							'choices' => array(
								'default'   => __( 'Default', 'customify' ),
								'blog_page' => __( 'Use title & tagline from blog page', 'customify' ),
								'current'   => __( 'Use title & tagline of current post', 'customify' ),
							),
						),
					),
				),
			),

			array(
				'name'            => "{$name}_title_tagline",
				'type'            => 'modal',
				'section'         => $section,
				'label'           => __( 'Title & Tagline', 'customify' ),
				'description'     => __( 'Title & tagline for special pages.', 'customify' ),
				'default'         => array(),
				'fields'          => array(
					'tabs'            => array(
						'titles'   => __( 'Title', 'customify' ),
						'taglines' => __( 'Tagline', 'customify' ),
					),
					'titles_fields'   => $title_fields,
					'taglines_fields' => $tagline_fields,
				),
				'selector'        => '#page-titlebar, #page-cover',
				'render_callback' => $render_cb_el,
			),

			array(
				'name'            => $name . '_show_archive_prefix',
				'type'            => 'checkbox',
				'section'         => $section,
				'title'           => __( 'Archive Prefix', 'customify' ),
				'description'     => __( 'Enable or disable archive prefix on category, date, tag page.', 'customify' ),
				'checkbox_label'  => __( 'Enable', 'customify' ),
				'default'         => 1,
				'selector'        => '#page-titlebar, #page-cover',
				'render_callback' => $render_cb_el,
			),

		);

		$configs = array_merge( $configs, $config );
		$configs = array_merge( $configs, $this->config_cover() );
		$configs = array_merge( $configs, $this->config_titlebar() );

		return $configs;
	}

	function config_titlebar() {

		$section      = 'page_header';
		$render_cb_el = array( $this, 'render' );
		$selector     = '#page-titlebar';
		$name         = 'titlebar';
		$config       = array(

			array(
				'name'    => "{$name}_styling_h_tb",
				'type'    => 'heading',
				'section' => 'page_header',
				'title'   => __( 'Titlebar Settings', 'customify' ),
			),

			array(
				'name'           => $name . '_show_title',
				'type'           => 'checkbox',
				'section'        => $section,
				'label'          => __( 'Show Title', 'customify' ),
				'description'    => __( 'Title is pull from post title, archive title.', 'customify' ),
				'checkbox_label' => __( 'Enable', 'customify' ),
				'default'        => 1,
			),

			array(
				'name'           => $name . '_show_tagline',
				'type'           => 'checkbox',
				'section'        => $section,
				'label'          => __( 'Show Tagline', 'customify' ),
				'description'    => __( 'Tagline is pull from post excerpt, archive description.', 'customify' ),
				'checkbox_label' => __( 'Enable', 'customify' ),
				'default'        => 1,
			),
			array(
				'name'       => $name . '_title_color',
				'type'       => 'color',
				'section'         => $section,
				'label'      => __( 'Title Color', 'customify' ),
				'selector'   => "$selector .titlebar-title",
				'css_format' => 'color: {{value}};',
			),

			array(
				'name'       => $name . '_tagline_color',
				'type'       => 'color',
				'section'         => $section,
				'label'      => __( 'Tagline Color', 'customify' ),
				'selector'   => "$selector .titlebar-tagline",
				'css_format' => 'color: {{value}};',
			),

			// Titlebar Background — explicit override. When set, the
			// generated CSS rule `#page-titlebar { background-color: <hex> }`
			// wins by id-selector specificity over the SCSS fallback chain
			// `.page-titlebar { background: var(--customify-surface, #f9f9f9) }`.
			// When empty (default for 30K legacy sites), the SCSS chain
			// applies — surface var if user opted in to Palette, else the
			// historical `#f9f9f9` hex.
			array(
				'name'        => $name . '_bg_color',
				'type'        => 'color',
				'section'     => $section,
				'label'       => __( 'Titlebar Background', 'customify' ),
				'description' => __( 'Override the titlebar background. Leave empty to follow the Palette Surface color (or the legacy #f9f9f9 fallback when Palette is not used).', 'customify' ),
				'selector'    => "$selector",
				'css_format'  => 'background-color: {{value}};',
			),

			array(
				'name'            => "{$name}_align",
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'device_settings' => true,
				'selector'        => "$selector",
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Text Align', 'customify' ),
			),

		);

		$config = apply_filters( 'customify/titlebar/config', $config, $this );

		return $config;
	}

	function config_cover() {

		$section      = 'page_header';
		$render_cb_el = array( $this, 'render' );
		$selector     = '#page-cover';
		$name         = 'header_cover';
		$config       = array(

			array(
				'name'    => "{$name}_settings_h",
				'type'    => 'heading',
				'section' => $section,
				'title'   => __( 'Cover Settings', 'customify' ),
			),

			array(
				'name'           => $name . '_show_title',
				'type'           => 'checkbox',
				'section'        => $section,
				'label'          => __( 'Show Title', 'customify' ),
				'description'    => __( 'Title is pull from post title, archive title.', 'customify' ),
				'checkbox_label' => __( 'Enable', 'customify' ),
				'default'        => 1,
			),

			array(
				'name'           => $name . '_show_tagline',
				'type'           => 'checkbox',
				'section'        => $section,
				'label'          => __( 'Show Tagline', 'customify' ),
				'description'    => __( 'Tagline is pull from post excerpt, archive description.', 'customify' ),
				'checkbox_label' => __( 'Enable', 'customify' ),
				'default'        => 1,
			),

			array(
				'name'           => $name . '_bg',
				'type'           => 'modal',
				'popover_chrome' => true,
				'section'    => $section,
				'title'      => __( 'Color & Background', 'customify' ),
				'selector'   => $selector,
				'css_format' => 'styling', // Styling.
				'default'    => array(
					'normal' => array(
						'bg_image' => array(
							'id'  => '',
							'url' => esc_url( get_template_directory_uri() ) . '/build/images/default-cover.jpg',
						),
					),
				),
				'fields'     => array(
					'tabs'          => array(
						'normal' => '_',
					),
					'normal_fields' => array(
						array(
							'name'       => 'title_color',
							'type'       => 'color',
							'label'      => __( 'Title Color', 'customify' ),
							'selector'   => "$selector .page-cover-title",
							'css_format' => 'color: {{value}};',
						),

						array(
							'name'       => 'tagline_color',
							'type'       => 'color',
							'label'      => __( 'Tagline Color', 'customify' ),
							'selector'   => "$selector .page-cover-tagline",
							'css_format' => 'color: {{value}};',
						),

						array(
							'name'       => 'bg_image',
							'type'       => 'image',
							'label'      => __( 'Background Image', 'customify' ),
							'selector'   => "$selector",
							'css_format' => 'background-image: url("{{value}}");',
						),
						array(
							'name'       => 'bg_cover',
							'type'       => 'select',
							'choices'    => array(
								''        => __( 'Default', 'customify' ),
								'auto'    => __( 'Auto', 'customify' ),
								'cover'   => __( 'Cover', 'customify' ),
								'contain' => __( 'Contain', 'customify' ),
							),
							'required'   => array( 'bg_image', 'not_empty', '' ),
							'label'      => __( 'Size', 'customify' ),
							'class'      => 'field-half-left',
							'selector'   => "$selector",
							'css_format' => '-webkit-background-size: {{value}}; -moz-background-size: {{value}}; -o-background-size: {{value}}; background-size: {{value}};',
						),
						array(
							'name'       => 'bg_position',
							'type'       => 'select',
							'label'      => __( 'Position', 'customify' ),
							'required'   => array( 'bg_image', 'not_empty', '' ),
							'class'      => 'field-half-right',
							'choices'    => array(
								''              => __( 'Default', 'customify' ),
								'center'        => __( 'Center', 'customify' ),
								'top left'      => __( 'Top Left', 'customify' ),
								'top right'     => __( 'Top Right', 'customify' ),
								'top center'    => __( 'Top Center', 'customify' ),
								'bottom left'   => __( 'Bottom Left', 'customify' ),
								'bottom center' => __( 'Bottom Center', 'customify' ),
								'bottom right'  => __( 'Bottom Right', 'customify' ),
							),
							'selector'   => "$selector",
							'css_format' => 'background-position: {{value}};',
						),
						array(
							'name'       => 'bg_repeat',
							'type'       => 'select',
							'label'      => __( 'Repeat', 'customify' ),
							'class'      => 'field-half-left',
							'required'   => array(
								array( 'bg_image', 'not_empty', '' ),
							),
							'choices'    => array(
								'repeat'    => __( 'Default', 'customify' ),
								'no-repeat' => __( 'No repeat', 'customify' ),
								'repeat-x'  => __( 'Repeat horizontal', 'customify' ),
								'repeat-y'  => __( 'Repeat vertical', 'customify' ),
							),
							'selector'   => "$selector",
							'css_format' => 'background-repeat: {{value}};',
						),

						array(
							'name'       => 'bg_attachment',
							'type'       => 'select',
							'label'      => __( 'Attachment', 'customify' ),
							'class'      => 'field-half-right',
							'required'   => array(
								array( 'bg_image', 'not_empty', '' ),
							),
							'choices'    => array(
								''       => __( 'Default', 'customify' ),
								'scroll' => __( 'Scroll', 'customify' ),
								'fixed'  => __( 'Fixed', 'customify' ),
							),
							'selector'   => "$selector",
							'css_format' => 'background-attachment: {{value}};',
						),

						array(
							'name'            => 'overlay',
							'type'            => 'color',
							'section'         => $section,
							'class'           => 'customify--clear',
							'device_settings' => false,
							'selector'        => "$selector:before",
							'label'           => __( 'Cover Overlay', 'customify' ),
							'css_format'      => 'background-color: {{value}};',
						),

					),
					'hover_fields'  => false,
				),
			),

			array(
				'name'            => "{$name}_align",
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'device_settings' => true,
				'selector'        => "$selector",
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Cover Text Align', 'customify' ),
			),

			array(
				'name'            => "{$name}_height",
				'type'            => 'slider',
				'section'         => $section,
				'device_settings' => true,
				'max'             => 1000,
				'title'           => __( 'Cover Height', 'customify' ),
				'selector'        => "{$selector} .page-cover-inner",
				'css_format'      => 'min-height: {{value}};',
				'default'         => array(
					'desktop' => array(
						'value' => '300',
					),
					'tablet'  => array(
						'value' => '250',
					),
					'mobile'  => array(
						'value' => '200',
					),
				),
			),

			array(
				'name'            => "{$name}_align",
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'device_settings' => true,
				'selector'        => "$selector",
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Cover Text Align', 'customify' ),
			),

		);
		$config       = apply_filters( 'customify/cover/config', $config, $this );

		return $config;
	}

	function get_settings() {

		if ( ! is_null( self::$_settings ) ) {
			return self::$_settings;
		}

		$args = array(
			'_page'                      => 'index',
			'display'                    => 'default',
			'title'                      => '',
			'tagline'                    => '',
			'image'                      => '',
			'title_tag'                  => 'h1',
			// Inline single-title control consumed by display_page_title():
			// '' = default rule (based on `display`), 'show' = force inline title,
			// 'hide' = suppress inline title only (cover/titlebar still render their title).
			'force_display_single_title' => '',
			// When true, hide the title TEXT in cover/titlebar AND inline. Set by
			// the per-post `_customify_disable_page_title` meta. Wrappers still render
			// so the cover background image / titlebar breadcrumb area survive.
			'disable_page_title'         => false,
			'show_title'                 => false, // force show post title.
			'shortcode'                  => false, // force show post title.
			'cover_tagline'              => 1, // Display tagline in cover.
			'titlebar_tagline'           => 1, // Display tagline in titlbar.
		);
		$name = 'page_header';

		$display  = Customify()->get_setting_tab( $name . '_display', 'display' );
		$advanced = Customify()->get_setting_tab( $name . '_display', 'advanced' );

		$titles   = Customify()->get_setting_tab( $name . '_title_tagline', 'titles' );
		$taglines = Customify()->get_setting_tab( $name . '_title_tagline', 'taglines' );

		$args['cover_tagline']    = Customify()->get_setting( 'header_cover_show_tagline' );
		$args['titlebar_tagline'] = Customify()->get_setting( 'titlebar_show_tagline' );

		$display = wp_parse_args(
			$display,
			array(
				'index'       => '',
				'category'    => '',
				'search'      => '',
				'archive'     => '',
				'page'        => '',
				'post'        => '',
				'singular'    => '',
				'product'     => '',
				'product_cat' => '',
				'product_tag' => '',
				'page_404'    => '',
			)
		);

		$advanced = wp_parse_args(
			$advanced,
			array(
				'post_bg'            => '',
				'post_title_tagline' => '',
			)
		);

		$titles = wp_parse_args(
			$titles,
			array(
				'index'    => '',
				'post'     => '',
				'product'  => '',
				'page_404' => '',
			)
		);

		$taglines = wp_parse_args(
			$taglines,
			array(
				'index'    => '',
				'post'     => '',
				'product'  => '',
				'page_404' => '',
			)
		);

		$post_thumbnail_id = false;

		$post_id = 0;
		if ( is_front_page() && is_home() ) { // index page.
			// Default homepage.
			$args['display'] = $display['index'];
			$args['title']   = $titles['index'];
			$args['tagline'] = $taglines['index'];
			$args['_page']   = 'index';
		} elseif ( is_front_page() ) {
			// static homepage.
			$args['display'] = $display['page'];
			$post_id         = get_the_ID();
			$args['_page']   = 'page';
		} elseif ( is_home() ) {
			// blog page.
			$args['display'] = $display['page'];
			$post_id         = get_option( 'page_for_posts' );
			$args['_page']   = 'page';
		} elseif ( is_category() ) {
			// category.
			$args['display'] = $display['category'];
			$args['title']   = get_the_archive_title();
			$args['tagline'] = get_the_archive_description();
			$args['_page']   = 'category';
			$post_id         = 0;
		} elseif ( is_page() ) {
			// single page.
			$args['display'] = $display['page'];
			$post_id         = get_the_ID();
			$args['_page']   = 'page';
		} elseif ( is_singular( 'post' ) ) {
			// single post.
			$args['display']   = $display['post'];
			$args['title_tag'] = 'h2';

			// Resolve per-post display meta UP FRONT so subsequent title
			// gating (e.g. "hide inline to avoid duplicating the wrapper
			// heading") sees the effective display mode for THIS post,
			// not the global default. The is_using_post() block below
			// re-applies the same meta — that's a harmless idempotent
			// reassignment, not a duplicate code path.
			$post_meta_display = get_post_meta( get_queried_object_id(), '_customify_page_header_display', true );
			if ( $post_meta_display && 'default' !== $post_meta_display ) {
				$args['display'] = ( 'normal' === $post_meta_display ) ? 'default' : $post_meta_display;
			}

			// Resolve the blog page once and defensively: only honour
			// `page_for_posts` when it actually points to a published `page`.
			// If the option holds a stale id (deleted page, or a non-page
			// record such as an attachment id), treat it as unset — otherwise
			// `get_the_title()` would echo an unrelated record (e.g. an
			// attachment titled "logo1") as the cover/titlebar heading.
			$blog_page_id = (int) get_option( 'page_for_posts' );
			if ( $blog_page_id && 'page' !== get_post_type( $blog_page_id ) ) {
				$blog_page_id = 0;
			}

			// Setup single post bg for cover.
			if ( 'blog_page' == $advanced['post_bg'] ) {
				$post_id           = $blog_page_id;
				$post_thumbnail_id = $post_id ? get_post_thumbnail_id( $post_id ) : false;
			} elseif ( 'featured' == $advanced['post_bg'] ) {
				$post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
			} else {
				$post_id = $blog_page_id;
				if ( $post_id ) {
					$post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
				}
			}

			if ( 'none' != $args['display'] ) {

				if ( 'blog_page' == $advanced['post_title_tagline'] ) {
					$post_id                            = $blog_page_id;
					$args['force_display_single_title'] = 'show';
					if ( ! $post_id ) {
						// Blog page invalid/unset. Respect the user's
						// explicit "use blog page title" choice — do NOT
						// substitute the current post's title (that would
						// override their stated intent and silently turn this
						// option into the 'current' option). Fall back to the
						// Customizer "Title for single post" text only,
						// otherwise leave the heading blank so the
						// cover/titlebar renders without a misleading title.
						if ( $titles['post'] || $taglines['post'] ) {
							$args['title']   = $titles['post'];
							$args['tagline'] = $taglines['post'];
						}
					}
				} elseif ( 'current' == $advanced['post_title_tagline'] ) {
					$post_id = get_queried_object_id();
					// Only hide inline when the cover/titlebar wrapper will
					// actually show the title and duplicate it. Comparing to
					// 'default' is unsafe here: $display['post'] may still be
					// the unset '' sentinel, which becomes 'default' later via
					// the bottom-of-function fallback. An empty value must
					// behave like 'default' (inline mode).
					if ( in_array( $args['display'], array( 'cover', 'titlebar' ), true ) ) {
						$args['force_display_single_title'] = 'hide';
					} else {
						$args['force_display_single_title'] = 'show';
					}
					$args['title_tag'] = 'h1';
				} else {
					$post_id                            = $blog_page_id;
					$args['force_display_single_title'] = 'show';
					if ( ! $post_id ) {
						if ( $titles['post'] || $taglines['post'] ) {
							$args['title']   = $titles['post'];
							$args['tagline'] = $taglines['post'];
						} else {
							// No (valid) blog page and no Customizer post
							// title: fall back to the current post so the
							// cover/titlebar shows something meaningful
							// instead of an empty heading. Hide inline only
							// when the cover/titlebar wrapper will actually
							// render and duplicate the title — NOT when
							// display is '' (the unset sentinel that the
							// bottom-of-function fallback rewrites to
							// 'default', i.e. inline mode).
							$post_id = get_queried_object_id();
							if ( in_array( $args['display'], array( 'cover', 'titlebar' ), true ) ) {
								$args['force_display_single_title'] = 'hide';
							}
						}
					}
				}
			}

			$args['_page'] = 'post';
		} elseif ( is_singular() ) {
			// single custom post type.
			$post_id   = get_the_ID();
			$post_type = get_post_type();
			if ( isset( $display[ $post_type ] ) ) {
				$args['display'] = $display[ $post_type ];
				$args['_page']   = 'singular_' . $post_type;
			} elseif ( isset( $display['singular'] ) ) {
				$args['display'] = $display['singular'];
				$args['_page']   = 'singular';
			}
		} elseif ( is_404() ) {
			// page not found.
			$args['display'] = $display['page_404'];
			$args['_page']   = '404';
			$args['title']   = $titles['page_404'];
			$args['tagline'] = $taglines['page_404'];
			if ( ! $args['title'] ) {
				$args['title'] = __( "Oops! That page can't be found.", 'customify' );
			}
		} elseif ( is_search() ) {
			// Search result.
			$args['display'] = $display['search'];
			$args['title']   = sprintf( // WPCS: XSS ok.
				/* translators: 1: Search query name */
				__( 'Search Results for: %s', 'customify' ),
				'<span>' . get_search_query() . '</span>'
			);
			$args['tagline'] = '';
			$args['_page']   = 'search';
			$post_id         = 0;
		} elseif ( is_archive() ) {
			$args['display'] = $display['archive'];
			$args['title']   = get_the_archive_title();
			$args['tagline'] = get_the_archive_description();
			$args['_page']   = 'archive';
			$post_id         = 0;

			// Per-CPT archive override (e.g. the Tours archive). Mirrors the
			// is_tax() pattern below: only override the generic 'archive' slice
			// when a per-type value is set, so sites that never set it are
			// unchanged.
			if ( is_post_type_archive() ) {
				$pt = get_query_var( 'post_type' );
				if ( is_array( $pt ) ) {
					$pt = reset( $pt );
				}
				if ( ! $pt ) {
					$queried = get_queried_object();
					$pt      = ( $queried instanceof WP_Post_Type ) ? $queried->name : '';
				}
				if ( $pt && ! empty( $display[ "{$pt}_archive" ] ) ) {
					$args['display'] = $display[ "{$pt}_archive" ];
					$args['_page']   = 'archive_' . $pt;
				}
			}
		}

		if ( is_tax() ) {
			$queried_object = get_queried_object();
			$tax            = $queried_object->taxonomy;
			// Use the queried taxonomy's OWN slice. Previously this assigned
			// $display['product_tag'] for any taxonomy, and assigned a title
			// string to ['display'] — a bug that stopped custom-taxonomy page
			// headers from working. Guarded on non-empty so an unset slice falls
			// through to the is_archive() defaults resolved above.
			if ( ! empty( $display[ $tax ] ) ) {
				$args['display'] = $display[ $tax ];
			}
			if ( isset( $titles[ $tax ] ) && '' !== $titles[ $tax ] ) {
				$args['title'] = $titles[ $tax ];
			}
			if ( isset( $taglines[ $tax ] ) && '' !== $taglines[ $tax ] ) {
				$args['tagline'] = $taglines[ $tax ];
			}
			$args['_page'] = 'tax_' . $queried_object->taxonomy;
		}

		// WooCommerce Settings.
		if ( Customify()->is_woocommerce_active() ) {
			if ( is_product() ) {
				$post_id         = wc_get_page_id( 'shop' );
				$args['display'] = $display['product'];
				$args['title']   = $titles['product'];
				$args['tagline'] = $taglines['product'];
				$args['_page']   = 'product';
				if ( $args['title'] || $args['tagline'] ) {
					$post_id = 0;
				}
			} elseif ( is_product_category() ) {
				$post_id         = 0;
				$args['display'] = $display['product_cat'];
				$args['title']   = get_the_archive_title();
				$args['tagline'] = get_the_archive_description();
				$args['_page']   = 'product_cat';
			} elseif ( is_product_tag() ) {
				$post_id         = 0;
				$args['display'] = $display['product_tag'];
				$args['title']   = get_the_archive_title();
				$args['tagline'] = get_the_archive_description();
				$args['_page']   = 'product_tag';
			} elseif ( is_shop() && ! is_search() ) {
				$args['display'] = $display['page'];
				$post_id         = wc_get_page_id( 'shop' );
				$args['_page']   = 'shop';
				$args['tagline'] = '';
			}
		}

		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$args['title'] = get_the_title( $post_id );
				if ( $post->post_excerpt ) {
					$args['tagline'] = get_the_excerpt( $post );
				}
				if ( ! $post_thumbnail_id ) {
					$post_thumbnail_id = get_post_thumbnail_id( $post_id );
				}
			}
		}

		if ( ! $args['image'] && $post_thumbnail_id ) {
			$_i = Customify()->get_media( $post_thumbnail_id );
			if ( $_i ) {
				$args['image'] = $_i;
			}
		}

		if ( Customify()->is_using_post() ) {
			$post_id = Customify()->get_current_post_id();

			// If Disable page title.
			$disable = get_post_meta( $post_id, '_customify_disable_page_title', true );
			if ( $disable ) {
				$args['disable_page_title'] = true;
			}

			// If has custom field custom title.
			$post_display = get_post_meta( $post_id, '_customify_page_header_display', true );
			if ( $post_display && 'default' != $post_display ) {
				if ( 'normal' == $post_display ) {
					$args['display'] = 'default';
				} else {
					$args['display'] = $post_display;
				}
			}

			// If has custom field custom title.
			$title = get_post_meta( $post_id, '_customify_page_header_title', true );
			if ( $title ) {
				$args['title'] = $title;
			}

			// If has custom field custom tagline.
			$tagline = trim( get_post_meta( $post_id, '_customify_page_header_tagline', true ) );
			if ( $tagline ) {
				$args['tagline'] = $tagline;
			}

			// If has custom field header media.
			$media = get_post_meta( $post_id, '_customify_page_header_image', true );
			if ( ! empty( $media ) ) {
				$image = Customify()->get_media( $media );
				if ( $image ) {
					$args['image'] = $image;
				}
			}

			// Has custom shortcode.
			$args['shortcode'] = trim( get_post_meta( $post_id, '_customify_page_header_shortcode', true ) );
			if ( $args['shortcode'] ) {
				$args['display'] = 'shortcode';
			}
		}

		if ( ! $args['display'] ) {
			$args['display'] = 'default';
		}

		self::$_settings = apply_filters( 'customify/page-header/get-settings', $args );

		return $args;
	}

	function display_page_title( $show ) {
		$args = $this->get_settings();

		if ( ! $args['display'] || 'default' == $args['display'] ) {
			$show = true;
		} elseif ( 'cover' == $args['display'] || 'titlebar' == $args['display'] || 'none' == $args['display'] ) {
			$show = false;
		}
		// disable_page_title meta wins over everything: hide inline title too.
		if ( ! empty( $args['disable_page_title'] ) ) {
			return false;
		}
		if ( 'hide' === $args['force_display_single_title'] ) {
			$show = false;
		} elseif ( 'show' === $args['force_display_single_title'] ) {
			$show = true;
		}

		return $show;
	}

	function render_cover( $args = array() ) {
		$args = $this->get_settings();

		extract( $args, EXTR_SKIP ); // phpcs:ignore

		$style = '';
		if ( $args['image'] ) {
			$style = ' style="background-image: url(\'' . esc_url( $args['image'] ) . '\')" ';
		}

		if ( ! $args['title_tag'] ) {
			$args['title_tag'] = 'h2';
		}

		$layout    = Customify()->get_setting_tab( 'page_header_layout' );
		$classes   = array( 'page-header--item page-cover' );
		$classes[] = $layout;

		?>
		<div id="page-cover" class="<?php echo esc_attr( join( ' ', $classes ) ); ?>"<?php echo $style; ?>>
			<div class="page-cover-inner customify-container">
				<?php
				do_action( 'customify/page-cover/before' );

				if ( Customify()->get_setting( 'header_cover_show_title' ) && empty( $args['disable_page_title'] ) ) {
					if ( $args['title'] ) {
						// WPCS: XSS ok.
						echo '<' . $args['title_tag'] . ' class="page-cover-title">' . apply_filters( 'customify_the_title', wp_kses_post( $args['title'] ) ) . '</' . $args['title_tag'] . '>';
					}
				}
				if ( $args['cover_tagline'] ) {
					if ( $args['tagline'] ) {
						// WPCS: XSS ok.
						echo '<div class="page-cover-tagline-wrapper"><div class="page-cover-tagline">' . apply_filters( 'customify_the_title', wp_kses_post( $args['tagline'] ) ) . '</div></div>';
					}
				}

				do_action( 'customify/page-cover/after' );
				?>
			</div>
		</div>
		<?php
	}

	function render_titlebar( $args = array() ) {
		$args = $this->get_settings();

		ob_start();
		/**
		 * Hook titlebar before
		 */
		do_action( 'customify/titlebar/before' );

		// WPCS: XSS ok.
		if ( Customify()->get_setting( 'titlebar_show_title' ) && empty( $args['disable_page_title'] ) ) {
			if ( $args['title'] ) {
				echo '<' . $args['title_tag'] . ' class="titlebar-title h4">' . apply_filters( 'customify_the_title', wp_kses_post( $args['title'] ) ) . '</' . $args['title_tag'] . '>';
			}
		}
		if ( $args['titlebar_tagline'] ) {
			if ( $args['tagline'] ) {
				// WPCS: XSS ok.
				echo '<div class="titlebar-tagline">' . apply_filters( 'customify_the_title', wp_kses_post( $args['tagline'] ) ) . '</div>';
			}
		}
		/**
		 * Hook titlebar after
		 */
		do_action( 'customify/titlebar/after' );
		$inner = ob_get_clean();

		if ( '' === trim( $inner ) ) {
			return;
		}

		$classes   = array( 'page-header--item page-titlebar' );
		$layout    = Customify()->get_setting_tab( 'page_header_layout' );
		$classes[] = $layout;
		?>
		<div id="page-titlebar" class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
			<div class="page-titlebar-inner customify-container">
				<?php echo $inner; // WPCS: XSS ok. ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Resolve which page-header element will render on the current request.
	 *
	 * Mirrors the gating logic of render() so callers (e.g. body_class filter)
	 * can know the outcome without triggering output.
	 *
	 * @return string  One of 'cover', 'titlebar', 'shortcode', or '' when nothing renders.
	 */
	function will_render() {
		$args = $this->get_settings();

		if ( 'none' === $args['display'] ) {
			return '';
		}

		// `_customify_disable_page_title` hides the title TEXT only, not the
		// whole #page-cover / #page-titlebar wrapper. get_settings() translates
		// that meta into `$args['disable_page_title'] = true`, and
		// render_cover()/render_titlebar() honour it to suppress just the
		// title echo. Returning '' here would also hide the cover background
		// image / titlebar breadcrumb strip, which are separate features.
		if ( in_array( $args['display'], array( 'cover', 'titlebar', 'shortcode' ), true ) ) {
			// Titlebar mode bails when there's no resolved title text. The
			// titlebar buffer also collects auxiliary content via the
			// `customify/titlebar/before|after` hooks (e.g. the breadcrumb
			// renderer when "Display Position = Inside cover/titlebar"), so
			// the existing "is buffer empty?" gate in render_titlebar() lets
			// the wrapper render with just a thin breadcrumb strip and no
			// title — which looks like a stray empty bar on landing pages
			// that intentionally omit the page title.
			//
			// Gating on `$args['title']` here makes the titlebar an
			// all-or-nothing element keyed off the page's primary title.
			// Callers (body_class, etc.) also benefit because will_render()
			// now reflects what render() actually outputs.
			if ( 'titlebar' === $args['display'] && '' === trim( (string) $args['title'] ) ) {
				return '';
			}
			return $args['display'];
		}

		return '';
	}

	function render() {
		$mode = $this->will_render();
		if ( ! $mode ) {
			return '';
		}

		$args = $this->get_settings();
		switch ( $mode ) {
			case 'cover':
				$this->render_cover( $args );
				break;
			case 'titlebar':
				$this->render_titlebar( $args );
				break;
			case 'shortcode':
				echo '<div class="page-header-shortcode">' . apply_filters( 'customify_the_content', $args['shortcode'] ) . '</div>';
				break;
		}

	}

}

Customify_Page_Header::get_instance();
