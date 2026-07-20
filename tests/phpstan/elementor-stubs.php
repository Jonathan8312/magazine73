<?php
/**
 * Minimal Elementor stubs for PHPStan (Elementor is a soft dependency).
 *
 * @package Magazine73
 */

namespace Elementor {

	/**
	 * Stub Elements_Manager.
	 */
	class Elements_Manager {
		/**
		 * @param string               $category_name Category slug.
		 * @param array<string, mixed> $category_args Category args.
		 */
		public function add_category( string $category_name, array $category_args ): void {}
	}

	/**
	 * Stub Widgets_Manager.
	 */
	class Widgets_Manager {
		/**
		 * @param Widget_Base $widget Widget instance.
		 */
		public function register( Widget_Base $widget ): void {}
	}

	/**
	 * Stub Controls_Manager constants used by Magazine73.
	 */
	class Controls_Manager {
		public const TAB_CONTENT = 'content';
		public const TAB_STYLE   = 'style';
		public const SELECT2     = 'select2';
		public const SELECT      = 'select';
		public const TEXT        = 'text';
		public const COLOR       = 'color';
	}

	/**
	 * Stub editor service.
	 */
	class Editor {
		public function is_edit_mode(): bool {
			return false;
		}
	}

	/**
	 * Stub Elementor plugin singleton.
	 */
	class Plugin {
		/**
		 * @var self|null
		 */
		public static $instance = null;

		/**
		 * @var Editor|null
		 */
		public $editor = null;

		/**
		 * @var object|null
		 */
		public $data_manager_v2 = null;

		/**
		 * @var object|null
		 */
		public $kits_manager = null;
	}

	/**
	 * Stub Widget_Base.
	 */
	abstract class Widget_Base {
		/**
		 * @return array<string, mixed>
		 */
		public function get_settings_for_display(): array {
			return array();
		}

		/**
		 * @param string               $id      Section id.
		 * @param array<string, mixed> $args    Section args.
		 */
		protected function start_controls_section( string $id, array $args ): void {}

		protected function end_controls_section(): void {}

		/**
		 * @param string               $id   Control id.
		 * @param array<string, mixed> $args Control args.
		 */
		protected function add_control( string $id, array $args ): void {}
	}
}
