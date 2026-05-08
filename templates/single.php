<?php
/**
 * single.php
 * Well This Is Sports — prediction detail page.
 *
 * Sprint 2: Cursor builds full design from approved tokens.
 * This is a functional scaffold displaying all prediction fields.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();

$post_id          = get_the_ID();
$team_home        = get_post_meta( $post_id, 'wtis_team_home', true );
$team_away        = get_post_meta( $post_id, 'wtis_team_away', true );
$sport            = get_post_meta( $post_id, 'wtis_sport', true );
$league           = get_post_meta( $post_id, 'wtis_league', true );
$matchup_date     = get_post_meta( $post_id, 'wtis_matchup_date', true );
$winner           = get_post_meta( $post_id, 'wtis_prediction_winner', true );
$confidence       = (int) get_post_meta( $post_id, 'wtis_confidence_score', true );
$analysis         = get_post_meta( $post_id, 'wtis_analysis', true );
$factors_for      = get_post_meta( $post_id, 'wtis_factors_for', true );
$factors_against  = get_post_meta( $post_id, 'wtis_factors_against', true );
$actual_result    = get_post_meta( $post_id, 'wtis_actual_result', true );
$pred_correct     = get_post_meta( $post_id, 'wtis_prediction_correct', true );
$article_stage    = get_post_meta( $post_id, 'wtis_article_stage', true );
$feat_img         = get_the_post_thumbnail_url( $post_id, 'wtis-hero' );

$factors_for_list    = $factors_for    ? explode( '|', $factors_for )    : [];
$factors_against_list = $factors_against ? explode( '|', $factors_against ) : [];
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

    <!-- ── Article stage badge ────────────────────────────────── -->
    <?php if ( 'urgent_update' === $article_stage ) : ?>
    <div class="wtis-urgent-badge">URGENT UPDATE</div>
    <?php endif; ?>

    <!-- ── Hero ───────────────────────────────────────────────── -->
    <?php if ( $feat_img ) : ?>
    <div class="wtis-story-img">
      <img src="<?php echo esc_url( $feat_img ); ?>"
           alt="<?php echo esc_attr( $team_home . ' vs ' . $team_away ); ?>"
           loading="eager">
    </div>
    <?php endif; ?>

    <!-- ── Story content ──────────────────────────────────────── -->
    <div class="wtis-story">

      <!-- Meta -->
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

      <!-- Teams -->
      <h1 class="wtis-story__teams">
        <span class="wtis-story__team-home"><?php echo esc_html( $team_home ?: get_the_title() ); ?></span>
        <span class="wtis-story__vs">vs</span>
        <span class="wtis-story__team-away"><?php echo esc_html( $team_away ); ?></span>
      </h1>

      <!-- The Pick + Confidence Meter -->
      <?php if ( $winner ) : ?>
      <div class="wtis-prediction-block">
        <div class="wtis-prediction-block__pick">
          <span class="wtis-prediction-block__label">The Pick</span>
          <span class="wtis-prediction-block__winner"><?php echo esc_html( $winner ); ?></span>
        </div>

        <?php if ( $confidence ) : ?>
        <div class="wtis-confidence-meter wtis-confidence-meter--hero"
             aria-label="Confidence: <?php echo esc_attr( $confidence ); ?>%">
          <div class="wtis-confidence-meter__bar"
               style="--confidence: <?php echo esc_attr( $confidence ); ?>">
            <div class="wtis-confidence-meter__fill"></div>
          </div>
          <span class="wtis-confidence-meter__label"><?php echo esc_html( $confidence ); ?>% confidence</span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Result (post-game) -->
      <?php if ( '' !== $pred_correct && $actual_result ) : ?>
      <div class="wtis-result-block wtis-result-block--<?php echo $pred_correct ? 'correct' : 'incorrect'; ?>">
        <strong><?php echo $pred_correct ? 'Correct Prediction' : 'Incorrect Prediction'; ?></strong>
        — Final result: <?php echo esc_html( $actual_result ); ?>
      </div>
      <?php endif; ?>

      <!-- Analysis -->
      <?php if ( $analysis ) : ?>
      <div class="wtis-analysis">
        <h2 class="wtis-analysis__heading">The Analysis</h2>
        <div class="wtis-analysis__body">
          <?php echo wp_kses_post( wpautop( $analysis ) ); ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Key Factors -->
      <?php if ( $factors_for_list || $factors_against_list ) : ?>
      <div class="wtis-factors">
        <?php if ( $factors_for_list ) : ?>
        <div class="wtis-factors__col wtis-factors__col--for">
          <h3 class="wtis-factors__heading">Why <?php echo esc_html( $winner ?: 'They' ); ?> Win</h3>
          <ul class="wtis-factors__list">
            <?php foreach ( $factors_for_list as $factor ) : ?>
            <li><?php echo esc_html( trim( $factor ) ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if ( $factors_against_list ) : ?>
        <div class="wtis-factors__col wtis-factors__col--against">
          <h3 class="wtis-factors__heading">Risk Factors</h3>
          <ul class="wtis-factors__list">
            <?php foreach ( $factors_against_list as $factor ) : ?>
            <li><?php echo esc_html( trim( $factor ) ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div><!-- .wtis-story -->

  </div><!-- .container -->
</div><!-- #page-wrapper -->

<?php get_footer(); ?>
