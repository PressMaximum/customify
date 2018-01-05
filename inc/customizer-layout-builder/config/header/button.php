<?php

class Customify_Builder_Item_Button{
    public $id = 'button';
    function item()
    {
        return array(
            'name' => __( 'Button', 'customify' ),
            'id' => 'button',
            'col' => 0,
            'width' => '4',
            'section' => 'header_button' // Customizer section to focus when click settings
        );
    }

    function customize(){
        $section = 'header_button';
        $prefix = 'header_button_';
        $fn = array( $this, 'render' );
        $selector = '.customify-builder-btn';
        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'title' => __( 'Button', 'customify' ),
            ),

            array(
                'name' => $prefix.'text',
                'type' => 'text',
                'section' => $section,
                'theme_supports' => '',
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Text', 'customify' ),
                'default'  => __( 'Button', 'customify' ),
            ),

            array(
                'name' => $prefix.'icon',
                'type' => 'icon',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'theme_supports' => '',
                'title'  => __( 'Icon', 'customify' ),
            ),

            array(
                'name' => $prefix.'link',
                'type' => 'text',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Link', 'customify' ),
            ),

            array(
                'name' => $prefix.'target',
                'type' => 'checkbox',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Target', 'customify' ),
                'checkbox_label'  => __( 'Open link in new window.', 'customify' ),
            ),

            /*
            array(
                'name' => $prefix.'style',
                'type' => 'select',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Style', 'customify' ),
                'choices' =>  array(
                    'style-1' => __( 'Default', 'customify' ),
                    'style-2' => __( 'Style 2', 'customify' ),
                )
            ),
            */

	        array(
		        'name' => $prefix.'text_options',
		        'type' => 'group',
		        'section' => $section,
		        'device_settings' => false,
		        'title' => __('Text Options', 'customify'),
		        'field_class' => 'customify-typography-control',
		        'selector' => $selector,
		        'css_format' => 'typography',
		        'default' => array(),
		        'fields' => array(
			        array(
				        'name' => 'font_style',
				        'type' => 'font_style',
				        'label' => __('Font Style', 'customify'),
			        ),

			        array(
				        'name' => 'font_size',
				        'type' => 'slider',
				        'min' => 7,
				        'max' => 20,
				        'step' => 1,
				        'label' => __('Font Size', 'customify'),
                        'device_settings' => true,
			        ),

			        array(
				        'name' => 'letter_spacing',
				        'type' => 'slider',
				        'label' => __('Letter Spacing', 'customify'),
				        'min' => -2,
				        'max' => 5,
				        'step' => .1,
			        ),
		        )
	        ),

            array(
                'name' => $prefix.'color',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'color: {{value}};',
                'selector' => $selector.', '.$selector.':visited',
                'title'  => __( 'Color', 'customify' ),
            ),

            array(
                'name' => $prefix.'color_hover',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'color: {{value}};',
                'selector' => $selector.':hover',
                'title'  => __( 'Color Hover', 'customify' ),
            ),

            array(
                'name' => $prefix.'bg_color',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'background-color: {{value}};',
                'selector' => $selector,
                'title'  => __( 'Background Color', 'customify' ),
            ),

            array(
                'name' => $prefix.'bg_color_hover',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'background-color: {{value}};',
                'selector' => $selector.':hover',
                'title'  => __( 'Background Color Hover', 'customify' ),
            ),

	        array(
		        'name' => $prefix.'border_radius',
		        'type' => 'slider',
		        'section' => $section,
		        'max' =>  100,
		        'default' =>  3,
		        'css_format' =>'border-radius: {{value}};',
		        'selector' => $selector,
		        'title'  => __( 'Border Radius', 'customify' ),
	        ),

            array(
                'name' => $prefix.'padding',
                'type' => 'css_ruler',
                'section' => $section,
                'css_format' => array(
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
                'selector' => $selector,
                'device_settings' => true,
                'title'  => __( 'Padding', 'customify' ),
            ),
	        array(
		        'name' => $prefix.'margin',
		        'type' => 'css_ruler',
		        'section' => $section,
		        'css_format' => array(
			        'top' => 'margin-top: {{value}};',
			        'right' => 'margin-right: {{value}};',
			        'bottom' => 'margin-bottom: {{value}};',
			        'left' => 'margin-left: {{value}};',
		        ),
		        'selector' => $selector,
		        'device_settings' => true,
		        'title'  => __( 'Margin', 'customify' ),
	        ),

            array(
                'name' => 'header_button_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'device_settings' => true,
                'selector' => '.builder-item--button',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
            ),

        );
        return $config;
    }


    function render(){
        $text = Customify_Customizer()->get_setting('header_button_text' );
        $icon = Customify_Customizer()->get_setting('header_button_icon' );
        $new_window = Customify_Customizer()->get_setting('header_button_target' );
        $link = Customify_Customizer()->get_setting('header_button_link' );
        //$style = sanitize_text_field( Customify_Customizer()->get_setting('header_button_style' ) );

        $classes = array('customify-btn customify-builder-btn');
//        if ( $style ){
//            $classes[]= $style;
//        }

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
            $icon_html = '<i class="'.esc_attr( $icon['icon'] ).'"></i> ';
        }

        echo '<a'.$target.' href="'.esc_url( $link ).'" class="'.esc_attr( join(" ", $classes ) ).'">'.$icon_html.esc_html( $text ).'</a>';
    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Button() );


