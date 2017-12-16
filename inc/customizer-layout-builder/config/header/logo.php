<?php

class Customify_Builder_Item_Logo {
    public $id= 'logo';
    function item(){
        return  array(
            'name' => __( 'Logo', 'customify' ),
            'id' => 'logo',
            'width' => '3',
            'section' => 'header_logo' // Customizer section to focus when click settings
        );
    }

    function customize( $wp_customize ){
        $section = 'header_logo';
        $render_cb_el = array( $this, 'render' ) ;
        $selector = '.site-branding';
        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'title' => __( 'Logo', 'customify' ),
            ),

            array(
                'name' => 'header_logo_use_default',
                'type' => 'checkbox',
                'section' => $section,
                'default' => 1,
                'selector' => $selector,
                'render_callback' => $render_cb_el,
                'title' => __( 'Logo', 'customify' ),
                'checkbox_label' => __( 'Use Site Identity Logo', 'customify' ),
                'description' => __( 'If check this, you can settings your logo in <a href="#title_tagline" class="focus-section">Site Identity</a> section.', 'customify' ),
            ),

            array(
                'name' => 'header_logo',
                'type' => 'image',
                'section' =>  $section,
                'device_settings' => false,
                'selector' => $selector,
                'render_callback' => $render_cb_el,
                'title' => __( 'Logo', 'customify' ),
                'required' => array( 'header_logo_use_default', '!=', '1'),
            ),

            array(
                'name' => 'header_logo_retina',
                'type' => 'image',
                'section' =>  $section,
                'device_settings' => false,
                'selector' => $selector,
                'render_callback' => $render_cb_el,
                'title' => __( 'Logo Retina', 'customify' ),
                'required' => array( 'header_logo_use_default', '!=', '1'),
            ),

            array(
                'name' => 'header_logo_name',
                'type' => 'checkbox',
                'section' => $section,
                'default' => '',
                'selector' => $selector,
                'render_callback' => $render_cb_el,
                'title' => __( 'Show Site Name', 'customify' ),
                'checkbox_label' => __( 'Show site name ?', 'customify' ),
            ),

            array(
                'name' => 'header_logo_desc',
                'type' => 'checkbox',
                'section' => $section,
                'default' => '',
                'selector' => $selector,
                'render_callback' => $render_cb_el,
                'title' => __( 'Show Site Description', 'customify' ),
                'checkbox_label' => __( 'Show site description ?', 'customify' ),
            ),

            array(
                'name' => 'logo_height',
                'type' => 'slider',
                'section' =>  $section,
                'default' => array(
                    'unit' => 'px',
                    'value' => 60,
                ),
                'max' => 200,
                'device_settings' => true,
                'title' => __( 'Logo Height', 'customify' ),
                'selector' => $selector.' img',
                'css_format' => 'height: {{value}};'
            ),


            array(
                'name' => 'header_logo_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'device_settings' => true,
                'selector' => '.builder-item--logo',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
            ),

        );

        // add selective refresh
        // remove_partial
        $wp_customize->selective_refresh->remove_partial( 'custom_logo' );

        $settings['settings'][] = 'custom_logo';
        $settings['settings'][] = 'blogname';
        $settings['settings'][] = 'blogdescription';


        $wp_customize->selective_refresh->add_partial( 'custom_logo', array(
            'selector' => $selector,
            'settings' => array( 'custom_logo', 'blogname', 'blogdescription' ),
            'render_callback' => $render_cb_el,
        ) );


        return $config;
    }

    function render(){
        $logo_default = Customify_Customizer()->get_setting( 'header_logo_use_default' );
        $show_name = Customify_Customizer()->get_setting( 'header_logo_name' );
        $show_desc = Customify_Customizer()->get_setting( 'header_logo_desc' );

        ?>
        <div class="site-branding">
            <?php
            if ( $logo_default ) {
                the_custom_logo();
            } else {

                $logo = Customify_Customizer()->get_setting( 'header_logo' );
                $logo_image = Customify_Customizer()->get_media( $logo );
                $logo_retina = Customify_Customizer()->get_setting( 'header_logo_retina' );
                $logo_retina_image = Customify_Customizer()->get_media( $logo_retina );

                if ( $logo_image ) {
                    ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-link" rel="home" itemprop="url">
                        <img src="<?php echo esc_url($logo_image); ?>"
                             alt="<?php esc_attr(get_bloginfo('name')); ?>"<?php if ($logo_retina_image) { ?> srcset="<?php echo esc_url($logo_retina_image); ?> 2x"<?php } ?>>
                    </a>
                    <?php
                }
            }
            if ( $show_name ) {
                if (is_front_page() && is_home()) : ?>
                    <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                <?php else : ?>
                    <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
                    <?php
                endif;
            }

            if ( $show_desc ) {
                $description = get_bloginfo('description', 'display');
                if ($description || is_customize_preview()) { ?>
                    <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                    <?php
                };
            }
            ?>
        </div><!-- .site-branding -->
        <?php
    }
}

Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Logo() );
