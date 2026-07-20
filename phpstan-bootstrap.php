<?php
/**
 * PHPStan bootstrap for Magazine73 static analysis.
 *
 * @package Magazine73
 */

defined( 'ABSPATH' ) || define( 'ABSPATH', true );

if ( ! defined( 'MAGAZINE73_VERSION' ) ) {
	define( 'MAGAZINE73_VERSION', '0.1.0' );
}

if ( ! defined( 'MAGAZINE73_FILE' ) ) {
	define( 'MAGAZINE73_FILE', __DIR__ . '/plugin/magazine73/magazine73.php' );
}

if ( ! defined( 'MAGAZINE73_PATH' ) ) {
	define( 'MAGAZINE73_PATH', __DIR__ . '/plugin/magazine73/' );
}

if ( ! defined( 'MAGAZINE73_URL' ) ) {
	define( 'MAGAZINE73_URL', 'https://example.test/wp-content/plugins/magazine73/' );
}

if ( ! defined( 'MAGAZINE73_BASENAME' ) ) {
	define( 'MAGAZINE73_BASENAME', 'magazine73/magazine73.php' );
}

require_once __DIR__ . '/tests/phpstan/elementor-stubs.php';

