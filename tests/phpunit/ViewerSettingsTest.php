<?php
/**
 * Viewer settings tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests;

use Magazine73\Viewer_Settings;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magazine73\Viewer_Settings
 */
final class ViewerSettingsTest extends TestCase {

	/**
	 * Reset stub state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		WordPressStub::reset();
	}

	public function test_get_defaults_returns_expected_structure(): void {
		$defaults = Viewer_Settings::get_defaults();

		$this->assertSame( '#f5f5f5', $defaults['colors']['background'] );
		$this->assertSame( '', $defaults['colors']['icons'] );
		$this->assertSame( '', $defaults['colors']['counter'] );
		$this->assertTrue( $defaults['controls']['download'] );
		$this->assertTrue( $defaults['controls']['thumbnails'] );
	}

	public function test_get_color_keys_excludes_legacy_text_key(): void {
		$this->assertNotContains( 'text', Viewer_Settings::get_color_keys() );
		$this->assertContains( 'icons', Viewer_Settings::get_color_keys() );
		$this->assertContains( 'counter', Viewer_Settings::get_color_keys() );
	}

	public function test_sanitize_global_treats_missing_controls_as_false(): void {
		$sanitized = Viewer_Settings::sanitize_global(
			array(
				'colors' => array(
					'background' => '#111111',
				),
			)
		);

		$this->assertSame( '#111111', $sanitized['colors']['background'] );
		$this->assertFalse( $sanitized['controls']['zoom'] );
	}

	public function test_get_for_magazine_uses_global_settings_by_default(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = array(
			'colors'   => array(
				'background' => '#123456',
				'controls'   => '#ffffff',
				'text'       => '',
			),
			'controls' => array(
				'previous'   => false,
				'next'       => true,
				'counter'    => true,
				'fullscreen' => true,
				'download'   => true,
				'zoom'       => true,
				'thumbnails' => true,
			),
		);

		$settings = Viewer_Settings::get_for_magazine( 42 );

		$this->assertSame( '#123456', $settings['colors']['background'] );
		$this->assertFalse( $settings['controls']['previous'] );
	}

	public function test_get_for_magazine_uses_overrides_when_global_disabled(): void {
		WordPressStub::$post_meta[7][ Viewer_Settings::USE_GLOBAL_META_KEY ] = false;
		WordPressStub::$post_meta[7][ Viewer_Settings::OVERRIDES_META_KEY ]  = array(
			'colors'   => array(
				'background' => '#abcdef',
				'controls'   => '#ffffff',
				'text'       => '',
			),
			'controls' => array(
				'previous'   => true,
				'next'       => true,
				'counter'    => true,
				'fullscreen' => false,
				'download'   => false,
				'zoom'       => true,
				'thumbnails' => false,
			),
		);

		$settings = Viewer_Settings::get_for_magazine( 7 );

		$this->assertSame( '#abcdef', $settings['colors']['background'] );
		$this->assertFalse( $settings['controls']['fullscreen'] );
		$this->assertFalse( $settings['controls']['thumbnails'] );
	}
}
