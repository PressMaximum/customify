<?php
class Customify_Builder_Item_Footer_Widget_1
{
    public $id = 'footer-1';

    function item()
    {
        return array(
            'name' => __( 'Footer 1', 'customify' ),
            'id' => 'footer-1',
            'width' => '3',
            'section' => 'sidebar-widgets-footer-1'
        );
    }
}

class Customify_Builder_Item_Footer_Widget_2
{
    public $id = 'footer-2';

    function item()
    {
        return array(
            'name' => __( 'Footer 2', 'customify' ),
            'id' => 'footer-2',
            'width' => '3',
            'section' => 'sidebar-widgets-footer-2'
        );
    }
}

class Customify_Builder_Item_Footer_Widget_3
{
    public $id = 'footer-3';

    function item()
    {
        return array(
            'name' => __( 'Footer 3', 'customify' ),
            'id' => 'footer-3',
            'width' => '3',
            'section' => 'sidebar-widgets-footer-3'
        );
    }
}

class Customify_Builder_Item_Footer_Widget_4
{
    public $id = 'footer-4';

    function item()
    {
        return array(
            'name' => __( 'Footer 4', 'customify' ),
            'id' => 'footer-4',
            'width' => '3',
            'section' => 'sidebar-widgets-footer-4'
        );
    }
}


function customify_change_footer_widgets_location( $wp_customize ){
    for ( $i = 1; $i<= 4; $i ++ ) {
        if (  $wp_customize->get_section( 'sidebar-widgets-footer-'.$i ) ) {
            $wp_customize->get_section( 'sidebar-widgets-footer-'.$i )->panel = 'footer_settings';
        }
    }
}
add_action( 'customize_register', 'customify_change_footer_widgets_location', 199 );

/**
 * Always show footer widgets for customize builder
 *
 * @param $active
 * @param $section
 * @return bool
 */
function customify_customize_footer_widgets_show(  $active, $section ){
    if ( strpos( $section->id, 'widgets-footer-' ) ) {
        $active = true;
    }
    return $active;
}
add_filter( 'customize_section_active', 'customify_customize_footer_widgets_show', 15, 2 );


/**
 * Display Footer widget
 *
 * @param string $footer_id
 */
function customify_builder_footer_widget_item( $footer_id = 'footer-1' ){
    dynamic_sidebar( $footer_id );
}

function customify_builder_footer_1_item(){
    customify_builder_footer_widget_item( 'footer-1' );
}

function customify_builder_footer_2_item(){
    customify_builder_footer_widget_item( 'footer-2' );
}

function customify_builder_footer_3_item(){
    customify_builder_footer_widget_item( 'footer-3' );
}

function customify_builder_footer_4_item(){
    customify_builder_footer_widget_item( 'footer-4' );
}

Customify_Customizer_Layout_Builder()->register_item('footer', new Customify_Builder_Item_Footer_Widget_1() );
Customify_Customizer_Layout_Builder()->register_item('footer', new Customify_Builder_Item_Footer_Widget_2() );
Customify_Customizer_Layout_Builder()->register_item('footer', new Customify_Builder_Item_Footer_Widget_3() );
Customify_Customizer_Layout_Builder()->register_item('footer', new Customify_Builder_Item_Footer_Widget_4() );