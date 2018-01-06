<?php

class Customify_Builder_Item_HTML {
    public $id = 'html'; // Required
    public $section = 'header_html'; // Optional
    public $name = 'header_html'; // Optional
    public $label = ''; // Optional

    /**
     * Optional construct
     *
     * Customify_Builder_Item_HTML constructor.
     */
    function __construct()
    {
        $this->label = __( 'HTML 1', 'customify' );
    }

    /**
     * Register Builder item
     * @return array
     */
    function item(){
        return array(
            'name' => __( 'HTML 1', 'customify' ),
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
        // Render callback function
        $fn = array( $this, 'render' );
        $config = array(
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
                'selector' => '.builder-header-'.$this->id.'-item',
                'render_callback' => $fn,
                'theme_supports' => '',
                'title' => __( 'HTML', 'customify' ),
                'description' => __( 'Arbitrary HTML code.', 'customify' ),
            ),

            array(
                'name' => $this->name.'_text_align',
                'type' => 'text_align',
                'section' => $this->section,
                'selector' => '.builder-first--'.$this->id,
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
                'device_settings' => true,
            ),

            // Merge Item
            array(
                'name' => 'header_'.$this->id.'_merge',
                'type' => 'select',
                'section' => $this->section,
                'selector' => '#masthead',
                'render_callback' => 'customify_customize_render_header',
                'priority' => 999,
                'title'   => __( 'Merge Item', 'customify' ),
                'description'   => __( 'Merge item with previous item.', 'customify' ),
                'choices' => array(
                    'no' => __( 'No', 'customify' ),
                    'desktop' => __( 'Merge on desktop', 'customify' ),
                    'mobile' => __( 'Merge on mobile', 'customify' ),
                    'both' => __( 'Merge on desktop & mobile', 'customify' ),
                )
            ),

        );

        // Merge Item
        $config[] = customify_header_merge_item_settings( $this->id, $this->section );
        return $config;
    }

    /**
     * Optional. Render item content
     */
    function render(){
        $content = Customify_Customizer()->get_setting( $this->name );
        echo '<div class="builder-header-'.esc_attr( $this->id ).'-item item--html">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
        echo '</div>';
    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_HTML() );
