<?php
/**
 * PHPUnit bootstrap for Magazine73.
 *
 * @package Magazine73
 */

declare(strict_types=1);

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

require_once __DIR__ . '/support/wordpress-stubs.php';
require_once __DIR__ . '/support/plugin-loader.php';

Magazine73\Tests\WordPressStub::reset();
