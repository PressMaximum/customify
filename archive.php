<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

get_header(); ?>
<div class="content-inner">
	<?php
	do_action( 'customify/content/before' );
	if ( ! customify_is_e_theme_location( 'archive' ) ) {
		customify_blog_posts_heading();
		customify_blog_posts();
	}
	do_action( 'customify/content/after' );
	?>
</div><!-- #.content-inner -->
<?php
get_footer();
