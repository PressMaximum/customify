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

		/**
		 * Fires immediately before the theme's search chrome opens.
		 *
		 * Together with `customify/search/region_end` this brackets the WHOLE
		 * search experience the theme renders: heading, results form, tab bar,
		 * listing and pagination. The pair exists so a page builder that owns
		 * this view can replace all of it in one go instead of stacking its own
		 * design under the theme's chrome - see
		 * customify_search_template_region_swap() in
		 * inc/search/functions-search.php.
		 *
		 * Inert on its own: with nothing hooked, both actions render nothing.
		 *
		 * @since 0.5.0
		 */
		do_action( 'customify/search/region_start' );

		customify_blog_posts_heading();
		?>
		<div class="cfy-search-results">
			<?php
			customify_search_results_form();
			do_action( 'customify/search/before_results' );
			customify_search_tabs();
			customify_search_results_layout();
			do_action( 'customify/search/after_results' );
			?>
		</div><!-- /.cfy-search-results -->
		<?php
		/**
		 * Fires immediately after the theme's search chrome closes.
		 *
		 * Closing half of the region bracket opened by
		 * `customify/search/region_start` - see that action for the contract.
		 *
		 * @since 0.5.0
		 */
		do_action( 'customify/search/region_end' );

		do_action( 'customify/content/after' );
		?>
	</div><!-- #.content-inner -->
<?php
get_footer();
