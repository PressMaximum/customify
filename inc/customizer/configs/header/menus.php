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
        $this->selector = '.builder-item--'.$this->id .' .nav-menu-desktop .primary-menu-ul';
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
                'type'    => 'image_select',
                'section' => $section,
                'selector' => '.builder-item--'.$this->id ." .primary-menu",
                'render_callback' => $fn,
                'title'   => __( 'Menu Preset', 'customify' ),
                'default' => 'style-plain',
                'css_format' => 'html_class',
                'choices' => array(
                    'style-plain' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/menu_style_1.svg',
                    ),
                    'style-full-height' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/menu_style_2.svg',
                    ),
                    'style-border-bottom' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/menu_style_3.svg',
                    ),
                    'style-border-top' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/menu_style_4.svg',
                    ),
                )
            ),

            array(
                'name' => $this->prefix.'__hide-arrow',
                'type'    => 'checkbox',
                'section' => $section,
                'selector' => '.builder-item--'.$this->id ." .primary-menu",
                'checkbox_label' => __( 'Hide menu dropdown arrow', 'customify' ),
                'css_format' => 'html_class',
            ),

            array(
                'name' => $this->prefix.'_top_heading',
                'type' => 'heading',
                'section' => $section,
                'title'  => __( 'Top Menu', 'customify' ),
            ),

            array(
                'name' => $this->prefix.'_item_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Top Menu Items Styling', 'customify' ),
                'description'  => __( 'Styling for top level menu items', 'customify' ),
                'selector'  => array(
                    'normal' => "{$this->selector} > li > a",
                    'normal_margin' => "{$this->selector} > li",
                    'hover' => "{$this->selector} > li > a:hover, {$this->selector} > li.current-menu-item > a, {$this->selector} > li.current-menu-ancestor > a, {$this->selector} > li.current-menu-parent > a",
                    'hover_text_color' => "{$this->selector} > li > a:hover, {$this->selector} > li > a:focus, {$this->selector} > li.current-menu-item > a, {$this->selector} > li.current-menu-ancestor > a, {$this->selector} > li.current-menu-parent > a",
                ),
                'css_format'  => 'styling',
                'fields' => array(
                    'tabs' => array(
                        'normal' => __( 'Normal', 'customify' ),
                        'hover'  => __( 'Hover/Active', 'customify' ),
                    ),
                    'normal_fields' => array(
                        //'padding' => false // disable for special field.
                        'link_color' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                    ),
                    'hover_fields' => array(
                        'link_color' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                    ), // disable hover tab and all fields inside.
                )
            ),

            array(
                'name' => $this->prefix.'_typography',
                'type' => 'typography',
                'section'  => $section,
                'title' => __( 'Top Menu Items Typography', 'customify' ),
                'description' => __( 'Typography for menu',  'customify' ),
                'selector' => "{$this->selector} > li > a",
                'css_format' => 'typography',
            ),

            array(
                'name' => $this->prefix.'_submenu_heading',
                'type' => 'heading',
                'section' => $section,
                'title'  => __( 'Submenu', 'customify' ),
            ),

            array(
                'name' => $this->prefix.'_submenu_width',
                'type' => 'slider',
                'section' => $section,
                'selector' => $this->selector.' .sub-menu',
                'device_settings' => true,
                'css_format' => 'width: {{value}};',
                'title'  => __( 'Submenu Width', 'customify' ),
                'min' => 100,
                'max' => 500,
                'step' => 5
            ),

            array(
                'name' => $this->prefix.'_sub_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Submenu Styling', 'customify' ),
                'description'  => __( 'Advanced styling for submenu', 'customify' ),
                'selector'  => array(
                    'normal' => "{$this->selector} .sub-menu",
                    'hover' => "{$this->selector} .sub-menu",
                ),
                'css_format'  => 'styling',
                'fields' => array(
                    'normal_fields' => array(
                        //'margin' => true,
                        'padding' => false, // disable for special field.
                        'text_color' => false,
                        'link_color' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                    ),
                    'hover_fields' => false
                )
            ),

            array(
                'name' => $this->prefix.'_sub_item_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Submenu Items Styling', 'customify' ),
                'description'  => __( 'Styling for submenu items', 'customify' ),
                'selector'  => array(
                    'normal' => "{$this->selector} .sub-menu li a",
                    'hover' => "{$this->selector} .sub-menu li a:hover, {$this->selector} .sub-menu li a:focus",
                ),
                'css_format'  => 'styling',
                'fields' => array(
                    'tabs' => array(
                        'normal' => __( 'Normal', 'customify' ),
                        'hover'  => __( 'Hover/Active', 'customify' ),
                    ),
                    'normal_fields' => array(
                        //'padding' => false, // disable for special field.
                        'link_color' => false,
                        'margin' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                    ),
                    'hover_fields' => array(
                        'padding' => false,
                        'link_color' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                    ), // disable hover tab and all fields inside.
                )
            ),

            array(
                'name' => $this->prefix.'_typography_submenu',
                'type' => 'typography',
                'section'  => $section,
                'title' => __( 'Submenu Items Typography', 'customify' ),
                'description' => __( 'Typography for submenu items',  'customify' ),
                'selector'  => "{$this->selector} .sub-menu li a",
                'css_format' => 'typography',
            ),

        );

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
    }

    function render(){
        $style = sanitize_text_field( Customify()->get_setting($this->prefix.'_style') );
        if ( $style ) {
            $style = sanitize_text_field( $style );
        }

        $hide_arrow = sanitize_text_field( Customify()->get_setting($this->prefix.'__hide-arrow') );
        if ( $hide_arrow ) {
            $style.=' hide-arrow-active';
        }

        $container_classes = $this->id.' '. $this->id.'-__id__ nav-menu-__device__ '.$this->id.'-__device__'.( $style ? ' '.$style : '' );
        echo '<nav  id="site-navigation-__id__-__device__" class="site-navigation '.$container_classes.'">';
        wp_nav_menu( array(
            'theme_location' => $this->theme_location,
            'container' => false,
            'container_id' => false,
            'container_class' => false,
            'menu_id'    =>false,
            'menu_class'   => $this->id.'-ul menu nav-menu',
            'fallback_cb' => false,
            'link_before' => '<span class="link-before">',
            'link_after' => '</span>',
        ) );
        echo '</nav>';

    }
}



Customify_Customize_Layout_Builder()->register_item('header', new Customify_Builder_Item_Primary_Menu() );

function customify_add_icon_to_menu( $title, $item, $args, $depth ){
    if ( in_array( 'menu-item-has-children', $item->classes ) ) {
        $title.= '<span class="nav-icon-angle"></span>';
    }
    return $title;
}

add_filter( 'nav_menu_item_title', 'customify_add_icon_to_menu', 25, 4 );
