<?php
/**
 * Register custom block styles.
 *
 * @since 0.4.14
 */
if ( ! function_exists( 'customify_register_block_styles' ) ) {
	function customify_register_block_styles() {

		// Button: Ghost (outline with no fill).
		register_block_style(
			'core/button',
			array(
				'name'  => 'ghost',
				'label' => __( 'Ghost', 'customify' ),
			)
		);

		// Image: Rounded corners.
		register_block_style(
			'core/image',
			array(
				'name'  => 'rounded',
				'label' => __( 'Rounded', 'customify' ),
			)
		);

		// Image: Shadow.
		register_block_style(
			'core/image',
			array(
				'name'  => 'shadow',
				'label' => __( 'Shadow', 'customify' ),
			)
		);

		// Quote: Bordered left accent.
		register_block_style(
			'core/quote',
			array(
				'name'  => 'accent',
				'label' => __( 'Accent Border', 'customify' ),
			)
		);

		// Separator: Thick.
		register_block_style(
			'core/separator',
			array(
				'name'  => 'thick',
				'label' => __( 'Thick', 'customify' ),
			)
		);

		// Group: Card (white background, border, padding).
		register_block_style(
			'core/group',
			array(
				'name'  => 'card',
				'label' => __( 'Card', 'customify' ),
			)
		);

		// Columns: No gap.
		register_block_style(
			'core/columns',
			array(
				'name'  => 'no-gap',
				'label' => __( 'No Gap', 'customify' ),
			)
		);
	}
}
add_action( 'init', 'customify_register_block_styles' );
