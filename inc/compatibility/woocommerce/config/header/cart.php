<?php

class Customify_Builder_Item_WC_Cart {
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
        $this->label = __( 'Cart', 'customify' );
    }

    /**
     * Register Builder item
     * @return array
     */
    function item(){
        return array(
            'name' => $this->label,
            'id' => $this->id,
            'col' => 0,
            'width' => '4',
            'section' => $this->section // Customizer section to focus when click settings
        );
    }

    /**
     * Optional, Register customize section and panel.
     *
     * @return array
     */
    function customize(){
        $fn = array( $this, 'render' );
        $config = array(
            array(
                'name' => $this->section,
                'type' => 'section',
                'panel' => $this->panel,
                'priority' => $this->priority,
                'title' => $this->label,
            ),

            array(
                'name' => "{$this->name}_text",
                'type' => 'text',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'title' => __( 'Label', 'customify' ),
                'default' => __( 'Cart', 'customify' ),
            ),

            array(
                'name' => "{$this->name}_icon",
                'type' => 'icon',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'default' => array(
                    'icon' => 'fa fa-shopping-cart',
                    'type' => 'font-awesome'
                ),
                'title' => __( 'Icon', 'customify' ),
            ),

            array(
                'name' => "{$this->name}_icon_position",
                'type' => 'select',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'default' => 'after',
                'choices' => array(
                    'before' => __( 'Before', 'customify' ),
                    'after' => __( 'After', 'customify' ),
                ),
                'title' => __( 'Icon Position', 'customify' ),
            ),

            array(
                'name' => "{$this->name}_show_qty",
                'type' => 'checkbox',
                'default' => '1',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'checkbox_label' => __( 'Show Quantity', 'customify' ),
            ),

            array(
                'name' => "{$this->name}_show_sub_total",
                'type' => 'checkbox',
                'default' => '1',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'checkbox_label' => __( 'Show Sub Total', 'customify' ),
            ),

            array(
                'name' => "{$this->name}_sep",
                'type' => 'text',
                'section' => $this->section,
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'title' => __( 'Separator', 'customify' ),
                'default' => __( '/', 'customify' ),
            ),

        );

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $this->section ) );
    }

    /**
     * Optional. Render item content
     */
    function render(){
        $icon           = Customify()->get_setting( "{$this->name}_icon" );
        $icon_position  = Customify()->get_setting( "{$this->name}_icon_position" );
        $text           = Customify()->get_setting( "{$this->name}_text" );
        $show_sub_total           = Customify()->get_setting( "{$this->name}_show_sub_total" );
        $show_qty           = Customify()->get_setting( "{$this->name}_show_qty" );
        $sep           = Customify()->get_setting( "{$this->name}_sep" );

        $icon = wp_parse_args( $icon, array(
            'type' => '',
            'icon' => ''
        ) );

        $icon_html = '';
        if ( $icon['icon'] ) {
            $icon_html = '<i class="' . esc_attr( $icon['icon'] ) . '"></i> ';
        }
        //$classes[] = 'is-icon-' . $icon_position;
        if ( $text ) {
            $text = '<span class="cart-text">'.sanitize_text_field( $text ).'</span>';
        }

        $html = $text;
        if ( $show_sub_total ) {
            if ( $sep ) {
                $html .= '<span class="cart-sep">'.sanitize_text_field( $sep ).'</span>';
            }
            $html .='<span class="cart-subtotal"><span class="customify-wc-sub-total"></span></span>';
        }

        if( $icon_html ) {
            $icon_html = '<span class="cart-icon">'.$icon_html;
            if ( $show_qty ) {
                $icon_html .= '<span class="cart-qty"><span class="customify-wc-total-qty"></span></span>';
            }
            $icon_html .= '</span>';
        }

        if ( $icon_position == 'before' ) {
            $html = $icon_html.$html;
        } else {
            $html = $html.$icon_html;
        }

        echo '<div class="builder-header-'.esc_attr( $this->id ).'-item item--'.esc_attr( $this->id ).'">';

            echo '<a href="#" class="cart-item-link">';
                echo $html; // WPCS: XSS OK.
            echo '</a>';

            add_filter( 'woocommerce_widget_cart_is_hidden', '__return_false', 999 );

            echo '<div class="cart-item-dropdown widget-area">';
                the_widget( 'WC_Widget_Cart', array(
                    'hide_if_empty' => 0
                ) );
            echo '</div>';

            remove_filter( 'woocommerce_widget_cart_is_hidden', '__return_false', 999 );

        echo '</div>';
    }
}

Customify_Customize_Layout_Builder()->register_item('header', new Customify_Builder_Item_WC_Cart() );
