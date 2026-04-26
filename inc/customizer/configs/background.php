<?php

class Customify_Advanced_Styling_Background {

	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
	}

	function config( $configs = array() ) {

		$config = array(

			array(
				'name'     => 'background',
				'type'     => 'section',
				'priority' => 15,
				'panel'    => 'styling_panel',
				'title'    => __( 'Background', 'customify' ),
			),

			array(
				'name'       => 'background',
				'type'       => 'styling',
				'section'    => 'background',
				'title'      => __( 'Site Background', 'customify' ),
				'selector'   => array(
					'normal' => 'body',
				),
				// Default `bg_color` removed so auto_css does not emit a
				// `body { background-color: #FFFFFF }` rule when user has
				// not picked a colour. The slot-bound rule in `_base.scss`
				// then paints from the active palette. User-picked colours
				// still win via cascade order.
				'css_format' => 'styling', // styling.
				'fields'     => array(
					'normal_fields' => array(
						'text_color'     => false,
						'link_color'     => false,
						'padding'        => false,
						'margin'         => false,
						'border_heading' => false,
						'border_width'   => false,
						'border_color'   => false,
						'border_radius'  => false,
						'box_shadow'     => false,
						'border_style'   => false,
					),
					'hover_fields'  => false,
				),
			),

			array(
				'name'     => 'site_content_styling',
				'type'     => 'section',
				'panel'    => 'styling_panel',
				'priority' => 20,
				'title'    => __( 'Site Content', 'customify' ),
			),

			array(
				'name'       => 'site_content_styling',
				'type'       => 'styling',
				'section'    => 'background',
				'title'      => __( 'Content Area Background', 'customify' ),
				'selector'   => array(
					'normal' => '.site-content .content-area',
				),
				// Default `bg_color` removed so auto_css does not emit a
				// `background-color: #FFFFFF` rule when user has not picked
				// a colour. The slot-bound rule in `_layouts.scss` then
				// paints from the active palette. User-picked colours still
				// win — auto_css emits their value as before.
				'css_format' => 'styling', // styling.
				'fields'     => array(
					'normal_fields' => array(
						'text_color'     => false,
						'link_color'     => false,
						'padding'        => false,
						'margin'         => false,
						'border_heading' => false,
						'border_width'   => false,
						'border_color'   => false,
						'border_radius'  => false,
						'box_shadow'     => false,
						'border_style'   => false,
					),
					'hover_fields'  => false,
				),
			),

			array(
				'name'       => 'content_background',
				'type'       => 'styling',
				'section'    => 'background',
				'title'      => __( 'Site Content Background', 'customify' ),
				'selector'   => array(
					'normal' => '.site-content',
				),
				'css_format' => 'styling', // styling.
				'fields'     => array(
					'normal_fields' => array(
						'text_color'     => false,
						'link_color'     => false,
						'padding'        => false,
						'margin'         => false,
						'border_heading' => false,
						'border_width'   => false,
						'border_color'   => false,
						'border_radius'  => false,
						'box_shadow'     => false,
						'border_style'   => false,
					),
					'hover_fields'  => false,
				),
			),

		);

		return array_merge( $configs, $config );

	}

}

new Customify_Advanced_Styling_Background();
