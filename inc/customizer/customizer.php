<?php
/**
 * _beacon Theme Customizer
 *
 * @package _beacon
 */




/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function _beacon_customize_register( $wp_customize ) {

    require_once get_template_directory().'/inc/customizer/customizer-control.php';

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => '_beacon_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => '_beacon_customize_partial_blogdescription',
		) );
	}


    /*------------------------------------------------------------------------*/
    /*  Site Options
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( '_beacon_test',
        array(
            //'priority'       => 22,
           // 'capability'     => 'edit_theme_options',
            'theme_supports' => '',
            'title'          => esc_html__( 'Beacon Test', '_beacon' ),
            'description'    => '',
        )
    );

    /* Global Settings
    ----------------------------------------------------------------------*/
    $wp_customize->add_section( '_beacon_test_section' ,
        array(
            'priority'    => 3,
            'title'       => esc_html__( 'Test Section', '_beacon' ),
            'description' => '',
            'panel'       => '_beacon_test',
        )
    );

    // Sidebar settings
    $wp_customize->add_setting( 'test_settings',
        array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'right-sidebar',
            //'transport'			=> 'postMessage'
        )
    );

    $wp_customize->add_control( new _Beacon_Customizer_Control( $wp_customize, 'test_settings',
        array(
            'label'       => esc_html__( 'Test Settings', '_beacon' ),
            'section'     => '_beacon_test_section',
            'description' => '',
            'priority'    => 1
        )
    ));





}
add_action( 'customize_register', '_beacon_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function _beacon_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function _beacon_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function _beacon_customize_preview_js() {
	wp_enqueue_script( '_beacon-customizer', get_template_directory_uri() . '/assets/js/customizer/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', '_beacon_customize_preview_js' );
