<?php
/**
 * Plugin data version and migration management.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Tracks schema versions and runs idempotent data migrations.
 */
final class Data_Lifecycle {

	/**
	 * Stored data/schema version option key.
	 */
	public const DATA_VERSION_OPTION = 'magazine73_data_version';

	/**
	 * Delete plugin-owned data on uninstall option key.
	 */
	public const DELETE_ON_UNINSTALL_OPTION = 'magazine73_delete_data_on_uninstall';

	/**
	 * Current internal data/schema version.
	 */
	public const CURRENT_DATA_VERSION = 1;

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'plugins_loaded', array( self::class, 'maybe_run_migrations' ), 5 );
	}

	/**
	 * Run pending migrations when the stored version is behind.
	 */
	public static function maybe_run_migrations(): void {
		$stored_version = self::get_data_version();

		if ( $stored_version >= self::CURRENT_DATA_VERSION ) {
			return;
		}

		foreach ( self::get_migrations() as $version => $callback ) {
			if ( $version <= $stored_version ) {
				continue;
			}

			if ( $version > self::CURRENT_DATA_VERSION ) {
				break;
			}

			call_user_func( $callback );
			self::set_data_version( $version );
		}
	}

	/**
	 * Get the stored data version.
	 */
	public static function get_data_version(): int {
		$stored = get_option( self::DATA_VERSION_OPTION, 0 );

		return is_numeric( $stored ) ? (int) $stored : 0;
	}

	/**
	 * Whether plugin-owned data should be removed on uninstall.
	 */
	public static function should_delete_on_uninstall(): bool {
		return rest_sanitize_boolean( get_option( self::DELETE_ON_UNINSTALL_OPTION, false ) );
	}

	/**
	 * Sanitize the delete-on-uninstall setting from the settings screen.
	 *
	 * @param mixed $value Submitted option value.
	 */
	public static function sanitize_delete_on_uninstall( $value ): bool {
		unset( $value );

		if ( ! isset( $_POST[ self::DELETE_ON_UNINSTALL_OPTION ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		return rest_sanitize_boolean( wp_unslash( $_POST[ self::DELETE_ON_UNINSTALL_OPTION ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Persist the data version.
	 *
	 * @param int $version Data version.
	 */
	private static function set_data_version( int $version ): void {
		update_option( self::DATA_VERSION_OPTION, $version, false );
	}

	/**
	 * Return versioned migration callbacks in ascending order.
	 *
	 * @return array<int, callable>
	 */
	private static function get_migrations(): array {
		return array(
			1 => array( self::class, 'migrate_to_version_1' ),
		);
	}

	/**
	 * Baseline migration for initial schema tracking.
	 *
	 * Idempotent placeholder so future versions can reshape stored data safely.
	 */
	public static function migrate_to_version_1(): void {
		// Intentionally empty for the initial data version.
	}
}
