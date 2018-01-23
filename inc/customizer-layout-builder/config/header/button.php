<?php

class Customify_Builder_Item_Button {
	public $id = 'button';

	function item() {
		return array(
			'name'    => __( 'Button', 'customify' ),
			'id'      => 'button',
			'col'     => 0,
			'width'   => '4',
			'section' => 'header_button' // Customizer section to focus when click settings
		);
	}

	function customize() {
		$section  = 'header_button';
		$prefix   = 'header_button';
		$fn       = array( $this, 'render' );
		$selector = '.customify-builder-btn';
		$config   = array(
			array(
				'name'  => $section,
				'type'  => 'section',
				'panel' => 'header_settings',
				'title' => __( 'Button', 'customify' ),
			),

			array(
				'name'            => $prefix . '_text',
				'type'            => 'text',
				'section'         => $section,
				'theme_supports'  => '',
				'selector'        => $selector,
				'render_callback' => $fn,
				'title'           => __( 'Text', 'customify' ),
				'default'         => __( 'Button', 'customify' ),
			),

			array(
				'name'            => $prefix . '_icon',
				'type'            => 'icon',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'theme_supports'  => '',
				'title'           => __( 'Icon', 'customify' ),
			),

			array(
				'name'            => $prefix . '_position',
				'type'            => 'radio_group',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'default'         => 'before',
				'title'           => __( 'Icon Position', 'customify' ),
				'choices'         => array(
					'before' => __( 'Before', 'customify' ),
					'after'  => __( 'After', 'customify' ),
				)
			),

			array(
				'name'            => $prefix . '_link',
				'type'            => 'text',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'title'           => __( 'Link', 'customify' ),
			),

			array(
				'name'            => $prefix . '_target',
				'type'            => 'checkbox',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'checkbox_label'  => __( 'Open link in new window.', 'customify' ),
			),

			array(
				'name'            => $prefix . '_padding',
				'type'            => 'css_ruler',
				'section'         => $section,
				'device_settings' => true,
				'css_format'      => array(
					'top'    => 'padding-top: {{value}};',
					'right'  => 'padding-right: {{value}};',
					'bottom' => 'padding-bottom: {{value}};',
					'left'   => 'padding-left: {{value}};',
				),
				'selector'        => $selector,
				'label'           => __( 'Padding', 'customify' ),
			),

			array(
				'name'       => $prefix . '_border_radius',
				'type'       => 'slider',
				'section'    => $section,
				'min'        => 0,
				'max'        => 100,
				'css_format' => 'border-radius: {{value}};',
				'selector'   => $selector,
				'title'      => __( 'Border Radius', 'customify' )
			),

            array(
                'name' => $section.'_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'selector' => $selector,
                'device_settings' => true,
                'css_format' => array(
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
                'title' => __( 'Padding', 'customify' ),
            ),

			array(
				'name'            => $prefix . '_typography',
				'type'            => 'typography',
				'section'         => $section,
				'title'           => __( 'Typography', 'customify' ),
				'field_class'     => 'customify-typography-control',
				'selector'        => $selector,
				'css_format'      => 'typography',
				'default'         => array(),
			),

			array(
				'name'        => $prefix . '_styling',
				'type'        => 'styling',
				'section'     => $section,
				'title'       => __( 'Styling', 'customify' ),
				'field_class' => 'customify-typography-control',
				'selector'    => array(
				    'normal' => $selector,
				    'hover' => $selector.':hover',
                ),
				'css_format'  => 'styling',
				'default'     => array(),
				'fields'     => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image' => false,
                        'bg_cover' => false,
                        'bg_position' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                    ),
                    'hover_fields' => array(
                        'link_color' => false, // disable for special field.
                    )
                ),
			),

		);

		// Item Layout
		return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
	}


	function render() {
		$text          = Customify_Customizer()->get_setting( 'header_button_text' );
		$icon          = Customify_Customizer()->get_setting( 'header_button_icon' );
		$new_window    = Customify_Customizer()->get_setting( 'header_button_target' );
		$link          = Customify_Customizer()->get_setting( 'header_button_link' );
		$icon_position = Customify_Customizer()->get_setting( 'header_button_position' );

		$classes = array( 'customify-btn customify-builder-btn' );

		$icon = wp_parse_args( $icon, array(
			'type' => '',
			'icon' => ''
		) );

		$target = '';
		if ( $new_window == 1 ) {
			$target = ' target="_blank" ';
		}

		$icon_html = '';
		if ( $icon['icon'] ) {
			$icon_html = '<i class="' . esc_attr( $icon['icon'] ) . '"></i> ';
		}
		$classes[] = 'is-icon-' . $icon_position;

		echo '<a' . $target . ' href="' . esc_url( $link ) . '" class="' . esc_attr( join( " ", $classes ) ) . '">';
		if ( $icon_position != 'after' ) {
			echo $icon_html . esc_html( $text );
		} else {
			echo esc_html( $text ) . $icon_html;
		}
		echo '</a>';
	}
}

Customify_Customizer_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Button() );


