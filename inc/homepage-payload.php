<?php
/**
 * Homepage layout: matchup payload + shared media/badges output.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build display data for a matchup post on the homepage.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function wtis_home_matchup_payload( int $post_id ): array {
	$team_home    = get_post_meta( $post_id, 'wtis_team_home', true );
	$team_away    = get_post_meta( $post_id, 'wtis_team_away', true );
	$matchup_date = get_post_meta( $post_id, 'wtis_matchup_date', true );
	$sport        = get_post_meta( $post_id, 'wtis_sport', true );
	$winner       = get_post_meta( $post_id, 'wtis_prediction_winner', true );
	$confidence   = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
	$grade        = (int) get_post_meta( $post_id, 'wtis_prediction_grade', true );
	$factors_for  = get_post_meta( $post_id, 'wtis_factors_for', true );
	$factors_list = $factors_for ? array_filter( array_map( 'trim', explode( '|', $factors_for ) ) ) : [];

	$h          = $team_home ? $team_home : get_the_title( $post_id );
	$a          = $team_away ? $team_away : '';
	$media_alt  = $a ? ( $h . ' vs ' . $a ) : $h;
	$title_line = get_the_title( $post_id );

	return [
		'id'             => $post_id,
		'permalink'      => get_permalink( $post_id ),
		'title_line'     => $title_line,
		'media_alt'      => $media_alt,
		'sport'          => $sport,
		'winner'         => $winner,
		'confidence'     => $confidence,
		'grade'          => $grade,
		'date_display'   => $matchup_date ? date_i18n( 'F j, Y', strtotime( $matchup_date ) ) : '',
		'img_card'       => get_the_post_thumbnail_url( $post_id, 'wtis-card' ),
		'img_hero'       => get_the_post_thumbnail_url( $post_id, 'wtis-hero' ),
		'img_square'     => get_the_post_thumbnail_url( $post_id, 'wtis-home-square' ),
		'teaser'         => $factors_list[0] ?? '',
	];
}

/**
 * Prediction + optional card-pick badges (top-left stack).
 *
 * @param int $grade Prediction grade.
 */
function wtis_home_print_badges( int $grade ): void {
	?>
	<div class="wtis-home__badges">
		<span class="wtis-home__badge wtis-home__badge--prediction"><?php esc_html_e( 'Prediction', 'wellthiissports-child' ); ?></span>
		<?php if ( $grade >= 8 ) : ?>
		<span class="wtis-home__badge wtis-home__badge--card-pick"><?php esc_html_e( 'Card pick', 'wellthiissports-child' ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Featured image or gold gradient placeholder (never broken img).
 *
 * @param string|null $url     Image URL.
 * @param string      $variant hero|square|compact|mid|mid_band|wide.
 * @param string      $title   Placeholder + alt text.
 * @param string      $loading img loading attribute.
 */
function wtis_home_print_media( ?string $url, string $variant, string $title, string $loading = 'lazy' ): void {
	$map = [
		'hero'     => 'wtis-home__media wtis-home__media--hero',
		'square'   => 'wtis-home__media wtis-home__media--square',
		'compact'  => 'wtis-home__media wtis-home__media--compact',
		'mid'      => 'wtis-home__media wtis-home__media--mid',
		'mid_band' => 'wtis-home__media wtis-home__media--mid-band',
		'wide'     => 'wtis-home__media wtis-home__media--wide',
	];
	$base = isset( $map[ $variant ] ) ? $map[ $variant ] : $map['mid'];

	if ( $url ) {
		echo '<div class="' . esc_attr( $base ) . '">';
		echo '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $title ) . '" loading="' . esc_attr( $loading ) . '" width="640" height="360" decoding="async" />';
		echo '</div>';
		return;
	}

	echo '<div class="' . esc_attr( $base . ' wtis-home__media--ph' ) . '" aria-hidden="true">';
	echo '<span class="wtis-home__ph-title">' . esc_html( $title ) . '</span>';
	echo '</div>';
}
