<?php
/**
 * 404.php
 * Well This Is Sports — 404 not found template.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<div class="wtis-page-content">
		<h1 class="wtis-page-title">Page Not Found</h1>
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to predictions &rarr;</a></p>
	</div>
</div>

<?php get_footer();
