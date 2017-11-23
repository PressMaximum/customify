<?php
function customify_builder_config_header_social_icons(){
    $section = 'header_social_icons';
    $prefix = 'header_social_icons_';
    $fn = 'customify_builder_header_social_icons_item';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Social Icons', 'customify' ),
        ),

        array(
            'name' => $prefix.'items',
            'type' => 'repeater',
            'section' => $section,
            'selector' => '.header-social-icons',
            'render_callback' => $fn,
            'title' => __( 'Socials', 'customify' ),
            'live_title_field' => 'title',
            'limit' => 4,
            'limit_msg' => __( 'Just limit 4 item, Ability HTML here',  'customify' ),
            'default' => array(

            ),
            'fields' => array(
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => __( 'Title', 'customify' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', 'customify' ),
                ),

                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'URL', 'customify' ),
                ),

            )
        ),

        array(
            'name' => $prefix.'size',
            'type' => 'slider',
            'device_settings' => true,
            'section' => $section,
            'max' => 150,
            'selector' => '.header-social-icons i',
            'css_format' => 'font-size: {{value}};',
            'render_callback' => $fn,
            'label' => __( 'Icon Size', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_text',
            'type' => 'checkbox',
            'device_settings' => true,
            'section' => $section,
            'selector' => '.header-social-icons',
            'render_callback' => $fn,
            'default' => array(
                'desktop' => 0,
                'tablet' => 0,
                'mobile' => 0,
            ),
            'checkbox_label' => __( 'Show text',  'customify' ),
            'label' => __( 'Show item text', 'customify' ),
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'checkbox',
            'section' => $section,
            'selector' => '.header-social-icons',
            'render_callback' => $fn,
            'checkbox_label' => __( 'Open URL in new window.',  'customify' ),
            'label' => __( 'Target', 'customify' ),
        ),

        array(
            'name' => 'header_social_icons_align',
            'type' => 'text_align_no_justify',
            'section' => $section,
            'device_settings' => true,
            'selector' => '.builder-item--social-icons',
            'css_format' => 'text-align: {{value}};',
            'title' => __( 'Align', 'customify' ),
        ),


    );
}

function customify_builder_header_social_icons_item( $item_config ){

    $target_blank = Customify_Customizer()->get_setting('header_social_icons_target');
    $target = '_self';
    if ( $target_blank == 1 ) {
        $target = '_blank';
    }

    $items = Customify_Customizer()->get_setting('header_social_icons_items');
    if ( ! empty( $items ) ) {

        $classes = array();
        $show_text = wp_parse_args( Customify_Customizer()->get_setting('header_social_icons_show_text', 'all'), array(
            'desktop' => '',
            'tablet' => '',
            'mobile' => ''
        ) );
        foreach ( $show_text as $k => $v ) {
            if (  ! $v ) {
                $classes[ $k ] = 'hide-on-'.sanitize_text_field( $k );
            }
        }


        echo '<ul class="header-social-icons">';
        foreach ( ( array ) $items as $index => $item) {
            $item = wp_parse_args( $item, array(
                'title' => '',
                'icon' => '',
                'url' => '',
                'show_text' => array(),
            ) );

            echo '<li>';
            if( ! $item['url'] ) {
                $item['url'] = '#';
            }
            if ( $item['url'] ) {
                echo '<a target="'.esc_attr( $target ).'" href="'.esc_url( $item['url']  ).'">';
            }

            $icon = wp_parse_args( $item['icon'], array(
                'type' => '',
                'icon' => '',
            ) );

            if ( $icon['icon'] ) {
                echo '<i class="'.esc_attr( $icon['icon'] ).'"></i>';
            }
            if ( $item['title'] ) {
                echo '<span class="'.esc_attr( join(' ', $classes ) ).'">'.wp_kses_post( $item['title'] ).'</span>';
            }

            if ( $item['url'] ) {
                echo '</a>';
            }
            echo '</li>';
        }

        echo '</ul>';
    }

}