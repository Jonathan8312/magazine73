<?php
/**
 * Magazine viewer template.
 *
 * @package Magazine73
 *
 * @var \WP_Post $magazine
 * @var array{background: string, controls: string, text: string} $settings['colors']
 * @var array<string, bool> $settings['controls']
 * @var array{magazineId: int, pages: array<int, array{url: string, width: int, height: int, blank?: bool}>, settings: array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}, dimensions: array{width: string, height: string}} $viewer_config
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
$magazine73_config_json     = wp_json_encode( $viewer_config );
$magazine73_show_previous   = ! empty( $settings['controls']['previous'] );
$magazine73_show_next       = ! empty( $settings['controls']['next'] );
$magazine73_show_counter    = ! empty( $settings['controls']['counter'] );
$magazine73_show_navigation = $magazine73_show_previous || $magazine73_show_next || $magazine73_show_counter;
?>
<div
	class="magazine73-viewer"
	data-magazine73-viewer
	data-magazine73-config="<?php echo esc_attr( is_string( $magazine73_config_json ) ? $magazine73_config_json : '' ); ?>"
	data-magazine-id="<?php echo esc_attr( (string) $magazine->ID ); ?>"
	<?php if ( '' !== $magazine73_style_attribute ) : ?>
		style="<?php echo esc_attr( $magazine73_style_attribute ); ?>"
	<?php endif; ?>
	tabindex="0"
	role="region"
	aria-label="<?php esc_attr_e( 'Magazine viewer', 'magazine73' ); ?>"
>
	<div class="magazine73-viewer__canvas">
		<div class="magazine73-viewer__book"></div>
	</div>
	<?php if ( $magazine73_show_navigation ) : ?>
		<div class="magazine73-viewer__controls magazine73-controls">
			<?php if ( $magazine73_show_previous ) : ?>
				<button type="button" class="magazine73-controls__button" data-magazine73-action="prev">
					<?php esc_html_e( 'Previous page', 'magazine73' ); ?>
				</button>
			<?php endif; ?>
			<?php if ( $magazine73_show_counter ) : ?>
				<span class="magazine73-controls__status" data-magazine73-page-status aria-live="polite"></span>
			<?php endif; ?>
			<?php if ( $magazine73_show_next ) : ?>
				<button type="button" class="magazine73-controls__button" data-magazine73-action="next">
					<?php esc_html_e( 'Next page', 'magazine73' ); ?>
				</button>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
