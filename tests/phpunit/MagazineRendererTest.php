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
		$this->assertSame( '#f5f5f5', $settings['colors']['icons'] );
		$this->assertSame( '#f5f5f5', $settings['colors']['counter'] );
	}

	public function test_resolve_settings_color_overrides_win_over_theme(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				'theme'            => 'dark',
				'color_background' => '#112233',
				'color_controls'   => '#445566',
				'color_text'       => '#aabbcc',
			)
		);

		$this->assertSame( '#112233', $settings['colors']['background'] );
		$this->assertSame( '#445566', $settings['colors']['controls'] );
		$this->assertSame( '#aabbcc', $settings['colors']['text'] );
	}

	public function test_resolve_settings_ignores_invalid_color_overrides(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();
		$defaults = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				'color_background' => 'not-a-color',
				'color_text'       => '',
			)
		);

		$this->assertSame( $defaults['colors']['background'], $settings['colors']['background'] );
		$this->assertSame( $defaults['colors']['text'], $settings['colors']['text'] );
	}

	public function test_resolve_settings_elementor_colors_override_magazine_colors(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = array(
			'colors' => array(
				'background' => '#939393',
				'controls'   => '#e5124a',
				'text'       => '#81d742',
			),
			'controls' => Viewer_Settings::get_defaults()['controls'],
		);

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				Magazine_Renderer::ELEMENTOR_COLOR_SOURCE => Magazine_Renderer::ELEMENTOR_COLOR_SOURCE_VALUE,
				'color_background'                        => '#6EC1E4',
				'color_controls'                          => '#54595F',
				'color_text'                              => '#7A7A7A',
			)
		);

		$this->assertSame( '#6EC1E4', $settings['colors']['background'] );
		$this->assertSame( '#54595F', $settings['colors']['controls'] );
		$this->assertSame( '#7A7A7A', $settings['colors']['text'] );
	}

	public function test_resolve_settings_elementor_inherits_magazine_colors_for_empty_fields(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = array(
			'colors' => array(
				'background' => '#939393',
				'controls'   => '#e5124a',
				'counter'    => '#222222',
				'text'       => '#81d742',
			),
			'controls' => Viewer_Settings::get_defaults()['controls'],
		);

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				Magazine_Renderer::ELEMENTOR_COLOR_SOURCE => Magazine_Renderer::ELEMENTOR_COLOR_SOURCE_VALUE,
				'color_background'                        => '#6EC1E4',
				'color_controls'                          => '',
				'color_counter'                           => '',
			)
		);

		$this->assertSame( '#6EC1E4', $settings['colors']['background'] );
		$this->assertSame( '#e5124a', $settings['colors']['controls'] );
		$this->assertSame( '#222222', $settings['colors']['counter'] );
		$this->assertSame( '#81d742', $settings['colors']['text'] );
	}

	public function test_resolve_settings_supports_icons_and_hover_overrides(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				'color_icons'          => '#112233',
				'color_icons_hover'    => '#445566',
				'color_counter'        => '#778899',
				'color_controls_hover' => '#aabbcc',
			)
		);

		$this->assertSame( '#112233', $settings['colors']['icons'] );
		$this->assertSame( '#445566', $settings['colors']['icons_hover'] );
		$this->assertSame( '#778899', $settings['colors']['counter'] );
		$this->assertSame( '#aabbcc', $settings['colors']['controls_hover'] );
	}

	public function test_resolve_settings_expands_legacy_text_color_into_icons_and_counter(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = Viewer_Settings::get_defaults();

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				Magazine_Renderer::ELEMENTOR_COLOR_SOURCE => Magazine_Renderer::ELEMENTOR_COLOR_SOURCE_VALUE,
				'color_text'                              => '#123456',
			)
		);

		$this->assertSame( '#123456', $settings['colors']['text'] );
		$this->assertSame( '#123456', $settings['colors']['icons'] );
		$this->assertSame( '#123456', $settings['colors']['counter'] );
	}

	public function test_resolve_settings_elementor_dark_theme_uses_palette_for_empty_style_colors(): void {
		WordPressStub::$options[ Viewer_Settings::OPTION_KEY ] = array(
			'colors' => array(
				'background' => '#939393',
				'controls'   => '#e5124a',
				'text'       => '#81d742',
			),
			'controls' => Viewer_Settings::get_defaults()['controls'],
		);

		$settings = Magazine_Renderer::resolve_settings(
			1,
			array(
				Magazine_Renderer::ELEMENTOR_COLOR_SOURCE => Magazine_Renderer::ELEMENTOR_COLOR_SOURCE_VALUE,
				'theme'                                   => 'dark',
				'color_background'                        => '#ff0000',
				'color_controls'                          => '',
				'color_icons'                             => '',
			)
		);

		$this->assertSame( '#ff0000', $settings['colors']['background'] );
		$this->assertSame( '#2d2d2d', $settings['colors']['controls'] );
		$this->assertSame( '#f5f5f5', $settings['colors']['icons'] );
		$this->assertSame( '#f5f5f5', $settings['colors']['counter'] );
	}
}
