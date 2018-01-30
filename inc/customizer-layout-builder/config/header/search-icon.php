<?php

class Customify_Builder_Item_Search_Icon {
    public $id = 'search_icon';
    function item(){
        return array(
            'name' => __( 'Search Icon', 'customify' ),
            'id' => 'search_icon',
            'col' => 0,
            'width' => '3',
            'section' => 'header_search' // Customizer section to focus when click settings
        );
    }

    function customize(){
        $section = 'header_search_icon';
        $fn = array( $this, 'render' );
        $mobile_selector = '.mobile-search-form-sidebar';
        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title' => __( 'Search', 'customify' ),
            ),

            array(
                'name' => 'header_search_placeholder',
                'type' => 'text',
                'section' => $section,
                'selector' => '.header-search-form',
                'render_callback' => $fn,
                'default' => __( 'Search...', 'customify' ),
                'title' => __( 'Placeholder Text', 'customify' ),
            ),

            array(
                'name' => 'header_search_btn',
                'type' => 'checkbox',
                'section' => $section,
                'selector' => '.'.$section.'-item',
                'render_callback' => $fn,
                'title' => __( 'Show Submit Button', 'customify' ),
                'default' => 1,
                'checkbox_label' => __( 'Show Submit Button', 'customify' ),
            ),

            array(
                'name' => 'header_search_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'selector' => '..builder-first--search',
                'css_format' => 'text-align: {{value}};',
                'title' => __( 'Align', 'customify' ),
                'device_settings' => true,
            ),

            array(
                'name' => 'header_search_mobile_h',
                'type' => 'heading',
                'section' => $section,
                'title' => __( 'Mobile Search Sidebar', 'customify' ),
                'description' => __( 'Heading description', 'customify' ),
            ),

            array(
                'name' => 'header_search_mobile_icon_size',
                'type' => 'slider',
                'max' => '150',
                'section' => $section,
                'selector' => '.builder-item--search .search-toggle i',
                'css_format' => 'font-size: {{value}};',
                'title' => __( 'Mobile Icon Size', 'customify' ),
            ),
        );

        // Item Layout
        return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
    }

    function render(){
        $placeholder = Customify_Customizer()->get_setting( 'header_search_placeholder' );
        $show_btn = Customify_Customizer()->get_setting( 'header_search_btn' );
        ?>
        <form role="search" method="get" class="search-form--__device__ hide-on-mobile hide-on-tablet header-search-form search-form " action="<?php echo home_url( '/' ); ?>">
            <div class="search-form-inner">
                <input type="text" name="s" class="s" placeholder="<?php echo esc_attr( $placeholder ); ?>">
                <?php if ( $show_btn ) { ?>
                    <input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', 'search submit', 'customify' ); ?>" />
                <?php } ?>
                <a class="close hide-on-desktop" href="#"><?php echo Customify_Customizer_Layout_Builder_Frontend()->close_icon(); ?></a>
            </div>
        </form>
        <span class="search-toggle hide-on-desktop"><i class="fa fa-search"></i></span>
        <?php
    }
}


Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Search_Icon() );



