<?php
/**
 * front-page.php
 * Ringer-style homepage: hero row, mid grid, ledger sidebar.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

require_once get_stylesheet_directory() . '/inc/homepage-payload.php';

$home_query = new WP_Query(
	[
		'posts_per_page' => 8,
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

$post_ids = wp_list_pluck( $home_query->posts, 'ID' );
$hero_id  = $post_ids[0] ?? 0;
$compact  = array_slice( $post_ids, 1, 2 );
$mid_ids  = array_slice( $post_ids, 3, 3 );
$wide_ids = array_slice( $post_ids, 6, 2 );

$ledger = get_option( 'wtis_ledger', [] );

require get_stylesheet_directory() . '/inc/masthead.php';
?>

<div class="wrapper wtis-home" id="page-wrapper">
	<main id="content" class="wtis-home__inner">

		<?php if ( $hero_id ) : ?>
		<section class="wtis-home__section wtis-home__top<?php echo empty( $compact ) ? ' wtis-home__top--solo' : ''; ?>" aria-label="<?php esc_attr_e( 'Featured matchups', 'wellthiissports-child' ); ?>">
			<div class="wtis-home__top-grid">
				<?php
				$h = wtis_home_matchup_payload( $hero_id );
				?>
				<article class="wtis-home-hero">
					<a href="<?php echo esc_url( $h['permalink'] ); ?>" class="wtis-home-hero__link">
						<div class="wtis-home-hero__visual">
							<?php wtis_home_print_badges( $h['grade'] ); ?>
							<?php
							$hero_img = $h['img_hero'] ? $h['img_hero'] : $h['img_card'];
							wtis_home_print_media( $hero_img, 'hero', $h['title_line'], 'eager' );
							?>
						</div>
						<div class="wtis-home-hero__body">
							<?php if ( $h['sport'] ) : ?>
							<p class="wtis-home__sport"><?php echo esc_html( $h['sport'] ); ?></p>
							<?php endif; ?>
							<h2 class="wtis-home-hero__title"><?php echo esc_html( $h['title_line'] ); ?></h2>
							<?php if ( $h['winner'] && $h['confidence'] ) : ?>
							<p class="wtis-home__pick-line wtis-home__pick-line--muted">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: team name, 2: confidence integer */
										__( 'The Pick: %1$s · %2$s%% confidence', 'wellthiissports-child' ),
										$h['winner'],
										(string) $h['confidence']
									)
								);
								?>
							</p>
							<?php endif; ?>
							<?php if ( $h['date_display'] ) : ?>
							<p class="wtis-home__date"><?php echo esc_html( $h['date_display'] ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				</article>

				<?php if ( ! empty( $compact ) ) : ?>
				<div class="wtis-home__stack" role="list">
					<?php foreach ( $compact as $cid ) : ?>
						<?php $c = wtis_home_matchup_payload( (int) $cid ); ?>
					<div class="wtis-home-compact" role="listitem">
						<a href="<?php echo esc_url( $c['permalink'] ); ?>" class="wtis-home-compact__link">
							<?php wtis_home_print_badges( $c['grade'] ); ?>
							<div class="wtis-home-compact__media">
								<?php
								$sq = $c['img_square'] ? $c['img_square'] : $c['img_card'];
								wtis_home_print_media( $sq, 'square', $c['title_line'] );
								?>
							</div>
							<div class="wtis-home-compact__body">
								<?php if ( $c['sport'] ) : ?>
								<p class="wtis-home__sport"><?php echo esc_html( $c['sport'] ); ?></p>
								<?php endif; ?>
								<h3 class="wtis-home-compact__title"><?php echo esc_html( $c['title_line'] ); ?></h3>
								<?php if ( $c['winner'] ) : ?>
								<p class="wtis-home__pick-gold"><?php echo esc_html( $c['winner'] ); ?><?php echo $c['confidence'] ? esc_html( ' · ' . (string) $c['confidence'] . '%' ) : ''; ?></p>
								<?php endif; ?>
								<?php if ( $c['date_display'] ) : ?>
								<p class="wtis-home__date"><?php echo esc_html( $c['date_display'] ); ?></p>
								<?php endif; ?>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( ! empty( $mid_ids ) ) : ?>
		<section class="wtis-home__section wtis-home__mid" aria-label="<?php esc_attr_e( 'More matchups', 'wellthiissports-child' ); ?>">
			<div class="wtis-home__mid-grid">
				<?php foreach ( $mid_ids as $mid_id ) : ?>
					<?php $m = wtis_home_matchup_payload( (int) $mid_id ); ?>
				<article class="wtis-home-mid">
					<a href="<?php echo esc_url( $m['permalink'] ); ?>" class="wtis-home-mid__link">
						<div class="wtis-home-mid__visual">
							<?php wtis_home_print_badges( $m['grade'] ); ?>
							<?php wtis_home_print_media( $m['img_card'], 'mid', $m['title_line'] ); ?>
						</div>
						<div class="wtis-home-mid__body">
							<?php if ( $m['sport'] ) : ?>
							<p class="wtis-home__sport"><?php echo esc_html( $m['sport'] ); ?></p>
							<?php endif; ?>
							<h3 class="wtis-home-mid__title"><?php echo esc_html( $m['title_line'] ); ?></h3>
							<?php if ( $m['winner'] && $m['confidence'] ) : ?>
							<p class="wtis-home__pick-line wtis-home__pick-line--gold">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: team name, 2: confidence */
										__( 'The Pick: %1$s · %2$s%%', 'wellthiissports-child' ),
										$m['winner'],
										(string) $m['confidence']
									)
								);
								?>
							</p>
							<?php endif; ?>
							<?php if ( $m['date_display'] ) : ?>
							<p class="wtis-home__date"><?php echo esc_html( $m['date_display'] ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<section class="wtis-home__section wtis-home__bottom<?php echo empty( $wide_ids ) ? ' wtis-home__bottom--ledger-only' : ''; ?>" aria-label="<?php esc_attr_e( 'Spotlight and accuracy', 'wellthiissports-child' ); ?>">
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
									wtis_home_print_media( $wi, 'wide', $w['title_line'] );
									?>
								</div>
								<div class="wtis-home-wide__body">
									<?php if ( $w['sport'] ) : ?>
									<p class="wtis-home__sport"><?php echo esc_html( $w['sport'] ); ?></p>
									<?php endif; ?>
									<h3 class="wtis-home-wide__title"><?php echo esc_html( $w['title_line'] ); ?></h3>
									<?php if ( $w['winner'] && $w['confidence'] ) : ?>
									<p class="wtis-home__pick-line wtis-home__pick-line--gold">
										<?php
										echo esc_html(
											sprintf(
												__( 'The Pick: %1$s · %2$s%%', 'wellthiissports-child' ),
												$w['winner'],
												(string) $w['confidence']
											)
										);
										?>
									</p>
									<?php endif; ?>
									<?php if ( $w['teaser'] ) : ?>
									<p class="wtis-home-wide__teaser"><?php echo esc_html( $w['teaser'] ); ?></p>
									<?php endif; ?>
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

	</main>
</div>

<?php get_footer(); ?>
