<?php
/**
 * Magazine shortcode.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the magazine shortcode.
 */
final class Shortcode {

	/**
	 * Shortcode tag.
	 */
	public const TAG = 'magazine73';

	/**
	 * Register shortcode hooks.
	 */
	public function init(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Render the magazine shortcode.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts(
			array(
				'id'                   => '',
				'width'                => '',
				'height'               => '',
				'controls'             => '',
				'fullscreen'           => '',
				'download'             => '',
				'thumbnails'           => '',
				'theme'                => '',
				'color_background'     => '',
				'color_controls'       => '',
				'color_controls_hover' => '',
				'color_icons'          => '',
				'color_icons_hover'    => '',
				'color_counter'        => '',
				'color_text'           => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		$magazine_id = absint( $atts['id'] );

		if ( $magazine_id <= 0 ) {
			return Magazine_Renderer::render_message( __( 'A valid magazine ID is required.', 'magazine73' ) );
		}

		return Magazine_Renderer::render_viewer( $magazine_id, $atts );
	}
}
