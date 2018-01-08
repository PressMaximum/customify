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
        $prefix = 'header_button';
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
                'name' => $prefix.'_text',
                'type' => 'text',
                'section' => $section,
                'theme_supports' => '',
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Text', 'customify' ),
                'default'  => __( 'Button', 'customify' ),
            ),

            array(
                'name' => $prefix.'_icon',
                'type' => 'icon',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'theme_supports' => '',
                'title'  => __( 'Icon', 'customify' ),
            ),

            array(
                'name' => $prefix.'_position',
                'type' => 'radio_group',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'default' => 'before',
                'title'  => __( 'Icon Position', 'customify' ),
                'choices' => array(
                    'before' => __( 'Before', 'customify' ),
                    'after' => __( 'After', 'customify' ),
                )
            ),

            array(
                'name' => $prefix.'_link',
                'type' => 'text',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title'  => __( 'Link', 'customify' ),
            ),

            array(
                'name' => $prefix.'_target',
                'type' => 'checkbox',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'default' => 1,
                'checkbox_label'  => __( 'Open link in new window.', 'customify' ),
            ),

	        array(
		        'name' => $prefix.'_nofollow',
		        'type' => 'checkbox',
		        'section' => $section,
		        'selector' => $selector,
		        'render_callback' => $fn,
		        'default' => 1,
		        'checkbox_label'  => __( 'Apply rel "nofollow" to social links.', 'customify' ),
	        ),

	        array(
		        'name' => $prefix.'_padding',
		        'type' => 'css_ruler',
		        'section' => $section,
		        'device_settings' => true,
		        'css_format' => array(
			        'top' => 'padding-top: {{value}};',
			        'right' => 'padding-right: {{value}};',
			        'bottom' => 'padding-bottom: {{value}};',
			        'left' => 'padding-left: {{value}};',
		        ),
		        'selector' => $selector,
		        'label'  => __( 'Padding', 'customify' ),
	        ),

	        array(
		        'name' => $prefix.'_border_radius',
		        'type' => 'slider',
		        'section' => $section,
		        'min' => 0,
		        'max' =>  100,
		        'css_format' =>'border-radius: {{value}};',
		        'selector' => $selector,
		        'title'  => __( 'Border Radius', 'customify' )
	        ),


	        array(
                'name' => $prefix.'_typography',
                'type' => 'group',
                'section' => $section,
                'device_settings' => false,
                'title' => __('Typography', 'customify'),
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
                'name' => $prefix.'_styling',
                'type' => 'group',
                'section' => $section,
                'title' => __('Styling', 'customify'),
                'field_class' => 'customify-typography-control',
                'selector' => $selector,
                'css_format' => 'styling',
                'default' => array(),
                'fields' => array(
                    array(
                        'name' => 'text_color',
                        'type' => 'color',
                        'label' => __('Text Color', 'customify'),
                        'selector' => $selector,
                        'css_format' => 'color: {{value}};',
                    ),

                    array(
                        'name' => 'color', // Background color
                        'type' => 'color',
                        'label' => __('Background Color', 'customify'),
                    ),

                    array(
                        'name' => 'border_style',
                        'type' => 'select',
                        'label' => __('Border Style', 'customify'),
                        'default' => 'none',
                        'choices' => array(
                            'none'      => __('None', 'customify'),
                            'solid'     => __('Solid', 'customify'),
                            'dotted'    => __('Dotted', 'customify'),
                            'dashed'    => __('Dashed', 'customify'),
                            'double'    => __('Double', 'customify'),
                            'ridge'     => __('Ridge', 'customify'),
                            'inset'     => __('Inset', 'customify'),
                            'outset'    => __('Outset', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'border_width',
                        'type' => 'css_ruler',
                        'label' => __('Border Width', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),
                    array(
                        'name' => 'border_color',
                        'type' => 'color',
                        'label' => __('Border Color', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),

                )
            ),

            array(
                'name' => $prefix.'_hover',
                'type' => 'group',
                'section' => $section,
                'title' => __('Hover Styling', 'customify'),
                'selector' => $selector,
                'css_format' => 'styling',
                'default' => array(),
                'fields' => array(
                    array(
                        'name' => 'text_color_hover',
                        'type' => 'color',
                        'label' => __('Text Color', 'customify'),
                        'selector' => $selector.":hover",
                        'css_format' => 'color: {{value}};',
                    ),

                    array(
                        'name' => 'color_hover', // Background color
                        'type' => 'color',
                        'label' => __('Background Color', 'customify'),
                        'selector' => $selector.":hover",
                        'css_format' => 'background-color: {{value}};',
                    ),

                    array(
                        'name' => 'border_style_hover',
                        'type' => 'select',
                        'label' => __('Border Style', 'customify'),
                        'default' => '',
                        'selector' => $selector.":hover",
                        'css_format' => 'border-style: {{value}};',
                        'choices' => array(
                            ''      => __('Inherit', 'customify'),
                            'solid'     => __('Solid', 'customify'),
                            'dotted'    => __('Dotted', 'customify'),
                            'dashed'    => __('Dashed', 'customify'),
                            'double'    => __('Double', 'customify'),
                            'ridge'     => __('Ridge', 'customify'),
                            'inset'     => __('Inset', 'customify'),
                            'outset'    => __('Outset', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'border_width_hover',
                        'type' => 'css_ruler',
                        'label' => __('Border Width', 'customify'),
                        'required' => array('border_style_hover', '!=', ''),
                        'selector' => $selector.":hover",
                        'css_format' => array(
                            'top' => 'border-top-width: {{value}};',
                            'right' => 'border-right-width: {{value}};',
                            'bottom' => 'border-bottom-width: {{value}};',
                            'left' => 'border-left-width: {{value}};',
                        )
                    ),
                    array(
                        'name' => 'border_color_hover',
                        'type' => 'color',
                        'label' => __('Border Color', 'customify'),
                        'required' => array('border_style_hover', '!=', ''),
                        'selector' => $selector.":hover",
                        'css_format' => 'border-color: {{value}};',
                    ),
                )
            ),

	        array(
		        'name' => $prefix.'_layout',
		        'type' => 'heading',
		        'section' => $section,
		        'title' => __( 'Item Layout', 'customify' )
	        ),

            array(
                'name' => $prefix.'_margin',
                'type' => 'css_ruler',
                'section' => $section,
                'device_settings' => true,
                'css_format' => array(
                    'top' => 'margin-top: {{value}};',
                    'right' => 'margin-right: {{value}};',
                    'bottom' => 'margin-bottom: {{value}};',
                    'left' => 'margin-left: {{value}};',
                ),
                'selector' => $selector,
                'label'  => __( 'Margin', 'customify' ),
            ),

            array(
                'name' => 'header_button_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'device_settings' => true,
                'selector' => '.builder-first--button',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
            ),

        );

        // Merge Item
        $config[] = customify_header_merge_item_settings( $this->id, $section );

        return $config;
    }


    function render(){
        $text = Customify_Customizer()->get_setting('header_button_text' );
        $icon = Customify_Customizer()->get_setting('header_button_icon' );
        $new_window = Customify_Customizer()->get_setting('header_button_target' );
	    $nofollow = Customify_Customizer()->get_setting('header_button_nofollow' );
        $link = Customify_Customizer()->get_setting('header_button_link' );
        $icon_position = Customify_Customizer()->get_setting('header_button_position' );
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

	    $rel = '';
        if ( $nofollow == 1 ) {
	        $rel = ' rel="nofollow" ';
        }

        $icon_html = '';
        if ( $icon['icon'] ) {
            $icon_html = '<i class="'.esc_attr( $icon['icon'] ).'"></i> ';
        }
        $classes[] = 'is_icon_'.$icon_position;

        echo '<a'.$target.$rel.' href="'.esc_url( $link ).'" class="'.esc_attr( join(" ", $classes ) ).'">';
            if ( $icon_position != 'after' ) {
                echo $icon_html.esc_html( $text );
            } else {
                echo esc_html( $text ).$icon_html;
            }
        echo '</a>';
    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Button() );


