<?php
/**
 * Title: Testimonial
 * Slug: customify/testimonial
 * Categories: customify, testimonials
 * Description: A single centered testimonial quote with author name.
 * Keywords: testimonial, quote, review, social proof
 * Viewport Width: 1280
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:4rem;padding-bottom:4rem">

	<!-- wp:pullquote {"align":"wide"} -->
	<figure class="wp-block-pullquote alignwide">
		<blockquote>
			<p><?php echo esc_html_x( 'This product completely changed the way we work. We saved countless hours and our team is more productive than ever. I highly recommend it to anyone looking to level up their workflow.', 'Pattern placeholder', 'customify' ); ?></p>
			<cite><?php echo esc_html_x( 'Jane Doe, CEO at Example Co.', 'Pattern placeholder', 'customify' ); ?></cite>
		</blockquote>
	</figure>
	<!-- /wp:pullquote -->

</div>
<!-- /wp:group -->
