<?php
/**
 * pipeline-api.php
 * Well This Is Sports — AI pipeline REST API endpoints.
 *
 * Endpoints:
 *   POST   /wp-json/wtis/v1/matchups                    — create matchup
 *   PATCH  /wp-json/wtis/v1/matchups/{id}               — update matchup fields
 *   PATCH  /wp-json/wtis/v1/matchups/{id}/taxonomies    — assign taxonomy terms
 *   POST   /wp-json/wtis/v1/matchups/{id}/image         — upload featured image
 *   PATCH  /wp-json/wtis/v1/matchups/{id}/status        — set draft/publish
 *   PATCH  /wp-json/wtis/v1/matchups/{id}/result        — post-game result update
 *   GET    /wp-json/wtis/v1/status                      — pipeline health check
 *   GET    /wp-json/wtis/v1/ledger                      — accuracy ledger per sport
 *
 * Auth: X-WTIS-Key header, key stored in WP option wtis_pipeline_api_key
 */

defined( 'ABSPATH' ) || exit;

// ── Register REST routes ─────────────────────────────────────

add_action( 'rest_api_init', 'wtis_register_pipeline_routes' );

function wtis_register_pipeline_routes() {

    // POST — create matchup
    register_rest_route( 'wtis/v1', '/matchups', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'wtis_pipeline_create_matchup',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => wtis_pipeline_matchup_args(),
    ] );

    // PATCH — update matchup fields
    register_rest_route( 'wtis/v1', '/matchups/(?P<id>\d+)', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'wtis_pipeline_update_matchup',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => wtis_pipeline_matchup_args(),
    ] );

    // PATCH — assign taxonomy terms
    register_rest_route( 'wtis/v1', '/matchups/(?P<id>\d+)/taxonomies', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'wtis_pipeline_set_taxonomies',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => [
            'tournament_slug' => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_title',
                'default'           => '',
            ],
            'sport_slug' => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_title',
                'default'           => '',
            ],
        ],
    ] );

    // POST — upload featured image
    register_rest_route( 'wtis/v1', '/matchups/(?P<id>\d+)/image', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'wtis_pipeline_upload_image',
        'permission_callback' => 'wtis_pipeline_auth',
    ] );

    // PATCH — set draft/publish status
    register_rest_route( 'wtis/v1', '/matchups/(?P<id>\d+)/status', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'wtis_pipeline_set_status',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => [
            'post_status' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'enum'              => [ 'draft', 'publish', 'trash' ],
            ],
        ],
    ] );

    // PATCH — post-game result update
    register_rest_route( 'wtis/v1', '/matchups/(?P<id>\d+)/result', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'wtis_pipeline_update_result',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => [
            'actual_result'      => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'prediction_correct' => [
                'required' => true,
                'type'     => 'boolean',
            ],
        ],
    ] );

    // GET — pipeline health check
    register_rest_route( 'wtis/v1', '/status', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'wtis_pipeline_status',
        'permission_callback' => 'wtis_pipeline_auth',
    ] );

    // POST — create guide
    register_rest_route( 'wtis/v1', '/guides', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'wtis_pipeline_create_guide',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => wtis_pipeline_guide_args(),
    ] );

    // PATCH — update guide fields
    register_rest_route( 'wtis/v1', '/guides/(?P<id>\d+)', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'wtis_pipeline_update_guide',
        'permission_callback' => 'wtis_pipeline_auth',
        'args'                => wtis_pipeline_guide_args(),
    ] );

    // POST — flush WP rewrite rules (pipeline auth required)
    register_rest_route( 'wtis/v1', '/flush-rewrites', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'wtis_pipeline_flush_rewrites',
        'permission_callback' => 'wtis_pipeline_auth',
    ] );

    // GET — accuracy ledger per sport
    register_rest_route( 'wtis/v1', '/ledger', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'wtis_pipeline_ledger',
        'permission_callback' => '__return_true', // public read
    ] );
}

// ── Auth ─────────────────────────────────────────────────────

function wtis_pipeline_auth( WP_REST_Request $request ) {

    // WP Application Password
    if ( is_user_logged_in() && current_user_can( 'publish_posts' ) ) {
        return true;
    }

    // Custom API key via X-WTIS-Key header
    $header_key = $request->get_header( 'X-WTIS-Key' );
    $stored_key = get_option( 'wtis_pipeline_api_key', '' );

    if ( $stored_key && hash_equals( $stored_key, (string) $header_key ) ) {
        return true;
    }

    return new WP_Error(
        'rest_forbidden',
        'Pipeline authentication failed.',
        [ 'status' => 401 ]
    );
}

// ── Argument schema ──────────────────────────────────────────

function wtis_pipeline_matchup_args() {
    return [
        // Matchup identifiers
        'team_home' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'team_away' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'matchup_title' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'sport' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'league' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'matchup_date' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],

        // Prediction
        'prediction_winner' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'confidence_score' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ],
        'analysis' => [
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default'           => '',
        ],
        'prediction_grade' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ],

        // Factors
        'factors_for' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'factors_against' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'what_nobody_saying' => [
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default'           => '',
        ],

        // Article lifecycle
        'article_stage' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'preview',
            'enum'              => [ 'preview', 'matchup', 'urgent_update' ],
        ],
        'image_brief_scene' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'headline_personality' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'headline_seo' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],

        // Taxonomy slugs
        'tournament_slug' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_title',
            'default'           => '',
        ],
        'sport_slug' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_title',
            'default'           => '',
        ],

        // Pipeline metadata
        'ingested_at' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'featured_image_url' => [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'format'            => 'uri',
            'default'           => '',
        ],
        'featured_attachment_id' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ],

        // Post
        'post_author' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ],
        'post_status' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'draft',
            'enum'              => [ 'draft', 'publish' ],
        ],
    ];
}

// ── Create matchup ───────────────────────────────────────────

function wtis_pipeline_create_matchup( WP_REST_Request $request ) {
    $params = $request->get_params();

    $post_id = wp_insert_post( [
        'post_title'   => $params['matchup_title'],
        'post_status'  => $params['post_status'] ?? 'draft',
        'post_type'    => 'wtis_matchup',
        'post_author'  => $params['post_author'] ?? 1,
        'post_content' => '',
        'post_excerpt' => sanitize_text_field(
            $params['team_home'] . ' vs ' . $params['team_away'] . ' — ' . $params['sport']
        ),
    ], true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error(
            'rest_insert_failed',
            $post_id->get_error_message(),
            [ 'status' => 500 ]
        );
    }

    wtis_save_matchup_meta( $post_id, $params );

    // Set featured image
    if ( ! empty( $params['featured_attachment_id'] ) ) {
        set_post_thumbnail( $post_id, (int) $params['featured_attachment_id'] );
    } elseif ( ! empty( $params['featured_image_url'] ) ) {
        wtis_sideload_featured_image( $post_id, $params['featured_image_url'], $params['matchup_title'] );
    }

    $attachment_id = (int) get_post_thumbnail_id( $post_id );

    return new WP_REST_Response( [
        'success'       => true,
        'post_id'       => $post_id,
        'attachment_id' => $attachment_id,
        'edit_url'      => get_edit_post_link( $post_id, 'raw' ),
        'permalink'     => get_permalink( $post_id ),
        'status'        => get_post_status( $post_id ),
    ], 201 );
}

// ── Update matchup ───────────────────────────────────────────

function wtis_pipeline_update_matchup( WP_REST_Request $request ) {
    $post_id = (int) $request->get_param( 'id' );
    $params  = $request->get_params();

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Matchup not found.', [ 'status' => 404 ] );
    }

    wtis_save_matchup_meta( $post_id, $params );

    if ( ! empty( $params['matchup_title'] ) ) {
        wp_update_post( [
            'ID'         => $post_id,
            'post_title' => $params['matchup_title'],
        ] );
    }

    if ( ! empty( $params['featured_attachment_id'] ) ) {
        set_post_thumbnail( $post_id, (int) $params['featured_attachment_id'] );
    } elseif ( ! empty( $params['featured_image_url'] ) ) {
        wtis_sideload_featured_image( $post_id, $params['featured_image_url'], get_the_title( $post_id ) );
    }

    return new WP_REST_Response( [
        'success' => true,
        'post_id' => $post_id,
    ], 200 );
}

// ── Assign taxonomy terms ─────────────────────────────────────

function wtis_pipeline_set_taxonomies( WP_REST_Request $request ) {
    $post_id         = (int) $request->get_param( 'id' );
    $tournament_slug = $request->get_param( 'tournament_slug' );
    $sport_slug      = $request->get_param( 'sport_slug' );

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Matchup not found.', [ 'status' => 404 ] );
    }

    $assigned = [];

    if ( $tournament_slug ) {
        $term_id = wtis_get_or_create_term( $tournament_slug, 'wtis_tournament' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_tournament' );
            $assigned['tournament'] = $tournament_slug;
        }
    }

    if ( $sport_slug ) {
        $term_id = wtis_get_or_create_term( $sport_slug, 'wtis_sport' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_sport' );
            $assigned['sport'] = $sport_slug;
        }
    }

    return new WP_REST_Response( [
        'success'  => true,
        'post_id'  => $post_id,
        'assigned' => $assigned,
    ], 200 );
}

// ── Set post status ──────────────────────────────────────────

function wtis_pipeline_set_status( WP_REST_Request $request ) {
    $post_id = (int) $request->get_param( 'id' );
    $status  = $request->get_param( 'post_status' );

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Matchup not found.', [ 'status' => 404 ] );
    }

    $result = wp_update_post( [ 'ID' => $post_id, 'post_status' => $status ], true );

    if ( is_wp_error( $result ) ) {
        return new WP_Error( 'update_failed', $result->get_error_message(), [ 'status' => 500 ] );
    }

    return new WP_REST_Response( [
        'success' => true,
        'post_id' => $post_id,
        'status'  => get_post_status( $post_id ),
    ], 200 );
}

// ── Post-game result update ───────────────────────────────────

function wtis_pipeline_update_result( WP_REST_Request $request ) {
    $post_id            = (int) $request->get_param( 'id' );
    $actual_result      = $request->get_param( 'actual_result' );
    $prediction_correct = (bool) $request->get_param( 'prediction_correct' );

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Matchup not found.', [ 'status' => 404 ] );
    }

    update_post_meta( $post_id, 'wtis_actual_result',      $actual_result );
    update_post_meta( $post_id, 'wtis_prediction_correct', $prediction_correct );

    // Update ledger aggregate for this sport
    $sport = get_post_meta( $post_id, 'wtis_sport', true );
    if ( $sport ) {
        wtis_update_ledger( $sport, $prediction_correct );
    }

    return new WP_REST_Response( [
        'success'            => true,
        'post_id'            => $post_id,
        'actual_result'      => $actual_result,
        'prediction_correct' => $prediction_correct,
    ], 200 );
}

// ── Upload featured image ─────────────────────────────────────

function wtis_pipeline_upload_image( WP_REST_Request $request ) {
    $post_id = (int) $request->get_param( 'id' );

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Matchup not found.', [ 'status' => 404 ] );
    }

    $files = $request->get_file_params();
    if ( empty( $files['image'] ) || empty( $files['image']['tmp_name'] ) ) {
        return new WP_Error( 'rest_missing_file', 'No image file provided.', [ 'status' => 400 ] );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $file   = $files['image'];
    $upload = wp_handle_upload( $file, [ 'test_form' => false ] );

    if ( isset( $upload['error'] ) ) {
        return new WP_Error( 'upload_failed', $upload['error'], [ 'status' => 500 ] );
    }

    $attachment    = [
        'post_mime_type' => $upload['type'],
        'post_title'     => get_the_title( $post_id ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];
    $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

    if ( is_wp_error( $attachment_id ) ) {
        return new WP_Error( 'attachment_failed', $attachment_id->get_error_message(), [ 'status' => 500 ] );
    }

    $meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    wp_update_attachment_metadata( $attachment_id, $meta );
    set_post_thumbnail( $post_id, $attachment_id );

    return new WP_REST_Response( [
        'success'       => true,
        'attachment_id' => $attachment_id,
        'url'           => $upload['url'],
    ], 201 );
}

// ── Pipeline status ───────────────────────────────────────────

function wtis_pipeline_status( WP_REST_Request $request ) {
    $post_counts    = wp_count_posts( 'wtis_matchup' );
    $legacy_counts  = wp_count_posts( 'post' );
    $draft_count    = (int) $post_counts->draft + (int) $legacy_counts->draft;
    $publish_count  = (int) $post_counts->publish + (int) $legacy_counts->publish;

    $last_pub = get_posts( [
        'post_type'      => [ 'wtis_matchup', 'post' ],
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    return new WP_REST_Response( [
        'status'          => 'ok',
        'drafts_pending'  => $draft_count,
        'published_total' => $publish_count,
        'last_published'  => $last_pub ? $last_pub[0]->post_date : null,
        'api_version'     => 'wtis/v1',
        'site_url'        => get_site_url(),
    ], 200 );
}

// ── Guide argument schema ─────────────────────────────────────

function wtis_pipeline_guide_args() {
    return [
        'title' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        'content' => [
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default'           => '',
        ],
        'headline_personality' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'headline_seo' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'venue_name' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'venue_address' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'venue_place_id' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'map_embed' => [
            'type'    => 'boolean',
            'default' => false,
        ],
        'instagram_account' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ],
        'instagram_post_url' => [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'format'            => 'uri',
            'default'           => '',
        ],
        'tournament_slug' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_title',
            'default'           => '',
        ],
        'sport_slug' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_title',
            'default'           => '',
        ],
        'post_status' => [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'draft',
            'enum'              => [ 'draft', 'publish' ],
        ],
        'post_author' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ],
        'featured_attachment_id' => [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ],
    ];
}

// ── Create guide ──────────────────────────────────────────────

function wtis_pipeline_create_guide( WP_REST_Request $request ) {
    $params = $request->get_params();

    $post_id = wp_insert_post( [
        'post_title'   => $params['title'],
        'post_content' => $params['content'] ?? '',
        'post_status'  => $params['post_status'] ?? 'draft',
        'post_type'    => 'wtis_guide',
        'post_author'  => $params['post_author'] ?? 1,
    ], true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'rest_insert_failed', $post_id->get_error_message(), [ 'status' => 500 ] );
    }

    wtis_save_guide_meta( $post_id, $params );

    if ( ! empty( $params['featured_attachment_id'] ) ) {
        set_post_thumbnail( $post_id, (int) $params['featured_attachment_id'] );
    }

    return new WP_REST_Response( [
        'success'   => true,
        'post_id'   => $post_id,
        'edit_url'  => get_edit_post_link( $post_id, 'raw' ),
        'permalink' => get_permalink( $post_id ),
        'status'    => get_post_status( $post_id ),
    ], 201 );
}

// ── Update guide ──────────────────────────────────────────────

function wtis_pipeline_update_guide( WP_REST_Request $request ) {
    $post_id = (int) $request->get_param( 'id' );
    $params  = $request->get_params();

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'rest_not_found', 'Guide not found.', [ 'status' => 404 ] );
    }

    $update = [ 'ID' => $post_id ];
    if ( isset( $params['title'] ) )   $update['post_title']   = $params['title'];
    if ( isset( $params['content'] ) ) $update['post_content'] = $params['content'];
    if ( ! empty( $update ) ) {
        wp_update_post( $update );
    }

    wtis_save_guide_meta( $post_id, $params );

    if ( ! empty( $params['featured_attachment_id'] ) ) {
        set_post_thumbnail( $post_id, (int) $params['featured_attachment_id'] );
    }

    return new WP_REST_Response( [ 'success' => true, 'post_id' => $post_id ], 200 );
}

// ── Save guide meta ───────────────────────────────────────────

function wtis_save_guide_meta( int $post_id, array $params ): void {
    $meta_map = [
        'headline_personality' => 'wtis_headline_personality',
        'headline_seo'         => 'wtis_headline_seo',
        'venue_name'           => 'wtis_guide_venue_name',
        'venue_address'        => 'wtis_guide_venue_address',
        'venue_place_id'       => 'wtis_guide_venue_place_id',
        'instagram_account'    => 'wtis_guide_instagram_account',
        'instagram_post_url'   => 'wtis_guide_instagram_post_url',
    ];

    foreach ( $meta_map as $param_key => $meta_key ) {
        if ( isset( $params[ $param_key ] ) ) {
            update_post_meta( $post_id, $meta_key, $params[ $param_key ] );
        }
    }

    // Boolean: only save if explicitly passed
    if ( array_key_exists( 'map_embed', $params ) ) {
        update_post_meta( $post_id, 'wtis_guide_map_embed', (bool) $params['map_embed'] );
    }

    // Taxonomy terms
    if ( ! empty( $params['tournament_slug'] ) ) {
        $term_id = wtis_get_or_create_term( $params['tournament_slug'], 'wtis_tournament' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_tournament' );
        }
    }
    if ( ! empty( $params['sport_slug'] ) ) {
        $term_id = wtis_get_or_create_term( $params['sport_slug'], 'wtis_sport' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_sport' );
        }
    }
}

// ── Flush rewrite rules ───────────────────────────────────────

function wtis_pipeline_flush_rewrites( WP_REST_Request $request ) {
    flush_rewrite_rules();
    return new WP_REST_Response( [ 'success' => true, 'message' => 'Rewrite rules flushed.' ], 200 );
}

// ── Accuracy ledger ───────────────────────────────────────────

function wtis_pipeline_ledger( WP_REST_Request $request ) {
    $ledger = get_option( 'wtis_ledger', [] );

    return new WP_REST_Response( [
        'ledger'     => $ledger,
        'updated_at' => get_option( 'wtis_ledger_updated_at', null ),
    ], 200 );
}

// ── Helpers ───────────────────────────────────────────────────

function wtis_save_matchup_meta( int $post_id, array $params ): void {
    $meta_map = [
        'team_home'            => 'wtis_team_home',
        'team_away'            => 'wtis_team_away',
        'matchup_title'        => 'wtis_matchup_title',
        'sport'                => 'wtis_sport',
        'league'               => 'wtis_league',
        'matchup_date'         => 'wtis_matchup_date',
        'prediction_winner'    => 'wtis_prediction_winner',
        'confidence_score'     => 'wtis_confidence_score',
        'analysis'             => 'wtis_analysis',
        'prediction_grade'     => 'wtis_prediction_grade',
        'ingested_at'          => 'wtis_ingested_at',
        'actual_result'        => 'wtis_actual_result',
        'prediction_correct'   => 'wtis_prediction_correct',
        'factors_for'          => 'wtis_factors_for',
        'factors_against'      => 'wtis_factors_against',
        'what_nobody_saying'   => 'wtis_what_nobody_saying',
        'article_stage'        => 'wtis_article_stage',
        'image_brief_scene'    => 'wtis_image_brief_scene',
        'headline_personality' => 'wtis_headline_personality',
        'headline_seo'         => 'wtis_headline_seo',
    ];

    foreach ( $meta_map as $param_key => $meta_key ) {
        if ( isset( $params[ $param_key ] ) ) {
            update_post_meta( $post_id, $meta_key, $params[ $param_key ] );
        }
    }

    update_post_meta( $post_id, 'wtis_ai_generated', true );

    // Assign taxonomy terms if slugs provided
    if ( ! empty( $params['tournament_slug'] ) ) {
        $term_id = wtis_get_or_create_term( $params['tournament_slug'], 'wtis_tournament' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_tournament' );
        }
    }
    if ( ! empty( $params['sport_slug'] ) ) {
        $term_id = wtis_get_or_create_term( $params['sport_slug'], 'wtis_sport' );
        if ( $term_id ) {
            wp_set_post_terms( $post_id, [ $term_id ], 'wtis_sport' );
        }
    }
}

function wtis_get_or_create_term( string $slug, string $taxonomy ): int {
    $existing = get_term_by( 'slug', $slug, $taxonomy );
    if ( $existing ) {
        return (int) $existing->term_id;
    }
    $label  = ucwords( str_replace( '-', ' ', $slug ) );
    $result = wp_insert_term( $label, $taxonomy, [ 'slug' => $slug ] );
    if ( is_wp_error( $result ) ) {
        return 0;
    }
    return (int) $result['term_id'];
}

function wtis_sideload_featured_image( int $post_id, string $url, string $title ): void {
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );

    if ( ! is_wp_error( $attachment_id ) ) {
        set_post_thumbnail( $post_id, $attachment_id );
    }
}

function wtis_update_ledger( string $sport, bool $correct ): void {
    $ledger = get_option( 'wtis_ledger', [] );

    // Normalize to WP slug format so ledger keys match category archive slugs.
    // e.g. "World Cup" → "world-cup", "NFL" → "nfl"
    $sport = sanitize_title( $sport );

    if ( ! isset( $ledger[ $sport ] ) ) {
        $ledger[ $sport ] = [
            'total'    => 0,
            'correct'  => 0,
            'accuracy' => 0.0,
            'streak'   => 0,
        ];
    }

    $ledger[ $sport ]['total']++;

    if ( $correct ) {
        $ledger[ $sport ]['correct']++;
        $ledger[ $sport ]['streak'] = max( 0, $ledger[ $sport ]['streak'] ) + 1;
    } else {
        $ledger[ $sport ]['streak'] = min( 0, $ledger[ $sport ]['streak'] ) - 1;
    }

    $ledger[ $sport ]['accuracy'] = round(
        ( $ledger[ $sport ]['correct'] / $ledger[ $sport ]['total'] ) * 100,
        1
    );

    update_option( 'wtis_ledger', $ledger );
    update_option( 'wtis_ledger_updated_at', gmdate( 'c' ) );
}
