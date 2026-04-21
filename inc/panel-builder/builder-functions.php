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
 * Display Footer Layout.
 * V2 data is used when saved; falls back to migrated V1 data or config defaults.
 * Migration logic lives in Customify_Layout_Builder_Frontend_V2::get_settings().
 */
function customify_customize_render_footer() {
	if ( ! customify_is_footer_display() ) {
		return;
	}

	do_action( 'customify/before-footer' );
	echo '<footer class="site-footer" id="site-footer">';

	$list_items = Customify_Customize_Layout_Builder()->get_builder_items( 'footer' );
	$builder    = Customify_Layout_Builder_Frontend_V2();
	$builder->set_id( 'footer' );
	$builder->set_control_id( 'footer_builder_panel_v2' );
	$builder->set_config_items( $list_items );
	$builder->render( array( 'main', 'bottom' ) );

	echo '</footer>';
	do_action( 'customify/after-footer' );
}
