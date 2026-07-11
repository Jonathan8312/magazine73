<?php
/**
 * Data lifecycle tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Data_Lifecycle;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Data_Lifecycle
 */
final class DataLifecycleTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_maybe_run_migrations_sets_initial_data_version(): void {
		Data_Lifecycle::maybe_run_migrations();

		$this->assertSame( Data_Lifecycle::CURRENT_DATA_VERSION, Data_Lifecycle::get_data_version() );
	}

	public function test_maybe_run_migrations_is_idempotent(): void {
		Data_Lifecycle::maybe_run_migrations();
		WordPressStub::$options[ Data_Lifecycle::DATA_VERSION_OPTION ] = Data_Lifecycle::CURRENT_DATA_VERSION;

		Data_Lifecycle::maybe_run_migrations();

		$this->assertSame( Data_Lifecycle::CURRENT_DATA_VERSION, Data_Lifecycle::get_data_version() );
	}

	public function test_sanitize_delete_on_uninstall_defaults_to_false(): void {
		$this->assertFalse( Data_Lifecycle::sanitize_delete_on_uninstall( null ) );
	}
}
