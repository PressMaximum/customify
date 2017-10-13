<?php

class _Beacon_Customizer_Control extends WP_Customize_Control {
    /**
     * The control type.
     *
     * @access public
     * @var string
     */
    //public $type = '_beacon';
    /**
     * Used to automatically generate all CSS output.
     *
     * @access public
     * @var array
     */
    public $output = array();
    /**
     * Data type
     *
     * @access public
     * @var string
     */
    public $option_type = 'theme_mod';

    /**
     * The fields that each container row will contain.
     *
     * @access public
     * @var array
     */
    public $fields = array();
    /**
     * Will store a filtered version of value for advenced fields (like images).
     *
     * @access protected
     * @var array
     */
    protected $filtered_value = array();


    /**
     * Enqueue control related scripts/styles.
     *
     * @access public
     */
    public function enqueue() {
        wp_enqueue_media();
        wp_enqueue_script( '_beacon_customizer_controls',  get_template_directory_uri().'/assets/js/customizer/customizer-controls.js' );
    }

    /**
     * Render the control's content.
     * Allows the content to be overriden without having to rewrite the wrapper in $this->render().
     *
     * @access protected
     */
    protected function render_content() {
        ?>
        <label>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
            <?php endif; ?>
            <input type="hidden" {{{ data.inputAttrs }}} value="" <?php echo wp_kses_post( $this->get_link() ); ?> />
        </label>

        <h2>This is test template of custom control</h2>

        <?php if ( isset( $this->choices['limit'] ) ) : ?>
            <?php // @codingStandardsIgnoreLine ?>
            <?php /* translators: %s represents the number of rows we're limiting the repeater to allow. */ ?>
            <p class="limit"><?php printf( esc_attr__( 'Limit: %s rows', 'kirki' ), esc_html( $this->choices['limit'] ) ); ?></p>
        <?php endif; ?>
        <button class="button-secondary repeater-add"><?php echo esc_html( $this->button_label ); ?></button>

        <?php
        //$this->repeater_js_template();
    }


}