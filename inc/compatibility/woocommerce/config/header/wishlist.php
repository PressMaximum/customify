<?php
/**
 * Header builder item: Wishlist (heart icon + count badge).
 *
 * Item id `wc_wishlist_counter`. Deliberately NOT `wc_wishlist`: that id and
 * its `wc_wishlist_*` options belong to Customify Pro's older Wishlist item
 * (Font Awesome icon shapes, TI-only). The builder keys items by id and the
 * last registration wins, so reusing it would silently swap Pro's item out
 * from under sites that placed it — and read Pro's saved option shapes.
 *
 * The count comes from the wishlist provider layer
 * (inc/compatibility/woocommerce/inc/wishlist.php) and is filled client-side
 * (src/frontend/js/compatibility/wishlist-counter.js); the markup never
 * carries a number, so full-page caches stay safe.
 *
 * @package customify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Customify_Builder_Item_WC_Wishlist_Counter
 */
class Customify_Builder_Item_WC_Wishlist_Counter {
	/**
	 * @var string Item Id.
	 */
	public $id = 'wc_wishlist_counter';
	/**
	 * @var string Section ID.
	 */
	public $section = 'wc_wishlist_counter';
	/**
	 * @var string Setting name prefix.
	 */
	public $name = 'wc_wishlist_counter';
	/**
	 * @var string Item label.
	 */
	public $label = '';
	/**
	 * @var int Priority.
	 */
	public $priority = 205;
	/**
	 * @var string Panel ID.
	 */
	public $panel = 'header_settings';

	/**
	 * Customify_Builder_Item_WC_Wishlist_Counter constructor.
	 */
	public function __construct() {
		$this->label = __( 'Wishlist', 'customify' );
	}

	/**
	 * Builder label. Customify Pro registers its own item called "Wishlist"
	 * (`wc_wishlist`); when that class is loaded, name this one "Wishlist
	 * Counter" so the two are distinguishable in the builder item list.
	 *
	 * @return string
	 */
	protected function get_label() {
		if ( class_exists( 'Customify_Builder_Item_WC_Wishlist' ) ) {
			return __( 'Wishlist Counter', 'customify' );
		}

		return $this->label;
	}

	/**
	 * Register Builder item
	 *
	 * @return array
	 */
	public function item() {
		return array(
			'name'    => $this->get_label(),
			'id'      => $this->id,
			'col'     => 0,
			'width'   => '2',
			'section' => $this->section, // Customizer section to focus when click settings.
		);
	}

	/**
	 * Register customize section and settings.
	 *
	 * @return array
	 */
	public function customize() {
		$fn       = array( $this, 'render' );
		$selector = '.builder-header-' . $this->id . '-item';
		$config   = array(
			array(
				'name'     => $this->section,
				'type'     => 'section',
				'panel'    => $this->panel,
				'priority' => $this->priority,
				'title'    => $this->get_label(),
			),
		);

		if ( ! customify_wc_wishlist()->get_active_provider() ) {
			$config[] = array(
				'name'        => "{$this->name}_provider_notice",
				'type'        => 'custom_html',
				'section'     => $this->section,
				'description' => __( 'This item shows the wishlist of <a target="_blank" href="https://wordpress.org/plugins/ti-woocommerce-wishlist/">TI WooCommerce Wishlist</a> or <a target="_blank" href="https://wordpress.org/plugins/yith-woocommerce-wishlist/">YITH WooCommerce Wishlist</a>. Install and activate one of them: until then the item is only visible here in the Customizer preview.', 'customify' ),
			);
		}

		$config = array_merge(
			$config,
			array(
				array(
					'name'            => "{$this->name}_text",
					'type'            => 'text',
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					'title'           => __( 'Label', 'customify' ),
					'default'         => __( 'Wishlist', 'customify' ),
				),

				array(
					'name'            => "{$this->name}_icon",
					'type'            => 'icon',
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					// Hearts first, then the other "saved" silhouettes.
					'presets'         => array( 'heart', 'heart-outline', 'heart-filled', 'heart-plus', 'bookmark', 'bookmark-filled', 'star', 'star-filled' ),
					// A new item has no saved values anywhere, so the modern
					// inline-SVG default needs no install-marker gate.
					'default'         => array(
						'icon' => 'heart',
						'type' => 'svg',
						'svg'  => '',
					),
					'title'           => __( 'Icon', 'customify' ),
				),

				array(
					'name'            => "{$this->name}_icon_position",
					'type'            => 'select',
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					'default'         => 'before',
					'choices'         => array(
						'before' => __( 'Before', 'customify' ),
						'after'  => __( 'After', 'customify' ),
					),
					'title'           => __( 'Icon Position', 'customify' ),
				),

				array(
					'name'            => "{$this->name}_show_label",
					'type'            => 'checkbox',
					// Icon-only by default, like the reference header design.
					'default'         => array(
						'desktop' => 0,
						'tablet'  => 0,
						'mobile'  => 0,
					),
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					'theme_supports'  => '',
					'label'           => __( 'Show Label', 'customify' ),
					'checkbox_label'  => __( 'Show Label', 'customify' ),
					'device_settings' => true,
				),

				array(
					'name'            => "{$this->name}_show_counter",
					'type'            => 'checkbox',
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					'default'         => 1,
					'label'           => __( 'Counter', 'customify' ),
					'checkbox_label'  => __( 'Show Counter', 'customify' ),
				),

				array(
					'name'            => "{$this->name}_hide_zero",
					'type'            => 'checkbox',
					'section'         => $this->section,
					'selector'        => $selector,
					'render_callback' => $fn,
					'default'         => 1,
					'checkbox_label'  => __( 'Hide Counter When Empty', 'customify' ),
					'required'        => array( "{$this->name}_show_counter", '=', 1 ),
				),

				array(
					'name'       => "{$this->name}_label_styling",
					'type'       => 'styling',
					'section'    => $this->section,
					'title'      => __( 'Styling', 'customify' ),
					'selector'   => array(
						'normal' => $selector . ' .wishlist-counter-link',
						'hover'  => $selector . ':hover .wishlist-counter-link',
					),
					'css_format' => 'styling',
					'default'    => array(),
					'fields'     => array(
						'normal_fields' => array(
							'link_color'    => false, // disable for special field.
							'margin'        => false,
							'bg_image'      => false,
							'bg_cover'      => false,
							'bg_position'   => false,
							'bg_repeat'     => false,
							'bg_attachment' => false,
						),
						'hover_fields'  => array(
							'link_color' => false, // disable for special field.
						),
					),
				),

				array(
					'name'       => "{$this->name}_typography",
					'type'       => 'typography',
					'section'    => $this->section,
					'title'      => __( 'Typography', 'customify' ),
					'selector'   => $selector . ' .wishlist-counter-link',
					'css_format' => 'typography',
					'default'    => array(),
				),

				array(
					'name'    => "{$this->name}_icon_h",
					'type'    => 'heading',
					'section' => $this->section,
					'title'   => __( 'Icon Settings', 'customify' ),
				),

				array(
					'name'            => "{$this->name}_icon_size",
					'type'            => 'slider',
					'section'         => $this->section,
					'device_settings' => true,
					'max'             => 150,
					'title'           => __( 'Icon Size', 'customify' ),
					// Same two-declaration pattern as the Cart item (see
					// docs/SPEC-icons.md §9.1): `font-size` sizes a Font Awesome
					// `<i>`, the custom property sizes the inline `<svg>` so its
					// default can track the shared header icon token exactly.
					'selector'        => $selector . ' .wishlist-counter-icon',
					'css_format'      => 'font-size: {{value}}; --customify-wishlist-icon-size: {{value}};',
					'default'         => array(),
				),

				array(
					'name'        => "{$this->name}_icon_styling",
					'type'        => 'styling',
					'section'     => $this->section,
					'title'       => __( 'Styling', 'customify' ),
					'description' => __( 'Advanced styling for wishlist icon', 'customify' ),
					'selector'    => array(
						'normal' => $selector . ' .wishlist-counter-icon i, ' . $selector . ' .wishlist-counter-icon svg',
						'hover'  => $selector . ':hover .wishlist-counter-icon i, ' . $selector . ':hover .wishlist-counter-icon svg',
					),
					'css_format'  => 'styling',
					'default'     => array(),
					'fields'      => array(
						'normal_fields' => array(
							'link_color'    => false, // disable for special field.
							'bg_image'      => false,
							'bg_cover'      => false,
							'bg_position'   => false,
							'bg_repeat'     => false,
							'bg_attachment' => false,
						),
						'hover_fields'  => array(
							'link_color' => false, // disable for special field.
						),
					),
				),

				array(
					'name'        => "{$this->name}_counter_styling",
					'type'        => 'styling',
					'section'     => $this->section,
					'title'       => __( 'Counter', 'customify' ),
					'description' => __( 'Advanced styling for wishlist counter', 'customify' ),
					'selector'    => array(
						'normal' => $selector . ' .wishlist-counter-icon .wishlist-counter-count',
						'hover'  => $selector . ':hover .wishlist-counter-icon .wishlist-counter-count',
					),
					'css_format'  => 'styling',
					'default'     => array(),
					'fields'      => array(
						'normal_fields' => array(
							'link_color'    => false, // disable for special field.
							'bg_image'      => false,
							'bg_cover'      => false,
							'bg_position'   => false,
							'bg_repeat'     => false,
							'bg_attachment' => false,
						),
						'hover_fields'  => array(
							'link_color' => false, // disable for special field.
						),
					),
				),
			)
		);

		// Item Layout.
		return array_merge( $config, customify_header_layout_settings( $this->id, $this->section ) );
	}

	/**
	 * Turn a device checkbox value into show/hide classes.
	 *
	 * @param array|string $array  Device => 0|1 map, or a scalar.
	 * @param string       $prefix Class prefix.
	 *
	 * @return string
	 */
	protected function device_classes( $array, $prefix ) {
		if ( ! is_array( $array ) ) {
			return $prefix . '-' . ( $array ? 'show' : 'hide' );
		}
		$classes = array();
		foreach ( array_reverse( $array ) as $device => $value ) {
			$classes[] = $prefix . '-' . sanitize_html_class( $device ) . '-' . ( $value ? 'show' : 'hide' );
		}

		return join( ' ', $classes );
	}

	/**
	 * Render item content.
	 */
	public function render() {
		$wishlist = customify_wc_wishlist();
		$provider = $wishlist->get_active_provider();

		// Without a wishlist plugin there is nothing to link to or count.
		// The Customizer preview still gets the item so it can be placed and
		// styled before the plugin is installed.
		if ( ! $provider && ! is_customize_preview() ) {
			return;
		}

		$icon          = Customify()->get_setting( "{$this->name}_icon" );
		$icon_position = Customify()->get_setting( "{$this->name}_icon_position" );
		$text          = (string) Customify()->get_setting( "{$this->name}_text" );
		$show_label    = Customify()->get_setting( "{$this->name}_show_label", 'all' );
		$show_counter  = Customify()->get_setting( "{$this->name}_show_counter" );
		$hide_zero     = Customify()->get_setting( "{$this->name}_hide_zero" );

		$url = $provider ? $wishlist->get_url( $provider ) : '';
		if ( '' === $url ) {
			$url = '#';
		}

		$icon_html = customify_render_icon(
			wp_parse_args(
				$icon,
				array(
					'type' => '',
					'icon' => '',
					'svg'  => '',
				)
			)
		);

		$badge = '';
		if ( $show_counter ) {
			$badge_classes = trim( 'wishlist-counter-count ' . ( $provider ? $provider['badge_class'] : '' ) );
			// Printed EMPTY on purpose (cache-safe); filled client-side.
			$badge = '<span class="' . esc_attr( $badge_classes ) . '" aria-hidden="true"></span>';
			$wishlist->enqueue_provider_scripts();
		}

		$html_icon = '';
		if ( '' !== $icon_html || '' !== $badge ) {
			$html_icon = '<span class="wishlist-counter-icon">' . $icon_html . $badge . '</span>';
		}

		$html_label = '';
		$label_text = '' !== trim( $text ) ? sanitize_text_field( $text ) : __( 'Wishlist', 'customify' );
		if ( '' !== trim( $text ) ) {
			$html_label = '<span class="wishlist-counter-label ' . esc_attr( $this->device_classes( $show_label, 'wishlist-counter' ) ) . '">' . esc_html( $label_text ) . '</span>';
		}

		$inner = 'after' === $icon_position ? $html_label . $html_icon : $html_icon . $html_label;

		$classes = array(
			'builder-header-' . $this->id . '-item',
			'item--' . $this->id,
		);
		if ( $show_counter ) {
			$classes[] = $hide_zero ? 'wishlist-counter--hide-zero' : 'wishlist-counter--show-zero';
		}

		printf(
			'<div class="%1$s" data-wishlist-provider="%2$s"><a href="%3$s" class="wishlist-counter-link link-meta" aria-label="%4$s" data-label="%4$s">%5$s</a></div>',
			esc_attr( join( ' ', $classes ) ),
			esc_attr( $provider ? $provider['id'] : '' ),
			esc_url( $url ),
			esc_attr( $label_text ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from customify_render_icon(), the rest escaped above.
		);
	}
}

Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_WC_Wishlist_Counter() );
