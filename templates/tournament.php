<?php
/**
 * tournament.php
 * World Cup and tournament landing page.
 * Handles: is_page('world-cup'), is_tax('wtis_tournament'), is_tax('wtis_sport')
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
require get_stylesheet_directory() . '/inc/masthead.php';

// Determine context and build query
if ( is_tax( 'wtis_tournament' ) || is_tax( 'wtis_sport' ) ) {
    $queried_term     = get_queried_object();
    $tournament_title = $queried_term->name;
    $taxonomy         = $queried_term->taxonomy;

    $query_args = [
        'post_type'      => [ 'post', 'wtis_matchup' ],
        'posts_per_page' => 30,
        'post_status'    => 'publish',
        'tax_query'      => [
            [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $queried_term->term_id,
            ],
        ],
        'meta_key' => 'wtis_matchup_date',
        'orderby'  => 'meta_value',
        'order'    => 'ASC',
    ];
} else {
    // Page context — /world-cup/ etc.
    // Try to find matching taxonomy term; fall back to sport meta query.
    the_post();
    $page_slug        = get_post_field( 'post_name', get_the_ID() );
    $tournament_title = get_the_title();

    $term    = get_term_by( 'slug', $page_slug, 'wtis_tournament' );
    $meta_q  = [
        [
            'key'     => 'wtis_matchup_date',
            'compare' => 'EXISTS',
        ],
    ];

    if ( $term ) {
        $query_args = [
            'post_type'      => [ 'post', 'wtis_matchup' ],
            'posts_per_page' => 30,
            'post_status'    => 'publish',
            'tax_query'      => [
                [
                    'taxonomy' => 'wtis_tournament',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ],
            ],
            'meta_query' => $meta_q,
            'meta_key'   => 'wtis_matchup_date',
            'orderby'    => 'meta_value',
            'order'      => 'ASC',
        ];
    } else {
        // Fallback: query by wtis_sport meta (supports seeded posts before taxonomy migration)
        $meta_q[] = [
            'key'   => 'wtis_sport',
            'value' => 'World Cup',
        ];
        $query_args = [
            'post_type'      => [ 'post', 'wtis_matchup' ],
            'posts_per_page' => 30,
            'post_status'    => 'publish',
            'meta_query'     => array_merge( [ 'relation' => 'AND' ], $meta_q ),
            'meta_key'       => 'wtis_matchup_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        ];
    }
}

$tournament_query = new WP_Query( $query_args );

require_once get_stylesheet_directory() . '/inc/homepage-payload.php';
?>

<div class="wrapper wtis-tournament" id="page-wrapper">
	<main id="content" class="wtis-tournament__main">

		<header class="wtis-tournament__header">
			<div class="container">
				<p class="wtis-tournament__label"><?php esc_html_e( '2026 Predictions', 'wellthiissports-child' ); ?></p>
				<h1 class="wtis-tournament__title"><?php echo esc_html( $tournament_title ); ?></h1>
			</div>
		</header>

		<div class="wtis-home__inner">
			<?php if ( $tournament_query->have_posts() ) : ?>
			<section class="wtis-home-mid" aria-label="<?php esc_attr_e( 'Tournament matchups', 'wellthiissports-child' ); ?>">
				<div class="wtis-home-dark wtis-home-dark--mid">
					<div class="wtis-home-mid__grid">
						<?php while ( $tournament_query->have_posts() ) : $tournament_query->the_post(); ?>
							<?php $m = wtis_home_matchup_payload( get_the_ID() ); ?>
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
									<h2 class="wtis-home-mid-card__title"><?php echo esc_html( $m['title_line'] ); ?></h2>
									<?php if ( $m['date_display'] ) : ?>
									<p class="wtis-home__date"><?php echo esc_html( $m['date_display'] ); ?></p>
									<?php endif; ?>
								</div>
							</a>
						</article>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				</div>
			</section>
			<?php else : ?>
			<section class="wtis-home__section">
				<p class="wtis-no-matchups"><?php esc_html_e( 'No matchups published yet. Check back soon.', 'wellthiissports-child' ); ?></p>
			</section>
			<?php endif; ?>
		</div>

	</main>
</div>

<?php get_footer(); ?>
