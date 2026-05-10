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

$post_id      = get_the_ID();
$page_slug    = get_post_field( 'post_name', $post_id );
$page_content = get_post_field( 'post_content', $post_id );

$disclaimer_text = "Well This Is Sports predictions are for entertainment purposes only. We make no claim of accuracy and accept no responsibility for decisions made based on our picks. Sports are unpredictable. That's why we love them.";

$about_body = [
	'Well This Is Sports is a fully automated AI-powered sports prediction platform. Every matchup gets a full analytical breakdown, a winner prediction with a confidence score, and a public accuracy ledger that tracks whether we got it right.',
	"The ledger is the product, not a feature. We don't hide our record. We publish it, update it after every game, and let you decide if we're worth listening to.",
];

$how_sections = [
	[
		'heading' => 'THE MATCHUP',
		'body'    => 'Before every game we publish a full breakdown of both teams. Form, injuries, head-to-head history, tactical matchups, and key factors that could decide the result. No filler. No generic takes.',
	],
	[
		'heading' => 'THE PICK',
		'body'    => "Our AI analyzes the available data and makes a prediction. Every pick includes a confidence score from 1 to 100. A score of 60 means we lean one way. A score of 85 means we're confident. We never pretend to be certain.",
	],
	[
		'heading' => 'THE RECORD',
		'body'    => "Every prediction is logged the moment it's published. After the final whistle, we update the result. Right or wrong. The accuracy ledger is public, permanent, and updated in real time. You can always see how we're doing.",
	],
];

/**
 * True when the page has meaningful authored content.
 */
$has_authored_content = '' !== trim( wp_strip_all_tags( (string) $page_content ) );
?>

<div class="wrapper" id="page-wrapper">
	<?php require get_stylesheet_directory() . '/inc/masthead.php'; ?>

	<div class="container">
		<main id="content" class="wtis-page-content">
			<?php if ( 'about' === $page_slug ) : ?>
			<h1 class="wtis-page-title">The Pick. The Prediction. The Record.</h1>
			<p class="wtis-page-subhead">We built a machine that makes predictions and shows its work.</p>
			<div class="wtis-page-body">
				<?php foreach ( $about_body as $paragraph ) : ?>
				<p><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
				<aside class="wtis-disclaimer" aria-label="<?php esc_attr_e( 'Disclaimer', 'wellthiissports-child' ); ?>">
					<?php echo esc_html( $disclaimer_text ); ?>
				</aside>
			</div>
			<?php elseif ( 'how-it-works' === $page_slug ) : ?>
			<h1 class="wtis-page-title">How The Prediction Engine Works</h1>
			<p class="wtis-page-subhead">Every pick is automated, transparent, and accountable.</p>
			<div class="wtis-page-body">
				<?php foreach ( $how_sections as $section ) : ?>
				<section class="wtis-page-section">
					<h2><?php echo esc_html( $section['heading'] ); ?></h2>
					<p><?php echo esc_html( $section['body'] ); ?></p>
				</section>
				<?php endforeach; ?>
				<aside class="wtis-disclaimer" aria-label="<?php esc_attr_e( 'Disclaimer', 'wellthiissports-child' ); ?>">
					<?php echo esc_html( $disclaimer_text ); ?>
				</aside>
			</div>
			<?php elseif ( 'privacy-policy' === $page_slug && ! $has_authored_content ) : ?>
			<h1 class="wtis-page-title"><?php the_title(); ?></h1>
			<div class="wtis-page-body">
				<p>We collect standard analytics data to improve the site. We do not sell your data.</p>
				<p>For questions contact: <a href="mailto:hello@wellthisiissports.com">hello@wellthisiissports.com</a></p>
			</div>
			<?php elseif ( 'terms-of-service' === $page_slug && ! $has_authored_content ) : ?>
			<h1 class="wtis-page-title"><?php the_title(); ?></h1>
			<div class="wtis-page-body">
				<p>Content on Well This Is Sports is for entertainment purposes only. Predictions do not constitute professional sports betting advice. Well This Is Sports accepts no liability for decisions made based on content published on this site.</p>
			</div>
			<?php else : ?>
			<h1 class="wtis-page-title"><?php the_title(); ?></h1>
			<div class="wtis-page-body"><?php the_content(); ?></div>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php get_footer(); ?>
