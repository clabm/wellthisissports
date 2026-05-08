<?php
/**
 * footer-content.php
 * Well This Is Sports — shared footer HTML partial.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;
?>

<footer class="wtis-footer-main" aria-label="Site footer">
  <div class="container">
    <div class="wtis-footer-grid">

      <!-- Column 1 — Brand -->
      <div class="wtis-footer-col wtis-footer-col--brand">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
          <span class="wtis-footer-wordmark">Well This Is Sports</span>
        </a>
        <div class="wtis-footer-tagline">AI predictions. Public ledger. No excuses.</div>
      </div>

      <!-- Column 2 — Navigate -->
      <div class="wtis-footer-col wtis-footer-col--nav">
        <p class="wtis-footer-label">Navigate</p>
        <?php
        $footer_menu = get_nav_menu_locations();
        if ( ! empty( $footer_menu['footer'] ) ) {
            wp_nav_menu( [
                'theme_location' => 'footer',
                'menu_class'     => 'wtis-footer-links',
                'container'      => false,
                'fallback_cb'    => false,
            ] );
        }
        ?>
      </div>

      <!-- Column 3 — Follow Us -->
      <div class="wtis-footer-col wtis-footer-col--social">
        <p class="wtis-footer-label">Follow Us</p>
        <div class="wtis-footer-social">

          <a href="https://bsky.app/profile/wellthisiissports.com"
             class="wtis-footer-social__link"
             aria-label="Follow Well This Is Sports on Bluesky"
             target="_blank"
             rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true" focusable="false">
              <path d="M6.335 5.144c-1.654-1.199-4.335-2.2-4.335 1.443 0 .826.327 3.452.49 3.845.614 1.595 2.817 2.135 4.996 1.887l-.047.061c-2.535.403-4.861 1.595-1.868 3.942C8.951 18.97 10.74 15.538 11.24 14.024c.5 1.514 2.272 4.946 5.752 2.298 2.993-2.347.667-3.539-1.868-3.942l-.047-.061c2.179.248 4.382-.292 4.996-1.887.163-.393.49-3.019.49-3.845 0-3.643-2.681-2.642-4.335-1.443C14.111 6.333 13.556 7.348 12 7.348S9.889 6.333 6.335 5.144z"/>
            </svg>
          </a>

          <a href="https://www.facebook.com/wellthisiissports"
             class="wtis-footer-social__link"
             aria-label="Follow Well This Is Sports on Facebook"
             target="_blank"
             rel="noopener noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true" focusable="false">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>

        </div>

        <div class="wtis-footer-subscribe">
          <p class="wtis-footer-subscribe__label">Prediction Digest</p>
          <p class="wtis-footer-subscribe__desc">Daily picks. Confidence scores. Straight to your inbox.</p>
          <form class="wtis-nl-form wtis-footer-nl-form" method="post" novalidate>
            <input type="email"
                   class="wtis-footer-subscribe__input"
                   placeholder="your@email.com"
                   required
                   autocomplete="email" />
            <button type="submit" class="wtis-footer-subscribe__btn">Subscribe free</button>
            <p class="wtis-nl-error"></p>
          </form>
        </div>
      </div>

    </div><!-- .wtis-footer-grid -->
  </div><!-- .container -->
</footer>

<div class="wtis-footer-bottom">
  <div class="container">
    <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Well This Is Sports. All rights reserved.
      &nbsp;&middot;&nbsp;
      <?php $pp = get_page_by_path( 'privacy-policy' ); ?>
      <a href="<?php echo esc_url( $pp ? get_permalink( $pp->ID ) : home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
      &nbsp;&middot;&nbsp;
      <?php $tos = get_page_by_path( 'terms-of-service' ); ?>
      <a href="<?php echo esc_url( $tos ? get_permalink( $tos->ID ) : home_url( '/terms-of-service/' ) ); ?>">Terms of Service</a>
    </p>
  </div>
</div>
