<?php

class Customify_Builder_Item_HTML {
    public $id = 'html';
    public $section = 'header_html';
    public $name = 'header_html';
    public $label = '';
    function __construct()
    {
        $this->label = __( 'HTML', 'customify' );
    }

    function item(){
        return array(
            'name' => __( 'HTML', 'customify' ),
            'id' => $this->id,
            'col' => 0,
            'width' => '4',
            'section' => $this->section // Customizer section to focus when click settings
        );
    }
    function customize(){
        // Render callback function
        $fn = array( $this, 'render' );
        return array(
            array(
                'name' => $this->section,
                'type' => 'section',
                'panel' => 'header_settings',
                'title' => $this->label,
            ),

            array(
                'name' => $this->name,
                'type' => 'textarea',
                'section' => $this->section,
                'selector' => '.builder-header-html-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'title' => __( 'HTML', 'customify' ),
                'description' => __( 'Arbitrary HTML code.', 'customify' ),
            ),

            array(
                'name' => $this->name.'_text_align',
                'type' => 'text_align',
                'section' => $this->section,
                'selector' => '.builder-item--'.$this->id,
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
                'device_settings' => true,
            ),

        );
    }

    function render(){
        $content = Customify_Customizer()->get_setting( $this->name );
        echo '<div class="builder-header-html-item item--html">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
        echo '</div>';
    }
}

class Customify_Builder_Item_HTML_2 extends  Customify_Builder_Item_HTML {
    public $id = 'html_2';
    public $section = 'header_html_2';
    public $name = 'header_html_2';
    public $label = '';
    function __construct()
    {
        parent::__construct();
        $this->label = __( 'HTML 2', 'customify' );
    }
    function item(){
        return array(
            'name' => __( 'HTML 2', 'customify' ),
            'id' => $this->id,
            'col' => 0,
            'width' => '4',
            'section' => $this->section // Customizer section to focus when click settings
        );
    }
}


Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_HTML() );
Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_HTML_2() );
