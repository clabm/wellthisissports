<?php
/**
 * functions.php
 * Well This Is Sports — Understrap child theme functions.
 *
 * Handles:
 *   - Child theme styles and scripts
 *   - Custom post types: wtis_matchup, wtis_guide
 *   - Custom taxonomies: wtis_tournament, wtis_sport, wtis_content_type
 *   - Custom post meta registration (prediction fields)
 *   - WP REST API endpoint for AI pipeline
 *   - Newsletter AJAX handler
 *   - Theme setup and supports
 */

defined( 'ABSPATH' ) || exit;

// Load includes
require_once get_stylesheet_directory() . '/inc/custom-fields.php';
require_once get_stylesheet_directory() . '/inc/pipeline-api.php';

// Suppress "Proudly powered by WordPress" credit from Understrap footer
add_filter( 'understrap_footer_text', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );

// ── Template loader — redirect WP hierarchy to templates/ ────

add_filter( 'template_include', 'wtis_template_loader' );
function wtis_template_loader( $template ) {
    if ( is_front_page() ) {
        $found = locate_template( 'templates/front-page.php' );
        if ( $found ) return $found;
    }
    if ( is_singular( 'wtis_guide' ) ) {
        $found = locate_template( 'templates/single-wtis_guide.php' );
        if ( ! $found ) {
            $found = locate_template( 'templates/guide.php' );
        }
        if ( ! $found ) {
            $found = locate_template( 'templates/page.php' );
        }
        if ( $found ) {
            return $found;
        }
    }
    if ( is_single() ) {
        $found = locate_template( 'templates/single.php' );
        if ( $found ) return $found;
    }
    if ( is_tax( 'wtis_tournament' ) || is_tax( 'wtis_sport' ) ) {
        $found = locate_template( 'templates/tournament.php' );
        if ( $found ) return $found;
    }
    if ( is_archive() || is_category() ) {
        $found = locate_template( 'templates/archive.php' );
        if ( $found ) return $found;
    }
    if ( is_page( 'world-cup' ) ) {
        $found = locate_template( 'templates/tournament.php' );
        if ( $found ) return $found;
    }
    if ( is_page() && ! is_front_page() ) {
        $found = locate_template( 'templates/page.php' );
        if ( $found ) return $found;
    }
    return $template;
}

// ── Theme setup ─────────────────────────────────────────────

add_action( 'after_setup_theme', 'wtis_theme_setup' );
function wtis_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
    ] );
    add_theme_support( 'custom-logo' );

    add_image_size( 'wtis-hero',        1240, 697, true );
    add_image_size( 'wtis-card',        640,  360, true );
    add_image_size( 'wtis-home-square', 480,  480, true );
    add_image_size( 'wtis-thumbnail',   160,  140, true );

    register_nav_menus( [
        'primary' => __( 'Primary Navigation', 'wellthiissports-child' ),
        'footer'  => __( 'Footer Menu', 'wellthiissports-child' ),
    ] );
}

// ── Custom post types ────────────────────────────────────────

add_action( 'init', 'wtis_register_post_types' );
function wtis_register_post_types() {
    register_post_type( 'wtis_matchup', [
        'labels'       => [
            'name'          => __( 'Matchups', 'wellthiissports-child' ),
            'singular_name' => __( 'Matchup', 'wellthiissports-child' ),
            'add_new_item'  => __( 'Add New Matchup', 'wellthiissports-child' ),
            'edit_item'     => __( 'Edit Matchup', 'wellthiissports-child' ),
            'search_items'  => __( 'Search Matchups', 'wellthiissports-child' ),
            'not_found'     => __( 'No matchups found.', 'wellthiissports-child' ),
        ],
        'public'       => true,
        'has_archive'  => 'predictions',
        'rewrite'      => [ 'slug' => 'predictions', 'with_front' => false ],
        'supports'     => [ 'title', 'thumbnail', 'excerpt', 'custom-fields' ],
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-superhero-alt',
    ] );

    register_post_type( 'wtis_guide', [
        'labels'       => [
            'name'          => __( 'Guides', 'wellthiissports-child' ),
            'singular_name' => __( 'Guide', 'wellthiissports-child' ),
            'add_new_item'  => __( 'Add New Guide', 'wellthiissports-child' ),
            'edit_item'     => __( 'Edit Guide', 'wellthiissports-child' ),
        ],
        'public'             => true,
        'publicly_queryable' => true,
        'has_archive'        => false,
        'rewrite'            => [ 'slug' => 'guides', 'with_front' => false ],
        'supports'           => [ 'title', 'thumbnail', 'excerpt', 'editor', 'custom-fields' ],
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-book',
    ] );
}

// ── Custom taxonomies ────────────────────────────────────────

add_action( 'init', 'wtis_register_taxonomies' );
function wtis_register_taxonomies() {
    $post_types = [ 'wtis_matchup', 'wtis_guide' ];

    register_taxonomy( 'wtis_tournament', $post_types, [
        'labels'        => [
            'name'          => __( 'Tournaments', 'wellthiissports-child' ),
            'singular_name' => __( 'Tournament', 'wellthiissports-child' ),
            'all_items'     => __( 'All Tournaments', 'wellthiissports-child' ),
            'add_new_item'  => __( 'Add New Tournament', 'wellthiissports-child' ),
        ],
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => [ 'slug' => 'tournament', 'with_front' => false ],
    ] );

    register_taxonomy( 'wtis_sport', $post_types, [
        'labels'        => [
            'name'          => __( 'Sports', 'wellthiissports-child' ),
            'singular_name' => __( 'Sport', 'wellthiissports-child' ),
            'all_items'     => __( 'All Sports', 'wellthiissports-child' ),
        ],
        'hierarchical'  => false,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => [ 'slug' => 'sport', 'with_front' => false ],
    ] );

    register_taxonomy( 'wtis_content_type', $post_types, [
        'labels'        => [
            'name'          => __( 'Content Types', 'wellthiissports-child' ),
            'singular_name' => __( 'Content Type', 'wellthiissports-child' ),
        ],
        'hierarchical'  => false,
        'public'        => false,
        'show_ui'       => true,
        'show_in_rest'  => true,
        'rewrite'       => false,
    ] );
}

// ── Enqueue styles and scripts ───────────────────────────────

add_action( 'wp_enqueue_scripts', 'wtis_enqueue_assets' );
function wtis_enqueue_assets() {

    // Google Fonts — TBD Sprint 1, placeholder until tokens approved
    wp_enqueue_style(
        'wtis-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;600&family=Inter:wght@400;500&display=swap',
        [],
        null
    );

    // Parent theme (Understrap)
    wp_dequeue_style( 'understrap-styles' );
    wp_enqueue_style(
        'understrap-styles',
        get_template_directory_uri() . '/css/theme.min.css',
        [ 'wtis-fonts' ],
        wp_get_theme( 'understrap' )->get( 'Version' )
    );

    // Child theme design tokens
    wp_enqueue_style(
        'wtis-tokens',
        get_stylesheet_uri(),
        [ 'wtis-fonts' ],
        wp_get_theme()->get( 'Version' )
    );

    // Child theme compiled CSS
    wp_enqueue_style(
        'wtis-styles',
        get_stylesheet_directory_uri() . '/css/style.min.css',
        [ 'understrap-styles', 'wtis-tokens' ],
        wp_get_theme()->get( 'Version' )
    );

    // Prediction component JS
    wp_enqueue_script(
        'wtis-prediction',
        get_stylesheet_directory_uri() . '/js/prediction.js',
        [],
        wp_get_theme()->get( 'Version' ),
        true
    );

    // Pass WP data to JS
    wp_localize_script( 'wtis-prediction', 'wtisData', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'wtis_nl_nonce' ),
        'siteUrl'   => get_site_url(),
        'devMode'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
    ] );
}

// ── Custom post meta — prediction fields ─────────────────────

add_action( 'init', 'wtis_register_post_meta' );
function wtis_register_post_meta() {
    $string_args = [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ];
    $text_args = [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'wp_kses_post',
    ];
    $int_args = [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
    ];
    $bool_args = [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'boolean',
    ];

    foreach ( [ 'post', 'wtis_matchup', 'wtis_guide' ] as $post_type ) {
        // Matchup identifiers
        register_post_meta( $post_type, 'wtis_team_home',      $string_args );
        register_post_meta( $post_type, 'wtis_team_away',      $string_args );
        register_post_meta( $post_type, 'wtis_matchup_title',  $string_args );
        register_post_meta( $post_type, 'wtis_sport',          $string_args );
        register_post_meta( $post_type, 'wtis_league',         $string_args );
        register_post_meta( $post_type, 'wtis_matchup_date',   $string_args );

        // Prediction
        register_post_meta( $post_type, 'wtis_prediction_winner', $string_args );
        register_post_meta( $post_type, 'wtis_confidence_score',  $int_args );
        register_post_meta( $post_type, 'wtis_analysis',          $text_args );
        register_post_meta( $post_type, 'wtis_prediction_grade',  $int_args );

        // Pipeline metadata
        register_post_meta( $post_type, 'wtis_ai_generated', $bool_args );
        register_post_meta( $post_type, 'wtis_ingested_at',  $string_args );

        // Post-game
        register_post_meta( $post_type, 'wtis_actual_result',      $string_args );
        register_post_meta( $post_type, 'wtis_prediction_correct', $bool_args );

        // Factors and narrative
        register_post_meta( $post_type, 'wtis_factors_for',        $string_args );
        register_post_meta( $post_type, 'wtis_factors_against',    $string_args );
        register_post_meta( $post_type, 'wtis_what_nobody_saying', $text_args );

        // Headlines
        register_post_meta( $post_type, 'wtis_headline_personality', $string_args );
        register_post_meta( $post_type, 'wtis_headline_seo',         $string_args );

        // Article lifecycle
        register_post_meta( $post_type, 'wtis_article_stage',     $string_args );
        register_post_meta( $post_type, 'wtis_image_brief_scene', $string_args );

        // SEO — writable via REST so pipeline can set them
        register_post_meta( $post_type, 'rank_math_title',         $string_args );
        register_post_meta( $post_type, 'rank_math_description',   $string_args );
        register_post_meta( $post_type, 'rank_math_focus_keyword', $string_args );
    }
}

// ── Newsletter AJAX handler ──────────────────────────────────

add_action( 'wp_ajax_wtis_newsletter_subscribe',        'wtis_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_wtis_newsletter_subscribe', 'wtis_newsletter_subscribe' );

function wtis_newsletter_subscribe() {
    check_ajax_referer( 'wtis_nl_nonce', 'nonce' );

    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Invalid email address.' ] );
    }

    $api_key     = get_option( 'mailchimp_api_key', '' );
    $audience_id = get_option( 'mailchimp_audience_id', '' );
    $dc          = get_option( 'mailchimp_dc', 'us7' );

    if ( ! $api_key || ! $audience_id ) {
        wp_send_json_error( [ 'message' => 'Newsletter service not configured.' ] );
    }

    $url      = "https://{$dc}.api.mailchimp.com/3.0/lists/{$audience_id}/members";
    $response = wp_remote_post( $url, [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode( [
            'email_address' => $email,
            'status'        => 'subscribed',
            'tags'          => [ 'wtis-signup' ],
        ] ),
        'timeout' => 15,
    ] );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => 'Subscription failed. Please try again.' ] );
    }

    $code = wp_remote_retrieve_response_code( $response );

    if ( 200 === $code || 201 === $code ) {
        wp_send_json_success( [ 'message' => 'Subscribed.' ] );
    }

    if ( 400 === $code ) {
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['title'] ) && false !== strpos( $body['title'], 'Member Exists' ) ) {
            wp_send_json_success( [ 'message' => 'Already subscribed.' ] );
        }
    }

    wp_send_json_error( [ 'message' => 'Subscription failed. Please try again.' ] );
}

// ── Cache purge on post publish ──────────────────────────────

function wtis_purge_cache_on_publish( $post_id ) {
    if ( class_exists( 'Breeze_Admin' ) ) {
        do_action( 'breeze_clear_all_cache' );
    }
}
add_action( 'publish_post',         'wtis_purge_cache_on_publish' );
add_action( 'publish_wtis_matchup', 'wtis_purge_cache_on_publish' );

// ── Disable per-post RSS feed endpoints ──────────────────────

add_action( 'do_feed',      'wtis_disable_post_feeds', 1 );
add_action( 'do_feed_rss2', 'wtis_disable_post_feeds', 1 );

function wtis_disable_post_feeds(): void {
    if ( ! is_home() && ! is_front_page() ) {
        wp_redirect( home_url(), 301 );
        exit;
    }
}
