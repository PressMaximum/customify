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
                'name' => 'nav_icon_style',
                'type' => 'select',
                'section' => $section,
                'title'          => __( 'Style', 'customify' ),
                'selector' => $selector,
                'render_callback' => $fn,
                'choices' => array(
                    'default' => __( 'Default', 'customify' ),
                    'style_2' => __( 'Style 2', 'customify' ),
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
                'css_format' => 'html_class',
                'title' => __( 'Icon Size', 'customify' ),
                'default' => 'is-default',
                'choices' => array(
                      'is-xs' => __( 'XS', 'customify' ),
                      'is-s' => __( 'S', 'customify' ),
                      'is-default' => __( 'Default', 'customify' ),
                      'is-m' => __( 'M', 'customify' ),
                      'is-l' => __( 'L', 'customify' ),
                      'is-xl' => __( 'XL', 'customify' ),
                )
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
                'selector' => $selector,
                'title'  => __( 'Icon Padding', 'customify' ),
            ),

            array(
                'name' => 'nav_icon_item_color',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Color', 'customify' ),
                'css_format' => 'color: {{value}};',
                'selector' => $selector,
            ),

            array(
                'name' => 'nav_icon_item_color_hover',
                'type' => 'color',
                'section' => $section,
                'css_format' => 'color: {{value}};',
                'selector' => $selector.':hover',
                'title' => __( 'Color Hover', 'customify' ),
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
        $icon = Customify_Customizer()->get_setting('nav_icon' );
        $size = Customify_Customizer()->get_setting('nav_icon_size' );
        $icon = Customify_Customizer()->setup_icon( $icon );

        $classes = array('nav-mobile-toggle item-button');
        if ( $show_label ) {
            $classes[] = 'nav-show-label';
        } else {
            $classes[] = 'nav-hide-label';
        }
        $classes[] = $size;

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

