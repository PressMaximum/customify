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
            </main><!-- #main -->
            <?php do_action( 'customify_sidebars' ); ?>
        </div><!-- #.customify-grid -->
    </div><!-- #.customify-container -->
</div><!-- #content -->
<?php
do_action( 'customify/site-end/before' );
if ( ! customify_is_e_theme_location( 'footer' ) ) {
    /**
     * Site end
     *
     * @hooked customify_customize_render_footer - 10
     *
     * @see customify_customize_render_footer
     */
    do_action( 'customify/site-end' );
}
do_action( 'customify/site-end/after' );

?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
