<?php
/**
 * front-page.php
 * Well This Is Sports — matchup grid homepage.
 *
 * Sprint 2: Cursor builds full design from approved tokens.
 * This is a functional scaffold showing the data structure.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ── Query: upcoming matchups ordered by matchup date ─────────
$matchup_args = [
    'posts_per_page'  => 12,
    'orderby'         => 'date',
    'order'           => 'DESC',
    'post_status'     => 'publish',
    'meta_query'      => [
        'relation' => 'OR',
        [
            'key'     => 'wtis_matchup_date',
            'compare' => 'EXISTS',
        ],
    ],
];
$matchup_query = new WP_Query( $matchup_args );

// ── Ledger data ───────────────────────────────────────────────
$ledger = get_option( 'wtis_ledger', [] );
?>

<div class="wrapper" id="page-wrapper">
  <div class="container">

    <!-- ── Masthead ────────────────────────────────────────────── -->
    <header class="wtis-masthead">
      <div class="wtis-masthead__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wtis-masthead__wordmark">
          Well This Is Sports
        </a>
        <nav class="wtis-masthead__nav" aria-label="Primary navigation">
          <!-- Sprint 2: sport navigation links -->
        </nav>
      </div>
    </header>

    <!-- ── Accuracy Ledger strip ───────────────────────────────── -->
    <?php if ( ! empty( $ledger ) ) : ?>
    <div class="wtis-ledger-strip">
      <?php foreach ( $ledger as $sport => $data ) : ?>
      <div class="wtis-ledger-strip__item">
        <span class="wtis-ledger-strip__sport"><?php echo esc_html( $sport ); ?></span>
        <span class="wtis-ledger-strip__stat">
          <?php echo esc_html( $data['accuracy'] ); ?>% (<?php echo esc_html( $data['correct'] . '/' . $data['total'] ); ?>)
        </span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Matchup Grid ─────────────────────────────────────────── -->
    <main id="content" class="wtis-matchup-grid">

      <?php if ( $matchup_query->have_posts() ) : ?>
        <?php while ( $matchup_query->have_posts() ) : $matchup_query->the_post(); ?>
          <?php
          $post_id        = get_the_ID();
          $team_home      = get_post_meta( $post_id, 'wtis_team_home', true );
          $team_away      = get_post_meta( $post_id, 'wtis_team_away', true );
          $sport          = get_post_meta( $post_id, 'wtis_sport', true );
          $league         = get_post_meta( $post_id, 'wtis_league', true );
          $winner         = get_post_meta( $post_id, 'wtis_prediction_winner', true );
          $confidence     = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
          $grade          = (int) get_post_meta( $post_id, 'wtis_prediction_grade', true );
          $matchup_date   = get_post_meta( $post_id, 'wtis_matchup_date', true );
          $pred_correct   = get_post_meta( $post_id, 'wtis_prediction_correct', true );
          $actual_result  = get_post_meta( $post_id, 'wtis_actual_result', true );
          $feat_img       = get_the_post_thumbnail_url( $post_id, 'wtis-card' );
          ?>
          <article class="wtis-matchup-card <?php echo $grade >= 8 ? 'wtis-matchup-card--featured' : ''; ?>">
            <a href="<?php the_permalink(); ?>" class="wtis-matchup-card__link">

              <?php if ( $feat_img ) : ?>
              <div class="wtis-matchup-card__img-wrap">
                <img class="wtis-matchup-card__img"
                     src="<?php echo esc_url( $feat_img ); ?>"
                     alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
                     loading="lazy">
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
                  <span class="wtis-matchup-card__team"><?php echo esc_html( $team_home ?: get_the_title() ); ?></span>
                  <span class="wtis-matchup-card__vs">vs</span>
                  <span class="wtis-matchup-card__team"><?php echo esc_html( $team_away ); ?></span>
                </div>

                <?php if ( $winner ) : ?>
                <div class="wtis-matchup-card__pick">
                  <span class="wtis-matchup-card__pick-label">The Pick</span>
                  <span class="wtis-matchup-card__pick-team"><?php echo esc_html( $winner ); ?></span>
                </div>
                <?php endif; ?>

                <?php if ( $confidence ) : ?>
                <div class="wtis-confidence-meter" aria-label="Confidence: <?php echo esc_attr( $confidence ); ?>%">
                  <div class="wtis-confidence-meter__bar"
                       style="--confidence: <?php echo esc_attr( $confidence ); ?>">
                    <div class="wtis-confidence-meter__fill"></div>
                  </div>
                  <span class="wtis-confidence-meter__label"><?php echo esc_html( $confidence ); ?>% confidence</span>
                </div>
                <?php endif; ?>

                <?php if ( '' !== $pred_correct && $actual_result ) : ?>
                <div class="wtis-matchup-card__result <?php echo $pred_correct ? 'wtis-matchup-card__result--correct' : 'wtis-matchup-card__result--incorrect'; ?>">
                  <?php echo $pred_correct ? '✓ Correct' : '✗ Incorrect'; ?>
                  — <?php echo esc_html( $actual_result ); ?>
                </div>
                <?php endif; ?>

              </div>
            </a>
          </article>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <p class="wtis-no-matchups">No matchups yet. Check back soon.</p>
      <?php endif; ?>

    </main><!-- #content -->

  </div><!-- .container -->
</div><!-- #page-wrapper -->

<?php get_footer(); ?>
