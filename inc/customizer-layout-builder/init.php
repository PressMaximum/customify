<?php

class Customify_Customizer_Layout_Builder {
    static $_instance;
    function __construct()
    {
        require_once get_template_directory().'/inc/customizer-layout-builder/config/header/panel.php';
        if ( is_admin() ) {
            add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
            add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
        }
        add_action( 'wp_ajax_customify_builder_save_template', array( $this, 'ajax_save_template' ) );
    }


    function ajax_save_template(){

        if ( ! current_user_can('edit_theme_options' ) ) {
            wp_send_json_error( __( 'Access denied', 'customify' ) );
        }

        $id = sanitize_text_field( $_POST['id'] );
        $control = sanitize_text_field( $_POST['control'] );
        $save_name = sanitize_text_field( $_POST['name'] );
        $data = wp_unslash( $_POST['preview_data'] );
        $fn = 'customify_customizer_get_'.$id.'_config' ;

        if ( ! function_exists( $fn ) ){
            wp_send_json_error( __( 'No Support', 'customify' ) );
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

                $s = new Customify_Sanitize_Input( $field, $field );

                $value = $s->sanitize( $value, $field );
                $new_template_data[ $name ] = $value;
            }
        }


        if ( ! $save_name ) {
            $key_id = date_i18n( 'Y-m-d H:i:s', current_time('timestamp') );
            $save_name = sprintf( __( 'Saved %s', 'customify' ), $key_id );
        } else {
            $key_id = $save_name;
        }

        $saved_templates[ $key_id ] = array(
            'name' => $save_name,
            'image' => '',
            'data' => $new_template_data
        );

        update_option( $option_name, $saved_templates );

        $html = '<li class="saved_template" data-control-id="'.esc_attr( $control ).'" data-id="'.esc_attr( $key_id ).'" data-data="'.esc_attr( json_encode( $new_template_data ) ).'">'.esc_html( $save_name ).' <a href="#" class="load-tpl">'.__( 'Load', 'customify' ).'</a><a href="#" class="remove-tpl">'.__( 'Remove', 'customify' ).'</a></li>';

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

        wp_enqueue_script( 'customify-layout-builder', get_template_directory_uri() . '/assets/js/customizer/builder.js', array( 'customize-controls', 'jquery-ui-resizable', 'jquery-ui-droppable', 'jquery-ui-draggable' ), false, true );
        wp_localize_script( 'customify-layout-builder',  'Customify_Layout_Builder',  array(

            'header' => array(
                'id'         => 'header',
                'control_id' => 'header_builder_panel',
                'panel'      => 'header_settings',
                'section'    => 'header_builder_panel',
                'items'      => $this->get_header_items(),
                'devices' => array(
                    'desktop'   => __( 'Desktop', 'customify' ),
                    'mobile'    => __( 'Mobile/Tablet', 'customify' ),
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
                'name' => __( 'Logo', 'customify' ),
                'id' => 'logo',
                'width' => '3',
                'section' => 'header_logo' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Nav Icon', 'customify' ),
                'id' => 'nav-icon',
                'width' => '3',
                'section' => 'header_nav_icon' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Primary Menu', 'customify' ),
                'id' => 'primary-menu',
                'width' => '6',
                'section' => 'header_menu_primary' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Search', 'customify' ),
                'id' => 'search',
                'col' => 0,
                'width' => '3',
                'section' => 'header_search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Social Icons', 'customify' ),
                'id' => 'social-icons',
                'col' => 0,
                'width' => '4',
                'section' => 'header_social_icons' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Button', 'customify' ),
                'id' => 'button',
                'col' => 0,
                'width' => '4',
                'section' => 'header_button' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Icon List', 'customify' ),
                'id' => 'icon-list',
                'col' => 0,
                'width' => '4',
                'section' => 'header_icon_list' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'HTML', 'customify' ),
                'id' => 'html',
                'col' => 0,
                'width' => '4',
                'section' => 'header_html' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'User', 'customify' ),
                'id' => 'user',
                'col' => 0,
                'width' => '4',
                'section' => 'header_user' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Languages', 'customify' ),
                'id' => 'languages',
                'col' => 0,
                'width' => '4',
                'section' => 'header_languages' // Customizer section to focus when click settings
            ),

        );


        $items = apply_filters( 'customify/builder/header/items', $items );
        $new_items = array();
        foreach (  $items as $k => $i ) {
            $new_items[ $i['id'] ] = $i;
        }

        return $new_items;
    }

    function template(){
        ?>
        <script type="text/html" id="tmpl-customify--builder-panel">
            <div class="customify--customize-builder">
                <div class="customify--cb-inner">
                    <div class="customify--cb-header">
                        <div class="customify--cb-devices-switcher">
                        </div>
                        <div class="customify--cb-actions">
                            <a data-id="{{ data.id }}_templates" class="focus-section" href="#"><?php _e( 'Templates', 'customify' ); ?></a>
                            <a class="customify--panel-close" href="#"><?php _e( 'Close', 'customify' ); ?></a>
                        </div>
                    </div>
                    <div class="customify--cb-body"></div>
                </div>
            </div>
        </script>


        <script type="text/html" id="tmpl-customify--cb-panel">
            <div class="customify--cp-rows">
                <div class="customify--row-top customify--cb-row">
                    <a class="customify--cb-row-settings" data-id="top" href="#"></a>
                    <div class="customify--row-inner">
                        <div class="customify--cb-items grid-stack gridster" data-id="top"></div>
                    </div>
                </div>
                <div class="customify--row-main customify--cb-row">
                    <a class="customify--cb-row-settings" data-id="main" href="#"></a>
                    <div class="customify--row-inner">
                        <div class="customify--cb-items grid-stack gridster" data-id="main"></div>
                    </div>
                </div>
                <div class="customify--row-bottom customify--cb-row">
                    <a class="customify--cb-row-settings" data-id="bottom" href="#"></a>
                    <div class="customify--row-inner">
                        <div class="customify--cb-items grid-stack gridster" data-id="bottom"></div>
                    </div>
                </div>
            </div>

            <# if ( data.device != 'desktop' ) { #>
            <div class="customify--cp-sidebar">
                <div class="customify--row-bottom customify--cb-row">
                    <a class="customify--cb-row-settings" data-id="sidebar" href="#"></a>
                    <div class="customify--row-inner">
                        <div class="customify--cb-items customify--sidebar-items grid-stack----" data-id="sidebar"></div>
                    </div>
                </div>
            <div>
            <# } #>

        </script>

        <script type="text/html" id="tmpl-customify--cb-item">
            <div class="grid-stack-item item-from-list for-s-{{ data.section }}"
                 title="{{ data.name }}"
                 data-id="{{ data.id }}"
                 data-section="{{ data.section }}"
                 data-gs-x="{{ data.x }}" data-gs-y="{{ data.y }}"
                 data-gs-width="{{ data.width }}" data-gs-height="1"
            >
                <div class="item-tooltip">{{ data.name }}</div>
                <div class="grid-stack-item-content">
                    <span class="customify--cb-item-name">{{ data.name }}</span>
                    <span class="customify--cb-item-remove customify-cb-icon"></span>
                    <span class="customify--cb-item-setting customify-cb-icon" data-section="{{ data.section }}"></span>
                </div>
            </div>
        </script>
        <?php
    }

}

new Customify_Customizer_Layout_Builder();

function Customify_Customizer_Layout_Builder(){
    return Customify_Customizer_Layout_Builder::get_instance();
}


class Customify_Customizer_Layout_Builder_Frontend {
     private $control_id = 'header_builder_panel';
     private $id = 'header';
     private $render_items = array();
     private $rows = array();
     private $data = false;
     private $config_items = false;

     public function __construct()
     {

     }

    function set_config_items( $config_items ){
        $this->config_items = $config_items;
    }

     function get_settings(){
         if ( $this->data ) {
             return $this->data;
         }
         $data = get_theme_mod( $this->control_id );
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

         $this->data = $data;

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

     function render_items( $list_items = array() ){
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

                        $item_config = isset( $this->config_items[ $item['id'] ] ) ? $this->config_items[ $item['id'] ] : array();

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
                            $fn = 'customify_builder_' . $id . '_item';
                            $has_cb = false;
                            $return_render = false;
                            if (function_exists($fn)) {
                                $return_render = call_user_func_array( $fn, array( $item_config , $item));
                                $has_cb = true;
                            } else {
                                $fn = 'customify_builder_' . $this->id . '_' . $id . '_item';
                                if (function_exists($fn)) {
                                    $return_render = call_user_func_array( $fn, array( $item_config , $item));
                                    $has_cb = true;
                                }
                            }

                            if ( ! $has_cb ) {
                                //echo $id;

                               printf( __( 'Callback function <strong>%s</strong> do not exists.', 'customify' ), $fn );
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

    /**
     * Sort items by their position on the grid.
     * @access  private
     * @since   1.0.0
     * @return  array
     */
    private function _sort_items_by_position( $items = array() ) {
        $ordered_items = array();

        foreach ( $items as $key => $item ) {
            $ordered_items[ $key ] = $item['x'];
        }

        array_multisort( $ordered_items, SORT_ASC, $items );

        return $items;
    }

     function render_row( $items, $id = '', $device = 'desktop' ){
         $row_html     = '';
         $max_columns  = 12;
         $items = $this->_sort_items_by_position( $items );
         $last_item = false;
         $next_item = false;

         foreach ( $items as $index => $item ) {
             $content = $this->render_items[$item['id']]['render_content'];
             if ( isset( $items[ $index + 1 ] ) ) {
                 $next_item = $items[ $index + 1 ];
             } else {
                 $next_item = false;
             }

             $item_id = $item['id'];
             $x = intval( $item['x'] );
             $width = intval($item['width']);
             if ( ! $next_item ) {
                 if ( $x + $width < $max_columns ) {
                     $width += $max_columns - ( $x + $width );
                 }
             }
             $atts = array();
             $classes = array();
             $classes[] = "customify-col-{$width}_md-{$width}_sm-{$width}";
             if ( $x > 0 ) {
                 if ( ! $last_item ) {
                     $atts[] = 'off-' . $x;
                 } else {
                    $o = intval( $last_item['width'] ) + intval( $last_item['x'] );
                    if ( $x - $o  > 0 ) {
                        $atts[] = 'off-' . ( $x - $o );
                    }
                 }
             }
             $last_item = $item;

             $item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();
             $classes[] = 'builder-item';
             $classes[] = 'builder-item--'.$item_id;

             if( is_customize_preview() ) {
                 $classes[] = ' builder-item-focus';
             }

             $classes = join(' ', $classes ); // customify-grid-middle

             $content = str_replace( '__id__', $id, $content );
             $content = str_replace( '__device__', $device, $content );
             
             echo '<div class="'.esc_attr( $classes ).'" data-section="'.$item_config['section'].'" data-item-id="' . esc_attr($item_id) . '" data-push-left="' . join(' ', $atts) . '">';
                echo $content;
             echo '</div>';
         }
     }

     function render( $row_ids = array( 'top', 'main', 'bottom') ){
         $setting = $this->get_settings();
         $items = $this->render_items( );
         foreach ($row_ids as $row_id ) {
            if ( isset( $this->rows[ $row_id ] ) ) {
                $show_on_devices = $this->rows[$row_id];
                if (!empty($show_on_devices)) {
                    $classes = array();
                    $classes[] = sprintf('%1$s-%2$s', $this->id, $row_id);
                    $desktop_items = $this->get_row_settings($row_id, 'desktop');
                    $mobile_items = $this->get_row_settings($row_id, 'mobile');
                    if ( empty( $desktop_items ) ){
                        $classes[] = 'hide-on-desktop';
                    }
                    if ( empty( $mobile_items ) ){
                        $classes[] = 'hide-on-mobile hide-on-tablet';
                    }



                    ?>
                    <div class="<?php echo esc_attr( join(' ', $classes ) ); ?>" data-row-id="<?php echo esc_attr($row_id); ?>" data-show-on="<?php echo esc_attr(join(" ", $show_on_devices)); ?>">
                        <div class="customify-container">
                            <?php
                            if ($desktop_items) {
                                echo '<div class="hide-on-mobile hide-on-tablet customify-grid customify-grid-middle">';
                                $this->render_row($desktop_items, $row_id, 'desktop');
                                echo '</div>';
                            }

                            if ($mobile_items) {
                                echo '<div class="hide-on-desktop customify-grid customify-grid-middle">';
                                $this->render_row($mobile_items, $row_id, 'mobile');
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
            }

         } // end for each row_ids
     }

     function render_sidebar(){
         $id = 'sidebar';
         $mobile_items = $this->get_row_settings( $id, 'mobile');
         if ($mobile_items) {
             echo '<div id="mobile-header-panel" class="mobile-header-panel">';
                 echo '<div id="mobile-header-panel-inner" class="mobile-header-panel-inner">';
                     echo '<a class="close-panel" href="#">'.__( 'Close', 'customify' ).'</a>';
                     foreach( $mobile_items as $item ) {
                         $item_id = $item['id'];
                         $content = $this->render_items[$item['id']]['render_content'];
                         $item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();

                         $classes = "builder-item-sidebar";
                         if( is_customize_preview() ) {
                             $classes = 'builder-item-focus '.$classes;
                         }

                         $content = str_replace( '__id__', $id, $content );
                         $content = str_replace( '__device__', 'mobile', $content );

                         echo '<div class="'.esc_attr( $classes ).'" data-section="'.$item_config['section'].'" data-item-id="' . esc_attr($item_id) . '">';
                            echo $content;
                         echo '</div>';
                     }
                 echo '</div>';
             echo '</div>';
         }
     }
}





function customify_customize_render_header(){
    echo '<header id="masthead" class="site-header">';
    $b = new Customify_Customizer_Layout_Builder_Frontend();
    if ( is_customize_preview() ) {
        ?>
        <span class="customize-partial-edit-shortcut customize-partial-edit-shortcut-header_panel"><button aria-label="Click to edit this element." title="Click to edit this element." class="customize-partial-edit-shortcut-button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg></button></span>
        <?php
    }
    $list_items = Customify_Customizer_Layout_Builder()->get_header_items();
    $b->set_config_items( $list_items );
    $b->render();
    $b->render_sidebar();


    /*
    $theme_name = wp_get_theme()->get('Name');
    $option_name = $theme_name.'_saved_templates';
    ?>
    <pre class="debug"><?php // print_r( $b->render_items()  ); ?></pre>
    <pre class="debug"><?php print_r( get_theme_mod( 'header_builder_panel' ) ); ?></pre>
    <?php
    */

    echo '</header>';
}
