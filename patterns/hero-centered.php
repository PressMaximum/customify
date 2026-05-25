<?php
/**
 * Title: Hero - Centered Text
 * Slug: customify/hero-centered
 * Categories: customify, featured
 * Description: Full-width hero section with centered heading, description, and CTA button.
 * Keywords: hero, banner, header, cta
 * Viewport Width: 1280
 */
?>
<!-- wp:cover {"dimRatio":50,"minHeight":500,"minHeightUnit":"px","isDark":false,"align":"full","className":"customify-hero"} -->
<div class="wp-block-cover alignfull is-light customify-hero" style="min-height:500px">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span>
	<div class="wp-block-cover__inner-container">
		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
			<!-- wp:heading {"textAlign":"center","level":1} -->
			<h1 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'Welcome to Our Site', 'Pattern placeholder', 'customify' ); ?></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"align":"center"} -->
			<p class="has-text-align-center"><?php echo esc_html_x( 'A short description of your business or product. Write something compelling that makes visitors want to learn more.', 'Pattern placeholder', 'customify' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"is-style-fill"} -->
				<div class="wp-block-button is-style-fill">
					<a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'Get Started', 'Pattern placeholder', 'customify' ); ?></a>
				</div>
				<!-- /wp:button -->

				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline">
					<a class="wp-block-button__link wp-element-button"><?php echo esc_html_x( 'Learn More', 'Pattern placeholder', 'customify' ); ?></a>
				</div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
</div>
<!-- /wp:cover -->
