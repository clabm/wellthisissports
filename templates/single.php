<?php
/**
 * single.php
 * Prediction detail: full-bleed hero, main + sticky sidebar.
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
$prediction_grade  = (int) get_post_meta( $post_id, 'wtis_prediction_grade', true );
$ai_generated      = get_post_meta( $post_id, 'wtis_ai_generated', true );
$ingested_at       = get_post_meta( $post_id, 'wtis_ingested_at', true );
$nobody_saying     = get_post_meta( $post_id, 'wtis_what_nobody_saying', true );
$headline_personality = trim( (string) get_post_meta( $post_id, 'wtis_headline_personality', true ) );
$headline_seo         = trim( (string) get_post_meta( $post_id, 'wtis_headline_seo', true ) );

$label_home = $team_home ? $team_home : get_the_title( $post_id );
$label_away = $team_away ? $team_away : '';
$vs_line    = $label_away ? sprintf( '%s vs %s', $label_home, $label_away ) : $label_home;
$matchup_title_trim = trim( (string) $matchup_title );
$story_h1 = $headline_personality !== '' ? $headline_personality : ( $matchup_title_trim !== '' ? $matchup_title_trim : $vs_line );
if ( $headline_seo !== '' ) {
	$story_h2 = $headline_seo;
} elseif ( $label_away !== '' ) {
	$story_h2 = sprintf(
		/* translators: 1: home team, 2: away team */
		__( '%1$s vs %2$s Prediction', 'wellthiissports-child' ),
		$label_home,
		$label_away
	);
} else {
	$story_h2 = sprintf(
		/* translators: %s: team or matchup label */
		__( '%s Prediction', 'wellthiissports-child' ),
		$label_home
	);
}

$factors_for_list     = $factors_for ? array_filter( array_map( 'trim', explode( '|', $factors_for ) ) ) : [];
$factors_against_list = $factors_against ? array_filter( array_map( 'trim', explode( '|', $factors_against ) ) ) : [];

$ledger_all  = get_option( 'wtis_ledger', [] );
$ledger_row  = ( $sport && isset( $ledger_all[ $sport ] ) ) ? $ledger_all[ $sport ] : null;
$ledger_total = $ledger_row && isset( $ledger_row['total'] ) ? (int) $ledger_row['total'] : 0;
$ledger_ok    = $ledger_row && isset( $ledger_row['correct'] ) ? (int) $ledger_row['correct'] : 0;
$ledger_miss  = max( 0, $ledger_total - $ledger_ok );

$related_meta = [
	[
		'key'     => 'wtis_matchup_date',
		'compare' => 'EXISTS',
	],
];
if ( $sport ) {
	$related_meta[] = [
		'key'   => 'wtis_sport',
		'value' => $sport,
	];
}
$related_q = new WP_Query(
	[
		'posts_per_page' => 3,
		'post__not_in'   => [ $post_id ],
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => $related_meta,
	]
);

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper" id="page-wrapper">

	<?php
	$wtis_hero_post_id      = $post_id;
	$wtis_hero_show_urgent  = ( 'urgent_update' === $article_stage );
	require get_stylesheet_directory() . '/inc/matchup-hero.php';
	unset( $wtis_hero_show_urgent );
	?>

	<div class="wtis-matchup-article">
		<div class="wtis-matchup-article__inner">
			<div id="content" class="wtis-matchup-article__main">

				<header class="wtis-story-headline-stack">
					<h3 class="wtis-story-headline-stack__matchup"><?php echo esc_html( $vs_line ); ?></h3>
					<h1 class="wtis-story-headline-stack__personality"><?php echo esc_html( $story_h1 ); ?></h1>
					<h2 class="wtis-story-headline-stack__seo"><?php echo esc_html( $story_h2 ); ?></h2>
					<div class="wtis-story-headline-stack__meta">
						<?php if ( $sport ) : ?>
						<span class="wtis-story-headline-stack__sport-pill"><?php echo esc_html( $sport ); ?></span>
						<?php endif; ?>
						<?php if ( $matchup_date ) : ?>
						<span class="wtis-story-headline-stack__date"><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $matchup_date ) ) ); ?></span>
						<?php endif; ?>
						<?php if ( $league ) : ?>
						<?php if ( $sport || $matchup_date ) : ?>
						<span class="wtis-story-headline-stack__meta-sep" aria-hidden="true">·</span>
						<?php endif; ?>
						<span class="wtis-story-headline-stack__league"><?php echo esc_html( $league ); ?></span>
						<?php endif; ?>
					</div>
				</header>

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

				<?php if ( '' !== $pred_correct && $actual_result ) : ?>
				<div class="wtis-result-block wtis-result-block--<?php echo $pred_correct ? 'correct' : 'incorrect'; ?>">
					<strong><?php echo $pred_correct ? esc_html__( 'Correct prediction', 'wellthiissports-child' ) : esc_html__( 'Incorrect prediction', 'wellthiissports-child' ); ?></strong>
					<?php esc_html_e( 'Final result:', 'wellthiissports-child' ); ?> <?php echo esc_html( $actual_result ); ?>
				</div>
				<?php endif; ?>

				<?php if ( $analysis ) : ?>
				<div class="wtis-analysis">
					<h2 class="wtis-analysis__heading"><?php esc_html_e( 'The Analysis', 'wellthiissports-child' ); ?></h2>
					<div class="wtis-analysis__body">
						<?php echo wp_kses_post( wpautop( $analysis ) ); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $factors_for_list || $factors_against_list ) : ?>
				<section class="wtis-takeaways" aria-labelledby="wtis-takeaways-heading">
					<h2 id="wtis-takeaways-heading" class="wtis-takeaways__heading"><?php esc_html_e( 'Key Factors', 'wellthiissports-child' ); ?></h2>
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
					<h2 id="wtis-nobody-heading" class="wtis-nobody-saying__label"><?php esc_html_e( 'What Nobody Is Saying', 'wellthiissports-child' ); ?></h2>
					<p class="wtis-nobody-saying__body"><?php echo esc_html( $nobody_saying ); ?></p>
				</section>
				<?php endif; ?>

			</div>

			<aside class="wtis-matchup-article__sidebar" aria-label="<?php esc_attr_e( 'Matchup sidebar', 'wellthiissports-child' ); ?>">

				<?php if ( $confidence ) : ?>
				<div class="wtis-sidebar-card wtis-sidebar-card--meter">
					<p class="wtis-sidebar-card__label"><?php esc_html_e( 'Confidence', 'wellthiissports-child' ); ?></p>
					<div class="wtis-confidence-meter wtis-confidence-meter--sidebar" style="--confidence: <?php echo esc_attr( (string) $confidence ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: confidence percentage */ __( 'Confidence %d percent', 'wellthiissports-child' ), $confidence ) ); ?>">
						<div class="wtis-confidence-meter__hero-row">
							<span class="wtis-confidence-meter__value"><?php echo esc_html( (string) $confidence ); ?></span>
						</div>
						<div class="wtis-confidence-meter__bar" role="presentation">
							<div class="wtis-confidence-meter__fill"></div>
						</div>
						<span class="wtis-confidence-meter__label"><?php echo esc_html( (string) $confidence ); ?>% <?php esc_html_e( 'model confidence', 'wellthiissports-child' ); ?></span>
					</div>
				</div>
				<?php endif; ?>

				<div class="wtis-sidebar-card">
					<h2 class="wtis-sidebar-card__heading"><?php esc_html_e( 'Team breakdown', 'wellthiissports-child' ); ?></h2>
					<div class="wtis-team-breakdown wtis-team-breakdown--sidebar">
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
				</div>

				<?php if ( $ledger_row && $sport ) : ?>
				<div class="wtis-sidebar-card wtis-sidebar-card--ledger">
					<h2 class="wtis-sidebar-card__heading"><?php esc_html_e( 'Our record', 'wellthiissports-child' ); ?></h2>
					<p class="wtis-sidebar-ledger__sport"><?php echo esc_html( $sport ); ?></p>
					<p class="wtis-sidebar-ledger__record" aria-label="<?php esc_attr_e( 'Wins and losses', 'wellthiissports-child' ); ?>">
						<span class="wtis-sidebar-ledger__w"><?php echo esc_html( (string) $ledger_ok ); ?></span>
						<span class="wtis-sidebar-ledger__sep" aria-hidden="true">-</span>
						<span class="wtis-sidebar-ledger__l"><?php echo esc_html( (string) $ledger_miss ); ?></span>
					</p>
					<?php if ( $ledger_total > 0 && isset( $ledger_row['accuracy'] ) ) : ?>
					<p class="wtis-sidebar-ledger__accuracy"><?php echo esc_html( number_format_i18n( (float) $ledger_row['accuracy'], 1 ) ); ?>%</p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( $related_q->have_posts() ) : ?>
				<div class="wtis-sidebar-card">
					<h2 class="wtis-sidebar-card__heading"><?php esc_html_e( 'Related matchups', 'wellthiissports-child' ); ?></h2>
					<ul class="wtis-related-matchups">
						<?php
						while ( $related_q->have_posts() ) :
							$related_q->the_post();
							$r_home = get_post_meta( get_the_ID(), 'wtis_team_home', true );
							$r_away = get_post_meta( get_the_ID(), 'wtis_team_away', true );
							$line   = trim( ( $r_home ? $r_home : get_the_title() ) . ( $r_away ? ' vs ' . $r_away : '' ) );
							?>
						<li class="wtis-related-matchups__item">
							<a class="wtis-related-matchups__link" href="<?php the_permalink(); ?>"><?php echo esc_html( $line ); ?></a>
						</li>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>
				<?php endif; ?>

			</aside>
		</div>
	</div>
</div>

<?php get_footer(); ?>
