<?php
add_filter( 'customify/customize/settings-default', 'customify_layout_builder_config_default', 15, 2 );
/**
 * Default theme customize settings data.
 *
 * @param string $val
 * @param string $name
 *
 * @return mixed
 */
function customify_layout_builder_config_default( $val, $name ) {
	$defaults =
		array(
			// Start header builder layout version 1.
			'header_builder_panel'      => array(
				'desktop' =>
					array(
						'main' =>
							array(
								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'logo',
								),

								array(
									'x'      => '3',
									'y'      => '1',
									'width'  => '9',
									'height' => '1',
									'id'     => 'primary-menu',
								),
							),
					),
				'mobile'  =>
					array(
						'main'    =>
							array(

								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '5',
									'height' => '1',
									'id'     => 'logo',
								),

								array(
									'x'      => '9',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'nav-icon',
								),
							),
						'sidebar' =>
							array(

								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '1',
									'height' => '1',
									'id'     => 'html',
								),

								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '1',
									'height' => '1',
									'id'     => 'primary-menu',
								),
							),
					),
			), // End header builder layout version 1.
			'header_builder_version' => '',
			// Start header builder layout version 2.
			'header_builder_panel_v2' => array(
				'desktop' => array(
					'top' => array(
						'left' => array(
							array(
								'id' => 'html',
							),
						),
						'center' => array(),
						'right' => array(
							array(
								'id' => 'social-icons',
							),
						),
					),
					'main' => array(
						'left' => array(
							array(
								'id' => 'logo',
							),
							array(
								'id' => 'primary-menu',
							),
						),
						'center' => array(),
						'right' => array(
							array(
								'id' => 'search_icon',
							),
							array(
								'id' => 'nav-icon',
							),
							array(
								'id' => 'button',
							),
						),
					),
					'bottom' => array(
						'left' => array(),
						'center' => array(),
						'right' => array(),
					),
				),
				'mobile' => array(
					'top' => array(
						'left' => array(),
						'center' => array(),
						'right' => array(),
					),
					'main' => array(
						'left' => array(
							array(
								'id' => 'logo',
							),
						),
						'center' => array(),
						'right' => array(
							array(
								'id' => 'search_icon',
							),
							array(
								'id' => 'nav-icon',
							),
						),
					),
					'bottom' => array(
						'left'   => array(),
						'center' => array(),
						'right'  => array(),
					),
					'sidebar' => array(
						'sidebar' => array(
							array(
								'id' => 'html',
							),
							array(
								'id' => 'search_box',
							),
							array(
								'id' => 'primary-menu',
							),
							array(
								'id' => 'social-icons',
							),
							array(
								'id' => 'button',
							),
						),
					),
				),
			), // End header builder layout version 2.

			'header_top_height'         => array(
				'desktop' =>
					array(
						'unit'  => 'px',
						'value' => '33',
					),
				'tablet'  =>
					array(
						'unit'  => 'px',
						'value' => '',
					),
				'mobile'  =>
					array(
						'unit'  => 'px',
						'value' => '33',
					),
			),
			'header_main_height'        => array(
				'desktop' => array(
					'unit' => 'px',
					'value' => '90',
				),
				'tablet'  => array(
					'unit' => 'px',
					'value' => '',
				),
				'mobile'  => array(
					'unit' => 'px',
					'value' => '',
				),
			),
			'header_bottom_height'      => array(
				'desktop' => array(
					'unit' => 'px',
					'value' => '55',
				),
				'tablet'  => array(
					'unit' => 'px',
					'value' => '',
				),
				'mobile'  => array(
					'unit' => 'px',
					'value' => '',
				),
			),
			'header_sidebar_animate'    => 'menu_sidebar_dropdown',
			'header_nav-icon_align'     => array(
				'desktop' => 'right',
				'tablet'  => 'right',
				'mobile'  => 'right',
			),
			'header_primary-menu_align' => array(
				'desktop' => 'right',
				'tablet'  => '',
				'mobile'  => '',
			),
			'footer_builder_panel'      => array(
				'desktop' =>
					array(
						'main'   =>
							array(
								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'footer-1',
								),
								array(
									'x'      => '3',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'footer-2',
								),
								array(
									'x'      => '6',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'footer-3',
								),

								array(
									'x'      => '9',
									'y'      => '1',
									'width'  => '3',
									'height' => '1',
									'id'     => 'footer-4',
								),
							),
						'bottom' =>
							array(

								array(
									'x'      => '0',
									'y'      => '1',
									'width'  => '6',
									'height' => '1',
									'id'     => 'footer_copyright',
								),
							),
					),
			),

			// V2 footer defaults — equivalent to the V1 default after migration.
			'footer_builder_panel_v2' => array(
				'desktop' => array(
					'main'   => array(
						'left'   => array( array( 'id' => 'footer-1' ) ),
						'center' => array( array( 'id' => 'footer-2' ) ),
						'right'  => array( array( 'id' => 'footer-3' ) ),
						'col4'   => array( array( 'id' => 'footer-4' ) ),
						'col5'   => array(),
					),
					'bottom' => array(
						'left'   => array( array( 'id' => 'footer_copyright' ) ),
						'center' => array(),
						'right'  => array(),
						'col4'   => array(),
						'col5'   => array(),
					),
				),
			),
			// Match DEFAULT_VALUE.count = 4 in
			// src/backend/footer-row-layout/presets.js so the renderer's
			// fallback (when nothing is saved) lines up with the React
			// builder's initial render. Especially relevant for the Pro-
			// added `top` row, which rarely has explicit col_layout saved.
			'footer_top_col_layout' => array(
				'count'   => 4,
				'desktop' => array( 'fr' => array( 1, 1, 1, 1 ) ),
				'tablet'  => array( 'fr' => array( 1, 1 ) ),
				'mobile'  => array( 'fr' => array( 1 ) ),
			),
			'footer_main_col_layout' => array(
				'count'   => 4,
				'desktop' => array( 'fr' => array( 1, 1, 1, 1 ) ),
				'tablet'  => array( 'fr' => array( 1, 1 ) ),
				'mobile'  => array( 'fr' => array( 1 ) ),
			),
			'footer_bottom_col_layout' => array(
				'count'   => 1,
				'desktop' => array( 'fr' => array( 1 ) ),
				'tablet'  => array( 'fr' => array( 1, 1 ) ),
				'mobile'  => array( 'fr' => array( 1 ) ),
			),
		);

	if ( ! $val && isset( $defaults[ $name ] ) ) {
		return $defaults[ $name ];
	}

	return $val;
}
