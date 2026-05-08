<?php
/**
 * page.php
 * Well This Is Sports — generic page template.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>

<div class="wrapper" id="page-wrapper">
  <div class="container">

    <header class="wtis-masthead">
      <div class="wtis-masthead__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wtis-masthead__wordmark">
          Well This Is Sports
        </a>
      </div>
    </header>

    <main id="content" class="wtis-page-content">
      <h1 class="wtis-page-title"><?php the_title(); ?></h1>
      <div class="wtis-page-body"><?php the_content(); ?></div>
    </main>

  </div>
</div>

<?php get_footer(); ?>
