<?php
/**
 * Optional Elementor integration.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Registers Magazine73 widgets when Elementor is active.
 */
final class Elementor_Integration {

	/**
	 * Widget category slug.
	 */
	public const CATEGORY = 'magazine73';

	/**
	 * Register Elementor hooks.
	 *
	 * Soft dependency: hooks only run when Elementor fires them.
	 */
	public function init(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );
		add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );
	}

	/**
	 * Ensure viewer CSS/JS load in Elementor editor preview and frontend.
	 *
	 * Widget/shortcode render also enqueues assets, but Elementor often mounts
	 * markup after the initial script module evaluation.
	 */
	public function enqueue_preview_assets(): void {
		Assets::enqueue_viewer();
	}

	/**
	 * Register the Magazine73 widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'Magazine73', 'magazine73' ),
				'icon'  => 'fa fa-book',
			)
		);
	}

	/**
	 * Register Magazine73 Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once MAGAZINE73_PATH . 'includes/elementor/class-widget-magazine-viewer.php';

		$widgets_manager->register( new Elementor\Widget_Magazine_Viewer() );
	}
}
