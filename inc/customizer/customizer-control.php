<?php

class _Beacon_Customizer_Control extends WP_Customize_Control {
    /**
     * The control type.
     *
     * @access public
     * @var string
     */
    public $type = '_beacon';
    /**
     * Data type
     *
     * @access public
     * @var string
     */
    public $option_type = 'theme_mod';

    public $setting_type = 'group';
    public $fields = array();
    public $choices = array();
    public $default = null;
    public $defaultValue = null;
    public $device = '';
    public $checkbox_label = '';
    public $limit ;

    // For slider
    public $min = 0;
    public $max = 700;

    public $limit_msg = '';

    public $live_title_field; // for repeater

    public $_settings;
    public $_selective_refresh;

    public $device_settings = false;


    /**
     * Provide the parent, comparison operator, and value which affects the field’s visibility
     *
     * @var
     */
    public $required;

    public $field_class = '';

    static $_js_template_added;
    function __construct($manager, $id, $args = array())
    {
        parent::__construct($manager, $id, $args);

        add_action( 'customize_controls_print_footer_scripts', array( $this, 'content_js_template' ) );
    }

    /**
     * Enqueue control related scripts/styles.
     *
     * @access public
     */
    public function enqueue() {
        wp_enqueue_media();
        if( $this->setting_type == 'repeater' ) {
            wp_enqueue_script('jquery-ui-sortable');
        }
       // wp_enqueue_editor();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-slider' );

        if ( _Beacon_Customizer()->has_icon() ) {
            require_once get_template_directory().'/inc/customizer/customizer-icons.php';
        }

        wp_enqueue_style('_beacon-customizer-control', get_template_directory_uri().'/assets/css/admin/customizer/customizer.css');
        wp_enqueue_script( '_beacon-color-picker-alpha',  get_template_directory_uri().'/assets/js/customizer/color-picker-alpha.js', array( 'wp-color-picker' ) );
        wp_enqueue_script( '_beacon-customizer-control',  get_template_directory_uri().'/assets/js/customizer/control.js', array( 'jquery', 'customize-base', 'jquery-ui-core', 'jquery-ui-sortable' ), false, true );
        wp_localize_script( '_beacon-customizer-control', '_Beacon_Control_Args', array(
            'home_url' => home_url(''),
            'ajax' => admin_url( 'admin-ajax.php' ),
            'has_icons' => _Beacon_Customizer()->has_icon(),
            'icons' => _Beacon_Font_Icons()->get_icons(),
            'theme_default' => __( 'Theme Default', '_beacon' ),
        ) );
    }



    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     *
     * @access public
     */
    public function to_json() {
        parent::to_json();
        // Add something here
        $value = $this->value();
        if ( $this->setting_type == 'group' ) {
            if ( ! is_array( $value ) ) {
                $value = array();
            }
            foreach ( $this->fields as $k => $f ) {
                if ( isset( $value[ $f['name'] ] ) ) {
                    $this->fields[ $k ]['value'] = $value[ $f['name'] ];
                }
            }

            if ( ! is_array( $this->default ) ) {
                $this->default = array();
            }

        } elseif (  $this->setting_type == 'repeater' ) {
            if ( ! is_array( $value ) ) {
                $value = array();
            }
            if ( ! is_array( $this->default ) ) {
                $this->default = array();
            }
        }

        // Devices switcher settings = true;
        $this->json['device_settings'] = $this->device_settings;
        if ( ! $this->device_settings && $this->setting_type != 'js_raw' ) {
            // Fallback value when device_settings from tru to false
            if ( is_array( $value ) && isset( $value['desktop'] ) ) {
                $value = $value['desktop'];
            }
        }

        $this->json['value']        = $value;
        $this->json['default']      = $this->defaultValue;
        $this->json['fields']       = $this->fields;
        $this->json['setting_type'] = $this->setting_type;
        $this->json['required']     = $this->required;

        $this->json['min'] = $this->min;
        $this->json['max'] = $this->max;

        if ( $this->setting_type == 'repeater' ) {
            $this->json['l10n'] = array(
                'untitled' => __( 'Untitled', '_beacon' )
            );
            $this->json['live_title_field'] = $this->live_title_field;
            $this->json['limit'] = $this->limit;
            $this->json['limit_msg'] = $this->limit_msg;
        }

        if ( $this->setting_type == 'select' || $this->setting_type == 'radio' ) {
            $this->json['choices'] = $this->choices;
        }
        if ( $this->setting_type == 'checkbox' ) {
            $this->json['checkbox_label'] = $this->checkbox_label;
        }
    }


    /**
     * Renders the control wrapper and calls $this->render_content() for the internals.
     *
     * @since 3.4.0
     */
    protected function render() {
        $id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
        $class = 'customize-control customize-control-' . $this->type.'-'.$this->setting_type;
        if ( $this->field_class ) {
            $class = sanitize_text_field( $this->field_class ).' '.$class;
        }

        ?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?><?php echo ( $this->device ) ? '  _beacon--device-show _beacon--device-'.esc_attr( $this->device ) : ''; ?>">
        <?php $this->render_content(); ?>
        </li><?php
    }


    /**
     * Render the control's content.
     * Allows the content to be overriden without having to rewrite the wrapper in $this->render().
     *
     * @access protected
     */
    protected function render_content() {
        if ( $this->setting_type == 'js_raw' ) {
            return '';
        }

        ?>
        <div class="_beacon--settings-wrapper">
            <div class="_beacon-control-field-header _beacon-field-heading">
                <label>
                    <?php if (!empty($this->label)) : ?>
                        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <?php endif; ?>
                </label>
            </div>
            <?php if (!empty($this->description)) : ?>
                <span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
            <?php endif; ?>
            <div class="_beacon--settings-fields<?php echo ( $this->setting_type == 'repeater' ) ? ' _beacon--repeater-items' : ''; ?>"></div>
            <?php if ( $this->setting_type == 'repeater' ) { ?>
                <div class="_beacon--repeater-actions">
                    <a href="#" class="_beacon--repeater-reorder" data-text="<?php _e( 'Reorder', '_beacon' ); ?>" data-done="<?php _e( 'Done', '_beacon' ); ?>"><?php _e( 'Reorder', '_beacon' ); ?></a>
                    <button type="button" class="button _beacon--repeater-add-new"><?php _e( 'Add an item', '_beacon' ); ?></button>
                </div>

            <?php } ?>
        </div>
        <?php

    }

    function content_js_template() {
        if ( is_null( self::$_js_template_added ) ) {
            self::$_js_template_added  = true;
        } else {
            return ;
        }

        $fields = array(
            'select',
            'font',
            'font_style',
            'checkbox',
            'css_ruler',
            'icon',
            'slider',
            'color',
            'textarea',
            'radio',
            'image' => 'media',
            'media' => 'media',
            'video' => 'media',
            'text'
        );
        foreach ( $fields as $key => $field ) {
            $id = $field;
            $cb = $field;
            if ( ! is_numeric( $key ) ){
                $id = $key;
            }
            ?>
            <script type="text/html" id="tmpl-field-<?php echo esc_attr( $this->type ).esc_attr( '-'.$id ); ?>">
                <?php
                if ( method_exists( $this, 'field_'.$cb ) ) {
                    call_user_func_array( array( $this, 'field_'.$cb ), array() );
                }
                ?>
             </script>
            <?php
        }

        ?>
        <script type="text/html" id="tmpl-customize-control-<?php echo esc_attr( $this->type ); ?>-repeater">
            <div class="_beacon--repeater-item">
                <div class="_beacon--repeater-item-heading">
                    <span class="_beacon--repeater-live-title"></span>
                    <div class="_beacon-nav-reorder">
                        <span class="_beacon--down" tabindex="-1"><span class="screen-reader-text"><?php _e( 'Move Down', '_beacon' ) ?></span></span>
                        <span class="_beacon--up" tabindex="0"><span class="screen-reader-text"><?php _e( 'Move Up', '_beacon' ) ?></span></span>
                    </div>
                    <a href="#" class="_beacon--repeater-item-toggle"><span class="screen-reader-text"><?php _e( 'Close', '_beacon' ) ?></span></a>
                </div>
                <div class="_beacon--repeater-item-settings">
                    <div class="_beacon--repeater-item-inside">
                        <div class="_beacon--repeater-item-inner">{{{ data }}}</div>
                        <a href="#" class="_beacon--remove"><?php _e( 'Remove', '_beacon' ); ?></a>
                    </div>

                </div>
            </div>
        </script>
        <div id="_beacon--sidebar-icons">
            <div class="_beacon--sidebar-header">
                <a class="customize-controls-close" href="#">
                    <span class="screen-reader-text"><?php _e( 'Cancel', '_beacon' );  ?></span>
                </a>
                <div class="_beacon--icon-type-inner">

                    <select id="_beacon--sidebar-icon-type">
                        <option value="all"><?php _e( 'All Icon Types', '_beacon' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="_beacon--sidebar-search">
               <input type="text" id="_beacon--icon-search" placeholder="<?php esc_attr_e( 'Type icon name', '_beacon' ) ?>">
            </div>
            <div id="_beacon--icon-browser">
            </div>
        </div>
        <?php
    }

    function before_field(){
        ?>
        <#
        var required = '';
        if ( ! _.isUndefined( field.required ) ) {
            required = JSON.stringify( field.required  );
        }
        #>
        <div class="_beacon--field _beacon--field-{{ field.type }} _beacon--field-name-{{ field.original_name }}" data-required="{{ required }}" data-field-name="{{ field.name }}">
        <?php
    }

     function after_field(){
        ?>
        </div>
        <?php
    }

    function field_header(){
        ?>
            <div class="_beacon-field-header">
                <# if ( field.label ) { #>
                    <div class="_beacon-field-heading">
                        <label class="customize-control-title">{{{ field.label }}}</label>
                    </div>
                <# } #>
                <# if ( field.description ) { #>
                    <p class="description">{{{ field.description }}}</p>
                <# } #>
            </div>
        <?php
    }

    function field_text(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <input type="text" class="_beacon-input _beacon-only" data-name="{{ field.name }}" value="{{ field.value }}">
        </div>
        <?php
        $this->after_field();
    }
    function field_icon(){
        $this->before_field();
        ?>
        <#
        if ( ! _.isObject( field.value ) ) {
            field.value = { };
        }
        #>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <div class="_beacon--icon-picker">
                <div class="_beacon--icon-preview">
                    <input type="hidden" class="_beacon-input _beacon--input-icon-type" data-name="{{ field.name }}-type" value="{{ field.value.type }}">
                    <div class="_beacon--icon-preview-icon _beacon--pick-icon">
                        <# if ( field.value.icon ) {  #>
                            <i class="{{ field.value.icon }}"></i>
                        <# }  #>
                    </div>
                </div>
                <input type="text" readonly class="_beacon-input _beacon--pick-icon _beacon--input-icon-name" placeholder="<?php esc_attr_e( 'Pick an icon', '_beacon' ); ?>" data-name="{{ field.name }}" value="{{ field.value.icon }}">
                <span class="_beacon--icon-remove" title="<?php esc_attr_e( 'Remove', '_beacon' ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                    <span class="screen-reader-text">
                    <?php _e( 'Remove', '_beacon' ) ?></span>
                </span>
            </div>
        </div>
        <?php
        $this->after_field();
    }


    function field_css_ruler(){
        $this->before_field();
        ?>
        <#
        if ( ! _.isObject( field.value ) ) {
            field.value = { link: 1 };
        }
        var uniqueID = field.name + ( new Date().getTime() );
        #>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <div class="_beacon--css-unit" title="<?php esc_attr_e( 'Chose an unit', '_beacon' ); ?>">
                <label class="<# if ( field.value.unit == 'px' || ! field.value.unit ){ #> _beacon--label-active <# } #>">
                    <?php _e( 'px', '_beacon' ); ?>
                    <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'px' || ! field.value.unit ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="px">
                </label>
                <label class="<# if ( field.value.unit == 'rem' ){ #> _beacon--label-active <# } #>">
                    <?php _e( 'rem', '_beacon' ); ?>
                    <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'rem' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="rem">
                </label>
                <label class="<# if ( field.value.unit == 'em' ){ #> _beacon--label-active <# } #>">
                    <?php _e( 'em', '_beacon' ); ?>
                    <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'em' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="em">
                </label>
                <label class="<# if ( field.value.unit == '%' ){ #> _beacon--label-active <# } #>">
                    <?php _e( '%', '_beacon' ); ?>
                    <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == '%' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="%">
                </label>
            </div>
            <div class="_beacon--css-ruler">
                <span>
                    <input type="number" class="_beacon-input _beacon-input-css change-by-js" data-name="{{ field.name }}-top" value="{{ field.value.top }}">
                    <span class="_beacon--small-label"><?php _e( 'Top', '_beacon' ); ?></span>
                </span>
                <span>
                    <input type="number" class="_beacon-input _beacon-input-css change-by-js" data-name="{{ field.name }}-right" value="{{ field.value.right }}">
                    <span class="_beacon--small-label"><?php _e( 'Right', '_beacon' ); ?></span>
                </span>
                <span>
                    <input type="number" class="_beacon-input _beacon-input-css change-by-js" data-name="{{ field.name }}-bottom" value="{{ field.value.bottom }}">
                    <span class="_beacon--small-label"><?php _e( 'Bottom', '_beacon' ); ?></span>
                </span>
                <span>
                    <input type="number" class="_beacon-input _beacon-input-css change-by-js" data-name="{{ field.name }}-left" value="{{ field.value.left }}">
                    <span class="_beacon--small-label"><?php _e( 'Left', '_beacon' ); ?></span>
                </span>
                <label title="<?php esc_attr_e( 'Toggle values together', '_beacon' ); ?>" class="_beacon--css-ruler-link <# if ( field.value.link == 1 ){ #> _beacon--label-active <# } #>">
                    <input type="checkbox" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.link == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-link" value="1">
                </label>
            </div>
        </div>
        <?php
        $this->after_field();
    }

    function field_color(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <div class="_beacon-input-color" data-default="{{ field.default }}">
                <input type="hidden" class="_beacon-input" data-name="{{ field.name }}" value="{{ field.value }}">
                <input type="text" class="_beacon--color-panel" data-alpha="true" value="{{ field.value }}">
            </div>
        </div>
        <?php
        $this->after_field();
    }

    function field_slider(){
        $this->before_field();
        ?>
        <#
        if ( ! _.isObject( field.value ) ) {
            field.value = { unit: 'px' };
        }
        var uniqueID = field.name + ( new Date().getTime() );
        #>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <div class="_beacon-input-slider-wrapper">
                <div class="_beacon--css-unit">
                    <label class="<# if ( field.value.unit == 'px' || ! field.value.unit ){ #> _beacon--label-active <# } #>">
                        <?php _e( 'px', '_beacon' ); ?>
                        <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'px' || ! field.value.unit ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="px">
                    </label>
                    <label class="<# if ( field.value.unit == 'rem' ){ #> _beacon--label-active <# } #>">
                        <?php _e( 'rem', '_beacon' ); ?>
                        <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'rem' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="rem">
                    </label>
                    <label class="<# if ( field.value.unit == 'em' ){ #> _beacon--label-active <# } #>">
                        <?php _e( 'em', '_beacon' ); ?>
                        <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == 'em' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="em">
                    </label>
                    <label class="<# if ( field.value.unit == '%' ){ #> _beacon--label-active <# } #>">
                        <?php _e( '%', '_beacon' ); ?>
                        <input type="radio" class="_beacon-input _beacon--label-parent change-by-js" <# if ( field.value.unit == '%' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="%">
                    </label>
                </div>
                <div data-min="{{ field.min }}" data-max="{{ field.max }}" class="_beacon-input-slider"></div>
                <input type="number" class="_beacon--slider-input _beacon-input" data-name="{{ field.name }}-value" value="{{ field.value.value }}" size="4">
            </div>
        </div>
        <?php
        $this->after_field();
    }

    function field_radio(){
        $this->before_field();
        ?>
        <#
        var uniqueID = field.name + ( new Date().getTime() );

        #>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <div class="_beacon-radio-list">
                <# _.each( field.choices, function( label, key ){  #>
                    <p>
                    <label><input type="radio" data-name="{{ field.name }}" value="{{ key }}" <# if ( field.value == key ){ #> checked="checked" <# } #> name="{{ uniqueID }}"> {{ label }}</label>
                    </p>
                <# } ); #>
            </div>
        </div>
        <?php
        $this->after_field();
    }

    function field_checkbox(){
        $this->before_field();
        ?>
        <label>
            <input type="checkbox" class="_beacon-input" <# if ( field.value == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}" value="1"> {{{ field.label }}}
        </label>
        <# if ( field.description ) { #>
            <p class="description">{{{ field.description }}}</p>
        <# } #>
        <?php
        $this->after_field();
    }

    function field_textarea(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <textarea rows="10" class="_beacon-input" data-name="{{ field.name }}">{{ field.value }}</textarea>
        </div>
        <?php
        $this->after_field();
    }

    function field_select(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <select class="_beacon-input" data-name="{{ field.name }}">
                <# _.each( field.choices, function( label, key ){  #>
                <option <# if ( field.value == key ){ #> selected="selected" <# } #> value="{{ key }}">{{ label }}</option>
                <# } ); #>
            </select>
        </div>
        <?php
        $this->after_field();
    }

    function field_font(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner">
            <input type="hidden" class="_beacon--font-type" data-name="{{ field.name }}-type" >
            <div class="_beacon--font-families-wrapper">
                <select class="_beacon--font-families" data-value="{{ JSON.stringify( field.value ) }}" data-name="{{ field.name }}-font"></select>
            </div>
            <div class="_beacon--font-variants-wrapper">
                <label><?php _e( 'Variants' ) ?></label>
                <select class="_beacon--font-variants" data-name="{{ field.name }}-variant"></select>
            </div>
            <div class="_beacon--font-subsets-wrapper">
                <label><?php _e( 'Languages' ) ?></label>
                <div data-name="{{ field.name }}-subsets" class="list-subsets">

                </div>
            </div>
        </div>
        <?php
        $this->after_field();
    }
    function field_font_style(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <#
        if ( ! _.isObject( field.value ) ) {
            field.value = { };
        }
        #>
        <div class="_beacon-field-settings-inner _beacon--font-style">
            <label title="<?php esc_attr_e( 'Bold', '_beacon' ); ?>" class="button <# if ( field.value.b == 1 ){ #> _beacon--checked <# } #>"><input type="checkbox" <# if ( field.value.b == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-b" value="1"><span class="dashicons dashicons-editor-bold"></span></label>
            <label title="<?php esc_attr_e( 'Italic', '_beacon' ); ?>" class="button <# if ( field.value.i == 1 ){ #> _beacon--checked <# } #>"><input type="checkbox" <# if ( field.value.i == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-i" value="1"><span class="dashicons dashicons-editor-italic"></span></label>
            <label title="<?php esc_attr_e( 'Underline', '_beacon' ); ?>" class="button <# if ( field.value.u == 1 ){ #> _beacon--checked <# } #>"><input type="checkbox" <# if ( field.value.u == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-u" value="1"><span class="dashicons dashicons-editor-underline"></span></label>
            <label title="<?php esc_attr_e( 'Strikethrough', '_beacon' ); ?>" class="button <# if ( field.value.s == 1 ){ #> _beacon--checked <# } #>"><input type="checkbox" <# if ( field.value.s == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-s" value="1"><span class="dashicons dashicons-editor-strikethrough"></span></label>
            <label title="<?php esc_attr_e( 'Uppercase', '_beacon' ); ?>" class="button <# if ( field.value.t == 1 ){ #> _beacon--checked <# } #>"><input type="checkbox" <# if ( field.value.t == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-t" value="1"><span class="dashicons dashicons-editor-textcolor"></span></label>
        </div>
        <?php
        $this->after_field();
    }


    function field_media(){
        $this->before_field();
        ?>
        <#
        if ( ! _.isObject(field.value) ) {
            field.value = {};
        }
        var url = field.value.url;
        #>
        <?php echo $this->field_header(); ?>
        <div class="_beacon-field-settings-inner _beacon-media-type-{{ field.type }}">
            <div class="_beacon--media">
                <input type="hidden" class="attachment-id" value="{{ field.value.id }}" data-name="{{ field.name }}">
                <input type="hidden" class="attachment-url"  value="{{ field.value.url }}" data-name="{{ field.name }}-url">
                <input type="hidden" class="attachment-mime"  value="{{ field.value.mime }}" data-name="{{ field.name }}-mime">
                <div class="_beacon-image-preview <# if ( url ) { #> _beacon--has-file <# } #>" data-no-file-text="<?php esc_attr_e( "No file selected", '_beacon' ); ?>">
                    <#

                    if ( url ) {
                        if ( url.indexOf('http://') > -1 || url.indexOf('https://') ){

                        } else {
                            url = _Beacon_Control_Args.home_url + url;
                        }

                        if ( ! field.value.mime || field.value.mime.indexOf('image/') > -1 ) {
                            #>
                            <img src="{{ url }}" alt="">
                        <# } else if ( field.value.mime.indexOf('video/' ) > -1 ) { #>
                            <video width="100%" height="" controls><source src="{{ url }}" type="{{ field.value.mime }}">Your browser does not support the video tag.</video>
                        <# } else {
                        var basename = url.replace(/^.*[\\\/]/, '');
                        #>
                            <a href="{{ url }}" class="attachment-file" target="_blank">{{ basename }}</a>
                        <# }
                    }
                    #>
                </div>
                <button type="button" class="button _beacon--add <# if ( url ) { #> _beacon--hide <# } #>"><?php _e( 'Add', '_beacon' ); ?></button>
                <button type="button" class="button _beacon--change <# if ( ! url ) { #> _beacon--hide <# } #>"><?php _e( 'Change', '_beacon' ); ?></button>
                <button type="button" class="button _beacon--remove <# if ( ! url ) { #> _beacon--hide <# } #>"><?php _e( 'Remove', '_beacon' ); ?></button>
            </div>
        </div>

        <?php
        $this->after_field();
    }







}