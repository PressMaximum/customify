<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _beacon
 */

?>
        </div><!-- ._beacon-container -->
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
        <div class="_beacon-container">
            <div class="site-info">
                <a href="<?php echo esc_url( __( 'https://wordpress.org/', '_beacon' ) ); ?>"><?php
                    /* translators: %s: CMS name, i.e. WordPress. */
                    printf( esc_html__( 'Proudly powered by %s', '_beacon' ), 'WordPress' );
                ?></a>
                <span class="sep"> | </span>
                <?php
                    /* translators: 1: Theme name, 2: Theme author. */
                    printf( esc_html__( 'Theme: %1$s by %2$s.', '_beacon' ), '_beacon', '<a href="https://www.famethemes.com">FameThemes</a>' );
                ?>
            </div><!-- .site-info -->
        </div><!-- #._beacon_container -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
