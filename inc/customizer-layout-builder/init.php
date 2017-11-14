<?php

class _Beacon_Customizer_Layout_Builder {
    static $_instance;
    function __construct()
    {
        require_once get_template_directory().'/inc/customizer-layout-builder/config/header/panel.php';
        if ( is_admin() ) {
            add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
            add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
        }
        add_action( 'wp_ajax__beacon_builder_save_template', array( $this, 'ajax_save_template' ) );
    }

    function ajax_save_template(){

        if ( ! current_user_can('edit_theme_options' ) ) {
            wp_send_json_error( __( 'Access denied', '_beacon' ) );
        }

        $id = sanitize_text_field( $_POST['id'] );
        $control = sanitize_text_field( $_POST['control'] );
        $save_name = sanitize_text_field( $_POST['name'] );
        $data = wp_unslash( $_POST['preview_data'] );
        $fn = '_beacon_customizer_get_'.$id.'_config' ;

        if ( ! function_exists( $fn ) ){
            wp_send_json_error( __( 'No Support', '_beacon' ) );
        }

        $theme_name = wp_get_theme()->get('Name');
        $option_name = $theme_name.'_saved_templates';

        $saved_templates = get_option( $option_name );
        if ( ! is_array( $saved_templates ) ) {
            $saved_templates = array();
        }

        if ( isset( $_POST['remove'] ) ) {
            $remove = sanitize_text_field( $_POST['remove'] );
            if ( isset( $saved_templates[ $remove ] ) ) {
                unset( $saved_templates[ $remove ] );
            }

            update_option( $option_name, $saved_templates );
            wp_send_json_success();
        }


        $config = call_user_func_array( $fn, array() );
        $new_template_data = array();

        foreach ( $config as $k => $field ) {
            if ( $field['type'] != 'panel' && $field['type'] != 'section' ) {
                $name = $field['name'];
                $value = isset( $data[ $name ] ) ? $data[ $name ] : '';

                if ( ! is_array( $value ) ) {
                    $value = json_decode( urldecode_deep( $value ), true );
                }

                $s = new _Beacon_Sanitize_Input( $field, $field );

                $value = $s->sanitize( $value, $field );
                $new_template_data[ $name ] = $value;
            }
        }


        if ( ! $save_name ) {
            $key_id = date_i18n( 'Y-m-d H:i:s', current_time('timestamp') );
            $save_name = sprintf( __( 'Saved %s', '_beacon' ), $key_id );
        } else {
            $key_id = $save_name;
        }

        $saved_templates[ $key_id ] = array(
            'name' => $save_name,
            'image' => '',
            'data' => $new_template_data
        );

        update_option( $option_name, $saved_templates );

        $html = '<li class="saved_template" data-control-id="'.esc_attr( $control ).'" data-id="'.esc_attr( $key_id ).'" data-data="'.esc_attr( json_encode( $new_template_data ) ).'">'.esc_html( $save_name ).' <a href="#" class="load-tpl">'.__( 'Load', '_beacon' ).'</a><a href="#" class="remove-tpl">'.__( 'Remove', '_beacon' ).'</a></li>';

        wp_send_json_success( array( 'key_id' => $key_id, 'name' => $save_name, 'li' => $html ) );


        die();
    }


    static function get_header_sections(){
        $elements = array(
            'row-top',
            'row-main',
            'row-bottom',
            'row-sidebar',

            'templates',

            'logo',
            'primary-menu',
            'nav-icon',
            'search',
            'button',
            'icon-list',
            'user',
            'social-icons',
            'languages',
            'html',
        );

        return $elements;
    }

    function scripts(){

        wp_enqueue_script( '_beacon-layout-builder', get_template_directory_uri() . '/assets/js/customizer/builder.js', array( 'customize-controls', 'jquery-ui-resizable', 'jquery-ui-droppable', 'jquery-ui-draggable' ), false, true );
        wp_localize_script( '_beacon-layout-builder',  '_Beacon_Layout_Builder',  array(

            'header' => array(
                'id'         => 'header',
                'control_id' => 'header_builder_panel',
                'panel'      => 'header_settings',
                'section'    => 'header_builder_panel',
                'items'      => $this->get_header_items(),
                'devices' => array(
                    'desktop'   => __( 'Desktop', '_beacon' ),
                    'mobile'    => __( 'Mobile/Tablet', '_beacon' ),
                ),
            )

        ) );
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    function get_header_items(){
        $items = array(
            array(
                'name' => __( 'Logo', '_beacon' ),
                'id' => 'logo',
                'width' => '3',
                'section' => 'header_logo' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Nav Icon', '_beacon' ),
                'id' => 'nav-icon',
                'width' => '3',
                'section' => 'header_nav_icon' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Primary Menu', '_beacon' ),
                'id' => 'nav-menu',
                'width' => '6',
                'section' => 'header_menu_primary' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Search', '_beacon' ),
                'id' => 'search',
                'col' => 0,
                'width' => '3',
                'section' => 'header_search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Social Icons', '_beacon' ),
                'id' => 'social-icons',
                'col' => 0,
                'width' => '4',
                'section' => 'header_social_icons' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Button', '_beacon' ),
                'id' => 'button',
                'col' => 0,
                'width' => '4',
                'section' => 'header_button' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Icon List', '_beacon' ),
                'id' => 'icon-list',
                'col' => 0,
                'width' => '4',
                'section' => 'header_icon_list' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'HTML', '_beacon' ),
                'id' => 'html',
                'col' => 0,
                'width' => '4',
                'section' => 'header_html' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'User', '_beacon' ),
                'id' => 'user',
                'col' => 0,
                'width' => '4',
                'section' => 'header_user' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Languages', '_beacon' ),
                'id' => 'languages',
                'col' => 0,
                'width' => '4',
                'section' => 'header_languages' // Customizer section to focus when click settings
            ),

        );


        $items = apply_filters( '_beacon/builder/header/items', $items );
        $new_items = array();
        foreach (  $items as $k => $i ) {
            $new_items[ $i['id'] ] = $i;
        }

        return $new_items;
    }

    function template(){
        ?>
        <script type="text/html" id="tmpl-_beacon--builder-panel">
            <div class="_beacon--customize-builder">
                <div class="_beacon--cb-inner">
                    <div class="_beacon--cb-header">
                        <div class="_beacon--cb-devices-switcher">
                        </div>
                        <div class="_beacon--cb-actions">
                            <a data-id="{{ data.id }}_templates" class="focus-section" href="#"><?php _e( 'Templates', '_beacon' ); ?></a>
                            <a class="_beacon--panel-close" href="#"><?php _e( 'Close', '_beacon' ); ?></a>
                        </div>
                    </div>
                    <div class="_beacon--cb-body"></div>
                </div>
            </div>
        </script>


        <script type="text/html" id="tmpl-_beacon--cb-panel">
            <div class="_beacon--cp-rows">
                <div class="_beacon--row-top _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="top" href="#"></a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="top"></div>
                    </div>
                </div>
                <div class="_beacon--row-main _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="main" href="#"></a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="main"></div>
                    </div>
                </div>
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="bottom" href="#"></a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="bottom"></div>
                    </div>
                </div>
            </div>

            <# if ( data.device != 'desktop' ) { #>
            <div class="_beacon--cp-sidebar">
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" data-id="sidebar" href="#"></a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items _beacon--sidebar-items grid-stack----" data-id="sidebar"></div>
                    </div>
                </div>
            <div>
            <# } #>

        </script>

        <script type="text/html" id="tmpl-_beacon--cb-item">
            <div class="grid-stack-item item-from-list for-s-{{ data.section }}"
                 title="{{ data.name }}"
                 data-id="{{ data.id }}"
                 data-section="{{ data.section }}"
                 data-gs-x="{{ data.x }}" data-gs-y="{{ data.y }}"
                 data-gs-width="{{ data.width }}" data-gs-height="1"
            >
                <div class="item-tooltip">{{ data.name }}</div>
                <div class="grid-stack-item-content">
                    <span class="_beacon--cb-item-name">{{ data.name }}</span>
                    <span class="_beacon--cb-item-remove _beacon-cb-icon"></span>
                    <span class="_beacon--cb-item-setting _beacon-cb-icon" data-section="{{ data.section }}"></span>
                </div>
            </div>
        </script>
        <?php
    }

}

new _Beacon_Customizer_Layout_Builder();

function _Beacon_Customizer_Layout_Builder(){

}


class _Beacon_Customizer_Layout_Builder_Frontend {
     private $control_id = 'header_builder_panel';
     private $id = 'header';
     private $render_items = array();
     private $rows = array();

     public function __construct()
     {

     }

     function get_settings(){
         $data =  get_theme_mod( $this->control_id );
         $data = wp_parse_args( $data, array(
             'desktop' => '',
             'tablet' => '',
             'mobile' => '',
         ) );

         foreach ( $data as $k => $v ) {
             if ( ! is_array( $v ) ) {
                 $v = array();
             }
             $data[ $k ] = $v;
         }

         return $data;
     }

     function get_row_settings( $row_id, $device = 'desktop' ){
        $data = $this->get_settings();
        if ( isset( $data[ $device ] ) ) {
            if ( isset( $data[ $device ][ $row_id ] ) ) {
                return ! empty( $data[ $device ][ $row_id ] ) ? $data[ $device ][ $row_id ] : false;
            }
        }
        return false;
     }

     function render_items(){
         $setting = $this->get_settings();
         $items = array();

         foreach ( $setting as $device => $device_settings ) {
            foreach ( $device_settings as $row_id => $row_items ) {
                if ( ! isset( $this->rows[ $row_id ] ) ) {
                    $this->rows[ $row_id ] = array();
                }

                if ( is_array( $row_items ) && count( $row_items ) ) {

                    $this->rows[ $row_id ][ $device ] = $device;

                    foreach ( $row_items as $item_index => $item ) {

                        $item = wp_parse_args( $item, array(
                            'x' => '',
                            'width' => '1',
                            'id' => '',
                        ) );

                        if ( ! isset( $items[ $item['id'] ] ) ) {
                            $items[ $item['id'] ] = array(
                                'render_content' => '',
                                'devices' => array(),
                                'rows' => array(),
                                'id' => $item['id']
                            );
                        }

                        if ( ! $items[ $item['id'] ] ['render_content'] ) {
                            ob_start();
                            $id = str_replace('-', '_', $item['id']);
                            $fn = '_beacon_builder_' . $id . '_item';
                            $has_cb = false;
                            $return_render = false;
                            if (function_exists($fn)) {
                                $return_render = call_user_func_array($fn, array($item));
                                $has_cb = true;
                            } else {
                                $fn = '_beacon_builder_' . $this->id . '_' . $id . '_item';
                                if (function_exists($fn)) {
                                    $return_render = call_user_func_array($fn, array($item));
                                    $has_cb = true;
                                }
                            }

                            if ( ! $has_cb ) {
                                printf( __( 'Callback function <strong>%s</strong> do not exists.', '_beacon' ), $fn );
                            }

                            $ob_render = ob_get_clean();

                            if ( ! $return_render ) {
                                if ( $ob_render ) {
                                    $return_render = $ob_render;
                                }
                            }

                            if ( $return_render ) {
                                $items[ $item['id'] ] ['render_content'] = $return_render;
                            }
                        }

                        $items[ $item['id'] ]['added'] = false;

                        $items[ $item['id'] ]['devices'][ $device ] = array(
                                'x' => $item['x'],
                                'width' => $item['width'],
                                'id' => $item['id'],
                                'row' => $row_id,

                        );
                        if( isset( $items[ $item['id'] ]['rows'][ $row_id ] ) ) {
                            $items[ $item['id'] ]['rows'][ $row_id ] = array(
                                $items[ $item['id'] ]['rows'][ $row_id ]
                            );

                            $items[ $item['id'] ]['rows'][ $row_id ][] = $device;

                        } else {
                            $items[ $item['id'] ]['rows'][ $row_id ] = $device;
                        }


                    }
                }

            }
         }

         $this->render_items = $items;

         return $items;
     }

     function render_row( $items, $device = 'desktop' ){
         $row_html     = '';
         $count        = 0;
         $columns      = 0;
         $max_columns  = 12;
         $widget_count = count( $items );

         foreach ( $items as $item ) {

             $content = $this->render_items[$item['id']]['render_content'];

             $item_id = $item['id'];

             if ($content) {
                 $count++;
             } else {
                 $widget_count--;
                 continue;
             }


             // Used to calculate empty columns between widgets.
             $empty = 0;

             // Init array for widget row classes.
             $classes = array();

             // Calculate empty space between columns.
             if ($columns < intval($item['x'])) {
                 $empty = intval($item['x']) - $columns;
             }

             $atts = array();

             // Add pre class and add empty columns to $columns var.
             if (0 < $empty) {
                 $columns = $columns + $empty;
                 $atts[] = 'off-' . $empty;

             }

             $columns = $columns + intval($item['width']);
             $classes[] = '_beacon-col-' . intval($item['width']);

             if ($widget_count === $count) {
                 if ($max_columns === $columns) {
                     $classes[] = '_beacon-col-last';
                 } else {
                     $p = $max_columns - $columns;
                     // $classes[] = 'sp-header-post-' . ( $max_columns - $columns );
                     $atts[] = 'off-' . $p;
                 }

                 $count = 0;
             }


             echo '<div class="builder-item ' . join(' ', $classes) . '" data-item-id="' . esc_attr($item_id) . '" data-push-left="' . join(' ', $atts) . '">';
            // echo $content;
             echo $item_id;
             echo '</div>';
         }
     }

     function render(){
         $setting = $this->get_settings();
         $items = $this->render_items();

         $row_ids = array( 'top', 'main', 'bottom' );

         foreach ($row_ids as $row_id ) {

             $show_on_devices = $this->rows[ $row_id ];
             if ( ! empty( $show_on_devices ) ) {
                 $class = sprintf( '%1$s-%2$s', $this->id, $row_id );
                 ?>
                  <div class="<?php echo esc_attr( $class ); ?>" data-row-id="<?php echo esc_attr( $row_id ); ?>" data-show-on="<?php echo esc_attr( join( " ", $show_on_devices ) ); ?>">
                        <?php
                        $desktop_items = $this->get_row_settings( $row_id, 'desktop' );
                        if ( $desktop_items ) {
                            echo '<div class="hide-on-mobile hide-on-tablet _beacon-grid">';
                                $this->render_row( $desktop_items );
                            echo '</div>';
                        }

                        $mobile_items = $this->get_row_settings( $row_id, 'mobile' );
                        if ( $mobile_items ) {
                            echo '<div class="hide-on-desktop _beacon-grid">';
                            $this->render_row( $mobile_items );
                            echo '</div>';
                        }

                        ?>
                  </div>
                 <?php
             }

         } // end for each row_ids


     }

}




function _beacon_customize_render_header(){


    $b = new _Beacon_Customizer_Layout_Builder_Frontend();
    $b->render();

    $theme_name = wp_get_theme()->get('Name');
    $option_name = $theme_name.'_saved_templates';

    ?>
    <pre class="debug"><?php // print_r( $b->render_items()  ); ?></pre>
    <pre class="debug"><?php print_r( get_theme_mod( 'header_builder_panel' ) ); ?></pre>
    <?php
}



function __backup(){
    ?>
    <div class="header-main">
        <div class="_beacon-container">
            <div class="site-branding">
                <?php
                the_custom_logo();
                if ( is_front_page() && is_home() ) : ?>
                    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <?php else : ?>
                    <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                    <?php
                endif;

                $description = get_bloginfo( 'description', 'display' );
                if ( $description || is_customize_preview() ) : ?>
                    <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                    <?php
                endif; ?>
            </div><!-- .site-branding -->

            <nav id="site-navigation" class="main-navigation">
                <?php
                //                            wp_nav_menu( array(
                //                                'theme_location' => 'menu-1',
                //                                'menu_id'        => 'primary-menu',
                //                            ) );
                ?>
            </nav><!-- #site-navigation -->
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-main -->

    <div class="header-bottom">
        <div class="_beacon-container">
            header bottom
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-bottom -->
    <?php
}