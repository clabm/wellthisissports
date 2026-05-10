<?php
/**
 * Shared masthead markup (logo, desktop + mobile nav).
 *
 * @package wellthiissports-child
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="wtis-masthead">
	<div class="container wtis-masthead__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wtis-logo">
			<img
				src="<?php echo esc_url( get_theme_file_uri( '/wtis-logo.png' ) ); ?>"
				alt="<?php esc_attr_e( 'Well This Is Sports', 'wellthiissports-child' ); ?>"
				width="280"
				height="40"
				decoding="async">
		</a>
		<button type="button"
				class="wtis-masthead__mobile-toggle"
				aria-controls="wtis-mobile-nav"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Open menu', 'wellthiissports-child' ); ?>">
			<span class="wtis-masthead__burger-line" aria-hidden="true"></span>
			<span class="wtis-masthead__burger-line" aria-hidden="true"></span>
			<span class="wtis-masthead__burger-line" aria-hidden="true"></span>
		</button>
		<nav class="wtis-masthead__nav-wrap" aria-label="<?php esc_attr_e( 'Primary navigation', 'wellthiissports-child' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'wtis-masthead__nav',
						'fallback_cb'    => false,
					]
				);
			} else {
				?>
				<ul class="wtis-masthead__nav">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Predictions', 'wellthiissports-child' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</nav>
	</div>
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			[
				'theme_location' => 'primary',
				'container'      => false,
				'menu_id'        => 'wtis-mobile-nav',
				'menu_class'     => 'wtis-mobile-nav',
				'fallback_cb'    => false,
			]
		);
	} else {
		?>
		<ul id="wtis-mobile-nav" class="wtis-mobile-nav">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Predictions', 'wellthiissports-child' ); ?></a></li>
		</ul>
		<?php
	}
	?>
</header>
