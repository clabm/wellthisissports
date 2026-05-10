<?php
/**
 * front-page.php
 * Ringer-style homepage: dark rails, hero row, mid grid, ledger.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

require_once get_stylesheet_directory() . '/inc/homepage-payload.php';

$home_query = new WP_Query(
	[
		'post_type'      => [ 'post', 'wtis_matchup' ],
		'posts_per_page' => 16,
		'post_status'    => 'publish',
		'meta_key'       => 'wtis_matchup_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => [
			[
				'key'     => 'wtis_matchup_date',
				'compare' => 'EXISTS',
			],
		],
	]
);

$post_ids     = wp_list_pluck( $home_query->posts, 'ID' );
$hero_id      = $post_ids[0] ?? 0;
$lead_rail_n  = 5;
$lead_rail_ids = $hero_id ? array_slice( $post_ids, 1, $lead_rail_n ) : [];
$mid_offset    = 1 + count( $lead_rail_ids );
$mid_ids       = array_slice( $post_ids, $mid_offset, 3 );
$wide_ids      = array_slice( $post_ids, $mid_offset + 3, 2 );

$ledger = get_option( 'wtis_ledger', [] );

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper wtis-home" id="page-wrapper">
	<main id="content" class="wtis-home__main">
		<div class="wtis-home__inner wtis-home__inner--lead">

		<?php if ( $hero_id ) : ?>
			<?php
			$h        = wtis_home_matchup_payload( $hero_id );
			$hero_img = $h['img_hero'] ? $h['img_hero'] : $h['img_card'];
			?>
		<section class="wtis-home-lead<?php echo empty( $lead_rail_ids ) ? ' wtis-home-lead--solo' : ''; ?>" aria-label="<?php esc_attr_e( 'Featured matchups', 'wellthiissports-child' ); ?>">
			<div class="wtis-home-lead__grid">
				<div class="wtis-home-lead__visual">
					<a class="wtis-home-lead__visual-link" href="<?php echo esc_url( $h['permalink'] ); ?>">
						<span class="wtis-home-lead__gradient" aria-hidden="true"></span>
						<?php if ( $hero_img ) : ?>
						<img
							class="wtis-home-lead__img"
							src="<?php echo esc_url( $hero_img ); ?>"
							alt="<?php echo esc_attr( $h['media_alt'] ); ?>"
							width="1240"
							height="697"
							loading="eager"
							decoding="async">
						<?php else : ?>
						<div class="wtis-home-lead__ph">
							<span class="wtis-home-lead__ph-title"><?php echo esc_html( $h['title_line'] ); ?></span>
						</div>
						<?php endif; ?>
						<?php if ( $hero_img ) : ?>
						<div class="wtis-home-lead__caption">
							<?php if ( $h['sport'] ) : ?>
							<p class="wtis-home-lead__sport"><?php echo esc_html( $h['sport'] ); ?></p>
							<?php endif; ?>
							<h2 class="wtis-home-lead__title"><?php echo esc_html( $h['title_line'] ); ?></h2>
							<?php
							$seo_headline = get_post_meta( $hero_id, 'wtis_headline_seo', true );
							if ( $seo_headline ) :
								?>
							<p class="wtis-home-lead__subhead"><?php echo esc_html( $seo_headline ); ?></p>
								<?php
							endif;
							?>
							<?php if ( $h['date_display'] ) : ?>
							<p class="wtis-home-lead__date"><?php echo esc_html( $h['date_display'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</a>
				</div>

				<?php if ( ! empty( $lead_rail_ids ) ) : ?>
				<div class="wtis-home-lead__rail">
					<ul class="wtis-home-lead__list" role="list">
						<?php foreach ( $lead_rail_ids as $rid ) : ?>
							<?php $r = wtis_home_matchup_payload( (int) $rid ); ?>
						<li class="wtis-home-lead__item" role="listitem">
							<a class="wtis-home-lead__row-link" href="<?php echo esc_url( $r['permalink'] ); ?>">
								<?php if ( $r['sport'] ) : ?>
								<span class="wtis-home-lead__row-sport"><?php echo esc_html( $r['sport'] ); ?></span>
								<?php endif; ?>
								<span class="wtis-home-lead__row-title"><?php echo esc_html( $r['title_line'] ); ?></span>
								<?php if ( $r['date_display'] ) : ?>
								<span class="wtis-home-lead__row-date"><?php echo esc_html( $r['date_display'] ); ?></span>
								<?php endif; ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		</div>

		<?php if ( ! empty( $mid_ids ) ) : ?>
		<section class="wtis-home-mid" aria-label="<?php esc_attr_e( 'Latest predictions', 'wellthiissports-child' ); ?>">
			<div class="wtis-home__inner wtis-home__inner--mid">
				<div class="wtis-home-dark wtis-home-dark--mid">
					<header class="wtis-home-mid__label-row">
						<h2 class="wtis-home-mid__label"><?php esc_html_e( 'Latest predictions', 'wellthiissports-child' ); ?></h2>
						<span class="wtis-home-mid__gold-rule" aria-hidden="true"></span>
					</header>
					<div class="wtis-home-mid__grid">
						<?php foreach ( $mid_ids as $mid_id ) : ?>
							<?php $m = wtis_home_matchup_payload( (int) $mid_id ); ?>
						<article class="wtis-home-mid-card">
							<a href="<?php echo esc_url( $m['permalink'] ); ?>" class="wtis-home-mid-card__link">
								<div class="wtis-home-mid-card__visual">
									<?php wtis_home_print_badges( $m['grade'] ); ?>
									<?php wtis_home_print_media( $m['img_card'], 'mid_band', $m['media_alt'] ); ?>
								</div>
								<div class="wtis-home-mid-card__body">
									<?php if ( $m['sport'] ) : ?>
									<p class="wtis-home__sport"><?php echo esc_html( $m['sport'] ); ?></p>
									<?php endif; ?>
									<h3 class="wtis-home-mid-card__title"><?php echo esc_html( $m['title_line'] ); ?></h3>
									<?php if ( $m['date_display'] ) : ?>
									<p class="wtis-home__date"><?php echo esc_html( $m['date_display'] ); ?></p>
									<?php endif; ?>
								</div>
							</a>
						</article>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<div class="wtis-home__inner">
		<section class="wtis-home-dark wtis-home-dark--bottom<?php echo empty( $wide_ids ) ? ' wtis-home-dark--bottom-solo' : ''; ?>" aria-label="<?php esc_attr_e( 'Spotlight and accuracy', 'wellthiissports-child' ); ?>">
			<div class="wtis-home__bottom-grid">
				<?php if ( ! empty( $wide_ids ) ) : ?>
				<div class="wtis-home__wide-col">
					<div class="wtis-home__wide-stack" role="list">
						<?php foreach ( $wide_ids as $wid ) : ?>
							<?php $w = wtis_home_matchup_payload( (int) $wid ); ?>
						<div class="wtis-home-wide" role="listitem">
							<a href="<?php echo esc_url( $w['permalink'] ); ?>" class="wtis-home-wide__link">
								<?php wtis_home_print_badges( $w['grade'] ); ?>
								<div class="wtis-home-wide__media">
									<?php
									$wi = $w['img_square'] ? $w['img_square'] : $w['img_card'];
									wtis_home_print_media( $wi, 'wide', $w['media_alt'] );
									?>
								</div>
								<div class="wtis-home-wide__body">
									<?php if ( $w['sport'] ) : ?>
									<p class="wtis-home__sport"><?php echo esc_html( $w['sport'] ); ?></p>
									<?php endif; ?>
									<h3 class="wtis-home-wide__title"><?php echo esc_html( $w['title_line'] ); ?></h3>
									<?php if ( $w['date_display'] ) : ?>
									<p class="wtis-home__date"><?php echo esc_html( $w['date_display'] ); ?></p>
									<?php endif; ?>
								</div>
							</a>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<aside class="wtis-home-ledger" aria-labelledby="wtis-home-ledger-heading">
					<h2 id="wtis-home-ledger-heading" class="wtis-home-ledger__heading"><?php esc_html_e( 'Our record', 'wellthiissports-child' ); ?></h2>
					<ul class="wtis-home-ledger__list" role="list">
						<?php
						if ( ! empty( $ledger ) ) :
							foreach ( $ledger as $sport_name => $data ) :
								$total    = isset( $data['total'] ) ? (int) $data['total'] : 0;
								$correct  = isset( $data['correct'] ) ? (int) $data['correct'] : 0;
								$wrong    = max( 0, $total - $correct );
								$accuracy = isset( $data['accuracy'] ) ? (float) $data['accuracy'] : null;
								?>
						<li class="wtis-home-ledger__row" role="listitem">
							<span class="wtis-home-ledger__sport"><?php echo esc_html( $sport_name ); ?></span>
							<div class="wtis-home-ledger__meta">
								<span class="wtis-home-ledger__record" aria-label="<?php echo esc_attr( $sport_name . ' ' . $correct . ' ' . $wrong ); ?>">
									<span class="wtis-home-ledger__w"><?php echo esc_html( (string) $correct ); ?></span>
									<span class="wtis-home-ledger__sep" aria-hidden="true">-</span>
									<span class="wtis-home-ledger__l"><?php echo esc_html( (string) $wrong ); ?></span>
								</span>
								<?php if ( null !== $accuracy && $total > 0 ) : ?>
								<span class="wtis-home-ledger__pct"><?php echo esc_html( number_format_i18n( $accuracy, 1 ) ); ?>%</span>
								<?php else : ?>
								<span class="wtis-home-ledger__pct wtis-home-ledger__pct--empty"><?php echo esc_html( '—' ); ?></span>
								<?php endif; ?>
							</div>
						</li>
								<?php
							endforeach;
						else :
							$placeholders = [
								__( 'NFL', 'wellthiissports-child' ),
								__( 'NBA', 'wellthiissports-child' ),
								__( 'World Cup', 'wellthiissports-child' ),
							];
							foreach ( $placeholders as $pl_name ) :
								?>
						<li class="wtis-home-ledger__row wtis-home-ledger__row--placeholder" role="listitem">
							<span class="wtis-home-ledger__sport"><?php echo esc_html( $pl_name ); ?></span>
							<div class="wtis-home-ledger__meta">
								<span class="wtis-home-ledger__record">
									<span class="wtis-home-ledger__w">0</span>
									<span class="wtis-home-ledger__sep" aria-hidden="true">-</span>
									<span class="wtis-home-ledger__l">0</span>
								</span>
								<span class="wtis-home-ledger__pct wtis-home-ledger__pct--empty"><?php echo esc_html( '—' ); ?></span>
							</div>
						</li>
								<?php
							endforeach;
						endif;
						?>
					</ul>
				</aside>
			</div>
		</section>

		<?php if ( ! $hero_id ) : ?>
		<section class="wtis-home__section">
			<p class="wtis-no-matchups"><?php esc_html_e( 'No matchups yet. Check back soon.', 'wellthiissports-child' ); ?></p>
		</section>
		<?php endif; ?>

		</div>
	</main>
</div>

<?php get_footer(); ?>
