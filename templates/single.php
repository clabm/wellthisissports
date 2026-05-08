<?php
/**
 * single.php
 * Prediction detail: meta, teams, confidence, pick, analysis, takeaways.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$post_id           = get_the_ID();
$team_home         = get_post_meta( $post_id, 'wtis_team_home', true );
$team_away         = get_post_meta( $post_id, 'wtis_team_away', true );
$matchup_title     = get_post_meta( $post_id, 'wtis_matchup_title', true );
$sport             = get_post_meta( $post_id, 'wtis_sport', true );
$league            = get_post_meta( $post_id, 'wtis_league', true );
$matchup_date      = get_post_meta( $post_id, 'wtis_matchup_date', true );
$winner            = get_post_meta( $post_id, 'wtis_prediction_winner', true );
$confidence        = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
$analysis          = get_post_meta( $post_id, 'wtis_analysis', true );
$factors_for       = get_post_meta( $post_id, 'wtis_factors_for', true );
$factors_against   = get_post_meta( $post_id, 'wtis_factors_against', true );
$actual_result     = get_post_meta( $post_id, 'wtis_actual_result', true );
$pred_correct      = get_post_meta( $post_id, 'wtis_prediction_correct', true );
$article_stage     = get_post_meta( $post_id, 'wtis_article_stage', true );
$feat_img          = get_the_post_thumbnail_url( $post_id, 'wtis-hero' );
$prediction_grade  = (int) get_post_meta( $post_id, 'wtis_prediction_grade', true );
$ai_generated      = get_post_meta( $post_id, 'wtis_ai_generated', true );
$ingested_at       = get_post_meta( $post_id, 'wtis_ingested_at', true );
$image_brief       = get_post_meta( $post_id, 'wtis_image_brief_scene', true );
$nobody_saying     = get_post_meta( $post_id, 'wtis_what_nobody_saying', true );

$factors_for_list     = $factors_for ? array_filter( array_map( 'trim', explode( '|', $factors_for ) ) ) : [];
$factors_against_list = $factors_against ? array_filter( array_map( 'trim', explode( '|', $factors_against ) ) ) : [];

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper" id="page-wrapper">
	<div class="container">

		<main id="content" class="wtis-story">

			<?php if ( 'urgent_update' === $article_stage ) : ?>
			<div class="wtis-urgent-badge"><?php esc_html_e( 'Urgent update', 'wellthiissports-child' ); ?></div>
			<?php endif; ?>

			<?php if ( $feat_img ) : ?>
			<div class="wtis-story-img">
				<img src="<?php echo esc_url( $feat_img ); ?>"
					alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
					loading="eager"
					width="1240"
					height="697">
			</div>
			<?php endif; ?>

			<div class="wtis-story__meta">
				<?php if ( $sport ) : ?>
				<span class="wtis-story__sport"><?php echo esc_html( $sport ); ?></span>
				<?php endif; ?>
				<?php if ( $league ) : ?>
				<span class="wtis-story__league"><?php echo esc_html( $league ); ?></span>
				<?php endif; ?>
				<?php if ( $matchup_date ) : ?>
				<span class="wtis-story__date">
					<?php echo esc_html( date_i18n( 'F j, Y', strtotime( $matchup_date ) ) ); ?>
				</span>
				<?php endif; ?>
			</div>

			<?php if ( $matchup_title ) : ?>
			<p class="wtis-story__title-sub"><?php echo esc_html( $matchup_title ); ?></p>
			<?php endif; ?>

			<h1 class="wtis-story__headline">
				<span class="wtis-story__team-home"><?php echo esc_html( $team_home ?: get_the_title() ); ?></span>
				<span class="wtis-story__vs"><?php esc_html_e( 'vs', 'wellthiissports-child' ); ?></span>
				<span class="wtis-story__team-away"><?php echo esc_html( $team_away ); ?></span>
			</h1>

			<?php
			$show_meta_row = $prediction_grade > 0 || $ingested_at
				|| ( $article_stage && 'urgent_update' !== $article_stage )
				|| metadata_exists( 'post', $post_id, 'wtis_ai_generated' );
			?>
			<?php if ( $show_meta_row ) : ?>
			<div class="wtis-story-meta-row">
				<?php if ( $prediction_grade > 0 ) : ?>
				<span class="wtis-story-meta-row__item"><strong><?php esc_html_e( 'Grade', 'wellthiissports-child' ); ?></strong> <?php echo esc_html( (string) $prediction_grade ); ?></span>
				<?php endif; ?>
				<span class="wtis-story-meta-row__item"><strong><?php esc_html_e( 'Source', 'wellthiissports-child' ); ?></strong> <?php echo $ai_generated ? esc_html__( 'AI generated', 'wellthiissports-child' ) : esc_html__( 'Editorial', 'wellthiissports-child' ); ?></span>
				<?php if ( $ingested_at ) : ?>
				<span class="wtis-story-meta-row__item"><strong><?php esc_html_e( 'Ingested', 'wellthiissports-child' ); ?></strong> <?php echo esc_html( $ingested_at ); ?></span>
				<?php endif; ?>
				<?php if ( $article_stage && 'urgent_update' !== $article_stage ) : ?>
				<span class="wtis-story-meta-row__item"><strong><?php esc_html_e( 'Stage', 'wellthiissports-child' ); ?></strong> <?php echo esc_html( $article_stage ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="wtis-team-breakdown">
				<div class="wtis-team-breakdown__col wtis-team-breakdown__col--home">
					<p class="wtis-team-breakdown__heading"><?php esc_html_e( 'Home', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__name"><?php echo esc_html( $team_home ?: '—' ); ?></p>
					<p class="wtis-team-breakdown__label"><?php esc_html_e( 'Strengths', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__text">
						<?php
						if ( $winner && $team_home && 0 === strcasecmp( trim( (string) $winner ), trim( (string) $team_home ) ) && $factors_for_list ) {
							echo esc_html( $factors_for_list[0] );
						} elseif ( $winner && $team_home && 0 !== strcasecmp( trim( (string) $winner ), trim( (string) $team_home ) ) && $factors_against_list ) {
							echo esc_html( $factors_against_list[0] ?? '' );
						} else {
							esc_html_e( 'See key factors for how the home side shapes this matchup.', 'wellthiissports-child' );
						}
						?>
					</p>
					<p class="wtis-team-breakdown__label"><?php esc_html_e( 'Weaknesses', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__text">
						<?php
						if ( $winner && $team_home && 0 === strcasecmp( trim( (string) $winner ), trim( (string) $team_home ) ) && isset( $factors_against_list[0] ) ) {
							echo esc_html( $factors_against_list[0] );
						} elseif ( $winner && $team_home && 0 !== strcasecmp( trim( (string) $winner ), trim( (string) $team_home ) ) && isset( $factors_for_list[0] ) ) {
							echo esc_html( $factors_for_list[0] );
						} else {
							esc_html_e( 'Risk factors for the home side are listed opposite.', 'wellthiissports-child' );
						}
						?>
					</p>
				</div>
				<div class="wtis-team-breakdown__col wtis-team-breakdown__col--away">
					<p class="wtis-team-breakdown__heading"><?php esc_html_e( 'Away', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__name"><?php echo esc_html( $team_away ?: '—' ); ?></p>
					<p class="wtis-team-breakdown__label"><?php esc_html_e( 'Strengths', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__text">
						<?php
						if ( $winner && $team_away && 0 === strcasecmp( trim( (string) $winner ), trim( (string) $team_away ) ) && $factors_for_list ) {
							echo esc_html( $factors_for_list[0] );
						} elseif ( $winner && $team_away && 0 !== strcasecmp( trim( (string) $winner ), trim( (string) $team_away ) ) && $factors_against_list ) {
							echo esc_html( $factors_against_list[0] ?? '' );
						} else {
							esc_html_e( 'See key factors for how the away side shapes this matchup.', 'wellthiissports-child' );
						}
						?>
					</p>
					<p class="wtis-team-breakdown__label"><?php esc_html_e( 'Weaknesses', 'wellthiissports-child' ); ?></p>
					<p class="wtis-team-breakdown__text">
						<?php
						if ( $winner && $team_away && 0 === strcasecmp( trim( (string) $winner ), trim( (string) $team_away ) ) && isset( $factors_against_list[0] ) ) {
							echo esc_html( $factors_against_list[0] );
						} elseif ( $winner && $team_away && 0 !== strcasecmp( trim( (string) $winner ), trim( (string) $team_away ) ) && isset( $factors_for_list[0] ) ) {
							echo esc_html( $factors_for_list[0] );
						} else {
							esc_html_e( 'Risk factors for the away side are listed opposite.', 'wellthiissports-child' );
						}
						?>
					</p>
				</div>
			</div>

			<?php if ( $winner ) : ?>
			<div class="wtis-prediction-block">
				<div class="wtis-prediction-block__pick">
					<span class="wtis-prediction-block__label"><?php esc_html_e( 'The Pick', 'wellthiissports-child' ); ?></span>
					<span class="wtis-prediction-block__winner"><?php echo esc_html( $winner ); ?></span>
				</div>

				<?php if ( $confidence ) : ?>
				<div class="wtis-confidence-meter wtis-confidence-meter--hero" style="--confidence: <?php echo esc_attr( (string) $confidence ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: confidence percentage */ __( 'Confidence %d percent', 'wellthiissports-child' ), $confidence ) ); ?>">
					<div class="wtis-confidence-meter__hero-row">
						<span class="wtis-confidence-meter__value"><?php echo esc_html( (string) $confidence ); ?></span>
						<span class="wtis-confidence-meter__unit"><?php esc_html_e( 'confidence score', 'wellthiissports-child' ); ?></span>
					</div>
					<div class="wtis-confidence-meter__bar" role="presentation">
						<div class="wtis-confidence-meter__fill"></div>
					</div>
					<span class="wtis-confidence-meter__label"><?php echo esc_html( (string) $confidence ); ?>% <?php esc_html_e( 'model confidence', 'wellthiissports-child' ); ?></span>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( '' !== $pred_correct && $actual_result ) : ?>
			<div class="wtis-result-block wtis-result-block--<?php echo $pred_correct ? 'correct' : 'incorrect'; ?>">
				<strong><?php echo $pred_correct ? esc_html__( 'Correct prediction', 'wellthiissports-child' ) : esc_html__( 'Incorrect prediction', 'wellthiissports-child' ); ?></strong>
				<?php esc_html_e( 'Final result:', 'wellthiissports-child' ); ?> <?php echo esc_html( $actual_result ); ?>
			</div>
			<?php endif; ?>

			<?php if ( $analysis ) : ?>
			<div class="wtis-analysis">
				<h2 class="wtis-analysis__heading"><?php esc_html_e( 'The analysis', 'wellthiissports-child' ); ?></h2>
				<div class="wtis-analysis__body">
					<?php echo wp_kses_post( wpautop( $analysis ) ); ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $factors_for_list || $factors_against_list ) : ?>
			<section class="wtis-takeaways" aria-labelledby="wtis-takeaways-heading">
				<h2 id="wtis-takeaways-heading" class="wtis-takeaways__heading"><?php esc_html_e( 'Key factors', 'wellthiissports-child' ); ?></h2>
				<div class="wtis-takeaways__grid">
					<?php if ( $factors_for_list ) : ?>
					<div class="wtis-takeaways__col wtis-takeaways__col--for">
						<h3 class="wtis-takeaways__col-title"><?php echo esc_html( sprintf( /* translators: %s: predicted winner team name */ __( 'Why %s wins', 'wellthiissports-child' ), $winner ? $winner : __( 'the pick', 'wellthiissports-child' ) ) ); ?></h3>
						<ul class="wtis-takeaways__list">
							<?php foreach ( $factors_for_list as $factor ) : ?>
							<li><?php echo esc_html( $factor ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
					<?php if ( $factors_against_list ) : ?>
					<div class="wtis-takeaways__col wtis-takeaways__col--against">
						<h3 class="wtis-takeaways__col-title"><?php esc_html_e( 'Risk factors', 'wellthiissports-child' ); ?></h3>
						<ul class="wtis-takeaways__list">
							<?php foreach ( $factors_against_list as $factor ) : ?>
							<li><?php echo esc_html( $factor ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
			</section>
			<?php endif; ?>

			<?php if ( $nobody_saying ) : ?>
			<section class="wtis-nobody-saying" aria-labelledby="wtis-nobody-heading">
				<h2 id="wtis-nobody-heading" class="wtis-nobody-saying__label"><?php esc_html_e( 'What nobody is saying', 'wellthiissports-child' ); ?></h2>
				<p class="wtis-nobody-saying__body"><?php echo esc_html( $nobody_saying ); ?></p>
			</section>
			<?php endif; ?>

		</main>

	</div>
</div>

<?php get_footer(); ?>
