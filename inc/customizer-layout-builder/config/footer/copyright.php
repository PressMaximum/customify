<?php

class Customify_Builder_Footer_Item_Copyright {
    public $id = 'footer_copyright'; // Required
    public $section = 'footer_copyright'; // Optional
    public $name = 'footer_copyright'; // Optional
    public $label = ''; // Optional

    /**
     * Optional construct
     *
     */
    function __construct()
    {
        $this->label = __( 'Copyright', 'customify' );
    }

    /**
     * Register Builder item
     * @return array
     */
    function item(){
        return array(
            'name' => __( 'Copyright', 'customify' ),
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
                'selector' => '.builder-footer-copyright-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'title' => __( 'Copyright Content', 'customify' ),
                'description' => __( 'Arbitrary HTML code or shortcode.', 'customify' ),
            ),

            array(
                'name' => $this->name.'_text_align',
                'type' => 'text_align',
                'section' => $this->section,
                'selector' => '.builder-footer-copyright-item',
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
        $content = Customify_Customizer()->get_setting( $this->name );
        echo '<div class="builder-footer-copyright-item">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
        echo '</div>';
    }
}

Customify_Customizer_Layout_Builder()->register_item('footer', new Customify_Builder_Footer_Item_Copyright() );
