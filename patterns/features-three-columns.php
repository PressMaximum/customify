<?php
/**
 * Title: Features - Three Columns
 * Slug: customify/features-three-columns
 * Categories: customify, columns
 * Description: Three-column feature section with icon, heading, and description.
 * Keywords: features, columns, grid, services
 * Viewport Width: 1280
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:4rem;padding-bottom:4rem">

	<!-- wp:heading {"textAlign":"center"} -->
	<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'Our Features', 'Pattern placeholder', 'customify' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><?php echo esc_html_x( 'Everything you need to build a great website.', 'Pattern placeholder', 'customify' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"textAlign":"center"} -->
			<h3 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'Feature One', 'Pattern placeholder', 'customify' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"align":"center"} -->
			<p class="has-text-align-center"><?php echo esc_html_x( 'Describe your first key feature or benefit here. Keep it short and focused.', 'Pattern placeholder', 'customify' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"textAlign":"center"} -->
			<h3 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'Feature Two', 'Pattern placeholder', 'customify' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"align":"center"} -->
			<p class="has-text-align-center"><?php echo esc_html_x( 'Describe your second key feature or benefit here. Keep it short and focused.', 'Pattern placeholder', 'customify' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"textAlign":"center"} -->
			<h3 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'Feature Three', 'Pattern placeholder', 'customify' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"align":"center"} -->
			<p class="has-text-align-center"><?php echo esc_html_x( 'Describe your third key feature or benefit here. Keep it short and focused.', 'Pattern placeholder', 'customify' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
