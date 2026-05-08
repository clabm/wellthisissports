<?php
/**
 * header.php
 * Well This Is Sports — child theme header.
 *
 * Overrides Understrap parent header.php to suppress Bootstrap navbar.
 * The WTIS masthead is rendered directly in each page template.
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php do_action( 'wp_body_open' ); ?>
<div class="site" id="page">
	<a class="skip-link sr-only sr-only-focusable" href="#content">
		<?php esc_html_e( 'Skip to content', 'wellthiissports-child' ); ?>
	</a>
