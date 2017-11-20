<?php
function customify_builder_config_header_icon_list(){
    $section = 'header_icon_list';
    $prefix = 'header_icon_list_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Icon List', 'customify' ),
        ),

        array(
            'name' => $prefix.'items',
            'type' => 'repeater',
            'section'     => $section,
            //'priority' => 22,
            'title'          => __( 'Items', 'customify' ),
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
                    'name' => 'show_text',
                    'type' => 'checkbox',
                    'device_settings' => true,
                    'checkbox_label' => __( 'Show text',  'customify' ),
                    'label' => __( 'Show text', 'customify' ),
                ),

                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'URL', 'customify' ),
                ),

            )
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'checkbox',
            'section'     => $section,
            'checkbox_label' => __( 'Open URL in new window.',  'customify' ),
            'label' => __( 'Target', 'customify' ),
        ),

    );
}

function customify_builder_header_icon_list_item( $item_config ){

    $target = Customify_Customizer()->get_setting('header_icon_list_target');
    $items = Customify_Customizer()->get_setting('header_icon_list_items');
    if ( ! empty( $items ) ) {
        echo '<ul>';
        foreach ( ( array ) $items as $index => $item) {
            $item = wp_parse_args( $item, array(
                'title' => '',
                'icon' => '',
                'url' => '',
                'show_text' => array(),

            ) );

            echo '<li>';
            if ( $item['url'] ) {
                echo '<a href="'.esc_url( $item['url']  ).'">';
            }

            $icon = wp_parse_args( $item['icon'], array(
                'type' => '',
                'icon' => '',
            ) );

            if ( $icon['icon'] ) {
                echo '<i class="'.esc_attr( $icon['icon'] ).'"></i>';
            }
            if ( $item['title'] ) {
                echo '<span>'.wp_kses_post( $item['title'] ).'</span>';
            }

            if ( $item['url'] ) {
                echo '</a>';
            }
            echo '</li>';
        }

        echo '</ul>';
    }

}