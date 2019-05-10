<?php
if ( is_admin() || is_customize_preview() ) {

	add_filter( 'customify/customizer/config', 'customify_pro_upsell', 9999 );

	function customify_pro_upsell( $configs ) {

		if ( class_exists( 'Customify_Pro' ) ) {
			return $configs;
		}

		$configs[] = array(
			'name'          => 'customify-pro',
			'type'          => 'section',
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'priority'      => 0,
			'pro_text'      => __( 'Customify Pro modules available', 'customify' ),
			'pro_url'       => 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_top',
		);

		$configs[] = array(
			'name'          => 'header_settings_pro',
			'panel'         => 'header_settings',
			'type'          => 'section',
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'priority'      => 99999,
			'title'         => __( 'Header options in Customify Pro', 'customify' ),
			'teaser'        => true,
			'pro_url'       => 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_header_side',
			'features'      => array(
				__( 'Header Sticky', 'customify' ),
				__( 'Header Transparent', 'customify' ),
				__( 'More HTML Items', 'customify' ),
				__( 'Secondary Menu', 'customify' ),
				__( 'Icon Box', 'customify' ),
				__( 'Contact Info', 'customify' ),
				__( 'And more header settings', 'customify' ),
			),
		);

		$configs[] = array(
			'name'          => 'footer_settings_pro',
			'panel'         => 'footer_settings',
			'type'          => 'section',
			'priority'      => 99999,
			'section_class' => 'Customify_WP_Customize_Section_Pro',
			'title'         => __( 'More Footer options in Customify Pro', 'customify' ),
			'pro_url'       => 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=customizer_footer_side',
			'teaser'        => true,
			'features'      => array(
				__( 'Footer Top Row', 'customify' ),
				__( 'Horizontal Menu Item', 'customify' ),
				__( 'More HTML Items', 'customify' ),
				__( 'Icon Box Item', 'customify' ),
				__( 'Contact Info Item', 'customify' ),
				__( 'Payment Methods Item', 'customify' ),
			),
		);

		return $configs;
	}
}
