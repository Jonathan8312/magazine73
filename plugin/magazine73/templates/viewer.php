<?php
/**
 * Magazine viewer template.
 *
 * @package Magazine73
 *
 * @var \WP_Post $magazine
 * @var array{background: string, controls: string, text: string} $settings['colors']
 * @var array<string, bool> $settings['controls']
 * @var int    $page_count
 * @var string $width
 * @var string $height
 */

defined( 'ABSPATH' ) || exit;

$magazine73_viewer_styles = array();

if ( ! empty( $width ) ) {
	$magazine73_viewer_styles[] = 'width:' . $width;
}

if ( ! empty( $height ) ) {
	$magazine73_viewer_styles[] = 'height:' . $height;
}

if ( ! empty( $settings['colors']['background'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-background:' . $settings['colors']['background'];
}

if ( ! empty( $settings['colors']['controls'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-controls:' . $settings['colors']['controls'];
}

if ( ! empty( $settings['colors']['text'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-text:' . $settings['colors']['text'];
}

$magazine73_style_attribute = empty( $magazine73_viewer_styles ) ? '' : implode( ';', $magazine73_viewer_styles );
?>
<div
	class="magazine73-viewer"
	data-magazine73-viewer
	data-magazine-id="<?php echo esc_attr( (string) $magazine->ID ); ?>"
	data-page-count="<?php echo esc_attr( (string) $page_count ); ?>"
	<?php if ( '' !== $magazine73_style_attribute ) : ?>
		style="<?php echo esc_attr( $magazine73_style_attribute ); ?>"
	<?php endif; ?>
>
	<div class="magazine73-viewer__canvas">
		<p class="magazine73-viewer__placeholder">
			<?php
			printf(
				/* translators: %d: number of pages */
				esc_html__( 'Magazine viewer placeholder (%d pages).', 'magazine73' ),
				(int) $page_count
			);
			?>
		</p>
	</div>
</div>
