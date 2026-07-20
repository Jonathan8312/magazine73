<?php
/**
 * Elementor magazine viewer widget.
 *
 * @package Magazine73
 */

namespace Magazine73\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Magazine73\Elementor_Integration;
use Magazine73\Magazine_Renderer;
use Magazine73\Post_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Renders a Magazine73 viewer inside Elementor.
 */
final class Widget_Magazine_Viewer extends Widget_Base {

	/**
	 * Widget slug.
	 */
	public function get_name(): string {
		return 'magazine73_viewer';
	}

	/**
	 * Widget title.
	 */
	public function get_title(): string {
		return __( 'Magazine73 Viewer', 'magazine73' );
	}

	/**
	 * Widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-document-file';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( Elementor_Integration::CATEGORY );
	}

	/**
	 * Widget keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'magazine', 'flipbook', 'viewer', 'magazine73' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_magazine',
			array(
				'label' => __( 'Magazine', 'magazine73' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'magazine_id',
			array(
				'label'       => __( 'Magazine', 'magazine73' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_magazine_options(),
				'label_block' => true,
			)
		);

		$this->add_control(
			'width',
			array(
				'label'       => __( 'Width', 'magazine73' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '100%',
				'description' => __( 'Optional CSS size (px, %, rem, em, vw, vh).', 'magazine73' ),
			)
		);

		$this->add_control(
			'height',
			array(
				'label'       => __( 'Height', 'magazine73' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '80vh',
				'description' => __( 'Optional CSS size (px, %, rem, em, vw, vh).', 'magazine73' ),
			)
		);

		$this->add_control(
			'theme',
			array(
				'label'       => __( 'Theme preset', 'magazine73' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''      => __( 'Magazine / global defaults', 'magazine73' ),
					'light' => __( 'Light', 'magazine73' ),
					'dark'  => __( 'Dark', 'magazine73' ),
				),
				'description' => __( 'Fills only the Style colors you leave empty. Magazine / global uses module colors; Light and Dark use those presets instead. Colors set in the Style tab always win.', 'magazine73' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_controls',
			array(
				'label' => __( 'Viewer Controls', 'magazine73' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'controls',
			array(
				'label'       => __( 'Show all controls', 'magazine73' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''      => __( 'Magazine / global defaults', 'magazine73' ),
					'true'  => __( 'Show', 'magazine73' ),
					'false' => __( 'Hide', 'magazine73' ),
				),
				'description' => __( 'Overrides every control at once when set.', 'magazine73' ),
			)
		);

		foreach ( array( 'fullscreen', 'download', 'thumbnails' ) as $control_key ) {
			$this->add_control(
				$control_key,
				array(
					'label'   => $this->get_control_label( $control_key ),
					'type'    => Controls_Manager::SELECT,
					'default' => '',
					'options' => array(
						''      => __( 'Magazine / global defaults', 'magazine73' ),
						'true'  => __( 'Show', 'magazine73' ),
						'false' => __( 'Hide', 'magazine73' ),
					),
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_colors',
			array(
				'label' => __( 'Viewer Colors', 'magazine73' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'color_background',
			array(
				'label' => __( 'Viewer background', 'magazine73' ),
				'type'  => Controls_Manager::COLOR,
				'alpha' => false,
			)
		);

		$this->add_control(
			'color_controls',
			array(
				'label' => __( 'Controls background', 'magazine73' ),
				'type'  => Controls_Manager::COLOR,
				'alpha' => false,
			)
		);

		$this->add_control(
			'color_controls_hover',
			array(
				'label'       => __( 'Controls background (hover)', 'magazine73' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => false,
				'description' => __( 'Leave empty to use a subtle automatic hover state.', 'magazine73' ),
			)
		);

		$this->add_control(
			'color_icons',
			array(
				'label'       => __( 'Action button icons', 'magazine73' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => false,
				'description' => __( 'Leave empty to inherit from the magazine or theme.', 'magazine73' ),
			)
		);

		$this->add_control(
			'color_icons_hover',
			array(
				'label'       => __( 'Action button icons (hover)', 'magazine73' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => false,
				'description' => __( 'Leave empty to keep the normal icon color on hover.', 'magazine73' ),
			)
		);

		$this->add_control(
			'color_counter',
			array(
				'label'       => __( 'Page counter', 'magazine73' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => false,
				'description' => __( 'Leave empty to inherit from the magazine or theme.', 'magazine73' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$magazine_id = absint( $settings['magazine_id'] ?? 0 );

		if ( $magazine_id <= 0 ) {
			if ( $this->is_editor_preview() ) {
				// Magazine_Renderer::render_message() escapes its output.
				echo Magazine_Renderer::render_message( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					__( 'Select a magazine in the widget settings.', 'magazine73' )
				);
			}
			return;
		}

		$color_keys = array(
			'color_background',
			'color_controls',
			'color_controls_hover',
			'color_icons',
			'color_icons_hover',
			'color_counter',
			'color_text',
		);

		$atts = array(
			Magazine_Renderer::ELEMENTOR_COLOR_SOURCE => Magazine_Renderer::ELEMENTOR_COLOR_SOURCE_VALUE,
			'width'                                   => isset( $settings['width'] ) ? (string) $settings['width'] : '',
			'height'                                  => isset( $settings['height'] ) ? (string) $settings['height'] : '',
			'controls'                                => isset( $settings['controls'] ) ? (string) $settings['controls'] : '',
			'fullscreen'                              => isset( $settings['fullscreen'] ) ? (string) $settings['fullscreen'] : '',
			'download'                                => isset( $settings['download'] ) ? (string) $settings['download'] : '',
			'thumbnails'                              => isset( $settings['thumbnails'] ) ? (string) $settings['thumbnails'] : '',
			'theme'                                   => isset( $settings['theme'] ) ? (string) $settings['theme'] : '',
		);

		foreach ( $color_keys as $color_key ) {
			$atts[ $color_key ] = $this->resolve_color_setting( $settings, $color_key );
		}

		// Magazine_Renderer escapes template output.
		echo Magazine_Renderer::render_viewer( $magazine_id, $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Whether the current request is an Elementor editor preview.
	 */
	private function is_editor_preview(): bool {
		return class_exists( '\Elementor\Plugin' )
			&& isset( \Elementor\Plugin::$instance->editor )
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Resolve a widget color setting, including Elementor global color links.
	 *
	 * @param array<string, mixed> $settings Widget settings from get_settings_for_display().
	 * @param string               $key      Color control key.
	 */
	private function resolve_color_setting( array $settings, string $key ): string {
		$value = isset( $settings[ $key ] ) ? trim( (string) $settings[ $key ] ) : '';

		if ( '' !== $value && 'global' !== $value ) {
			if ( str_starts_with( $value, 'globals/colors?' ) ) {
				return $this->resolve_elementor_global_color( $value );
			}

			return $value;
		}

		$globals = $settings['__globals__'] ?? array();

		if ( ! is_array( $globals ) || empty( $globals[ $key ] ) ) {
			return '';
		}

		return $this->resolve_elementor_global_color( (string) $globals[ $key ] );
	}

	/**
	 * Resolve an Elementor global color reference to a hex value.
	 *
	 * @param string $global_key Global reference such as globals/colors?id=secondary.
	 */
	private function resolve_elementor_global_color( string $global_key ): string {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		$plugin = \Elementor\Plugin::$instance;

		if ( isset( $plugin->data_manager_v2 ) ) {
			$data = $plugin->data_manager_v2->run( $global_key );

			if ( is_array( $data ) && ! empty( $data['value'] ) ) {
				$resolved = trim( (string) $data['value'] );

				if ( '' !== $resolved ) {
					return $resolved;
				}
			}
		}

		if ( ! isset( $plugin->kits_manager ) || ! preg_match( '/globals\/colors\?id=(.+)/', $global_key, $matches ) ) {
			return '';
		}

		$color_id = $matches[1];
		$kit      = $plugin->kits_manager->get_active_kit();

		if ( ! is_object( $kit ) || ! method_exists( $kit, 'get_settings' ) ) {
			return '';
		}

		foreach ( array( 'system_colors', 'custom_colors' ) as $collection_key ) {
			$colors = $kit->get_settings( $collection_key );

			if ( ! is_array( $colors ) ) {
				continue;
			}

			foreach ( $colors as $color ) {
				if ( ! is_array( $color ) ) {
					continue;
				}

				if ( ( $color['_id'] ?? '' ) === $color_id && ! empty( $color['color'] ) ) {
					return trim( (string) $color['color'] );
				}
			}
		}

		return '';
	}

	/**
	 * Build SELECT2 options for published magazines.
	 *
	 * @return array<int|string, string>
	 */
	private function get_magazine_options(): array {
		$posts = get_posts(
			array(
				'post_type'              => Post_Type::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => 100,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$options = array();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$title = get_the_title( $post );

			if ( '' === $title ) {
				$title = sprintf(
					/* translators: %d: magazine post ID. */
					__( 'Magazine #%d', 'magazine73' ),
					(int) $post->ID
				);
			}

			$options[ (string) $post->ID ] = $title;
		}

		return $options;
	}

	/**
	 * Translated label for a viewer control key.
	 *
	 * @param string $control_key Control key.
	 */
	private function get_control_label( string $control_key ): string {
		$labels = array(
			'fullscreen' => __( 'Fullscreen', 'magazine73' ),
			'download'   => __( 'Download', 'magazine73' ),
			'thumbnails' => __( 'Thumbnails', 'magazine73' ),
		);

		return $labels[ $control_key ] ?? $control_key;
	}
}
