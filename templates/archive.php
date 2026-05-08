<?php
/**
 * archive.php
 * Well This Is Sports — sport/league archive template.
 *
 * Sprint 2: Cursor builds full design from approved tokens.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sport    = get_query_var( 'category_name' ) ?: '';
$ledger   = get_option( 'wtis_ledger', [] );
$sport_ledger = isset( $ledger[ $sport ] ) ? $ledger[ $sport ] : null;
?>

<div class="wrapper" id="page-wrapper">
  <div class="container">

    <!-- ── Masthead ───────────────────────────────────────────── -->
    <header class="wtis-masthead">
      <div class="wtis-masthead__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wtis-masthead__wordmark">
          Well This Is Sports
        </a>
      </div>
    </header>

    <!-- ── Archive header ─────────────────────────────────────── -->
    <div class="wtis-archive-header">
      <h1 class="wtis-archive-header__title">
        <?php
        if ( is_category() ) {
            single_cat_title();
        } elseif ( is_tag() ) {
            single_tag_title();
        } else {
            esc_html_e( 'Archive', 'wellthiissports-child' );
        }
        ?>
      </h1>

      <?php if ( $sport_ledger ) : ?>
      <div class="wtis-ledger-block">
        <span class="wtis-ledger-block__label">Accuracy</span>
        <span class="wtis-ledger-block__stat">
          <?php echo esc_html( $sport_ledger['accuracy'] ); ?>%
          (<?php echo esc_html( $sport_ledger['correct'] . '/' . $sport_ledger['total'] ); ?> correct)
        </span>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Matchup list ───────────────────────────────────────── -->
    <main id="content" class="wtis-archive-grid">
      <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
          <?php
          $post_id    = get_the_ID();
          $team_home  = get_post_meta( $post_id, 'wtis_team_home', true );
          $team_away  = get_post_meta( $post_id, 'wtis_team_away', true );
          $winner     = get_post_meta( $post_id, 'wtis_prediction_winner', true );
          $confidence = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
          $feat_img   = get_the_post_thumbnail_url( $post_id, 'wtis-card' );
          ?>
          <article class="wtis-archive-card">
            <a href="<?php the_permalink(); ?>" class="wtis-archive-card__link">
              <?php if ( $feat_img ) : ?>
              <img class="wtis-archive-card__img"
                   src="<?php echo esc_url( $feat_img ); ?>"
                   alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
                   loading="lazy">
              <?php endif; ?>
              <div class="wtis-archive-card__body">
                <div class="wtis-archive-card__teams">
                  <?php echo esc_html( $team_home ?: get_the_title() ); ?>
                  <?php if ( $team_away ) echo ' vs ' . esc_html( $team_away ); ?>
                </div>
                <?php if ( $winner && $confidence ) : ?>
                <div class="wtis-archive-card__pick">
                  <?php echo esc_html( $winner ); ?> — <?php echo esc_html( $confidence ); ?>%
                </div>
                <?php endif; ?>
              </div>
            </a>
          </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
      <?php else : ?>
        <p><?php esc_html_e( 'No matchups found.', 'wellthiissports-child' ); ?></p>
      <?php endif; ?>
    </main>

  </div><!-- .container -->
</div><!-- #page-wrapper -->

<?php get_footer(); ?>
