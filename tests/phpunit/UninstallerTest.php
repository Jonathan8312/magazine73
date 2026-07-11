<?php
/**
 * Uninstaller tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Data_Lifecycle;
use Magazine73\Uninstaller;
use Magazine73\Viewer_Settings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Uninstaller
 */
final class UninstallerTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_run_skips_cleanup_when_delete_option_is_disabled(): void {
		WordPressStub::$magazine_post_ids = array( 101, 102 );
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ]           = Viewer_Settings::get_defaults();
		WordPressStub::$options[ Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION ] = false;

		Uninstaller::run();

		$this->assertSame( array(), WordPressStub::$deleted_post_ids );
		$this->assertSame( array(), WordPressStub::$deleted_options );
	}

	public function test_run_deletes_magazine_posts_and_owned_options_when_enabled(): void {
		WordPressStub::$magazine_post_ids = array( 201, 202 );
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ]              = Viewer_Settings::get_defaults();
		WordPressStub::$options[ Data_Lifecycle::DATA_VERSION_OPTION ]        = Data_Lifecycle::CURRENT_DATA_VERSION;
		WordPressStub::$options[ Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION ] = true;

		Uninstaller::run();

		$this->assertSame( array( 201, 202 ), WordPressStub::$deleted_post_ids );
		$this->assertContains( Viewer_Settings::OPTION_KEY, WordPressStub::$deleted_options );
		$this->assertContains( Data_Lifecycle::DATA_VERSION_OPTION, WordPressStub::$deleted_options );
		$this->assertContains( Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION, WordPressStub::$deleted_options );
	}

	public function test_should_delete_on_uninstall_defaults_to_false(): void {
		$this->assertFalse( Data_Lifecycle::should_delete_on_uninstall() );
	}
}
