<?php
class Customify_Builder_Item_Primary_Menu {
    public $id;
    public $label;
    public $prefix;
    public $selector;
    public $section;
    public $theme_location;

    /**
     * Optional construct
     *
     * Customify_Builder_Item_HTML constructor.
     */
    function __construct()
    {
        $this->id = 'primary-menu';
        $this->label = __( 'Primary Menu', 'customify' );
        $this->prefix = 'primary_menu';
        $this->selector = '.builder-item--'.$this->id .' .primary-menu';
        $this->section = 'header_menu_primary';
        $this->theme_location = 'menu-1';
    }

    function item(){
        return array(
            'name' => $this->label,
            'id' => $this->id,
            'width' => '6',
            'section' => $this->section // Customizer section to focus when click settings
        );
    }

    function customize() {
        $section = $this->section;
        $fn = array( $this, 'render' );
        $config = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title' => $this->label,
                'description' => sprintf( __( 'Assign <a href="#menu_locations"  class="focus-section">Menu Location</a> for %1$s', 'customify' ), $this->label )
            ),

            array(
                'name' => $this->prefix.'_style',
                'type' => 'select',
                'section' => $section,
                'selector' => $this->selector,
                'render_callback' => $fn,
                'title' => __( 'Style', 'customify' ),
                'choices' => array(
                    'style_default' => __( 'Default', 'customify' ),
                    'style_2' => __( 'Style 2', 'customify' ),
                )
            ),

            array(
                'name' => $this->prefix.'_item_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'title' => __( 'Item Padding', 'customify' ),
                'selector' => $this->selector.' li a',
                'device_settings' => true,
                'css_format' => array(
                    'unit' => '',
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
            ),

            array(
                'name' => $this->prefix.'_item_margin',
                'type' => 'css_ruler',
                'section' => $section,
                'selector' => $this->selector.' .menu li',
                'device_settings' => true,
                'css_format' => array(
                    'top' => 'margin-top: {{value}};',
                    'right' => 'margin-right: {{value}};',
                    'bottom' => 'margin-bottom: {{value}};',
                    'left' => 'margin-left: {{value}};',
                ),
                'title'  => __( 'Item Margin', 'customify' ),
            ),

            array(
                'name' => $this->prefix.'_item_color',
                'type' => 'color',
                'section' => $section,
                'title'  => __( 'Item Color', 'customify' ),
                'selector'  => "{$this->selector} li a, {$this->selector} li",
                'device_settings' => true,
                'css_format'  => 'color: {{value}}; text-decoration-color: {{value}};',
            ),

            array(
                'name' => $this->prefix.'_item_color_hover',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Item Color Hover', 'customify' ),
                'device_settings' => true,
                'selector'  => "{$this->selector} li a:hover, {$this->selector} li:hover > span, {$this->selector}",
                'css_format'  => 'color: {{value}}; text-decoration-color: {{value}};',
            ),

            array(
                'name' => $this->prefix.'_typography',
                'type' => 'group',
                'section'     => $section,
                'title'          => __( 'Typography', 'customify' ),
                'description'    => __( 'This is description',  'customify' ),
                'field_class' => 'customify-typography-control',
                'selector' => $this->selector,
                'css_format' => 'typography',
                'default' => array(

                ),
                'fields' => array(

                    array(
                        'name' => 'font',
                        'type' => 'font',
                        'label' => __( 'Font', 'customify' ),
                    ),

                    array(
                        'name' => 'font_style',
                        'type' => 'font_style',
                        'label' => __( 'Font Style', 'customify' ),
                    ),

                    array(
                        'name' => 'font_size',
                        'type' => 'slider',
                        'label' => __( 'Font Size', 'customify' ),
                        'device_settings' => true,
                    ),

                    array(
                        'name' => 'line_height',
                        'type' => 'slider',
                        'label' => __( 'Height', 'customify' ),
                        'device_settings' => true,
                    ),

                    array(
                        'name' => 'letter_spacing',
                        'type' => 'slider',
                        'label' => __( 'Letter Spacing', 'customify' ),
                        'min' => -10,
                        'max' => 10,
                    ),

                )
            ),

        );

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
    }


    function render(){

        $style = sanitize_text_field( Customify_Customizer()->get_setting($this->prefix.'_style') );

        wp_nav_menu( array(
            'theme_location' => $this->theme_location,
            'container' => 'nav',
            'container_id' => 'site-navigation-__id__-__device__',
            'container_class' => $this->id.' '. $this->id.'-__id__ nav-menu-__device__ '.$this->id.'-__device__'.( $style ? ' '.$style : '' ),
            'menu_id'        => $this->id.'-__id__-__device__',
            'fallback_cb' => false
        ) );

    }

}


Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Primary_Menu() );


class Customify_Builder_Item_Secondary_Menu extends  Customify_Builder_Item_Primary_Menu {

    public $id;
    public $label;
    public $prefix;
    public $selector;
    public $section;
    public $theme_location;

    /**
     * Optional construct
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->label = __( 'Secondary Menu', 'customify' );
        $this->id = 'secondary_menu';
        $this->prefix = 'secondary_menu';
       // $this->selector = '.secondary-menu';
        $this->selector = '.builder-item--'.$this->id .' .secondary-menu';
        $this->section = 'header_menu_secondary';
        $this->theme_location = 'menu-2';
    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Secondary_Menu() );
