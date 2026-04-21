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
 * Fallback footer render: one equal-width column per active sidebar item.
 * Used when footer_builder_panel_v2 has no saved data yet.
 *
 * @param array $list_items Registered footer builder items.
 */
function customify_render_footer_fallback( $list_items ) {
	$active = array();
	foreach ( $list_items as $id => $item ) {
		if ( strpos( $id, 'footer-' ) === 0 && is_active_sidebar( $id ) ) {
			$active[] = $id;
		}
	}

	if ( empty( $active ) ) {
		return;
	}

	$col_width = floor( 12 / count( $active ) );
	$gridlex   = 'col-md-' . $col_width;

	echo '<div id="cb-row--footer-main" class="footer-main footer--row layout-full-contained">';
	echo '<div class="footer--row-inner footer-main-inner">';
	echo '<div class="customify-container">';
	echo '<div class="customify-grid">';

	foreach ( $active as $sidebar_id ) {
		$fn = 'customify_builder_' . str_replace( '-', '_', $sidebar_id ) . '_item';
		echo '<div class="' . esc_attr( $gridlex ) . ' builder-item--' . esc_attr( $sidebar_id ) . '">';
		if ( function_exists( $fn ) ) {
			call_user_func( $fn );
		}
		echo '</div>';
	}

	echo '</div></div></div></div>';
}

/**
 * Display Footer Layout — uses v2 React builder data when available, falls back to v1.
 */
function customify_customize_render_footer() {
	if ( ! customify_is_footer_display() ) {
		return;
	}

	do_action( 'customify/before-footer' );
	echo '<footer class="site-footer" id="site-footer">';

	$v2_data    = Customify()->get_setting( 'footer_builder_panel_v2' );
	$list_items = Customify_Customize_Layout_Builder()->get_builder_items( 'footer' );
	$builder    = Customify_Layout_Builder_Frontend_V2();
	$builder->set_id( 'footer' );
	$builder->set_control_id( 'footer_builder_panel_v2' );
	$builder->set_config_items( $list_items );

	if ( ! empty( $v2_data ) ) {
		$builder->render( array( 'main', 'bottom' ) );
	} else {
		customify_render_footer_fallback( $list_items );
	}

	echo '</footer>';
	do_action( 'customify/after-footer' );
}
