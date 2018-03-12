<?php

class Customify_Breadcrumb {
    static $is_transparent = null;
    static $_instance = null;
    static $_settings = null;

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            add_filter( 'customify/customizer/config', array( self::$_instance, 'config' ) );

            add_action( 'wp_head', array( self::$_instance, 'display' ) );
        }
        return self::$_instance;
    }

    function display(){
        // Display position
        $display_pos = Customify_Customizer()->get_setting('breadcrumb_display_pos' );
        switch( $display_pos ) {
            case 'below_header': // below header
                add_action('customify/site-start',  array( self::$_instance, 'render' ), 15 );
                break;
            case 'below_titlebar':
                add_action('customify/site-start',  array( self::$_instance, 'render' ), 65 );
                break;
            case 'inside_titlebar':
                $cover = false;
                if ( class_exists( 'Customify_Pro_Header_Cover' ) ) {
                    $cover = Customify_Pro_Header_Cover::get_instance()->get_settings();
                }
                if ( is_array( $cover ) && ! $cover['hide'] ) {
                    add_action('customify/page-cover/content',  array( self::$_instance, 'render' ), 55 );
                } else {
                    add_action('customify/titlebar/after-title',  array( self::$_instance, 'render' ), 55 );
                }
                break;
            default:
                add_action('customify/site-start',  array( self::$_instance, 'render' ), 55 );
                break;
        }
    }

    function support_plugins_active(){
        $activated = false;
        if ( function_exists( 'bcn_display' ) ) {
            //if( is_plugin_active( 'breadcrumb-navxt/breadcrumb-navxt.php' ) ) {
                $activated = true;
            //}
        }

        return $activated;
    }

    function config( $configs ){
        $section = 'breadcrumb';
        $selector = '#page-breadcrumb';

        if( ! $this->support_plugins_active() ) {
            $desc = __( 'Your should install and active plugin <a target="_blank" href="https://wordpress.org/plugins/breadcrumb-navxt/">Breadcrumb NavXT</a> to use this function.', 'customify' );
        } else {
            $desc = '';
        }

        $panel = 'compatibility_panel';
        $config = array();
        $config[] = array(
            'name'           => $section,
            'type'           => 'section',
            'panel'          => $panel,
            'title'          => __( 'Breadcrumb', 'customify' ),
            'description'    => $desc,
        );

        $config[] = array(
            'name' => "{$section}_display_pos",
            'type' => 'select',
            'section' =>  $section,
            'default' => 1,
            'title' => __( 'Display Position', 'customify-pro' ),
            'choices' => apply_filters( 'customify/breadcrumb/config/positions', array(
                'below_header' => __( 'Display below header', 'customify-pro' ),
                'below_titlebar' => __( 'Display below titlebar', 'customify-pro' ),
                'inside_titlebar' => __( 'Display inside titlebar', 'customify-pro' ),
            ) ),
        );

        $config[] =  array(
            'name' => "{$section}_display_blog",
            'type' => 'checkbox',
            'section' =>  $section,
            'default' => 1,
            'checkbox_label' => __( 'Display on posts page', 'customify' ),
        );

        $config[] =  array(
            'name' => "{$section}_display_cat",
            'type' => 'checkbox',
            'section' =>  $section,
            'default' => 1,
            'checkbox_label' => __( 'Display on categories', 'customify' ),
        );

        $config[] = array(
            'name' => "{$section}_display_search",
            'type' => 'checkbox',
            'section' =>  $section,
            'default' => 1,
            'checkbox_label' => __( 'Display on search', 'customify' ),
        );

        $config[] = array(
            'name' => "{$section}_display_archive",
            'type' => 'checkbox',
            'default' => 1,
            'section' =>  $section,
            'checkbox_label' => __( 'Display on archive', 'customify' ),
        );

        $config[] = array(
            'name' => "{$section}_display_page",
            'type' => 'checkbox',
            'default' => false,
            'section' =>  $section,
            'checkbox_label' => __( 'Display on single page', 'customify' ),
        );

        $config[] = array(
            'name' => "{$section}_display_post",
            'type' => 'checkbox',
            'default' => 1,
            'section' =>  $section,
            'checkbox_label' => __( 'Display on single post', 'customify' ),
        );


        if ( Customify_Init()->is_woocommerce_active() ) {
            $config[] = array(
                'name' => "{$section}_display_shop",
                'type' => 'checkbox',
                'default' => 1,
                'section' =>  $section,
                'checkbox_label' => __( 'Display on shop and product page', 'customify' ),
            );
        }


        $config[] =  array(
            'name' => $section.'_typo',
            'type' => 'typography',
            'section' => $section,
            'title'  => __( 'Typography', 'customify' ),
            'description'  => __( 'Typography for breadcrumb', 'customify' ),
            'selector' => "{$selector}",
            'css_format' => 'typography',
        );

        $config[] = array(
            'name' => $section.'_styling',
            'type' => 'styling',
            'section' => $section,
            'title'  => __( 'Styling', 'customify' ),
            'description'  => __( 'Styling for breadcrumb', 'customify' ),
            'selector' => array(
                'normal' => "{$selector}, #page-titlebar {$selector}",
                'normal_box_shadow' => "{$selector}, #page-titlebar {$selector}",
                'normal_text_color' => "{$selector}, #page-titlebar {$selector}",
                'normal_link_color' => "{$selector} a, #page-titlebar {$selector} a",
                'hover_link_color' => "{$selector} a:hover, #page-titlebar {$selector} a:hover",
            ),
            'css_format' => 'styling', // styling
            'fields' => array(
                'normal_fields' => array(
                    'margin' => false // disable for special field.
                ),
                'hover_fields' => array(
                    'text_color' => false,
                    //'link_color' => false,
                    'padding' => false,
                    'bg_color' => false,
                    'bg_heading' => false,
                    'bg_cover' => false,
                    'bg_image' => false,
                    'bg_repeat' => false,
                    'border_heading' => false,
                    'border_color' => false,
                    'border_radius' => false,
                    'border_width' => false,
                    'border_style' => false,
                    'box_shadow' => false,
                ), // disable hover tab and all fields inside.
            )
        );

        $config = array_merge( $config, $config );
        return array_merge( $configs, $config );
    }

    function is_showing(){
        if( ! $this->support_plugins_active() ) {
            return false;
        }

        if( is_home() && is_front_page() ) {
            return false;
        }

        $is_showing = true;
        if ( (  is_home() && ! is_front_page() ) || (  is_home() && is_front_page() ) ) { // Posts page - Blog page
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_blog' ) ) {
                $is_showing = false;
            }
        } elseif ( is_category() ){
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_cat' ) ) {
                $is_showing = false;
            }
        } elseif( is_search() ) {
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_search' ) ) {
                $is_showing = false;
            }
        } elseif( is_archive() ) {
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_archive' ) ) {
                $is_showing = false;
            }
        } elseif ( is_page() ) { // is page or page for posts or is front page
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_page' ) ) {
                $is_showing = false;
            }
        }  elseif ( is_single() ) {
            if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_post' ) ) {
                $is_showing = false;
            }
        } else {
            $is_showing = false;
        }

        if ( Customify_Init()->is_woocommerce_active() ) {
            if ( is_shop() || is_product_taxonomy() || is_product() ) {
                if ( ! Customify_Customizer()->get_setting( 'breadcrumb_display_shop' ) ) {
                    $is_showing = false;
                } else {
                    $is_showing = true;
                }
            }
        }

        if ( Customify_Init()->is_using_post() ) {
            $id = Customify_Init()->get_current_post_id();
            $breadcrumb_display = get_post_meta( $id, '_customify_breadcrumb_display', true );
            if ( $breadcrumb_display == 'hide' ) {
                $is_showing = false;
            } elseif( $breadcrumb_display == 'show' ) {
                $is_showing = true;
            }
        }



        $is_showing = apply_filters( 'customify/breadcrumb/is-showing', $is_showing );

        return $is_showing;
    }

    /**
     * Display below header cover
     *
     * @return bool|string
     */
    function render(){
        if ( ! $this->is_showing() ) {
            return '';
        }
        $list = bcn_display_list(true);
        if ( $list ) {
?>
<div id="page-breadcrumb" class="page-breadcrumb">
    <div class="page-breadcrumb-inner customify-container">
        <ul class="page-breadcrumb-list">
        <?php
            echo $list;
        ?>
        </ul>
    </div>
</div>
<?php
        }
    }

}
Customify_Breadcrumb::get_instance();

