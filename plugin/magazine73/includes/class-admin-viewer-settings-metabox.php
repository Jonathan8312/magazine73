<?php
/**
 * Per-magazine viewer settings metabox.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves per-magazine viewer setting overrides.
 */
final class Admin_Viewer_Settings_Metabox {

	/**
	 * Metabox identifier.
	 */
	private const METABOX_ID = 'magazine73_viewer_settings';

	/**
	 * Register admin hooks.
	 */
	public function init(): void {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post_' . Post_Type::POST_TYPE, array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Register the viewer settings metabox.
	 */
	public function register_metabox(): void {
		add_meta_box(
			self::METABOX_ID,
			__( 'Viewer Settings', 'magazine73' ),
			array( $this, 'render_metabox' ),
			Post_Type::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Render the viewer settings metabox.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_metabox( \WP_Post $post ): void {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		wp_nonce_field( 'magazine73_save_viewer_settings', 'magazine73_viewer_settings_nonce' );

		$use_global = Viewer_Settings::uses_global_settings( $post->ID );
		$overrides  = self::get_metabox_overrides( $post->ID );
		?>
		<div class="magazine73-admin-panel magazine73-viewer-settings-panel" data-magazine73-admin data-magazine73-viewer-settings>
			<p>
				<label for="magazine73-use-global-settings">
					<input
						type="checkbox"
						id="magazine73-use-global-settings"
						name="magazine73_use_global_settings"
						value="1"
						<?php checked( $use_global ); ?>
					/>
					<?php esc_html_e( 'Use global settings', 'magazine73' ); ?>
				</label>
			</p>
			<div class="magazine73-viewer-settings-panel__overrides" id="magazine73-viewer-settings-overrides"<?php echo $use_global ? ' hidden' : ''; ?>>
				<p class="description">
					<?php esc_html_e( 'Override global viewer colors and visible controls for this magazine.', 'magazine73' ); ?>
				</p>
				<fieldset>
					<legend><strong><?php esc_html_e( 'Viewer Colors', 'magazine73' ); ?></strong></legend>
					<?php foreach ( Viewer_Settings::get_color_keys() as $color_key ) : ?>
						<?php
						$value      = $overrides['colors'][ $color_key ] ?? '';
						$defaults   = Viewer_Settings::get_defaults();
						$field_id   = 'magazine73-override-color-' . $color_key;
						$field_name = sprintf(
							'magazine73_viewer_settings[colors][%s]',
							$color_key
						);
						?>
						<p>
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $this->get_color_label( $color_key ) ); ?>
							</label>
							<?php
							Admin_Color_Field::render(
								$field_id,
								$field_name,
								is_string( $value ) ? $value : '',
								array(
									'placeholder' => 'text' === $color_key ? __( 'Inherit from theme', 'magazine73' ) : '',
									'default'     => $defaults['colors'][ $color_key ] ?? '',
									'required'    => 'text' !== $color_key,
								)
							);
							?>
						</p>
					<?php endforeach; ?>
				</fieldset>
				<fieldset>
					<legend><strong><?php esc_html_e( 'Visible Controls', 'magazine73' ); ?></strong></legend>
					<?php foreach ( Viewer_Settings::get_control_keys() as $control_key ) : ?>
						<?php
						$checked    = ! empty( $overrides['controls'][ $control_key ] );
						$field_id   = 'magazine73-override-control-' . $control_key;
						$field_name = sprintf(
							'magazine73_viewer_settings[controls][%s]',
							$control_key
						);
						?>
						<p>
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<input
									type="checkbox"
									id="<?php echo esc_attr( $field_id ); ?>"
									name="<?php echo esc_attr( $field_name ); ?>"
									value="1"
									<?php checked( $checked ); ?>
								/>
								<?php echo esc_html( $this->get_control_label( $control_key ) ); ?>
							</label>
						</p>
					<?php endforeach; ?>
				</fieldset>
			</div>
		</div>
		<?php
	}

	/**
	 * Save viewer settings from the metabox.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_metabox( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['magazine73_viewer_settings_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['magazine73_viewer_settings_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'magazine73_save_viewer_settings' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$use_global = isset( $_POST['magazine73_use_global_settings'] );

		update_post_meta( $post_id, Viewer_Settings::USE_GLOBAL_META_KEY, $use_global );

		if ( $use_global ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Values are sanitized in Viewer_Settings::sanitize_magazine_overrides().
		$raw_overrides = isset( $_POST['magazine73_viewer_settings'] ) ? wp_unslash( $_POST['magazine73_viewer_settings'] ) : array();

		if ( ! is_array( $raw_overrides ) ) {
			$raw_overrides = array();
		}

		$viewer_settings = new Viewer_Settings();
		$overrides       = $viewer_settings->sanitize_magazine_overrides( $raw_overrides );

		update_post_meta( $post_id, Viewer_Settings::OVERRIDES_META_KEY, $overrides );
	}

	/**
	 * Get override values for the metabox form.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	private function get_metabox_overrides( int $post_id ): array {
		$stored = get_post_meta( $post_id, Viewer_Settings::OVERRIDES_META_KEY, true );

		if ( ! is_array( $stored ) || array() === $stored ) {
			return Viewer_Settings::get_global();
		}

		return Viewer_Settings::get_magazine_overrides( $post_id );
	}

	/**
	 * Get a translated color field label.
	 *
	 * @param string $color_key Color key.
	 */
	private function get_color_label( string $color_key ): string {
		$labels = array(
			'background' => __( 'Viewer background', 'magazine73' ),
			'controls'   => __( 'Control buttons', 'magazine73' ),
			'text'       => __( 'Viewer text', 'magazine73' ),
		);

		return $labels[ $color_key ] ?? $color_key;
	}

	/**
	 * Get a translated control field label.
	 *
	 * @param string $control_key Control key.
	 */
	private function get_control_label( string $control_key ): string {
		$labels = array(
			'previous'   => __( 'Previous page', 'magazine73' ),
			'next'       => __( 'Next page', 'magazine73' ),
			'counter'    => __( 'Page counter', 'magazine73' ),
			'fullscreen' => __( 'Fullscreen', 'magazine73' ),
			'download'   => __( 'PDF download', 'magazine73' ),
			'zoom'       => __( 'Zoom', 'magazine73' ),
			'thumbnails' => __( 'Thumbnails', 'magazine73' ),
		);

		return $labels[ $control_key ] ?? $control_key;
	}
}
