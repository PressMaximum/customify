<?php
/**
 * PHPUnit bootstrap.
 *
 * Two run modes:
 *   - unit   — Brain Monkey only, no WordPress booted. Fast (<1s).
 *   - integration — full WP test suite via WP_TESTS_DIR env var.
 *
 * Pure unit tests should not need WP loaded. Integration tests should
 * extend WP_UnitTestCase and live under tests/php/integration/.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Define common WP constants the theme code touches at load-time so unit
 * tests can `require_once` theme files without booting WordPress.
 */
if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (! defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (! defined('CUSTOMIFY_TESTING')) {
    define('CUSTOMIFY_TESTING', true);
}

/**
 * Integration mode: bootstrap WP test suite if WP_TESTS_DIR is exported.
 */
$wp_tests_dir = getenv('WP_TESTS_DIR');
if ($wp_tests_dir && is_dir($wp_tests_dir)) {
    require_once $wp_tests_dir . '/includes/functions.php';

    tests_add_filter('muplugins_loaded', function () {
        switch_theme('customify');
    });

    require $wp_tests_dir . '/includes/bootstrap.php';
}
