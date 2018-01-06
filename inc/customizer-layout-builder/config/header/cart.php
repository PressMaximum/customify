<?php
class Customify_Builder_Item_Cart
{
    public $id = 'button';

    function item()
    {
        return array(
            'name' => __('Button', 'customify'),
            'id' => 'button',
            'col' => 0,
            'width' => '4',
            'section' => 'header_button' // Customizer section to focus when click settings
        );
    }

    function customize(){
        $section = 'header_cart';
        $prefix = 'header_cart_';
        $config = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'title' => __( 'Cart', 'customify' ),
            ),

            array(
                'name' => $prefix.'icon',
                'type' => 'icon',
                'section' => $section,
                'title'  => __( 'Cart Icon', 'customify' ),
            ),

            array(
                'name' => $prefix.'show_price',
                'type' => 'check',
                'section' => $section,
                'title' => __( 'Show Total Price', 'customify' ),
            ),

            array(
                'name' => $prefix.'show_number_item',
                'type' => 'check',
                'section' => $section,
                'title' => __( 'Show Number Item', 'customify' ),
            ),
            array(
                'name' => $prefix.'url',
                'type' => 'text',
                'section' => $section,
                'title' => __( 'Cart Page URL', 'customify' ),
            ),

            array(
                'name' => $prefix.'url',
                'type' => 'text',
                'section' => $section,
                'title' => __( 'Cart Page URL', 'customify' ),
            ),

            array(
                'name' => $prefix.'color',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Color', 'customify' ),
            ),

            array(
                'name' => $prefix.'color_hover',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Color Hover', 'customify' ),
            ),
        );

        // Merge Item
        $config[] = customify_header_merge_item_settings( $this->id, $section );
        return $config;

    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Cart() );


