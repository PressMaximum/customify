<?php

require_once get_template_directory() . '/inc/panel-builder/class-customize-builder-panel.php';
require_once get_template_directory() . '/inc/panel-builder/class-customize-layout-builder.php';
require_once get_template_directory() . '/inc/panel-builder/class-customize-layout-builder-frontend.php';

/**
 * Alias of class Customify_Customize_Layout_Builder
 *
 * @see Customify_Customize_Layout_Builder
 *
 * @return Customify_Customize_Layout_Builder
 */
function Customify_Customize_Layout_Builder() {
	return Customify_Customize_Layout_Builder::get_instance();
}

/**
 * Alias of class Customify_Customize_Layout_Builder_Frontend
 *
 * @see Customify_Customize_Layout_Builder_Frontend
 *
 * @return Customify_Customize_Layout_Builder_Frontend
 */
function Customify_Customize_Layout_Builder_Frontend() {
	return Customify_Customize_Layout_Builder_Frontend::get_instance();
}

/**
 * Display Header Layout
 */
function customify_customize_render_header() {
	if ( ! customify_is_header_display() ) {
		return;
	}
	echo Customify_Customize_Layout_Builder_Frontend()->close_icon( ' close-panel close-sidebar-panel' );
	/**
	 * Hook before header
	 *
	 * @since 0.2.2
	 */
	do_action( 'customizer/before-header' );
	echo '<header id="masthead" class="site-header">';
		echo '<div id="masthead-inner" class="site-header-inner">';
			$list_items = Customify_Customize_Layout_Builder()->get_builder_items( 'header' );
			Customify_Customize_Layout_Builder_Frontend()->set_config_items( $list_items );
			Customify_Customize_Layout_Builder_Frontend()->render();
			Customify_Customize_Layout_Builder_Frontend()->render_mobile_sidebar();
		echo '</div>';
	echo '</header>';
	/**
	 * Hook after header
	 *
	 * @since 0.2.2
	 */
	do_action( 'customizer/after-header' );
}

/**
 * Display Footer Layout
 */
function customify_customize_render_footer() {
	if ( ! customify_is_footer_display() ) {
		return;
	}
	/**
	 * Hook before footer
	 *
	 * @since 0.2.2
	 */
	do_action( 'customify/before-footer' );
	echo '<footer class="site-footer" id="site-footer">';
	Customify_Customize_Layout_Builder_Frontend()->set_id( 'footer' );
	Customify_Customize_Layout_Builder_Frontend()->set_control_id( 'footer_builder_panel' );
	$list_items = Customify_Customize_Layout_Builder()->get_builder_items( 'footer' );
	Customify_Customize_Layout_Builder_Frontend()->set_config_items( $list_items );
	Customify_Customize_Layout_Builder_Frontend()->render();
	echo '</footer>';
	/**
	 * Hook before footer
	 *
	 * @since 0.2.2
	 */
	do_action( 'customify/after-footer' );
}

/**
 * Initial Layout Builder
 */
Customify_Customize_Layout_Builder()->init();

/**
 * Add Header Content To Frontend
 */
add_action( 'customify/site-start', 'customify_customize_render_header' );
/**
 * Add Footer Content To Frontend
 */
add_action( 'customify/site-end', 'customify_customize_render_footer' );
