<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package customify
 */

global $post;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
<?php if ( customify_is_post_title_display() ) { ?>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>
	</header><!-- .entry-header -->
	<?php
}

if ( 'post' === get_post_type() ) :
	Customify_Post_Entry()->post_meta(
		$post,
		array(
			array(
				'_key' => 'author',
			),
			array(
				'_key' => 'date',
			),
			array(
				'_key' => 'categories',
			),
			array(
				'_key' => 'comment',
			),
		)
	);

		endif;
?>

	<div class="entry-content">
		<?php
		the_content(
			// Translators: %s: Name of current post. Only visible to screen readers.
			sprintf( esc_html__( 'Continue reading %s', 'customify' ), '<span class="screen-reader-text">' . the_title( '', '', false ) . '</span>' )
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'customify' ),
				'after'  => '</div>',
			)
		);
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php customify_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
