<?php

class Customify_TitleBar {
    static $_instance = null;
    static $is_showing = null;

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            add_filter( 'customify/customizer/config', array( self::$_instance, 'config' ) );
            if (! is_admin() ) {
                add_action('wp_head',  array( self::$_instance, 'display' ), 15 );
            }

        }
        return self::$_instance;
    }

    function display(){
        add_action('customify/site-start',  array( self::$_instance, 'render' ), 45 );
        add_filter('customify_is_post_title_display', array( self::$_instance, 'display_page_title' ), 65 );
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
                'title'          => __( 'Titlebar', 'customify' ),
            ),
            array(
                'name' => "{$section}_styling_h",
                'type' => 'heading',
                'section' =>  $section,
                'title' => __( 'Styling Settings', 'customify' )
            ),

            array(
                'name' => $section.'_typo',
                'type' => 'typography',
                'section' => $section,
                'title'  => __( 'Typography', 'customify' ),
                'description'  => __( 'Typography for titlebar', 'customify' ),
                'selector' => "{$selector} .titlebar-title",
                'css_format' => 'typography',
            ),

            array(
                'name' => $section.'_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Styling', 'customify' ),
                'selector' => array(
                    'normal' => "{$selector}",
                    'normal_text_color' => "{$selector} .titlebar-title",
                    'normal_padding' => "{$selector} .page-titlebar-inner",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image' => false,
                        'bg_cover' => false,
                        'bg_repeat' => false,
                        'margin' => false,
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
                'title'   => __( 'Text Align', 'customify' ),
            ),

            array(
                'name' => "{$section}_display_h",
                'type' => 'heading',
                'section' =>  $section,
                'title' => __( 'Display Settings', 'customify' )
            ),

            array(
                'name' => "{$section}_display_cat",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on categories', 'customify' ),
                'selector' => $selector,
                'default' => 1,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name' => "{$section}_display_search",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on search', 'customify' ),
                'selector' => $selector,
                'default' => 1,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name' => "{$section}_display_archive",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on archive', 'customify' ),
                'selector' => $selector,
                'default' => 1,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name' => "{$section}_display_page",
                'type' => 'checkbox',
                'default' => 1,
                'section' =>  $section,
                'checkbox_label' => __( 'Display on single page', 'customify' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name' => "{$section}_display_post",
                'type' => 'checkbox',
                'section' =>  $section,
                'checkbox_label' => __( 'Display on single post', 'customify' ),
                'selector' => $selector,
                'render_callback' => $render_cb_el,
            ),

        );

        $config = apply_filters( 'customify/titlebar/config', $config, $this );
        return array_merge( $configs, $config );
    }


    function is_showing(){
        if ( self::$is_showing === null ) {

            $is_showing = true;

            if ( is_category() ){
                if ( ! Customify()->get_setting( 'titlebar_display_cat' ) ) {
                    $is_showing = false;
                }
            } elseif( is_search() ) {
                if ( ! Customify()->get_setting( 'titlebar_display_search' ) ) {
                    $is_showing = false;
                }
            } elseif( is_archive() ) {
                if ( ! Customify()->get_setting( 'titlebar_display_archive' ) ) {
                    $is_showing = false;
                }
            } elseif ( is_page() || ( ! is_front_page() && is_home() ) || ( is_front_page() && ! is_home() )  ) { // is page or page for posts or is front page
                if ( ! Customify()->get_setting( 'titlebar_display_page' ) ) {
                    $is_showing = false;
                }
            }  elseif ( is_single() ) {
                if ( ! Customify()->get_setting( 'titlebar_display_post' ) ) {
                    $is_showing = false;
                }
            }

            // Do not show if page settings disable page title
            if ( Customify()->is_using_post() ) {
                $disable = get_post_meta(Customify()->get_current_post_id(), '_customify_disable_page_title', true);
                if ( $disable ) {
                    $is_showing = false;
                }
            }

            if ( is_home() && is_front_page() ) {
                $is_showing = false;
            }

            self::$is_showing = apply_filters('customify/titlebar/is-showing', $is_showing );

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
        if ( ! $this->is_showing() ){
            return '';
        }

        if ( Customify()->is_using_post() ) {
            $title =  get_the_title( Customify()->get_current_post_id() );
        } elseif( is_search() ) {
            $title = sprintf( // WPCS: XSS ok.
            /* translators: 1: Search query name */
                __( 'Search Results for: %s', 'customify' ),
                '<span>' . get_search_query() . '</span>'
            );
        } else {
            $title = get_the_archive_title();
        }

        $args = array(
            'title' => $title,
            'tag' => 'h1'
        );

        $args = apply_filters( 'customify/titlebar/args', $args );

        ?>
        <div id="page-titlebar" class="page-titlebar">
            <div class="page-titlebar-inner customify-container">
                <?php
                // WPCS: XSS ok.
                echo '<'.$args['tag'].' class="titlebar-title h2">'.$args['title'].'</'.$args['tag'].'>';
                ?>
                <?php do_action('customify/titlebar/after-title'); ?>
            </div>
        </div>
        <?php
    }

}
Customify_TitleBar::get_instance();

