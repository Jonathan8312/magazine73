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

if ( ! empty( $settings['colors']['controls_hover'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-controls-hover:' . $settings['colors']['controls_hover'];
}

if ( ! empty( $settings['colors']['icons'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-icons:' . $settings['colors']['icons'];
}

if ( ! empty( $settings['colors']['icons_hover'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-icons-hover:' . $settings['colors']['icons_hover'];
}

if ( ! empty( $settings['colors']['counter'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-counter:' . $settings['colors']['counter'];
}

if ( ! empty( $settings['colors']['text'] ) ) {
	$magazine73_viewer_styles[] = '--magazine73-viewer-text:' . $settings['colors']['text'];
}

$magazine73_style_attribute   = empty( $magazine73_viewer_styles ) ? '' : implode( ';', $magazine73_viewer_styles );
$magazine73_config_json       = wp_json_encode( $viewer_config );
$magazine73_show_previous     = ! empty( $settings['controls']['previous'] );
$magazine73_show_next         = ! empty( $settings['controls']['next'] );
$magazine73_show_counter      = ! empty( $settings['controls']['counter'] );
$magazine73_show_zoom         = ! empty( $settings['controls']['zoom'] );
$magazine73_show_fullscreen   = ! empty( $settings['controls']['fullscreen'] );
$magazine73_show_download     = ! empty( $settings['controls']['download'] ) && ! empty( $viewer_config['download']['url'] );
$magazine73_show_thumbnails   = ! empty( $settings['controls']['thumbnails'] );
$magazine73_show_controls_bar = $magazine73_show_previous || $magazine73_show_next || $magazine73_show_counter || $magazine73_show_zoom || $magazine73_show_fullscreen || $magazine73_show_download || $magazine73_show_thumbnails;
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
	<div class="magazine73-viewer__layout">
		<?php if ( $magazine73_show_thumbnails ) : ?>
			<aside class="magazine73-thumbnails" data-magazine73-thumbnails hidden aria-label="<?php esc_attr_e( 'Page thumbnails', 'magazine73' ); ?>">
				<div class="magazine73-thumbnails__list" data-magazine73-thumbnails-list></div>
			</aside>
		<?php endif; ?>
		<div class="magazine73-viewer__main">
			<div class="magazine73-viewer__canvas">
				<div class="magazine73-viewer__loading" data-magazine73-loading aria-live="polite" aria-busy="true" hidden>
					<span class="magazine73-viewer__loading-spinner" aria-hidden="true"></span>
					<p
						class="magazine73-viewer__loading-label"
						data-magazine73-loading-label
						<?php /* translators: %1$d: loaded page count, %2$d: total page count. */ ?>
						data-template="<?php echo esc_attr__( 'Loading pages %1$d of %2$d', 'magazine73' ); ?>"
					>
						<?php esc_html_e( 'Loading magazine pages…', 'magazine73' ); ?>
					</p>
					<progress class="magazine73-viewer__loading-progress" data-magazine73-loading-progress max="100" value="0"></progress>
				</div>
				<div class="magazine73-viewer__viewport">
					<div class="magazine73-viewer__zoom" data-magazine73-zoom>
						<div class="magazine73-viewer__book"></div>
					</div>
				</div>
			</div>
			<p
				class="magazine73-viewer__loading-status"
				data-magazine73-loading-status
				hidden
				aria-live="polite"
				<?php /* translators: %1$d: loaded page count, %2$d: total page count. */ ?>
				data-template="<?php echo esc_attr__( 'Loading pages %1$d of %2$d…', 'magazine73' ); ?>"
			></p>
			<?php if ( $magazine73_show_controls_bar ) : ?>
				<div class="magazine73-viewer__controls magazine73-controls">
					<?php if ( $magazine73_show_thumbnails ) : ?>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="thumbnails"
							aria-expanded="false"
							aria-label="<?php esc_attr_e( 'Toggle page thumbnails', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="thumbnails" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
					<?php if ( $magazine73_show_previous ) : ?>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="prev"
							aria-label="<?php esc_attr_e( 'Previous page', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="prev" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
					<?php if ( $magazine73_show_counter ) : ?>
						<span class="magazine73-controls__status" data-magazine73-page-status aria-live="polite"></span>
					<?php endif; ?>
					<?php if ( $magazine73_show_next ) : ?>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="next"
							aria-label="<?php esc_attr_e( 'Next page', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="next" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
					<?php if ( $magazine73_show_zoom ) : ?>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="zoom-out"
							aria-label="<?php esc_attr_e( 'Zoom out', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="zoom-out" aria-hidden="true"></span>
						</button>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="zoom-reset"
							aria-label="<?php esc_attr_e( 'Reset zoom', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="zoom-reset" aria-hidden="true"></span>
						</button>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="zoom-in"
							aria-label="<?php esc_attr_e( 'Zoom in', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="zoom-in" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
					<?php if ( $magazine73_show_fullscreen ) : ?>
						<button
							type="button"
							class="magazine73-controls__button"
							data-magazine73-action="fullscreen"
							data-enter-label="<?php esc_attr_e( 'Enter fullscreen', 'magazine73' ); ?>"
							data-exit-label="<?php esc_attr_e( 'Exit fullscreen', 'magazine73' ); ?>"
							aria-label="<?php esc_attr_e( 'Enter fullscreen', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="fullscreen-enter" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
					<?php if ( $magazine73_show_download ) : ?>
						<a
							class="magazine73-controls__button"
							href="<?php echo esc_url( (string) $viewer_config['download']['url'] ); ?>"
							download="<?php echo esc_attr( (string) $viewer_config['download']['filename'] ); ?>"
							aria-label="<?php esc_attr_e( 'Download PDF', 'magazine73' ); ?>"
						>
							<span class="magazine73-controls__icon" data-magazine73-icon="download" aria-hidden="true"></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div
		class="magazine73-viewer__resume"
		data-magazine73-resume
		hidden
		role="dialog"
		aria-modal="true"
		aria-labelledby="magazine73-resume-title-<?php echo esc_attr( (string) $magazine->ID ); ?>"
	>
		<div class="magazine73-viewer__resume-panel">
			<p id="magazine73-resume-title-<?php echo esc_attr( (string) $magazine->ID ); ?>" class="magazine73-viewer__resume-title">
				<?php esc_html_e( 'Resume reading?', 'magazine73' ); ?>
			</p>
			<p
				class="magazine73-viewer__resume-message"
				data-magazine73-resume-message
				<?php /* translators: %d: saved page number. */ ?>
				data-template="<?php echo esc_attr__( 'Continue from page %d or start from the cover?', 'magazine73' ); ?>"
			></p>
			<div class="magazine73-viewer__resume-actions">
				<button type="button" class="magazine73-controls__button" data-magazine73-resume-action="continue">
					<?php esc_html_e( 'Continue reading', 'magazine73' ); ?>
				</button>
				<button type="button" class="magazine73-controls__button" data-magazine73-resume-action="restart">
					<?php esc_html_e( 'Start from cover', 'magazine73' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
