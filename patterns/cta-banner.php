<?php
/**
 * Title: Call to Action Banner
 * Slug: customify/cta-banner
 * Categories: customify, call-to-action
 * Description: A centered call-to-action banner with heading and button.
 * Keywords: cta, banner, call to action, conversion
 * Viewport Width: 1280
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}},"color":{"background":"#235787"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#235787;padding-top:4rem;padding-bottom:4rem">

	<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#ffffff"}}} -->
	<h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff"><?php echo esc_html_x( 'Ready to Get Started?', 'Pattern placeholder', 'customify' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#ffffff"}}} -->
	<p class="has-text-align-center has-text-color" style="color:#ffffff"><?php echo esc_html_x( 'Join thousands of satisfied customers. No credit card required.', 'Pattern placeholder', 'customify' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"style":{"color":{"background":"#ffffff","text":"#235787"}}} -->
		<div class="wp-block-button">
			<a class="wp-block-button__link has-text-color has-background wp-element-button" style="color:#235787;background-color:#ffffff"><?php echo esc_html_x( 'Start Free Trial', 'Pattern placeholder', 'customify' ); ?></a>
		</div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
