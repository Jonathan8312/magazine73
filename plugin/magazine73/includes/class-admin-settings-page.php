<?php
/**
 * Global viewer settings admin page.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves global viewer settings.
 */
final class Admin_Settings_Page {

	/**
	 * Settings page slug.
	 */
	public const PAGE_SLUG = 'magazine73-settings';

	/**
	 * Register admin hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the settings submenu.
	 */
	public function register_menu(): void {
		add_submenu_page(
			'edit.php?post_type=' . Post_Type::POST_TYPE,
			__( 'Magazine Settings', 'magazine73' ),
			__( 'Settings', 'magazine73' ),
			Capabilities::MANAGE_SETTINGS_CAP,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register global viewer settings.
	 */
	public function register_settings(): void {
		register_setting(
			Viewer_Settings::SETTINGS_GROUP,
			Viewer_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Viewer_Settings::class, 'sanitize_global' ),
				'default'           => Viewer_Settings::get_defaults(),
			)
		);

		add_settings_section(
			'magazine73_viewer_colors',
			__( 'Viewer Colors', 'magazine73' ),
			array( $this, 'render_colors_section' ),
			self::PAGE_SLUG
		);

		foreach ( Viewer_Settings::get_color_keys() as $color_key ) {
			add_settings_field(
				'magazine73_color_' . $color_key,
				Viewer_Settings::get_color_label( $color_key ),
				array( $this, 'render_color_field' ),
				self::PAGE_SLUG,
				'magazine73_viewer_colors',
				array(
					'color_key' => $color_key,
				)
			);
		}

		add_settings_section(
			'magazine73_viewer_controls',
			__( 'Visible Controls', 'magazine73' ),
			array( $this, 'render_controls_section' ),
			self::PAGE_SLUG
		);

		foreach ( Viewer_Settings::get_control_keys() as $control_key ) {
			add_settings_field(
				'magazine73_control_' . $control_key,
				$this->get_control_label( $control_key ),
				array( $this, 'render_control_field' ),
				self::PAGE_SLUG,
				'magazine73_viewer_controls',
				array(
					'control_key' => $control_key,
				)
			);
		}

		register_setting(
			Viewer_Settings::SETTINGS_GROUP,
			Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( Data_Lifecycle::class, 'sanitize_delete_on_uninstall' ),
				'default'           => false,
			)
		);

		add_settings_section(
			'magazine73_data_lifecycle',
			__( 'Data & Uninstall', 'magazine73' ),
			array( $this, 'render_data_lifecycle_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'magazine73_delete_data_on_uninstall',
			__( 'Delete plugin data on uninstall', 'magazine73' ),
			array( $this, 'render_delete_on_uninstall_field' ),
			self::PAGE_SLUG,
			'magazine73_data_lifecycle'
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( Capabilities::MANAGE_SETTINGS_CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access these settings.', 'magazine73' ) );
		}

		?>
		<div class="wrap magazine73-settings-page">
			<h1><?php esc_html_e( 'Magazine Settings', 'magazine73' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Configure neutral global defaults for the magazine viewer. Magazines can inherit these settings or override them individually.', 'magazine73' ); ?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( Viewer_Settings::SETTINGS_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the colors section description.
	 */
	public function render_colors_section(): void {
		echo '<p>' . esc_html__( 'Choose neutral colors for the viewer shell and controls. Use the color picker or enter a hex value such as #f5f5f5. Optional fields inherit from the active theme when left empty.', 'magazine73' ) . '</p>';
	}

	/**
	 * Render the controls section description.
	 */
	public function render_controls_section(): void {
		echo '<p>' . esc_html__( 'Choose which viewer controls are visible by default.', 'magazine73' ) . '</p>';
	}

	/**
	 * Render the data lifecycle section description.
	 */
	public function render_data_lifecycle_section(): void {
		echo '<p>' . esc_html__( 'Control whether Magazine73-owned database content is removed when the plugin is deleted. Media Library files are never deleted.', 'magazine73' ) . '</p>';
		echo '<p class="description">' . esc_html__( 'Multisite: uninstall cleanup runs only for the site where the plugin is deleted.', 'magazine73' ) . '</p>';
	}

	/**
	 * Render the delete-on-uninstall checkbox.
	 */
	public function render_delete_on_uninstall_field(): void {
		$checked  = Data_Lifecycle::should_delete_on_uninstall();
		$field_id = 'magazine73-delete-data-on-uninstall';
		?>
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION ); ?>"
				value="1"
				<?php checked( $checked ); ?>
			/>
			<?php esc_html_e( 'Remove magazines, metadata, and plugin settings when uninstalling Magazine73.', 'magazine73' ); ?>
		</label>
		<?php
	}

	/**
	 * Render a color settings field.
	 *
	 * @param array{color_key: string} $args Field arguments.
	 */
	public function render_color_field( array $args ): void {
		$color_key  = $args['color_key'];
		$settings   = Viewer_Settings::get_global();
		$defaults   = Viewer_Settings::get_defaults();
		$value      = $settings['colors'][ $color_key ] ?? '';
		$field_id   = 'magazine73-color-' . $color_key;
		$field_name = sprintf(
			'%s[colors][%s]',
			Viewer_Settings::OPTION_KEY,
			$color_key
		);

		Admin_Color_Field::render(
			$field_id,
			$field_name,
			is_string( $value ) ? $value : '',
			array(
				'placeholder' => Viewer_Settings::is_optional_color_key( $color_key ) ? __( 'Inherit from theme', 'magazine73' ) : '',
				'default'     => $defaults['colors'][ $color_key ] ?? '',
				'required'    => ! Viewer_Settings::is_optional_color_key( $color_key ),
			)
		);
	}

	/**
	 * Render a control visibility field.
	 *
	 * @param array{control_key: string} $args Field arguments.
	 */
	public function render_control_field( array $args ): void {
		$control_key = $args['control_key'];
		$settings    = Viewer_Settings::get_global();
		$checked     = ! empty( $settings['controls'][ $control_key ] );
		$field_id    = 'magazine73-control-' . $control_key;
		$field_name  = sprintf(
			'%s[controls][%s]',
			Viewer_Settings::OPTION_KEY,
			$control_key
		);
		?>
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
		<?php
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
