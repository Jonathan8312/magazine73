<?php
/**
 * Magazine73 uninstall handler.
 *
 * @package Magazine73
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-post-type.php';
require_once __DIR__ . '/includes/class-viewer-settings.php';
require_once __DIR__ . '/includes/class-data-lifecycle.php';
require_once __DIR__ . '/includes/class-uninstaller.php';

Magazine73\Uninstaller::run();
