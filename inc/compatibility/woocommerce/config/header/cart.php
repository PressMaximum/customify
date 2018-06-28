<?php

class Customify_Builder_Item_WC_Cart
{
    public $id = 'wc_cart'; // Required
    public $section = 'wc_cart'; // Optional
    public $name = 'wc_cart'; // Optional
    public $label = ''; // Optional
    public $priority = 200;
    public $panel = 'header_settings';

    /**
     * Optional construct
     *
     * Customify_Builder_Item_HTML constructor.
     */
    function __construct()
    {
        $this->label = __('Cart', 'customify');
    }

    /**
     * Register Builder item
     * @return array
     */
    function item()
    {
        return array(
            'name'    => $this->label,
            'id'      => $this->id,
            'col'     => 0,
            'width'   => '4',
            'section' => $this->section // Customizer section to focus when click settings
        );
    }

    /**
     * Optional, Register customize section and panel.
     *
     * @return array
     */
    function customize()
    {
        $fn = array($this, 'render');
        $config = array(
            array(
                'name'     => $this->section,
                'type'     => 'section',
                'panel'    => $this->panel,
                'priority' => $this->priority,
                'title'    => $this->label,
            ),

            array(
                'name'            => "{$this->name}_text",
                'type'            => 'text',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'title'           => __('Label', 'customify'),
                'default'         => __('Cart', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_icon",
                'type'            => 'icon',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'default'         => array(
                    'icon' => 'fa fa-shopping-cart',
                    'type' => 'font-awesome'
                ),
                'title'           => __('Icon', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_icon_position",
                'type'            => 'select',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'default'         => 'after',
                'choices'         => array(
                    'before' => __('Before', 'customify'),
                    'after'  => __('After', 'customify'),
                ),
                'title'           => __('Icon Position', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_show_label",
                'type'            => 'checkbox',
                'default'         => array(
                    'desktop' => 1,
                    'tablet' => 1,
                    'mobile' => 0,
                ),
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'theme_supports'  => '',
                'label'  => __('Show Label', 'customify'),
                'checkbox_label'  => __('Show Label', 'customify'),
                'device_settings' => true,
            ),

            array(
                'name'            => "{$this->name}_show_sub_total",
                'type'            => 'checkbox',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'theme_supports'  => '',
                'label'  => __('Sub Total', 'customify'),
                'checkbox_label'  => __('Show Sub Total', 'customify'),
                'device_settings' => true,
                'default'         => array(
                    'desktop' => 1,
                    'tablet' => 1,
                    'mobile' => 0,
                ),
            ),

            array(
                'name'            => "{$this->name}_show_qty",
                'type'            => 'checkbox',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'default'  => 1,
                'label'  => __('Quantity', 'customify'),
                'checkbox_label'  => __('Show Quantity', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_sep",
                'type'            => 'text',
                'section'         => $this->section,
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'title'           => __('Separator', 'customify'),
                'default'         => __('/', 'customify'),
            ),

            array(
                'name'        => "{$this->name}_label_styling",
                'type'        => 'styling',
                'section'     => $this->section,
                'title'       => __('Styling', 'customify'),
                'description' => __('Advanced styling for button', 'customify'),
                'selector'    => array(
                    'normal' => '.builder-header-' . $this->id . '-item .cart-item-link',
                    'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-item-link',
                ),
                'css_format'  => 'styling',
                'default'     => array(),
                'fields'      => array(
                    'normal_fields' => array(
                        'link_color'    => false, // disable for special field.
                        'margin'        => false,
                        'bg_image'      => false,
                        'bg_cover'      => false,
                        'bg_position'   => false,
                        'bg_repeat'     => false,
                        'bg_attachment' => false,
                    ),
                    'hover_fields'  => array(
                        'link_color' => false, // disable for special field.
                    )
                ),
            ),

            array(
                'name'       => "{$this->name}_typography",
                'type'       => 'typography',
                'section'    => $this->section,
                'title'      => __('Typography', 'customify'),
                'selector'   => '.builder-header-' . $this->id . '-item',
                'css_format' => 'typography',
                'default'    => array(),
            ),

            array(
                'name'    => "{$this->name}_icon_h",
                'type'    => 'heading',
                'section' => $this->section,
                'title'   => __('Icon Settings', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_icon_size",
                'type'            => 'slider',
                'section'         => $this->section,
                'device_settings' => true,
                'max'             => 150,
                'title'           => __('Icon Size', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-icon i:before',
                'css_format'      => 'font-size: {{value}};',
                'default'         => array(),
            ),

            array(
                'name'        => "{$this->name}_icon_styling",
                'type'        => 'styling',
                'section'     => $this->section,
                'title'       => __('Styling', 'customify'),
                'description' => __('Advanced styling for cart icon', 'customify'),
                'selector'    => array(
                    'normal' => '.builder-header-' . $this->id . '-item .cart-item-link .cart-icon i',
                    'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-item-link .cart-icon i',
                ),
                'css_format'  => 'styling',
                'default'     => array(),
                'fields'      => array(
                    'normal_fields' => array(
                        'link_color'    => false, // disable for special field.
                        // 'margin' => false,
                        'bg_image'      => false,
                        'bg_cover'      => false,
                        'bg_position'   => false,
                        'bg_repeat'     => false,
                        'bg_attachment' => false,
                    ),
                    'hover_fields'  => array(
                        'link_color' => false, // disable for special field.
                    )
                ),
            ),

            array(
                'name'        => "{$this->name}_qty_styling",
                'type'        => 'styling',
                'section'     => $this->section,
                'title'       => __('Quantity', 'customify'),
                'description' => __('Advanced styling for cart quantity', 'customify'),
                'selector'    => array(
                    'normal' => '.builder-header-' . $this->id . '-item  .cart-icon .cart-qty',
                    'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-icon .cart-qty',
                ),
                'css_format'  => 'styling',
                'default'     => array(),
                'fields'      => array(
                    'normal_fields' => array(
                        'link_color'    => false, // disable for special field.
                        //'margin' => false,
                        'bg_image'      => false,
                        'bg_cover'      => false,
                        'bg_position'   => false,
                        'bg_repeat'     => false,
                        'bg_attachment' => false,
                    ),
                    'hover_fields'  => array(
                        'link_color' => false, // disable for special field.
                    )
                ),
            ),

            array(
                'name'    => "{$this->name}_d_h",
                'type'    => 'heading',
                'section' => $this->section,
                'title'   => __('Dropdown Settings', 'customify'),
            ),

            array(
                'name'            => "{$this->name}_d_align",
                'type'            => 'select',
                'section'         => $this->section,
                'device_settings' => true,
                'title'           => __('Dropdown Alignment', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item',
                'render_callback' => $fn,
                'default'         => array(),
                'choices'         => array(
                    'left' => __('Left', 'customify'),
                    'right' => __('Right', 'customify'),
                ),
            ),


            array(
                'name'            => "{$this->name}_d_width",
                'type'            => 'slider',
                'section'         => $this->section,
                'device_settings' => true,
                'max'             => 600,
                'title'           => __('Dropdown Width', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item  .cart-item-dropdown',
                'css_format'      => 'width: {{value}};',
                'default'         => array(),
            ),

            array(
                'name'            => "{$this->name}_d_color",
                'type'            => 'color',
                'section'         => $this->section,
                'title'           => __('Color', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget, .builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget a:not(.button)',
                'css_format'      => 'color: {{value}};',
            ),

            array(
                'name'            => "{$this->name}_d_btn_color",
                'type'            => 'color',
                'section'         => $this->section,
                'title'           => __('Button Color', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget a.button',
                'css_format'      => 'color: {{value}};',
            ),

            array(
                'name'            => "{$this->name}_d_btn_bg",
                'type'            => 'color',
                'section'         => $this->section,
                'title'           => __('Button Background', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget a.button',
                'css_format'      => 'background-color: {{value}};',
            ),

            array(
                'name'            => "{$this->name}_d_btn_Check_bg",
                'type'            => 'color',
                'section'         => $this->section,
                'title'           => __('Button Checkout Background', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget a.button.checkout',
                'css_format'      => 'background-color: {{value}};',
            ),

            array(
                'name'        => "{$this->name}_dropdown_styling",
                'type'        => 'styling',
                'section'     => $this->section,
                'title'       => __('Dropdown Styling', 'customify'),
                'description' => __('Advanced styling for cart dropdown', 'customify'),
                'selector'    => array(
                    'normal' => '.builder-header-' . $this->id . '-item .cart-item-dropdown',
                ),
                'css_format'  => 'styling',
                'default'     => array(),
                'fields'      => array(
                    'normal_fields' => array(
                        'color'    => false, // disable for special field.
                        'link_color'    => false, // disable for special field.
                        //'margin' => false,
                        'bg_image'      => false,
                        'bg_cover'      => false,
                        'bg_position'   => false,
                        'bg_repeat'     => false,
                        'bg_attachment' => false,
                        'bg_color'      => false,
                        'bg_heading'      => false,
                        'border_style'      => false,
                        'border_radius'      => false,
                        'border_width'      => false,
                        'border_color'      => false,
                        'box_shadow'      => false,
                        'border_heading'      => false,
                    ),
                    'hover_fields'  => false
                ),
            ),

            array(
                'name'            => "{$this->name}_li_border_width",
                'type'            => 'slider',
                'section'         => $this->section,
                'max'             => 10,
                'title'           => __('Item Separator Width', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget  li',
                'css_format'      => 'border-bottom-width: {{value}};',
                'default'         => array(),
            ),

            array(
                'name'            => "{$this->name}_li_border_color",
                'type'            => 'color',
                'section'         => $this->section,
                'title'           => __('Item Separator Color', 'customify'),
                'selector'        => '.builder-header-' . $this->id . '-item .cart-item-dropdown.widget-area .widget li',
                'css_format'      => 'border-bottom-color: {{value}};',
                'default'         => array(),
            ),

        );

        // Item Layout
        return array_merge($config, customify_header_layout_settings($this->id, $this->section));
    }

    function array_to_class( $array, $prefix ){
        if ( ! array( $array ) ) {
            return '';
        }
        $classes = array();
        $array = array_reverse($array);
        foreach( $array as $k => $v ) {
            if ( $v == 1 ) {
                $v = 'show';
            } elseif ( $v == 0 ) {
                $v = 'hide';
            }
            $classes[] = "{$prefix}-{$k}-{$v}";
        }

        return join( ' ', $classes );
    }

    /**
     * Optional. Render item content
     */
    function render()
    {
        $icon = Customify()->get_setting("{$this->name}_icon");
        $icon_position = Customify()->get_setting("{$this->name}_icon_position");
        $text = Customify()->get_setting("{$this->name}_text");

        $show_label = Customify()->get_setting("{$this->name}_show_label" , 'all' );
        $show_sub_total = Customify()->get_setting("{$this->name}_show_sub_total", 'all');
        $show_qty = Customify()->get_setting("{$this->name}_show_qty");
        $sep = Customify()->get_setting("{$this->name}_sep");

        $classes = array();

        $align = Customify()->get_setting("{$this->name}_d_align", 'all');
        $classes[] = $this->array_to_class( $align, 'd-align' );

        $label_classes = $this->array_to_class( $show_label, 'wc-cart' );
        $subtotal_classes = $this->array_to_class( $show_sub_total, 'wc-cart' );

        $icon = wp_parse_args($icon, array(
            'type' => '',
            'icon' => ''
        ));

        $icon_html = '';
        if ($icon['icon']) {
            $icon_html = '<i class="' . esc_attr($icon['icon']) . '"></i> ';
        }
        //$classes[] = 'is-icon-' . $icon_position;
        if ($text) {
            $text = '<span class="cart-text cart-label '.esc_attr(  $label_classes ).'">' . sanitize_text_field($text) . '</span>';
        }

        $sub_total = WC()->cart->get_subtotal();
        $quantities = WC()->cart->get_cart_item_quantities();

        $html = $text;

        if ( $sep && $html) {
            $html .= '<span class="cart-sep cart-label '.esc_attr(  $label_classes ).'">' . sanitize_text_field($sep) . '</span>';
        }
        $html .= '<span class="cart-subtotal cart-label '.esc_attr( $subtotal_classes ) .'"><span class="customify-wc-sub-total">' . wc_price($sub_total) . '</span></span>';


        if ($icon_html) {
            $icon_html = '<span class="cart-icon">' . $icon_html;
            if ($show_qty) {
                $icon_html .= '<span class="cart-qty"><span class="customify-wc-total-qty">' . array_sum($quantities) . '</span></span>';
            }
            $icon_html .= '</span>';
        }

        if ($icon_position == 'before') {
            $html = $icon_html . $html;
        } else {
            $html = $html . $icon_html;
        }

        $classes[] = 'builder-header-' . $this->id.'-item';
        $classes[] = 'item--' . $this->id;

        echo '<div class="' . esc_attr(join( ' ', $classes )) . '">';

        echo '<a href="#" class="cart-item-link">';
        echo $html; // WPCS: XSS OK.
        echo '</a>';

        add_filter('woocommerce_widget_cart_is_hidden', '__return_false', 999);

        echo '<div class="cart-item-dropdown widget-area">';
        the_widget('WC_Widget_Cart', array(
            'hide_if_empty' => 0
        ));
        echo '</div>';

        remove_filter('woocommerce_widget_cart_is_hidden', '__return_false', 999);

        echo '</div>';
    }
}

Customify_Customize_Layout_Builder()->register_item('header', new Customify_Builder_Item_WC_Cart());
