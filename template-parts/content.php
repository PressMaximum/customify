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
    <?php if( customify_is_post_title_display() ) { ?>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>
	</header><!-- .entry-header -->
    <?php }

    if ( 'post' === get_post_type() ) :
        Customify_Blog_Builder()->post_meta( $post, array(
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
        ) );

    endif;
    ?>

	<div class="entry-content">
		<?php
			the_content( sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'customify' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'customify' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php customify_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
