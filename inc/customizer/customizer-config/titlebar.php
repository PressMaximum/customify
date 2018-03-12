<?php

class Customify_TitleBar {
    static $_instance = null;
    static $is_showing = null;

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            add_filter( 'customify/customizer/config', array( self::$_instance, 'config' ) );
            add_action('customify/site-start',  array( self::$_instance, 'render' ), 45 );
            add_action('customify_is_post_title_display',  array( self::$_instance, 'display_page_title' ), 45 );
        }
        return self::$_instance;
    }

    function config( $configs ){
        $section = 'titlebar';
        $render_cb_el = array( $this, 'render' );
        $selector = '#page-titlebar';
        $config = array(

            array(
                'name'           => $section,
                'type'           => 'section',
                'panel'          => 'layout_panel',
                'title'          => __( 'Titlebar', 'customify-pro' ),
            ),
            array(
                'name' => "{$section}_styling_h",
                'type' => 'heading',
                'section' =>  $section,
                'title' => __( 'Styling Settings', 'customify-pro' )
            ),

            array(
                'name' => $section.'_typo',
                'type' => 'typography',
                'section' => $section,
                'title'  => __( 'Typography', 'customify' ),
                'description'  => __( 'Typography for titlebar', 'customify' ),
                'selector' => "{$selector}",
                'css_format' => 'typography',
            ),

            array(
                'name' => $section.'_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Styling', 'customify-pro' ),
                'selector' => array(
                    'normal' => "{$selector}",
                    'normal_text_color' => "{$selector} h1",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image' => false,
                        'bg_cover' => false,
                        'bg_repeat' => false,
                        //'box_shadow' => false,
                    ),
                    'hover_fields' => false
                )
            ),

            array(
                'name' => "{$section}_align",
                'type' => 'text_align_no_justify',
                'section' => $section,
                'device_settings' => true,
                'selector' => "$selector",
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Text Align', 'customify-pro' ),
            ),

            array(
                'name' => "{$section}_display_h",
                'type' => 'heading',
                'section' =>  $section,
                'title' => __( 'Display Settings', 'customify-pro' )
            ),

            array(
                'name' => "{$section}_display_cat",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on categories', 'customify-pro' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name' => "{$section}_display_search",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on search', 'customify-pro' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name' => "{$section}_display_archive",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on archive', 'customify-pro' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name' => "{$section}_display_page",
                'type' => 'checkbox',
                'default' => 1,
                'section' =>  $section,
                'checkbox_label' => __( 'Display on single page', 'customify-pro' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name' => "{$section}_display_post",
                'type' => 'checkbox',
                'default' => 1,
                'section' =>  $section,
                'checkbox_label' => __( 'Display on single post', 'customify-pro' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),

        );

        $config = array_merge( $config, $config );
        return array_merge( $configs, $config );
    }


    function is_showing(){
        if ( self::$is_showing === null ) {

            $is_showing = true;

            if ( is_category() ){
                if ( ! Customify_Customizer()->get_setting( 'titlebar_display_cat' ) ) {
                    $is_showing = false;
                }
            } elseif( is_search() ) {
                if ( ! Customify_Customizer()->get_setting( 'titlebar_display_search' ) ) {
                    $is_showing = false;
                }
            } elseif( is_archive() ) {
                if ( ! Customify_Customizer()->get_setting( 'titlebar_display_archive' ) ) {
                    $is_showing = false;
                }
            } elseif ( is_page() || ( ! is_front_page() && is_home() ) || ( is_front_page() && ! is_home() )  ) { // is page or page for posts or is front page
                if ( ! Customify_Customizer()->get_setting( 'titlebar_display_page' ) ) {
                    $is_showing = false;
                }
            }  elseif ( is_single() ) {
                if ( ! Customify_Customizer()->get_setting( 'titlebar_display_post' ) ) {
                    $is_showing = false;
                }
            }

            self::$is_showing = apply_filters('customify-pro/titlebar/is-showing', $is_showing );
        }
        return self::$is_showing;
    }

    function display_page_title( $show ){
        if ( $this->is_showing() ) {
            $show = false;
        }
        return $show;
    }

    function render(){
        $showing = apply_filters( 'customify/titlebar/check-showing', true );
        if ( ! $showing ) {
            return '';
        }

        if ( ! $this->is_showing() ){
            return '';
        }

        ?>
        <div id="page-titlebar" class="page-titlebar">
            <div class="page-titlebar-inner customify-container">
                <h1>
                    <?php if ( Customify_Init()->is_using_post() ) {
                        echo get_the_title( Customify_Init()->get_current_post_id() );
                    } elseif( is_search() ) {
                        printf( // WPCS: XSS ok.
                        /* translators: 1: Search query name */
                            __( 'Search Results for: %s', 'customify-pro' ),
                            '<span>' . get_search_query() . '</span>'
                        );
                    } else {
                        the_archive_title();
                    }

                    ?>
                </h1>
                <?php do_action('customify/titlebar/after-title'); ?>
            </div>
        </div>
        <?php
    }

}
Customify_TitleBar::get_instance();

