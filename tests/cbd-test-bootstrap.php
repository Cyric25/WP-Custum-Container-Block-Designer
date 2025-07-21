<?php
/**
 * PHPUnit bootstrap file
 *
 * @package ContainerBlockDesigner
 */

// Define test constants
define('CBD_TESTS', true);

// Get WordPress tests directory
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration
if (!getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    putenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH=' . dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills');
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
    require dirname(__DIR__) . '/container-block-designer.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Load composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';