<?php
/**
 * Transparent Header — Customizer config, front-end class hooks, and CSS output.
 *
 * Ported from the Customify Pro module (header-transparent) with the following changes:
 * - Removed the Customify_Pro_Module_Base dependency.
 * - Removed the page-cover gate: transparent header activates on any page type.
 * - Uses 'customify' text domain.
 * - No sticky-JS asset loading (not part of this feature).
 */
class Customify_Header_Transparent {

	/** @var bool|null Cached result of is_transparent(). */
	private static $is_transparent = null;

	/** @var self|null */
	private static $_instance = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 6 );

		if ( ! is_admin() ) {
			add_filter( 'customify/builder/row-classes', array( $this, 'row_classes' ), 20, 3 );
			add_filter( 'body_class', array( $this, 'body_classes' ) );
			add_action( 'customizer/after-logo-img', array( $this, 'transparent_logo' ) );
			add_filter( 'customify/logo-classes', array( $this, 'logo_classes' ) );
		}
	}

	/**
	 * Build per-row config (checkbox + styling control).
	 */
	private function config_row( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'name' => '',
				'id'   => '',
			)
		);

		$section      = 'header_transparent';
		$row_selector = ".header--row.header-{$args['id']}.header--transparent";

		return array(
			array(
				'name'    => 'header_' . $args['id'] . '_transparent_h',
				'type'    => 'heading',
				'section' => $section,
				'title'   => $args['name'],
			),
			array(
				'name'           => 'header_' . $args['id'] . '_transparent',
				'type'           => 'checkbox',
				'section'        => $section,
				'checkbox_label' => sprintf( __( 'Transparent %s', 'customify' ), $args['name'] ),
			),
			array(
				'name'             => 'header_' . $args['id'] . '_transparent_styling',
				'type'             => 'styling',
				'section'          => $section,
				'title'            => __( 'Styling', 'customify' ),
				'description'      => sprintf( __( 'Transparent styling for %s', 'customify' ), $args['name'] ),
				'live_title_field' => 'title',
				'selector'         => array(
					'normal' => "{$row_selector} .customify-container, {$row_selector}.layout-full-contained, {$row_selector}.layout-fullwidth",
				),
				'css_format'       => 'styling',
				'fields'           => array(
					'normal_fields' => array(
						'padding'       => false,
						'margin'        => false,
						'text_color'    => false,
						'link_color'    => false,
						'bg_heading'    => false,
						'bg_cover'      => false,
						'bg_image'      => false,
						'bg_repeat'     => false,
						'border_radius' => false,
						'box_shadow'    => false,
					),
					'hover_fields'  => false,
				),
			),
		);
	}

	/**
	 * Merge transparent header settings into the global Customizer config.
	 */
	public function config( $configs ) {
		$section  = 'header_transparent';
		$selector = '.site-header .site-branding';

		$display_fields = array(
			array(
				'name'           => 'index',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on index', 'customify' ),
			),
			array(
				'name'           => 'category',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on categories', 'customify' ),
			),
			array(
				'name'           => 'search',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on search', 'customify' ),
			),
			array(
				'name'           => 'archive',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on archive', 'customify' ),
			),
			array(
				'name'           => 'page',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on single page', 'customify' ),
			),
			array(
				'name'           => 'post',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on single post', 'customify' ),
			),
			array(
				'name'           => 'singular',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on singular', 'customify' ),
			),
			array(
				'name'           => 'page_404',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on 404 page', 'customify' ),
			),
		);

		if ( Customify()->is_woocommerce_active() ) {
			$display_fields[] = array(
				'name'           => 'product',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on product page', 'customify' ),
			);
			$display_fields[] = array(
				'name'           => 'product_cat',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on product category', 'customify' ),
			);
			$display_fields[] = array(
				'name'           => 'product_tag',
				'type'           => 'checkbox',
				'checkbox_label' => __( 'Disable on product tag', 'customify' ),
			);
		}

		$config = array(
			array(
				'name'  => $section,
				'type'  => 'section',
				'panel' => 'header_settings',
				'title' => __( 'Transparent Header', 'customify' ),
			),
			array(
				'name'    => "{$section}_display_h",
				'type'    => 'heading',
				'section' => $section,
				'title'   => __( 'Advanced Settings', 'customify' ),
			),
			array(
				'name'        => "{$section}_display_pages",
				'type'        => 'modal',
				'section'     => $section,
				'label'       => __( 'Display', 'customify' ),
				'description' => __( 'Settings display for special pages.', 'customify' ),
				'default'     => array(),
				'fields'      => array(
					'tabs'           => array(
						'display' => __( 'Display', 'customify' ),
					),
					'display_fields' => $display_fields,
				),
			),
		);

		// Merge per-row settings before the section-level settings.
		$rows = array(
			array( 'id' => 'top',    'name' => __( 'Header Top', 'customify' ) ),
			array( 'id' => 'main',   'name' => __( 'Header Main', 'customify' ) ),
			array( 'id' => 'bottom', 'name' => __( 'Header Bottom', 'customify' ) ),
		);

		$row_configs = array();
		foreach ( $rows as $arg ) {
			$row_configs = array_merge( $row_configs, $this->config_row( $arg ) );
		}
		$config = array_merge( $row_configs, $config );

		// Transparent logo controls.
		$render_logo_cb_el = array(
			Customify_Customize_Layout_Builder()->get_builder_item( 'header', 'logo' ),
			'render',
		);

		$config[] = array(
			'name'            => 'header_logo_tran',
			'type'            => 'image',
			'section'         => $section,
			'device_settings' => false,
			'selector'        => $selector,
			'render_callback' => $render_logo_cb_el,
			'priority'        => 820,
			'title'           => __( 'Transparent Logo', 'customify' ),
		);

		$config[] = array(
			'name'            => 'header_logo_tran_retina',
			'type'            => 'image',
			'section'         => $section,
			'device_settings' => false,
			'selector'        => $selector,
			'render_callback' => $render_logo_cb_el,
			'priority'        => 825,
			'title'           => __( 'Transparent Logo Retina', 'customify' ),
		);

		$config[] = array(
			'name'            => 'logo_tran_max_width',
			'type'            => 'slider',
			'section'         => $section,
			'default'         => array(),
			'max'             => 400,
			'priority'        => 830,
			'device_settings' => true,
			'title'           => __( 'Transparent Logo Max Width', 'customify' ),
			'selector'        => $selector . ' img.site-img-logo-tran',
			'css_format'      => 'max-width: {{value}};',
		);

		return array_merge( $configs, $config );
	}

	/**
	 * Add 'header--transparent' CSS class to each row when transparent is active.
	 */
	public function row_classes( $classes, $row_id, $builder ) {
		if ( $builder->get_id() === 'header' && $this->is_transparent() ) {
			if ( Customify()->get_setting( "header_{$row_id}_transparent" ) ) {
				$classes['transparent'] = 'header--transparent';
			}
		}
		return $classes;
	}

	/**
	 * Determine whether the transparent header should be active on the current page.
	 *
	 * Checks:
	 *   1. At least one header row has its transparent setting enabled.
	 *   2. The page type is not in the "disable on" list.
	 *   3. The per-page post meta does not force it off (or forces it on).
	 */
	public function is_transparent( $force = false ) {
		if ( self::$is_transparent === null || $force ) {
			$is_tran = false;

			foreach ( array( 'top', 'main', 'bottom' ) as $row_id ) {
				if ( Customify()->get_setting( "header_{$row_id}_transparent" ) ) {
					$is_tran = true;
					break;
				}
			}

			if ( $is_tran ) {
				$display = Customify()->get_setting_tab( 'header_transparent_display_pages', 'display' );
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

				$hide = false;

				if ( is_front_page() && is_home() ) {
					$hide = $display['index'];
				} elseif ( is_front_page() || is_home() ) {
					$hide = $display['page'];
				} elseif ( is_category() ) {
					$hide = $display['category'];
				} elseif ( is_page() ) {
					$hide = $display['page'];
				} elseif ( is_single() ) {
					$hide = $display['post'];
				} elseif ( is_singular() ) {
					$hide = $display['singular'];
				} elseif ( is_404() ) {
					$hide = $display['page_404'];
				} elseif ( is_search() ) {
					$hide = $display['search'];
				} elseif ( is_archive() ) {
					$hide = $display['archive'];
				}

				if ( Customify()->is_woocommerce_active() ) {
					if ( is_product() ) {
						$hide = $display['product'];
					} elseif ( is_product_category() ) {
						$hide = $display['product_cat'];
					} elseif ( is_product_tag() ) {
						$hide = $display['product_tag'];
					} elseif ( is_shop() ) {
						$hide = $display['page'];
					}
				}

				if ( $hide ) {
					$is_tran = false;
				}

				// Per-page post meta override.
				if ( Customify()->is_using_post() ) {
					$id = Customify()->get_current_post_id();
					if ( $id ) {
						$meta = get_post_meta( $id, '_customify_header_transparent_display', true );
						if ( $meta === 'hide' ) {
							$is_tran = false;
						} elseif ( $meta === 'show' ) {
							$is_tran = true;
						}
					}
				}
			}

			self::$is_transparent = apply_filters( 'customify/render_header/is-transparent', $is_tran );
		}

		return self::$is_transparent;
	}

	/**
	 * Add 'is-header-transparent' body class.
	 */
	public function body_classes( $classes ) {
		if ( $this->is_transparent() ) {
			$classes['header_transparent'] = 'is-header-transparent';
		}
		return $classes;
	}

	/**
	 * Render the transparent logo <img> after the default logo.
	 */
	public function transparent_logo() {
		$logo_id           = Customify()->get_setting( 'header_logo_tran' );
		$logo_image        = Customify()->get_media( $logo_id, 'full' );
		$logo_retina       = Customify()->get_setting( 'header_logo_tran_retina' );
		$logo_retina_image = Customify()->get_media( $logo_retina );

		if ( $logo_image ) {
			$retina_attr = $logo_retina_image ? ' srcset="' . esc_url( $logo_retina_image ) . ' 2x"' : '';
			printf(
				'<img class="site-img-logo-tran" src="%s" alt="%s"%s>',
				esc_url( $logo_image ),
				esc_attr( get_bloginfo( 'name' ) ),
				$retina_attr
			);
		}
	}

	/**
	 * Add has-tran-logo / no-tran-logo class to the branding element.
	 */
	public function logo_classes( $classes ) {
		$logo_id    = Customify()->get_setting( 'header_logo_tran' );
		$logo_image = Customify()->get_media( $logo_id, 'full' );

		$classes[] = $logo_image ? 'has-tran-logo' : 'no-tran-logo';

		return $classes;
	}
}

// Pro's Header Transparent module owns the feature when the plugin is
// active so its dashboard toggle works normally (defaults ON, user can
// turn off). Theme's port runs only as the Free fallback. Hook on
// after_setup_theme so Pro has finished loading its module classes.
add_action(
	'after_setup_theme',
	function () {
		if ( class_exists( 'Customify_Pro_Module_Header_Transparent' ) ) {
			return;
		}
		Customify_Header_Transparent::get_instance();
	},
	30
);
