<?php
/**
 * custom-fields.php
 * Well This Is Sports — prediction meta box.
 *
 * Adds a meta box to the post editor showing all prediction
 * fields for editorial review and override.
 */

defined( 'ABSPATH' ) || exit;

// ── Register meta box ────────────────────────────────────────

add_action( 'add_meta_boxes', 'wtis_add_prediction_meta_box' );
function wtis_add_prediction_meta_box() {
    add_meta_box(
        'wtis-prediction',
        'Prediction Data',
        'wtis_render_prediction_meta_box',
        'post',
        'normal',
        'high'
    );
}

// ── Render meta box ──────────────────────────────────────────

function wtis_render_prediction_meta_box( WP_Post $post ) {
    wp_nonce_field( 'wtis_save_prediction', 'wtis_prediction_nonce' );

    $team_home         = get_post_meta( $post->ID, 'wtis_team_home', true );
    $team_away         = get_post_meta( $post->ID, 'wtis_team_away', true );
    $matchup_title     = get_post_meta( $post->ID, 'wtis_matchup_title', true );
    $sport             = get_post_meta( $post->ID, 'wtis_sport', true );
    $league            = get_post_meta( $post->ID, 'wtis_league', true );
    $matchup_date      = get_post_meta( $post->ID, 'wtis_matchup_date', true );
    $prediction_winner = get_post_meta( $post->ID, 'wtis_prediction_winner', true );
    $confidence_score  = get_post_meta( $post->ID, 'wtis_confidence_score', true );
    $prediction_grade  = get_post_meta( $post->ID, 'wtis_prediction_grade', true );
    $article_stage     = get_post_meta( $post->ID, 'wtis_article_stage', true );
    $factors_for       = get_post_meta( $post->ID, 'wtis_factors_for', true );
    $factors_against   = get_post_meta( $post->ID, 'wtis_factors_against', true );
    $actual_result     = get_post_meta( $post->ID, 'wtis_actual_result', true );
    $pred_correct      = get_post_meta( $post->ID, 'wtis_prediction_correct', true );
    $ai_generated      = get_post_meta( $post->ID, 'wtis_ai_generated', true );
    $ingested_at       = get_post_meta( $post->ID, 'wtis_ingested_at', true );

    echo '<style>
        .wtis-mb { margin: 0; }
        .wtis-mb-meta { display:flex; gap:16px; margin-bottom:16px; padding:8px 12px;
            background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;
            font-size:12px; color:#666; }
        .wtis-mb-section { border:1px solid #e0e0e0; border-radius:4px;
            margin-bottom:16px; overflow:hidden; }
        .wtis-mb-section-header { padding:8px 12px; background:#f9f9f9;
            border-bottom:1px solid #e0e0e0; font-weight:600; font-size:12px;
            text-transform:uppercase; letter-spacing:0.05em; color:#444; }
        .wtis-mb-section-body { padding:12px; display:grid;
            grid-template-columns:1fr 1fr; gap:12px; }
        .wtis-mb-field label { display:block; font-size:11px; font-weight:600;
            text-transform:uppercase; letter-spacing:0.05em; color:#555;
            margin-bottom:4px; }
        .wtis-mb-field input, .wtis-mb-field textarea, .wtis-mb-field select { width:100%; }
        .wtis-mb-field textarea { height:80px; resize:vertical; }
        .wtis-mb-field--full { grid-column:1/-1; }
        .wtis-mb-correct { color:#2E7D32; font-weight:600; }
        .wtis-mb-incorrect { color:#C62828; font-weight:600; }
    </style>';

    echo '<div class="wtis-mb">';

    // Pipeline metadata row
    echo '<div class="wtis-mb-meta">';
    echo '<span>' . ( $ai_generated ? '🤖 AI generated' : '✏️ Manual' ) . '</span>';
    if ( $article_stage ) echo '<span>Stage: ' . esc_html( $article_stage ) . '</span>';
    if ( $ingested_at )   echo '<span>Ingested: ' . esc_html( $ingested_at ) . '</span>';
    if ( '' !== $pred_correct ) {
        $label = $pred_correct ? '<span class="wtis-mb-correct">✓ Correct</span>' : '<span class="wtis-mb-incorrect">✗ Incorrect</span>';
        echo '<span>Result: ' . $label . '</span>'; // phpcs:ignore
    }
    echo '</div>';

    // ── Matchup section ──────────────────────────────────────
    ?>
    <div class="wtis-mb-section">
      <div class="wtis-mb-section-header">Matchup</div>
      <div class="wtis-mb-section-body">
        <div class="wtis-mb-field">
          <label for="wtis_team_home">Home Team</label>
          <input type="text" id="wtis_team_home" name="wtis_team_home"
                 value="<?php echo esc_attr( $team_home ); ?>">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_team_away">Away Team</label>
          <input type="text" id="wtis_team_away" name="wtis_team_away"
                 value="<?php echo esc_attr( $team_away ); ?>">
        </div>
        <div class="wtis-mb-field wtis-mb-field--full">
          <label for="wtis_matchup_title">Matchup Title</label>
          <input type="text" id="wtis_matchup_title" name="wtis_matchup_title"
                 value="<?php echo esc_attr( $matchup_title ); ?>">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_sport">Sport</label>
          <input type="text" id="wtis_sport" name="wtis_sport"
                 value="<?php echo esc_attr( $sport ); ?>"
                 placeholder="World Cup, NFL, NBA…">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_league">League / Tournament</label>
          <input type="text" id="wtis_league" name="wtis_league"
                 value="<?php echo esc_attr( $league ); ?>">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_matchup_date">Matchup Date (ISO)</label>
          <input type="text" id="wtis_matchup_date" name="wtis_matchup_date"
                 value="<?php echo esc_attr( $matchup_date ); ?>"
                 placeholder="2026-06-11T20:00:00Z">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_article_stage">Article Stage</label>
          <select id="wtis_article_stage" name="wtis_article_stage">
            <option value="preview"       <?php selected( $article_stage, 'preview' ); ?>>Preview</option>
            <option value="matchup"       <?php selected( $article_stage, 'matchup' ); ?>>Matchup</option>
            <option value="urgent_update" <?php selected( $article_stage, 'urgent_update' ); ?>>Urgent Update</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ── Prediction section ───────────────────────────────── -->
    <div class="wtis-mb-section">
      <div class="wtis-mb-section-header">Prediction</div>
      <div class="wtis-mb-section-body">
        <div class="wtis-mb-field">
          <label for="wtis_prediction_winner">Predicted Winner</label>
          <input type="text" id="wtis_prediction_winner" name="wtis_prediction_winner"
                 value="<?php echo esc_attr( $prediction_winner ); ?>">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_confidence_score">Confidence Score (1-100)</label>
          <input type="number" id="wtis_confidence_score" name="wtis_confidence_score"
                 value="<?php echo esc_attr( $confidence_score ); ?>"
                 min="1" max="100">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_prediction_grade">Prediction Grade (1-100)</label>
          <input type="number" id="wtis_prediction_grade" name="wtis_prediction_grade"
                 value="<?php echo esc_attr( $prediction_grade ); ?>"
                 min="1" max="100">
        </div>
        <div class="wtis-mb-field wtis-mb-field--full">
          <label for="wtis_factors_for">Factors For (pipe-separated)</label>
          <input type="text" id="wtis_factors_for" name="wtis_factors_for"
                 value="<?php echo esc_attr( $factors_for ); ?>"
                 placeholder="Factor one|Factor two|Factor three">
        </div>
        <div class="wtis-mb-field wtis-mb-field--full">
          <label for="wtis_factors_against">Factors Against (pipe-separated)</label>
          <input type="text" id="wtis_factors_against" name="wtis_factors_against"
                 value="<?php echo esc_attr( $factors_against ); ?>"
                 placeholder="Risk one|Risk two|Risk three">
        </div>
      </div>
    </div>

    <!-- ── Post-game section ────────────────────────────────── -->
    <div class="wtis-mb-section">
      <div class="wtis-mb-section-header">Post-Game Result</div>
      <div class="wtis-mb-section-body">
        <div class="wtis-mb-field">
          <label for="wtis_actual_result">Actual Result</label>
          <input type="text" id="wtis_actual_result" name="wtis_actual_result"
                 value="<?php echo esc_attr( $actual_result ); ?>"
                 placeholder="e.g. Argentina 3-0 France">
        </div>
        <div class="wtis-mb-field">
          <label for="wtis_prediction_correct">Prediction Correct?</label>
          <select id="wtis_prediction_correct" name="wtis_prediction_correct">
            <option value="">— not set —</option>
            <option value="1" <?php selected( $pred_correct, '1' ); ?>>Yes</option>
            <option value="0" <?php selected( $pred_correct, '0' ); ?>>No</option>
          </select>
        </div>
      </div>
    </div>
    <?php

    echo '</div>'; // .wtis-mb
}

// ── Save meta box ────────────────────────────────────────────

add_action( 'save_post', 'wtis_save_prediction_meta_box' );
function wtis_save_prediction_meta_box( int $post_id ) {
    if ( ! isset( $_POST['wtis_prediction_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['wtis_prediction_nonce'], 'wtis_save_prediction' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $string_fields = [
        'wtis_team_home', 'wtis_team_away', 'wtis_matchup_title',
        'wtis_sport', 'wtis_league', 'wtis_matchup_date',
        'wtis_prediction_winner', 'wtis_actual_result',
        'wtis_factors_for', 'wtis_factors_against',
        'wtis_article_stage',
    ];
    $int_fields = [
        'wtis_confidence_score', 'wtis_prediction_grade',
    ];

    foreach ( $string_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
    foreach ( $int_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
        }
    }

    if ( isset( $_POST['wtis_prediction_correct'] ) && '' !== $_POST['wtis_prediction_correct'] ) {
        update_post_meta( $post_id, 'wtis_prediction_correct', (bool) $_POST['wtis_prediction_correct'] );
    }
}
