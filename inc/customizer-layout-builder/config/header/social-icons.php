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
				'name'    => $prefix . '_preset',
				'type'    => 'image_select',
				'section' => $section,
				'render_callback' => $fn,
				'title'   => __( 'Icon Preset', 'customify' ),
				'selector'=> '.header-social-icons',
				'device_settings' => true,
				'default'         => array (
					'desktop' => 'plain',
					'tablet' => 'plain',
					'mobile' => 'plain',
				),
				'choices' => array(
					'plain' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style1.svg',
					),
					'outline-square' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style2.svg',
					),
					'fill-square' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style3.svg',
					),
					'fill-rounded' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style4.svg',
					),
					'outline-rounded' => array(
						'img' => get_template_directory_uri() . '/assets/images/customizer/social_icon_style5.svg',
					),
				)
			),
			array(
				'name'            => $prefix . '_size',
				'type'            => 'radio_group',
				'section'         => $section,
				'render_callback' => $fn,
				'title'           => __( 'Icon Size', 'customify' ),
				'selector'        => '.header-social-icons',
				'default'         => array (
					'desktop' => 'medium',
					'tablet' => 'medium',
					'mobile' => 'medium',
				),
				'device_settings' => true,
				'choices'         => array(
					's'       => __( 'Small', 'customify' ),
					'medium'  => __( 'Medium', 'customify' ),
					'l'       => __( 'Large', 'customify' ),
					'xl'      => __( 'X-Large', 'customify' ),
				)
			),
			array(
				'name'            => $prefix . '_spacing',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $section,
				'min'             => 2,
				'max'             => 20,
				'selector'        => '.header-social-icons li',
				'css_format'      => 'margin-left: {{value}}; margin-right: {{value}};',
				'label'           => __( 'Icon Spacing', 'customify' ),
			),

		);

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
	}

	function render( $item_config ) {

		$preset = Customify_Customizer()->get_setting( 'header_social_icons_preset', 'all' );
		$sizes = Customify_Customizer()->get_setting( 'header_social_icons_size', 'all' );
		$items = Customify_Customizer()->get_setting( 'header_social_icons_items' );
		$nofollow      = Customify_Customizer()->get_setting( 'header_social_icons_nofollow' );
		$target_blank = Customify_Customizer()->get_setting( 'header_social_icons_target' );

		$rel = '';
		if ( $nofollow == 1 ) {
			$rel = 'rel="nofollow" ';
		}

		$target       = '_self';
		if ( $target_blank == 1 ) {
			$target = '_blank';
		}

		if ( ! empty( $items ) ) {

			$classes   = array( 'header-social-icons customify-builder-social-icons' );
			if ( $preset ) {
			    if ( is_array( $preset ) ) {
                    foreach ( $preset as $d => $s ) {
                        $classes[] = 'is-style-'.$d.'-'.$s;
                    }
                } else {
                    $classes[] = 'is-style-'.$preset;
                }
			}
            
			if ( ! empty( $sizes ) ) {
			    if ( is_string( $sizes ) ) {
                    $classes[] = 'is-size-'.$sizes;
                } else {
			        foreach ( $sizes as $d => $s ) {
			            if (! is_string( $s ) ) {
                            $s = 'medium';
                        }
                        $classes[] = 'is-size-'.$d.'-'.$s;
                    }

                }

			}

			echo '<ul class="' . esc_attr( join( " ", $classes ) ) . '">';
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

					$icon = wp_parse_args( $item['icon'], array(
						'type' => '',
						'icon' => '',
					) );

					if ( $item['url'] && $icon['icon'] ) {
						echo '<a class="is-'. str_replace( ' ', '', esc_attr( $icon['icon'] )) .'" '.$rel.'target="' . esc_attr( $target ) . '" href="' . esc_url( $item['url'] ) . '">';
					}

					if ( $icon['icon'] ) {
						echo '<i class="icon ' . esc_attr( $icon['icon'] ) . '"></i>';
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

