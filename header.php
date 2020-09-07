<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package customify
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10.0, user-scalable=yes">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}
?>
<div id="page" <?php customify_site_classes(); ?>>
	<a class="skip-link screen-reader-text" href="#site-content"><?php esc_html_e( 'Skip to content', 'customify' ); ?></a>
	<?php
	do_action( 'customify/site-start/before' );
	if ( ! customify_is_e_theme_location( 'header' ) ) {
		/**
		 * Site start
		 *
		 * Hooked
		 *
		 * @see customify_customize_render_header - 10
		 * @see Customify_Page_Header::render - 35
		 */
		do_action( 'customify/site-start' );
	}
	do_action( 'customify/site-start/after' );

	/**
	 * Hook before main content
	 *
	 * @since 0.2.1
	 */
	do_action( 'customify/before-site-content' );
	?>
	<div id="site-content" <?php customify_site_content_class(); ?>>
		<div <?php customify_site_content_container_class(); ?>>
			<div <?php customify_site_content_grid_class(); ?>>
				<main id="main" <?php customify_main_content_class(); ?>>
					<?php do_action( 'customify/main/before' ); ?>
