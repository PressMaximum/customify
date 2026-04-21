<?php
/**
 * Display Header Layout (always uses v2 builder).
 */
function customify_customize_render_header() {
	if ( ! customify_is_header_display() ) {
		return;
	}

	$list_items = Customify_Customize_Layout_Builder()->get_builder_items( 'header' );
	$builder    = Customify_Layout_Builder_Frontend_V2();
	$builder->set_id( 'header' );
	$builder->set_control_id( 'header_builder_panel_v2' );
	$builder->set_config_items( $list_items );

	echo $builder->close_icon( ' close-panel close-sidebar-panel' );

	do_action( 'customify/before-header' );
	echo '<header id="masthead" class="site-header header-v2">';
		echo '<div id="masthead-inner" class="site-header-inner">';
			$builder->render();
			$builder->render_mobile_sidebar();
		echo '</div>';
	echo '</header>';
	do_action( 'customify/after-header' );
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
