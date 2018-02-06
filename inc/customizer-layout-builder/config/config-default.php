<?php
add_filter( 'customify/customize/settings-default', 'customify_layout_builder_config_default', 15, 2 );
function customify_layout_builder_config_default ( $val, $name ){
    $defaults = array (
        'header_builder_panel' =>
            array (
                'desktop' =>
                    array (
                        'top' =>
                            array (
                                0 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '3',
                                        'height' => '1',
                                        'id' => 'nav-icon',
                                    ),
                                1 =>
                                    array (
                                        'x' => '8',
                                        'y' => '1',
                                        'width' => '4',
                                        'height' => '1',
                                        'id' => 'social-icons',
                                    ),
                            ),
                        'main' =>
                            array (
                                0 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '2',
                                        'height' => '1',
                                        'id' => 'logo',
                                    ),
                                1 =>
                                    array (
                                        'x' => '2',
                                        'y' => '1',
                                        'width' => '7',
                                        'height' => '1',
                                        'id' => 'primary-menu',
                                    ),
                                2 =>
                                    array (
                                        'x' => '9',
                                        'y' => '1',
                                        'width' => '3',
                                        'height' => '1',
                                        'id' => 'button',
                                    ),
                            ),
                        'bottom' =>
                            array (
                                0 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '12',
                                        'height' => '1',
                                        'id' => 'html',
                                    ),
                            ),
                    ),
                'mobile' =>
                    array (
                        'top' =>
                            array (
                            ),
                        'main' =>
                            array (
                                0 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '3',
                                        'height' => '1',
                                        'id' => 'nav-icon',
                                    ),
                            ),
                        'bottom' =>
                            array (
                            ),
                        'sidebar' =>
                            array (
                                0 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '1',
                                        'height' => '1',
                                        'id' => 'html',
                                    ),
                                1 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '1',
                                        'height' => '1',
                                        'id' => 'button',
                                    ),
                                2 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '1',
                                        'height' => '1',
                                        'id' => 'social-icons',
                                    ),
                                3 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '1',
                                        'height' => '1',
                                        'id' => 'primary-menu',
                                    ),
                                4 =>
                                    array (
                                        'x' => '0',
                                        'y' => '1',
                                        'width' => '1',
                                        'height' => '1',
                                        'id' => 'logo',
                                    ),
                            ),
                    ),
            ),
        'header_top_layout' => '',
        'header_top_noti_layout' => '',
        'header_top_height' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'value' => '0',
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
            ),
        'header_top_styling' =>
            array (
                'normal' =>
                    array (
                        'text_color' => '',
                        'link_color' => '',
                        'bg_color' => '#81d742',
                        'bg_image' =>
                            array (
                                'id' => '',
                                'url' => '',
                                'mime' => '',
                            ),
                        'bg_cover' => '',
                        'bg_position' => '',
                        'bg_repeat' => 'repeat',
                        'bg_attachment' => '',
                        'border_style' => 'none',
                        'border_width' =>
                            array (
                                'unit' => 'px',
                                'top' => '',
                                'right' => '',
                                'bottom' => '',
                                'left' => '',
                                'link' => '1',
                            ),
                        'border_color' => '',
                        'border_radius' =>
                            array (
                                'unit' => 'px',
                                'value' => '',
                            ),
                        'box_shadow' =>
                            array (
                                'color' => '',
                                'x' => '',
                                'y' => '',
                                'blur' => '',
                                'spread' => '',
                                'inset' => '',
                            ),
                    ),
                'hover' =>
                    array (
                        'link_color' => '',
                    ),
            ),
        'header_main_layout' => '',
        'header_main_noti_layout' => '',
        'header_main_height' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'value' => '72',
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
            ),
        'header_main_styling' =>
            array (
                'normal' =>
                    array (
                        'margin' =>
                            array (
                                'desktop' =>
                                    array (
                                        'unit' => 'px',
                                        'top' => '',
                                        'right' => '',
                                        'bottom' => '',
                                        'left' => '',
                                        'link' => '1',
                                    ),
                                'tablet' =>
                                    array (
                                        'unit' => 'px',
                                        'top' => '',
                                        'right' => '',
                                        'bottom' => '',
                                        'left' => '',
                                        'link' => '1',
                                    ),
                                'mobile' =>
                                    array (
                                        'unit' => 'px',
                                        'top' => '',
                                        'right' => '',
                                        'bottom' => '',
                                        'left' => '',
                                        'link' => '1',
                                    ),
                            ),
                        'bg_color' => '#969696',
                        'bg_image' =>
                            array (
                                'id' => '',
                                'url' => '',
                                'mime' => '',
                            ),
                        'bg_cover' => '',
                        'bg_position' => '',
                        'bg_repeat' => 'repeat',
                        'bg_attachment' => '',
                        'border_style' => 'none',
                        'border_width' =>
                            array (
                                'unit' => 'px',
                                'top' => '',
                                'right' => '',
                                'bottom' => '',
                                'left' => '',
                                'link' => '1',
                            ),
                        'border_color' => '',
                        'border_radius' =>
                            array (
                                'unit' => 'px',
                                'value' => '',
                            ),
                        'box_shadow' =>
                            array (
                                'color' => '',
                                'x' => '',
                                'y' => '',
                                'blur' => '',
                                'spread' => '',
                                'inset' => '',
                            ),
                    ),
            ),
        'header_bottom_layout' => '',
        'header_bottom_noti_layout' => '',
        'header_bottom_height' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
            ),
        'header_bottom_styling' => '',
        'header_sidebar_animate' => '',
        'header_sidebar_text_mode' => '',
        'header_sidebar_styling' => '',
        'header_html' => 'Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet',
        'header_html_l_heading' => '',
        'header_html_margin' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => 1,
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => 1,
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => 1,
                    ),
            ),
        'header_htmln_align' => '',
        'header_html_merge' =>
            array (
                'desktop' => '0',
                'mobile' => 'next',
            ),
        'logo_height' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'value' => '49',
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'value' => '45',
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'value' => '45',
                    ),
            ),
        'header_logo_retina' =>
            array (
                'id' => '20',
                'url' => 'http://beacon.dev/wp-content/uploads/2017/10/sea-landscape-mountains-nature-1024x700.jpg?t=1517298582322',
                'mime' => 'image/jpeg',
            ),
        'header_logo_name' => '',
        'header_logo_desc' => '',
        'header_logo_pos' => '',
        'header_logo_l_heading' => '',
        'header_logo_margin' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => NULL,
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => 1,
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'link' => 1,
                    ),
            ),
        'header_logon_align' => '',
        'header_logo_merge' =>
            array (
                'desktop' => 'next',
                'mobile' => '0',
            ),
        'nav_icon_text' => '',
        'nav_icon_show_text' =>
            array (
                'desktop' => 1,
                'tablet' => 1,
                'mobile' => 1,
            ),
        'nav_icon_size' =>
            array (
                'desktop' => 'small',
                'tablet' => 'medium',
                'mobile' => 'small',
            ),
        'nav_icon_item_color' => '',
        'nav_icon_item_color_hover' => '',
        'header_nav-icon_l_heading' => '',
        'header_nav-icon_margin' =>
            array (
                'unit' => 'px',
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'link' => 1,
            ),
        'header_nav-iconn_align' => '',
        'header_nav-icon_merge' =>
            array (
                'desktop' => 'prev',
                'mobile' => '0',
            ),
        'header_templates_save' => '',
        'primary_menu_style' => '',
        'primary_menu__hide-arrow' => 0,
        'primary_menu_top_heading' => '',
        'primary_menu_item_styling' => '',
        'primary_menu_typography' => '',
        'primary_menu_submenu_heading' => '',
        'primary_menu_submenu_width' =>
            array (
                'unit' => 'px',
                'value' => '',
            ),
        'primary_menu_sub_styling' => '',
        'primary_menu_sub_item_styling' => '',
        'primary_menu_typography_submenu' => '',
        'header_primary-menu_l_heading' => '',
        'header_primary-menu_margin' =>
            array (
                'unit' => 'px',
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'link' => 1,
            ),
        'header_primary-menun_align' => '',
        'header_primary-menu_merge' => '',
        'header_button_text' => 'Click me!',
        'header_button_icon' =>
            array (
                'type' => '',
                'icon' => '',
            ),
        'header_button_position' => '',
        'header_button_link' => '',
        'header_button_target' => 1,
        'header_button_typography' => '',
        'header_button_styling' => '',
        'header_button_l_heading' => '',
        'header_button_margin' =>
            array (
                'unit' => 'px',
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'link' => 1,
            ),
        'header_buttonn_align' =>
            array (
                'desktop' => 'center',
                'tablet' => '',
                'mobile' => '',
            ),
        'header_button_merge' => '',
        'header_social_icons_items' =>
            array (
                0 =>
                    array (
                        'title' => '',
                        'icon' =>
                            array (
                                'type' => 'font-awesome',
                                'icon' => 'fab fa-facebook-f',
                            ),
                        'url' => '',
                        '_visibility' => 'visible',
                    ),
                1 =>
                    array (
                        'title' => '',
                        'icon' =>
                            array (
                                'type' => 'font-awesome',
                                'icon' => 'fab fa-adn',
                            ),
                        'url' => '',
                        '_visibility' => 'visible',
                    ),
            ),
        'header_social_icons_target' => 1,
        'header_social_icons_nofollow' => 1,
        'header_social_icons_preset' =>
            array (
                'desktop' => 'plain',
                'tablet' => 'fill-rounded',
                'mobile' => 'outline-square',
            ),
        'header_social_icons_size' =>
            array (
                'desktop' => 'l',
                'tablet' => 'medium',
                'mobile' => 's',
            ),
        'header_social_icons_spacing' =>
            array (
                'desktop' =>
                    array (
                        'unit' => 'px',
                        'value' => '4',
                    ),
                'tablet' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
                'mobile' =>
                    array (
                        'unit' => 'px',
                        'value' => '',
                    ),
            ),
        'header_social-icons_l_heading' => '',
        'header_social-icons_margin' =>
            array (
                'unit' => 'px',
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'link' => 1,
            ),
        'header_social-iconsn_align' =>
            array (
                'desktop' => 'right',
                'tablet' => '',
                'mobile' => '',
            ),
        'header_social-icons_merge' => '',
    );
    if ( isset( $defaults[ $name ] ) ) {
        return $defaults[ $name ];
    }
    return $val;
}