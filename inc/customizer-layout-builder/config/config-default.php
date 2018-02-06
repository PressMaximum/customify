<?php
add_filter('customify/customize/settings-default', 'customify_layout_builder_config_default', 15, 2);
function customify_layout_builder_config_default($val, $name)
{
    $defaults =
        array(
            'header_builder_panel' =>
                array(
                    'desktop' =>
                        array(
                            'top' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '4',
                                            'height' => '1',
                                            'id' => 'html',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '8',
                                            'y' => '1',
                                            'width' => '4',
                                            'height' => '1',
                                            'id' => 'social-icons',
                                        ),
                                ),
                            'main' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'logo',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '3',
                                            'y' => '1',
                                            'width' => '6',
                                            'height' => '1',
                                            'id' => 'primary-menu',
                                        ),
                                    2 =>
                                        array(
                                            'x' => '9',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'button',
                                        ),
                                ),
                        ),
                    'mobile' =>
                        array(
                            'top' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '6',
                                            'height' => '1',
                                            'id' => 'social-icons',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '6',
                                            'y' => '1',
                                            'width' => '6',
                                            'height' => '1',
                                            'id' => 'button',
                                        ),
                                ),
                            'main' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '9',
                                            'height' => '1',
                                            'id' => 'logo',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '9',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'nav-icon',
                                        ),
                                ),
                            'sidebar' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '1',
                                            'height' => '1',
                                            'id' => 'html',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '1',
                                            'height' => '1',
                                            'id' => 'primary-menu',
                                        ),
                                ),
                        ),
                ),
            'header_top_height' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'value' => '26',
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'value' => '33',
                        ),
                ),
            'header_main_layout' => 'layout-full-contained',
            'header_main_height' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'value' => '85',
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                ),
            'header_main_styling' =>
                array(
                    'normal' =>
                        array(
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '',
                            'bg_image' =>
                                array(
                                    'id' => '',
                                    'url' => '',
                                    'mime' => '',
                                ),
                            'bg_cover' => '',
                            'bg_position' => '',
                            'bg_repeat' => 'repeat',
                            'bg_attachment' => '',
                            'border_style' => 'solid',
                            'border_width' =>
                                array(
                                    'unit' => 'px',
                                    'top' => '0',
                                    'right' => '0',
                                    'bottom' => '1',
                                    'left' => '0',
                                    'link' => '',
                                ),
                            'border_color' => '#eaecee',
                            'border_radius' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                    'hover' =>
                        array(),
                ),
            'header_bottom_height' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'value' => '48',
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                ),
            'header_bottom_styling' =>
                array(
                    'normal' =>
                        array(
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '#212121',
                            'bg_image' =>
                                array(
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
                                array(
                                    'unit' => 'px',
                                    'top' => '',
                                    'right' => '',
                                    'bottom' => '',
                                    'left' => '',
                                    'link' => '1',
                                ),
                            'border_color' => '',
                            'border_radius' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                ),
            'header_sidebar_styling' =>
                array(
                    'normal' =>
                        array(
                            'text_color' => '',
                            'link_color' => '',
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'padding' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '#7b8994',
                            'bg_image' =>
                                array(
                                    'id' => '',
                                    'url' => '',
                                    'mime' => '',
                                ),
                            'bg_cover' => '',
                            'bg_position' => '',
                            'bg_repeat' => 'repeat',
                            'bg_attachment' => '',
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                    'hover' =>
                        array(
                            'link_color' => '',
                        ),
                ),
            'logo_height' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'value' => '41',
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'value' => '41',
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'value' => '43',
                        ),
                ),
            'header_logo_retina' =>
                array(
                    'id' => '',
                    'url' => '',
                    'mime' => '',
                ),
            'header_logo_margin' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'link' => NULL,
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'link' => 1,
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '0',
                            'bottom' => '',
                            'left' => '',
                            'link' => NULL,
                        ),
                ),
            'header_logo_align' =>
                array(
                    'desktop' => 'left',
                    'tablet' => '',
                    'mobile' => '',
                ),
            'header_logo_merge' =>
                array(
                    'desktop' => '0',
                    'mobile' => '0',
                ),
            'nav_icon_show_text' =>
                array(
                    'desktop' => 1,
                    'tablet' => 1,
                    'mobile' => 1,
                ),
            'nav_icon_size' =>
                array(
                    'desktop' => 'small',
                    'tablet' => 'medium',
                    'mobile' => 'small',
                ),
            'header_nav-icon_margin' =>
                array(
                    'desktop' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'link' => 1,
                        ),
                    'tablet' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'link' => 1,
                        ),
                    'mobile' =>
                        array(
                            'unit' => 'px',
                            'top' => '',
                            'right' => '',
                            'bottom' => '',
                            'left' => '',
                            'link' => NULL,
                        ),
                ),
            'header_nav-icon_align' =>
                array(
                    'desktop' => 'right',
                    'tablet' => '',
                    'mobile' => 'right',
                ),
            'header_nav-icon_merge' =>
                array(
                    'desktop' => 'next',
                    'mobile' => 'next',
                ),
            'primary_menu_typography' =>
                array(
                    'font' => '',
                    'font_weight' => '',
                    'font_size' =>
                        array(
                            'desktop' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'tablet' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'mobile' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                        ),
                    'line_height' =>
                        array(
                            'desktop' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'tablet' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'mobile' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                        ),
                    'letter_spacing' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                    'style' => '',
                    'text_decoration' => '',
                    'text_transform' => '',
                    'font_type' => 'normal',
                ),
            'primary_menu_submenu_width' =>
                array(
                    'unit' => 'px',
                    'value' => '',
                ),
            'primary_menu_typography_submenu' =>
                array(
                    'font' => '',
                    'font_weight' => '',
                    'font_size' =>
                        array(
                            'desktop' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'tablet' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'mobile' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                        ),
                    'line_height' =>
                        array(
                            'desktop' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'tablet' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'mobile' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                        ),
                    'letter_spacing' =>
                        array(
                            'unit' => 'px',
                            'value' => '',
                        ),
                    'style' => '',
                    'text_decoration' => '',
                    'text_transform' => '',
                    'font_type' => 'normal',
                ),
            'header_primary-menu_margin' =>
                array(
                    'unit' => 'px',
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                    'link' => 1,
                ),
            'header_primary-menu_align' =>
                array(
                    'desktop' => 'right',
                    'tablet' => '',
                    'mobile' => '',
                ),
            'header_primary-menu_merge' =>
                array(
                    'desktop' => 'next',
                    'mobile' => '0',
                ),
            'header_button_icon' =>
                array(
                    'type' => '',
                    'icon' => '',
                ),
            'header_social_icons_items' =>
                array(
                    0 =>
                        array(
                            'title' => 'Twitter',
                            'icon' =>
                                array(
                                    'type' => 'font-awesome',
                                    'icon' => 'fab fa-twitter',
                                ),
                            'url' => '#',
                            '_visibility' => 'visible',
                        ),
                    1 =>
                        array(
                            'title' => 'Facebook',
                            'icon' =>
                                array(
                                    'type' => 'font-awesome',
                                    'icon' => 'fab fa-facebook-f',
                                ),
                            'url' => '',
                            '_visibility' => 'visible',
                        ),
                    2 =>
                        array(
                            'title' => 'TripAdvisor',
                            'icon' =>
                                array(
                                    'type' => 'font-awesome',
                                    'icon' => 'fab fa-tripadvisor',
                                ),
                            'url' => '',
                            '_visibility' => 'visible',
                        ),
                    3 =>
                        array(
                            'title' => 'VK',
                            'icon' =>
                                array(
                                    'type' => 'font-awesome',
                                    'icon' => 'fab fa-vk',
                                ),
                            'url' => '',
                            '_visibility' => 'visible',
                        ),
                ),
            'header_social_icons_target' => 1,
            'header_social_icons_nofollow' => 1,
            'header_social_icons_preset' =>
                array(
                    'desktop' => 'plain',
                    'tablet' => 'plain',
                    'mobile' => 'plain',
                ),
            'header_social_icons_size' =>
                array(
                    'desktop' => 'medium',
                    'tablet' => 'medium',
                    'mobile' => 'medium',
                ),
            'header_social_icons_spacing' =>
                array(
                    'unit' => 'px',
                    'value' => '',
                ),
            'header_social-icons_margin' =>
                array(
                    'unit' => 'px',
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                    'link' => 1,
                ),
            'header_social-icons_align' =>
                array(
                    'desktop' => 'right',
                    'tablet' => 'left',
                    'mobile' => 'left',
                ),
            'footer_builder_panel' =>
                array(
                    'desktop' =>
                        array(
                            'main' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'footer-1',
                                        ),
                                    1 =>
                                        array(
                                            'x' => '3',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'footer-2',
                                        ),
                                    2 =>
                                        array(
                                            'x' => '6',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'footer-3',
                                        ),
                                    3 =>
                                        array(
                                            'x' => '9',
                                            'y' => '1',
                                            'width' => '3',
                                            'height' => '1',
                                            'id' => 'footer-4',
                                        ),
                                ),
                            'bottom' =>
                                array(
                                    0 =>
                                        array(
                                            'x' => '0',
                                            'y' => '1',
                                            'width' => '6',
                                            'height' => '1',
                                            'id' => 'footer_copyright',
                                        ),
                                ),
                        ),
                ),
            'footer_main_layout' => 'layout-full-contained',
            'footer_main_styling' =>
                array(
                    'normal' =>
                        array(
                            'text_color' => '',
                            'link_color' => '',
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'padding' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '',
                            'bg_image' =>
                                array(
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
                                array(
                                    'unit' => 'px',
                                    'top' => '',
                                    'right' => '',
                                    'bottom' => '',
                                    'left' => '',
                                    'link' => '1',
                                ),
                            'border_color' => '',
                            'border_radius' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                    'hover' =>
                        array(
                            'link_color' => '',
                        ),
                ),
            'footer_bottom_layout' => 'layout-full-contained',
            'footer_bottom_styling' =>
                array(
                    'normal' =>
                        array(
                            'text_color' => '',
                            'link_color' => '',
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'padding' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '#dbdbdb',
                            'bg_image' =>
                                array(
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
                                array(
                                    'unit' => 'px',
                                    'top' => '',
                                    'right' => '',
                                    'bottom' => '',
                                    'left' => '',
                                    'link' => '1',
                                ),
                            'border_color' => '',
                            'border_radius' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                    'hover' =>
                        array(
                            'link_color' => '',
                        ),
                ),
            'footer_general_styling' =>
                array(
                    'normal' =>
                        array(
                            'text_color' => '',
                            'link_color' => '',
                            'margin' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'padding' =>
                                array(
                                    'desktop' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'tablet' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                    'mobile' =>
                                        array(
                                            'unit' => 'px',
                                            'top' => '',
                                            'right' => '',
                                            'bottom' => '',
                                            'left' => '',
                                            'link' => '1',
                                        ),
                                ),
                            'bg_color' => '#e2e2e2',
                            'bg_image' =>
                                array(
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
                                array(
                                    'unit' => 'px',
                                    'top' => '',
                                    'right' => '',
                                    'bottom' => '',
                                    'left' => '',
                                    'link' => '1',
                                ),
                            'border_color' => '',
                            'border_radius' =>
                                array(
                                    'unit' => 'px',
                                    'value' => '',
                                ),
                            'box_shadow' =>
                                array(
                                    'color' => '',
                                    'x' => '',
                                    'y' => '',
                                    'blur' => '',
                                    'spread' => '',
                                    'inset' => '',
                                ),
                        ),
                    'hover' =>
                        array(
                            'link_color' => '',
                        ),
                ),
        );


    if (!$val && isset($defaults[$name])) {
        return $defaults[$name];
    }
    return $val;
}