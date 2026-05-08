<?php
/**
 * archive.php
 * Sport / league archives with ledger summary and matchup grid.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ledger     = get_option( 'wtis_ledger', [] );
$ledger_row = null;
$term       = get_queried_object();

if ( is_category() && $term instanceof WP_Term ) {
	if ( isset( $ledger[ $term->name ] ) ) {
		$ledger_row = $ledger[ $term->name ];
	} elseif ( isset( $ledger[ $term->slug ] ) ) {
		$ledger_row = $ledger[ $term->slug ];
	}
}

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper" id="page-wrapper">
	<div class="container">

		<header class="wtis-archive-header">
			<h1 class="wtis-archive-header__title">
				<?php
				if ( is_category() ) {
					single_cat_title();
				} elseif ( is_tag() ) {
					single_tag_title();
				} else {
					esc_html_e( 'Archive', 'wellthiissports-child' );
				}
				?>
			</h1>

			<?php if ( $ledger_row ) : ?>
				<?php
				$total    = isset( $ledger_row['total'] ) ? (int) $ledger_row['total'] : 0;
				$correct  = isset( $ledger_row['correct'] ) ? (int) $ledger_row['correct'] : 0;
				$wrong    = max( 0, $total - $correct );
				$accuracy = isset( $ledger_row['accuracy'] ) ? (float) $ledger_row['accuracy'] : 0.0;
				$tone     = $accuracy >= 55.0 ? 'high' : ( $accuracy >= 45.0 ? 'mid' : 'low' );
				?>
			<div class="wtis-ledger-block">
				<span class="wtis-ledger-block__label"><?php esc_html_e( 'Ledger on this sport', 'wellthiissports-child' ); ?></span>
				<span class="wtis-ledger-block__stat">
					<span class="wtis-ledger-block__stat-w"><?php echo esc_html( (string) $correct ); ?>W</span>
					<span aria-hidden="true"> — </span>
					<span class="wtis-ledger-block__stat-l"><?php echo esc_html( (string) $wrong ); ?>L</span>
				</span>
				<span class="wtis-ledger-block__accuracy wtis-ledger-block__accuracy--<?php echo esc_attr( $tone ); ?>">
					<?php echo esc_html( number_format_i18n( $accuracy, 1 ) ); ?>% <?php esc_html_e( 'accuracy', 'wellthiissports-child' ); ?>
				</span>
			</div>
			<?php endif; ?>
		</header>

		<main id="content" class="wtis-archive-grid">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					$post_id    = get_the_ID();
					$team_home  = get_post_meta( $post_id, 'wtis_team_home', true );
					$team_away  = get_post_meta( $post_id, 'wtis_team_away', true );
					$sport_meta = get_post_meta( $post_id, 'wtis_sport', true );
					$winner     = get_post_meta( $post_id, 'wtis_prediction_winner', true );
					$confidence = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
					$feat_img   = get_the_post_thumbnail_url( $post_id, 'wtis-card' );
					?>
			<article class="wtis-archive-card">
				<a href="<?php the_permalink(); ?>" class="wtis-archive-card__link">
					<?php if ( $feat_img ) : ?>
					<img class="wtis-archive-card__img"
						src="<?php echo esc_url( $feat_img ); ?>"
						alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
						loading="lazy"
						width="640"
						height="360">
					<?php endif; ?>
					<div class="wtis-archive-card__body">
						<?php if ( $sport_meta ) : ?>
						<div class="wtis-archive-card__sport"><?php echo esc_html( $sport_meta ); ?></div>
						<?php endif; ?>
						<div class="wtis-archive-card__teams">
							<?php echo esc_html( $team_home ?: get_the_title() ); ?>
							<?php if ( $team_away ) : ?>
							<?php esc_html_e( ' vs ', 'wellthiissports-child' ); ?><?php echo esc_html( $team_away ); ?>
							<?php endif; ?>
						</div>
						<?php if ( $winner ) : ?>
						<div class="wtis-archive-card__pick">
							<span class="wtis-archive-card__pick-winner"><?php echo esc_html( $winner ); ?></span>
							<?php if ( $confidence ) : ?>
							<span class="wtis-archive-card__confidence"><?php echo esc_html( (string) $confidence ); ?>%</span>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				</a>
			</article>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
			<p><?php esc_html_e( 'No matchups found.', 'wellthiissports-child' ); ?></p>
			<?php endif; ?>
		</main>

	</div>
</div>

<?php get_footer(); ?>
