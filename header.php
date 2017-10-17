<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _beacon
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', '_beacon' ); ?></a>

	<header id="masthead" class="site-header">

            <div class="header-top">
                <div class="_beacon-container">
                    header top
                </div> <!-- #._beacon-container -->
            </div><!-- #.header-top -->

            <div class="header-main">
                <div class="_beacon-container">
                    <div class="site-branding">
                        <?php
                        the_custom_logo();
                        if ( is_front_page() && is_home() ) : ?>
                            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                        <?php else : ?>
                            <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                        <?php
                        endif;

                        $description = get_bloginfo( 'description', 'display' );
                        if ( $description || is_customize_preview() ) : ?>
                            <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                        <?php
                        endif; ?>
                    </div><!-- .site-branding -->

                    <nav id="site-navigation" class="main-navigation">
                        <?php
                            wp_nav_menu( array(
                                'theme_location' => 'menu-1',
                                'menu_id'        => 'primary-menu',
                            ) );
                        ?>
                    </nav><!-- #site-navigation -->
                </div> <!-- #._beacon-container -->
            </div><!-- #.header-main -->

            <div class="header-bottom">
                <div class="_beacon-container">
                    header bottom
                </div> <!-- #._beacon-container -->
            </div><!-- #.header-bottom -->

	</header><!-- #masthead -->

	<div id="content" class="site-content">
        <div class="_beacon-container">
            <?php
            $fields = apply_filters( '_beacon/customizer/config', array() );
            foreach ( $fields as $f ) {
                if (  $f['type'] != 'panel' && $f['type'] != 'section' ) {
                    var_dump( $f['name'] );
                    var_dump( _Beacon_Customizer()->get_setting( $f['name'] ) );
                }
            }
            ?>
