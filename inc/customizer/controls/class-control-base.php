<?php
class Customify_Customizer_Control_Base extends WP_Customize_Control {
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
    public $reset_controls = array();
    public $limit ;

    // For slider
    public $min = 0;
    public $max = 700;
    public $step = 1;
    public $unit = false;

    // For CSS Ruler
    public $fields_disabled = array();


    public $limit_msg = '';
    public $live_title_field; // for repeater
    public $addable = null; // for repeater
    public $title_only = null; // for repeater
    public $_settings;
    public $_selective_refresh;
    public $device_settings = false;
    public $no_setup = false;

    /**
     * Provide the parent, comparison operator, and value which affects the field’s visibility
     *
     * @var
     */
    public $required;
    public $field_class = '';
    static $_js_template_added;
    static $_args_loaded;
    function __construct($manager, $id, $args = array())
    {
        parent::__construct($manager, $id, $args);
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
            // Fallback value when device_settings from true to false
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
        $this->json['reset_controls']      = $this->reset_controls;

        if ( $this->no_setup ) {
            return;
        }

        $this->json['min'] = $this->min;
        $this->json['max'] = $this->max;
        $this->json['step'] = $this->step;
        $this->json['unit'] = $this->unit;
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
     * Enqueue control related scripts/styles.
     *
     * @access public
     */
    public function enqueue() {
        wp_enqueue_media();
        if( $this->setting_type == 'repeater' ) {
            wp_enqueue_script('jquery-ui-sortable');
        }

        $suffix = Customify()->get_asset_suffix();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_style('customify-customizer-control', esc_url( get_template_directory_uri() ) .'/assets/css/admin/customizer/customizer'.$suffix.'.css');
        wp_enqueue_script( 'customify-color-picker-alpha', esc_url( get_template_directory_uri() ).'/assets/js/customizer/color-picker-alpha'.$suffix.'.js', array( 'wp-color-picker' ), false, true );
        wp_enqueue_script( 'customify-customizer-control', esc_url( get_template_directory_uri() ).'/assets/js/customizer/control'.$suffix.'.js', array( 'jquery', 'customize-base', 'jquery-ui-core', 'jquery-ui-sortable' ), false, true );
        if ( is_null( self::$_args_loaded ) ) {
            wp_localize_script('customify-customizer-control', 'Customify_Control_Args', array(
                'home_url' => esc_url( home_url('') ),
                'ajax' => admin_url('admin-ajax.php'),
                'is_rtl' => is_rtl(),
                'theme_default' => __('Theme Default', 'customify'),
                'reset' => __('Reset this section settings', 'customify'),
                'untitled' => __('Untitled', 'customify'),
                'confirm_reset' => __('Do you want to reset this section settings?', 'customify'),
                'list_font_weight' => array(
                    ''   => __('Default', 'customify'),
                    'normal'    => _x('Normal', 'customify-font-weight', 'customify'),
                    'bold'      => _x('Bold', 'customify-font-weight', 'customify'),
                ),
                'typo_fields' => Customify()->customizer->get_typo_fields(),
                'styling_config' => Customify()->customizer->get_styling_config(),
                'devices' => Customify()->customizer->devices,
            ));
            self::$_args_loaded = true;
        }
    }


    /**
     * Renders the control wrapper and calls $this->render_content() for the internals.
     *
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
            <?php if ( $this->label ) { ?>
            <div data-control="<?php echo esc_attr( $this->id ); ?>" class="customify-control-field-header customify-field-heading">
                <label>
                    <?php if (!empty($this->label)) : ?>
                        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <?php endif; ?>
                </label>
            </div>
            <?php } ?>
            <?php
            if ( $this->setting_type == 'custom_html' ) {
                ?>
                <div class="custom_html"><?php
                    echo $this->description;  // WPCS: XSS OK.
                ?></div>
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
                    <a href="#" class="customify--repeater-reorder" data-text="<?php esc_attr_e( 'Reorder', 'customify' ); ?>" data-done="<?php _e( 'Done', 'customify' ); ?>"><?php _e( 'Reorder', 'customify' ); ?></a>
                    <?php if ( $this->addable !== false ) { ?>
                    <button type="button" class="button customify--repeater-add-new"><?php _e( 'Add an item', 'customify' ); ?></button>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
    }

    static function before_field(){
        ?>
        <#
        var required = '';
        if ( ! _.isUndefined( field.required ) ) {
            required = JSON.stringify( field.required  );
        }
        #>
        <div class="customify--field customify--field-{{ field.type }} {{ field.class }} customify--field-name-{{ field.original_name }}" data-required="{{ required }}" data-field-name="{{ field.name }}">
        <?php
    }

    static function after_field(){
        ?>
        </div>
        <?php
    }

    static function field_header(){
        ?>
            <# if ( field.label || field.description ) { #>
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
            <# } #>
        <?php
    }

    static function field_template(){
        ?>
        <script type="text/html" id="tmpl-field-customify-text">
            <?php
        self::before_field();
        ?>
        <?php echo self::field_header(); ?>
        <div class="customify-field-settings-inner">
            <input type="{{ field.type }}" class="customify-input customify-only" data-name="{{ field.name }}" value="{{ field.value }}">
        </div>
        <?php
        self::after_field();
        ?>
        </script>
        <?php
    }


}