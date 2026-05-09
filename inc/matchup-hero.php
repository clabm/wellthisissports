<?php
/**
 * Shared full-bleed matchup hero (50/50 image + dark panel).
 *
 * Expects $wtis_hero_post_id (int). Optional:
 * - $wtis_hero_permalink (string) — if set, title links here (e.g. homepage).
 * - $wtis_hero_heading_tag (string) — 'h1' or 'h2', default h1 when no permalink, else h2 (linked personality line only).
 * - $wtis_hero_show_urgent (bool) — show urgent update badge in panel.
 *
 * On single posts, visible document headings live in single.php; duplicate
 * title stack here is aria-hidden for sighted layout only.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $wtis_hero_post_id ) ) {
	return;
}

$hero_pid     = (int) $wtis_hero_post_id;
$team_home    = get_post_meta( $hero_pid, 'wtis_team_home', true );
$team_away    = get_post_meta( $hero_pid, 'wtis_team_away', true );
$sport        = get_post_meta( $hero_pid, 'wtis_sport', true );
$league       = get_post_meta( $hero_pid, 'wtis_league', true );
$matchup_date = get_post_meta( $hero_pid, 'wtis_matchup_date', true );
$winner       = get_post_meta( $hero_pid, 'wtis_prediction_winner', true );
$confidence   = (int) get_post_meta( $hero_pid, 'wtis_confidence_score', true );
$feat_img     = get_the_post_thumbnail_url( $hero_pid, 'wtis-hero' );
$matchup_title_meta = trim( (string) get_post_meta( $hero_pid, 'wtis_matchup_title', true ) );
$headline_personality = trim( (string) get_post_meta( $hero_pid, 'wtis_headline_personality', true ) );
$headline_seo         = trim( (string) get_post_meta( $hero_pid, 'wtis_headline_seo', true ) );

$label_home = $team_home ? $team_home : get_the_title( $hero_pid );
$label_away = $team_away ? $team_away : '';
$vs_line    = $label_away ? sprintf( '%s vs %s', $label_home, $label_away ) : $label_home;

$personality_text = $headline_personality !== '' ? $headline_personality : ( $matchup_title_meta !== '' ? $matchup_title_meta : $vs_line );
if ( $headline_seo !== '' ) {
	$seo_text = $headline_seo;
} elseif ( $label_away !== '' ) {
	$seo_text = sprintf(
		/* translators: 1: home team, 2: away team */
		__( '%1$s vs %2$s Prediction', 'wellthiissports-child' ),
		$label_home,
		$label_away
	);
} else {
	$seo_text = sprintf(
		/* translators: %s: team or matchup label */
		__( '%s Prediction', 'wellthiissports-child' ),
		$label_home
	);
}

$link_url = isset( $wtis_hero_permalink ) ? $wtis_hero_permalink : '';
if ( isset( $wtis_hero_heading_tag ) && in_array( $wtis_hero_heading_tag, [ 'h1', 'h2' ], true ) ) {
	$heading_tag = $wtis_hero_heading_tag;
} else {
	$heading_tag = $link_url ? 'h2' : 'h1';
}

$hero_class = 'wtis-matchup-hero';
if ( $link_url ) {
	$hero_class .= ' wtis-matchup-hero--linked';
}
?>
<section class="<?php echo esc_attr( $hero_class ); ?>" aria-label="<?php esc_attr_e( 'Matchup hero', 'wellthiissports-child' ); ?>">
	<div class="wtis-matchup-hero__media">
		<?php if ( $feat_img ) : ?>
		<img src="<?php echo esc_url( $feat_img ); ?>"
			alt="<?php echo esc_attr( $label_home . ( $label_away ? ' vs ' . $label_away : '' ) ); ?>"
			loading="<?php echo $link_url ? 'lazy' : 'eager'; ?>"
			width="1240"
			height="697">
		<?php else : ?>
		<div class="wtis-matchup-hero__media-placeholder" aria-hidden="true"></div>
		<?php endif; ?>
	</div>
	<div class="wtis-matchup-hero__panel">
		<div class="wtis-matchup-hero__panel-inner">
			<?php if ( ! empty( $wtis_hero_show_urgent ) ) : ?>
			<div class="wtis-urgent-badge wtis-urgent-badge--hero"><?php esc_html_e( 'Urgent update', 'wellthiissports-child' ); ?></div>
			<?php endif; ?>

			<?php if ( $link_url ) : ?>
			<h3 class="wtis-matchup-hero__matchup-label"><?php echo esc_html( $vs_line ); ?></h3>
			<?php
			printf( '<%1$s class="wtis-matchup-hero__personality-heading">', esc_attr( $heading_tag ) );
			echo '<a class="wtis-matchup-hero__personality-link" href="' . esc_url( $link_url ) . '">';
			echo esc_html( $personality_text );
			echo '</a>';
			printf( '</%1$s>', esc_attr( $heading_tag ) );
			?>
			<p class="wtis-matchup-hero__seo-line"><?php echo esc_html( $seo_text ); ?></p>
			<?php else : ?>
			<div class="wtis-matchup-hero__titles" aria-hidden="true">
				<p class="wtis-matchup-hero__matchup-label"><?php echo esc_html( $vs_line ); ?></p>
				<p class="wtis-matchup-hero__personality"><?php echo esc_html( $personality_text ); ?></p>
				<p class="wtis-matchup-hero__seo-line"><?php echo esc_html( $seo_text ); ?></p>
			</div>
			<?php endif; ?>

			<div class="wtis-matchup-hero__meta">
				<?php if ( $sport ) : ?>
				<span class="wtis-matchup-hero__sport-pill"><?php echo esc_html( $sport ); ?></span>
				<?php endif; ?>
				<?php if ( $matchup_date ) : ?>
				<span class="wtis-matchup-hero__date"><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $matchup_date ) ) ); ?></span>
				<?php endif; ?>
				<?php if ( $league ) : ?>
				<?php if ( $sport || $matchup_date ) : ?>
				<span class="wtis-matchup-hero__meta-sep" aria-hidden="true">·</span>
				<?php endif; ?>
				<span class="wtis-matchup-hero__league"><?php echo esc_html( $league ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $winner ) : ?>
			<div class="wtis-matchup-hero__pick">
				<span class="wtis-matchup-hero__pick-label"><?php esc_html_e( 'The Pick', 'wellthiissports-child' ); ?></span>
				<span class="wtis-matchup-hero__pick-winner"><?php echo esc_html( $winner ); ?></span>
				<?php if ( $confidence ) : ?>
				<div class="wtis-matchup-hero__pick-score">
					<span class="wtis-matchup-hero__pick-score-num"><?php echo esc_html( (string) $confidence ); ?></span>
					<span class="wtis-matchup-hero__pick-score-suffix"><?php esc_html_e( 'confidence', 'wellthiissports-child' ); ?></span>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="wtis-matchup-hero__accent" aria-hidden="true"></div>
		</div>
	</div>
</section>
