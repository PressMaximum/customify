<?php

/**
 * Calls the class on the post edit screen.
 */
function comtomify_metabox_init() {
    Customify_MetaBox::get_instance();
}

if ( is_admin() ) {
    add_action( 'load-post.php',     'comtomify_metabox_init' );
    add_action( 'load-post-new.php', 'comtomify_metabox_init' );
}

/**
 * The Class.
 */
class Customify_MetaBox {

    static $_instance = null;

    public $fields = array(
        'sidebar' => '',
        'content_layout' => '',
        'disable_header' => '',
        'disable_page_title' => '',
        'disable_footer_main' => '',
        'disable_footer_bottom' => '',
        'page_header_display' => '',
        'breadcrumb_display' => '',
    );

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            add_action( 'add_meta_boxes', array( self::$_instance, 'add_meta_box' ) );
            add_action( 'save_post',      array( self::$_instance, 'save'         ) );
            add_action( 'admin_enqueue_scripts',  array( self::$_instance, 'scripts' ) );
        }
        return self::$_instance;
    }
    function scripts( $hook ){

        if($hook != 'post.php' && $hook !='post-new.php' ) {
            return;
        }
        $suffix = Customify()->get_asset_suffix();
        wp_enqueue_script( 'customify-metabox',  esc_url( get_template_directory_uri() ).'/assets/js/admin/metabox'.$suffix.'.js',  array( 'jquery' ),  Customify::$version, true );
        wp_enqueue_style( 'customify-metabox',  esc_url( get_template_directory_uri() ). '/assets/css/admin/metabox'.$suffix.'.css', false, Customify::$version );
    }

    function get_support_post_types(){
        $args = array(
            'public' => true,
        );

        $output = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'
        $post_types = get_post_types( $args, $output, $operator );
        return array_values( $post_types );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        $post_types = $this->get_support_post_types();
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'customify_page_settings',
                __( 'Customify Settings', 'customify' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'side',
                'low'
            );
        }
    }

    function get_fields(){
        return apply_filters( 'customify/metabox/fields', $this->fields );
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['customify_page_settings_nonce'] ) ) {
            return $post_id;
        }


        $nonce = sanitize_text_field( wp_unslash( $_POST['customify_page_settings_nonce'] ) );

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'customify_page_settings' ) ) {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == get_post_type( $post_id ) ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $settings = isset( $_POST['customify_page_settings'] ) ? wp_unslash( $_POST['customify_page_settings'] ) : array();
        $settings = wp_parse_args( $settings, $this->get_fields() );

        foreach( $settings as $key => $value ) {
            if ( ! is_array( $value ) ) {
                $value = wp_kses_post( $value );
            } else {
                $value = array_map( 'wp_kses_post', $value );
            }
            // Update the meta field.
            update_post_meta( $post_id, '_customify_'.$key, $value );
        }

    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'customify_page_settings', 'customify_page_settings_nonce' );
        $values = $this->get_fields();
        foreach( $values as $key => $value ) {
            $values[ $key ] = get_post_meta( $post->ID, '_customify_'.$key, true );
        }
        ?>
        <div class="customify_metabox_section">
            <label for="customify_page_layout"><strong><?php _e( 'Sidebar', 'customify' ); ?></strong></label>
            <select id="customify_page_layout" name="customify_page_settings[sidebar]">
                <option value=""><?php _e( 'Inherit from customize settings', 'customify' ); ?></option>
                <?php foreach( customify_get_config_sidebar_layouts() as $k => $label ) { ?>
                <option <?php selected( $values['sidebar'],  $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="customify_metabox_section">
            <label for="customify_content_layout"><strong><?php _e( 'Content Layout', 'customify' ); ?></strong></label>
            <select id="customify_content_layout" name="customify_page_settings[content_layout]">
                <option value=""><?php _e( 'Default', 'customify' ); ?></option>
                <?php foreach( array(
                        'full-width' => __( 'Full Width', 'customify' ),
                        'full-stretched' => __( 'Full Width - Stretched', 'customify' ),
                       ) as $k => $label ) { ?>
                    <option <?php selected( $values['content_layout'],  $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="customify_metabox_section">
            <label><strong><?php _e( 'Disable Elements', 'customify' ); ?></strong></label>

            <div class="checkbox_input">
                <input type="checkbox" name="customify_page_settings[disable_header]" <?php checked( $values['disable_header'], 1 ); ?> value="1"> <?php _e( 'Disable Header', 'customify' ); ?>
            </div>

            <div class="checkbox_input">
                <input type="checkbox" name="customify_page_settings[disable_page_title]" <?php checked( $values['disable_page_title'], 1 ); ?> value="1"> <?php _e( 'Disable Title', 'customify' ); ?>
            </div>

            <div class="checkbox_input">
                <input type="checkbox" name="customify_page_settings[disable_footer_main]" <?php checked( $values['disable_footer_main'], 1 ); ?> value="1"> <?php _e( 'Disable Footer Main', 'customify' ); ?>
            </div>

            <div class="checkbox_input">
                <input type="checkbox" name="customify_page_settings[disable_footer_bottom]" <?php checked( $values['disable_footer_bottom'], 1 ); ?> value="1"> <?php _e( 'Disable Footer Bottom', 'customify' ); ?>
            </div>

        </div>

        <div class="customify_metabox_section">
            <label for="customify_page_header_display"><strong><?php _e( 'Page Header Display', 'customify' ); ?></strong></label>
            <select id="customify_page_header_display" name="customify_page_settings[page_header_display]">
            <?php
            foreach(
                array(
                   'default' => __( 'Inherit from customize settings', 'customify' ),
                   'cover' => __( 'Cover', 'customify' ),
                   'titlebar' => __( 'Titlebar', 'customify' ),
                   'none' => __( 'Hide', 'customify' ),
                ) as $k => $label ) { ?>
                <option <?php selected( $values['page_header_display'],  $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php } ?>
            </select>
        </div>
        <?php

        do_action('customify/metabox/settings', $post );

        if ( Customify_Breadcrumb::get_instance()->support_plugins_active() ) { ?>
        <div class="customify_metabox_section">
            <label for="customify_page_breadcrumb_display"><strong><?php _e('Breadcrumb Display', 'customify'); ?></strong></label>
            <select id="customify_page_breadcrumb_display" name="customify_page_settings[breadcrumb_display]">
                <?php
                foreach (array(
                             'default' => __('Inherit from customize settings', 'customify'),
                             'hide' => __('Hide', 'customify'),
                             'show' => __('Show', 'customify'),
                         ) as $k => $label) { ?>
                    <option <?php selected($values['breadcrumb_display'], $k); ?> value="<?php echo esc_attr($k); ?>"><?php echo esc_html($label); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php
        }

    }
}