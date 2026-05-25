<?php

class Customify_Builder_Footer_Templates {
	public $id = 'footer_templates';

	function customize() {
		$section = 'footer_templates';
		$prefix  = 'footer_templates_';

		return array(
			array(
				'name'     => $section,
				'type'     => 'section',
				'panel'    => 'footer_settings',
				'priority' => 0,
				'title'    => __( 'Templates', 'customify' ),
			),

			array(
				'name'           => $prefix . 'save',
				'type'           => 'custom_html',
				'section'        => $section,
				'theme_supports' => '',
				'title'          => '',
				'description'    => '<div id="customify-footer-templates-mount" class="customify-templates-mount" data-builder-id="footer" data-control-id="' . esc_attr( $prefix . 'save' ) . '"></div>',
			),
		);
	}
}

Customify_Customize_Layout_Builder()->register_item( 'footer', 'Customify_Builder_Footer_Templates' );
