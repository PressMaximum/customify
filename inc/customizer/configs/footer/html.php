<?php

class Customify_Builder_Footer_Item_HTML {
    public $id = 'html'; // Required
    public $section = 'footer_html'; // Optional
    public $name = 'footer_html'; // Optional
    public $label = ''; // Optional

    /**
     * Optional construct
     *
     * Customify_Builder_Item_HTML constructor.
     */
    function __construct()
    {
        $this->label = __( 'HTML', 'customify' );
    }

    /**
     * Register Builder item
     * @return array
     */
    function item(){
        return array(
            'name' => __( 'HTML', 'customify' ),
            'id' => $this->id,
            'col' => 0,
            'width' => '6',
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
        return array(
            array(
                'name' => $this->section,
                'type' => 'section',
                'panel' => 'footer_settings',
                'title' => $this->label,
            ),

            array(
                'name' => $this->name,
                'type' => 'textarea',
                'section' => $this->section,
                'selector' => '.builder-footer-html-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'default' => __( 'Add custom text here or remove it', 'customify' ),
                'title' => __( 'HTML', 'customify' ),
                'description' => __( 'Arbitrary HTML code.', 'customify' ),
            ),
            array(
                'name' => $this->name.'_text_align',
                'type' => 'text_align',
                'section' => $this->section,
                'selector' => '.builder-first--html',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
                'device_settings' => true,
            ),
        );
    }

    /**
     * Optional. Render item content
     */
    function render(){
        $content = Customify()->get_setting( $this->name );
        echo '<div class="builder-footer-html-item item-footer--html">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
        echo '</div>';
    }
}

Customify_Customize_Layout_Builder()->register_item('footer', new Customify_Builder_Footer_Item_HTML() );
