<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

?>

<section class="no-results not-found">

	<div class="page-content widget-area">
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) :
			?>

			<p>
			<?php
				printf(
					wp_kses(
						/* translators: 1: link to WP admin new post page. */
						__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'customify' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
			?>
				</p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'customify' ); ?></p>
			<?php
			echo '<div class="widget">';
			get_search_form();
			echo '</div>';

		else :
			?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'customify' ); ?></p>
			<?php

			echo '<div class="widget">';
			get_search_form();
			echo '</div>';

		endif;
		?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
