<?php
if (is_admin() || is_customize_preview()) {

    if( class_exists( 'WP_Customize_Section' ) ) {
        class Customify_Section_Pro extends WP_Customize_Section
        {
            /**
             * The type of customize section being rendered.
             *
             * @since  1.0.0
             * @access public
             * @var    string
             */
            public $type = 'customify-pro';
            /**
             * Custom button text to output.
             *
             * @since  1.0.0
             * @access public
             * @var    string
             */
            public $pro_text = '';
            /**
             * Custom plus section URL.
             *
             * @since  1.0.0
             * @access public
             * @var    string
             */
            public $pro_url = '';
            /**
             * Custom section ID.
             *
             * @since  1.0.0
             * @access public
             * @var    string
             */
            public $id = '';

            /**
             * Add custom parameters to pass to the JS via JSON.
             *
             * @since  1.0.0
             * @access public
             * @return void
             */
            public function json()
            {
                $json = parent::json();
                $json['pro_text'] = $this->pro_text;
                $json['pro_url'] = $this->pro_url;
                $json['id'] = $this->id;
                return $json;
            }

            /**
             * Outputs the Underscore.js template.
             *
             * @since  1.0.0
             * @access public
             * @return void
             */
            protected function render_template()
            { ?>
                <li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">
                    <h3><a href="{{ data.pro_url }}" target="_blank">{{{ data.pro_text }}}</a></h3>
                </li>
            <?php }
        }
    }

    function customify_pro_upsell( $wp_customize ){
        if ( class_exists( 'Customify_Pro' ) ) {
            return ;
        }
        $wp_customize->register_section_type( 'Customify_Section_Pro' );
        $wp_customize->add_section(
            new Customify_Section_Pro(
                $wp_customize,
                'customify-pro',
                array(
                    'priority'  => 0,
                    'pro_text' => __( 'Customify Pro modules available', 'customify' ),
                    'pro_url'  => 'https://wpcustomify.com/pricing/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_top'
                )
            )
        );

    }

    add_action('customize_register', 'customify_pro_upsell');
}