<?php

class Customify_Builder_Item_Social_Icons {
	public $id = 'social-icons';

	function item() {
		return array(
			'name'    => __( 'Social Icons', 'customify' ),
			'id'      => 'social-icons',
			'col'     => 0,
			'width'   => '4',
			'section' => 'header_social_icons' // Customizer section to focus when click settings
		);
	}

	function customize() {
		$section = 'header_social_icons';
		$prefix  = 'header_social_icons';
		$fn      = array( $this, 'render' );
		$config  = array(
			array(
				'name'           => $section,
				'type'           => 'section',
				'panel'          => 'header_settings',
				'theme_supports' => '',
				'title'          => __( 'Social Icons', 'customify' ),
			),

			array(
				'name'             => $prefix . '_items',
				'type'             => 'repeater',
				'section'          => $section,
				'selector'         => '.header-social-icons',
				'render_callback'  => $fn,
				'title'            => __( 'Social Profiles', 'customify' ),
				'live_title_field' => 'title',
				'limit'            => 4,
				'limit_msg'        => __( 'Just limit 4 item, Ability HTML here', 'customify' ),
				'default'          => array(),
				'fields'           => array(
					array(
						'name'  => 'title',
						'type'  => 'text',
						'label' => __( 'Title', 'customify' ),
					),
					array(
						'name'  => 'icon',
						'type'  => 'icon',
						'label' => __( 'Icon', 'customify' ),
					),

					array(
						'name'  => 'url',
						'type'  => 'text',
						'label' => __( 'URL', 'customify' ),
					),

				)
			),

			array(
				'name'            => $prefix . '_target',
				'type'            => 'checkbox',
				'section'         => $section,
				'selector'        => '.header-social-icons',
				'render_callback' => $fn,
				'default'         => 1,
				'checkbox_label'  => __( 'Open URL in new window.', 'customify' ),
			),
			array(
				'name'            => $prefix . '_nofollow',
				'type'            => 'checkbox',
				'section'         => $section,
				'render_callback' => $fn,
				'default'         => 1,
				'checkbox_label'  => __( 'Apply rel "nofollow" to social links.', 'customify' ),
			),

			array(
				'name'    => $prefix . '_predefined_style',
				'type'    => 'image_select',
				'default' => '1',
				'section' => $section,
				'title'   => __( 'Icon style', 'customify' ),
				'choices' => array(
					'1' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style1.svg',
					),
					'2' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style2.svg',
					),
					'3' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style3.svg',
					),
					'4' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style4.svg',
					),
					'5' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style5.svg',
					),
				)
			),

			array(
				'name'            => $prefix . '_size',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $section,
				'min'             => 10,
				'max'             => 150,
				'selector'        => '.header-social-icons i',
				'css_format'      => 'font-size: {{value}};',
				'render_callback' => $fn,
				'label'           => __( 'Icon Size', 'customify' ),
			),

			array(
				'name'    => $prefix . '_layout',
				'type'    => 'heading',
				'section' => $section,
				'title'   => __( 'Item Layout', 'customify' )
			),

			array(
				'name'            => 'header_social_icons_align',
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'device_settings' => true,
				'priority'        => 990,
				'selector'        => '.builder-first--social-icons',
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Align', 'customify' ),
			),

		);

		// Merge Item
		$config[] = customify_header_merge_item_settings( $this->id, $section );

		return $config;
	}

	function render( $item_config ) {

		$nofollow      = Customify_Customizer()->get_setting( 'header_social_icons_nofollow' );
		$rel = '';
		if ( $nofollow == 1 ) {
			$rel = 'rel="nofollow" ';
		}

		$target_blank = Customify_Customizer()->get_setting( 'header_social_icons_target' );
		$target       = '_self';
		if ( $target_blank == 1 ) {
			$target = '_blank';
		}

		$items = Customify_Customizer()->get_setting( 'header_social_icons_items' );
		if ( ! empty( $items ) ) {

			$classes   = array();

			echo '<ul class="header-social-icons customify-builder-social-icons">';
			foreach ( ( array ) $items as $index => $item ) {
				$item = wp_parse_args( $item, array(
					'title'       => '',
					'icon'        => '',
					'url'         => '',
					'_visibility' => ''
				) );

				if ( $item['_visibility'] !== 'hidden' ) {
					echo '<li>';
					if ( ! $item['url'] ) {
						$item['url'] = '#';
					}
					if ( $item['url'] ) {
						echo '<a '.$rel.'target="' . esc_attr( $target ) . '" href="' . esc_url( $item['url'] ) . '">';
					}

					$icon = wp_parse_args( $item['icon'], array(
						'type' => '',
						'icon' => '',
					) );

					if ( $icon['icon'] ) {
						echo '<i class="' . esc_attr( $icon['icon'] ) . '"></i>';
					}

					if ( $item['url'] ) {
						echo '</a>';
					}
				}
				echo '</li>';
			}

			echo '</ul>';
		}

	}

}

Customify_Customizer_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Social_Icons() );

