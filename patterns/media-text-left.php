<?php
/**
 * Title: Media & Text - Image Left
 * Slug: customify/media-text-left
 * Categories: customify, media
 * Description: Side-by-side layout with image on the left and text on the right.
 * Keywords: media, text, image, two columns
 * Viewport Width: 1280
 */
?>
<!-- wp:media-text {"align":"wide","mediaPosition":"left","verticalAlignment":"center"} -->
<div class="wp-block-media-text alignwide is-stacked-on-mobile is-vertically-aligned-center">
	<figure class="wp-block-media-text__media">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/build/images/placeholder.png' ); ?>" alt="<?php esc_attr_e( 'Placeholder image', 'customify' ); ?>" />
	</figure>
	<div class="wp-block-media-text__content">
		<!-- wp:heading {"level":2} -->
		<h2 class="wp-block-heading"><?php echo esc_html_x( 'Your Story Starts Here', 'Pattern placeholder', 'customify' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'Use this space to tell your story, explain your product, or highlight what makes you unique. Great content creates connections.', 'Pattern placeholder', 'customify' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button -->
			<div class="wp-block-button">
				<a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'Read More', 'Pattern placeholder', 'customify' ); ?></a>
			</div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
</div>
<!-- /wp:media-text -->
