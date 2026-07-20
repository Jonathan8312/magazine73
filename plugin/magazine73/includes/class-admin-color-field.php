<?php
/**
 * Admin color field helpers.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Renders WordPress color-picker fields for viewer colors.
 */
final class Admin_Color_Field {

	/**
	 * Render a text + Iris color picker input.
	 *
	 * @param string              $field_id   Input id.
	 * @param string              $field_name Input name.
	 * @param string              $value      Current hex value (may be empty).
	 * @param array<string,mixed> $args {
	 *     Optional arguments.
	 *
	 *     @type string $placeholder Placeholder text.
	 *     @type string $default     Default color for the picker reset control.
	 *     @type bool   $required    Whether an empty value is allowed.
	 * }
	 */
	public static function render( string $field_id, string $field_name, string $value, array $args = array() ): void {
		$placeholder = isset( $args['placeholder'] ) ? (string) $args['placeholder'] : '';
		$default     = isset( $args['default'] ) ? (string) $args['default'] : '';
		$required    = ! empty( $args['required'] );

		printf(
			'<span class="magazine73-color-field-wrap"><input type="text" class="magazine73-color-field" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s"%5$s%6$s autocomplete="off" spellcheck="false" /></span>',
			esc_attr( $field_id ),
			esc_attr( $field_name ),
			esc_attr( $value ),
			esc_attr( $placeholder ),
			'' !== $default ? ' data-default-color="' . esc_attr( $default ) . '"' : '',
			$required ? ' aria-required="true"' : ''
		);
	}
}
