<?php
/**
 * Default template fallback for Well This Is Sports child theme.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<div class="row">
		<div class="col-12">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="entry-content"><?php the_excerpt(); ?></div>
					</article>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No content found.', 'wellthiissports-child' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer();
