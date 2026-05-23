<?php

class Customify_Builder_Item_Footer_Widget_1 {
	public $id = 'footer-1';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 1', 'customify' ),
			'id'      => 'footer-1',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-1',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-1', 'sidebar-widgets-footer-1', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}

class Customify_Builder_Item_Footer_Widget_2 { //phpcs:ignore
	public $id = 'footer-2';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 2', 'customify' ),
			'id'      => 'footer-2',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-2',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-2', 'sidebar-widgets-footer-2', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}

class Customify_Builder_Item_Footer_Widget_3 { //phpcs:ignore
	public $id = 'footer-3';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 3', 'customify' ),
			'id'      => 'footer-3',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-3',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-3', 'sidebar-widgets-footer-3', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}

class Customify_Builder_Item_Footer_Widget_4 { //phpcs:ignore
	public $id = 'footer-4';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 4', 'customify' ),
			'id'      => 'footer-4',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-4',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-4', 'sidebar-widgets-footer-4', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}

class Customify_Builder_Item_Footer_Widget_5 { //phpcs:ignore
	public $id = 'footer-5';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 5', 'customify' ),
			'id'      => 'footer-5',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-5',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-5', 'sidebar-widgets-footer-5', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}

class Customify_Builder_Item_Footer_Widget_6 { //phpcs:ignore
	public $id = 'footer-6';

	function item() {
		return array(
			'name'    => __( 'Footer Sidebar 6', 'customify' ),
			'id'      => 'footer-6',
			'width'   => '3',
			'section' => 'sidebar-widgets-footer-6',
		);
	}

	function customize() {
		if ( function_exists( 'customify_header_layout_settings' ) ) {
			return customify_header_layout_settings( 'footer-6', 'sidebar-widgets-footer-6', 'customify_customize_render_footer', 'footer_' );
		}
		return array();
	}
}


function customify_change_footer_widgets_location( $wp_customize ) {
	for ( $i = 1; $i <= 6; $i ++ ) {
		if ( $wp_customize->get_section( 'sidebar-widgets-footer-' . $i ) ) {
			$wp_customize->get_section( 'sidebar-widgets-footer-' . $i )->panel = 'footer_settings';
		}
	}

}

add_action( 'customize_register', 'customify_change_footer_widgets_location', 999 );

/**
 * Always keep footer widget sections active from PHP's perspective.
 * WP uses sidebar section active state to sync widget data to the preview —
 * forcing inactive breaks widget rendering. JS handles hiding them from the UI.
 *
 * @param bool                 $active
 * @param WP_Customize_Section $section
 * @return bool
 */
function customify_customize_footer_widgets_show( $active, $section ) {
	if ( preg_match( '/widgets-footer-\d+$/', $section->id ) ) {
		return true;
	}
	return $active;
}
add_filter( 'customize_section_active', 'customify_customize_footer_widgets_show', 15, 2 );

/**
 * Display Footer widget
 *
 * @param string $footer_id
 */
function customify_builder_footer_widget_item( $footer_id = 'footer-1' ) {
	if ( is_active_sidebar( $footer_id ) ) {
		echo '<div class="widget-area">';
		dynamic_sidebar( $footer_id );
		echo '</div>';
	}
}

function customify_builder_footer_1_item() {
	customify_builder_footer_widget_item( 'footer-1' );
}

function customify_builder_footer_2_item() {
	customify_builder_footer_widget_item( 'footer-2' );
}

function customify_builder_footer_3_item() {
	customify_builder_footer_widget_item( 'footer-3' );
}

function customify_builder_footer_4_item() {
	customify_builder_footer_widget_item( 'footer-4' );
}

function customify_builder_footer_5_item() {
	customify_builder_footer_widget_item( 'footer-5' );
}

function customify_builder_footer_6_item() {
	customify_builder_footer_widget_item( 'footer-6' );
}

Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_1() );
Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_2() );
Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_3() );
Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_4() );
Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_5() );
Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Footer_Widget_6() );
