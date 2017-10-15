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
    public $default = array();
    public $device = '';

    public $live_title_field; // for repeater

    public $_settings;
    public $_selective_refresh;


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
        wp_enqueue_style('_beacon-customizer-control', get_template_directory_uri().'/assets/css/admin/customizer/customizer.css');
        wp_enqueue_script( '_beacon-customizer-control',  get_template_directory_uri().'/assets/js/customizer/control.js', array( 'jquery', 'customize-base', 'jquery-ui-core', 'jquery-ui-sortable' ), false, true );
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
                $value = ( array ) $value;
            }
            foreach ( $this->fields as $k => $f ) {
                if ( isset( $value[ $f['name'] ] ) ) {
                    $this->fields[ $k ]['value'] = $value[ $f['name'] ];
                }
            }
        } elseif (  $this->setting_type == 'repeater' ) {
            if ( ! is_array( $value ) ) {
                $value = ( array ) $value;
            }
        }

        $this->json['value']        = $value;
        $this->json['default']      = $this->default;
        $this->json['fields']       = $this->fields;
        $this->json['setting_type'] = $this->setting_type;
        if ( $this->setting_type == 'repeater' ) {
            $this->json['l10n'] = array(
                'untitled' => __( 'Untitled', '_beacon' )
            );

            $this->json['live_title_field'] = $this->live_title_field;
        }
        //$this->json['link']       = $this->get_link();
    }


    /**
     * Renders the control wrapper and calls $this->render_content() for the internals.
     *
     * @since 3.4.0
     */
    protected function render() {
        $id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
        $class = 'customize-control customize-control-' . $this->type.'-'.$this->setting_type;

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

        if ( $this->setting_type == 'device_select' ) {
            ?>
            <div class="_beacon--device-select">
                <a href="#" class="_beacon--active _beacon--tab-device-general"><?php _e( 'General', '_beacon' ); ?></a>
                <a href="#" class="_beacon--tab-device-mobile"><?php _e( 'Mobile', '_beacon' ); ?></a>
            </div>
            <?php

        } else {
            ?>
            <div class="_beacon--settings-wrapper">
                <label>
                    <?php if (!empty($this->label)) : ?>
                        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($this->description)) : ?>
                        <span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
                    <?php endif; ?>
                    <input type="hidden" {{{ data.inputAttrs }}} value="" <?php echo wp_kses_post($this->get_link()); ?> />
                </label>
                <div class="_beacon--settings-fields<?php echo ( $this->setting_type == 'repeater' ) ? ' _beacon--repeater-items' : ''; ?>"></div>
                <?php if ( $this->setting_type == 'repeater' ) { ?>
                    <a href="#" class="_beacon--repeater-add-new"><?php _e( 'Add item', '_beacon' ); ?></a>
                <?php } ?>
            </div>
            <?php
        }

    }

    function content_js_template() {
        if ( is_null( self::$_js_template_added ) ) {
            self::$_js_template_added  = true;
        } else {
            return ;
        }

        ?>
        <script type="text/html" id="tmpl-customize-control-<?php echo esc_attr( $this->type ); ?>-fields">
            <# _.each( data, function( field ){
                    switch( field.type ) { case 'select':
                    #>
                <# case 'textarea': #>
                    <?php $this->field_textarea(); ?>
                    <# break; #>
                <# break;
                    default: #>
                    <?php $this->field_text(); ?>
                <# break;
                }
            }); #>
        </script>
        <script type="text/html" id="tmpl-customize-control-<?php echo esc_attr( $this->type ); ?>-repeater">
            <div class="_beacon--repeater-item">
                <div class="_beacon--repeater-item-heading">
                    <span class="_beacon--repeater-live-title"></span>
                    <a href="#" class="_beacon--repeater-item-toggle"><span class="screen-reader-text"><?php _e( 'Close', '_beacon' ) ?></span></a>
                </div>
                <div class="_beacon--repeater-item-settings">
                    <div class="_beacon--repeater-item-inner">{{{ data }}}</div>
                </div>
            </div>
        </script>
        <?php
    }

    function before_field(){
        ?>
        <div class="_beacon--field _beacon--field-{{ field.type }}">
        <?php
    }

     function after_field(){
        ?>
        </div>
        <?php
    }

    function field_text(){
        $this->before_field();
        ?>
        <# if ( field.label ) { #>
            <label>{{{ field.label }}}</label>
        <# } #>
        <# if ( field.description ) { #>
            <p class="description">{{{ field.description }}}</p>
        <# } #>
        <input type="text" data-name="{{ field.name }}" value="{{ field.value }}">
        <?php
        $this->after_field();
    }

    function field_textarea(){
        $this->before_field();
        ?>
        <# if ( field.label ) { #>
            <label>{{{ field.label }}}</label>
        <# } #>
        <# if ( field.description ) { #>
            <p class="description">{{{ field.description }}}</p>
        <# } #>
        <textarea rows="10" data-name="{{ field.name }}">{{ field.value }}</textarea>
        <?php
        $this->after_field();
    }



}