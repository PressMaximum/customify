<?php

class Customify_Builder_Item_Nav_Icon{
     public $id= 'nav-icon';
    function item(){
        return  array(
            'name' => __( 'Nav Icon', 'customify' ),
            'id' => 'nav-icon',
            'width' => '3',
            //'devices' => 'mobile',
            'section' => 'header_nav_icon' // Customizer section to focus when click settings
        );
    }

    function customize(){
        $section = 'header_nav_icon';
        $fn = array( $this, 'render' );
        $selector = '.nav-mobile-toggle';
        $config = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title' => __( 'Nav Icon', 'customify' ),
            ),


            array(
                'name'    => 'nav_icon_style',
                'type'    => 'image_select',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title'   => __( 'Icon Preset', 'customify' ),
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
                'name' => 'nav_icon_text',
                'type' => 'text',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'default' => __( 'Navigation', 'customify' ),
                'title' => __( 'Label', 'customify' ),
            ),

            array(
                'name' => 'nav_icon_show_text',
                'type' => 'checkbox',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'default' => 1,
                'checkbox_label' => __( 'Show Label', 'customify' ),
            ),

            array(
                'name' => 'nav_icon_size',
                'type' => 'radio_group',
                'section' => $section,
                'selector' => $selector,
                'render_callback' => $fn,
                'title' => __( 'Icon Size', 'customify' ),
                'default' => 'is-size-memdium',
                'device_settings' => true,
                'choices' => array(
                      'small' => __( 'Small', 'customify' ),
                      'medium' => __( 'Medium', 'customify' ),
                      'large' => __( 'Large', 'customify' ),
                )
            ),

            array(
                'name' => 'nav_icon_item_color',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Color', 'customify' ),
                'css_format' => 'color: {{value}}; background-color: {{value}};',
                'selector' => "{$selector} .nav-icon--label, {$selector} .hamburger-inner, {$selector} .hamburger-inner:after,  {$selector} .hamburger-inner:before",

            ),

            array(
                'name' => 'nav_icon_item_color_hover',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'color: {{value}}; background-color: {{value}};',
                'selector' => "{$selector}:hover .nav-icon--label, {$selector}:hover .hamburger-inner, {$selector}:hover .hamburger-inner:after,  {$selector}:hover .hamburger-inner:before",
                'title' => __( 'Color Hover', 'customify' ),
            ),

            array(
                'name'    => 'header_nav_icon_l_heading',
                'type'    => 'heading',
                'section' => $section,
                'title'   => __( 'Item Layout', 'customify' )
            ),

            array(
                'name' => 'nav_icon_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'css_format' => array(
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
                'selector' => $selector. ' .hamburger',
                'title'  => __( 'Icon Padding', 'customify' ),
            ),


            array(
                'name' => 'header_nav_icon_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'selector' => '.builder-first--nav-icon',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
            ),
        );

        // Merge Item
        $config[] = customify_header_merge_item_settings( $this->id, $section );
        return $config;
    }

    function render(){
        $label = sanitize_text_field( Customify_Customizer()->get_setting( 'nav_icon_text' ) );
        $show_label = Customify_Customizer()->get_setting('nav_icon_show_text');
        $style = sanitize_text_field( Customify_Customizer()->get_setting('nav_icon_style' ) );
        $sizes = Customify_Customizer()->get_setting('nav_icon_size', 'all' );

        $classes = array('nav-mobile-toggle item-button');
        if ( $show_label ) {
            $classes[] = 'nav-show-label';
        } else {
            $classes[] = 'nav-hide-label';
        }

        if ( empty( $sizes ) ) {
            $sizes = 'is-size-'.$sizes;
        }

        if ( is_string( $sizes ) ) {
            $classes[] = $sizes;
        } else {
            foreach ( $sizes as $d => $s ) {
                if ( !is_string( $s ) ) {
                    $s = 'is-size-medium';
                }

                $classes[] = 'is-size-'.$d.'-'.$s;
            }
        }


        if( $style ) {
            $classes[] = $style;
        }

        ?>
        <span class="<?php echo esc_attr( join(' ', $classes ) ); ?>">
            <span class="hamburger hamburger--squeeze">
                <span class="hamburger-box">
                  <span class="hamburger-inner"></span>
                </span>
              </span>
            <?php
            if ( $show_label ) {
                echo '<span class="nav-icon--label">'.$label.'</span>';
            }
            ?></span>
        <?php
    }

}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Nav_Icon() );

