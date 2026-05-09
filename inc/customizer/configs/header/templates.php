<?php

class Customify_Builder_Header_Templates {
	public $id = 'header_templates';

	function customize() {
		$section = 'header_templates';
		$prefix  = 'header_templates_';

		return array(
			array(
				'name'     => $section,
				'type'     => 'section',
				'panel'    => 'header_settings',
				'priority' => 299,
				'title'    => __( 'Templates', 'customify' ),
			),

			array(
				'name'           => $prefix . 'save',
				'type'           => 'custom_html',
				'section'        => $section,
				'theme_supports' => '',
				'title'          => '',
				'description'    => '<div id="customify-header-templates-mount" class="customify-templates-mount" data-builder-id="header" data-control-id="' . esc_attr( $prefix . 'save' ) . '"></div>',
			),
		);
	}
}

Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Header_Templates() );
