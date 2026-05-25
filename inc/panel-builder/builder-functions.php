<?php
/**
 * Switch new installs to header builder v2 automatically.
 *
 * - If v2 is already active: hide the version switcher and return.
 * - If no v1 data exists: activate v2 and hide the switcher so the
 *   user is not prompted to choose a builder version.
 *
 * Called on theme activation (admin_notice hook) and on every header
 * render so the switch happens even if the notice was missed.
 *
 * @since 0.2.9
 */
function customify_maybe_change_header_version() {
	$current_ver = get_theme_mod( 'header_builder_version' );
	if ( 'v2' === $current_ver ) {
		set_theme_mod( 'hide_header_builder_switcher', 'yes' );
		return;
	}
	$ver1_data = get_theme_mod( 'header_builder_panel' );
	if ( ! $ver1_data || empty( $ver1_data ) ) {
		set_theme_mod( 'header_builder_version', 'v2' );
		set_theme_mod( 'hide_header_builder_switcher', 'yes' );
	}
}

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
 * One-time migration of V1 footer data into V2 storage.
 *
 * Reads footer_builder_panel (V1: { desktop: { main: [{x,y,id,...}], bottom: [...] } })
 * and writes footer_builder_panel_v2 plus footer_main_col_layout / footer_bottom_col_layout.
 *
 * Algorithm per row (main, bottom):
 *  1. Take items from V1 desktop row, drop empty/duplicate ids, sort by x ASC.
 *  2. Cap at 5 items (V2 supports max 5 columns).
 *  3. Distribute items in order into V2 columns: left, center, right, col4, col5.
 *  4. Build col_layout: desktop = N equal cols; tablet = 2 cols; mobile = 1 col.
 *
 * Strategy: force overwrite. The v1->v2 flag prevents re-running, so subsequent
 * user edits in the React builder are preserved.
 *
 * @since 0.5.0
 */
function customify_migrate_footer_v1_to_v2() {
	if ( get_theme_mod( 'footer_v1_to_v2_migrated' ) ) {
		return;
	}

	$v1 = get_theme_mod( 'footer_builder_panel' );
	if ( ! is_array( $v1 ) || empty( $v1['desktop'] ) || ! is_array( $v1['desktop'] ) ) {
		set_theme_mod( 'footer_v1_to_v2_migrated', true );
		return;
	}

	$all_cols = array( 'left', 'center', 'right', 'col4', 'col5' );
	$rows     = array( 'main', 'bottom' );
	$v2       = array( 'desktop' => array() );

	foreach ( $rows as $row_id ) {
		$v1_items = isset( $v1['desktop'][ $row_id ] ) && is_array( $v1['desktop'][ $row_id ] )
			? $v1['desktop'][ $row_id ]
			: array();

		// Drop items with empty id; dedupe by id (keep first occurrence).
		$seen  = array();
		$items = array();
		foreach ( $v1_items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || isset( $seen[ $item['id'] ] ) ) {
				continue;
			}
			$seen[ $item['id'] ] = true;
			$items[]             = $item;
		}

		// Sort by x ASC for left-to-right visual order.
		usort(
			$items,
			function ( $a, $b ) {
				return (int) ( isset( $a['x'] ) ? $a['x'] : 0 ) - (int) ( isset( $b['x'] ) ? $b['x'] : 0 );
			}
		);

		$items = array_slice( $items, 0, 5 );
		$count = count( $items );

		$v2_row = array_fill_keys( $all_cols, array() );
		foreach ( $items as $i => $item ) {
			$v2_row[ $all_cols[ $i ] ] = array( array( 'id' => $item['id'] ) );
		}
		$v2['desktop'][ $row_id ] = $v2_row;

		if ( 0 === $count ) {
			continue;
		}

		set_theme_mod(
			'footer_' . $row_id . '_col_layout',
			array(
				'count'   => $count,
				'desktop' => array( 'fr' => array_fill( 0, $count, 1 ) ),
				'tablet'  => array( 'fr' => array( 1, 1 ) ),
				'mobile'  => array( 'fr' => array( 1 ) ),
			)
		);
	}

	set_theme_mod( 'footer_builder_panel_v2', $v2 );
	set_theme_mod( 'footer_v1_to_v2_migrated', true );
}
add_action( 'admin_init', 'customify_migrate_footer_v1_to_v2' );

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
	// Row order must match the Customizer (top → main → bottom). `top` is
	// registered by Customify Pro via the `customify/builder/footer/rows`
	// filter; the renderer's own isset() guard silently skips it when no
	// items have been placed in that row, so passing it here is safe in
	// the Free theme too.
	$builder->render( array( 'top', 'main', 'bottom' ) );

	echo '</footer>';
	do_action( 'customify/after-footer' );
}
