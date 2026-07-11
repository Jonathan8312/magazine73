<?php
/**
 * Magazine renderer tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Magazine_Renderer;
use Magazine73\Viewer_Settings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Magazine_Renderer
 */
final class MagazineRendererTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_resolve_settings_honors_shortcode_download_override(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				'download' => 'false',
			)
		);

		$this->assertFalse( $settings['controls']['download'] );
	}

	public function test_resolve_settings_applies_dark_theme_preset(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				'theme' => 'dark',
			)
		);

		$this->assertSame( '#1a1a1a', $settings['colors']['background'] );
		$this->assertSame( '#f5f5f5', $settings['colors']['text'] );
	}
}
