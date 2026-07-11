<?php
/**
 * Single magazine public template.
 *
 * @package Magazine73
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="magazine73-public-magazine" id="magazine73-public-magazine">
	<?php
	while ( have_posts() ) :
		the_post();

		$magazine73_magazine_id = get_the_ID();
		$magazine73_edition     = Magazine_Meta::get_edition( $magazine73_magazine_id );
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
				echo Magazine_Renderer::render_viewer( $magazine73_magazine_id );
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
