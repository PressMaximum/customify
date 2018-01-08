<?php

class Customify_Customizer_Control extends WP_Customize_Control {
    /**
     * The control type.
     *
     * @access public
     * @var string
     */
    public $type = 'customify';
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
    public $devices = null;
    public $checkbox_label = '';
    public $limit ;

    // For slider
    public $min = 0;
    public $max = 700;
    public $step = 1;

    // For CSS Ruler
    public $fields_disabled = array();


    public $limit_msg = '';
    public $live_title_field; // for repeater
    public $addable = null; // for repeater
    public $title_only = null; // for repeater
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
    static $_icon_loaded;
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

        if ( Customify_Customizer()->has_icon() ) {
            require_once get_template_directory().'/inc/customizer/customizer-icons.php';
        }

        wp_enqueue_style('customify-customizer-control', get_template_directory_uri().'/assets/css/admin/customizer/customizer.css');
        wp_enqueue_script( 'customify-color-picker-alpha',  get_template_directory_uri().'/assets/js/customizer/color-picker-alpha.js', array( 'wp-color-picker' ) );
        wp_enqueue_script( 'customify-customizer-control',  get_template_directory_uri().'/assets/js/customizer/control.js', array( 'jquery', 'customize-base', 'jquery-ui-core', 'jquery-ui-sortable' ), false, true );
        if ( is_null( self::$_icon_loaded ) ) {
            wp_localize_script('customify-customizer-control', 'Customify_Control_Args', array(
                'home_url' => home_url(''),
                'ajax' => admin_url('admin-ajax.php'),
                'has_icons' => Customify_Customizer()->has_icon(),
                'icons' => Customify_Font_Icons()->get_icons(),
                'theme_default' => __('Theme Default', 'customify'),
                'reset' => __('Reset this section settings', 'customify'),
                'confirm_reset' => __('Do you want to reset this section settings?', 'customify'),
            ));
            self::$_icon_loaded = true;
        }
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

        if ( 'slider' == $this->setting_type ) {
           if ( ! $value || empty( $value ) ) {
               $value = $this->defaultValue;
           }
        }

        $this->json['value']        = $value;
        $this->json['default']      = $this->defaultValue;
        $this->json['fields']       = $this->fields;
        $this->json['setting_type'] = $this->setting_type;
        $this->json['required']     = $this->required;
        $this->json['devices']      = $this->devices;

        $this->json['min'] = $this->min;
        $this->json['max'] = $this->max;
        $this->json['step'] = $this->step;
        if ( 'css_ruler' == $this->setting_type ) {
            // $disabled
            $this->json['fields_disabled'] = $this->fields_disabled;
        }

        if ( $this->setting_type == 'repeater' ) {
            $this->json['l10n'] = array(
                'untitled' => __( 'Untitled', 'customify' )
            );
            $this->json['live_title_field'] = $this->live_title_field;
            $this->json['limit'] = $this->limit;
            $this->json['limit_msg'] = $this->limit_msg;
            $this->json['title_only'] = $this->title_only;
            if ( $this->addable === false ) {
                $this->json['addable'] = false;
                if( empty( $this->json['value'] ) ) {
                    $this->json['value'] = $this->defaultValue;
                }
            } else {
                $this->json['addable'] = true;
            }

            // Just make live title file translate able.
            if ( $this->title_only && $this->live_title_field ) {
                $new_array = array();
                foreach ( ( array ) $this->defaultValue as $f ) {
                    if ( isset( $f['_key'] ) ) {
                        if ( isset( $f[ $this->live_title_field ] ) ) {
                            $new_array[$f['_key']] = $f;
                        }
                    }
                }
                if ( ! empty( $new_array ) ) {
                    $new_values = array();
                    foreach( ( array ) $this->json['value'] as $index => $f ) {
                        if ( isset( $f['_key'] ) && $new_array[ $f['_key'] ] ) {
                            $f[$this->live_title_field] = $new_array[ $f['_key'] ][$this->live_title_field];
                            $new_values[$f['_key']] = $f;
                        }
                    }

                    $new_values = array_merge( $new_array, $new_values );
                    if ( ! empty( $new_values ) ) {
                        $this->json['value'] = array_values( $new_values );
                    }
                }
            }

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

        ?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?><?php echo ( $this->device ) ? '  customify--device-show customify--device-'.esc_attr( $this->device ) : ''; ?>">
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

        if ( $this->setting_type == 'heading' ) {
            ?>
            <div class="customify-control--heading">
                <label>
                    <?php if (!empty($this->label)) : ?>
                        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <?php endif; ?>
                </label>
            </div>
            <?php if (!empty($this->description)) : ?>
                <span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
            <?php endif; ?>
            <?php
            return '';
        }

        ?>
        <div class="customify--settings-wrapper">
            <div class="customify-control-field-header customify-field-heading">
                <label>
                    <?php if (!empty($this->label)) : ?>
                        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <?php endif; ?>
                </label>
            </div>
            <?php
            if ( $this->setting_type == 'custom_html' ) {
                ?>
                <div class="custom_html"><?php echo balanceTags( $this->description ) ?></div>
                <?php
            } else {
            ?>
                <?php if (!empty($this->description)) : ?>
                    <span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
                <?php endif; ?>
            <?php } ?>

            <?php if ( $this->setting_type != 'custom_html' ) { ?>
                <div class="customify--settings-fields<?php echo ( $this->setting_type == 'repeater' ) ? ' customify--repeater-items' : ''; ?>"></div>
                <?php if ( $this->setting_type == 'repeater' ) { ?>
                <div class="customify--repeater-actions">
                    <a href="#" class="customify--repeater-reorder" data-text="<?php _e( 'Reorder', 'customify' ); ?>" data-done="<?php _e( 'Done', 'customify' ); ?>"><?php _e( 'Reorder', 'customify' ); ?></a>
                    <?php if ( $this->addable !== false ) { ?>
                    <button type="button" class="button customify--repeater-add-new"><?php _e( 'Add an item', 'customify' ); ?></button>
                    <?php } ?>
                </div>
                <?php } ?>
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
            'text_align',
            'text_align_no_justify',
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
            'text',
            'hidden',
            'heading'
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
            <div class="customify--repeater-item">
                <div class="customify--repeater-item-heading">
                    <label class="customify--repeater-visible" title="<?php esc_attr_e( 'Toggle item visible', 'customify' ); ?>"><input type="checkbox" class="r-visible-input"><span class="r-visible-icon"></span><span class="screen-reader-text"><?php _e( 'Show', 'customify' ) ?></label>
                    <span class="customify--repeater-live-title"></span>
                    <div class="customify-nav-reorder">
                        <span class="customify--down" tabindex="-1"><span class="screen-reader-text"><?php _e( 'Move Down', 'customify' ) ?></span></span>
                        <span class="customify--up" tabindex="0"><span class="screen-reader-text"><?php _e( 'Move Up', 'customify' ) ?></span></span>
                    </div>
                    <a href="#" class="customify--repeater-item-toggle"><span class="screen-reader-text"><?php _e( 'Close', 'customify' ) ?></span></a>
                </div>
                <div class="customify--repeater-item-settings">
                    <div class="customify--repeater-item-inside">
                        <div class="customify--repeater-item-inner"></div>
                        <# if ( data.addable ){  #>
                        <a href="#" class="customify--remove"><?php _e( 'Remove', 'customify' ); ?></a>
                        <# } #>
                    </div>
                </div>
            </div>
        </script>
        <div id="customify--sidebar-icons">
            <div class="customify--sidebar-header">
                <a class="customize-controls-icon-close" href="#">
                    <span class="screen-reader-text"><?php _e( 'Cancel', 'customify' );  ?></span>
                </a>
                <div class="customify--icon-type-inner">

                    <select id="customify--sidebar-icon-type">
                        <option value="all"><?php _e( 'All Icon Types', 'customify' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="customify--sidebar-search">
               <input type="text" id="customify--icon-search" placeholder="<?php esc_attr_e( 'Type icon name', 'customify' ) ?>">
            </div>
            <div id="customify--icon-browser">
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
        <div class="customify--field customify--field-{{ field.type }} customify--field-name-{{ field.original_name }}" data-required="{{ required }}" data-field-name="{{ field.name }}">
        <?php
    }

     function after_field(){
        ?>
        </div>
        <?php
    }

    function field_header(){
        ?>
            <div class="customify-field-header">
                <# if ( field.label ) { #>
                    <div class="customify-field-heading">
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
        <div class="customify-field-settings-inner">
            <input type="text" class="customify-input customify-only" data-name="{{ field.name }}" value="{{ field.value }}">
        </div>
        <?php
        $this->after_field();
    }
    function field_heading(){
        $this->before_field();
        ?>
        <h3 class="customify-field--heading">{{ field.label }}</h3>
        <?php
        $this->after_field();
    }
    function field_hidden(){
        $this->before_field();
        ?>
        <input type="hidden" class="customify-input customify-only" data-name="{{ field.name }}" value="{{ field.value }}">
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
        <div class="customify-field-settings-inner">
            <div class="customify--icon-picker">
                <div class="customify--icon-preview">
                    <input type="hidden" class="customify-input customify--input-icon-type" data-name="{{ field.name }}-type" value="{{ field.value.type }}">
                    <div class="customify--icon-preview-icon customify--pick-icon">
                        <# if ( field.value.icon ) {  #>
                            <i class="{{ field.value.icon }}"></i>
                        <# }  #>
                    </div>
                </div>
                <input type="text" readonly class="customify-input customify--pick-icon customify--input-icon-name" placeholder="<?php esc_attr_e( 'Pick an icon', 'customify' ); ?>" data-name="{{ field.name }}" value="{{ field.value.icon }}">
                <span class="customify--icon-remove" title="<?php esc_attr_e( 'Remove', 'customify' ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                    <span class="screen-reader-text">
                    <?php _e( 'Remove', 'customify' ) ?></span>
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

        var fields_disabled;
        if ( ! _.isObject( field.fields_disabled ) ) {
            fields_disabled = {};
        } else {
            fields_disabled = _.clone( field.fields_disabled );
        }

        var defaultpl = <?php echo json_encode( __( 'Auto', 'customify' ) ); ?>;

        _.each( [ 'top', 'right', 'bottom', 'left' ], function( key ){
            if ( ! _.isUndefined( fields_disabled[ key ] ) ) {
                if ( ! fields_disabled[ key ] ) {
                    fields_disabled[ key ] = defaultpl;
                }
            } else {
                fields_disabled[ key ] = false;
            }
        } );
                
        var uniqueID = field.name + ( new Date().getTime() );
        #>
        <?php echo $this->field_header(); ?>
        <div class="customify-field-settings-inner">
            <div class="customify--css-unit" title="<?php esc_attr_e( 'Chose an unit', 'customify' ); ?>">
                <label class="<# if ( field.value.unit == 'px' || ! field.value.unit ){ #> customify--label-active <# } #>">
                    <?php _e( 'px', 'customify' ); ?>
                    <input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == 'px' || ! field.value.unit ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="px">
                </label>
                <label class="<# if ( field.value.unit == 'rem' ){ #> customify--label-active <# } #>">
                    <?php _e( 'rem', 'customify' ); ?>
                    <input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == 'rem' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="rem">
                </label>
                <label class="<# if ( field.value.unit == 'em' ){ #> customify--label-active <# } #>">
                    <?php _e( 'em', 'customify' ); ?>
                    <input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == 'em' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="em">
                </label>
                <label class="<# if ( field.value.unit == '%' ){ #> customify--label-active <# } #>">
                    <?php _e( '%', 'customify' ); ?>
                    <input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == '%' ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="%">
                </label>
            </div>
            <div class="customify--css-ruler">
                <span>
                    <input type="number" class="customify-input customify-input-css change-by-js" <# if ( fields_disabled['top'] ) {  #> disabled="disabled" placeholder="{{ fields_disabled['top'] }}" <# } #> data-name="{{ field.name }}-top" value="{{ field.value.top }}">
                    <span class="customify--small-label"><?php _e( 'Top', 'customify' ); ?></span>
                </span>
                <span>
                    <input type="number" class="customify-input customify-input-css change-by-js" <# if ( fields_disabled['right'] ) {  #> disabled="disabled" placeholder="{{ fields_disabled['right'] }}" <# } #> data-name="{{ field.name }}-right" value="{{ field.value.right }}">
                    <span class="customify--small-label"><?php _e( 'Right', 'customify' ); ?></span>
                </span>
                <span>
                    <input type="number" class="customify-input customify-input-css change-by-js" <# if ( fields_disabled['bottom'] ) {  #> disabled="disabled" placeholder="{{ fields_disabled['bottom'] }}" <# } #> data-name="{{ field.name }}-bottom" value="{{ field.value.bottom }}">
                    <span class="customify--small-label"><?php _e( 'Bottom', 'customify' ); ?></span>
                </span>
                <span>
                    <input type="number" class="customify-input customify-input-css change-by-js" <# if ( fields_disabled['left'] ) {  #> disabled="disabled" placeholder="{{ fields_disabled['left'] }}" <# } #> data-name="{{ field.name }}-left" value="{{ field.value.left }}">
                    <span class="customify--small-label"><?php _e( 'Left', 'customify' ); ?></span>
                </span>
                <label title="<?php esc_attr_e( 'Toggle values together', 'customify' ); ?>" class="customify--css-ruler-link <# if ( field.value.link == 1 ){ #> customify--label-active <# } #>">
                    <input type="checkbox" class="customify-input customify--label-parent change-by-js" <# if ( field.value.link == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-link" value="1">
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
        <div class="customify-field-settings-inner">
            <div class="customify-input-color" data-default="{{ field.default }}">
                <input type="hidden" class="customify-input" data-name="{{ field.name }}" value="{{ field.value }}">
                <input type="text" class="customify--color-panel" data-alpha="true" value="{{ field.value }}">
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

        if ( ! field.device_settings ) {
            if ( ! _.isObject( field.default  ) ) {
                field.default = {
                    unit: 'px',
                    value: field.default
                }
            }
            if ( _.isUndefined( field.value.value ) || ! field.value.value ) {
                field.value.value = field.default.value;
            }

        } else {
            _.each( field.default, function( value, device ){
                if ( ! _.isObject( value  ) ) {
                    value = {
                        unit: 'px',
                        value: value
                    }
                }
                field.default[device] = value;
            } );

            try {
                if ( ! _.isUndefined( field.default[field._current_device] ) ) {
                    if ( field._current_device ) {
                       field.default = field.default[field._current_device];
                    }
                }
            } catch ( e ) {

            }
        }


         #>
        <?php echo $this->field_header(); ?>
        <div class="customify-field-settings-inner">
            <div class="customify-input-slider-wrapper">
                <div class="customify--css-unit">
                    <label class="<# if ( field.value.unit == 'px' || ! field.value.unit ){ #> customify--label-active <# } #>">
                        <?php _e( 'px', 'customify' ); ?>
                        <input type="radio" class="customify-input customify--label-parent change-by-js" <# if ( field.value.unit == 'px' || ! field.value.unit ){ #> checked="checked" <# } #> data-name="{{ field.name }}-unit" name="r{{ uniqueID }}" value="px">
                    </label>
                    <a href="#" class="reset" title="<?php esc_attr_e( 'Reset', 'customify' ); ?>"></a>
                </div>
                <div data-min="{{ field.min }}" data-default="{{ JSON.stringify( field.default ) }}" data-step="{{ field.step }}" data-max="{{ field.max }}" class="customify-input-slider"></div>
                <input type="number" min="{{ field.min }}" step="{{ field.step }}" max="{{ field.max }}" class="customify--slider-input customify-input" data-name="{{ field.name }}-value" value="{{ field.value.value }}" size="4">
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
        <div class="customify-field-settings-inner">
            <div class="customify-radio-list">
                <# _.each( field.choices, function( label, key ){
                    var l = '';
                    if ( ! _.isObject( label ) ) {
                        l = label;
                    } else {
                        if ( label.img ) {
                            l = '<img src="'+label.img+'" alt="">';
                        }
                        if ( label.label ) {
                            l += '<span>'+label.label+'</span>';
                        }
                    }
                    #>
                    <p>
                        <label><input type="radio" data-name="{{ field.name }}" value="{{ key }}" <# if ( field.value == key ){ #> checked="checked" <# } #> name="{{ uniqueID }}">
                            <span class="label">{{{ l }}}</span>
                        </label>
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
        <?php echo $this->field_header(); ?>

        <div class="customify-field-settings-inner">
            <label>
            <input type="checkbox" class="customify-input" <# if ( field.value == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}" value="1">
                {{{ field.checkbox_label }}}
            </label>
        </div>

        <?php
        $this->after_field();
    }

    function field_textarea(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="customify-field-settings-inner">
            <textarea rows="10" class="customify-input" data-name="{{ field.name }}">{{ field.value }}</textarea>
        </div>
        <?php
        $this->after_field();
    }

    function field_select(){
        $this->before_field();
        ?>
        <?php echo $this->field_header(); ?>
        <div class="customify-field-settings-inner">
            <select class="customify-input" data-name="{{ field.name }}">
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
        <div class="customify-field-settings-inner">
            <input type="hidden" class="customify--font-type" data-name="{{ field.name }}-type" >
            <div class="customify--font-families-wrapper">
                <select class="customify--font-families" data-value="{{ JSON.stringify( field.value ) }}" data-name="{{ field.name }}-font"></select>
            </div>
            <div class="customify--font-variants-wrapper">
                <label><?php _e( 'Variants', 'customify' ) ?></label>
                <select class="customify--font-variants" data-name="{{ field.name }}-variant"></select>
            </div>
            <div class="customify--font-subsets-wrapper">
                <label><?php _e( 'Languages', 'customify' ) ?></label>
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
        <div class="customify-field-settings-inner customify--font-style">
            <label title="<?php esc_attr_e( 'Bold', 'customify' ); ?>" class="button <# if ( field.value.b == 1 ){ #> customify--checked <# } #>"><input type="checkbox" <# if ( field.value.b == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-b" value="1"><span class="dashicons dashicons-editor-bold"></span></label>
            <label title="<?php esc_attr_e( 'Italic', 'customify' ); ?>" class="button <# if ( field.value.i == 1 ){ #> customify--checked <# } #>"><input type="checkbox" <# if ( field.value.i == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-i" value="1"><span class="dashicons dashicons-editor-italic"></span></label>
            <label title="<?php esc_attr_e( 'Underline', 'customify' ); ?>" class="button <# if ( field.value.u == 1 ){ #> customify--checked <# } #>"><input type="checkbox" <# if ( field.value.u == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-u" value="1"><span class="dashicons dashicons-editor-underline"></span></label>
            <label title="<?php esc_attr_e( 'Strikethrough', 'customify' ); ?>" class="button <# if ( field.value.s == 1 ){ #> customify--checked <# } #>"><input type="checkbox" <# if ( field.value.s == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-s" value="1"><span class="dashicons dashicons-editor-strikethrough"></span></label>
            <label title="<?php esc_attr_e( 'Uppercase', 'customify' ); ?>" class="button <# if ( field.value.t == 1 ){ #> customify--checked <# } #>"><input type="checkbox" <# if ( field.value.t == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}-t" value="1"><span class="dashicons dashicons-editor-textcolor"></span></label>
        </div>
        <?php
        $this->after_field();
    }

    function field_text_align(){
        $this->before_field();
        ?>
        <#
        var uniqueID = field.name + ( new Date().getTime() );
        #>
        <?php echo $this->field_header(); ?>
        <div class="customify-field-settings-inner">
            <div class="customify-text-align">
                <label><input type="radio" data-name="{{ field.name }}" value="left" <# if ( field.value == 'left' ){ #> checked="checked" <# } #> name="{{ uniqueID }}"> <span class="button"><span class="dashicons dashicons-editor-alignleft"></span></span></label>
                <label><input type="radio" data-name="{{ field.name }}" value="center" <# if ( field.value == 'center' ){ #> checked="checked" <# } #> name="{{ uniqueID }}"> <span class="button"><span class="dashicons dashicons-editor-aligncenter"></span></span></label>
                <label><input type="radio" data-name="{{ field.name }}" value="right" <# if ( field.value == 'right' ){ #> checked="checked" <# } #> name="{{ uniqueID }}"> <span class="button"><span class="dashicons dashicons-editor-alignright"></span></span></label>
                <# if ( ! field.no_justify ) {  #>
                <label><input type="radio" data-name="{{ field.name }}" value="justify" <# if ( field.value == 'justify' ){ #> checked="checked" <# } #> name="{{ uniqueID }}"> <span class="button"><span class="dashicons dashicons-editor-justify"></span></span></label>
                <# } #>
            </div>
        </div>
        <?php
        $this->after_field();
    }
    function field_text_align_no_justify() {
        ?>
        <#
            if ( _.isUndefined( field.no_justify ) )  {
                field.no_justify = true;
            }
            field.no_justify = true;
        #>
        <?php
        $this->field_text_align();
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
        <div class="customify-field-settings-inner customify-media-type-{{ field.type }}">
            <div class="customify--media">
                <input type="hidden" class="attachment-id" value="{{ field.value.id }}" data-name="{{ field.name }}">
                <input type="hidden" class="attachment-url"  value="{{ field.value.url }}" data-name="{{ field.name }}-url">
                <input type="hidden" class="attachment-mime"  value="{{ field.value.mime }}" data-name="{{ field.name }}-mime">
                <div class="customify-image-preview <# if ( url ) { #> customify--has-file <# } #>" data-no-file-text="<?php esc_attr_e( "No file selected", 'customify' ); ?>">
                    <#

                    if ( url ) {
                        if ( url.indexOf('http://') > -1 || url.indexOf('https://') ){

                        } else {
                            url = Customify_Control_Args.home_url + url;
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
                <button type="button" class="button customify--add <# if ( url ) { #> customify--hide <# } #>"><?php _e( 'Add', 'customify' ); ?></button>
                <button type="button" class="button customify--change <# if ( ! url ) { #> customify--hide <# } #>"><?php _e( 'Change', 'customify' ); ?></button>
                <button type="button" class="button customify--remove <# if ( ! url ) { #> customify--hide <# } #>"><?php _e( 'Remove', 'customify' ); ?></button>
            </div>
        </div>

        <?php
        $this->after_field();
    }







}