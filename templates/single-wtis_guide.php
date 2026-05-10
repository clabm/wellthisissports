<?php
/**
 * Single template: wtis_guide — game guides with 50/50 hero and narrow body.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$post_id       = get_the_ID();
$seo_headline  = trim( (string) get_post_meta( $post_id, 'wtis_headline_seo', true ) );
$feat_img = get_the_post_thumbnail_url( $post_id, 'wtis-hero' );
$thumb_id = get_post_thumbnail_id( $post_id );
$img_alt  = '';
if ( $thumb_id ) {
	$img_alt = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
}
if ( $img_alt === '' ) {
	$img_alt = get_the_title();
}

$content_type_label = __( 'GAME GUIDE', 'wellthiissports-child' );
$ct_terms           = get_the_terms( $post_id, 'wtis_content_type' );
if ( $ct_terms && ! is_wp_error( $ct_terms ) ) {
	$content_type_label = strtoupper( $ct_terms[0]->name );
}

$tournament_label = '';
$tt_terms         = get_the_terms( $post_id, 'wtis_tournament' );
if ( $tt_terms && ! is_wp_error( $tt_terms ) ) {
	$tournament_label = strtoupper( $tt_terms[0]->name );
}

$sport_label = '';
$sp_terms    = get_the_terms( $post_id, 'wtis_sport' );
if ( $sp_terms && ! is_wp_error( $sp_terms ) ) {
	$sport_label = $sp_terms[0]->name;
} else {
	$sport_meta = get_post_meta( $post_id, 'wtis_sport', true );
	if ( $sport_meta ) {
		$sport_label = $sport_meta;
	}
}

$date_display = get_the_date( get_option( 'date_format' ) );

$guide_venue_address = trim( (string) get_post_meta( $post_id, 'wtis_guide_venue_address', true ) );
$guide_venue_name    = trim( (string) get_post_meta( $post_id, 'wtis_guide_venue_name', true ) );
$guide_maps_url = '';
if ( $guide_venue_address !== '' ) {
	$guide_maps_url = 'https://www.google.com/maps/search/' . rawurlencode( $guide_venue_address );
}
$guide_venue_label = $guide_venue_name !== '' ? $guide_venue_name : $guide_venue_address;

$ig_embed_code = trim( (string) get_post_meta( $post_id, 'wtis_guide_instagram_embed_code', true ) );
$ig_post_url   = trim( (string) get_post_meta( $post_id, 'wtis_guide_instagram_post_url', true ) );

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper wtis-guide" id="page-wrapper">
	<section class="wtis-guide-hero" aria-label="<?php esc_attr_e( 'Guide hero', 'wellthiissports-child' ); ?>">
		<div class="wtis-guide-hero__grid">
			<div class="wtis-guide-hero__media">
				<?php if ( $feat_img ) : ?>
				<img
					class="wtis-guide-hero__img"
					src="<?php echo esc_url( $feat_img ); ?>"
					alt="<?php echo esc_attr( $img_alt ); ?>"
					width="1240"
					height="697"
					loading="eager"
					decoding="async">
				<?php else : ?>
				<div class="wtis-guide-hero__media-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</div>
			<div class="wtis-guide-hero__panel">
				<div class="wtis-guide-hero__panel-inner">
					<p class="wtis-guide-hero__badge wtis-guide-hero__badge--type"><?php echo esc_html( $content_type_label ); ?></p>
					<?php if ( $tournament_label ) : ?>
					<p class="wtis-guide-hero__badge wtis-guide-hero__badge--tournament"><?php echo esc_html( $tournament_label ); ?></p>
					<?php endif; ?>
					<h1 class="wtis-guide-hero__title"><?php the_title(); ?></h1>
					<?php if ( $seo_headline ) : ?>
					<p class="wtis-guide-hero__subhead"><?php echo esc_html( $seo_headline ); ?></p>
					<?php endif; ?>
					<div class="wtis-guide-hero__meta">
						<?php if ( $date_display ) : ?>
						<time class="wtis-guide-hero__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( $date_display ); ?></time>
						<?php endif; ?>
						<?php if ( $sport_label ) : ?>
							<?php if ( $date_display ) : ?>
						<span class="wtis-guide-hero__meta-sep" aria-hidden="true"></span>
							<?php endif; ?>
						<span class="wtis-guide-hero__sport"><?php echo esc_html( $sport_label ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $guide_venue_address !== '' ) : ?>
	<div class="wtis-guide-map-bar">
		<span class="wtis-guide-map-bar__venue">
			<span class="wtis-guide-map-bar__pin" aria-hidden="true">&#128205;</span>
			<?php echo esc_html( $guide_venue_label ); ?>
		</span>
		<a href="<?php echo esc_url( $guide_maps_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			class="wtis-guide-map-bar__link">
			<?php esc_html_e( 'Get Directions', 'wellthiissports-child' ); ?>
		</a>
	</div>
	<?php endif; ?>

	<main id="content" class="wtis-guide-body">
		<div class="wtis-guide-body__inner">
			<?php the_content(); ?>
		</div>
		<?php
		if ( $ig_embed_code !== '' ) {
			echo '<div class="wtis-guide-instagram">';
			echo $ig_embed_code; // raw HTML from Director; sanitized via wp_kses_post on save
			echo '</div>';
			wp_enqueue_script( 'instagram-embed', 'https://www.instagram.com/embed.js', [], null, true );
		} elseif ( $ig_post_url !== '' ) {
			$ig_embed = wp_oembed_get( $ig_post_url );
			if ( $ig_embed ) {
				echo '<div class="wtis-guide-instagram">';
				echo $ig_embed;
				echo '</div>';
			}
		}
		?>
	</main>
</div>

<?php get_footer(); ?>
