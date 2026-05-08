<?php
/**
 * front-page.php
 * Homepage: ledger widget, matchup grid by sport section.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$matchup_args = [
	'posts_per_page' => 60,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'post_status'    => 'publish',
	'meta_query'     => [
		[
			'key'     => 'wtis_matchup_date',
			'compare' => 'EXISTS',
		],
	],
];
$matchup_query = new WP_Query( $matchup_args );

$by_sport = [];
if ( $matchup_query->have_posts() ) {
	while ( $matchup_query->have_posts() ) {
		$matchup_query->the_post();
		$sport = get_post_meta( get_the_ID(), 'wtis_sport', true );
		$key   = $sport ? $sport : __( 'Matchups', 'wellthiissports-child' );
		if ( ! isset( $by_sport[ $key ] ) ) {
			$by_sport[ $key ] = [];
		}
		$by_sport[ $key ][] = get_the_ID();
	}
	wp_reset_postdata();
}

$ledger = get_option( 'wtis_ledger', [] );

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper" id="page-wrapper">
	<div class="container">

		<?php if ( ! empty( $ledger ) ) : ?>
		<section class="wtis-ledger-widget" aria-labelledby="wtis-ledger-widget-title">
			<h2 id="wtis-ledger-widget-title" class="wtis-ledger-widget__title"><?php esc_html_e( 'Accuracy ledger', 'wellthiissports-child' ); ?></h2>
			<div class="wtis-ledger-widget__grid">
				<?php foreach ( $ledger as $sport_name => $data ) : ?>
					<?php
					$total    = isset( $data['total'] ) ? (int) $data['total'] : 0;
					$correct  = isset( $data['correct'] ) ? (int) $data['correct'] : 0;
					$wrong    = max( 0, $total - $correct );
					$accuracy = isset( $data['accuracy'] ) ? (float) $data['accuracy'] : 0.0;
					$streak   = isset( $data['streak'] ) ? (int) $data['streak'] : 0;
					$tone     = $accuracy >= 55.0 ? 'high' : ( $accuracy >= 45.0 ? 'mid' : 'low' );
					?>
				<div class="wtis-ledger-widget__item">
					<div class="wtis-ledger-widget__sport"><?php echo esc_html( $sport_name ); ?></div>
					<div class="wtis-ledger-widget__record" aria-label="<?php esc_attr_e( 'Wins and losses', 'wellthiissports-child' ); ?>">
						<span class="wtis-ledger-widget__record-w"><?php echo esc_html( (string) $correct ); ?>W</span>
						<span class="wtis-ledger-widget__record-sep" aria-hidden="true"> — </span>
						<span class="wtis-ledger-widget__record-l"><?php echo esc_html( (string) $wrong ); ?>L</span>
					</div>
					<div class="wtis-ledger-widget__accuracy wtis-ledger-widget__accuracy--<?php echo esc_attr( $tone ); ?>">
						<?php echo esc_html( number_format_i18n( $accuracy, 1 ) ); ?>%
					</div>
					<?php if ( $streak !== 0 ) : ?>
					<div class="wtis-ledger-widget__streak">
						<?php
						printf(
							/* translators: %d: streak count (signed) */
							esc_html__( 'Streak: %+d', 'wellthiissports-child' ),
							(int) $streak
						);
						?>
					</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( ! empty( $by_sport ) ) : ?>
		<main id="content">
		<?php
		$section_i = 0;
		foreach ( $by_sport as $sport_label => $post_ids ) :
			$section_i++;
			$mod_class = ( 0 === $section_i % 2 ) ? ' wtis-sport-section--alt' : '';
			?>
		<section class="wtis-sport-section<?php echo esc_attr( $mod_class ); ?>" aria-labelledby="<?php echo esc_attr( 'wtis-sport-' . sanitize_title( $sport_label ) ); ?>">
			<header class="wtis-sport-section__header">
				<h2 id="<?php echo esc_attr( 'wtis-sport-' . sanitize_title( $sport_label ) ); ?>" class="wtis-sport-section__title"><?php echo esc_html( $sport_label ); ?></h2>
				<span class="wtis-sport-section__meta">
					<?php
					printf(
						/* translators: %d: number of matchup cards */
						esc_html( _n( '%d matchup', '%d matchups', count( $post_ids ), 'wellthiissports-child' ) ),
						(int) count( $post_ids )
					);
					?>
				</span>
			</header>
			<div class="wtis-sport-section__body">
				<div class="wtis-matchup-grid">
					<?php
					foreach ( $post_ids as $post_id ) :
						$post_id        = (int) $post_id;
						$team_home      = get_post_meta( $post_id, 'wtis_team_home', true );
						$team_away      = get_post_meta( $post_id, 'wtis_team_away', true );
						$sport          = get_post_meta( $post_id, 'wtis_sport', true );
						$winner         = get_post_meta( $post_id, 'wtis_prediction_winner', true );
						$confidence     = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
						$grade          = (int) get_post_meta( $post_id, 'wtis_prediction_grade', true );
						$matchup_date   = get_post_meta( $post_id, 'wtis_matchup_date', true );
						$pred_correct   = get_post_meta( $post_id, 'wtis_prediction_correct', true );
						$actual_result  = get_post_meta( $post_id, 'wtis_actual_result', true );
						$feat_img       = get_the_post_thumbnail_url( $post_id, 'wtis-card' );
						$permalink      = get_permalink( $post_id );
						?>
					<article class="wtis-matchup-card <?php echo $grade >= 8 ? 'wtis-matchup-card--featured' : ''; ?>">
						<a href="<?php echo esc_url( $permalink ); ?>" class="wtis-matchup-card__link">
							<?php if ( $grade >= 8 ) : ?>
							<span class="wtis-matchup-card__badge"><?php esc_html_e( 'Card pick', 'wellthiissports-child' ); ?></span>
							<?php elseif ( $winner ) : ?>
							<span class="wtis-matchup-card__badge"><?php esc_html_e( 'Prediction', 'wellthiissports-child' ); ?></span>
							<?php endif; ?>

							<?php if ( $feat_img ) : ?>
							<div class="wtis-matchup-card__img-wrap">
								<img class="wtis-matchup-card__img"
									src="<?php echo esc_url( $feat_img ); ?>"
									alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
									loading="lazy"
									width="640"
									height="360">
							</div>
							<?php endif; ?>

							<div class="wtis-matchup-card__body">
								<div class="wtis-matchup-card__meta">
									<?php if ( $sport ) : ?>
									<span class="wtis-matchup-card__sport"><?php echo esc_html( $sport ); ?></span>
									<?php endif; ?>
									<?php if ( $matchup_date ) : ?>
									<span class="wtis-matchup-card__date">
										<?php echo esc_html( date_i18n( 'M j', strtotime( $matchup_date ) ) ); ?>
									</span>
									<?php endif; ?>
								</div>

								<div class="wtis-matchup-card__teams">
									<span class="wtis-matchup-card__team"><?php echo esc_html( $team_home ?: get_the_title( $post_id ) ); ?></span>
									<span class="wtis-matchup-card__vs"><?php esc_html_e( 'vs', 'wellthiissports-child' ); ?></span>
									<span class="wtis-matchup-card__team"><?php echo esc_html( $team_away ); ?></span>
								</div>

								<?php if ( $winner ) : ?>
								<div class="wtis-matchup-card__pick">
									<span class="wtis-matchup-card__pick-label"><?php esc_html_e( 'The Pick', 'wellthiissports-child' ); ?></span>
									<span class="wtis-matchup-card__pick-team"><?php echo esc_html( $winner ); ?></span>
								</div>
								<?php endif; ?>

								<?php if ( $confidence ) : ?>
								<div class="wtis-matchup-card__confidence-preview">
									<span class="wtis-matchup-card__confidence-num"><?php echo esc_html( (string) $confidence ); ?></span>
									<span class="wtis-matchup-card__confidence-suffix"><?php esc_html_e( 'confidence', 'wellthiissports-child' ); ?></span>
								</div>
								<div class="wtis-confidence-meter" style="--confidence: <?php echo esc_attr( (string) $confidence ); ?>">
									<div class="wtis-confidence-meter__bar" role="presentation">
										<div class="wtis-confidence-meter__fill"></div>
									</div>
									<span class="wtis-confidence-meter__label"><?php echo esc_html( (string) $confidence ); ?>% <?php esc_html_e( 'model confidence', 'wellthiissports-child' ); ?></span>
								</div>
								<?php endif; ?>

								<?php if ( '' !== $pred_correct && $actual_result ) : ?>
								<div class="wtis-matchup-card__result <?php echo $pred_correct ? 'wtis-matchup-card__result--correct' : 'wtis-matchup-card__result--incorrect'; ?>">
									<?php
									echo $pred_correct ? esc_html__( 'Correct', 'wellthiissports-child' ) : esc_html__( 'Incorrect', 'wellthiissports-child' );
									echo ' — ';
									echo esc_html( $actual_result );
									?>
								</div>
								<?php endif; ?>
							</div>
						</a>
					</article>
						<?php endforeach; ?>
				</div>
			</div>
		</section>
			<?php
		endforeach;
		?>
		</main>

		<?php endif; ?>

		<?php if ( empty( $by_sport ) ) : ?>
		<main id="content" class="wtis-matchup-grid">
			<p class="wtis-no-matchups"><?php esc_html_e( 'No matchups yet. Check back soon.', 'wellthiissports-child' ); ?></p>
		</main>
		<?php endif; ?>

	</div>
</div>

<?php get_footer(); ?>
