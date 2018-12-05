<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

get_header(); ?>
	<div class="content-inner">
		<?php
		do_action( 'customify/content/before' );
		if ( is_singular() ) {
			if ( ! customify_is_e_theme_location( 'single' ) ) {
				customify_blog_posts_heading();
				customify_blog_posts();
			}
		} elseif ( is_archive() || is_home() || is_search() ) {
			if ( ! customify_is_e_theme_location( 'archive' ) ) {
				customify_blog_posts_heading();
				customify_blog_posts();
			}
		} else {
			if ( ! customify_is_e_theme_location( 'single' ) ) {
				get_template_part( 'template-parts/404' );
			}
		}
		do_action( 'customify/content/after' );
		?>
	</div><!-- #.content-inner -->
<?php
get_footer();
