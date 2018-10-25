<?php
if (is_admin() || is_customize_preview()) {

	add_filter('customify/customizer/config', 'customify_pro_upsell' , 9999 );

	function customify_pro_upsell( $configs ) {

		if ( class_exists( 'Customify_Pro' ) ) {
			return $configs;
		}

		$configs[] = array(
			'name' => 'customify-pro',
			'type' => 'section',
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'priority' => 0,
			'pro_text' => __( 'Customify Pro modules available', 'customify' ),
			'pro_url'  => 'https://wpcustomify.com/pricing/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_top'
		);

		$configs[] = array(
			'name'     => 'header_settings_pro',
			'panel'     => 'header_settings',
			'type'     => 'section',
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'priority' => 99999,
			'title'    => __( 'More Options on Customify Pro', 'customify' ),
			'teaser'    => true,
			'pro_url'  => 'https://wpcustomify.com/pricing/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_top',
			'features' => array(
				__( 'Header Sticky', 'customify' ),
				__( 'Header Transparent', 'customify' ),
				__( 'More HTML Items', 'customify' ),
				__( 'Secondary Menu', 'customify' ),
				__( 'Icon Box', 'customify' ),
				__( 'Contact Info', 'customify' ),
			)
		);

		$configs[] = array(
			'name'     => 'footer_settings_pro',
			'panel'     => 'footer_settings',
			'type'      => 'section',
			'priority' => 99999,
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'title'    => __( 'More Options on Customify Pro', 'customify' ),
			'pro_url'  => 'https://wpcustomify.com/pricing/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_top',
			'teaser'    => true,
			'features' => array(
				__( 'Horizontal menu', 'customify' ),
				__( 'More HTML items', 'customify' ),
				__( 'Icon Box', 'customify' ),
				__( 'Contact Info', 'customify' )
			)
		);

		return $configs;
	}
}