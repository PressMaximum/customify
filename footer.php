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
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
        <div class="footer-widgets-wrap">
            <div class="_beacon-container">
                <div class="footer-widgets footer-widget-area widget-area _beacon-grid">
                    <?php if ( is_active_sidebar( 'footer-1' ) ) { ?>
                    <div class="_beacon-col-3_md-6_xs-12">
                        <?php dynamic_sidebar( 'footer-1' ); ?>
                    </div>
                    <?php } ?>

                    <?php if ( is_active_sidebar( 'footer-2' ) ) { ?>
                        <div class="_beacon-col-3_md-6_xs-12">
                            <?php dynamic_sidebar( 'footer-2' ); ?>
                        </div>
                    <?php } ?>

                    <?php if ( is_active_sidebar( 'footer-3' ) ) { ?>
                        <div class="_beacon-col-3_md-6_xs-12">
                            <?php dynamic_sidebar( 'footer-3' ); ?>
                        </div>
                    <?php } ?>

                    <?php if ( is_active_sidebar( 'footer-4' ) ) { ?>
                        <div class="_beacon-col-3_md-6_xs-12">
                            <?php dynamic_sidebar( 'footer-4' ); ?>
                        </div>
                    <?php } ?>

                </div><!-- #.footer-widgets -->

                <div class="site-info">
                    <div class="site-copyright">
                        <?php
                        $copyright = sprintf( '<span class="copyright">&copy; %1$s</span> %2$s &sdot; %3$s <a href="%4$s" target="_blank" itemprop="url">%5$s</a>.',
                            date( 'Y' ),
                            get_bloginfo( 'name' ),
	                        __( 'Powered by','_beacon' ),
                            esc_url( 'https://www.famethemes.com/beacon' ),
                            __( 'Beacon','_beacon' )
                        );
                        echo $copyright;
                        ?>
                    </div>
                </div><!-- .site-info -->
            </div><!-- #._beacon_container -->
        </div><!-- #.footer-widgets-wrap -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
