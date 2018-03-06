<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

?>
	</div><!-- #content -->

<?php
customify_customize_render_footer();
do_action( 'customify/site-end' );
?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
