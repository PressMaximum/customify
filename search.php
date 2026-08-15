<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package customify
 */

get_header(); ?>
	<div class="content-inner">
		<?php
		do_action( 'customify/content/before' );
		customify_blog_posts_heading();
		?>
		<div class="cfy-search-results">
			<?php
			do_action( 'customify/search/before_results' );
			customify_search_tabs();
			customify_search_results_layout();
			do_action( 'customify/search/after_results' );
			?>
		</div><!-- /.cfy-search-results -->
		<?php
		do_action( 'customify/content/after' );
		?>
	</div><!-- #.content-inner -->
<?php
get_footer();
