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
		$selector = '.header-social-icons.customify-builder-social-icons';
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
                'name'            => $prefix . '_size',
                'type'            => 'slider',
                'device_settings' => true,
                'unit'            => 'em',
                'section'         => $section,
                'min'             => 10,
                'step'            => 1,
                'max'             => 100,
                'selector'        => "$selector li a",
                'css_format'      => 'font-size: {{value}};',
                'label'           => __( 'Size', 'customify' ),
            ),

            array(
                'name'            => $prefix . '_padding',
                'type'            => 'slider',
                'device_settings' => true,
                'section'         => $section,
                'min'             => .1,
                'step'            => .1,
                'max'             => 5,
                'selector'        => "$selector li a",
                'unit'            => 'em',
                'css_format'      => 'padding: {{value_no_unit}}em;',
                'label'           => __( 'Padding', 'customify' ),
            ),

			array(
				'name'            => $prefix . '_spacing',
				'type'            => 'slider',
				'device_settings' => true,
                'unit'            => 'em',
				'section'         => $section,
				'min'             => 2,
				'max'             => 20,
				'selector'        => "$selector li",
				'css_format'      => 'margin-left: {{value}}; margin-right: {{value}};',
				'label'           => __( 'Icon Spacing', 'customify' ),
			),

            array(
                'name'             => $prefix . '_shape',
                'type'             => 'select',
                'section'          => $section,
                'selector'         => '.header-social-icons',
                'default'         => 'rounded',
                'render_callback'  => $fn,
                'title'            => __( 'Shape', 'customify' ),
                'choices'          => array(
                    'rounded' => __( 'Rounded', 'customify' ),
                    'square' => __( 'Square', 'customify' ),
                    'circle' => __( 'Circle', 'customify' ),
                    'none' => __( 'None', 'customify' ),
                ),
            ),

            array(
                'name'             => $prefix . '_color_type',
                'type'             => 'select',
                'section'          => $section,
                'selector'         => '.header-social-icons',
                'default'         => 'default',
                'render_callback'  => $fn,
                'title'            => __( 'Color', 'customify' ),
                'choices'          => array(
                    'default' => __( 'Official Color', 'customify' ),
                    'custom' => __( 'Custom', 'customify' ),
                ),
            ),

            array(
                'name'             => $prefix . '_custom_color',
                'type'             => 'modal',
                'section'          => $section,
                'selector'         => '.header-social-icons li a',
                'required' => array(    $prefix . '_color_type', '=', 'custom'),
                'css_format'       => 'styling',
                'title'            => __( 'Custom Color', 'customify' ),
                'fields' => array(
                    'tabs' => array(
                        'default'=> __('Normal', 'customify'),
                        'hover'=> __('Hover', 'customify'),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'primary',
                            'type' => 'color',
                            'label' => __('Background Color', 'customify'),
                            'selector'        => "$selector.color-custom li a",
                            'css_format' => 'background-color: {{value}};',
                        ),
                        array(
                            'name' => 'secondary',
                            'type' => 'color',
                            'label' => __('Icon Color', 'customify'),
                            'selector'        => "$selector.color-custom li a",
                            'css_format' => 'color: {{value}};',
                        ),
                    ),
                    'hover_fields' => array(
                        array(
                            'name' => 'primary',
                            'type' => 'color',
                            'label' => __('Background Color', 'customify'),
                            'selector'        => "$selector.color-custom li a:hover",
                            'css_format' => 'background-color: {{value}};',
                        ),
                        array(
                            'name' => 'secondary',
                            'type' => 'color',
                            'label' => __('Icon Color', 'customify'),
                            'selector'        => "$selector.color-custom li a:hover",
                            'css_format' => 'color: {{value}};',
                        ),
                    )
                )
            ),


            array(
                'name'             => $prefix . '_border',
                'type'             => 'modal',
                'section'          => $section,
                'selector'         => '.header-social-icons li a',
                'css_format'       => 'styling',
                'title'            => __( 'Border', 'customify' ),
                'description'      => __( 'Border & border radius', 'customify' ),
                'fields' => array(
                    'tabs' => array(
                        'default'=> '_',
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'border_style',
                            'type' => 'select',
                            'class' => 'clear',
                            'label' => __('Border Style', 'customify'),
                            'default' => 'none',
                            'choices' => array(
                                ''          => __('Default', 'customify'),
                                'none'      => __('None', 'customify'),
                                'solid'     => __('Solid', 'customify'),
                                'dotted'    => __('Dotted', 'customify'),
                                'dashed'    => __('Dashed', 'customify'),
                                'double'    => __('Double', 'customify'),
                                'ridge'     => __('Ridge', 'customify'),
                                'inset'     => __('Inset', 'customify'),
                                'outset'    => __('Outset', 'customify'),
                            ),
                            'css_format' => 'border-style: {{value}};',
                            'selector'        => "$selector li a",
                        ),

                        array(
                            'name' => 'border_width',
                            'type' => 'css_ruler',
                            'label' => __('Border Width', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector'        => "$selector li a",
                            'css_format' => array(
                                'top' => 'border-top-width: {{value}};',
                                'right' => 'border-right-width: {{value}};',
                                'bottom'=> 'border-bottom-width: {{value}};',
                                'left'=> 'border-left-width: {{value}};'
                            ),
                        ),
                        array(
                            'name' => 'border_color',
                            'type' => 'color',
                            'label' => __('Border Color', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector'        => "$selector li a",
                            'css_format' => 'border-color: {{value}};',
                        ),

                        array(
                            'name' => 'border_radius',
                            'type' => 'slider',
                            'label' => __('Border Radius', 'customify'),
                            'selector'        => "$selector li a",
                            'css_format' => 'border-radius: {{value}};',
                        ),
                    )
                )
            ),


		);

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
	}

	function render( $item_config ) {

		$shape = Customify_Customizer()->get_setting( 'header_social_icons_shape', 'all' );
		$color_type = Customify_Customizer()->get_setting( 'header_social_icons_color_type' );
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
			if ( $shape ) {
                $shape = ' shape-'.sanitize_text_field( $shape );
            }
            if ( $color_type ) {
                $classes[] = 'color-'.sanitize_text_field( $color_type );
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
						echo '<a class="social-'. str_replace( array( ' ', 'fa-fa' ), array( '-', 'icon' ), esc_attr( $icon['icon'] )) . $shape. '" '.$rel.'target="' . esc_attr( $target ) . '" href="' . esc_url( $item['url'] ) . '">';
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

