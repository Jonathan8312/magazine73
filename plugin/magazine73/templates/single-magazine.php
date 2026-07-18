<?php
/**
 * Single magazine public template.
 *
 * Compatible with classic themes (`get_header` / `get_footer`) and block themes
 * (`block_header_area` / `block_footer_area`).
 *
 * @package Magazine73
 */

defined( 'ABSPATH' ) || exit;

$magazine73_is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

if ( $magazine73_is_block_theme ) {
	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="wp-site-blocks">
		<header class="wp-block-template-part">
			<?php block_header_area(); ?>
		</header>
	<?php
} else {
	get_header();
}
?>
		<main class="magazine73-public-magazine alignwide" id="magazine73-public-magazine">
	<?php
	while ( have_posts() ) :
		the_post();

		$magazine73_magazine_id = get_the_ID();
		$magazine73_edition     = \Magazine73\Magazine_Meta::get_edition( $magazine73_magazine_id );
		?>
		<article <?php post_class( 'magazine73-public-magazine__article' ); ?>>
			<header class="magazine73-public-magazine__header">
				<h1 class="magazine73-public-magazine__title"><?php the_title(); ?></h1>
				<?php if ( '' !== $magazine73_edition ) : ?>
					<p class="magazine73-public-magazine__edition"><?php echo esc_html( $magazine73_edition ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== get_the_content() ) : ?>
					<div class="magazine73-public-magazine__description">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
			</header>
			<div class="magazine73-public-magazine__viewer">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in Magazine_Renderer and viewer template.
				echo \Magazine73\Magazine_Renderer::render_viewer( $magazine73_magazine_id );
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
if ( $magazine73_is_block_theme ) {
	?>
		<footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
		</footer>
	</div>
	<?php
	if ( function_exists( 'wp_enqueue_stored_styles' ) ) {
		wp_enqueue_stored_styles();
	}
	wp_footer();
	?>
</body>
</html>
	<?php
} else {
	get_footer();
}
